<?php
    @include_once dirname(__FILE__)."/../../config/config.vb3.php";
    
    class VB3Binding
    {
        public static $HashMethod = "vb3_md5s";
        
        public $BindingName = "vb3";
        private $Connector = null;
    
        // -------------------------------------------------------------------------
        
        public function __construct( $Name )
        {
            $this->BindingName = $Name;
        }
        
        // -------------------------------------------------------------------------
        
        public function IsActive()
        {
            return defined("VB3_BINDING") && VB3_BINDING;
        }
        
        // -------------------------------------------------------------------------
        
        private function GetGroup( $UserData )
        {
            $MemberGroups   = explode(",", VB3_MEMBER_GROUPS );
            $RaidleadGroups = explode(",", VB3_RAIDLEAD_GROUPS );
            
            if ( in_array($UserData["usergroupid"], $RaidleadGroups) )
                return "raidlead";
                
            if ( in_array($UserData["usergroupid"], $MemberGroups) )
                return "member";
                        
            return "none";
        }
        
        // -------------------------------------------------------------------------
        
        private function GenerateInfo( $UserData )
        {
            $info = new UserInfo();
            $info->UserId      = $UserData["userid"];
            $info->UserName    = $UserData["username"];
            $info->Password    = $UserData["password"];
            $info->Salt        = $UserData["salt"];
            $info->Group       = $this->GetGroup($UserData);
            $info->BindingName = $this->BindingName;
            $info->PassBinding = $this->BindingName;
        
            return $info;
        }
        
        // -------------------------------------------------------------------------
        
        public function GetUserInfoByName( $UserName )
        {
            if ($this->Connector == null)
                $this->Connector = new Connector(SQL_HOST, VB3_DATABASE, VB3_USER, VB3_PASS);
            
            $UserSt = $this->Connector->prepare("SELECT userid, username, password, salt, usergroupid ".
                                                "FROM `".VB3_TABLE_PREFIX."user` ".
                                                "WHERE username = :Login LIMIT 1");
                                          
            $UserSt->BindValue( ":Login", strtolower($UserName), PDO::PARAM_STR );
        
            if ( $UserSt->execute() && ($UserSt->rowCount() > 0) )
            {
                $UserData = $UserSt->fetch( PDO::FETCH_ASSOC );
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
                $this->Connector = new Connector(SQL_HOST, VB3_DATABASE, VB3_USER, VB3_PASS);
            
            $UserSt = $this->Connector->prepare("SELECT userid, username, password, salt, usergroupid ".
                                                "FROM `".VB3_TABLE_PREFIX."user` ".
                                                "WHERE userid = :UserId LIMIT 1");
                                          
            $UserSt->BindValue( ":UserId", $UserId, PDO::PARAM_INT );
        
            if ( $UserSt->execute() && ($UserSt->rowCount() > 0) )
            {
                $UserData = $UserSt->fetch( PDO::FETCH_ASSOC );
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
    }
?>