<?php
    @include_once dirname(__FILE__)."/../../config/config.smf.php";
    
    array_push(PluginRegistry::$Classes, "SMFBinding");
    
    class SMFBinding
    {
        public static $HashMethod = "smf_sha1s";
        
        public $BindingName = "smf";
        private $mConnector = null;
    
        // -------------------------------------------------------------------------
        
        public function isActive()
        {
            return defined("SMF_BINDING") && SMF_BINDING;
        }
        
        // -------------------------------------------------------------------------
        
        private function getGroup( $aUserData )
        {
            if ($aUserData["ban_time"] > 0)
            {
                $CurrentTime = time();
                if ( ($aUserData["ban_time"] < $CurrentTime) &&
                     (($aUserData["expire_time"] == 0) || ($aUserData["expire_time"] > $CurrentTime)) )
                {
                    return "none"; // ### return, banned ###
                }
            };
            
            $MemberGroups   = explode(",", SMF_MEMBER_GROUPS );
            $RaidleadGroups = explode(",", SMF_RAIDLEAD_GROUPS );
            $DefaultGroup   = "none";
            
            $Groups = explode(",", $aUserData["additional_groups"]);
            array_push($Groups, $aUserData["id_group"] );
            
            foreach( $Groups as $Group )
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
            $Info = new UserInfo();
            $Info->UserId      = $aUserData["id_member"];
            $Info->UserName    = $aUserData["member_name"];
            $Info->Password    = $aUserData["passwd"];
            $Info->Salt        = strtolower($aUserData["member_name"]);
            $Info->Group       = $this->getGroup($aUserData);
            $Info->BindingName = $this->BindingName;
            $Info->PassBinding = $this->BindingName;
        
            return $Info;
        }
        
        // -------------------------------------------------------------------------
        
        public function getUserInfoByName( $aUserName )
        {
            if ($this->mConnector == null)
                $this->mConnector = new Connector(SQL_HOST, SMF_DATABASE, SMF_USER, SMF_PASS);
            
            $UserSt = $this->mConnector->prepare("SELECT id_member, member_name, passwd, id_group, additional_groups, ban_time, expire_time ".
                                                "FROM `".SMF_TABLE_PREFIX."members` ".
                                                "LEFT JOIN `".SMF_TABLE_PREFIX."ban_items` USING(id_member) ".
                                                "LEFT JOIN `".SMF_TABLE_PREFIX."ban_groups` USING(id_ban_group) ".
                                                "WHERE member_name = :Login LIMIT 1");
                                          
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
                $this->mConnector = new Connector(SQL_HOST, SMF_DATABASE, SMF_USER, SMF_PASS);
            
            $UserSt = $this->mConnector->prepare("SELECT id_member, member_name, passwd, id_group, additional_groups, ban_time, expire_time ".
                                                "FROM `".SMF_TABLE_PREFIX."members` ".
                                                "LEFT JOIN `".SMF_TABLE_PREFIX."ban_items` USING(id_member) ".
                                                "LEFT JOIN `".SMF_TABLE_PREFIX."ban_groups` USING(id_ban_group) ".
                                                "WHERE id_member = :UserId LIMIT 1");
                                          
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
            return sha1($aSalt.$aPassword);
        }
    }
?>