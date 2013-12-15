<?php
    include_once_exists(dirname(__FILE__)."/../../config/config.eqdkp.php");
    
    array_push(PluginRegistry::$Classes, "EQDKPBinding");
    
    class EQDKPBinding
    {
        public static $HashMethod_sha512s = "eqdkp_sha512s";
        public static $HashMethod_sha512b = "eqdkp_sha512sb";
        public static $HashMethod_sha512d = "eqdkp_sha512sd";
        public static $HashMethod_sha512r = "eqdkp_sha512r";
        public static $HashMethod_md5     = "eqdkp_md5";
        
        private static $Itoa64 = "./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
        
        public $BindingName = "eqdkp";
        private $mConnector = null;
    
        // -------------------------------------------------------------------------
        
        public function isActive()
        {
            return defined("EQDKP_BINDING") && EQDKP_BINDING;
        }
        
        // -------------------------------------------------------------------------
        
        public function getConfig()
        {
            return array(
                "database"  => defined("EQDKP_DATABASE") ? EQDKP_DATABASE : RP_DATABASE,
                "user"      => defined("EQDKP_USER") ? EQDKP_USER : RP_USER,
                "password"  => defined("EQDKP_PASS") ? EQDKP_PASS : RP_PASS,
                "prefix"    => defined("EQDKP_TABLE_PREFIX") ? EQDKP_TABLE_PREFIX : "eqdkp_",
                "autologin" => defined("EQDKP_AUTOLOGIN") ? EQDKP_AUTOLOGIN : false,
                "members"   => array(),
                "leads"     => array(),
                "cookie_ex" => false,
                "groups"    => false
            );
        }
        
        // -------------------------------------------------------------------------
        
        public function queryExternalConfig($aRelativePath)
        {
            $ConfigPath = $_SERVER["DOCUMENT_ROOT"]."/".$aRelativePath."/config.php";
            if (!file_exists($ConfigPath))
            {
                Out::getInstance()->pushError($ConfigPath." ".L("NotExisting").".");
                return null;
            }
            
            @include_once($ConfigPath);
            
            if (!defined("EQDKP_INSTALLED"))
            {
                Out::getInstance()->pushError(L("NoValidConfig"));
                return null;
            }
            
            return array(
                "database"  => $dbname,
                "user"      => $dbuser,
                "password"  => $dbpass,
                "prefix"    => $table_prefix,
                "cookie"    => null
            );
        }
        
        // -------------------------------------------------------------------------
        
        public function isConfigWriteable()
        {
            $ConfigFolder = dirname(__FILE__)."/../../config";
            $ConfigFile   = $ConfigFolder."/config.eqdkp.php";
            
            return (!file_exists($ConfigFile) && is_writable($ConfigFolder)) || is_writable($ConfigFile);
        }
        
        // -------------------------------------------------------------------------
        
        public function writeConfig($aEnable, $aDatabase, $aPrefix, $aUser, $aPass, $aAutoLogin, $aMembers, $aLeads, $aCookieEx)
        {
            $Config = fopen( dirname(__FILE__)."/../../config/config.eqdkp.php", "w+" );
            
            fwrite( $Config, "<?php\n");
            fwrite( $Config, "\tdefine(\"EQDKP_BINDING\", ".(($aEnable) ? "true" : "false").");\n");
            
            if ( $aEnable )
            {
                fwrite( $Config, "\tdefine(\"EQDKP_DATABASE\", \"".$aDatabase."\");\n");
                fwrite( $Config, "\tdefine(\"EQDKP_USER\", \"".$aUser."\");\n");
                fwrite( $Config, "\tdefine(\"EQDKP_PASS\", \"".$aPass."\");\n");
                fwrite( $Config, "\tdefine(\"EQDKP_TABLE_PREFIX\", \"".$aPrefix."\");\n");
                fwrite( $Config, "\tdefine(\"EQDKP_AUTOLOGIN\", ".(($aAutoLogin) ? "true" : "false").");\n");
            }
            
            fwrite( $Config, "?>");    
            fclose( $Config );
        }
        
        // -------------------------------------------------------------------------
        
        public function getGroups($aDatabase, $aPrefix, $aUser, $aPass, $aThrow)
        {
            $Connector = new Connector(SQL_HOST, $aDatabase, $aUser, $aPass, $aThrow);
            
            if ($Connector != null)
            {
                // only test if we can read the user table
                $TestQuery = $Connector->prepare( "SELECT user_id FROM `".$aPrefix."users` LIMIT 1" );
                
                if ( !$TestQuery->execute() && $aThrow)
                {
                    $Connector->throwError($TestQuery);
                }
                
                $TestQuery->closeCursor(); 
           }
            
            return null;
        }
        
        // -------------------------------------------------------------------------
        
        public function getGroupsFromConfig()
        {
            $Config = $this->getConfig();
            return $this->getGroups($Config["database"], $Config["prefix"], $Config["user"], $Config["password"], false);
        }
        
        // -------------------------------------------------------------------------
        
        private function getGroup( $aUserId )
        {
            $UserRightsSt = $this->mConnector->prepare("SELECT ".EQDKP_TABLE_PREFIX."users.user_active, ".EQDKP_TABLE_PREFIX."auth_users.auth_setting,  ".EQDKP_TABLE_PREFIX."auth_options.auth_value ".
                                                      "FROM `".EQDKP_TABLE_PREFIX."users` ".
                                                      "LEFT JOIN `".EQDKP_TABLE_PREFIX."auth_users` USING(user_id) ".
                                                      "LEFT JOIN `".EQDKP_TABLE_PREFIX."auth_options` USING(auth_id) ".
                                                      "WHERE user_id = :UserId");
                                          
            $UserRightsSt->bindValue(":UserId", $aUserId, PDO::PARAM_INT);
            $UserRightsSt->execute();
            
            while ( $Right = $UserRightsSt->fetch(PDO::FETCH_ASSOC) )
            {
                if ( $Right["user_active"] == 0 )
                {
                    return "none"; // ### return, not active ###
                }
                
                if ( (($Right["auth_value"] == "a_raid_add") || ($Right["auth_value"] == "a_raid_upd"))
                     && ($Right["auth_setting"] == "Y") )
                {
                    return "raidlead"; // ### return, highest possible group ###
                }
            }
            
            return "member";
        }
        
        // -------------------------------------------------------------------------
        
        private function generateInfo( $aUserData )
        {
            $Info = new UserInfo();
            $Info->UserId      = $aUserData["user_id"];
            $Info->UserName    = $aUserData["username"];
            $Info->Password    = $aUserData["user_password"];
            $Info->Salt        = self::extractSaltPart($aUserData["user_password"]);
            $Info->SessionSalt = null;
            $Info->Group       = $this->getGroup($aUserData["user_id"]);
            $Info->BindingName = $this->BindingName;
            $Info->PassBinding = $this->BindingName;
            
            return $Info;
        }
        
        // -------------------------------------------------------------------------
        
        public function getExternalLoginData()
        {
            if (!defined("EQDKP_AUTOLOGIN") || !EQDKP_AUTOLOGIN)
                return null;
                
            if ($this->mConnector == null)
                $this->mConnector = new Connector(SQL_HOST, EQDKP_DATABASE, EQDKP_USER, EQDKP_PASS);
            
            $UserInfo = null;
            
            // Fetch cookie name
            
            $CookieSt = $this->mConnector->prepare("SELECT config_value ".
                "FROM `".EQDKP_TABLE_PREFIX."backup_cnf` ".
                "WHERE config_name = 'cookie_name' LIMIT 1");
                
            if ( $CookieSt->execute() && ($CookieSt->rowCount() > 0) )
            {
                $ConfigData = $CookieSt->fetch( PDO::FETCH_ASSOC );
                $CookieName = $ConfigData["config_value"]."_sid";
                
                // Fetch user info if seesion cookie is set
                    
                if (isset($_COOKIE[$CookieName]))
                {
                    $UserSt = $this->mConnector->prepare("SELECT session_user_id ".
                        "FROM `".EQDKP_TABLE_PREFIX."sessions` ".
                        "WHERE session_id = :sid LIMIT 1");
                                              
                    $UserSt->BindValue( ":sid", $_COOKIE[$CookieName], PDO::PARAM_STR );
                    
                    if ( $UserSt->execute() && ($UserSt->rowCount() > 0) )
                    {
                        // Get user info by external id
                        
                        $UserData = $UserSt->fetch( PDO::FETCH_ASSOC );
                        $UserId = $UserData["session_user_id"];                        
                        $UserInfo = $this->getUserInfoById($UserId);
                    }
                    
                    $UserSt->closeCursor();
                }
            }
            
            $CookieSt->closeCursor();
            return $UserInfo;
        }
        
        // -------------------------------------------------------------------------
        
        public function getUserInfoByName( $aUserName )
        {
            if ($this->mConnector == null)
                $this->mConnector = new Connector(SQL_HOST, EQDKP_DATABASE, EQDKP_USER, EQDKP_PASS);
            
            $UserSt = $this->mConnector->prepare("SELECT user_id, username, user_password ".
                                                "FROM `".EQDKP_TABLE_PREFIX."users` ".
                                                "WHERE LOWER(username) = :Login LIMIT 1");
                                              
            $UserSt->bindValue(":Login", strtolower($aUserName), PDO::PARAM_STR);
            $UserSt->execute();
            
            if ( $UserSt->execute() && ($UserSt->rowCount() > 0) )
            {
                $UserData = $UserSt->fetch(PDO::FETCH_ASSOC);
                $UserSt->closeCursor();
                
                return $this->generateInfo( $UserData );
            }
        
            $UserSt->closeCursor();
            return null;
        }
        
        // -------------------------------------------------------------------------
        
        public function getUserInfoById( $aUserId )
        {
            if ($this->mConnector == null)
                $this->mConnector = new Connector(SQL_HOST, EQDKP_DATABASE, EQDKP_USER, EQDKP_PASS);
            
            $UserSt = $this->mConnector->prepare("SELECT user_id, username, user_password ".
                                                "FROM `".EQDKP_TABLE_PREFIX."users` ".
                                                "WHERE user_id = :UserId LIMIT 1");
                                              
            $UserSt->bindValue(":UserId", $aUserId, PDO::PARAM_INT);
            $UserSt->execute();
            
            if ( $UserSt->execute() && ($UserSt->rowCount() > 0) )
            {
                $UserData = $UserSt->fetch( PDO::FETCH_ASSOC );
                $UserSt->closeCursor();
                
                return $this->generateInfo( $UserData );
            }
        
            $UserSt->closeCursor();
            return null;
        }
        
        // -------------------------------------------------------------------------
        
        private static function extractSaltPart( $aPassword )
        {
            $Length = strlen(substr($aPassword, 0, strpos($aPassword, ":")));
        
            if ((substr($aPassword, 0, 4) == '$2a$') && ($Length == 60))
            {
                $Parts = explode(":", $aPassword);                
                return substr($Parts[0],0,29).":".$Parts[1];
            }
            
            if (($aPassword[0] == '_') && ($Length == 20))
            {
                $Parts = explode(":", $aPassword);
                return substr($Parts[0],0,9).":".$Parts[1];
            }
            
            if ((substr($aPassword, 0, 3) == '$S$') && ($Length == 98))
            {
                $Count = strpos(self::$Itoa64, $aPassword[3]);
                $Salt2 = substr($aPassword, 4, 8);
                $Salt  = substr($aPassword, strpos($aPassword,":")+1);
                
                return $Count.":".$Salt.":".$Salt2;
            }
                
            if ($Length == 128)
            {
                $Parts = explode(":", $aPassword);
                return $Parts[1];
            }
            
            return "";
        }
        
        // -------------------------------------------------------------------------
        
        public function getMethodFromPass( $aPassword )
        {
            $Length = strlen(substr($aPassword, 0, strpos($aPassword, ":")));
            
            if ((substr($aPassword, 0, 4) == '$2a$') && ($Length == 60))
                return self::$HashMethod_sha512b;
            
            if (($Length > 0) && ($aPassword[0] == "_") && ($Length == 20))
                return self::$HashMethod_sha512d;
            
            if ((substr($aPassword, 0, 3) == '$S$') && ($Length == 98)) 
                return self::$HashMethod_sha512r;
                
            if ($Length == 128) 
                return self::$HashMethod_sha512s;
    
            return self::$HashMethod_md5;
        }
        
        // -------------------------------------------------------------------------
        
        private static function encode64( $aInput, $aCount )
        {
            $Output = '';
            $i = 0;
            
            do {
                $Value = ord($aInput[$i++]);
                $Output .= self::$Itoa64[$Value & 0x3f];
                
                if ($i < $aCount)
                   $Value |= ord($aInput[$i]) << 8;
                
                $Output .= self::$Itoa64[($Value >> 6) & 0x3f];
                
                if ($i++ >= $aCount)
                   break;
                
                if ($i < $aCount)
                   $Value |= ord($aInput[$i]) << 16;
                
                $Output .= self::$Itoa64[($Value >> 12) & 0x3f];
                
                if ($i++ >= $aCount)
                   break;
                
                $Output .= self::$Itoa64[($Value >> 18) & 0x3f];
            } while ($i < $aCount);
            
            return $Output;
        }
        
        // -------------------------------------------------------------------------
        
        public static function hash( $aPassword, $aSalt, $aMethod )
        {
            if ( ($aMethod == self::$HashMethod_sha512b) ||
                 ($aMethod == self::$HashMethod_sha512d) )
            {
                $Parts  = explode(":",$aSalt);
                $Config = $Parts[0];
                $Salt   = $Parts[1];
                
                $PreHash = hash('sha512', $Salt.$aPassword);                
                return crypt($PreHash, $Config).":".$Salt;
            }
            
            if ( $aMethod == self::$HashMethod_sha512r )
            {
                $Parts   = explode(":",$aSalt);
                $CountB2 = intval($Parts[0], 10);
                $Count   = 1 << $CountB2;
                $Salt    = $Parts[1];
                $Salt2   = $Parts[2];
                
                $PreHash = hash("sha512", $Salt.$aPassword);
                $Hash    = hash("sha512", $Salt2.$PreHash, true);
                
                do {
                    $Hash = hash("sha512", $Hash.$PreHash, true);
                } while(--$Count);
                
                return '$S$'.self::$Itoa64[$CountB2].$Salt2.self::encode64($Hash,strlen($Hash)).":".$Salt;
            }
            
            if ( $aMethod == self::$HashMethod_sha512s )
            {
                return hash("sha512", $aSalt.$aPassword);
            }
            
            return md5($aPassword);
        }
    }
?>