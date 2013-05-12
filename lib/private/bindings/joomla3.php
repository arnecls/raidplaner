<?php
    @include_once dirname(__FILE__)."/../../config/config.joomla3.php";
    
    array_push(PluginRegistry::$Classes, "JoomlaBinding");
    
    class JoomlaBinding
    {
        public static $HashMethod = "jml_md5s";
        
        public $BindingName = "jml3";
        private $Connector = null;
    
        // -------------------------------------------------------------------------
        
        public function IsActive()
        {
            return defined("JML3_BINDING") && JML3_BINDING;
        }
        
        // -------------------------------------------------------------------------
        
        private function GetGroup( $UserData )
        {
            // TODO: Banning?
            
            $MemberGroups   = explode(",", JML3_MEMBER_GROUPS );
            $RaidleadGroups = explode(",", JML3_RAIDLEAD_GROUPS );
            $DefaultGroup   = "none";
            
            foreach( $UserData["Groups"] as $Group )
            {
                if ( in_array($Group, $MemberGroups) )
                    $DefaultGroup = "member";
                   
                if ( in_array($Group, $RaidleadGroups) )
                    return "raidlead"; // ### return, best possible group ###
            }
            
            return $DefaultGroup;
        }
        
        // -------------------------------------------------------------------------
        
        private function GenerateInfo( $UserData )
        {
            $parts = explode(":", $UserData["password"]);
            $password = $parts[0];
            $salt = $parts[1];

            $info = new UserInfo();
            $info->UserId      = $UserData["user_id"];
            $info->UserName    = $UserData["username"];
            $info->Password    = $password;
            $info->Salt        = $salt;
            $info->Group       = $this->GetGroup($UserData);
            $info->BindingName = $this->BindingName;
            $info->PassBinding = $this->BindingName;
        
            return $info;
        }
        
        // -------------------------------------------------------------------------
        
        public function GetUserInfoByName( $UserName )
        {
            if ($this->Connector == null)
                $this->Connector = new Connector(SQL_HOST, JML3_DATABASE, JML3_USER, JML3_PASS);
            
            $UserSt = $this->Connector->prepare("SELECT user_id, group_id, username, password, activation ".
                                                "FROM `".JML3_TABLE_PREFIX."users` ".
                                                "LEFT JOIN `".JML3_TABLE_PREFIX."user_usergroup_map` ON id=user_id ".
                                                "WHERE username = :Login");
                                          
            $UserSt->BindValue( ":Login", $UserName, PDO::PARAM_STR );
        
            if ( $UserSt->execute() && ($UserSt->rowCount() > 0) )
            {                
                $UserData = $UserSt->fetch(PDO::FETCH_ASSOC);
                $UserData["Groups"] = array($UserData["group_id"]);
                
                while ($row = $UserSt->fetch(PDO::FETCH_ASSOC))
                    array_push($UserData["Groups"], $row["group_id"]);
                
                $UserSt->closeCursor();
                return $this->GenerateInfo($UserData);
            }
        
            $UserSt->closeCursor();
            return null;
        }
        
        // -------------------------------------------------------------------------
        
        public function GetUserInfoById( $UserId )
        {
            if ($this->Connector == null)
                $this->Connector = new Connector(SQL_HOST, JML3_DATABASE, JML3_USER, JML3_PASS);
            
            $UserSt = $this->Connector->prepare("SELECT user_id, group_id, username, password, activation ".
                                                "FROM `".JML3_TABLE_PREFIX."users` ".
                                                "LEFT JOIN `".JML3_TABLE_PREFIX."user_usergroup_map` ON id=user_id ".
                                                "WHERE id = :UserId");
                                          
            $UserSt->BindValue( ":UserId", $UserId, PDO::PARAM_INT );
        
            if ( $UserSt->execute() && ($UserSt->rowCount() > 0) )
            {
                $UserData = $UserSt->fetch(PDO::FETCH_ASSOC);
                $UserData["Groups"] = array($UserData["group_id"]);
                
                while ($row = $UserSt->fetch(PDO::FETCH_ASSOC))
                    array_push($UserData["Groups"], $row["group_id"]);
                               
                $UserSt->closeCursor();
                return $this->GenerateInfo($UserData);
            }
        
            $UserSt->closeCursor();
            return null;
        }
        
        // -------------------------------------------------------------------------
        
        public function GetMethodFromPass( $Password )
        {
            return self::$HashMethod;
        }
        
        // -------------------------------------------------------------------------
        
        public static function Hash( $Password, $Salt, $Method )
        {
            return md5($Password.$Salt);
        }
    }
?>