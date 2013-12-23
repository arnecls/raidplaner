<?php
    include_once_exists(dirname(__FILE__)."/../../config/config.phpbb3.php");
    
    array_push(PluginRegistry::$Classes, "PHPBB3Binding");
    
    class PHPBB3Binding extends Binding
    {
        private static $BindingName = "phpbb3";
        private static $Itoa64 = "./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
        
        public static $HashMethod_md5r = "phpbb3_md5r";
        public static $HashMethod_md5  = "phpbb3_md5";
        
        // -------------------------------------------------------------------------
        
        public function getName()
        {
            return self::$BindingName;
        }
        
        // -------------------------------------------------------------------------
        
        public function getConfig()
        {
            $Config = new BindingConfig();
            
            $Config->Database         = defined("PHPBB3_DATABASE") ? PHPBB3_DATABASE : RP_DATABASE;
            $Config->User             = defined("PHPBB3_USER") ? PHPBB3_USER : RP_USER;
            $Config->Password         = defined("PHPBB3_PASS") ? PHPBB3_PASS : RP_PASS;
            $Config->Prefix           = defined("PHPBB3_TABLE_PREFIX") ? PHPBB3_TABLE_PREFIX : "phpbb_";
            $Config->AutoLoginEnabled = defined("PHPBB3_AUTOLOGIN") ? PHPBB3_AUTOLOGIN : false;
            $Config->PostTo           = defined("PHPBB3_POSTTO") ? PHPBB3_POSTTO : "";
            $Config->PostAs           = defined("PHPBB3_POSTAS") ? PHPBB3_POSTAS : "";
            $Config->Members          = defined("PHPBB3_RAIDLEAD_GROUPS") ? explode(",", PHPBB3_RAIDLEAD_GROUPS ) : array();
            $Config->Raidleads        = defined("PHPBB3_MEMBER_GROUPS") ? explode(",", PHPBB3_MEMBER_GROUPS ) : array();
            $Config->HasGroupConfig   = true;
            $Config->HasForumConfig   = true;
            
            return $Config;
        }
        
        // -------------------------------------------------------------------------
        
        public function getExternalConfig($aRelativePath)
        {
            $Out = Out::getInstance();
            
            $ConfigPath = $_SERVER["DOCUMENT_ROOT"]."/".$aRelativePath."/config.php";
            if (!file_exists($ConfigPath))
            {
                $Out->pushError($ConfigPath." ".L("NotExisting").".");
                return null;
            }
            
            @include_once($ConfigPath);
            
            if (!defined("PHPBB_INSTALLED"))
            {
                $Out->pushError(L("NoValidConfig"));
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
        
        public function writeConfig($aEnable, $aDatabase, $aPrefix, $aUser, $aPass, $aAutoLogin, $aPostTo, $aPostAs, $aMembers, $aLeads, $aCookieEx)
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
                
                fwrite( $Config, "\tdefine(\"PHPBB3_POSTTO\", ".$aPostTo.");\n");
                fwrite( $Config, "\tdefine(\"PHPBB3_POSTAS\", ".$aPostAs.");\n");
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
                $Groups = array();
                $GroupQuery = $Connector->prepare( "SELECT group_id, group_name FROM `".$aPrefix."groups` ORDER BY group_name" );
                
                $GroupQuery->loop(function($Group) use (&$Groups) 
                {
                    array_push( $Groups, array(
                        "id"   => $Group["group_id"], 
                        "name" => $Group["group_name"])
                    );
                }, $aThrow);
                
                return $Groups;
            }
            
            return null;
        }
        
        // -------------------------------------------------------------------------
        
        public function getForums($aDatabase, $aPrefix, $aUser, $aPass, $aThrow)
        {
            $Connector = new Connector(SQL_HOST, $aDatabase, $aUser, $aPass, $aThrow);
            
            if ($Connector != null)
            {
                $Forums = array();
                $ForumQuery = $Connector->prepare( "SELECT forum_id, forum_name FROM `".$aPrefix."forums` ".
                                                   "WHERE forum_type = 1 ORDER BY forum_name" );
                
                $ForumQuery->loop(function($Forum) use (&$Forums)
                {
                    array_push( $Forums, array(
                        "id"   => $Forum["forum_id"], 
                        "name" => $Forum["forum_name"])
                    );
                }, $aThrow);
                
                return $Forums;
            }
            
            return null;
        }
        
        // -------------------------------------------------------------------------
        
        public function getUsers($aDatabase, $aPrefix, $aUser, $aPass, $aThrow)
        {
            $Connector = new Connector(SQL_HOST, $aDatabase, $aUser, $aPass, $aThrow);
            
            if ($Connector != null)
            {
                $Users = array();
                $UserQuery = $Connector->prepare("SELECT user_id, username FROM `".$aPrefix."users` ".
                                                 "LEFT JOIN `".$aPrefix."groups` USING(group_id) ".
                                                 "WHERE group_name != 'BOTS' ".
                                                 "ORDER BY username" );
                
                $UserQuery->loop(function($User) use (&$Users)
                {
                    array_push( $Users, array(
                        "id"   => $User["user_id"], 
                        "name" => $User["username"])
                    );
                }, $aThrow);
                
                return $Users;
            }
            
            return null;
        }
        
        // -------------------------------------------------------------------------
        
        private function getGroupForUser( $aUserId )
        {
            $AssignedGroup  = "none";
            $MemberGroups   = explode(",", PHPBB3_MEMBER_GROUPS );
            $RaidleadGroups = explode(",", PHPBB3_RAIDLEAD_GROUPS );
            
            $Connector = $this->getConnector();
            $GroupQuery = $Connector->prepare("SELECT user_type, `".PHPBB3_TABLE_PREFIX."user_group`.group_id, ban_start, ban_end ".
                                           "FROM `".PHPBB3_TABLE_PREFIX."users` ".
                                           "LEFT JOIN `".PHPBB3_TABLE_PREFIX."user_group` USING(user_id) ".
                                           "LEFT JOIN `".PHPBB3_TABLE_PREFIX."banlist` ON user_id = ban_userid ".
                                           "WHERE user_id = :UserId");
                                                 
            $GroupQuery->bindValue(":UserId", $aUserId, PDO::PARAM_INT);
            
            $GroupQuery->loop(function($Group) use (&$AssignedGroup)
            {
                if ( ($Group["user_type"] == 1) || 
                     ($Group["user_type"] == 2) )
                {
                    // 1 equals "inactive"
                    // 2 equals "ignore"
                    $AssignedGroup = "none";
                    return false; // ### return, disabled ###
                }
                
                if ($Group["ban_start"] > 0)
                {
                    $CurrentTime = time();
                    if ( ($Group["ban_start"] < $CurrentTime) &&
                         (($Group["ban_end"] == 0) || ($Group["ban_end"] > $CurrentTime)) )
                    {
                        $AssignedGroup = "none"; 
                        return false; // ### return, banned ###
                    }
                }
            
                if ( in_array($Group["group_id"], $MemberGroups) )
                {
                    $AssignedGroup = "member";
                }
                   
                if ( in_array($Group["group_id"], $RaidleadGroups) )
                {
                    $AssignedGroup = "raidlead"; 
                    return false; // ### return, highest possible group ###
                }
            }

            return $AssignedGroup;
        }
        
        // -------------------------------------------------------------------------
        
        private function generateUserInfo( $aUserData )
        {
            $Info = new UserInfo();
            $Info->UserId      = $aUserData["user_id"];
            $Info->UserName    = $aUserData["username_clean"];
            $Info->Password    = $aUserData["user_password"];
            $Info->Salt        = self::extractSaltPart($aUserData["user_password"]);
            $Info->SessionSalt = null;
            $Info->Group       = $this->getGroupForUser($aUserData["user_id"]);
            $Info->BindingName = $this->BindingName;
            $Info->PassBinding = $this->BindingName;
        
            return $Info;
        }
        
        // -------------------------------------------------------------------------
        
        public function getExternalLoginData()
        {
            if (!defined("PHPBB3_AUTOLOGIN") || !PHPBB3_AUTOLOGIN)
                return null;
                
            $Connector = $this->getConnector();
            $UserInfo = null;
            
            // Fetch cookie name
            
            $CookieQuery = $Connector->prepare("SELECT config_value ".
                                            "FROM `".PHPBB3_TABLE_PREFIX."config` ".
                                            "WHERE config_name = 'cookie_name' LIMIT 1");
            
            $ConfigData = $CookieQuery->fetchFirst();
            
            if ( $ConfigData != null )
            {
                $CookieName = $ConfigData["config_value"]."_sid";
                
                // Fetch user info if seesion cookie is set
                    
                if (isset($_COOKIE[$CookieName]))
                {
                    $UserQuery = $Connector->prepare("SELECT session_user_id ".
                                                  "FROM `".PHPBB3_TABLE_PREFIX."sessions` ".
                                                  "WHERE session_id = :sid LIMIT 1");
                                              
                    $UserQuery->BindValue( ":sid", $_COOKIE[$CookieName], PDO::PARAM_STR );
                    $UserData = $UserQuery->fetchFirst();
                    
                    if ( $UserData != null )
                    {
                        // Get user info by external id
                        
                        $UserId = $UserData["session_user_id"];                        
                        $UserInfo = $this->getUserInfoById($UserId);
                    }
                }
            }
            
            return $UserInfo;
        }
        
        // -------------------------------------------------------------------------
        
        public function getUserInfoByName( $aUserName )
        {
            $Connector = $this->getConnector();
            $UserQuery = $Connector->prepare("SELECT user_id, username_clean, user_password ".
                                          "FROM `".PHPBB3_TABLE_PREFIX."users` ".
                                          "WHERE LOWER(username_clean) = :Login LIMIT 1");
                                          
            $UserQuery->BindValue( ":Login", strtolower($aUserName), PDO::PARAM_STR );
            $UserData = $UserQuery->fetchFirst();
            
            return ($UserData != null)
                ? $this->generateUserInfo($UserData)
                : null;
        }
        
        // -------------------------------------------------------------------------
        
        public function getUserInfoById( $aUserId )
        {
            $Connector = $this->getConnector();
            $UserQuery = $Connector->prepare("SELECT user_id, username_clean, user_password ".
                                          "FROM `".PHPBB3_TABLE_PREFIX."users` ".
                                          "WHERE user_id = :UserId LIMIT 1");
                                          
            $UserQuery->BindValue( ":UserId", $aUserId, PDO::PARAM_INT );
            $UserData = $UserQuery->fetchFirst();
            
            return ($UserData != null)
                ? $this->generateUserInfo($UserData)
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
        
        public function post( $aSubject, $aMessage )
        {
            $Connector = $this->getConnector();
            $Connector->beginTransaction();
            
            $Timestamp = time();
            
            // Fetch user
            
            try
            {
                $UserQuery = $Connector->prepare("SELECT username, user_colour FROM `".PHPBB3_TABLE_PREFIX."users` WHERE user_id=:UserId LIMIT 1");
                $UserQuery->BindValue( ":UserId", PHPBB3_POSTAS, PDO::PARAM_INT );
                
                $UserData = $UserQuery, true->fetchFirst();
                    
                // Create topic
                
                $TopicQuery = $Connector->prepare("INSERT INTO `".PHPBB3_TABLE_PREFIX."topics` ".
                                               "(forum_id, topic_poster, topic_title, topic_last_post_subject, topic_time, topic_first_poster_name, topic_first_poster_colour, topic_last_poster_name, topic_last_poster_colour, topic_last_post_time) VALUES ".
                                               "(:ForumId, :UserId, :Subject, :Subject, :Now, :Username, :Color, :Username, :Color, :Now)");
                
                $TopicQuery->BindValue( ":ForumId", PHPBB3_POSTTO, PDO::PARAM_INT );
                $TopicQuery->BindValue( ":UserId", PHPBB3_POSTAS, PDO::PARAM_INT );
                $TopicQuery->BindValue( ":Now", $Timestamp, PDO::PARAM_INT );
                $TopicQuery->BindValue( ":Username", $UserData["username"], PDO::PARAM_STR );
                $TopicQuery->BindValue( ":Color", $UserData["user_colour"], PDO::PARAM_STR );
                $TopicQuery->BindValue( ":Subject", $aSubject, PDO::PARAM_STR );
                
                
                $Connector->run($TopicQuery, true);
                $TopicId = $Connector->lastInsertId();
                
                // Create post
                
                $PostQuery = $Connector->prepare("INSERT INTO `".PHPBB3_TABLE_PREFIX."posts` ".
                                              "(forum_id, topic_id, post_time, post_username, poster_id, post_subject, post_text, post_checksum) VALUES ".
                                              "(:ForumId, :TopicId, :Now, :Username, :UserId, :Subject, :Text, :TextMD5)");
                
                $PostQuery->BindValue( ":ForumId", PHPBB3_POSTTO, PDO::PARAM_INT );
                $PostQuery->BindValue( ":TopicId", $TopicId, PDO::PARAM_INT );
                $PostQuery->BindValue( ":UserId", PHPBB3_POSTAS, PDO::PARAM_INT );
                $PostQuery->BindValue( ":Now", $Timestamp, PDO::PARAM_INT );
                $PostQuery->BindValue( ":Username", $UserData["username"], PDO::PARAM_STR );
                
                $PostQuery->BindValue( ":Subject", $aSubject, PDO::PARAM_STR );
                $PostQuery->BindValue( ":Text", $aMessage, PDO::PARAM_STR );
                $PostQuery->BindValue( ":TextMD5", md5($aMessage), PDO::PARAM_STR );
                
                $Connector->run($PostQuery, true);
                $PostId = $Connector->lastInsertId();
                
                // Finish topic
                
                $TopicFinishQuery = $Connector->prepare("UPDATE `".PHPBB3_TABLE_PREFIX."topics` ".
                                                     "SET topic_first_post_id = :PostId, topic_last_post_id = :PostId ".
                                                     "WHERE topic_id = :TopicId LIMIT 1");
                                                                                                        
                $TopicFinishQuery->BindValue( ":TopicId", $TopicId, PDO::PARAM_INT );
                $TopicFinishQuery->BindValue( ":PostId", $PostId, PDO::PARAM_INT );
                
                $Connector->run($TopicFinishQuery, true);
                $Connector->commit();
            }
            catch (Exception $e)
            {
                $Connector->rollBack();
                throw $e;
            }
        }
    }
?>