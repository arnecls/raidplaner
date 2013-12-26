<?php
    include_once_exists(dirname(__FILE__)."/../../config/config.jml3.php");
    
    array_push(PluginRegistry::$Classes, "JoomlaBinding");
    
    class JoomlaBinding extends Binding
    {
        private static $BindingName = "jml3";
        
        public static $HashMethod = "jml_md5s";
        
        // -------------------------------------------------------------------------
        
        public function getName()
        {
            return self::$BindingName;
        }
        
        // -------------------------------------------------------------------------
        
        public function getConfig()
        {
            $Config = new BindingConfig();
            
            $Config->Database         = defined("JML3_DATABASE") ? JML3_DATABASE : RP_DATABASE;
            $Config->User             = defined("JML3_USER") ? JML3_USER : RP_USER;
            $Config->Password         = defined("JML3_PASS") ? JML3_PASS : RP_PASS;
            $Config->Prefix           = defined("JML3_TABLE_PREFIX") ? JML3_TABLE_PREFIX : "jml_";
            $Config->AutoLoginEnabled = defined("JML3_AUTOLOGIN") ? JML3_AUTOLOGIN : false;
            $Config->CookieData       = defined("JML3_SECRET") ? JML3_SECRET : "0123456789ABCDEF";
            $Config->Members          = defined("JML3_RAIDLEAD_GROUPS") ? explode(",", JML3_RAIDLEAD_GROUPS ) : array();
            $Config->Raidleads        = defined("JML3_MEMBER_GROUPS") ? explode(",", JML3_MEMBER_GROUPS ) : array();
            $Config->HasCookieConfig  = true;
            $Config->HasGroupConfig   = true;
            
            return $Config;
        }
        
        // -------------------------------------------------------------------------
        
        public function getExternalConfig($aRelativePath)
        {
            $Out = Out::getInstance();
            
            $ConfigPath = $_SERVER["DOCUMENT_ROOT"]."/".$aRelativePath."/Configuration.php";
            if (!file_exists($ConfigPath))
            {
                $Out->pushError($ConfigPath." ".L("NotExisting").".");
                return null;
            }
            
            @include_once($ConfigPath);
            $Config = new JConfig();
            
            return array(
                "database"  => $Config->db,
                "user"      => $Config->user,
                "password"  => $Config->password,
                "prefix"    => $Config->dbprefix,
                "cookie"    => $Config->secret,
            );
        }
        
        // -------------------------------------------------------------------------
        
        public function writeConfig($aEnable, $aDatabase, $aPrefix, $aUser, $aPass, $aAutoLogin, $aPostTo, $aPostAs, $aMembers, $aLeads, $aCookieEx)
        {
            $Config = fopen( dirname(__FILE__)."/../../config/config.jml3.php", "w+" );
            
            fwrite( $Config, "<?php\n");
            fwrite( $Config, "\tdefine(\"JML3_BINDING\", ".(($aEnable) ? "true" : "false").");\n");
            
            if ( $aEnable )
            {
                fwrite( $Config, "\tdefine(\"JML3_DATABASE\", \"".$aDatabase."\");\n");
                fwrite( $Config, "\tdefine(\"JML3_USER\", \"".$aUser."\");\n");
                fwrite( $Config, "\tdefine(\"JML3_PASS\", \"".$aPass."\");\n");
                fwrite( $Config, "\tdefine(\"JML3_TABLE_PREFIX\", \"".$aPrefix."\");\n");
                fwrite( $Config, "\tdefine(\"JML3_SECRET\", \"".$aCookieEx."\");\n");
                fwrite( $Config, "\tdefine(\"JML3_AUTOLOGIN\", ".(($aAutoLogin) ? "true" : "false").");\n");
            
                fwrite( $Config, "\tdefine(\"JML3_MEMBER_GROUPS\", \"".implode( ",", $aMembers )."\");\n");
                fwrite( $Config, "\tdefine(\"JML3_RAIDLEAD_GROUPS\", \"".implode( ",", $aLeads )."\");\n");
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
                $GroupQuery = $Connector->prepare( "SELECT id, title FROM `".$aPrefix."usergroups` ORDER BY title" );
                $Groups = array();
                
                $GroupQuery->loop(function($Group) use (&$Groups) 
                {
                    array_push( $Groups, array(
                        "id"   => $Group["id"], 
                        "name" => $Group["title"])
                    );
                }, $aThrow);
                
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
        
        private function getGroupForUser( $aUserData )
        {
            // TODO: Banning?
            
            $AssignedGroup  = "none";
            $MemberGroups   = explode(",", JML3_MEMBER_GROUPS );
            $RaidleadGroups = explode(",", JML3_RAIDLEAD_GROUPS );
            
            foreach( $aUserData["Groups"] as $Group )
            {
                if ( in_array($Group, $MemberGroups) )
                    $AssignedGroup = "member";
                   
                if ( in_array($Group, $RaidleadGroups) )
                    return "raidlead"; // ### return, best possible group ###
            }
            
            return $AssignedGroup;
        }
        
        // -------------------------------------------------------------------------
        
        private function generateUserInfo( $aUserData )
        {
            $Parts = explode(":", $aUserData["password"]);
            $Password = $Parts[0];
            $Salt = $Parts[1];

            $Info = new UserInfo();
            $Info->UserId      = $aUserData["user_id"];
            $Info->UserName    = $aUserData["username"];
            $Info->Password    = $Password;
            $Info->Salt        = $Salt;
            $Info->SessionSalt = null;
            $Info->Group       = $this->getGroupForUser($aUserData);
            $Info->BindingName = $this->BindingName;
            $Info->PassBinding = $this->BindingName;
        
            return $Info;
        }
        
        // -------------------------------------------------------------------------
        
        public function getExternalLoginData()
        {
            if (!defined("JML3_AUTOLOGIN") || !JML3_AUTOLOGIN)
                return null;
                
            $UserInfo = null;
            
            if (defined("JML3_SECRET"))
            {
                // Fetch user info if seesion cookie is set
                        
                $CookieName = md5(md5(JML3_SECRET."site"));
                
                if (isset($_COOKIE[$CookieName]))
                {
                    $Connector = $this->getConnector();
                    $UserQuery = $Connector->prepare("SELECT userid ".
                        "FROM `".JML3_TABLE_PREFIX."session` ".
                        "WHERE session_id = :sid LIMIT 1");
                                              
                    $UserQuery->BindValue( ":sid", $_COOKIE[$CookieName], PDO::PARAM_STR );
                    $UserData = $UserQuery->fetchFirst();
                    
                    if ( $UserData != null )
                    {
                        // Get user info by external id
                        
                        $UserId = $UserData["userid"];
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
            $UserQuery = $Connector->prepare("SELECT user_id, group_id, username, password, activation ".
                                          "FROM `".JML3_TABLE_PREFIX."users` ".
                                          "LEFT JOIN `".JML3_TABLE_PREFIX."user_usergroup_map` ON id=user_id ".
                                          "WHERE LOWER(username) = :Login");
                                          
            $UserQuery->BindValue( ":Login", strtolower($aUserName), PDO::PARAM_STR );
            $UserData = null;
            $Groups = array();
            
            $UserQuery->loop(function($UserData) use (&$UserData, &$Groups)
            {
                array_push($Groups, $UserData["group_id"]);
            });
            
            if ($UserData == null)
                return null; // ### return, no users ###
                
            $UserData["Groups"] = $Groups;
            return $this->generateUserInfo($UserData);
        }
        
        // -------------------------------------------------------------------------
        
        public function getUserInfoById( $aUserId )
        {
            $Connector = $this->getConnector();
            $UserQuery = $Connector->prepare("SELECT user_id, group_id, username, password, activation ".
                                          "FROM `".JML3_TABLE_PREFIX."users` ".
                                          "LEFT JOIN `".JML3_TABLE_PREFIX."user_usergroup_map` ON id=user_id ".
                                          "WHERE id = :UserId");
                                          
            $UserQuery->BindValue( ":UserId", $aUserId, PDO::PARAM_INT );
            $UserData = null;
            $Groups = array();
            
            $UserQuery->loop(function($UserData) use (&$UserData, &$Groups)
            {
                array_push($Groups, $UserData["group_id"]);
            });
            
            if ($UserData == null)
                return null; // ### return, no users ###
                
            $UserData["Groups"] = $Groups;
            return $this->generateUserInfo($UserData);
        }
        
        // -------------------------------------------------------------------------
        
        public function getMethodFromPass( $aPassword )
        {
            return self::$HashMethod;
        }
        
        // -------------------------------------------------------------------------
        
        public function hash( $aPassword, $aSalt, $aMethod )
        {
            return md5($aPassword.$aSalt);
        }
        
        // -------------------------------------------------------------------------
        
        public function post($aSubject, $aMessage)
        {
            
        }
    }
?>