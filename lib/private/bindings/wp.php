<?php
    @include_once dirname(__FILE__)."/../../config/config.wp.php";
    
    array_push(PluginRegistry::$Classes, "WPBinding");
    
    class WPBinding
    {
        public static $HashMethod_md5r = "wp_md5r";
        
        private static $Itoa64 = "./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
        
        public $BindingName = "wp";
        private $mConnector = null;
    
        // -------------------------------------------------------------------------
        
        public function isActive()
        {
            return defined("WP_BINDING") && WP_BINDING;
        }
        
        // -------------------------------------------------------------------------
        
        public static function readWpObj( $aString, &$aResult, $aOffset )
        {
            $Idx = $aOffset;
            $MaxIdx = strlen($aString);
            $Key = "";
            
            while ($Idx < $MaxIdx)
            {
                switch ($aString[$Idx])
                {
                case 'a':
                    $Obj = array();
                    $Idx = self::readWpObj($aString, $Obj, strpos($aString, '{', $Idx)+1);
                    
                    if ($aResult == null)
                        $aResult = $Obj;
                    else
                        array_push($aResult, $Obj);
                    break;
                    
                case 's':
                    $StartIdx = strpos($aString, '"', $Idx)+1;
                    $EndIdx = strpos($aString, '"', $StartIdx);
                    array_push($aResult, substr($aString, $StartIdx, $EndIdx-$StartIdx));
                    $Idx = $EndIdx+1;
                    break;
                    
                case '}':
                    return $Idx;
                    
                default:
                    $Idx = strpos($aString, ';', $Idx);
                    break;
                }
                
                ++$Idx;
            }
            
            return $Idx;
        }
        
        // -------------------------------------------------------------------------
        
        private function getGroup( $aUserId )
        {
            $MemberGroups   = explode(",", WP_MEMBER_GROUPS );
            $RaidleadGroups = explode(",", WP_RAIDLEAD_GROUPS );
            
            $MetaSt = $this->mConnector->prepare("SELECT meta_key, meta_value ".
                                                 "FROM `".WP_TABLE_PREFIX."usermeta` ".
                                                 "WHERE user_id = :UserId AND meta_key = \"wp_capabilities\" LIMIT 1");
                                                 
            $MetaSt->bindValue(":UserId", $aUserId, PDO::PARAM_INT);
            $MetaSt->execute();
            
            while ($MetaData = $MetaSt->fetch(PDO::FETCH_ASSOC))
            {
                $Capabilities = null;
                self::readWpObj($MetaData["meta_value"], $Capabilities, 0);
                
                $Group = strtolower($Capabilities[0]);
                                
                if (in_array($Group, $RaidleadGroups))
                    return "raidlead";
                    
                if (in_array($Group, $MemberGroups))
                    return "member";
            }
            
            return "none";
        }
        
        // -------------------------------------------------------------------------
        
        private function generateInfo( $aUserData )
        {
            $Info = new UserInfo();
            $Info->UserId      = $aUserData["ID"];
            $Info->UserName    = $aUserData["user_login"];
            $Info->Password    = $aUserData["user_pass"];
            $Info->Salt        = self::extractSaltPart($aUserData["user_pass"]);
            $Info->Group       = $this->getGroup($aUserData["ID"]);
            $Info->BindingName = $this->BindingName;
            $Info->PassBinding = $this->BindingName;
        
            return $Info;
        }
        
        // -------------------------------------------------------------------------
        
        public function getUserInfoByName( $aUserName )
        {
            if ($this->mConnector == null)
                $this->mConnector = new Connector(SQL_HOST, WP_DATABASE, WP_USER, WP_PASS);
            
            $UserSt = $this->mConnector->prepare("SELECT ID, user_login, user_pass, user_status ".
                                                 "FROM `".WP_TABLE_PREFIX."users` ".
                                                 "WHERE LOWER(user_login) = :Login LIMIT 1");
                                          
            $UserSt->BindValue( ":Login", strtolower($aUserName), PDO::PARAM_STR );
            
            if ( $UserSt->execute() && ($UserSt->rowCount() > 0) )
            {
                $UserData = $UserSt->fetch( PDO::FETCH_ASSOC );
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
                $this->mConnector = new Connector(SQL_HOST, WP_DATABASE, WP_USER, WP_PASS);
            
            $UserSt = $this->mConnector->prepare("SELECT ID, user_login, user_pass, user_status ".
                                                 "FROM `".WP_TABLE_PREFIX."users` ".
                                                 "WHERE ID = :UserId LIMIT 1");
                                          
            $UserSt->BindValue( ":UserId", $aUserId, PDO::PARAM_INT );
        
            if ( $UserSt->execute() && ($UserSt->rowCount() > 0) )
            {
                $UserData = $UserSt->fetch( PDO::FETCH_ASSOC );
                $UserSt->closeCursor();
                
                return $this->generateInfo($UserData);
            }
        
            $UserSt->closeCursor();
            return null;
        }
        
        // -------------------------------------------------------------------------
        
        private static function extractSaltPart( $aPassword )
        {
            if (strlen($aPassword) == 34)
            {
                $Count = strpos(self::$Itoa64, $aPassword[3]);
                $Salt = substr($aPassword, 4, 8);
                
                return $Count.":".$Salt;
            }
            
            return "";
        }
        
        // -------------------------------------------------------------------------
        
        public function getMethodFromPass( $aPassword )
        {
            if (strlen($aPassword) == 34)
                return self::$HashMethod_md5r;
                
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
                
                if ($i < $aCount)
                {
                   $Value |= ord($aInput[$i]) << 8;
                }
                
                $Output .= self::$Itoa64[($Value >> 6) & 0x3f];
                
                if ($i++ >= $aCount)
                {
                   break;
                }
                
                if ($i < $aCount)
                {
                   $Value |= ord($aInput[$i]) << 16;
                }
                
                $Output .= self::$Itoa64[($Value >> 12) & 0x3f];
                
                if ($i++ >= $aCount)
                {
                   break;
                }
                
                $Output .= self::$Itoa64[($Value >> 18) & 0x3f];
            } while ($i < $aCount);
            
            return $Output;
        }
        
        // -------------------------------------------------------------------------
        
        public static function hash( $aPassword, $aSalt, $aMethod )
        {
            if ($aMethod == self::$HashMethod_md5 )
            {
                return md5($aPassword);
            }
            
            $Parts   = explode(":",$aSalt);
            $CountB2 = intval($Parts[0],10);
            $Count   = 1 << $CountB2;
            $Salt    = $Parts[1];
            
            $Hash = md5($Salt.$aPassword, true);
            
            do {
                $Hash = md5($Hash.$aPassword, true);
            } while (--$Count);
            
            return '$H$'.self::$Itoa64[$CountB2].$Salt.self::encode64($Hash,16);
        }
    }
?>