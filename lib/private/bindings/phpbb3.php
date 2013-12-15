<?php
    include_once_exists(dirname(__FILE__)."/../../config/config.phpbb3.php");
    
    array_push(PluginRegistry::$Classes, "PHPBB3Binding");
    
    class PHPBB3Binding
    {
        public static $HashMethod_md5r = "phpbb3_md5r";
        public static $HashMethod_md5  = "phpbb3_md5";
        
        private static $Itoa64 = "./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
        
        public $BindingName = "phpbb3";
        private $mConnector = null;
    
        // -------------------------------------------------------------------------
        
        public function isActive()
        {
            return defined("PHPBB3_BINDING") && PHPBB3_BINDING;
        }
        
        // -------------------------------------------------------------------------
        
        public function getConfig()
        {
            return array(
                "database"  => defined("PHPBB3_DATABASE") ? PHPBB3_DATABASE : RP_DATABASE,
                "user"      => defined("PHPBB3_USER") ? PHPBB3_USER : RP_USER,
                "password"  => defined("PHPBB3_PASS") ? PHPBB3_PASS : RP_PASS,
                "prefix"    => defined("PHPBB3_TABLE_PREFIX") ? PHPBB3_TABLE_PREFIX : "phpbb_",
                "autologin" => defined("PHPBB3_AUTOLOGIN") ? PHPBB3_AUTOLOGIN : false,
                "members"   => defined("PHPBB3_RAIDLEAD_GROUPS") ? explode(",", PHPBB3_RAIDLEAD_GROUPS ) : array(),
                "leads"     => defined("PHPBB3_MEMBER_GROUPS") ? explode(",", PHPBB3_MEMBER_GROUPS ) : array(),
                "cookie_ex" => false,
                "groups"    => true
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
            
            if (!defined("PHPBB_INSTALLED"))
            {
                Out::getInstance()->pushError(L("NoValidConfig"));
                return null;
            }
            
            return array(
                "database"  => $dbname,
                "user"      => $dbuser,
                "password"  => $dbpasswd,
                "prefix"    => $table_prefix,
                "cookie"    => null
            );
        }
        
        // -------------------------------------------------------------------------
        
        public function isConfigWriteable()
        {
            $ConfigFolder = dirname(__FILE__)."/../../config";
            $ConfigFile   = $ConfigFolder."/config.phpbb3.php";
            
            return (!file_exists($ConfigFile) && is_writable($ConfigFolder)) || is_writable($ConfigFile);
        }
        
        // -------------------------------------------------------------------------
        
        public function writeConfig($aEnable, $aDatabase, $aPrefix, $aUser, $aPass, $aAutoLogin, $aMembers, $aLeads, $aCookieEx)
        {
            $Config = fopen( dirname(__FILE__)."/../../config/config.phpbb3.php", "w+" );
            
            fwrite( $Config, "<?php\n");
            fwrite( $Config, "\tdefine(\"PHPBB3_BINDING\", ".(($aEnable) ? "true" : "false").");\n");
            
            if ( $aEnable )
            {
                fwrite( $Config, "\tdefine(\"PHPBB3_DATABASE\", \"".$aDatabase."\");\n");
                fwrite( $Config, "\tdefine(\"PHPBB3_USER\", \"".$aUser."\");\n");
                fwrite( $Config, "\tdefine(\"PHPBB3_PASS\", \"".$aPass."\");\n");
                fwrite( $Config, "\tdefine(\"PHPBB3_TABLE_PREFIX\", \"".$aPrefix."\");\n");
                fwrite( $Config, "\tdefine(\"PHPBB3_AUTOLOGIN\", ".(($aAutoLogin) ? "true" : "false").");\n");
                                             
                fwrite( $Config, "\tdefine(\"PHPBB3_MEMBER_GROUPS\", \"".implode( ",", $aMembers )."\");\n");
                fwrite( $Config, "\tdefine(\"PHPBB3_RAIDLEAD_GROUPS\", \"".implode( ",", $aLeads )."\");\n");
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
                $GroupQuery = $Connector->prepare( "SELECT group_id, group_name FROM `".$aPrefix."groups` ORDER BY group_name" );
                $Groups = array();
                
                if ( $GroupQuery->execute() )
                {
                    while ( $Group = $GroupQuery->fetch(PDO::FETCH_ASSOC) )
                    {
                        array_push( $Groups, array(
                            "id"   => $Group["group_id"], 
                            "name" => $Group["group_name"])
                        );
                    }
                }
                else if ($aThrow)
                {
                    $Connector->throwError($GroupQuery);
                }
                
                $GroupQuery->closeCursor();
                return $Groups;
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
            $DefaultGroup = "none";
            $MemberGroups   = explode(",", PHPBB3_MEMBER_GROUPS );
            $RaidleadGroups = explode(",", PHPBB3_RAIDLEAD_GROUPS );
            
            $GroupSt = $this->mConnector->prepare("SELECT user_type, `".PHPBB3_TABLE_PREFIX."user_group`.group_id, ban_start, ban_end ".
                                                 "FROM `".PHPBB3_TABLE_PREFIX."users` ".
                                                 "LEFT JOIN `".PHPBB3_TABLE_PREFIX."user_group` USING(user_id) ".
                                                 "LEFT JOIN `".PHPBB3_TABLE_PREFIX."banlist` ON user_id = ban_userid ".
                                                 "WHERE user_id = :UserId");
                                                 
            $GroupSt->bindValue(":UserId", $aUserId, PDO::PARAM_INT);
            $GroupSt->execute();
            
            while ($Group = $GroupSt->fetch(PDO::FETCH_ASSOC))
            {
                if ( ($Group["user_type"] == 1) || 
                     ($Group["user_type"] == 2) )
                {
                    // 1 equals "inactive"
                    // 2 equals "ignore"
                    return "none"; // ### return, disabled ###
                }
                
                if ($Group["ban_start"] > 0)
                {
                    $CurrentTime = time();
                    if ( ($Group["ban_start"] < $CurrentTime) &&
                         (($Group["ban_end"] == 0) || ($Group["ban_end"] > $CurrentTime)) )
                    {
                        return "none"; // ### return, banned ###
                    }
                }
            
                if ( in_array($Group["group_id"], $MemberGroups) )
                {
                    $DefaultGroup = "member";
                }
                   
                if ( in_array($Group["group_id"], $RaidleadGroups) )
                {
                    return "raidlead"; // ### return, highest possible group ###
                }
            }

            return $DefaultGroup;
        }
        
        // -------------------------------------------------------------------------
        
        private function generateInfo( $aUserData )
        {
            $Info = new UserInfo();
            $Info->UserId      = $aUserData["user_id"];
            $Info->UserName    = $aUserData["username_clean"];
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
            if (!defined("PHPBB3_AUTOLOGIN") || !PHPBB3_AUTOLOGIN)
                return null;
                
            if ($this->mConnector == null)
                $this->mConnector = new Connector(SQL_HOST, PHPBB3_DATABASE, PHPBB3_USER, PHPBB3_PASS);
            
            $UserInfo = null;
            
            // Fetch cookie name
            
            $CookieSt = $this->mConnector->prepare("SELECT config_value ".
                "FROM `".PHPBB3_TABLE_PREFIX."config` ".
                "WHERE config_name = 'cookie_name' LIMIT 1");
            
            if ( $CookieSt->execute() && ($CookieSt->rowCount() > 0) )
            {
                $ConfigData = $CookieSt->fetch( PDO::FETCH_ASSOC );
                $CookieName = $ConfigData["config_value"]."_sid";
                
                // Fetch user info if seesion cookie is set
                    
                if (isset($_COOKIE[$CookieName]))
                {
                    $UserSt = $this->mConnector->prepare("SELECT session_user_id ".
                        "FROM `".PHPBB3_TABLE_PREFIX."sessions` ".
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
                $this->mConnector = new Connector(SQL_HOST, PHPBB3_DATABASE, PHPBB3_USER, PHPBB3_PASS);
            
            $UserSt = $this->mConnector->prepare("SELECT user_id, username_clean, user_password ".
                                                 "FROM `".PHPBB3_TABLE_PREFIX."users` ".
                                                 "WHERE LOWER(username_clean) = :Login LIMIT 1");
                                          
            $UserSt->BindValue( ":Login", strtolower($aUserName), PDO::PARAM_STR );
            
            if ( $UserSt->execute() && ($UserSt->rowCount() > 0) )
            {
                $UserData = $UserSt->fetch( PDO::FETCH_ASSOC );
                $UserSt->closeCursor();
                
                return $this->generateInfo($UserData);
            }
        
            $UserSt->closeCursor();
            return null;
        }
        
        // -------------------------------------------------------------------------
        
        public function getUserInfoById( $aUserId )
        {
            if ($this->mConnector == null)
                $this->mConnector = new Connector(SQL_HOST, PHPBB3_DATABASE, PHPBB3_USER, PHPBB3_PASS);
            
            $UserSt = $this->mConnector->prepare("SELECT user_id, username_clean, user_password ".
                                                 "FROM `".PHPBB3_TABLE_PREFIX."users` ".
                                                 "WHERE user_id = :UserId LIMIT 1");
                                          
            $UserSt->BindValue( ":UserId", $aUserId, PDO::PARAM_INT );
        
            if ( $UserSt->execute() && ($UserSt->rowCount() > 0) )
            {
                $UserData = $UserSt->fetch( PDO::FETCH_ASSOC );
                $UserSt->closeCursor();
                
                return $this->generateInfo($UserData);
            }
        
            $UserSt->closeCursor();
            return null;
        }
        
        // -------------------------------------------------------------------------
        
        private static function extractSaltPart( $aPassword )
        {
            if (strlen($aPassword) == 34)
            {
                $Count = strpos(self::$Itoa64, $aPassword[3]);
                $Salt = substr($aPassword, 4, 8);
                
                return $Count.":".$Salt;
            }
            
            return "";
        }
        
        // -------------------------------------------------------------------------
        
        public function getMethodFromPass( $aPassword )
        {
            if (strlen($aPassword) == 34)
                return self::$HashMethod_md5r;
                
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
                {
                   $Value |= ord($aInput[$i]) << 8;
                }
                
                $Output .= self::$Itoa64[($Value >> 6) & 0x3f];
                
                if ($i++ >= $aCount)
                {
                   break;
                }
                
                if ($i < $aCount)
                {
                   $Value |= ord($aInput[$i]) << 16;
                }
                
                $Output .= self::$Itoa64[($Value >> 12) & 0x3f];
                
                if ($i++ >= $aCount)
                {
                   break;
                }
                
                $Output .= self::$Itoa64[($Value >> 18) & 0x3f];
            } while ($i < $aCount);
            
            return $Output;
        }
        
        // -------------------------------------------------------------------------
        
        public static function hash( $aPassword, $aSalt, $aMethod )
        {
            if ($aMethod == self::$HashMethod_md5 )
            {
                return md5($aPassword);
            }
            
            $Parts   = explode(":",$aSalt);
            $CountB2 = intval($Parts[0],10);
            $Count   = 1 << $CountB2;
            $Salt    = $Parts[1];
            
            $Hash = md5($Salt.$aPassword, true);
            
            do {
                $Hash = md5($Hash.$aPassword, true);
            } while (--$Count);
            
            return '$H$'.self::$Itoa64[$CountB2].$Salt.self::encode64($Hash,16);
        }
    }
?>