<?php
    @include_once dirname(__FILE__)."/../../config/config.vanilla.php";
    
    class VanillaBinding
    {
        public static $HashMethod = "vanilla_md5r";
        public static $Itoa64     = "./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
        
        public $BindingName = "vanilla";
        private $Connector = null;
    
        // -------------------------------------------------------------------------
        
        public function __construct( $Name )
        {
            $this->BindingName = $Name;
        }
        
        // -------------------------------------------------------------------------
        
        public function IsActive()
        {
            return defined("VANILLA_BINDING") && MYBB_BINDING;
        }
        
        // -------------------------------------------------------------------------
        
        private function GetGroup( $UserData )
        {
            if ($UserData["Banned"] > 0)
            {
                return "none"; // ### return, banned ###
            }
            
            $MemberGroups   = explode(",", VANILLA_MEMBER_GROUPS );
            $RaidleadGroups = explode(",", VANILLA_RAIDLEAD_GROUPS );
            $DefaultGroup   = "none";
            
            foreach( $UserData["Roles"] as $RoleId )
            {
                if ( in_array($RoleId, $MemberGroups) )
                {
                    $DefaultGroup = "member";
                }
                
                if ( in_array($RoleId, $RaidleadGroups) )
                {
                    return "raidlead"; // ### return, highest possible group ###
                }
            }
            
            return $DefaultGroup;
        }
        
        // -------------------------------------------------------------------------
        
        private function GenerateInfo( $UserData )
        {
            $info = new UserInfo();
            $info->UserId      = $UserData["UserID"];
            $info->UserName    = $UserData["Name"];
            $info->Password    = $UserData["Password"];
            $info->Salt        = self::ExtractSaltPart($UserData["Password"]);
            $info->Group       = $this->GetGroup($UserData);
            $info->BindingName = $this->BindingName;
            $info->PassBinding = $this->BindingName;
        
            return $info;
        }
        
        // -------------------------------------------------------------------------
        
        private static function ExtractSaltPart( $Password )
        {            
            $Count = strpos(self::$Itoa64, $Password[3]);
            $Salt = substr($Password, 4, 8);
            
            return $Count.":".$Salt;
        }
        
        // -------------------------------------------------------------------------
        
        public function GetUserInfoByName( $UserName )
        {
            if ($this->Connector == null)
                $this->Connector = new Connector(SQL_HOST, VANILLA_DATABASE, VANILLA_USER, VANILLA_PASS);
                
            $UserSt = $this->Connector->prepare("SELECT UserID, `".VANILLA_TABLE_PREFIX."User`.Name, Password, Banned, `".VANILLA_TABLE_PREFIX."Role`.RoleID ".
                                                "FROM `".VANILLA_TABLE_PREFIX."User` ".
                                                "LEFT JOIN `".VANILLA_TABLE_PREFIX."UserRole` USING(UserID) ".
                                                "LEFT JOIN `".VANILLA_TABLE_PREFIX."Role` USING(RoleID) ".
                                                "WHERE `".VANILLA_TABLE_PREFIX."User`.Name = :Login");
            
            $UserSt->BindValue( ":Login", strtolower($UserName), PDO::PARAM_STR );
            
            if ( $UserSt->execute() && ($UserSt->rowCount() > 0) )
            {
                $UserData = null;                               
                while ( $row = $UserSt->fetch(PDO::FETCH_ASSOC) )
                {
                    if ($UserData == null)
                    {
                        $UserData = $row;
                        $UserData["Roles"] = array();
                    }
                    
                    array_push($UserData["Roles"], $row["RoleID"]);
                }
                
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
                $this->Connector = new Connector(SQL_HOST, VANILLA_DATABASE, VANILLA_USER, VANILLA_PASS);
                
            $UserSt = $this->Connector->prepare("SELECT UserID, `".VANILLA_TABLE_PREFIX."User`.Name, Password, Banned, `".VANILLA_TABLE_PREFIX."Role`.RoleID ".
                                                "FROM `".VANILLA_TABLE_PREFIX."User` ".
                                                "LEFT JOIN `".VANILLA_TABLE_PREFIX."UserRole` USING(UserID) ".
                                                "LEFT JOIN `".VANILLA_TABLE_PREFIX."Role` USING(RoleID) ".
                                                "WHERE `".VANILLA_TABLE_PREFIX."User`.UserID = :UserId");
        
            $UserSt->BindValue( ":UserId", $UserId, PDO::PARAM_INT );
        
            if ( $UserSt->execute() && ($UserSt->rowCount() > 0) )
            {
                $UserData = null;                               
                while ( $row = $UserSt->fetch(PDO::FETCH_ASSOC) )
                {
                    if ($UserData == null)
                    {
                        $UserData = $row;
                        $UserData["Roles"] = array();
                    }
                    
                    array_push($UserData["Roles"], $row["RoleID"]);
                }
                
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
            $Parts   = explode(":",$Salt);
            $CountB2 = intval($Parts[0],10);
            $Count   = 1 << $CountB2;
            $Salt    = $Parts[1];
            
            $hash = md5($Salt.$Password, true);
            
            do {
                $hash = md5($hash.$Password, true);
            } while (--$Count);
            
            return '$P$'.self::$Itoa64[$CountB2].$Salt.self::Encode64($hash,16);
        }
        
        // -------------------------------------------------------------------------
        
        private static function Encode64( $input, $count )
        {
            $output = '';
    		$i = 0;
    		do {
    			$value = ord($input[$i++]);
    			$output .= $this->Itoa64[$value & 0x3f];
    			if ($i < $count)
    				$value |= ord($input[$i]) << 8;
    			$output .= $this->Itoa64[($value >> 6) & 0x3f];
    			if ($i++ >= $count)
    				break;
    			if ($i < $count)
    				$value |= ord($input[$i]) << 16;
    			$output .= $this->Itoa64[($value >> 12) & 0x3f];
    			if ($i++ >= $count)
    				break;
    			$output .= $this->Itoa64[($value >> 18) & 0x3f];
    		} while ($i < $count);
    
    		return $output;
        }
    }
?>