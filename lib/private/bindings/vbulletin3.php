<?php
    @include_once dirname(__FILE__)."/../../config/config.vb3.php";
    
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
        
        public function getUserInfoByName( $aUserName )
        {
            if ($this->mConnector == null)
                $this->mConnector = new Connector(SQL_HOST, VB3_DATABASE, VB3_USER, VB3_PASS);
            
            $UserSt = $this->mConnector->prepare("SELECT `".VB3_TABLE_PREFIX."user`.userid, `".VB3_TABLE_PREFIX."user`.usergroupid, ".
                                                "username, password, salt, bandate, liftdate ".
                                                "FROM `".VB3_TABLE_PREFIX."user` ".
                                                "LEFT JOIN `".VB3_TABLE_PREFIX."userban` USING(userid) ".
                                                "WHERE username = :Login LIMIT 1");
                                          
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