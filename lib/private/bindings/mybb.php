<?php
    @include_once dirname(__FILE__)."/../../config/config.mybb.php";
    
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
        
        public function getUserInfoByName( $aUserName )
        {
            if ($this->mConnector == null)
                $this->mConnector = new Connector(SQL_HOST, MYBB_DATABASE, MYBB_USER, MYBB_PASS);
                
            $UserSt = $this->mConnector->prepare("SELECT uid, username, password, salt, usergroup, additionalgroups, dateline, lifted ".
                                                "FROM `".MYBB_TABLE_PREFIX."users` ".
                                                "LEFT JOIN `".MYBB_TABLE_PREFIX."banned` USING(uid) ".
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
                $this->mConnector = new Connector(SQL_HOST, MYBB_DATABASE, MYBB_USER, MYBB_PASS);
            
            $UserSt = $this->mConnector->prepare("SELECT uid, username, password, salt, usergroup, additionalgroups ".
                                                "FROM `".MYBB_TABLE_PREFIX."users` ".
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