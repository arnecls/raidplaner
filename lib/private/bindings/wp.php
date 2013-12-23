<?php
    include_once_exists(dirname(__FILE__)."/../../config/config.wp.php");
    
    array_push(PluginRegistry::$Classes, "WPBinding");
    
    class WPBinding
    {
        private static $Itoa64 = "./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
        private static $BindingName = "wp";
        
        public static $HashMethod_md5r = "wp_md5r";
        
        // -------------------------------------------------------------------------
        
        public function getName()
        {
            return self::$BindingName;
        }
        
        // -------------------------------------------------------------------------
        
        public function getConfig()
        {
            $Config = new BindingConfig();
            
            $Config->Database         = defined("WP_DATABASE") ? WP_DATABASE : RP_DATABASE;
            $Config->User             = defined("WP_USER") ? WP_USER : RP_USER;
            $Config->Password         = defined("WP_PASS") ? WP_PASS : RP_PASS;
            $Config->Prefix           = defined("WP_TABLE_PREFIX") ? WP_TABLE_PREFIX : "wp_";
            $Config->AutoLoginEnabled = defined("WP_AUTOLOGIN") ? WP_AUTOLOGIN : false;
            $Config->CookieData       = defined("WP_SECRET") ? WP_SECRET : "";
            $Config->Members          = defined("WP_RAIDLEAD_GROUPS") ? explode(",", WP_RAIDLEAD_GROUPS ) : array();
            $Config->Raidleads        = defined("WP_MEMBER_GROUPS") ? explode(",", WP_MEMBER_GROUPS ) : array();
            $Config->HasCookieConfig  = true;
            $Config->HasGroupConfig   = true;
            
            return $Config;
        }
        
        // -------------------------------------------------------------------------
        
        public function getExternalConfig($aRelativePath)
        {
            $Out = Out::getInstance();
            $ConfigPath = $_SERVER["DOCUMENT_ROOT"]."/".$aRelativePath."/wp-config.php";
            if (!file_exists($ConfigPath))
            {
                $Out->pushError($ConfigPath." ".L("NotExisting").".");
                return null;
            }
            
            define("SHORTINIT", true);
            @include_once($ConfigPath);
            
            if (!isset($table_prefix))
            {
                $Out->pushError(L("NoValidConfig"));
                return null;
            }
            
            return array(
                "database"  => DB_NAME,
                "user"      => DB_USER,
                "password"  => DB_PASSWORD,
                "prefix"    => $table_prefix,
                "cookie"    => LOGGED_IN_KEY.LOGGED_IN_SALT
            );
        }
        
        // -------------------------------------------------------------------------
        
        public function writeConfig($aEnable, $aDatabase, $aPrefix, $aUser, $aPass, $aAutoLogin, $aPostTo, $aPostAs, $aMembers, $aLeads, $aCookieEx)
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
                fwrite( $Config, "\tdefine(\"WP_AUTOLOGIN\", ".(($aAutoLogin) ? "true" : "false").");\n");
                                             
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
                $OptionsQuery = $Connector->prepare( "SELECT option_value FROM `".$aPrefix."options` WHERE option_name = \"wp_user_roles\" LIMIT 1" );
                $Option = $OptionsQuery, $aThrow->fetchFirst();
                
                $Groups = array();
                $Roles = unserialize($Option["option_value"]);
                
                while (list($Role,$Options) = each($Roles))
                {
                    array_push( $Groups, array(
                        "id"   => strtolower($Role), 
                        "name" => $Role)
                    );
                }
                
                return $Groups;
            }
            
            return null;
        }
        
        // -------------------------------------------------------------------------
        
        public function getForums($aDatabase, $aPrefix, $aUser, $aPass, $aThrow)
        {
            return null;    
        }
        
        // -------------------------------------------------------------------------
        
        public function getUsers($aDatabase, $aPrefix, $aUser, $aPass, $aThrow)
        {
            return null;    
        }
                
        // -------------------------------------------------------------------------
        
        private function getGroup( $aUserId )
        {
            $Connector      = $this->getConnector();
            $AssigedGroup   = "none";
            $MemberGroups   = explode(",", WP_MEMBER_GROUPS );
            $RaidleadGroups = explode(",", WP_RAIDLEAD_GROUPS );
            
            $MetaQuery = $Connector->prepare("SELECT meta_key, meta_value ".
                                          "FROM `".WP_TABLE_PREFIX."usermeta` ".
                                          "WHERE user_id = :UserId AND meta_key = \"wp_capabilities\" LIMIT 1");
                                                 
            $MetaQuery->bindValue(":UserId", $aUserId, PDO::PARAM_INT);
            
            $MetaQuery->loop(function($MetaData) use (&$AssigedGroup)
            {
                $Roles = array_keys(unserialize($MetaData["meta_value"]));
                
                foreach($Roles as $Role)
                {
                    if (in_array($Role, $RaidleadGroups))
                    {
                        $AssigedGroup = "raidlead";
                        return false;
                    }
                       
                    if (in_array($Role, $MemberGroups))
                        $AssigedGroup = "member";
                }
            });
            
            return $AssigedGroup;
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
            if (!defined("WP_AUTOLOGIN") || !WP_AUTOLOGIN)
                return null;
                
            $UserInfo = null;
            
            if (defined("WP_SECRET"))
            {
                $Connector = $this->getConnector();
               
                // Fetch cookie name
                
                $ConfigQuery = $Connector->prepare("SELECT option_value ".
                                                "FROM `".WP_TABLE_PREFIX."options` ".
                                                "WHERE option_name = 'siteurl' LIMIT 1");
                                                
                $ConfigData = $ConfigQuery->fetchFirst();
                
                if ( $ConfigData != null )
                {
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
            }
            
            return $UserInfo;
        }
        
        // -------------------------------------------------------------------------
        
        public function getUserInfoByName( $aUserName )
        {
            $Connector = $this->getConnector();
            $UserQuery = $Connector->prepare("SELECT ID, user_login, user_pass, user_status ".
                                          "FROM `".WP_TABLE_PREFIX."users` ".
                                          "WHERE LOWER(user_login) = :Login LIMIT 1");
                                          
            $UserQuery->BindValue( ":Login", strtolower($aUserName), PDO::PARAM_STR );
            $UserData = $UserQuery->fetchFirst();
            
            return ($UserData != null)
                ? $this->generateInfo($UserData)
                : null;
        }
        
        // -------------------------------------------------------------------------
        
        public function getUserInfoById( $aUserId )
        {
            $Connector = $this->getConnector();
            $UserQuery = $Connector->prepare("SELECT ID, user_login, user_pass, user_status ".
                                          "FROM `".WP_TABLE_PREFIX."users` ".
                                          "WHERE ID = :UserId LIMIT 1");
                                          
            $UserQuery->BindValue( ":UserId", $aUserId, PDO::PARAM_INT );
            $UserData = $UserQuery->fetchFirst();
            
            return ($UserData != null)
                ? $this->generateInfo($UserData)
                : null;
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
        
        public function hash( $aPassword, $aSalt, $aMethod )
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
        
        // -------------------------------------------------------------------------
        
        public function post($aSubject, $aMessage)
        {
            
        }
    }
?>