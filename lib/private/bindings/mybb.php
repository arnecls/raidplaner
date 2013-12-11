<?php
    include_once_exists(dirname(__FILE__)."/../../config/config.mybb.php");
    
    array_push(PluginRegistry::$Classes, "MYBBBinding");
    
    class MYBBBinding
    {
        public static $HashMethod = "mybb_md5s";
        
        public $BindingName = "mybb";
        private $mConnector = null;
    
        // -------------------------------------------------------------------------
        
        public function isActive()
        {
            return defined("MYBB_BINDING") && MYBB_BINDING;
        }
        
        // -------------------------------------------------------------------------
        
        public function getConfig()
        {
            return array(
                "database"   => defined("MYBB_DATABASE") ? MYBB_DATABASE : RP_DATABASE,
                "user"       => defined("MYBB_USER") ? MYBB_USER : RP_USER,
                "password"   => defined("MYBB_PASS") ? MYBB_PASS : RP_PASS,
                "prefix"     => defined("MYBB_TABLE_PREFIX") ? MYBB_TABLE_PREFIX : "mybb_",
                "cookiename" => defined("MYBB_COOKIE") ? MYBB_COOKIE : "",
                "members"    => defined("MYBB_RAIDLEAD_GROUPS") ? explode(",", MYBB_RAIDLEAD_GROUPS ) : [],
                "leads"      => defined("MYBB_MEMBER_GROUPS") ? explode(",", MYBB_MEMBER_GROUPS ) : [],
                "cookie"     => true,
                "basedir"    => false,
                "groups"     => true
            );
        }
        
        // -------------------------------------------------------------------------
        
        public function isConfigWriteable()
        {
            $ConfigFolder = dirname(__FILE__)."/../../config";
            $ConfigFile   = $ConfigFolder."/config.mybb.php";
            
            return (!file_exists($ConfigFile) && is_writable($ConfigFolder)) || is_writable($ConfigFile);
        }
        
        // -------------------------------------------------------------------------
        
        public function writeConfig($aEnable, $aDatabase, $aPrefix, $aUser, $aPass, $aMembers, $aLeads, $aCookie)
        {
            $Config = fopen( dirname(__FILE__)."/../../config/config.mybb.php", "w+" );
            
            fwrite( $Config, "<?php\n");
            fwrite( $Config, "\tdefine(\"MYBB_BINDING\", ".(($aEnable) ? "true" : "false").");\n");
            
            if ( $aEnable )
            {
                fwrite( $Config, "\tdefine(\"MYBB_DATABASE\", \"".$aDatabase."\");\n");
                fwrite( $Config, "\tdefine(\"MYBB_USER\", \"".$aUser."\");\n");
                fwrite( $Config, "\tdefine(\"MYBB_PASS\", \"".$aPass."\");\n");
                fwrite( $Config, "\tdefine(\"MYBB_TABLE_PREFIX\", \"".$aPrefix."\");\n");
                fwrite( $Config, "\tdefine(\"MYBB_COOKIE\", \"".$aCookie."\");\n");
            
                fwrite( $Config, "\tdefine(\"MYBB_MEMBER_GROUPS\", \"".implode( ",", $aMembers )."\");\n");
                fwrite( $Config, "\tdefine(\"MYBB_RAIDLEAD_GROUPS\", \"".implode( ",", $aLeads )."\");\n");
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
                $GroupQuery = $Connector->prepare( "SELECT gid, title FROM `".$aPrefix."usergroups` ORDER BY title" );
                $Groups = [];
                
                if ( $GroupQuery->execute() )
                {
                    while ( $Group = $GroupQuery->fetch( PDO::FETCH_ASSOC ) )
                    {
                        array_push( $Groups, array(
                            "id"   => $Group["gid"], 
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
            if ($aUserData["dateline"] > 0)
            {
                $CurrentTime = time();
                if ( ($aUserData["dateline"] < $CurrentTime) &&
                     (($aUserData["lifted"] == 0) || ($aUserData["lifted"] > $CurrentTime)) )
                {
                    return "none"; // ### return, banned ###
                }
            }
            
            $MemberGroups   = explode(",", MYBB_MEMBER_GROUPS );
            $RaidleadGroups = explode(",", MYBB_RAIDLEAD_GROUPS );
            $DefaultGroup   = "none";
            
            $Groups = explode(",", $aUserData["additionalgroups"]);
            array_push($Groups, $aUserData["usergroup"] );
            
            foreach( $Groups as $Group )
            {
                if ( in_array($Group, $MemberGroups) )
                    $DefaultGroup = "member";
                   
                if ( in_array($Group, $RaidleadGroups) )
                    return "raidlead"; // ### return, highest possible group ###
            }
            
            return $DefaultGroup;
        }
        
        // -------------------------------------------------------------------------
        
        private function generateInfo( $aUserData )
        {
            $Info = new UserInfo();
            $Info->UserId      = $aUserData["uid"];
            $Info->UserName    = $aUserData["username"];
            $Info->Password    = $aUserData["password"];
            $Info->Salt        = $aUserData["salt"];
            $Info->Group       = $this->getGroup($aUserData);
            $Info->BindingName = $this->BindingName;
            $Info->PassBinding = $this->BindingName;
        
            return $Info;
        }
        
        // -------------------------------------------------------------------------
        
        public function getExternalLoginData()
        {
            if (defined("MYBB_COOKIE") && isset($_COOKIE[MYBB_COOKIE."sid"]))
            {
                if ($this->mConnector == null)
                    $this->mConnector = new Connector(SQL_HOST, MYBB_DATABASE, MYBB_USER, MYBB_PASS);
                
                $UserSt = $this->mConnector->prepare("SELECT uid ".
                     "FROM `".MYBB_TABLE_PREFIX."sessions` ".
                     "WHERE sid = :sid LIMIT 1");
                                          
                $UserSt->BindValue( ":sid", $_COOKIE[MYBB_COOKIE."sid"], PDO::PARAM_STR );
                
                if ( $UserSt->execute() && ($UserSt->rowCount() > 0) )
                {
                    $UserData = $UserSt->fetch( PDO::FETCH_ASSOC );
                    $UserId = $UserData["uid"];
                    $UserSt->closeCursor();
                    
                    return $this->getUserInfoById($UserId); // ### return, userinfo ###
                }
                
                $UserSt->closeCursor();
            }
            
            return null;
        }
        
        // -------------------------------------------------------------------------
        
        public function getUserInfoByName( $aUserName )
        {
            if ($this->mConnector == null)
                $this->mConnector = new Connector(SQL_HOST, MYBB_DATABASE, MYBB_USER, MYBB_PASS);
                
            $UserSt = $this->mConnector->prepare("SELECT uid, username, password, salt, usergroup, additionalgroups, dateline, lifted ".
                                                "FROM `".MYBB_TABLE_PREFIX."users` ".
                                                "LEFT JOIN `".MYBB_TABLE_PREFIX."banned` USING(uid) ".
                                                "WHERE LOWER(username) = :Login LIMIT 1");
            
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
                $this->mConnector = new Connector(SQL_HOST, MYBB_DATABASE, MYBB_USER, MYBB_PASS);
            
            $UserSt = $this->mConnector->prepare("SELECT uid, username, password, salt, usergroup, dateline, additionalgroups ".
                                                "FROM `".MYBB_TABLE_PREFIX."users` ".
                                                "LEFT JOIN `".MYBB_TABLE_PREFIX."banned` USING(uid) ".
                                                "WHERE uid = :UserId LIMIT 1");
        
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
        
        public function getMethodFromPass( $aPassword )
        {
            return self::$HashMethod;
        }
        
        // -------------------------------------------------------------------------
        
        public static function hash( $aPassword, $aSalt, $aMethod )
        {
            return md5(md5($aSalt).md5($aPassword));
        }
    }
?>