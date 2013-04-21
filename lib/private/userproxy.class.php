<?php
    require_once dirname(__FILE__)."/connector.class.php";
    require_once dirname(__FILE__)."/../config/config.php";
    require_once dirname(__FILE__)."/bindings/native.php";

    require_once dirname(__FILE__)."/bindings/phpbb3.php";
    require_once dirname(__FILE__)."/bindings/eqdkp.php";
    require_once dirname(__FILE__)."/bindings/vbulletin3.php";
    require_once dirname(__FILE__)."/bindings/mybb.php";
    require_once dirname(__FILE__)."/bindings/smf.php";
    require_once dirname(__FILE__)."/bindings/vanilla.php";
    
    // Helper class for external bindings, so we don't have to use string
    // based associative arrays.
    class UserInfo
    {
        public $UserId;
        public $UserName;
        public $Password;
        public $Salt;
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
        private static $Instance = null;
        private static $StickyLifeTime = 604800; // 60 * 60 * 24 * 7; // 1 week
        private static $StickyCookieName = "ppx_raidplaner_sticky";
        private static $CryptName = "rijndael-256";
        
        private static $Bindings;
    
        // --------------------------------------------------------------------------------------------

        public static function InitBindings()
        {
            self::$Bindings = array(
                "none"    => new NativeBinding("none"), // native has to be first
                "eqdkp"   => new EQDKPBinding("eqdkp"),
                "phpbb3"  => new PHPBB3Binding("phpbb3"),
                "vb3"     => new VB3Binding("vb3"),
                "smf"     => new SMFBinding("smf"),
                "mybb"    => new MYBBBinding("mybb"),
                "vanilla" => new VanillaBinding("vanilla")
            );
        }

        // --------------------------------------------------------------------------------------------

        public $UserId     = 0;
        public $UserGroup  = "none";
        public $Characters = array();

        // --------------------------------------------------------------------------------------------

        public function __construct()
        {
            assert(self::$Instance == NULL);

            session_name("ppx_raidplaner");

            ini_set("session.cookie_httponly", true);
            ini_set("session.hash_function", 1);

            session_start();

            if (isset($_REQUEST["logout"]))
            {
                // explicit "logout"
                
                $this->ResetUser();                
                $this->SetSessionCookie(null);
                
                return; // ### return, logout ###
            }

            if (isset($_SESSION["User"]))
            {
                // Session says user is still logged in
                // Check if session matches database
                
                if ( $this->CheckSessionCookie() )
                {
                    $this->UpdateCharacters();
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
            else if ( isset($_COOKIE[self::$StickyCookieName]) )
            {
                // Login via cookie
                // Reconstruct login data from cookie + database hash
                
                $Connector  = Connector::GetInstance();
                $CookieData = $this->GetSessionCookieData( $_COOKIE[self::$StickyCookieName] );
                               
                $UserSt = $Connector->prepare( "SELECT SessionKey FROM `".RP_TABLE_PREFIX."User` WHERE UserId = :UserId LIMIT 1" );
                
                $UserSt->bindValue(":UserId", $CookieData["UserId"], PDO::PARAM_INT );
                $UserSt->execute();
                
                if ($UserSt->rowcount() > 0)
                {
                    $UserData  = $UserSt->fetch( PDO::FETCH_ASSOC );                
                    $LoginData = self::DecryptData($UserData["SessionKey"], $CookieData["InitVector"], $CookieData["Data"]);
                
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
            
            if ( !$this->ProcessLoginRequest($LoginUser) )
            {
                // All checks failed -> logout            
                $this->ResetUser();
            }
        }

        // --------------------------------------------------------------------------------------------

        public static function GetInstance()
        {
            if (self::$Instance == NULL)
                self::$Instance = new UserProxy();

            return self::$Instance;
        }
        
        // --------------------------------------------------------------------------------------------

        private function ResetUser()
        {
            $this->UserGroup  = "none";
            $this->UserId     = 0;
            $this->Characters = array();
            
            unset($_SESSION["User"]);
            unset($_SESSION["Calendar"]);
        }
        
        // --------------------------------------------------------------------------------------------

        public static function GenerateKey128()
        {
            return md5(mcrypt_create_iv(2048, MCRYPT_RAND));
        }
        
        // --------------------------------------------------------------------------------------------
        
        public function InvalidateOneTimeKey( $UserId )
        {
            $OneTimeKey = self::GenerateKey128();
            $Connector  = Connector::GetInstance();
            
            $OtkSt = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."User` SET OneTimeKey = :Key ".
                                         "WHERE AND UserId = :UserId LIMIT 1" );
            
            $OtkSt->bindValue( ":Key",     $OneTimeKey, PDO::PARAM_STR );
            $OtkSt->bindValue( ":UserId",  $UserId,     PDO::PARAM_INT );
            $OtkSt->execute();
            $OtkSt->closeCursor();
        }

        // --------------------------------------------------------------------------------------------

        private static function EncryptData( $Key, $Data )
        {
            $cryptDesc  = mcrypt_module_open( self::$CryptName, "", MCRYPT_MODE_CBC, "" );
            $initVector = mcrypt_create_iv( mcrypt_enc_get_iv_size($cryptDesc), MCRYPT_RAND );
            
            $cryptedData = mcrypt_encrypt( self::$CryptName, $Key, serialize($Data), MCRYPT_MODE_CBC, $initVector );
            mcrypt_module_close($cryptDesc);
            
            return Array(base64_encode($initVector), base64_encode($cryptedData));
        }

        // --------------------------------------------------------------------------------------------

        private static function DecryptData( $Key, $InitVector, $Data )
        {
            $cryptDesc = mcrypt_module_open( self::$CryptName, "", MCRYPT_MODE_CBC, "" );
            
            $decryptedData = mcrypt_decrypt( self::$CryptName, $Key, base64_decode($Data), MCRYPT_MODE_CBC, base64_decode($InitVector) );
            mcrypt_module_close($cryptDesc);
            
            return @unserialize($decryptedData);
        }
        
        // --------------------------------------------------------------------------------------------

        private function ValidateCleartextPassword( $Password, $UserId, $BindingName )
        {
            $Binding  = self::$Bindings[$BindingName];
            $UserInfo = $Binding->GetUserInfoById($UserId);
            
            if ($UserInfo == null)
                return false;
            
            $Method = $Binding->GetMethodFromPass($UserInfo->Password);
            $Hashed = $Binding->Hash($Password, $UserInfo->Salt, $Method);
            
            return $UserInfo->Password == $Hashed;
        }
        
        // --------------------------------------------------------------------------------------------

        private function SetSessionCookie( $Data )
        {
            $serverName = "";
            $serverPath = "";
            $serverUsesHttps = isset($_SERVER["HTTPS"]) && ($_SERVER["HTTPS"] != "") && ($_SERVER["HTTPS"] != null) && ($_SERVER["HTTPS"] != "off");
            
            if ( $Data == null )
            {
                setcookie( self::$StickyCookieName, null, 0, $serverPath, $serverName, $serverUsesHttps, true );
            }
            else
            {
                setcookie( self::$StickyCookieName, $Data, time()+self::$StickyLifeTime, $serverPath, $serverName, $serverUsesHttps, true );
            }
        }
        
        // --------------------------------------------------------------------------------------------

        private function GetSessionCookieData( $CookieData )
        {
            $packedData = explode(",", $CookieData);
            return Array( "UserId" => $packedData[0], "InitVector" => $packedData[1], "Data" => $packedData[2] );
        }  
        
        // --------------------------------------------------------------------------------------------

        private function CheckSessionCookie()
        {
            $this->UserGroup = "none";
            
            if ( isset($_SESSION["User"]) )
            {
                // Get the cookie data from the current session.
                // This test is weaker than the cookie based authentication which does a full
                // login. This function is ment to be used at each messsage hub call and must
                // this be fast.
                
                $CookieData = $this->GetSessionCookieData( $_SESSION["User"] );
            
                $Connector = Connector::GetInstance();
                $UserSt = $Connector->prepare("SELECT Login, Password, `Group`, SessionKey FROM `".RP_TABLE_PREFIX."User` ".
                                              "WHERE UserId = :UserId LIMIT 1");

                $UserSt->bindValue(":UserId", $CookieData["UserId"], PDO::PARAM_INT);
                $UserSt->execute();
                
                if ( $UserSt->rowcount() > 0 )
                {
                    $UserData = $UserSt->fetch(PDO::FETCH_ASSOC);
                    $UserSt->closeCursor();
                    
                    $LoginData = self::DecryptData($UserData["SessionKey"], $CookieData["InitVector"], $CookieData["Data"]);
                    $this->UserGroup = $UserData["Group"];
                    $this->UserId    = $CookieData["UserId"];
                    
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
        
        private function GetUserCredentialsFromInfo( $UserInfo, $Binding )
        {
            if ( $UserInfo != null )
            {
                // UserInfo could be retrieved.
                // Generate a suitable, (mostly) random one-time-key,
                // store that key and return the required userInfo data.
                // By using the external id we prevent creating the same
                // user twice after an external rename.
                
                $OneTimeKey = self::GenerateKey128();
                $IsLocalInfo = $UserInfo->BindingName == "none";
                $IsNativeBinding = $Binding->BindingName == "none";
                
                $UpdateUserSt = $this->UpdateUserMirror( $UserInfo, $IsNativeBinding, $OneTimeKey );
                $UpdateUserSt->execute();
                
                if ( $UpdateUserSt->rowcount() == 0 )
                {
                    if ( $IsLocalInfo )
                        return null; // ### return, rare case guard (e.g. race condition) ###
                        
                    // Update did not succeed, so the user is not yet registered to
                    // the local database. Create a new local hook for that user.
                    
                    if ( self::CreateUser($UserInfo->Group, $UserInfo->UserId, $UserInfo->BindingName, 
                                          $UserName, $UserInfo->Password, $UserInfo->Salt) === false )
                    {
                        return null; // ### return, user could not be created ###
                    }
                    
                    // Set the one time key for the now existing user
                    
                    $UpdateUserSt->execute();
                }
                
                $UpdateUserSt->closeCursor();
                
                if (defined("USE_CLEARTEXT_PASSWORDS") && USE_CLEARTEXT_PASSWORDS)
                    $hashMethod = "cleartext";
                else
                    $hashMethod = self::$Bindings[$UserInfo->PassBinding]->GetMethodFromPass($UserInfo->Password);
                
                return Array( "salt"   => $UserInfo->Salt, 
                              "key"    => $OneTimeKey, 
                              "method" => $hashMethod );
                
                // ### return, found user ###
            }
            
            return null;
        }
        
        // --------------------------------------------------------------------------------------------

        public function GetUserCredentials( $UserName )
        {
            // Iterate all bindings and search for the given user
            
            foreach( self::$Bindings as $Binding )
            {
                if ( $Binding->IsActive() )
                {                    
                    $UserInfo    = $Binding->GetUserInfoByName($UserName);
                    $Credentials = $this->GetUserCredentialsFromInfo($UserInfo, $Binding);
                    
                    if ( $Credentials != null )
                        return $Credentials;
                }
            }
            
            // User not found
            
            return null;
        }
        
        // --------------------------------------------------------------------------------------------

        public function GetUserCredentialsById( $UserId )
        {
            // Iterate all bindings and search for the given user
            
            foreach( self::$Bindings as $Binding )
            {
                if ( $Binding->IsActive() )
                {                    
                    $UserInfo    = $Binding->GetUserInfoById($UserId);
                    $Credentials = $this->GetUserCredentialsFromInfo($UserInfo, $Binding);
                    
                    if ( $Credentials != null )
                        return $Credentials;
                }
            }
            
            // User not found
            
            return null;
        }
        
        // --------------------------------------------------------------------------------------------

        public function GetUserInfoById( $BindingName, $ExternalId )
        {
            $Binding = self::$Bindings[$BindingName];
            
            if ( $Binding->IsActive() )
            {                    
                return $Binding->GetUserInfoById($ExternalId);
            }
            
            return null;
        }
        
        // --------------------------------------------------------------------------------------------

        public function GetAllUserInfosById( $ExternalId )
        {
            $candidates = array();
            
            foreach( self::$Bindings as $Binding )
            {
                if ( $Binding->IsActive() )
                {                    
                    $info = $Binding->GetUserInfoById($ExternalId);
                    if ( $info != null )
                    {
                        $candidates[$Binding->BindingName] = $info;
                    }
                }
            }
            
            return $candidates;
        }
        
        // --------------------------------------------------------------------------------------------

        public function GetAllUserInfosByName( $UserName )
        {
            $candidates = array();
            
            foreach( self::$Bindings as $Binding )
            {
                if ( $Binding->IsActive() )
                {                    
                    $info = $Binding->GetUserInfoByName($UserName);
                    if ( $info != null )
                    {
                        $candidates[$Binding->BindingName] = $info;
                    }
                }
            }
            
            return $candidates;
        }
        
        // --------------------------------------------------------------------------------------------
        
        public function ValidateCredentials( $SignedPassword )
        {
            $Connector = Connector::GetInstance();
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
                    
                    return $this->ValidateCleartextPassword( $SignedPassword, $UserId, $UserData["ExternalBinding"] );
                }
                else
                {
                    $this->InvalidateOneTimeKey( $this->UserId );
                        
                    $HashedStoredPassword = hash("sha256", $UserData["OneTimeKey"].$UserData["Password"]);
                
                    if ( $SignedPassword == $HashedStoredPassword )
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
        
        private function ProcessLoginRequest( $LoginUser )
        {
            if ( $LoginUser == null )
                return false; // ### return, no data ###

            $Connector = Connector::GetInstance();
                
            $UserSt = $Connector->prepare( "SELECT * FROM `".RP_TABLE_PREFIX."User` WHERE Login = :Login LIMIT 1" );
            $UserSt->bindValue(":Login", $LoginUser["Login"], PDO::PARAM_STR );
            $UserSt->execute();
            
            if ($UserSt->rowcount() > 0)
            {
                $UserData = $UserSt->fetch(PDO::FETCH_ASSOC);
                $UserSt->closeCursor();
                                
                if ( $LoginUser["Cookie"] )
                {
                    // User logged in using the encrypted cookie data.
                    // In this case just check the password.
                    
                    $PasswordCheckOk = ($LoginUser["Password"] == $UserData["Password"]);
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
                    
                        
                        $PasswordCheckOk = $this->ValidateCleartextPassword( $LoginUser["Password"], $UserId, $UserData["ExternalBinding"] );
                    }
                    else
                    { 
                        // User logged in using one-time-key authentication
                        // In this case we get a HMAC based password and need 
                        // to reset the key
                        
                        $this->InvalidateOneTimeKey( $UserData["UserId"] );                        
                        $HashedStoredPassword = hash("sha256", $UserData["OneTimeKey"].$UserData["Password"]);                            
                        $PasswordCheckOk = ($LoginUser["Password"] == $HashedStoredPassword);
                    }
                }
                
                // Test and proceed ...                
                
                if ( $PasswordCheckOk )
                {
                    
                    // Login successfull. Prepare session.
                    // Update the current user entry to fix the external data binding (password, etc.)
                    // and create a new session key while at it.
                    
                    $SessionKey = $this->UpdateSession( $UserData );
                    
                    // Encrypt session cookie
                        
                    $data = array( "Login"    => $UserData["Login"],
                                   "Password" => $UserData["Password"],
                                   "Remote"   => $_SERVER["REMOTE_ADDR"] );

                    $cookieData = intval($UserData["UserId"]).",".implode(",",self::EncryptData($SessionKey, $data));
                    
                    // Now query and set the session variables
                    
                    $_SESSION["User"] = $cookieData;
                    $this->UserGroup  = $UserData["Group"];
                    $this->UserId     = $UserData["UserId"];
                    
                    $this->UpdateCharacters();
                    
                    // Process sticky cookie
                    // The sticky cookie stores the encrypted "credentials" part of the session
    
                    if ( (isset($_REQUEST["sticky"]) && ($_REQUEST["sticky"] == "true")) ||
                         (isset($_COOKIE[self::$StickyCookieName])) )
                    {
                        $this->SetSessionCookie($cookieData);
                    }
                    
                    return true; // ### return, logged in ###
                }
            }
            
            $UserSt->closeCursor();
            return false;
        }
        
        // --------------------------------------------------------------------------------------------

        public function UpdateCharacters()
        {
            if ( $this->UserGroup != "none" )
            {
                $Connector = Connector::GetInstance();
                $CharacterSt = $Connector->prepare( "SELECT * FROM `".RP_TABLE_PREFIX."Character` ".
                                                    "WHERE UserId = :UserId ".
                                                    "ORDER BY Mainchar, Name" );

                $CharacterSt->bindValue(":UserId", $this->UserId, PDO::PARAM_INT);
                $CharacterSt->execute();
                
                $this->Characters = array();

                while ( $row = $CharacterSt->fetch( PDO::FETCH_ASSOC ) )
                {
                    $character = new CharacterInfo();
                    
                    $character->CharacterId = $row["CharacterId"];
                    $character->Name        = $row["Name"];
                    $character->ClassName   = $row["Class"];
                    $character->IsMainChar  = $row["Mainchar"] == "true";
                    $character->Role1       = $row["Role1"];
                    $character->Role2       = $row["Role2"];
                    
                    array_push($this->Characters, $character);
                }
                
                $CharacterSt->closeCursor();
            }
        }

        // --------------------------------------------------------------------------------------------

        public static function CreateUser( $Group, $ExternalUserId, $BindingName, $Login, $HashedPassword, $Salt )
        {
            $Connector = Connector::GetInstance();
            
            // Pre-check:
            // Login must be unique
            
            $UserSt = $Connector->prepare("SELECT UserId FROM `".RP_TABLE_PREFIX."User` ".
                                          "WHERE Login = :Login LIMIT 1");

            $UserSt->bindValue(":Login", strtolower($Login), PDO::PARAM_STR);
            $UserSt->execute();
            
            if ( $UserSt->rowcount() == 0 )
            {
                // User does not exist, so we can create one
                               
                $UserSt->closeCursor();
                $UserSt = $Connector->prepare("INSERT INTO `".RP_TABLE_PREFIX."User` ".
                                              "(`Group`, ExternalId, ExternalBinding, BindingActive, Login, Password, Salt, Created, OneTimeKey, SessionKey) ".
                                              "VALUES (:Group, :ExternalUserId, :Binding, :Active, :Login, :Password, :Salt, FROM_UNIXTIME(:Created), '', '')");
											  
				$Active = ($BindingName != "none") ? "true" : "false";

                $UserSt->bindValue(":Group",          $Group,               PDO::PARAM_STR);
                $UserSt->bindValue(":ExternalUserId", $ExternalUserId,      PDO::PARAM_INT);
                $UserSt->bindValue(":Binding",        $BindingName,         PDO::PARAM_STR);
                $UserSt->bindValue(":Active",         $Active, 				PDO::PARAM_STR);
                $UserSt->bindValue(":Login",          strtolower($Login),   PDO::PARAM_STR);
                $UserSt->bindValue(":Password",       $HashedPassword,      PDO::PARAM_STR);
                $UserSt->bindValue(":Salt",           $Salt,                PDO::PARAM_STR);
                $UserSt->bindValue(":Created",        time(),               PDO::PARAM_INT);

                if (!$UserSt->execute())
                    postErrorMessage($UserSt);
                $UserSt->closeCursor();

                return $Connector->lastInsertId(); // ### return, inserted ###
            }

            $UserSt->closeCursor();
            return false;
        }
        
        // --------------------------------------------------------------------------------------------
        
        public function UpdateUserMirror( &$UserInfo, $IsStoredLocally, $Key )
        {   
            $Connector = Connector::GetInstance();
                 
            if ($UserInfo->BindingName == "none")
            {
                // Local users don't change externally, so just update the key
                
                $MirrorSt = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."User` SET OneTimeKey = :Key ".
                                                "WHERE UserId = :UserId LIMIT 1" );
                                                
                $MirrorSt->bindValue( ":Key",    $Key,              PDO::PARAM_STR );
                $MirrorSt->bindValue( ":UserId", $UserInfo->UserId, PDO::PARAM_INT );
            }
            else
            {
                if ( $IsStoredLocally )
                {
                    $ExternalInfo = self::$Bindings[$UserInfo->PassBinding]->GetUserInfoById($UserInfo->UserId);
                    if ( $ExternalInfo != null )
                        $UserInfo = $ExternalInfo;
                }
                
                // Local users may update externally, so sync the credentials
                         
                $MirrorSt = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."User` SET ".
                                                "Login = :Login, Password = :Password, `Group` = :Group, Salt = :Salt, OneTimeKey = :Key ".
                                                "WHERE ExternalBinding = :Binding AND ExternalId = :UserId LIMIT 1" );
            
                $MirrorSt->bindValue( ":Login",    $UserInfo->UserName,    PDO::PARAM_STR );
                $MirrorSt->bindValue( ":Password", $UserInfo->Password,    PDO::PARAM_STR );
                $MirrorSt->bindValue( ":Group",    $UserInfo->Group,       PDO::PARAM_STR );
                $MirrorSt->bindValue( ":Salt",     $UserInfo->Salt,        PDO::PARAM_STR );
                $MirrorSt->bindValue( ":Key",      $Key,                   PDO::PARAM_STR );
                $MirrorSt->bindValue( ":Binding",  $UserInfo->BindingName, PDO::PARAM_STR );
                $MirrorSt->bindValue( ":UserId",   $UserInfo->UserId,      PDO::PARAM_INT );
            }                     
            
            return $MirrorSt;
        }
        
        // --------------------------------------------------------------------------------------------
        
        private function UpdateSession( &$UserData )
        {
            $SessionKey = self::GenerateKey128();  
            $Connector = Connector::GetInstance();      
            
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
                $ExternalUserInfo = self::$Bindings[$UserData["ExternalBinding"]]->GetUserInfoById($UserData["ExternalId"]);
            
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

        public static function ChangePassword( $UserId, $HashedPassword, $Salt )
        {
            $IsCurrentUser = self::GetInstance()->UserId == $UserId;
            
            if ( !$IsCurrentUser && !ValidAdmin() )
                return false; // ### return, security check failed ###
                
            // Change password to new values.
            // Only accounts with an inactive binding may be changed.
            
            $SessionKey = self::GenerateKey128();
            
            $Connector = Connector::GetInstance();
            $UpdateSt  = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."User` SET ".
                                             "ExternalBinding = 'none', Password = :Password, Salt = :Salt, SessionKey = :Key ".
                                             "WHERE UserId = :UserId AND (BindingActive='false' OR ExternalBinding='none') LIMIT 1");
                                            
            $UpdateSt->bindValue(":UserId",   $UserId,         PDO::PARAM_INT);
            $UpdateSt->bindValue(":Password", $HashedPassword, PDO::PARAM_STR);
            $UpdateSt->bindValue(":Salt",     $Salt,           PDO::PARAM_STR);
            $UpdateSt->bindValue(":Key",      $SessionKey,     PDO::PARAM_STR);

            $Success = $UpdateSt->execute();
            $UpdateSt->closeCursor();
            
            if ( $Success && $IsCurrentUser )
            {
                // Fetch login name for user (might not be current)
                
                $LoginSt = $Connector->prepare("SELECT Login FROM `".RP_TABLE_PREFIX."User` WHERE UserId = :UserId");
                $LoginSt->bindValue(":UserId", $UserId, PDO::PARAM_INT);
                $LoginSt->execute();
                
                $UserData = $LoginSt->fetch(PDO::FETCH_ASSOC);
                $LoginSt->closeCursor();
                
                // update cookie data
                // both session and sticky cookie have to be updated
                
                $data = array( "Login"    => $UserData["Login"],
                               "Password" => $HashedPassword,
                               "Remote"   => $_SERVER["REMOTE_ADDR"] );

                $CookieData = intval($UserId).",".implode(",",self::EncryptData($SessionKey, $data));
                
                $_SESSION["User"] = $CookieData;
                    
                if ( isset($_COOKIE[self::$StickyCookieName]) )
                    $this->SetSessionCookie($CookieData);
            }

            return $Success;
        }
    }
    
    // --------------------------------------------------------------------------------------------

    UserProxy::InitBindings();

    // --------------------------------------------------------------------------------------------

    function RegisteredUser()
    {
        UserProxy::GetInstance();
        return isset($_SESSION["User"]);
    }

    // --------------------------------------------------------------------------------------------
    
    function ValidUser()
    {
        $Group = UserProxy::GetInstance()->UserGroup;
        return isset($_SESSION["User"]) && ($Group != "none");
    }

    // --------------------------------------------------------------------------------------------

    function ValidRaidlead()
    {
        $Group = UserProxy::GetInstance()->UserGroup;
        return isset($_SESSION["User"]) && (($Group == "raidlead") || ($Group == "admin"));
    }

    // --------------------------------------------------------------------------------------------

    function ValidAdmin()
    {
        $Group = UserProxy::GetInstance()->UserGroup;
        return isset($_SESSION["User"]) && ($Group == "admin");
    }
?>