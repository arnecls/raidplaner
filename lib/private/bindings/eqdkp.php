<?php
    @include_once dirname(__FILE__)."/../../config/config.eqdkp.php";
    
    class EQDKPBinding
    {
        public static $HashMethod_sha512s = "eqdkp_sha512s";
        public static $HashMethod_sha512b = "eqdkp_sha512sb";
        public static $HashMethod_sha512d = "eqdkp_sha512sd";
        public static $HashMethod_sha512r = "eqdkp_sha512r";
        public static $HashMethod_md5     = "eqdkp_md5";
        public static $Itoa64             = "./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
        
        public $BindingName = "eqdkp";
        private $Connector = null;
    
        // -------------------------------------------------------------------------
        
        public function __construct( $Name )
        {
            $this->BindingName = $Name;
        }
        
        // -------------------------------------------------------------------------
        
        public function IsActive()
        {
            return defined("EQDKP_BINDING") && EQDKP_BINDING;
        }
        
        // -------------------------------------------------------------------------
        
        private function GetGroup( $UserId )
        {
            $UserRightsSt = $this->Connector->prepare("SELECT ".EQDKP_TABLE_PREFIX."users.user_active, ".EQDKP_TABLE_PREFIX."auth_users.auth_setting,  ".EQDKP_TABLE_PREFIX."auth_options.auth_value ".
                                                      "FROM `".EQDKP_TABLE_PREFIX."users` ".
                                                      "LEFT JOIN `".EQDKP_TABLE_PREFIX."auth_users` USING(user_id) ".
                                                      "LEFT JOIN `".EQDKP_TABLE_PREFIX."auth_options` USING(auth_id) ".
                                                      "WHERE user_id = :UserId");
                                          
            $UserRightsSt->bindValue(":UserId", $UserId, PDO::PARAM_INT);
            $UserRightsSt->execute();
            
            while ( $Right = $UserRightsSt->fetch(PDO::FETCH_ASSOC) )
            {
                if ( $Right["user_active"] == 0 )
                {
                    return "none"; // ### return, not active ###
                }
                
                if ( (($Right["auth_value"] == "a_raid_add") || ($Right["auth_value"] == "a_raid_upd"))
                     && ($Right["auth_setting"] == "Y") )
                {
                    return "raidlead"; // ### return, highest possible group ###
                }
            }
            
            return "member";
        }
        
        // -------------------------------------------------------------------------
        
        private function GenerateInfo( $UserData )
        {
            $info = new UserInfo();
            $info->UserId      = $UserData["user_id"];
            $info->UserName    = $UserData["username"];
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
                $this->Connector = new Connector(SQL_HOST, EQDKP_DATABASE, EQDKP_USER, EQDKP_PASS);
            
            $UserSt = $this->Connector->prepare("SELECT user_id, username, user_password ".
                                                "FROM `".EQDKP_TABLE_PREFIX."users` ".
                                                "WHERE username = :Login LIMIT 1");
                                              
            $UserSt->bindValue(":Login", $UserName, PDO::PARAM_STR);
            $UserSt->execute();
            
            if ( $UserSt->execute() && ($UserSt->rowCount() > 0) )
            {
                $UserData = $UserSt->fetch(PDO::FETCH_ASSOC);
                $UserSt->closeCursor();
                
                return $this->GenerateInfo( $UserData );
            }
        
            $UserSt->closeCursor();
            return null;
        }
        
        // -------------------------------------------------------------------------
        
        public function GetUserInfoById( $UserId )
        {
            if ($this->Connector == null)
                $this->Connector = new Connector(SQL_HOST, EQDKP_DATABASE, EQDKP_USER, EQDKP_PASS);
            
            $UserSt = $this->Connector->prepare("SELECT user_id, username, user_password ".
                                                "FROM `".EQDKP_TABLE_PREFIX."users` ".
                                                "WHERE user_id = :UserId LIMIT 1");
                                              
            $UserSt->bindValue(":UserId", $UserId, PDO::PARAM_INT);
            $UserSt->execute();
            
            if ( $UserSt->execute() && ($UserSt->rowCount() > 0) )
            {
                $UserData = $UserSt->fetch( PDO::FETCH_ASSOC );
                $UserSt->closeCursor();
                
                return $this->GenerateInfo( $UserData );
            }
        
            $UserSt->closeCursor();
            return null;
        }
        
        // -------------------------------------------------------------------------
        
        private static function ExtractSaltPart( $Password )
        {
            $length = strlen(substr($Password, 0, strpos($Password, ":")));
        
            if ((substr($Password, 0, 4) == '$2a$') && ($length == 60))
            {
                $Parts = explode(":", $Password);
                
                return substr($Parts[0],0,29).":".$Parts[1];
            }
            
            if (($Password[0] == '_') && ($length == 20))
            {
                $Parts = explode(":", $Password);
                return substr($Parts[0],0,9).":".$Parts[1];
            }
            
            if ((substr($Password, 0, 3) == '$S$') && ($length == 98))
            {
                $Count = strpos(self::$Itoa64, $Password[3]);
                $Salt2 = substr($Password, 4, 8);
                $Salt  = substr($Password, strpos($Password,":")+1);
                
                return $Count.":".$Salt.":".$Salt2;
            }
                
            if ($length == 128)
            {
                $Parts = explode(":", $Password);
                return $Parts[1];
            }
            
    		return "";
        }
        
        // -------------------------------------------------------------------------
        
        public function GetMethodFromPass( $Password )
        {
            $length = strlen(substr($Password, 0, strpos($Password, ":")));
            
            if ((substr($Password, 0, 4) == '$2a$') && ($length == 60))
                return self::$HashMethod_sha512b;
            
            if (($length > 0) && ($Password[0] == "_") && ($length == 20))
                return self::$HashMethod_sha512d;
            
            if ((substr($Password, 0, 3) == '$S$') && ($length == 98)) 
                return self::$HashMethod_sha512r;
                
            if ($length == 128) 
                return self::$HashMethod_sha512s;
    
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
            if ( ($Method == self::$HashMethod_sha512b) ||
                 ($Method == self::$HashMethod_sha512d) )
            {
                $parts  = explode(":",$Salt);
                $config = $parts[0];
                $salt   = $parts[1];
                
                $preHash = hash('sha512', $salt.$Password);                
                return crypt($preHash, $config).":".$salt;
            }
            
            if ( $Method == self::$HashMethod_sha512r )
            {
                $parts   = explode(":",$Salt);
                $countB2 = intval($parts[0], 10);
                $count   = 1 << $countB2;
                $salt    = $parts[1];
                $salt2   = $parts[2];
                
                $preHash = hash("sha512", $salt.$Password);
                $hash    = hash("sha512", $salt2.$preHash, true);
                
                do {
                    $hash = hash("sha512", $hash.$preHash, true);
                } while(--$count);
                
                return '$S$'.self::$Itoa64[$countB2].$salt2.self::Encode64($hash,strlen($hash)).":".$salt;
            }
            
            if ( $Method == self::$HashMethod_sha512s )
            {
                return hash("sha512", $Salt.$Password);
            }
            
            return md5($Password);
        }
    }
?>