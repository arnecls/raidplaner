<?php
    @include_once dirname(__FILE__)."/../../config/config.eqdkp.php";
    
    array_push(PluginRegistry::$Classes, "EQDKPBinding");
    
    class EQDKPBinding
    {
        public static $HashMethod_sha512s = "eqdkp_sha512s";
        public static $HashMethod_sha512b = "eqdkp_sha512sb";
        public static $HashMethod_sha512d = "eqdkp_sha512sd";
        public static $HashMethod_sha512r = "eqdkp_sha512r";
        public static $HashMethod_md5     = "eqdkp_md5";
        public static $Itoa64             = "./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
        
        public $BindingName = "eqdkp";
        private $mConnector = null;
    
        // -------------------------------------------------------------------------
        
        public function isActive()
        {
            return defined("EQDKP_BINDING") && EQDKP_BINDING;
        }
        
        // -------------------------------------------------------------------------
        
        private function getGroup( $aUserId )
        {
            $UserRightsSt = $this->mConnector->prepare("SELECT ".EQDKP_TABLE_PREFIX."users.user_active, ".EQDKP_TABLE_PREFIX."auth_users.auth_setting,  ".EQDKP_TABLE_PREFIX."auth_options.auth_value ".
                                                      "FROM `".EQDKP_TABLE_PREFIX."users` ".
                                                      "LEFT JOIN `".EQDKP_TABLE_PREFIX."auth_users` USING(user_id) ".
                                                      "LEFT JOIN `".EQDKP_TABLE_PREFIX."auth_options` USING(auth_id) ".
                                                      "WHERE user_id = :UserId");
                                          
            $UserRightsSt->bindValue(":UserId", $aUserId, PDO::PARAM_INT);
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
        
        private function generateInfo( $aUserData )
        {
            $Info = new UserInfo();
            $Info->UserId      = $aUserData["user_id"];
            $Info->UserName    = $aUserData["username"];
            $Info->Password    = $aUserData["user_password"];
            $Info->Salt        = self::extractSaltPart($aUserData["user_password"]);
            $Info->Group       = $this->getGroup($aUserData["user_id"]);
            $Info->BindingName = $this->BindingName;
            $Info->PassBinding = $this->BindingName;
            
            return $Info;
        }
        
        // -------------------------------------------------------------------------
        
        public function getUserInfoByName( $aUserName )
        {
            if ($this->mConnector == null)
                $this->mConnector = new Connector(SQL_HOST, EQDKP_DATABASE, EQDKP_USER, EQDKP_PASS);
            
            $UserSt = $this->mConnector->prepare("SELECT user_id, username, user_password ".
                                                "FROM `".EQDKP_TABLE_PREFIX."users` ".
                                                "WHERE username = :Login LIMIT 1");
                                              
            $UserSt->bindValue(":Login", $aUserName, PDO::PARAM_STR);
            $UserSt->execute();
            
            if ( $UserSt->execute() && ($UserSt->rowCount() > 0) )
            {
                $UserData = $UserSt->fetch(PDO::FETCH_ASSOC);
                $UserSt->closeCursor();
                
                return $this->generateInfo( $UserData );
            }
        
            $UserSt->closeCursor();
            return null;
        }
        
        // -------------------------------------------------------------------------
        
        public function getUserInfoById( $aUserId )
        {
            if ($this->mConnector == null)
                $this->mConnector = new Connector(SQL_HOST, EQDKP_DATABASE, EQDKP_USER, EQDKP_PASS);
            
            $UserSt = $this->mConnector->prepare("SELECT user_id, username, user_password ".
                                                "FROM `".EQDKP_TABLE_PREFIX."users` ".
                                                "WHERE user_id = :UserId LIMIT 1");
                                              
            $UserSt->bindValue(":UserId", $aUserId, PDO::PARAM_INT);
            $UserSt->execute();
            
            if ( $UserSt->execute() && ($UserSt->rowCount() > 0) )
            {
                $UserData = $UserSt->fetch( PDO::FETCH_ASSOC );
                $UserSt->closeCursor();
                
                return $this->generateInfo( $UserData );
            }
        
            $UserSt->closeCursor();
            return null;
        }
        
        // -------------------------------------------------------------------------
        
        private static function extractSaltPart( $aPassword )
        {
            $Length = strlen(substr($aPassword, 0, strpos($aPassword, ":")));
        
            if ((substr($aPassword, 0, 4) == '$2a$') && ($Length == 60))
            {
                $Parts = explode(":", $aPassword);
                
                return substr($Parts[0],0,29).":".$Parts[1];
            }
            
            if (($aPassword[0] == '_') && ($Length == 20))
            {
                $Parts = explode(":", $aPassword);
                return substr($Parts[0],0,9).":".$Parts[1];
            }
            
            if ((substr($aPassword, 0, 3) == '$S$') && ($Length == 98))
            {
                $Count = strpos(self::$Itoa64, $aPassword[3]);
                $Salt2 = substr($aPassword, 4, 8);
                $Salt  = substr($aPassword, strpos($aPassword,":")+1);
                
                return $Count.":".$Salt.":".$Salt2;
            }
                
            if ($Length == 128)
            {
                $Parts = explode(":", $aPassword);
                return $Parts[1];
            }
            
    		return "";
        }
        
        // -------------------------------------------------------------------------
        
        public function getMethodFromPass( $aPassword )
        {
            $Length = strlen(substr($aPassword, 0, strpos($aPassword, ":")));
            
            if ((substr($aPassword, 0, 4) == '$2a$') && ($Length == 60))
                return self::$HashMethod_sha512b;
            
            if (($Length > 0) && ($aPassword[0] == "_") && ($Length == 20))
                return self::$HashMethod_sha512d;
            
            if ((substr($aPassword, 0, 3) == '$S$') && ($Length == 98)) 
                return self::$HashMethod_sha512r;
                
            if ($Length == 128) 
                return self::$HashMethod_sha512s;
    
    		return self::$HashMethod_md5;
        }
        
        // -------------------------------------------------------------------------
        
        private static function encode64( $aInput, $aCount )
        {
            $Output = '';
            $i = 0;
            
            do {
                $Value = ord($aInput[$i++]);
                $Output .= self::$Itoa64[$Value & 0x3f];
                
                if ($i < $Count)
                   $Value |= ord($aInput[$i]) << 8;
                
                $Output .= self::$Itoa64[($Value >> 6) & 0x3f];
                
                if ($i++ >= $Count)
                   break;
                
                if ($i < $Count)
                   $Value |= ord($aInput[$i]) << 16;
                
                $Output .= self::$Itoa64[($Value >> 12) & 0x3f];
                
                if ($i++ >= $aCount)
                   break;
                
                $Output .= self::$Itoa64[($Value >> 18) & 0x3f];
            } while ($i < $aCount);
            
            return $Output;
        }
        
        // -------------------------------------------------------------------------
        
        public static function hash( $aPassword, $aSalt, $aMethod )
        {
            if ( ($aMethod == self::$HashMethod_sha512b) ||
                 ($aMethod == self::$HashMethod_sha512d) )
            {
                $Parts  = explode(":",$aSalt);
                $Config = $Parts[0];
                $Salt   = $Parts[1];
                
                $PreHash = hash('sha512', $Salt.$aPassword);                
                return crypt($PreHash, $Config).":".$Salt;
            }
            
            if ( $aMethod == self::$HashMethod_sha512r )
            {
                $Parts   = explode(":",$aSalt);
                $CountB2 = intval($Parts[0], 10);
                $Count   = 1 << $CountB2;
                $Salt    = $Parts[1];
                $Salt2   = $Parts[2];
                
                $PreHash = hash("sha512", $Salt.$aPassword);
                $Hash    = hash("sha512", $Salt2.$PreHash, true);
                
                do {
                    $Hash = hash("sha512", $Hash.$PreHash, true);
                } while(--$Count);
                
                return '$S$'.self::$Itoa64[$CountB2].$Salt2.self::encode64($Hash,strlen($Hash)).":".$Salt;
            }
            
            if ( $aMethod == self::$HashMethod_sha512s )
            {
                return hash("sha512", $aSalt.$aPassword);
            }
            
            return md5($aPassword);
        }
    }
?>