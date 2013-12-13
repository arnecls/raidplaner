<?php
    include_once_exists(dirname(__FILE__)."/../../config/config.vb3.php");
    
    array_push(PluginRegistry::$Classes, "VB3Binding");
    
    class VB3Binding
    {
        public static $HashMethod = "vb3_md5s";
        
        public $BindingName = "vb3";
        private $mConnector = null;
    
        // -------------------------------------------------------------------------
        
        public function isActive()
        {
            return defined("VB3_BINDING") && VB3_BINDING;
        }
        
        // -------------------------------------------------------------------------
        
        public function getConfig()
        {
            return array(
                "database"  => defined("VB3_DATABASE") ? VB3_DATABASE : RP_DATABASE,
                "user"      => defined("VB3_USER") ? VB3_USER : RP_USER,
                "password"  => defined("VB3_PASS") ? VB3_PASS : RP_PASS,
                "prefix"    => defined("VB3_TABLE_PREFIX") ? VB3_TABLE_PREFIX : "vb_",
                "cookie"    => defined("VB3_COOKIE_PREFIX") ? VB3_COOKIE_PREFIX : "bb",
                "members"   => defined("VB3_RAIDLEAD_GROUPS") ? explode(",", VB3_RAIDLEAD_GROUPS ) : [],
                "leads"     => defined("VB3_MEMBER_GROUPS") ? explode(",", VB3_MEMBER_GROUPS ) : [],
                "cookie_ex" => true,
                "groups"    => true
            );
        }
        
        // -------------------------------------------------------------------------
        
        public function queryCookieEx($aRelativePath)
        {
            return null;
        }
        
        // -------------------------------------------------------------------------
        
        public function isConfigWriteable()
        {
            $ConfigFolder = dirname(__FILE__)."/../../config";
            $ConfigFile   = $ConfigFolder."/config.vb3.php";
            
            return (!file_exists($ConfigFile) && is_writable($ConfigFolder)) || is_writable($ConfigFile);
        }
        
        // -------------------------------------------------------------------------
        
        public function writeConfig($aEnable, $aDatabase, $aPrefix, $aUser, $aPass, $aMembers, $aLeads, $aCookieEx)
        {
            $Config = fopen( dirname(__FILE__)."/../../config/config.vb3.php", "w+" );
            
            fwrite( $Config, "<?php\n");
            fwrite( $Config, "\tdefine(\"VB3_BINDING\", ".(($aEnable) ? "true" : "false").");\n");
            
            if ( $aEnable )
            {
                fwrite( $Config, "\tdefine(\"VB3_DATABASE\", \"".$aDatabase."\");\n");
                fwrite( $Config, "\tdefine(\"VB3_USER\", \"".$aUser."\");\n");
                fwrite( $Config, "\tdefine(\"VB3_PASS\", \"".$aPass."\");\n");
                fwrite( $Config, "\tdefine(\"VB3_TABLE_PREFIX\", \"".$aPrefix."\");\n");
                fwrite( $Config, "\tdefine(\"VB3_COOKIE_PREFIX\", \"".$aCookieEx."\");\n");
                                             
                fwrite( $Config, "\tdefine(\"VB3_MEMBER_GROUPS\", \"".implode( ",", $aMembers )."\");\n");
                fwrite( $Config, "\tdefine(\"VB3_RAIDLEAD_GROUPS\", \"".implode( ",", $aLeads )."\");\n");
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
                $GroupQuery = $Connector->prepare( "SELECT usergroupid, title FROM `".$aPrefix."usergroup` ORDER BY title" );
                $Groups = [];
                
                if ( $GroupQuery->execute() )
                {
                    while ( $Group = $GroupQuery->fetch(PDO::FETCH_ASSOC) )
                    {
                        array_push( $Groups, array(
                            "id"   => $Group["usergroupid"], 
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
            if ($aUserData["bandate"] > 0)
            {
                $CurrentTime = time();
                if ( ($aUserData["bandate"] < $CurrentTime) &&
                     (($aUserData["liftdate"] == 0) || ($aUserData["liftdate"] > $CurrentTime)) )
                {
                    return "none"; // ### return, banned ###
                }
            }
            
            $MemberGroups   = explode(",", VB3_MEMBER_GROUPS );
            $RaidleadGroups = explode(",", VB3_RAIDLEAD_GROUPS );
            
            if ( in_array($aUserData["usergroupid"], $RaidleadGroups) )
                return "raidlead";
                
            if ( in_array($aUserData["usergroupid"], $MemberGroups) )
                return "member";
                        
            return "none";
        }
        
        // -------------------------------------------------------------------------
        
        private function generateInfo( $aUserData )
        {
            $Info = new UserInfo();
            $Info->UserId      = $aUserData["userid"];
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
            $UserInfo = null;
            
            // Fetch cookie name
            if ( defined("VB3_COOKIE_PREFIX") )
            {
                $CookieName = VB3_COOKIE_PREFIX."sessionhash";
                
                // Fetch user info if seesion cookie is set
                
                if (isset($_COOKIE[$CookieName]))
                {
                    if ($this->mConnector == null)
                        $this->mConnector = new Connector(SQL_HOST, VB3_DATABASE, VB3_USER, VB3_PASS);
            
                    $UserSt = $this->mConnector->prepare("SELECT userid ".
                        "FROM `".VB3_TABLE_PREFIX."session` ".
                        "WHERE sessionhash = :sid LIMIT 1");
                                              
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
                $this->mConnector = new Connector(SQL_HOST, VB3_DATABASE, VB3_USER, VB3_PASS);
            
            $UserSt = $this->mConnector->prepare("SELECT `".VB3_TABLE_PREFIX."user`.userid, `".VB3_TABLE_PREFIX."user`.usergroupid, ".
                                                "username, password, salt, bandate, liftdate ".
                                                "FROM `".VB3_TABLE_PREFIX."user` ".
                                                "LEFT JOIN `".VB3_TABLE_PREFIX."userban` USING(userid) ".
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
                $this->mConnector = new Connector(SQL_HOST, VB3_DATABASE, VB3_USER, VB3_PASS);
            
            $UserSt = $this->mConnector->prepare("SELECT `".VB3_TABLE_PREFIX."user`.userid, `".VB3_TABLE_PREFIX."user`.usergroupid, ".
                                                "username, password, salt, bandate, liftdate ".
                                                "FROM `".VB3_TABLE_PREFIX."user` ".
                                                "LEFT JOIN `".VB3_TABLE_PREFIX."userban` USING(userid) ".
                                                "WHERE userid = :UserId LIMIT 1");
                                          
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
            return md5(md5($aPassword).$aSalt);
        }
    }
?>