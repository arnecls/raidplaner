<?php
    @include_once dirname(__FILE__)."/../../config/config.mybb.php";
    
    class MYBBBinding
    {
        public static $HashMethod = "mybb_md5s";
        
        public $BindingName = "mybb";
        private $Connector = null;
    
        // -------------------------------------------------------------------------
        
        public function __construct( $Name )
        {
            $this->BindingName = $Name;
        }
        
        // -------------------------------------------------------------------------
        
        public function IsActive()
        {
            return defined("MYBB_BINDING") && MYBB_BINDING;
        }
        
        // -------------------------------------------------------------------------
        
        private function GetGroup( $UserData )
        {
            $DefaultGroup   = "none";
            $MemberGroups   = explode(",", MYBB_MEMBER_GROUPS );
            $RaidleadGroups = explode(",", MYBB_RAIDLEAD_GROUPS );
            
            $Groups = explode(",", $UserData["additionalgroups"]);
            array_push($Groups, $UserData["usergroup"] );
            
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
        
        private function GenerateInfo( $UserData )
        {
            $info = new UserInfo();
            $info->UserId      = $UserData["uid"];
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
                $this->Connector = new Connector(SQL_HOST, MYBB_DATABASE, MYBB_USER, MYBB_PASS);
                
            $UserSt = $this->Connector->prepare("SELECT uid, username, password, salt, usergroup, additionalgroups ".
                                                "FROM `".MYBB_TABLE_PREFIX."users` ".
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
                $this->Connector = new Connector(SQL_HOST, MYBB_DATABASE, MYBB_USER, MYBB_PASS);
            
            $UserSt = $this->Connector->prepare("SELECT uid, username, password, salt, usergroup, additionalgroups ".
                                                "FROM `".MYBB_TABLE_PREFIX."users` ".
                                                "WHERE uid = :UserId LIMIT 1");
        
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