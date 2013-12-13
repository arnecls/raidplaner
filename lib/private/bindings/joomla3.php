<?php
    include_once_exists(dirname(__FILE__)."/../../config/config.joomla3.php");
    
    array_push(PluginRegistry::$Classes, "JoomlaBinding");
    
    class JoomlaBinding
    {
        public static $HashMethod = "jml_md5s";
        
        public $BindingName = "jml3";
        private $mConnector = null;
    
        // -------------------------------------------------------------------------
        
        public function isActive()
        {
            return defined("JML3_BINDING") && JML3_BINDING;
        }
        
        // -------------------------------------------------------------------------
        
        public function getConfig()
        {
            return array(
                "database"  => defined("JML3_DATABASE") ? JML3_DATABASE : RP_DATABASE,
                "user"      => defined("JML3_USER") ? JML3_USER : RP_USER,
                "password"  => defined("JML3_PASS") ? JML3_PASS : RP_PASS,
                "prefix"    => defined("JML3_TABLE_PREFIX") ? JML3_TABLE_PREFIX : "jml_",
                "cookie"    => defined("JML3_SECRET") ? JML3_SECRET : "put JConfig->secret here",
                "members"   => defined("JML3_RAIDLEAD_GROUPS") ? explode(",", JML3_RAIDLEAD_GROUPS ) : [],
                "leads"     => defined("JML3_MEMBER_GROUPS") ? explode(",", JML3_MEMBER_GROUPS ) : [],
                "cookie_ex" => true,
                "groups"    => true
            );
        }
        
        // -------------------------------------------------------------------------
        
        public function isConfigWriteable()
        {
            $ConfigFolder = dirname(__FILE__)."/../../config";
            $ConfigFile   = $ConfigFolder."/config.joomla3.php";
            
            return (!file_exists($ConfigFile) && is_writable($ConfigFolder)) || is_writable($ConfigFile);
        }
        
        // -------------------------------------------------------------------------
        
        public function writeConfig($aEnable, $aDatabase, $aPrefix, $aUser, $aPass, $aMembers, $aLeads, $aCookieEx)
        {
            $Config = fopen( dirname(__FILE__)."/../../config/config.joomla3.php", "w+" );
            
            fwrite( $Config, "<?php\n");
            fwrite( $Config, "\tdefine(\"JML3_BINDING\", ".(($aEnable) ? "true" : "false").");\n");
            
            if ( $aEnable )
            {
                fwrite( $Config, "\tdefine(\"JML3_DATABASE\", \"".$aDatabase."\");\n");
                fwrite( $Config, "\tdefine(\"JML3_USER\", \"".$aUser."\");\n");
                fwrite( $Config, "\tdefine(\"JML3_PASS\", \"".$aPass."\");\n");
                fwrite( $Config, "\tdefine(\"JML3_TABLE_PREFIX\", \"".$aPrefix."\");\n");
                fwrite( $Config, "\tdefine(\"JML3_SECRET\", \"".$aCookieEx."\");\n");
            
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
                $Groups = [];
                
                if ( $GroupQuery->execute() )
                {
                    while ( $Group = $GroupQuery->fetch( PDO::FETCH_ASSOC ) )
                    {
                        array_push( $Groups, array(
                            "id"   => $Group["id"], 
                            "name" => $Group["title"])
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
        
        private function getGroup( $aUserData )
        {
            // TODO: Banning?
            
            $MemberGroups   = explode(",", JML3_MEMBER_GROUPS );
            $RaidleadGroups = explode(",", JML3_RAIDLEAD_GROUPS );
            $DefaultGroup   = "none";
            
            foreach( $aUserData["Groups"] as $Group )
            {
                if ( in_array($Group, $MemberGroups) )
                    $DefaultGroup = "member";
                   
                if ( in_array($Group, $RaidleadGroups) )
                    return "raidlead"; // ### return, best possible group ###
            }
            
            return $DefaultGroup;
        }
        
        // -------------------------------------------------------------------------
        
        private function generateInfo( $aUserData )
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
            $Info->Group       = $this->getGroup($aUserData);
            $Info->BindingName = $this->BindingName;
            $Info->PassBinding = $this->BindingName;
        
            return $Info;
        }
        
        // -------------------------------------------------------------------------
        
        public function getExternalLoginData()
        {
            $UserInfo = null;
            
            if (defined("JML3_SECRET"))
            {
                // Fetch user info if seesion cookie is set
                        
                $CookieName = md5(md5(JML3_SECRET."site"));
                
                if (isset($_COOKIE[$CookieName]))
                {
                    if ($this->mConnector == null)
                        $this->mConnector = new Connector(SQL_HOST, JML3_DATABASE, JML3_USER, JML3_PASS);
                
                    $UserSt = $this->mConnector->prepare("SELECT userid ".
                        "FROM `".JML3_TABLE_PREFIX."session` ".
                        "WHERE session_id = :sid LIMIT 1");
                                              
                    $UserSt->BindValue( ":sid", $_COOKIE[$CookieName], PDO::PARAM_STR );
                    
                    if ( $UserSt->execute() && ($UserSt->rowCount() > 0) )
                    {
                        // Get user info by external id
                        
                        $UserData = $UserSt->fetch( PDO::FETCH_ASSOC );
                        $UserId = $UserData["userid"];
                        
                        $UserInfo = $this->getUserInfoById($UserId);
                    }
                    
                    $UserSt->closeCursor();
                }
            }
            
            return $UserInfo;
        }
        
        // -------------------------------------------------------------------------
        
        public function getUserInfoByName( $aUserName )
        {
            if ($this->mConnector == null)
                $this->mConnector = new Connector(SQL_HOST, JML3_DATABASE, JML3_USER, JML3_PASS);
            
            $UserSt = $this->mConnector->prepare("SELECT user_id, group_id, username, password, activation ".
                                                "FROM `".JML3_TABLE_PREFIX."users` ".
                                                "LEFT JOIN `".JML3_TABLE_PREFIX."user_usergroup_map` ON id=user_id ".
                                                "WHERE LOWER(username) = :Login");
                                          
            $UserSt->BindValue( ":Login", strtolower($aUserName), PDO::PARAM_STR );
        
            if ( $UserSt->execute() && ($UserSt->rowCount() > 0) )
            {                
                $UserData = $UserSt->fetch(PDO::FETCH_ASSOC);
                $UserData["Groups"] = array($UserData["group_id"]);
                
                while ($Row = $UserSt->fetch(PDO::FETCH_ASSOC))
                    array_push($UserData["Groups"], $Row["group_id"]);
                
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
                $this->mConnector = new Connector(SQL_HOST, JML3_DATABASE, JML3_USER, JML3_PASS);
            
            $UserSt = $this->mConnector->prepare("SELECT user_id, group_id, username, password, activation ".
                                                "FROM `".JML3_TABLE_PREFIX."users` ".
                                                "LEFT JOIN `".JML3_TABLE_PREFIX."user_usergroup_map` ON id=user_id ".
                                                "WHERE id = :UserId");
                                          
            $UserSt->BindValue( ":UserId", $aUserId, PDO::PARAM_INT );
        
            if ( $UserSt->execute() && ($UserSt->rowCount() > 0) )
            {
                $UserData = $UserSt->fetch(PDO::FETCH_ASSOC);
                $UserData["Groups"] = array($UserData["group_id"]);
                
                while ($Row = $UserSt->fetch(PDO::FETCH_ASSOC))
                    array_push($UserData["Groups"], $Row["group_id"]);
                               
                $UserSt->closeCursor();
                return $this->generateInfo($UserData);
            }
        
            $UserSt->closeCursor();
            return null;
        }
        
        // -------------------------------------------------------------------------
        
        public function getMethodFromPass( $aPassword )
        {
            return self::$HashMethod;
        }
        
        // -------------------------------------------------------------------------
        
        public static function hash( $aPassword, $aSalt, $aMethod )
        {
            return md5($aPassword.$aSalt);
        }
    }
?>