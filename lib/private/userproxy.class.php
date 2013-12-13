<?php
    require_once dirname(__FILE__)."/connector.class.php";
    require_once dirname(__FILE__)."/tools_site.php";
    require_once dirname(__FILE__)."/../config/config.php";
    
    // Helper class for loaded plugins
    class PluginRegistry
    {
        public static $Classes = array();
    }
    
    // load files from the bindings folder
    if ($FolderHandle = opendir(dirname(__FILE__)."/bindings")) 
    {
        while (($PluginFile = readdir($FolderHandle)) !== false) 
        {
            $FileParts = explode(".",$PluginFile);            
            if (strtolower($FileParts[sizeof($FileParts)-1]) == "php")
                require_once dirname(__FILE__)."/bindings/".$PluginFile;    
        }
    }
    
    // Helper class for external bindings, so we don't have to use string
    // based associative arrays.
    class UserInfo
    {
        public $UserId;
        public $UserName;
        public $Password;
        public $Salt;
        public $SessionSalt;
        public $Group;
        public $BindingName;
        public $PassBinding;
    }
    
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
            
            self::$mBindingsByName[$NativeBinding->BindingName] = $NativeBinding;
            
            foreach(PluginRegistry::$Classes as $PluginName)
            {
                $Plugin = new ReflectionClass($PluginName);
                $PluginInstance = $Plugin->newInstance();
                array_push(self::$mBindings, $PluginInstance);
                
                self::$mBindingsByName[$PluginInstance->BindingName] = $PluginInstance;
            }
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
                               
                $UserSt = $Connector->prepare( "SELECT SessionKey FROM `".RP_TABLE_PREFIX."User` WHERE UserId = :UserId LIMIT 1" );
                
                $UserSt->bindValue(":UserId", $CookieData["UserId"], PDO::PARAM_INT );
                $UserSt->execute();
                
                if ($UserSt->rowcount() > 0)
                {
                    $UserData  = $UserSt->fetch( PDO::FETCH_ASSOC );                
                    $LoginData = self::decryptData($UserData["SessionKey"], $CookieData["InitVector"], $CookieData["Data"]);
                
                    if ( $LoginData !== false )
                    {
                        $LoginUser = array( "Login"     => $LoginData["Login"],
                                            "Password"  => $LoginData["Password"],
                                            "Cookie"    => true );
                    }
                }

                $UserSt->closeCursor();
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
            
            $OtkSt = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."User` SET OneTimeKey = :Key ".
                                         "WHERE AND UserId = :UserId LIMIT 1" );
            
            $OtkSt->bindValue( ":Key",     $OneTimeKey, PDO::PARAM_STR );
            $OtkSt->bindValue( ":UserId",  $aUserId,    PDO::PARAM_INT );
            $OtkSt->execute();
            $OtkSt->closeCursor();
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
                $UserSt = $Connector->prepare("SELECT Login, Password, `Group`, SessionKey FROM `".RP_TABLE_PREFIX."User` ".
                                              "WHERE UserId = :UserId LIMIT 1");

                $UserSt->bindValue(":UserId", $CookieData["UserId"], PDO::PARAM_INT);
                $UserSt->execute();
                
                if ( $UserSt->rowcount() > 0 )
                {
                    $UserData = $UserSt->fetch(PDO::FETCH_ASSOC);
                    $UserSt->closeCursor();
                    
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
                
                $UserSt->closeCursor();
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
                $IsLocalInfo = $aUserInfo->BindingName == "none";
                $IsNativeBinding = $aBinding->BindingName == "none";
                
                $UpdateUserSt = $this->updateUserMirror( $aUserInfo, $IsNativeBinding, $OneTimeKey );
                $UpdateUserSt->execute();
                
                if ( $UpdateUserSt->rowcount() == 0 )
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
                    
                    $UpdateUserSt->execute();
                }
                
                $UpdateUserSt->closeCursor();
                
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

        public function getExternalLoginData()
        {
            if (defined("ALLOW_AUTO_LOGIN") && ALLOW_AUTO_LOGIN)
            {         
                // Iterate all bindings and search for the given user
                
                foreach( self::$mBindings as $Binding )
                {
                    if ( $Binding->isActive() )
                    {
                        $UserData = $Binding->getExternalLoginData();
                        if ( $UserData != null )
                            return $UserData;
                    }
                }
            }
            
            // No external login detected
            
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
            $UserSt = $Connector->prepare( "SELECT OneTimeKey, Password, ExternalBinding, BindingActive, ExternalId FROM `".RP_TABLE_PREFIX."User` WHERE UserId = :UserId LIMIT 1" );
            $UserSt->bindValue(":UserId", $this->UserId, PDO::PARAM_INT );
            $UserSt->execute();
            
            if ($UserSt->rowcount() > 0)
            {
                $UserData = $UserSt->fetch(PDO::FETCH_ASSOC);
                
                if ( defined("USE_CLEARTEXT_PASSWORDS") && USE_CLEARTEXT_PASSWORDS )
                {
                    // Cleartext mode fallback
                
                    $UserSt->closeCursor();
                    $UserId = (($UserData["BindingActive"] == "false") || ($UserData["ExternalBinding"] == "none")) 
                        ? $this->UserId 
                        : $UserData["ExternalId"];
                    
                    return $this->validateCleartextPassword( $aSignedPassword, $UserId, $UserData["ExternalBinding"] );
                }
                else
                {
                    $this->invalidateOneTimeKey( $this->UserId );
                        
                    $HashedStoredPassword = hash("sha256", $UserData["OneTimeKey"].$UserData["Password"]);
                
                    if ( $aSignedPassword == $HashedStoredPassword )
                    {
                        $UserSt->closeCursor();
                        return true;
                    }
                }
            }
            
            $UserSt->closeCursor();
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
                    
                // try to fetch the externally bound user 
                
                $UserSt = $Connector->prepare( "SELECT * FROM `".RP_TABLE_PREFIX."User` WHERE ExternalId = :UserId AND ExternalBinding = :Binding LIMIT 1" );
                $UserSt->bindValue(":UserId", $ExternalUser->UserId, PDO::PARAM_INT );
                $UserSt->bindValue(":Binding", $ExternalUser->BindingName, PDO::PARAM_STR );
                $UserSt->execute();
                
                if ($UserSt->rowcount() > 0)
                {
                    $UserData = $UserSt->fetch(PDO::FETCH_ASSOC);
                }
                
                $UserSt->closeCursor();
                if ($UserData == null)
                    return false; // ### return, not registered ###               
            }
            else
            {           
                // Try to login by $aLoginUser
                
                $PasswordCheckOk = false;
                                
                $UserSt = $Connector->prepare( "SELECT * FROM `".RP_TABLE_PREFIX."User` WHERE Login = :Login LIMIT 1" );
                $UserSt->bindValue(":Login", $aLoginUser["Login"], PDO::PARAM_STR );
                $UserSt->execute();
                
                if ($UserSt->rowcount() > 0)
                {
                    $UserData = $UserSt->fetch(PDO::FETCH_ASSOC);
                                                        
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
                
                $UserSt->closeCursor();                
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
                $CharacterSt = $Connector->prepare( "SELECT * FROM `".RP_TABLE_PREFIX."Character` ".
                                                    "WHERE UserId = :UserId ".
                                                    "ORDER BY Mainchar, Name" );

                $CharacterSt->bindValue(":UserId", $this->UserId, PDO::PARAM_INT);
                $CharacterSt->execute();
                
                $this->Characters = array();

                while ( $Row = $CharacterSt->fetch( PDO::FETCH_ASSOC ) )
                {
                    $Character = new CharacterInfo();
                    
                    $Character->CharacterId = $Row["CharacterId"];
                    $Character->Name        = $Row["Name"];
                    $Character->ClassName   = $Row["Class"];
                    $Character->IsMainChar  = $Row["Mainchar"] == "true";
                    $Character->Role1       = $Row["Role1"];
                    $Character->Role2       = $Row["Role2"];
                    
                    array_push($this->Characters, $Character);
                }
                
                $CharacterSt->closeCursor();
            }
        }
        
        // --------------------------------------------------------------------------------------------

        public function updateSettings()
        {
            if ( $this->UserGroup != "none" )
            {
                $Connector = Connector::getInstance();
                $SettingSt = $Connector->prepare( "SELECT * FROM `".RP_TABLE_PREFIX."UserSetting` ".
                                                  "WHERE UserId = :UserId" );

                $SettingSt->bindValue(":UserId", $this->UserId, PDO::PARAM_INT);
                $SettingSt->execute();
                
                $this->Settings = array();

                while ( $Row = $SettingSt->fetch( PDO::FETCH_ASSOC ) )
                {
                    $this->Settings[$Row["Name"]] = array("number" => $Row["IntValue"], "text" => $Row["TextValue"]);
                }
                
                $SettingSt->closeCursor();
            }
        }

        // --------------------------------------------------------------------------------------------

        public static function createUser( $aGroup, $aExternalUserId, $aBindingName, $aLogin, $aHashedPassword, $aSalt )
        {
            $Connector = Connector::getInstance();
            
            // Pre-check:
            // Login must be unique
            
            $UserSt = $Connector->prepare("SELECT UserId FROM `".RP_TABLE_PREFIX."User` ".
                                          "WHERE Login = :Login LIMIT 1");

            $UserSt->bindValue(":Login", strtolower($aLogin), PDO::PARAM_STR);
            $UserSt->execute();
            
            if ( $UserSt->rowcount() == 0 )
            {
                // User does not exist, so we can create one
                               
                $UserSt->closeCursor();
                $UserSt = $Connector->prepare("INSERT INTO `".RP_TABLE_PREFIX."User` ".
                                              "(`Group`, ExternalId, ExternalBinding, BindingActive, Login, Password, Salt, Created, OneTimeKey, SessionKey) ".
                                              "VALUES (:Group, :ExternalUserId, :Binding, :Active, :Login, :Password, :Salt, FROM_UNIXTIME(:Created), '', '')");
                                              
                $Active = ($aBindingName != "none") ? "true" : "false";

                $UserSt->bindValue(":Group",          $aGroup,             PDO::PARAM_STR);
                $UserSt->bindValue(":ExternalUserId", $aExternalUserId,    PDO::PARAM_INT);
                $UserSt->bindValue(":Binding",        $aBindingName,       PDO::PARAM_STR);
                $UserSt->bindValue(":Active",         $Active,             PDO::PARAM_STR);
                $UserSt->bindValue(":Login",          strtolower($aLogin), PDO::PARAM_STR);
                $UserSt->bindValue(":Password",       $aHashedPassword,    PDO::PARAM_STR);
                $UserSt->bindValue(":Salt",           $aSalt,              PDO::PARAM_STR);
                $UserSt->bindValue(":Created",        time(),              PDO::PARAM_INT);

                if (!$UserSt->execute())
                    postErrorMessage($UserSt);
                $UserSt->closeCursor();

                return $Connector->lastInsertId(); // ### return, inserted ###
            }

            $UserSt->closeCursor();
            return false;
        }
        
        // --------------------------------------------------------------------------------------------
        
        public function updateUserMirror( &$UserInfo, $aIsStoredLocally, $aKey )
        {   
            $Out = Out::getInstance();
            $Connector = Connector::getInstance();
                 
            if ($UserInfo->BindingName == "none")
            {
                // Local users don't change externally, so just update the key
                
                $MirrorSt = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."User` SET OneTimeKey = :Key ".
                                                "WHERE UserId = :UserId LIMIT 1" );
                                                
                $MirrorSt->bindValue( ":Key",    $aKey,             PDO::PARAM_STR );
                $MirrorSt->bindValue( ":UserId", $UserInfo->UserId, PDO::PARAM_INT );
            }
            else
            {
                if ( $aIsStoredLocally )
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
                }
                
                // Local users may update externally, so sync the credentials
                
                $SyncGroup = !defined("ALLOW_GROUP_SYNC") || ALLOW_GROUP_SYNC;
                
                $MirrorSt = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."User` SET ".
                                                "Login = :Login, Password = :Password, Salt = :Salt, OneTimeKey = :Key".
                                                (($SyncGroup) ? ", `Group` = :Group " : " ").
                                                "WHERE ExternalBinding = :Binding AND ExternalId = :UserId LIMIT 1" );
                
                $MirrorSt->bindValue( ":Login",     $UserInfo->UserName,    PDO::PARAM_STR );
                $MirrorSt->bindValue( ":Password",  $UserInfo->Password,    PDO::PARAM_STR );
                $MirrorSt->bindValue( ":Salt",      $UserInfo->Salt,        PDO::PARAM_STR );
                $MirrorSt->bindValue( ":Key",       $aKey,                  PDO::PARAM_STR );
                $MirrorSt->bindValue( ":Binding",   $UserInfo->BindingName, PDO::PARAM_STR );
                $MirrorSt->bindValue( ":UserId",    $UserInfo->UserId,      PDO::PARAM_INT );
                
                if ($SyncGroup) 
                    $MirrorSt->bindValue( ":Group", $UserInfo->Group,       PDO::PARAM_STR );
            }                     
            
            return $MirrorSt;
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
                
                $SessionSt = $Connector->prepare( "UPDATE `".RP_TABLE_PREFIX."User` SET SessionKey = :Key ".
                                                  "WHERE UserId = :UserId LIMIT 1" );
                
                $SessionSt->bindValue( ":UserId", $UserData["UserId"], PDO::PARAM_INT );                        
                $SessionSt->bindValue( ":Key",    $SessionKey,         PDO::PARAM_STR );
                $SessionSt->execute();
                $SessionSt->closeCursor();
                
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
                    
                    $ConvertSt = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."User` ".
                                                     "SET SessionKey = :Key, BindingActive='false' ".
                                                     "WHERE UserId = :UserId LIMIT 1" );
                
                    $ConvertSt->bindValue( ":UserId", $UserData["UserId"], PDO::PARAM_INT );                        
                    $ConvertSt->bindValue( ":Key",    $SessionKey,         PDO::PARAM_STR );
                    $ConvertSt->execute();
                    $ConvertSt->closeCursor();
                    
                    // To avoid re-fetching, update $UserData
                    
                    $UserData["SessionKey"]    = $SessionKey;
                    $UserData["BindingActive"] = "false";
                }
                else
                {
                    // Update binding
                    // Update the session key and validate the binding as active.
                    // Login has to be synced to cover user renaming.
                    
                    $UpdateSt = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."User` SET ".
                                                    "Login = :Login, SessionKey = :Key, BindingActive='true' ".
                                                    "WHERE UserId = :UserId LIMIT 1" );
                
                    $UpdateSt->bindValue( ":UserId", $UserData["UserId"],         PDO::PARAM_INT );
                    $UpdateSt->bindValue( ":Login",  $ExternalUserInfo->UserName, PDO::PARAM_STR );
                    $UpdateSt->bindValue( ":Key",    $SessionKey,                 PDO::PARAM_STR );
                    $UpdateSt->execute();
                    $UpdateSt->closeCursor();
                    
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
            $UpdateSt  = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."User` SET ".
                                             "ExternalBinding = 'none', Password = :Password, Salt = :Salt, SessionKey = :Key ".
                                             "WHERE UserId = :UserId AND (BindingActive='false' OR ExternalBinding='none') LIMIT 1");
                                            
            $UpdateSt->bindValue(":UserId",   $aUserId,         PDO::PARAM_INT);
            $UpdateSt->bindValue(":Password", $aHashedPassword, PDO::PARAM_STR);
            $UpdateSt->bindValue(":Salt",     $aSalt,           PDO::PARAM_STR);
            $UpdateSt->bindValue(":Key",      $SessionKey,      PDO::PARAM_STR);

            $Success = $UpdateSt->execute();
            $UpdateSt->closeCursor();
            
            if ( $Success && $IsCurrentUser )
            {
                // Fetch login name for user (might not be current)
                
                $LoginSt = $Connector->prepare("SELECT Login FROM `".RP_TABLE_PREFIX."User` WHERE UserId = :UserId");
                $LoginSt->bindValue(":UserId", $aUserId, PDO::PARAM_INT);
                $LoginSt->execute();
                
                $UserData = $LoginSt->fetch(PDO::FETCH_ASSOC);
                $LoginSt->closeCursor();
                
                // update cookie data
                // both session and sticky cookie have to be updated
                
                $Data = array( "Login"    => $UserData["Login"],
                               "Password" => $aHashedPassword,
                               "Remote"   => $_SERVER["REMOTE_ADDR"] );

                $CookieData = intval($aUserId).",".implode(",",self::encryptData($SessionKey, $Data));
                $_SESSION["User"] = $CookieData;
                    
                if ( isset($_COOKIE[self::$mStickyCookieName.$this->SiteId]) )
                    $this->setSessionCookie($CookieData);
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
            
            $CharacterIds = [];
            $CharacterNames = [];
            $CharacterClasses = [];
            $CharacterRoles1 = [];
            $CharacterRoles2 = [];
            $Settings = [];
            
            foreach( $CurrentUser->Characters as $Character )
            {
                array_push($CharacterIds, $Character->CharacterId);
                array_push($CharacterNames, $Character->Name);
                array_push($CharacterClasses, $Character->ClassName);
                array_push($CharacterRoles1, $Character->Role1);
                array_push($CharacterRoles2, $Character->Role2);
            }
            
            $Out->pushValue("validUser", true);
            $Out->pushValue("id", $CurrentUser->UserId);
            $Out->pushValue("name", $CurrentUser->UserName);
            $Out->pushValue("characterIds", $CharacterIds);
            $Out->pushValue("characterNames", $CharacterNames);
            $Out->pushValue("characterClass", $CharacterClasses);
            $Out->pushValue("role1", $CharacterRoles1);
            $Out->pushValue("role2", $CharacterRoles2);            
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
            $Out->pushValue("validUser", false);
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