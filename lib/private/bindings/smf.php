<?php
    @include_once dirname(__FILE__)."/../../config/config.smf.php";
    
    class SMFBinding
    {
        public static $HashMethod = "smf_sha1s";
        
        public $BindingName = "smf";
        private $Connector = null;
    
        // -------------------------------------------------------------------------
        
        public function __construct( $Name )
        {
            $this->BindingName = $Name;
        }
        
        // -------------------------------------------------------------------------
        
        public function IsActive()
        {
            return defined("SMF_BINDING") && SMF_BINDING;
        }
        
        // -------------------------------------------------------------------------
        
        private function GetGroup( $UserData )
        {
            if ($UserData["ban_time"] > 0)
            {
                $currentTime = time();
                if ( ($UserData["ban_time"] < $currentTime) &&
                     (($UserData["expire_time"] == 0) || ($UserData["expire_time"] > $currentTime)) )
                {
                    return "none"; // ### return, banned ###
                }
            };
            
            $MemberGroups   = explode(",", SMF_MEMBER_GROUPS );
            $RaidleadGroups = explode(",", SMF_RAIDLEAD_GROUPS );
            $DefaultGroup   = "none";
            
            $Groups = explode(",", $UserData["additional_groups"]);
            array_push($Groups, $UserData["id_group"] );
            
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
        
        private function GenerateInfo( $UserData )
        {
            $info = new UserInfo();
            $info->UserId      = $UserData["id_member"];
            $info->UserName    = $UserData["member_name"];
            $info->Password    = $UserData["passwd"];
            $info->Salt        = strtolower($UserData["member_name"]);
            $info->Group       = $this->GetGroup($UserData);
            $info->BindingName = $this->BindingName;
            $info->PassBinding = $this->BindingName;
        
            return $info;
        }
        
        // -------------------------------------------------------------------------
        
        public function GetUserInfoByName( $UserName )
        {
            if ($this->Connector == null)
                $this->Connector = new Connector(SQL_HOST, SMF_DATABASE, SMF_USER, SMF_PASS);
            
            $UserSt = $this->Connector->prepare("SELECT id_member, member_name, passwd, id_group, additional_groups, ban_time, expire_time ".
                                                "FROM `".SMF_TABLE_PREFIX."members` ".
                                                "LEFT JOIN `".SMF_TABLE_PREFIX."ban_items` USING(id_member) ".
                                                "LEFT JOIN `".SMF_TABLE_PREFIX."ban_groups` USING(id_ban_group) ".
                                                "WHERE member_name = :Login LIMIT 1");
                                          
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
                $this->Connector = new Connector(SQL_HOST, SMF_DATABASE, SMF_USER, SMF_PASS);
            
            $UserSt = $this->Connector->prepare("SELECT id_member, member_name, passwd, id_group, additional_groups ".
                                                "FROM `".SMF_TABLE_PREFIX."members` ".
                                                "WHERE id_member = :UserId LIMIT 1");
                                          
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
        
        // -------------------------------------------------------------------------
        
        public static function Hash( $Password, $Salt, $Method )
        {
            return sha1($Salt.$Password);
        }
    }
?>