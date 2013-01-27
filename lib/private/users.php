<?php
    require_once dirname(__FILE__)."/connector.class.php";
    require_once dirname(__FILE__)."/../config/config.php";
    require_once dirname(__FILE__)."/bindings/native.php";

    require_once dirname(__FILE__)."/bindings/phpbb3.php";
    require_once dirname(__FILE__)."/bindings/eqdkp.php";
    require_once dirname(__FILE__)."/bindings/vbulletin3.php";
    require_once dirname(__FILE__)."/bindings/mybb.php";

    class UserProxy
    {
        private static $Instance = null;
        private static $StickyLifeTime = 604800; // 60 * 60 * 24 * 7; // 1 week
        private static $StickyCookieName = "ppx_raidplaner_sticky";

        private static $Bindings = array(
            "Default" => array( "Function" => "BindNativeUser", "Available" => true ),
            "PHPBB3"  => array( "Function" => "BindPHPBB3User", "Available" => PHPBB3_BINDING ),
            "EQDKP"   => array( "Function" => "BindEQDKPUser",  "Available" => EQDKP_BINDING ),
            "VB3"     => array( "Function" => "BindVB3User",    "Available" => VB3_BINDING ),
            "MYBB"    => array( "Function" => "BindMyBBUser",   "Available" => MYBB_BINDING )
        );

        private static $Salt = null;
        private static $CryptName = "rijndael-256";

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
                
                unset($_SESSION["User"]);
                unset($_SESSION["Calendar"]);

                $this->SetSessionCookie(null);                
                return; // ### return, logout ###
            }

            if (isset($_SESSION["User"]) && 
                isset($_SESSION["User"]["UserId"]))
            {
                // Session says user is still logged in
                // Check if session matches database
                
                if ( $this->CheckSessionCRC() )
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
                
                $LoginUser = array( "Login"     => $_REQUEST["user"],
                                    "Password"  => $_REQUEST["pass"],
                                    "cleartext" => true );
            }
            else if ( isset($_COOKIE[self::$StickyCookieName]) )
            {
                // Login via cookie
                // Reconstruct login data from cookie + database hash
                
                $Connector  = Connector::GetInstance();
                $CookieData = $this->GetSessionCookieData();
                               
                $UserSt = $Connector->prepare( "SELECT Hash FROM `".RP_TABLE_PREFIX."User` WHERE UserId = :UserId" );
                
                $UserSt->bindValue(":UserId", $CookieData["ID"], PDO::PARAM_INT );
                $UserSt->execute();
                
                if ($UserSt->rowcount() > 0)
                {
                    $UserData  = $UserSt->fetch( PDO::FETCH_ASSOC );                
                    $LoginData = $this->DecryptData($UserData["Hash"], $CookieData["InitVector"], $CookieData["Data"]);
                
                    $LoginUser = array( "Login"     => $LoginData["Login"],
                                        "Password"  => $LoginData["Password"],
                                        "cleartext" => false );
                }

                $UserSt->closeCursor();
            }
            
            // Check if login was requested (direct or indirect)
            // Process all available bindings in their order of registration 
            
            if ( $LoginUser != null )
            {
                foreach ( self::$Bindings as $Binding )
                {
                    if ( $Binding["Available"] && 
                         call_user_func($Binding["Function"], $LoginUser) )
                    {
                        // Logged in via binding
                        // Process sticky cookie request and clear salt value

                        if ( (isset($_REQUEST["sticky"]) && ($_REQUEST["sticky"] == "true")) ||
                             (isset($_COOKIE[self::$StickyCookieName])) )
                        {
                            // Sticky login is requested
                            
                            $data = array( "Login"    => $LoginUser["Login"],
                                           "Password" => $_SESSION["User"]["Password"] );

                            $cookieData = intval($_SESSION["User"]["UserId"]).",".implode(",",$this->EncryptData(self::$Salt, $data));
                            
                            $this->SetSessionCookie($cookieData);
                        }

                        self::$Salt = null; // do not store hash
                        return; // ### return, logged in ###
                    }
                }
            }

            // All checks failed -> logout
            
            unset($_SESSION["User"]);
            unset($_SESSION["Calendar"]);
        }

        // --------------------------------------------------------------------------------------------

        private function EncryptData( $Key, $Data )
        {
            $cryptDesc  = mcrypt_module_open( UserProxy::$CryptName, "", MCRYPT_MODE_CBC, "" );
            $initVector = mcrypt_create_iv( mcrypt_enc_get_iv_size($cryptDesc), MCRYPT_RAND );
            
            $cryptedData = mcrypt_encrypt(UserProxy::$CryptName, $Key, serialize($Data), MCRYPT_MODE_CBC, $initVector);
            mcrypt_module_close($cryptDesc);
            
            return Array(base64_encode($initVector), base64_encode($cryptedData));
        }

        // --------------------------------------------------------------------------------------------

        private function DecryptData( $Key, $InitVector, $Data )
        {
            $cryptDesc = mcrypt_module_open( UserProxy::$CryptName, "", MCRYPT_MODE_CBC, "" );
            
            $decryptedData = mcrypt_decrypt(UserProxy::$CryptName, $Key, base64_decode($Data), MCRYPT_MODE_CBC, base64_decode($InitVector));
            mcrypt_module_close($cryptDesc);
            
            return unserialize($decryptedData);
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

        private function GetSessionCookieData()
        {
            $packedData = explode(",", $_COOKIE[self::$StickyCookieName]);
            return Array( "ID" => $packedData[0], "InitVector" => $packedData[1], "Data" => $packedData[2] );
        }

        // --------------------------------------------------------------------------------------------

        private function CheckSessionCRC()
        {
            if (isset($_SESSION["User"]))
            {
                $Connector = Connector::GetInstance();
                $UserSt = $Connector->prepare("SELECT * FROM `".RP_TABLE_PREFIX."User` ".
                                              "WHERE UserId = :UserId LIMIT 1");

                $UserSt->bindValue(":UserId", $_SESSION["User"]["UserId"], PDO::PARAM_INT);
                $UserSt->execute();

                if ( $UserSt->rowCount() > 0 )
                {
                    $UserDataFromDb = $UserSt->fetch( PDO::FETCH_ASSOC );
                    $UserSt->closeCursor();

                    while ( $item = current($UserDataFromDb) )
                    {
                        $key = key( $UserDataFromDb );

                        if ( $key != "Hash" )
                        {
                            if ( !isset($_SESSION["User"][$key]) )
                                return false; // ### return, missing field ###
                            
                            if ( crc32($_SESSION["User"][$key]) != crc32($item) )
                                return false; // ### return, modified field ###
                        }

                        next( $UserDataFromDb );
                    }

                    return true; // ### return, matching CRC ###
                }
                
                $UserSt->closeCursor();
            }

            return false;
        }

        // --------------------------------------------------------------------------------------------

        public static function GetInstance()
        {
            if (self::$Instance == NULL)
                self::$Instance = new UserProxy();

            return self::$Instance;
        }

        // --------------------------------------------------------------------------------------------

        private static function GenerateSalt( $BaseValue )
        {
            $Salt = sha1( strval(microtime() + rand()) . $_SERVER["REMOTE_ADDR"] );
            self::$Salt = md5( $BaseValue.$Salt );
        }

        // --------------------------------------------------------------------------------------------

        public static function CreateUser( $Group, $ExternalUserId, $BindingName, $Login, $Password, $Salt=null )
        {
            $Connector = Connector::GetInstance();
            $UserSt = $Connector->prepare("SELECT UserId FROM `".RP_TABLE_PREFIX."User` ".
                                          "WHERE Login = :Login LIMIT 1");

            $UserSt->bindValue(":Login", strtolower($Login), PDO::PARAM_STR);

            if ( $UserSt->execute() && 
                 ($UserSt->rowCount() == 0) )
            {
                if ($Salt != null )
                    self::$Salt = $Salt;
                else
                    self::GenerateSalt( $Login );
                
                if ( $BindingName == "none" )
                {
                    $hashedPassword = HashNativePasswordWithSalt($Password, self::$Salt);
                }
                else
                {
                    $hashedPassword = $Password;
                }
                   
                $UserSt->closeCursor();
                $UserSt = $Connector->prepare("INSERT INTO `".RP_TABLE_PREFIX."User` (".
                                              "`Group`, ExternalId, ExternalBinding, Login, Password, Hash, Created) ".
                                              "VALUES (:Group, :ExternalUserId, :Binding, :Login, :Password, :Hash, FROM_UNIXTIME(:Created))");

                $UserSt->bindValue(":ExternalUserId", $ExternalUserId,    PDO::PARAM_INT);
                $UserSt->bindValue(":Login",          strtolower($Login), PDO::PARAM_STR);
                $UserSt->bindValue(":Password",       $hashedPassword,    PDO::PARAM_STR);
                $UserSt->bindValue(":Hash",           self::$Salt,        PDO::PARAM_STR);
                $UserSt->bindValue(":Group",          $Group,             PDO::PARAM_STR);
                $UserSt->bindValue(":Binding",        $BindingName,       PDO::PARAM_STR);
                $UserSt->bindValue(":Created",        time(),             PDO::PARAM_INT);

                $UserSt->execute();
                $UserSt->closeCursor();

                return $Connector->lastInsertId();
            }

            $UserSt->closeCursor();
            return false;
        }

        // --------------------------------------------------------------------------------------------

        public static function ChangePassword( $UserId, $NewPassword, $OldPassword )
        {
            $changeCurrentUser = ($UserId == $_SESSION["User"]["UserId"]);

            if ( !$changeCurrentUser && !ValidAdmin() )
                return false; // ### return, security requirements not met ###

            $Connector = Connector::GetInstance();
            $UserSt = $Connector->prepare("SELECT Login FROM `".RP_TABLE_PREFIX."User` ".
                                          "WHERE ExternalBinding = 'none' AND UserId = :UserId ".
                                          (($changeCurrentUser) ? "AND Password = :OldPass LIMIT 1" : "LIMIT 1") );
                                          
            $UserSt->bindValue( ":UserId", $UserId, PDO::PARAM_STR );
            
            if ($changeCurrentUser)
            {                
                $hashedOldPassword = HashNativePasswordForId($UserId, $OldPassword);
                $UserSt->bindValue( ":OldPass", $hashedOldPassword, PDO::PARAM_STR );
            }
            
            // Check if user with old password and id exists (password check and query login)

            if ( $UserSt->execute() && ($UserSt->rowCount() != 0) )
            {
                $userData = $UserSt->fetch( PDO::FETCH_ASSOC );

                self::GenerateSalt( $userData["Login"] );
                $hashedNewPassword = HashNativePasswordWithSalt($NewPassword, self::$Salt);

                $UserSt->closeCursor();
                $UserSt = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."User` SET Password = :Password, Hash = :Hash WHERE UserId = :UserId LIMIT 1" );

                $UserSt->bindValue(":UserId",   $UserId,            PDO::PARAM_INT);
                $UserSt->bindValue(":Password", $hashedNewPassword, PDO::PARAM_STR);
                $UserSt->bindValue(":Hash",     self::$Salt,        PDO::PARAM_STR);

                $UserSt->execute();
                $UserSt->closeCursor();

                // Update session to keep login valid

                if ( $changeCurrentUser )
                {
                    $_SESSION["User"]["Password"] = $NewPassword;
                }

                self::$Salt = null; // do not store salt
                return true; // ### return, password changed ###
            }

            $UserSt->closeCursor();
            return false;
        }

        // --------------------------------------------------------------------------------------------

        public static function CheckForBindingUpdate( $ExternalId, $Username, $Password, $Binding, $UpdateSession, $Salt=null )
        {
            if ( !isset($_SESSION["User"]) ||
                 ($_SESSION["User"]["Password"] != $Password) ||
                 ($_SESSION["User"]["Login"] != $Username) ||
                 (($Salt!=null) && ($Salt != self::$Salt)) )
            {        
                $Connector = Connector::GetInstance();
                $UserSt = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."User` SET ".
                                              "Password = :Password, Login = :Username, Hash = :Hash ".
                                              "WHERE ExternalId = :ExternalId AND ExternalBinding = :ExternalBinding LIMIT 1");
                                              
                if ($Salt != null )
                    self::$Salt = $Salt;
                else
                    self::GenerateSalt( $Username );
    
                $UserSt->bindValue(":ExternalId",      $ExternalId, PDO::PARAM_INT);
                $UserSt->bindValue(":ExternalBinding", $Binding,    PDO::PARAM_STR);
                $UserSt->bindValue(":Username",        $Username,   PDO::PARAM_STR);
                $UserSt->bindValue(":Password",        $Password,   PDO::PARAM_STR);
                $UserSt->bindValue(":Hash",            self::$Salt, PDO::PARAM_STR);
    
                $UserSt->execute();
    
                $Updated = $UserSt->rowCount() == 1;
                $UserSt->closeCursor();
    
                if ($Updated && $UpdateSession)
                {
                    $_SESSION["User"]["Password"] = $Password;
                    $_SESSION["User"]["Login"]    = $Username;
                }
    
                return $Updated;
            }
            
            return false;
        }

        // --------------------------------------------------------------------------------------------

        public static function ConvertCurrentUserToLocalBinding( $ClearTextPassword )
        {
            $Connector = Connector::GetInstance();
            $UserSt = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."User` SET ".
                                          "ExternalId = 0, ExternalBinding = \"none\", Password = :Password, Hash = :Hash ".
                                          "WHERE UserId = :UserId LIMIT 1");
                                          
            self::GenerateSalt( $_SESSION["User"]["Login"] );
            $hashedPassword = HashNativePasswordWithSalt($ClearTextPassword, self::$Salt);
            
            $UserSt->bindValue(":UserId", $_SESSION["User"]["UserId"], PDO::PARAM_INT);
            $UserSt->bindValue(":Password", $hashedPassword, PDO::PARAM_STR);
            $UserSt->bindValue(":Hash", self::$Salt, PDO::PARAM_STR);
            $UserSt->execute();

            $Updated = $UserSt->rowCount() == 1;
            $UserSt->closeCursor();

            if ( $Updated )
            {
                $_SESSION["User"]["ExternalId"] = 0;
                $_SESSION["User"]["ExternalBinding"] = "none";
                $_SESSION["User"]["Password"] = $hashedPassword;
            }
        }

        // --------------------------------------------------------------------------------------------

        private static function SetSessionVariables( $UserQuery )
        {
            $_SESSION["User"] = $UserQuery->fetch( PDO::FETCH_ASSOC );

            $_SESSION["User"]["Role1"] = array( $_SESSION["User"]["Role1"] );
            $_SESSION["User"]["Role2"] = array( $_SESSION["User"]["Role2"] );
            $_SESSION["User"]["CharacterId"] = array( $_SESSION["User"]["CharacterId"] );
            $_SESSION["User"]["CharacterName"] = array( $_SESSION["User"]["CharacterName"] );

            while ( $row = $UserQuery->fetch( PDO::FETCH_ASSOC ) )
            {
                array_push( $_SESSION["User"]["Role1"], $row["Role1"] );
                array_push( $_SESSION["User"]["Role2"], $row["Role2"] );
                array_push( $_SESSION["User"]["CharacterId"], $row["CharacterId"] );
                array_push( $_SESSION["User"]["CharacterName"], $row["CharacterName"] );
            }

            $UserSalt = $_SESSION["User"]["Hash"];
            unset( $_SESSION["User"]["Hash"] );

            return $UserSalt;
        }

        // --------------------------------------------------------------------------------------------


        public static function TryLoginUser( $Login, $HashedPassword, $BindingName )
        {
            $Connector = Connector::GetInstance();

            $UserSt = $Connector->prepare("SELECT ".RP_TABLE_PREFIX."User.*, ".RP_TABLE_PREFIX."Character.Name AS CharacterName, ".RP_TABLE_PREFIX."Character.Role1, ".RP_TABLE_PREFIX."Character.Role2, ".RP_TABLE_PREFIX."Character.CharacterId FROM `".RP_TABLE_PREFIX."User` ".
                                          "LEFT JOIN `".RP_TABLE_PREFIX."Character` USING (UserId) ".
                                          "WHERE Login = :Login AND Password = :Password AND ExternalBinding = '".$BindingName."' ".
                                          "ORDER BY Mainchar, ".RP_TABLE_PREFIX."Character.Name" );

            $UserSt->bindValue(":Login",    strtolower($Login), PDO::PARAM_STR);
            $UserSt->bindValue(":Password", $HashedPassword,    PDO::PARAM_STR);

            if (!$UserSt->execute() )
            {
                $ErrorInfo = $UserSt->errorInfo();
                echo "<error>".L("DatabaseError")."</error>";
                echo "<error>".$ErrorInfo[0]."</error>";
                echo "<error>".$ErrorInfo[2]."</error>";

                die();
            }

            $Success = $UserSt->rowCount() > 0;

            if ( $Success )
            {
                self::$Salt = UserProxy::SetSessionVariables( $UserSt );
            }

            $UserSt->closeCursor();

            return $Success;
        }

        // --------------------------------------------------------------------------------------------

        private function UpdateCharacters()
        {
            if ( isset($_SESSION["User"]) && ($_SESSION["User"]["Group"] != "none") )
            {
                $Connector = Connector::GetInstance();
                $CharacterSt = $Connector->prepare(    "SELECT * FROM `".RP_TABLE_PREFIX."Character` ".
                                                      "WHERE UserId = :UserId ".
                                                      "ORDER BY Mainchar, Name" );

                $CharacterSt->bindValue(":UserId", $_SESSION["User"]["UserId"], PDO::PARAM_INT);

                if ( $CharacterSt->execute() )
                {
                    $_SESSION["User"]["Role1"] = array();
                    $_SESSION["User"]["Role2"] = array();
                    $_SESSION["User"]["CharacterId"] = array();
                    $_SESSION["User"]["CharacterName"] = array();

                    while ( $row = $CharacterSt->fetch( PDO::FETCH_ASSOC ) )
                    {
                        array_push( $_SESSION["User"]["Role1"], $row["Role1"] );
                        array_push( $_SESSION["User"]["Role2"], $row["Role2"] );
                        array_push( $_SESSION["User"]["CharacterId"], $row["CharacterId"] );
                        array_push( $_SESSION["User"]["CharacterName"], $row["Name"] );
                    }
                }

                $CharacterSt->closeCursor();
            }
        }
    }

     // --------------------------------------------------------------------------------------------

    function RegisteredUser()
    {
        UserProxy::GetInstance();
        return isset($_SESSION["User"]);
    }

    // --------------------------------------------------------------------------------------------

    function ValidUser()
    {
        UserProxy::GetInstance();

        if (isset($_SESSION["User"]))
        {
            return ($_SESSION["User"]["Group"] != "none");
        }

        return false;
    }

    // --------------------------------------------------------------------------------------------

    function ValidRaidlead()
    {
        UserProxy::GetInstance();

        if (isset($_SESSION["User"]))
        {
            return (($_SESSION["User"]["Group"] == "raidlead") ||
                    ($_SESSION["User"]["Group"] == "admin"));
        }

        return false;
    }

    // --------------------------------------------------------------------------------------------

    function ValidAdmin()
    {
        UserProxy::GetInstance();

        if (isset($_SESSION["User"]))
        {
            return ($_SESSION["User"]["Group"] == "admin");
        }

        return false;
    }

    // --------------------------------------------------------------------------------------------

    function msgUserCreate( $Request )
    {
        if ( ALLOW_REGISTRATION )
        {
            if ( !UserProxy::CreateUser("none", 0, "none", $Request["name"], $Request["pass"]) )
            {
                echo "<error>".L("NameInUse")."</error>";
            }
        }
        else
        {
            echo "<error>".L("AccessDenied")."</error>";
        }
    }
?>