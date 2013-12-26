<?php
    require_once dirname(__FILE__)."/connector.class.php";
    require_once dirname(__FILE__)."/binding.class.php";
    require_once dirname(__FILE__)."/tools_site.php";
    require_once dirname(__FILE__)."/../config/config.php";
    require_once dirname(__FILE__)."/pluginregistry.class.php";

    // Helper class for character information for the current user.
    class CharacterInfo
    {
        public $CharacterId;
        public $Name;
        public $ClassName;
        public $IsMainChar;
        public $Role1;
        public $Role2;
    }

    // The UserProxy wraps away data for the currently logged in user (if any)
    // and provides functions for user authentication, modification, etc.
    class UserProxy
    {
        private static $mInstance = null;
        private static $mStickyLifeTime = 604800; // 60 * 60 * 24 * 7; // 1 week
        private static $mStickyCookieName = "ppx_raidplaner_sticky_";
        private static $mCryptName = "rijndael-256";
        private static $mBindings;
        private static $mBindingsByName;

        public $UserId     = 0;
        public $UserName   = "";
        public $UserGroup  = "none";
        public $Characters = array();
        public $Settings   = array();
        private $SiteId    = "";

        // --------------------------------------------------------------------------------------------

        public static function initBindings()
        {
            $NativeBinding = new NativeBinding();
            self::$mBindings = array(
                $NativeBinding // native has to be first
            );

            self::$mBindingsByName[$NativeBinding->getName()] = $NativeBinding;

            PluginRegistry::ForEachPlugin(function($PluginInstance)

            {
                array_push(self::$mBindings, $PluginInstance);
                self::$mBindingsByName[$PluginInstance->getName()] = $PluginInstance;
            });
        }

        // --------------------------------------------------------------------------------------------

        public function __construct($aAllowAutoLogin)
        {
            assert(self::$mInstance == NULL);

            beginSession();
            $this->SiteId = dechex(crc32(dirname(__FILE__)));

            if (isset($_REQUEST["logout"]))
            {
                // explicit "logout"

                $this->resetUser();

                $this->setSessionCookie(null);

                return; // ### return, logout ###
            }

            if (isset($_SESSION["User"]))
            {
                // Session says user is still logged in
                // Check if session matches database

                if ( $this->checkSessionCookie() )
                {
                    $this->updateCharacters();
                    $this->updateSettings();
                    return; // ### return, valid user ###
                }
            }

            // No "logout" and no exisiting session
            // Try to login via request or session cookie

            $LoginUser = null;

            if (isset($_REQUEST["user"]) &&

                isset($_REQUEST["pass"]))
            {
                // Explicit "login"

                $LoginUser = array( "Login"    => $_REQUEST["user"],
                                    "Password" => $_REQUEST["pass"],
                                    "Cookie"   => false );
            }
            else if ( isset($_COOKIE[self::$mStickyCookieName.$this->SiteId]) )
            {
                // Login via cookie
                // Reconstruct login data from cookie + database hash

                $Connector  = Connector::getInstance();
                $CookieData = $this->getSessionCookieData( $_COOKIE[self::$mStickyCookieName.$this->SiteId] );

                $UserQuery = $Connector->prepare( "SELECT SessionKey FROM `".RP_TABLE_PREFIX."User` WHERE UserId = :UserId LIMIT 1" );

                $UserQuery->bindValue(":UserId", $CookieData["UserId"], PDO::PARAM_INT );
                $UserData  = $UserQuery->fetchFirst();

                if ($UserData != null)
                {
                    $LoginData = self::decryptData($UserData["SessionKey"], $CookieData["InitVector"], $CookieData["Data"]);

                    if ( $LoginData !== false )
                    {
                        $LoginUser = array( "Login"     => $LoginData["Login"],
                                            "Password"  => $LoginData["Password"],
                                            "Cookie"    => true );
                    }
                }
            }

            // Check if login was requested (direct or indirect)
            // Process all available bindings in their order of registration

            if ( !$this->processLoginRequest($LoginUser, $aAllowAutoLogin) )
            {
                // All checks failed -> logout

                $this->resetUser();
            }
        }

        // --------------------------------------------------------------------------------------------

        public static function getInstance($aAllowAutoLogin=false)
        {
            if (self::$mInstance == NULL)
                self::$mInstance = new UserProxy($aAllowAutoLogin);

            return self::$mInstance;
        }

        // --------------------------------------------------------------------------------------------

        private function resetUser()
        {
            $this->UserGroup     = "none";
            $this->UserId        = 0;
            $this->UserName      = "";
            $this->Characters    = array();
            $this->Settings      = array();

            unset($_SESSION["User"]);
            unset($_SESSION["Calendar"]);
        }

        // --------------------------------------------------------------------------------------------

        public static function generateKey128()
        {
            return md5(mcrypt_create_iv(2048, MCRYPT_RAND));
        }

        // --------------------------------------------------------------------------------------------

        public function invalidateOneTimeKey( $aUserId )
        {
            $OneTimeKey = self::generateKey128();
            $Connector  = Connector::getInstance();

            $OtkQuery = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."User` SET OneTimeKey = :Key ".
                                            "WHERE AND UserId = :UserId LIMIT 1" );

            $OtkQuery->bindValue( ":Key",     $OneTimeKey, PDO::PARAM_STR );
            $OtkQuery->bindValue( ":UserId",  $aUserId,    PDO::PARAM_INT );
            $OtkQuery->execute();
        }

        // --------------------------------------------------------------------------------------------

        private static function encryptData( $aKey, $aData )
        {
            $CryptDesc  = mcrypt_module_open( self::$mCryptName, "", MCRYPT_MODE_CBC, "" );
            $InitVector = mcrypt_create_iv( mcrypt_enc_get_iv_size($CryptDesc), MCRYPT_RAND );

            $CryptedData = mcrypt_encrypt( self::$mCryptName, $aKey, serialize($aData), MCRYPT_MODE_CBC, $InitVector );
            mcrypt_module_close($CryptDesc);

            return Array(base64_encode($InitVector), base64_encode($CryptedData));
        }

        // --------------------------------------------------------------------------------------------

        private static function decryptData( $aKey, $aInitVector, $aData )
        {
            $CryptDesc = mcrypt_module_open( self::$mCryptName, "", MCRYPT_MODE_CBC, "" );

            $DecryptedData = mcrypt_decrypt( self::$mCryptName, $aKey, base64_decode($aData), MCRYPT_MODE_CBC, base64_decode($aInitVector) );
            mcrypt_module_close($CryptDesc);

            return @unserialize($DecryptedData);
        }

        // --------------------------------------------------------------------------------------------

        private function validateCleartextPassword( $aPassword, $aUserId, $aBindingName )
        {
            $Binding  = self::$mBindingsByName[$aBindingName];
            $UserInfo = $Binding->getUserInfoById($aUserId);

            if ($UserInfo == null)
                return false;

            $Method = $Binding->getMethodFromPass($UserInfo->Password);
            $Hashed = $Binding->hash($aPassword, $UserInfo->Salt, $Method);

            return $UserInfo->Password == $Hashed;
        }

        // --------------------------------------------------------------------------------------------

        private function setSessionCookie( $aData )
        {
            $ServerName = "";
            $ServerPath = "";
            $ServerUsesHttps = isset($_SERVER["HTTPS"]) && ($_SERVER["HTTPS"] != "") && ($_SERVER["HTTPS"] != null) && ($_SERVER["HTTPS"] != "off");

            if ( $aData == null )
            {
                setcookie( self::$mStickyCookieName.$this->SiteId, null, 0, $ServerPath, $ServerName, $ServerUsesHttps, true );
            }
            else
            {
                setcookie( self::$mStickyCookieName.$this->SiteId, $aData, time()+self::$mStickyLifeTime, $ServerPath, $ServerName, $ServerUsesHttps, true );
            }
        }

        // --------------------------------------------------------------------------------------------

        private function getSessionCookieData( $aCookieData )
        {
            $PackedData = explode(",", $aCookieData);
            return Array( "UserId" => $PackedData[0], "InitVector" => $PackedData[1], "Data" => $PackedData[2] );
        }

        // --------------------------------------------------------------------------------------------

        private function checkSessionCookie()
        {
            $this->UserGroup = "none";

            if ( isset($_SESSION["User"]) )
            {
                // Get the cookie data from the current session.
                // This test is weaker than the cookie based authentication which does a full
                // login. This function is ment to be used at each messsage hub call and must
                // this be fast.

                $CookieData = $this->getSessionCookieData( $_SESSION["User"] );

                $Connector = Connector::getInstance();
                $UserQuery = $Connector->prepare("SELECT Login, Password, `Group`, SessionKey FROM `".RP_TABLE_PREFIX."User` ".
                                                 "WHERE UserId = :UserId LIMIT 1");

                $UserQuery->bindValue(":UserId", $CookieData["UserId"], PDO::PARAM_INT);
                $UserData = $UserQuery->fetchFirst();

                if ( $UserData != null )
                {
                    $LoginData = self::decryptData($UserData["SessionKey"], $CookieData["InitVector"], $CookieData["Data"]);
                    $this->UserGroup = $UserData["Group"];
                    $this->UserId    = $CookieData["UserId"];
                    $this->UserName  = $UserData["Login"];

                    if ($LoginData !== false)
                    {
                        return ($LoginData["Login"]    == $UserData["Login"]) &&
                               ($LoginData["Password"] == $UserData["Password"]) &&
                               ($LoginData["Remote"]   == $_SERVER["REMOTE_ADDR"]);

                        // ### return, valid session cookie ###
                    }
                }
            }

            return false;
        }

        // --------------------------------------------------------------------------------------------

        private function getUserCredentialsFromInfo( $aUserInfo, $aBinding )
        {
            if ( $aUserInfo != null )
            {
                // UserInfo could be retrieved.
                // Generate a suitable, (mostly) random one-time-key,
                // store that key and return the required userInfo data.
                // By using the external id we prevent creating the same
                // user twice after an external rename.

                $OneTimeKey = self::generateKey128();
                $IsLocalInfo = $aUserInfo->BindingName == "none"; // In this case UserId != ExternalUserId (!)

                $UpdateUserQuery = $this->updateUserMirror( $aUserInfo, $IsLocalInfo, $OneTimeKey );
                $UpdateUserQuery->execute();

                if ( $UpdateUserQuery->getAffectedRows() == 0 )
                {
                    if ( $IsLocalInfo )
                        return null; // ### return, rare case guard (e.g. race condition) ###

                    // Update did not succeed, so the user is not yet registered to
                    // the local database. Create a new local hook for that user.

                    if ( self::createUser($aUserInfo->Group, $aUserInfo->UserId, $aUserInfo->BindingName,

                                          $aUserInfo->UserName, $aUserInfo->Password, $aUserInfo->Salt) === false )
                    {
                        return null; // ### return, user could not be created ###
                    }

                    // Set the one time key for the now existing user

                    $UpdateUserQuery->execute();
                }

                if (defined("USE_CLEARTEXT_PASSWORDS") && USE_CLEARTEXT_PASSWORDS)
                    $HashMethod = "cleartext";
                else
                    $HashMethod = self::$mBindingsByName[$aUserInfo->PassBinding]->getMethodFromPass($aUserInfo->Password);

                return Array( "salt"   => $aUserInfo->Salt,

                              "key"    => $OneTimeKey,

                              "method" => $HashMethod );

                // ### return, found user ###
            }

            return null;
        }

        // --------------------------------------------------------------------------------------------

        public function getExternalLoginData()
        {
            // Iterate all bindings and search for the given user

            foreach( self::$mBindings as $Binding )
            {
                if ( $Binding->isActive() )
                {
                    $UserInfo = $Binding->getExternalLoginData();

                    if ($UserInfo != null)
                    {
                        // Fetch the user data so UserId and Binding fields
                        // can be set to correct values for getUserCredentialsFromInfo

                        $ExternalUserId = $UserInfo->UserId;

                        $Connector = Connector::getInstance();
                        $UserQuery = $Connector->prepare( "SELECT * FROM `".RP_TABLE_PREFIX."User` ".
                                                          "WHERE ExternalId = :UserId AND ExternalBinding = :Binding LIMIT 1" );

                        $UserQuery->bindValue(":UserId", $UserInfo->UserId, PDO::PARAM_INT );
                        $UserQuery->bindValue(":Binding", $UserInfo->BindingName, PDO::PARAM_STR );

                        // The query might fail if the user is not yet registered

                        $UserData = $UserQuery->fetchFirst();

                        if ($UserData != null)
                        {

                            if ($UserData["BindingActive"] == "false")
                            {
                                // We are querying a local user in that case we need to
                                // modify the userinfo for getUserCredentialsFromInfo
                                // to work as expected

                                $UserInfo->BindingName = "none";
                                $UserInfo->UserId = $UserData["UserId"];
                            }
                        }

                        // Checking the credentials will create the user if necessary.

                        if ( $this->getUserCredentialsFromInfo($UserInfo, $Binding) != null )
                        {
                            // Revert user info to database values

                            $UserInfo->UserId = $ExternalUserId;
                            $UserInfo->BindingName = $Binding->BindingName;

                            return $UserInfo;
                        }
                    }
                }
            }

            // No external login detected

            return null;
        }

        // --------------------------------------------------------------------------------------------

        public function getUserCredentials( $aUserName )
        {
            // Iterate all bindings and search for the given user

            foreach( self::$mBindings as $Binding )
            {
                if ( $Binding->isActive() )
                {

                    $UserInfo    = $Binding->getUserInfoByName($aUserName);
                    $Credentials = $this->getUserCredentialsFromInfo($UserInfo, $Binding);

                    if ( $Credentials != null )
                        return $Credentials;
                }
            }

            // User not found

            return null;
        }

        // --------------------------------------------------------------------------------------------

        public function getUserCredentialsById( $aUserId, $aBindingName )
        {
            // Iterate all bindings and search for the given user

            if (isset(self::$mBindingsByName[$aBindingName]))
            {
                $Binding = self::$mBindingsByName[$aBindingName];
                if ( $Binding->isActive() )
                {

                    $UserInfo    = $Binding->getUserInfoById($aUserId);
                    $Credentials = $this->getUserCredentialsFromInfo($UserInfo, $Binding);

                    if ( $Credentials != null )
                        return $Credentials;
                }
            }

            // User not found

            return null;
        }

        // --------------------------------------------------------------------------------------------

        public function getUserInfoById( $aBindingName, $aExternalId )
        {
            $Binding = self::$mBindingsByName[$aBindingName];

            if ( $Binding->isActive() )
            {

                return $Binding->getUserInfoById($aExternalId);
            }

            return null;
        }

        // --------------------------------------------------------------------------------------------

        public static function getAllUserInfosById( $aExternalId )
        {
            $Candidates = array();

            foreach( self::$mBindings as $Binding )
            {
                if ( $Binding->isActive() )
                {

                    $Info = $Binding->getUserInfoById($aExternalId);
                    if ( $Info != null )
                    {
                        $Candidates[$Binding->BindingName] = $Info;
                    }
                }
            }

            return $Candidates;
        }

        // --------------------------------------------------------------------------------------------

        public function getAllUserInfosByName( $aUserName )
        {
            $Candidates = array();

            foreach( self::$mBindings as $Binding )
            {
                if ( $Binding->isActive() )
                {

                    $Info = $Binding->getUserInfoByName($aUserName);
                    if ( $Info != null )
                    {
                        $Candidates[$Binding->BindingName] = $Info;
                    }
                }
            }

            return $Candidates;
        }

        // --------------------------------------------------------------------------------------------

        public function validateCredentials( $aSignedPassword )
        {
            $Connector = Connector::getInstance();

            $UserQuery = $Connector->prepare( "SELECT OneTimeKey, Password, ExternalBinding, BindingActive, ExternalId ".
                                              "FROM `".RP_TABLE_PREFIX."User` WHERE UserId = :UserId LIMIT 1" );
            $UserQuery->bindValue(":UserId", $this->UserId, PDO::PARAM_INT );
            $UserData = $UserQuery->fetchFirst();

            if ($UserData != null)
            {

                if ( defined("USE_CLEARTEXT_PASSWORDS") && USE_CLEARTEXT_PASSWORDS )
                {
                    // Cleartext mode fallback

                    $UserId = (($UserData["BindingActive"] == "false") || ($UserData["ExternalBinding"] == "none"))

                        ? $this->UserId

                        : $UserData["ExternalId"];

                    return $this->validateCleartextPassword( $aSignedPassword, $UserId, $UserData["ExternalBinding"] );
                }

                $this->invalidateOneTimeKey( $this->UserId );

                $HashedStoredPassword = hash("sha256", $UserData["OneTimeKey"].$UserData["Password"]);

                if ( $aSignedPassword == $HashedStoredPassword )
                    return true;
            }

            return false;
        }

        // --------------------------------------------------------------------------------------------

        private function processLoginRequest( $aLoginUser, $aAllowAutoLogin )
        {
            $Connector = Connector::getInstance();
            $UserData = null;

            if ($aLoginUser == null)
            {
                if ( !$aAllowAutoLogin )
                    return false; // ### return, no data ###

                // Check for external logins

                $ExternalUser = $this->getExternalLoginData();

                if ($ExternalUser == null)
                    return false; // ### return, no data ###

                // Try to fetch the externally bound user.
                // We need to do this again (has already been done in getExternalLoginData)

                // to fetch any updated/newly created data

                $UserQuery = $Connector->prepare( "SELECT * FROM `".RP_TABLE_PREFIX."User` ".

                                                  "WHERE ExternalId = :UserId AND ExternalBinding = :Binding LIMIT 1" );

                $UserQuery->bindValue(":UserId", $ExternalUser->UserId, PDO::PARAM_INT );
                $UserQuery->bindValue(":Binding", $ExternalUser->BindingName, PDO::PARAM_STR );
                $UserData = $UserQuery->fetchFirst();

                if ($UserData == null)
                    return false; // ### return, not registered ###

            }
            else
            {

                // Try to login by $aLoginUser

                $PasswordCheckOk = false;

                $UserQuery = $Connector->prepare( "SELECT * FROM `".RP_TABLE_PREFIX."User` WHERE Login = :Login LIMIT 1" );

                $UserQuery->bindValue(":Login", $aLoginUser["Login"], PDO::PARAM_STR );
                $UserData = $UserQuery->fetchFirst();

                if ($UserData != null)
                {
                    if ( $aLoginUser["Cookie"] )
                    {
                        // User logged in using the encrypted cookie data.
                        // In this case just check the password.

                        $PasswordCheckOk = ($aLoginUser["Password"] == $UserData["Password"]);
                    }
                    else
                    {
                        if ( defined("USE_CLEARTEXT_PASSWORDS") && USE_CLEARTEXT_PASSWORDS )
                        {
                            // User logged in using a cleartext password.
                            // In this case we encrypt locally via php.

                            $UserId = (($UserData["BindingActive"] == "false") || ($UserData["ExternalBinding"] == "none"))

                                ? $UserData["UserId"]

                                : $UserData["ExternalId"];

                            $PasswordCheckOk = $this->validateCleartextPassword( $aLoginUser["Password"], $UserId, $UserData["ExternalBinding"] );
                        }
                        else
                        {

                            // User logged in using one-time-key authentication
                            // In this case we get a HMAC based password and need

                            // to reset the key

                            $this->invalidateOneTimeKey( $UserData["UserId"] );

                            $HashedStoredPassword = hash("sha256", $UserData["OneTimeKey"].$UserData["Password"]);

                            $PasswordCheckOk = ($aLoginUser["Password"] == $HashedStoredPassword);
                        }
                    }
                }

                if ( !$PasswordCheckOk )
                    return false; // ### return, invalid password ###
            }

            // Login successfull. Prepare session.
            // Update the current user entry to fix the external data binding (password, etc.)
            // and create a new session key while at it.

            $SessionKey = $this->updateSession( $UserData );

            // Encrypt session cookie

            $Data = array( "Login"    => $UserData["Login"],
                           "Password" => $UserData["Password"],
                           "Remote"   => $_SERVER["REMOTE_ADDR"] );

            $CookieData = intval($UserData["UserId"]).",".implode(",",self::encryptData($SessionKey, $Data));

            // Now query and set the session variables

            $_SESSION["User"] = $CookieData;
            $this->UserGroup  = $UserData["Group"];
            $this->UserId     = $UserData["UserId"];
            $this->UserName   = $UserData["Login"];

            $this->updateCharacters();
            $this->updateSettings();

            // Process sticky cookie
            // The sticky cookie stores the encrypted "credentials" part of the session

            if ( (isset($_REQUEST["sticky"]) && ($_REQUEST["sticky"] == "true")) ||
                 (isset($_COOKIE[self::$mStickyCookieName.$this->SiteId])) )
            {
                $this->setSessionCookie($CookieData);
            }

            return true; // ### return, logged in ###
        }

        // --------------------------------------------------------------------------------------------

        public function updateCharacters()
        {
            if ( $this->UserGroup != "none" )
            {
                $Connector = Connector::getInstance();
                $CharacterQuery = $Connector->prepare( "SELECT * FROM `".RP_TABLE_PREFIX."Character` ".
                                                       "WHERE UserId = :UserId ".
                                                       "ORDER BY Mainchar, Name" );

                $CharacterQuery->bindValue(":UserId", $this->UserId, PDO::PARAM_INT);

                $Characters = array();
                $CharacterQuery->loop( function($Row) use (&$Characters)
                {
                    $Character = new CharacterInfo();

                    $Character->CharacterId = $Row["CharacterId"];
                    $Character->Name        = $Row["Name"];
                    $Character->ClassName   = $Row["Class"];
                    $Character->IsMainChar  = $Row["Mainchar"] == "true";
                    $Character->Role1       = $Row["Role1"];
                    $Character->Role2       = $Row["Role2"];

                    array_push($Characters, $Character);
                });

                $this->Characters = $Characters;
            }
        }

        // --------------------------------------------------------------------------------------------

        public function updateSettings()
        {
            if ( $this->UserGroup != "none" )
            {
                $Connector = Connector::getInstance();
                $SettingQuery = $Connector->prepare( "SELECT * FROM `".RP_TABLE_PREFIX."UserSetting` ".
                                                     "WHERE UserId = :UserId" );

                $SettingQuery->bindValue(":UserId", $this->UserId, PDO::PARAM_INT);

                $Settings = array();

                $SettingQuery->loop( function($Row) use (&$Settings)
                {
                    $Settings[$Row["Name"]] = array("number" => $Row["IntValue"], "text" => $Row["TextValue"]);
                });

                $this->Settings = $Settings;
            }
        }

        // --------------------------------------------------------------------------------------------

        public static function createUser( $aGroup, $aExternalUserId, $aBindingName, $aLogin, $aHashedPassword, $aSalt )
        {
            $Connector = Connector::getInstance();

            // Pre-check:
            // Login must be unique

            $UserQuery = $Connector->prepare("SELECT UserId FROM `".RP_TABLE_PREFIX."User` ".
                                             "WHERE Login = :Login LIMIT 1");

            $UserQuery->bindValue(":Login", strtolower($aLogin), PDO::PARAM_STR);

            if ( $UserQuery->execute() && ($UserQuery->getAffectedRows() == 0) )
            {
                // User does not exist, so we can create one

                $UserQuery = $Connector->prepare("INSERT INTO `".RP_TABLE_PREFIX."User` ".
                                                 "(`Group`, ExternalId, ExternalBinding, BindingActive, Login, Password, Salt, Created, OneTimeKey, SessionKey) ".
                                                 "VALUES (:Group, :ExternalUserId, :Binding, :Active, :Login, :Password, :Salt, FROM_UNIXTIME(:Created), '', '')");

                $Active = ($aBindingName != "none") ? "true" : "false";

                $UserQuery->bindValue(":Group",          $aGroup,             PDO::PARAM_STR);
                $UserQuery->bindValue(":ExternalUserId", $aExternalUserId,    PDO::PARAM_INT);
                $UserQuery->bindValue(":Binding",        $aBindingName,       PDO::PARAM_STR);
                $UserQuery->bindValue(":Active",         $Active,             PDO::PARAM_STR);
                $UserQuery->bindValue(":Login",          strtolower($aLogin), PDO::PARAM_STR);
                $UserQuery->bindValue(":Password",       $aHashedPassword,    PDO::PARAM_STR);
                $UserQuery->bindValue(":Salt",           $aSalt,              PDO::PARAM_STR);
                $UserQuery->bindValue(":Created",        time(),              PDO::PARAM_INT);

                $UserQuery->execute();
                return $Connector->lastInsertId(); // ### return, inserted ###
            }

            return false;
        }

        // --------------------------------------------------------------------------------------------

        public function updateUserMirror( &$UserInfo, $aIsStoredLocally, $aKey )
        {

            // TODO: $UserInfo->UserId referres to UserId when updating a local user
            //       and ExternalUserId otherwise. This is inconvenient.

            $Out = Out::getInstance();
            $Connector = Connector::getInstance();

            if ($aIsStoredLocally)
            {
                // Local users don't change externally, so just update the key

                $MirrorQuery = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."User` SET OneTimeKey = :Key ".
                                                   "WHERE UserId = :UserId LIMIT 1" );

                $MirrorQuery->bindValue( ":Key",    $aKey,             PDO::PARAM_STR );
                $MirrorQuery->bindValue( ":UserId", $UserInfo->UserId, PDO::PARAM_INT );
            }
            else
            {
                if (!isset(self::$mBindingsByName[$UserInfo->PassBinding]))
                {
                    $Out->pushError($UserInfo->PassBinding." binding did not register correctly.");
                }
                else
                {
                    $Binding = self::$mBindingsByName[$UserInfo->PassBinding];

                    if ($Binding->isActive())
                    {
                        $ExternalInfo = $Binding->getUserInfoById($UserInfo->UserId);
                        if ( $ExternalInfo != null )
                            $UserInfo = $ExternalInfo;

                    }
                    else
                    {
                        $Out->pushError($UserInfo->PassBinding." binding has been disabled.");
                    }
                }

                // Local users may update externally, so sync the credentials

                $SyncGroup = !defined("ALLOW_GROUP_SYNC") || ALLOW_GROUP_SYNC;

                $MirrorQuery = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."User` SET ".
                                                   "Login = :Login, Password = :Password, Salt = :Salt, OneTimeKey = :Key".
                                                   (($SyncGroup) ? ", `Group` = :Group " : " ").
                                                   "WHERE ExternalBinding = :Binding AND ExternalId = :UserId LIMIT 1" );

                $MirrorQuery->bindValue( ":Login",     $UserInfo->UserName,    PDO::PARAM_STR );
                $MirrorQuery->bindValue( ":Password",  $UserInfo->Password,    PDO::PARAM_STR );
                $MirrorQuery->bindValue( ":Salt",      $UserInfo->Salt,        PDO::PARAM_STR );
                $MirrorQuery->bindValue( ":Key",       $aKey,                  PDO::PARAM_STR );
                $MirrorQuery->bindValue( ":Binding",   $UserInfo->BindingName, PDO::PARAM_STR );
                $MirrorQuery->bindValue( ":UserId",    $UserInfo->UserId,      PDO::PARAM_INT );

                if ($SyncGroup)

                    $MirrorQuery->bindValue( ":Group", $UserInfo->Group,       PDO::PARAM_STR );
            }

            return $MirrorQuery;
        }

        // --------------------------------------------------------------------------------------------

        private function updateSession( &$UserData )
        {
            $SessionKey = self::generateKey128();

            $Connector = Connector::getInstance();

            if ( ($UserData["ExternalBinding"] == "none") || ($UserData["BindingActive"] == "false"))
            {
                // Local user
                // Just update the session key.

                $SessionQuery = $Connector->prepare( "UPDATE `".RP_TABLE_PREFIX."User` SET SessionKey = :Key ".
                                                  "WHERE UserId = :UserId LIMIT 1" );

                $SessionQuery->bindValue( ":UserId", $UserData["UserId"], PDO::PARAM_INT );

                $SessionQuery->bindValue( ":Key",    $SessionKey,         PDO::PARAM_STR );
                $SessionQuery->execute();

                // To avoid re-fetching, update $UserData

                $UserData["SessionKey"] = $SessionKey;
            }
            else
            {
                $ExternalUserInfo = self::$mBindingsByName[$UserData["ExternalBinding"]]->getUserInfoById($UserData["ExternalId"]);

                if ($ExternalUserInfo == null)
                {
                    // Convert to local user
                    // Update the session key and disable the binding.

                    $ConvertQuery = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."User` ".
                                                     "SET SessionKey = :Key, BindingActive='false' ".
                                                     "WHERE UserId = :UserId LIMIT 1" );

                    $ConvertQuery->bindValue( ":UserId", $UserData["UserId"], PDO::PARAM_INT );

                    $ConvertQuery->bindValue( ":Key",    $SessionKey,         PDO::PARAM_STR );
                    $ConvertQuery->execute();

                    // To avoid re-fetching, update $UserData

                    $UserData["SessionKey"]    = $SessionKey;
                    $UserData["BindingActive"] = "false";
                }
                else
                {
                    // Update binding
                    // Update the session key and validate the binding as active.
                    // Login has to be synced to cover user renaming.

                    $UpdateQuery = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."User` SET ".
                                                    "Login = :Login, SessionKey = :Key, BindingActive='true' ".
                                                    "WHERE UserId = :UserId LIMIT 1" );

                    $UpdateQuery->bindValue( ":UserId", $UserData["UserId"],         PDO::PARAM_INT );
                    $UpdateQuery->bindValue( ":Login",  $ExternalUserInfo->UserName, PDO::PARAM_STR );
                    $UpdateQuery->bindValue( ":Key",    $SessionKey,                 PDO::PARAM_STR );
                    $UpdateQuery->execute();

                    // To avoid re-fetching, update $UserData

                    $UserData["Login"]         = $ExternalUserInfo->UserName;
                    $UserData["SessionKey"]    = $SessionKey;
                    $UserData["BindingActive"] = "true";
                }

            }

            return $SessionKey;
        }

        // --------------------------------------------------------------------------------------------

        public static function changePassword( $aUserId, $aHashedPassword, $aSalt )
        {
            $IsCurrentUser = self::getInstance()->UserId == $aUserId;

            if ( !$IsCurrentUser && !validAdmin() )
                return false; // ### return, security check failed ###

            // Change password to new values.
            // Only accounts with an inactive binding may be changed.

            $SessionKey = self::generateKey128();

            $Connector = Connector::getInstance();
            $UpdateQuery  = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."User` SET ".
                                                "ExternalBinding = 'none', Password = :Password, Salt = :Salt, SessionKey = :Key ".
                                                "WHERE UserId = :UserId AND (BindingActive='false' OR ExternalBinding='none') LIMIT 1");

            $UpdateQuery->bindValue(":UserId",   $aUserId,         PDO::PARAM_INT);
            $UpdateQuery->bindValue(":Password", $aHashedPassword, PDO::PARAM_STR);
            $UpdateQuery->bindValue(":Salt",     $aSalt,           PDO::PARAM_STR);
            $UpdateQuery->bindValue(":Key",      $SessionKey,      PDO::PARAM_STR);

            if ( $UpdateQuery->execute() && $IsCurrentUser )
            {
                // Fetch login name for user (might not be current)

                $LoginQuery = $Connector->prepare("SELECT Login FROM `".RP_TABLE_PREFIX."User` WHERE UserId = :UserId");
                $LoginQuery->bindValue(":UserId", $aUserId, PDO::PARAM_INT);

                $UserData = $LoginQuery->fetchFirst();

                // update cookie data
                // both session and sticky cookie have to be updated

                if ($UserData != null)
                {
                    $Data = array( "Login"    => $UserData["Login"],
                                   "Password" => $aHashedPassword,
                                   "Remote"   => $_SERVER["REMOTE_ADDR"] );

                    $CookieData = intval($aUserId).",".implode(",",self::encryptData($SessionKey, $Data));
                    $_SESSION["User"] = $CookieData;

                    if ( isset($_COOKIE[self::$mStickyCookieName.self::$mInstance->SiteId]) )
                        $this->setSessionCookie($CookieData);
                }
            }

            return $Success;
        }
    }

    // --------------------------------------------------------------------------------------------

    UserProxy::initBindings();

    // --------------------------------------------------------------------------------------------

    function msgQueryUser($aRequest)
    {
        $Out = Out::getInstance();

        if (registeredUser())
        {
            $CurrentUser = UserProxy::getInstance();

            $CharacterIds = array();
            $CharacterNames = array();
            $CharacterClasses = array();
            $CharacterRoles1 = array();
            $CharacterRoles2 = array();
            $Settings = array();

            foreach( $CurrentUser->Characters as $Character )
            {
                array_push($CharacterIds, $Character->CharacterId);
                array_push($CharacterNames, $Character->Name);
                array_push($CharacterClasses, $Character->ClassName);
                array_push($CharacterRoles1, $Character->Role1);
                array_push($CharacterRoles2, $Character->Role2);
            }

            $Out->pushValue("registeredUser", true);
            $Out->pushValue("id", $CurrentUser->UserId);
            $Out->pushValue("name", $CurrentUser->UserName);
            $Out->pushValue("characterIds", $CharacterIds);
            $Out->pushValue("characterNames", $CharacterNames);
            $Out->pushValue("characterClass", $CharacterClasses);
            $Out->pushValue("role1", $CharacterRoles1);
            $Out->pushValue("role2", $CharacterRoles2);

            $Out->pushValue("validUser", validUser());
            $Out->pushValue("isRaidlead", validRaidlead());
            $Out->pushValue("isAdmin", validAdmin());
            $Out->pushValue("settings", $CurrentUser->Settings);

            if (isset($_SESSION["Calendar"]))
            {
                $CalendarValues = array("month" => $_SESSION["Calendar"]["month"], "year" => $_SESSION["Calendar"]["year"]);
                $Out->pushValue("calendar", $CalendarValues);
            }
            else
            {
                $Out->pushValue("calendar", null);
            }
        }
        else
        {
            $Out->pushValue("registeredUser", false);
        }
    }

    // --------------------------------------------------------------------------------------------

    function registeredUser()
    {
        UserProxy::getInstance();
        return isset($_SESSION["User"]);
    }

    // --------------------------------------------------------------------------------------------

    function validUser()
    {
        $Group = UserProxy::getInstance()->UserGroup;
        return isset($_SESSION["User"]) && ($Group != "none");
    }

    // --------------------------------------------------------------------------------------------

    function validRaidlead()
    {
        $Group = UserProxy::getInstance()->UserGroup;
        return isset($_SESSION["User"]) && (($Group == "raidlead") || ($Group == "admin"));
    }

    // --------------------------------------------------------------------------------------------

    function validAdmin()
    {
        $Group = UserProxy::getInstance()->UserGroup;
        return isset($_SESSION["User"]) && ($Group == "admin");
    }
?>