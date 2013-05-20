<?php
    @include_once dirname(__FILE__)."/../../config/config.vanilla.php";
    
    array_push(PluginRegistry::$Classes, "VanillaBinding");
    
    class VanillaBinding
    {
        public static $HashMethod = "vanilla_md5r";
        public static $Itoa64     = "./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
        
        public $BindingName = "vanilla";
        private $mConnector = null;
    
        // -------------------------------------------------------------------------
        
        public function isActive()
        {
            return defined("VANILLA_BINDING") && MYBB_BINDING;
        }
        
        // -------------------------------------------------------------------------
        
        private function getGroup( $aUserData )
        {
            if ($aUserData["Banned"] > 0)
            {
                return "none"; // ### return, banned ###
            }
            
            $MemberGroups   = explode(",", VANILLA_MEMBER_GROUPS );
            $RaidleadGroups = explode(",", VANILLA_RAIDLEAD_GROUPS );
            $DefaultGroup   = "none";
            
            foreach( $aUserData["Roles"] as $RoleId )
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
        
        private function generateInfo( $aUserData )
        {
            $Info = new UserInfo();
            $Info->UserId      = $aUserData["UserID"];
            $Info->UserName    = $aUserData["Name"];
            $Info->Password    = $aUserData["Password"];
            $Info->Salt        = self::extractSaltPart($aUserData["Password"]);
            $Info->Group       = $this->getGroup($aUserData);
            $Info->BindingName = $this->BindingName;
            $Info->PassBinding = $this->BindingName;
        
            return $Info;
        }
        
        // -------------------------------------------------------------------------
        
        private static function extractSaltPart( $aPassword )
        {            
            $Count = strpos(self::$Itoa64, $aPassword[3]);
            $Salt = substr($aPassword, 4, 8);
            
            return $Count.":".$Salt;
        }
        
        // -------------------------------------------------------------------------
        
        public function getUserInfoByName( $aUserName )
        {
            if ($this->mConnector == null)
                $this->mConnector = new Connector(SQL_HOST, VANILLA_DATABASE, VANILLA_USER, VANILLA_PASS);
                
            $UserSt = $this->mConnector->prepare("SELECT UserID, `".VANILLA_TABLE_PREFIX."User`.Name, Password, Banned, `".VANILLA_TABLE_PREFIX."Role`.RoleID ".
                                                "FROM `".VANILLA_TABLE_PREFIX."User` ".
                                                "LEFT JOIN `".VANILLA_TABLE_PREFIX."UserRole` USING(UserID) ".
                                                "LEFT JOIN `".VANILLA_TABLE_PREFIX."Role` USING(RoleID) ".
                                                "WHERE `".VANILLA_TABLE_PREFIX."User`.Name = :Login");
            
            $UserSt->BindValue( ":Login", strtolower($aUserName), PDO::PARAM_STR );
            
            if ( $UserSt->execute() && ($UserSt->rowCount() > 0) )
            {
                $UserData = null;                               
                while ( $Row = $UserSt->fetch(PDO::FETCH_ASSOC) )
                {
                    if ($UserData == null)
                    {
                        $UserData = $Row;
                        $UserData["Roles"] = array();
                    }
                    
                    array_push($UserData["Roles"], $Row["RoleID"]);
                }
                
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
                $this->mConnector = new Connector(SQL_HOST, VANILLA_DATABASE, VANILLA_USER, VANILLA_PASS);
                
            $UserSt = $this->mConnector->prepare("SELECT UserID, `".VANILLA_TABLE_PREFIX."User`.Name, Password, Banned, `".VANILLA_TABLE_PREFIX."Role`.RoleID ".
                                                "FROM `".VANILLA_TABLE_PREFIX."User` ".
                                                "LEFT JOIN `".VANILLA_TABLE_PREFIX."UserRole` USING(UserID) ".
                                                "LEFT JOIN `".VANILLA_TABLE_PREFIX."Role` USING(RoleID) ".
                                                "WHERE `".VANILLA_TABLE_PREFIX."User`.UserID = :UserId");
        
            $UserSt->BindValue( ":UserId", $aUserId, PDO::PARAM_INT );
        
            if ( $UserSt->execute() && ($UserSt->rowCount() > 0) )
            {
                $UserData = null;                               
                while ( $Row = $UserSt->fetch(PDO::FETCH_ASSOC) )
                {
                    if ($UserData == null)
                    {
                        $UserData = $Row;
                        $UserData["Roles"] = array();
                    }
                    
                    array_push($UserData["Roles"], $Row["RoleID"]);
                }
                
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
            $Parts   = explode(":",$aSalt);
            $CountB2 = intval($Parts[0],10);
            $Count   = 1 << $CountB2;
            $Salt    = $Parts[1];
            
            $Hash = md5($Salt.$aPassword, true);
            
            do {
                $Hash = md5($Hash.$aPassword, true);
            } while (--$Count);
            
            return '$P$'.self::$Itoa64[$CountB2].$Salt.self::encode64($Hash,16);
        }
        
        // -------------------------------------------------------------------------
        
        private static function encode64( $aInput, $aCount )
        {
            $Output = '';
            $i = 0;
            do {
                $Value = ord($aInput[$i++]);
                $Output .= self::$Itoa64[$Value & 0x3f];
                if ($i < $aCount)
                    $Value |= ord($aInput[$i]) << 8;
                $Output .= self::$Itoa64[($Value >> 6) & 0x3f];
                if ($i++ >= $aCount)
                    break;
                if ($i < $aCount)
                    $Value |= ord($aInput[$i]) << 16;
                $Output .= self::$Itoa64[($Value >> 12) & 0x3f];
                if ($i++ >= $aCount)
                    break;
                $Output .= self::$Itoa64[($Value >> 18) & 0x3f];
            } while ($i < $aCount);
    
            return $Output;
        }
    }
?>