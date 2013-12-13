<?php
    include_once_exists(dirname(__FILE__)."/../../config/config.wp.php");
    
    array_push(PluginRegistry::$Classes, "WPBinding");
    
    class WPBinding
    {
        public static $HashMethod_md5r = "wp_md5r";
        
        private static $Itoa64 = "./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
        
        public $BindingName = "wp";
        private $mConnector = null;
    
        // -------------------------------------------------------------------------
        
        public function isActive()
        {
            return defined("WP_BINDING") && WP_BINDING;
        }
        
        // -------------------------------------------------------------------------
        
        public function getConfig()
        {
            return array(
                "database"  => defined("WP_DATABASE") ? WP_DATABASE : RP_DATABASE,
                "user"      => defined("WP_USER") ? WP_USER : RP_USER,
                "password"  => defined("WP_PASS") ? WP_PASS : RP_PASS,
                "prefix"    => defined("WP_TABLE_PREFIX") ? WP_TABLE_PREFIX : "wp_",
                "cookie"    => defined("WP_SECRET") ? WP_SECRET : "put LOGGED_IN_KEY + LOGGED_IN_SALT here",
                "members"   => defined("WP_RAIDLEAD_GROUPS") ? explode(",", WP_RAIDLEAD_GROUPS ) : [],
                "leads"     => defined("WP_MEMBER_GROUPS") ? explode(",", WP_MEMBER_GROUPS ) : [],
                "cookie_ex" => true,
                "groups"    => true
            );
        }
        
        // -------------------------------------------------------------------------
        
        public function isConfigWriteable()
        {
            $ConfigFolder = dirname(__FILE__)."/../../config";
            $ConfigFile   = $ConfigFolder."/config.wp.php";
            
            return (!file_exists($ConfigFile) && is_writable($ConfigFolder)) || is_writable($ConfigFile);
        }
        
        // -------------------------------------------------------------------------
        
        public function writeConfig($aEnable, $aDatabase, $aPrefix, $aUser, $aPass, $aMembers, $aLeads, $aCookieEx)
        {
            $Config = fopen( dirname(__FILE__)."/../../config/config.wp.php", "w+" );
            
            fwrite( $Config, "<?php\n");
            fwrite( $Config, "\tdefine(\"WP_BINDING\", ".(($aEnable) ? "true" : "false").");\n");
            
            if ( $aEnable )
            {
                fwrite( $Config, "\tdefine(\"WP_DATABASE\", \"".$aDatabase."\");\n");
                fwrite( $Config, "\tdefine(\"WP_USER\", \"".$aUser."\");\n");
                fwrite( $Config, "\tdefine(\"WP_PASS\", \"".$aPass."\");\n");
                fwrite( $Config, "\tdefine(\"WP_TABLE_PREFIX\", \"".$aPrefix."\");\n");
                fwrite( $Config, "\tdefine(\"WP_SECRET\", \"".$aCookieEx."\");\n");
                                             
                fwrite( $Config, "\tdefine(\"WP_MEMBER_GROUPS\", \"".implode( ",", $aMembers )."\");\n");
                fwrite( $Config, "\tdefine(\"WP_RAIDLEAD_GROUPS\", \"".implode( ",", $aLeads )."\");\n");
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
                $Groups = [];
                $OptionsQuery = $Connector->prepare( "SELECT option_value FROM `".$aPrefix."options` WHERE option_name = \"wp_user_roles\" LIMIT 1" );
                
                if ( $OptionsQuery->execute() )
                {
                    $Option = $OptionsQuery->fetch(PDO::FETCH_ASSOC);
                    $Roles = unserialize($Option["option_value"]);
                    
                    while (list($Role,$Options) = each($Roles))
                    {
                        array_push( $Groups, array(
                            "id"   => strtolower($Role), 
                            "name" => $Role)
                        );
                    }
                }
                else if ($aThrow)
                {
                    $Connector->throwError($OptionsQuery);
                }
                
                $OptionsQuery->closeCursor();
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
            $MemberGroups   = explode(",", WP_MEMBER_GROUPS );
            $RaidleadGroups = explode(",", WP_RAIDLEAD_GROUPS );
            
            $MetaSt = $this->mConnector->prepare("SELECT meta_key, meta_value ".
                                                 "FROM `".WP_TABLE_PREFIX."usermeta` ".
                                                 "WHERE user_id = :UserId AND meta_key = \"wp_capabilities\" LIMIT 1");
                                                 
            $MetaSt->bindValue(":UserId", $aUserId, PDO::PARAM_INT);
            $MetaSt->execute();
            
            while ($MetaData = $MetaSt->fetch(PDO::FETCH_ASSOC))
            {
                $Roles = array_keys(unserialize($MetaData["meta_value"]));
                
                foreach($Roles as $Role)
                {
                    if (in_array($Role, $RaidleadGroups))
                        return "raidlead";
                        
                    if (in_array($Role, $MemberGroups))
                        return "member";
                }
            }
            
            return "none";
        }
        
        // -------------------------------------------------------------------------
        
        private function generateInfo( $aUserData )
        {
            $Info = new UserInfo();
            $Info->UserId      = $aUserData["ID"];
            $Info->UserName    = $aUserData["user_login"];
            $Info->Password    = $aUserData["user_pass"];
            $Info->Salt        = self::extractSaltPart($aUserData["user_pass"]);
            $Info->SessionSalt = null;
            $Info->Group       = $this->getGroup($aUserData["ID"]);
            $Info->BindingName = $this->BindingName;
            $Info->PassBinding = $this->BindingName;
        
            return $Info;
        }
        
        // -------------------------------------------------------------------------
        
        public function getExternalLoginData()
        {
            $UserInfo = null;
            
            if (defined("WP_SECRET"))
            {
                if ($this->mConnector == null)
                    $this->mConnector = new Connector(SQL_HOST, WP_DATABASE, WP_USER, WP_PASS);
               
                // Fetch cookie name
                
                $CookieSt = $this->mConnector->prepare("SELECT option_value ".
                    "FROM `".WP_TABLE_PREFIX."options` ".
                    "WHERE option_name = 'siteurl' LIMIT 1");
                
                if ( $CookieSt->execute() && ($CookieSt->rowCount() > 0) )
                {
                    $ConfigData = $CookieSt->fetch( PDO::FETCH_ASSOC );
                    $CookieName = "wordpress_logged_in_".md5($ConfigData["option_value"]);
                    
                    // Fetch user info if seesion cookie is set
                        
                    if (isset($_COOKIE[$CookieName]))
                    {
                        list($UserName, $Expiration, $hmac) = explode("|", $_COOKIE[$CookieName]);
                    
                        $UserInfo = $this->getUserInfoByName($UserName);
                        
                        if ($UserInfo != null)
                        {
                            $PassFragment = substr($UserInfo->Password, 8, 4);
                            
                            $Key  = hash_hmac('md5', $UserName.$PassFragment.'|'.$Expiration, WP_SECRET);
                            $Hash = hash_hmac('md5', $UserName . '|' . $Expiration, $Key);
                            
                            if ($Hash != $hmac)
                                $UserInfo = null;
                        }    
                    }
                }
                
                $CookieSt->closeCursor();
            }
            
            return $UserInfo;
        }
        
        // -------------------------------------------------------------------------
        
        public function getUserInfoByName( $aUserName )
        {
            if ($this->mConnector == null)
                $this->mConnector = new Connector(SQL_HOST, WP_DATABASE, WP_USER, WP_PASS);
            
            $UserSt = $this->mConnector->prepare("SELECT ID, user_login, user_pass, user_status ".
                                                 "FROM `".WP_TABLE_PREFIX."users` ".
                                                 "WHERE LOWER(user_login) = :Login LIMIT 1");
                                          
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
                $this->mConnector = new Connector(SQL_HOST, WP_DATABASE, WP_USER, WP_PASS);
            
            $UserSt = $this->mConnector->prepare("SELECT ID, user_login, user_pass, user_status ".
                                                 "FROM `".WP_TABLE_PREFIX."users` ".
                                                 "WHERE ID = :UserId LIMIT 1");
                                          
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