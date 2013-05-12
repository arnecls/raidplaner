<?php
    @include_once dirname(__FILE__)."/../../config/config.phpbb3.php";
    
    array_push(PluginRegistry::$Classes, "PHPBB3Binding");
    
    class PHPBB3Binding
    {
        public static $HashMethod_md5r = "phpbb3_md5r";
        public static $HashMethod_md5  = "phpbb3_md5";
        public static $Itoa64          = "./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
        
        public $BindingName = "phpbb3";
        private $Connector = null;
    
        // -------------------------------------------------------------------------
        
        public function IsActive()
        {
            return defined("PHPBB3_BINDING") && PHPBB3_BINDING;
        }
        
        // -------------------------------------------------------------------------
        
        private function GetGroup( $UserId )
        {
            $DefaultGroup = "none";
            $MemberGroups   = explode(",", PHPBB3_MEMBER_GROUPS );
            $RaidleadGroups = explode(",", PHPBB3_RAIDLEAD_GROUPS );
            
            $GroupSt = $this->Connector->prepare("SELECT user_type, `".PHPBB3_TABLE_PREFIX."users`.group_id, ban_start, ban_end ".
                                                 "FROM `".PHPBB3_TABLE_PREFIX."users` ".
                                                 "LEFT JOIN `".PHPBB3_TABLE_PREFIX."user_group` USING(user_id) ".
                                                 "LEFT JOIN `".PHPBB3_TABLE_PREFIX."banlist` ON user_id = ban_userid ".
                                                 "WHERE user_id = :UserId");
            
            $GroupSt->bindValue(":UserId", $UserId, PDO::PARAM_INT);
            $GroupSt->execute();
            
            while ($Group = $GroupSt->fetch(PDO::FETCH_ASSOC))
            {
                if ( ($Group["user_type"] == 1) || 
                     ($Group["user_type"] == 2) )
                {
                    // 1 equals "inactive"
                    // 2 equals "ignore"
                    return "none"; // ### return, disabled ###
                }
                
                if ($Group["ban_start"] > 0)
                {
                    $currentTime = time();
                    if ( ($Group["ban_start"] < $currentTime) &&
                         (($Group["ban_end"] == 0) || ($Group["ban_end"] > $currentTime)) )
                    {
                        return "none"; // ### return, banned ###
                    }
                }
            
                if ( in_array($Group["group_id"], $MemberGroups) )
                {
                    $DefaultGroup = "member";
                }
                   
                if ( in_array($Group["group_id"], $RaidleadGroups) )
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
            $info->UserId      = $UserData["user_id"];
            $info->UserName    = $UserData["username_clean"];
            $info->Password    = $UserData["user_password"];
            $info->Salt        = self::ExtractSaltPart($UserData["user_password"]);
            $info->Group       = $this->GetGroup($UserData["user_id"]);
            $info->BindingName = $this->BindingName;
            $info->PassBinding = $this->BindingName;
        
            return $info;
        }
        
        // -------------------------------------------------------------------------
        
        public function GetUserInfoByName( $UserName )
        {
            if ($this->Connector == null)
                $this->Connector = new Connector(SQL_HOST, PHPBB3_DATABASE, PHPBB3_USER, PHPBB3_PASS);
            
            $UserSt = $this->Connector->prepare("SELECT user_id, username_clean, user_password ".
                                                "FROM `".PHPBB3_TABLE_PREFIX."users` ".
                                                "WHERE username_clean = :Login LIMIT 1");
                                          
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
                $this->Connector = new Connector(SQL_HOST, PHPBB3_DATABASE, PHPBB3_USER, PHPBB3_PASS);
            
            $UserSt = $this->Connector->prepare("SELECT user_id, username_clean, user_password ".
                                                "FROM `".PHPBB3_TABLE_PREFIX."users` ".
                                                "WHERE user_id = :UserId LIMIT 1");
                                          
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
        
        private static function ExtractSaltPart( $Password )
        {
            if (strlen($Password) == 34)
            {
                $Count = strpos(self::$Itoa64, $Password[3]);
                $Salt = substr($Password, 4, 8);
                
                return $Count.":".$Salt;
            }
            
            return "";
        }
        
        // -------------------------------------------------------------------------
        
        public function GetMethodFromPass( $Password )
        {
            if (strlen($Password) == 34)
                return self::$HashMethod_md5r;
                
            return self::$HashMethod_md5;
        }
        
        // -------------------------------------------------------------------------
        
        private static function Encode64( $input, $count )
        {
            $output = '';
            $i = 0;
            
            do {
                $value = ord($input[$i++]);
                $output .= self::$Itoa64[$value & 0x3f];
                
                if ($i < $count)
                {
                   $value |= ord($input[$i]) << 8;
                }
                
                $output .= self::$Itoa64[($value >> 6) & 0x3f];
                
                if ($i++ >= $count)
                {
                   break;
                }
                
                if ($i < $count)
                {
                   $value |= ord($input[$i]) << 16;
                }
                
                $output .= self::$Itoa64[($value >> 12) & 0x3f];
                
                if ($i++ >= $count)
                {
                   break;
                }
                
                $output .= self::$Itoa64[($value >> 18) & 0x3f];
            } while ($i < $count);
            
            return $output;
        }
        
        // -------------------------------------------------------------------------
        
        public static function Hash( $Password, $Salt, $Method )
        {
            if ($Method == self::$HashMethod_md5 )
            {
                return md5($Password);
            }
            
            $Parts   = explode(":",$Salt);
            $CountB2 = intval($Parts[0],10);
            $Count   = 1 << $CountB2;
            $Salt    = $Parts[1];
            
            $hash = md5($Salt.$Password, true);
            
            do {
                $hash = md5($hash.$Password, true);
            } while (--$Count);
            
            return '$H$'.self::$Itoa64[$CountB2].$Salt.self::Encode64($hash,16);
        }
    }
?>