<?php
    include_once_exists(dirname(__FILE__)."/../../config/config.drupal.php");
    
    array_push(PluginRegistry::$Classes, "DrupalBinding");
    
    class DrupalBinding
    {
        public static $HashMethod_sha512  = "drupal_sha512";
        public static $HashMethod_usha512 = "drupal_usha512";
        public static $HashMethod_pmd5    = "drupal_pmd5";
        public static $HashMethod_hmd5    = "drupal_hmd5";
        public static $HashMethod_upmd5   = "drupal_upmd5";
        public static $HashMethod_uhmd5   = "drupal_uhmd5";
        
        private static $AuthenticatedGroupId = 2;
        private static $Itoa64 = "./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
        
        public $BindingName = "drupal";
        private $mConnector = null;
    
        // -------------------------------------------------------------------------
        
        public function isActive()
        {
            return defined("DRUPAL_BINDING") && DRUPAL_BINDING;
        }
        
        // -------------------------------------------------------------------------
        
        public function getConfig()
        {
            return array(
                "database"  => defined("DRUPAL_DATABASE") ? DRUPAL_DATABASE : RP_DATABASE,
                "user"      => defined("DRUPAL_USER") ? DRUPAL_USER : RP_USER,
                "password"  => defined("DRUPAL_PASS") ? DRUPAL_PASS : RP_PASS,
                "prefix"    => defined("DRUPAL_TABLE_PREFIX") ? DRUPAL_TABLE_PREFIX : "",
                "members"   => defined("DRUPAL_RAIDLEAD_GROUPS") ? explode(",", DRUPAL_RAIDLEAD_GROUPS ) : [],
                "leads"     => defined("DRUPAL_MEMBER_GROUPS") ? explode(",", DRUPAL_MEMBER_GROUPS ) : [],
                "groups"    => true
            );
        }
        
        // -------------------------------------------------------------------------
        
        public function isConfigWriteable()
        {
            $ConfigFolder = dirname(__FILE__)."/../../config";
            $ConfigFile   = $ConfigFolder."/config.drupal.php";
            
            return (!file_exists($ConfigFile) && is_writable($ConfigFolder)) || is_writable($ConfigFile);
        }
        
        // -------------------------------------------------------------------------
        
        public function writeConfig($aEnable, $aDatabase, $aPrefix, $aUser, $aPass, $aMembers, $aLeads)
        {
            $Config = fopen( dirname(__FILE__)."/../../config/config.drupal.php", "w+" );
            
            fwrite( $Config, "<?php\n");
            fwrite( $Config, "\tdefine(\"DRUPAL_BINDING\", ".(($aEnable) ? "true" : "false").");\n");
            
            if ( $aEnable )
            {
                fwrite( $Config, "\tdefine(\"DRUPAL_DATABASE\", \"".$aDatabase."\");\n");
                fwrite( $Config, "\tdefine(\"DRUPAL_USER\", \"".$aUser."\");\n");
                fwrite( $Config, "\tdefine(\"DRUPAL_PASS\", \"".$aPass."\");\n");
                fwrite( $Config, "\tdefine(\"DRUPAL_TABLE_PREFIX\", \"".$aPrefix."\");\n");
            
                fwrite( $Config, "\tdefine(\"DRUPAL_MEMBER_GROUPS\", \"".implode( ",", $aMembers )."\");\n");
                fwrite( $Config, "\tdefine(\"DRUPAL_RAIDLEAD_GROUPS\", \"".implode( ",", $aLeads )."\");\n");
            }
            
            fwrite( $Config, "?>");    
            fclose( $Config );
        }
        
        // -------------------------------------------------------------------------
        
        public function getGroups($aDatabase, $aPrefix, $aUser, $aPass, $aThrow)
        {
            $Connector = new Connector(SQL_HOST, $aDatabase, $aUser, $aPass, $aThrow);
            
            if ($Connector != null)
            {
                $GroupQuery = $Connector->prepare( "SELECT rid, name FROM `".$aPrefix."role` ORDER BY name" );
                $Groups = [];
                
                if ( $GroupQuery->execute() )
                {
                    while ( $Group = $GroupQuery->fetch(PDO::FETCH_ASSOC) )
                    {
                        array_push( $Groups, array(
                            "id"   => $Group["rid"], 
                            "name" => $Group["name"])
                        );
                    }
                }
                else if ($aThrow)
                {
                    $Connector->throwError($GroupQuery);
                }
                
                $GroupQuery->closeCursor();
                return $Groups;
            }
            
            return null;
        }
        
        // -------------------------------------------------------------------------
        
        public function getGroupsFromConfig()
        {
            $Config = $this->getConfig();
            return $this->getGroups($Config["database"], $Config["prefix"], $Config["user"], $Config["password"], false);
        }
        
        // -------------------------------------------------------------------------
        
        private function getGroup( $aUserId )
        {
            $DefaultGroup = "none";
            $MemberGroups   = explode(",", DRUPAL_MEMBER_GROUPS );
            $RaidleadGroups = explode(",", DRUPAL_RAIDLEAD_GROUPS );
            
            $GroupSt = $this->mConnector->prepare("SELECT status, rid ".
                                                  "FROM `".DRUPAL_TABLE_PREFIX."users` ".
                                                  "LEFT OUTER JOIN `".DRUPAL_TABLE_PREFIX."users_roles` USING(uid) ".
                                                  "WHERE uid = :UserId");
            
            $GroupSt->bindValue(":UserId", $aUserId, PDO::PARAM_INT);
            $GroupSt->execute();
            
            $Group = $GroupSt->fetch(PDO::FETCH_ASSOC);
            
            if ( $Group["status"] == 0 )
                return "none"; // ### return, blocked ###
                
            // Authenticated users don't gain the corresponding role, so we need to
            // fake the assigment check. "If the user is not blocked, he/she is
            // authenticated".
            
            if ( in_array(self::$AuthenticatedGroupId, $MemberGroups) )
                $DefaultGroup = "member";
                
            if ( $Group["rid"] != NULL )
            {
                do
                {
                    if ( in_array($Group["rid"], $MemberGroups) )
                        $DefaultGroup = "member";
                
                    if ( in_array($Group["rid"], $RaidleadGroups) )
                        return "raidlead"; // ### return, highest possible group ###
                }
                while ($Group = $GroupSt->fetch(PDO::FETCH_ASSOC));
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
                                                 "WHERE LOWER(name) = :Login LIMIT 1");
                                              
            $UserSt->bindValue(":Login", strtolower($aUserName), PDO::PARAM_STR);
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
        
        // -------------------------------------------------------------------------
        
        public static function hash( $aPassword, $aSalt, $aMethod )
        {
            $Password = $aPassword;
            $Prefix = '';
            
            switch($aMethod)
            {
            case self::$HashMethod_sha512:
                $Prefix = '$S$';
                break;
            
            case self::$HashMethod_usha512:
                $Password = md5($Password);
                $Prefix = '$S$';
                break;
            
            case self::$HashMethod_uhmd5:
                $Password = md5($Password);
                $Prefix = 'U$H$';
                break;
            
            case self::$HashMethod_hmd5:
                $Prefix = '$H$';
                break;
            
            case self::$HashMethod_upmd5:
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
                } while (--$Count);
                
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
            
            return substr($Prefix.self::$Itoa64[$CountB2].$Salt.$Hash, 0, 55);
        }
    }
?>