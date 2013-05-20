<?php
    @include_once dirname(__FILE__)."/../../config/config.drupal.php";
    
    array_push(PluginRegistry::$Classes, "DrupalBinding");
    
    class DrupalBinding
    {
        public static $HashMethod_sha512  = "drupal_sha512";
        public static $HashMethod_usha512 = "drupal_usha512";
        public static $HashMethod_pmd5    = "drupal_pmd5";
        public static $HashMethod_hmd5    = "drupal_hmd5";
        public static $HashMethod_upmd5   = "drupal_upmd5";
        public static $HashMethod_uhmd5   = "drupal_uhmd5";
        public static $Itoa64 = "./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
        
        public $BindingName = "drupal";
        private $mConnector = null;
    
        // -------------------------------------------------------------------------
        
        public function isActive()
        {
            return defined("DRUPAL_BINDING") && DRUPAL_BINDING;
        }
        
        // -------------------------------------------------------------------------
        
        private function getGroup( $aUserId )
        {
            $DefaultGroup = "none";
            $MemberGroups   = explode(",", DRUPAL_MEMBER_GROUPS );
            $RaidleadGroups = explode(",", DRUPAL_RAIDLEAD_GROUPS );
            
            $GroupSt = $this->mConnector->prepare("SELECT status, rid ".
                                                  "FROM `".DRUPAL_TABLE_PREFIX."users` ".
                                                  "LEFT JOIN `".DRUPAL_TABLE_PREFIX."users_roles` USING(uid) ".
                                                  "WHERE uid = :UserId");
            
            $GroupSt->bindValue(":UserId", $aUserId, PDO::PARAM_INT);
            $GroupSt->execute();
            
            while ($Group = $GroupSt->fetch(PDO::FETCH_ASSOC))
            {
                if ( $Group["status"] == 1 )
                    return "none"; // ### return, blocked ###
                    
                if ( in_array($Group["rid"], $MemberGroups) )
                    $DefaultGroup = "member";
            
                if ( in_array($Group["rid"], $RaidleadGroups) )
                    return "raidlead"; // ### return, highest possible group ###
            }

            return $DefaultGroup;
        }
        
        // -------------------------------------------------------------------------
        
        private function generateInfo( $aUserData )
        {
            $Info = new UserInfo();
            $Info->UserId      = $aUserData["uid"];
            $Info->UserName    = $aUserData["name"];
            $Info->Password    = $aUserData["pass"];
            $Info->Salt        = self::extractSaltPart($aUserData["pass"]);
            $Info->Group       = $this->getGroup($aUserData["uid"]);
            $Info->BindingName = $this->BindingName;
            $Info->PassBinding = $this->BindingName;
            
            return $Info;
        }
        
        // -------------------------------------------------------------------------
        
        public function getUserInfoByName( $aUserName )
        {
            if ($this->mConnector == null)
                $this->mConnector = new Connector(SQL_HOST, DRUPAL_DATABASE, DRUPAL_USER, DRUPAL_PASS);
            
            $UserSt = $this->mConnector->prepare("SELECT uid, name, pass ".
                                                 "FROM `".DRUPAL_TABLE_PREFIX."users` ".
                                                 "WHERE name = :Login LIMIT 1");
                                              
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
                $this->mConnector = new Connector(SQL_HOST, DRUPAL_DATABASE, DRUPAL_USER, DRUPAL_PASS);
            
            $UserSt = $this->mConnector->prepare("SELECT uid, name, pass ".
                                                 "FROM `".DRUPAL_TABLE_PREFIX."users` ".
                                                 "WHERE uid = :UserId LIMIT 1");
                                              
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
            $MD5Prefix = (substr($aPassword, 0, 2) == 'U$');            
            $Salt = ($MD5Prefix)
                ? substr($aPassword, 5, 8)
                : substr($aPassword, 4, 8);
                
            $Count = strpos(self::$Itoa64, ($MD5Prefix) ? $aPassword[4] : $aPassword[3]);
            
            return $Count.":".$Salt;
        }
        
        // -------------------------------------------------------------------------
        
        public function getMethodFromPass( $aPassword )
        {
            $MD5Prefix = (substr($aPassword, 0, 2) == 'U$');
            $Type = ($MD5Prefix)
                ? substr($aPassword, 1, 3)
                : substr($aPassword, 0, 3);
            
            switch($Type)
            {
            case '$S$':
                return ($MD5Prefix) ? self::$HashMethod_usha512 : self::$HashMethod_sha512;
                
            case '$H$':
                return ($MD5Prefix) ? self::$HashMethod_uhmd5 : self::$HashMethod_hmd5;
            
            case '$P$':
                return ($MD5Prefix) ? self::$HashMethod_upmd5 : self::$HashMethod_pmd5;
                
            default:
                break;
            }
            
            return self::$HashMethod_md5s;
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
            $Password = $aPassword;
            $Prefix = "";
            
            switch($aSalt)
            {
            case self::$HashMethod_sha512:
                $Prefix = '$S$';
                break;
            
            case self::$HashMethod_usha512:
                $Password = md5($Password);
                $Prefix = 'U$S$';
                break;
            
            case self::$HashMethod_uhmd5:
                $Password = md5($Password);
                $Prefix = 'U$H$';
                break;
            
            case self::$HashMethod_hmd5:
                $Prefix = '$H$';
                break;
            
            case self::$HashMethod_uphmd5:
                $Password = md5($Password);
                $Prefix = 'U$P$';
                break;
            
            case self::$HashMethod_pmd5:
                $Prefix = '$P$';
                break;
                
            default:
                break;
            }
            
            $Parts   = explode(":",$aSalt);
            $CountB2 = intval($Parts[0],10);
            $Count   = 1 << $CountB2;
            $Salt    = $Parts[1];
            $Hash    = null;
            
            if (($aMethod == self::$HashMethod_sha512) ||
                ($aMethod == self::$HashMethod_usha512))
            {                     
                $Hash = hash("sha512", $Salt.$Password, TRUE);
                
                do {
                    $Hash = hash("sha512", $Hash.$Password, TRUE);
                } while (--$count);
                
                $Hash = self::encode64($Hash,64);
            }
            else
            {
                $Hash = md5($Salt.$Password, TRUE);
                
                do {
                    $Hash = md5($Hash.$Password, TRUE);
                } while (--$count);
                
                $Hash = self::encode64($Hash,16);
            }
                
            return $Prefix.self::$Itoa64[$CountB2].$Salt.$Hash;
        }
    }
?>