<?php
    @include_once dirname(__FILE__)."/../../config/config.joomla3.php";
    
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
            $Info->Group       = $this->getGroup($aUserData);
            $Info->BindingName = $this->BindingName;
            $Info->PassBinding = $this->BindingName;
        
            return $Info;
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