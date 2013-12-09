<?php
    include_once_exists(dirname(__FILE__)."/../../config/config.vanilla.php");
    
    array_push(PluginRegistry::$Classes, "VanillaBinding");
    
    class VanillaBinding
    {
        public static $HashMethod = "vanilla_md5r";
        
        private static $Itoa64 = "./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
        
        public $BindingName = "vanilla";
        private $mConnector = null;
    
        // -------------------------------------------------------------------------
        
        public function isActive()
        {
            return defined("VANILLA_BINDING") && VANILLA_BINDING;
        }
        
        // -------------------------------------------------------------------------
        
        public function getConfig()
        {
            return array(
                "database"  => defined("VANILLA_DATABASE") ? VANILLA_DATABASE : RP_DATABASE,
                "user"      => defined("VANILLA_USER") ? VANILLA_USER : RP_USER,
                "password"  => defined("VANILLA_PASS") ? VANILLA_PASS : RP_PASS,
                "prefix"    => defined("VANILLA_TABLE_PREFIX") ? VANILLA_TABLE_PREFIX : "GDN_",
                "members"   => defined("VANILLA_RAIDLEAD_GROUPS") ? explode(",", VANILLA_RAIDLEAD_GROUPS ) : [],
                "leads"     => defined("VANILLA_MEMBER_GROUPS") ? explode(",", VANILLA_MEMBER_GROUPS ) : [],
                "groups"    => true
            );
        }
        
        // -------------------------------------------------------------------------
        
        public function isConfigWriteable()
        {
            $ConfigFolder = dirname(__FILE__)."/../../config";
            $ConfigFile   = $ConfigFolder."/config.vanilla.php";
            
            return (!file_exists($ConfigFile) && is_writable($ConfigFolder)) || is_writable($ConfigFile);
        }
        
        // -------------------------------------------------------------------------
        
        public function writeConfig($aEnable, $aDatabase, $aPrefix, $aUser, $aPass, $aMembers, $aLeads)
        {
            $Config = fopen( dirname(__FILE__)."/../../config/config.vanilla.php", "w+" );
            
            fwrite( $Config, "<?php\n");
            fwrite( $Config, "\tdefine(\"VANILLA_BINDING\", ".(($aEnable) ? "true" : "false").");\n");
            
            if ( $aEnable )
            {
                fwrite( $Config, "\tdefine(\"VANILLA_DATABASE\", \"".$aDatabase."\");\n");
                fwrite( $Config, "\tdefine(\"VANILLA_USER\", \"".$aUser."\");\n");
                fwrite( $Config, "\tdefine(\"VANILLA_PASS\", \"".$aPass."\");\n");
                fwrite( $Config, "\tdefine(\"VANILLA_TABLE_PREFIX\", \"".$aPrefix."\");\n");
                                             
                fwrite( $Config, "\tdefine(\"VANILLA_MEMBER_GROUPS\", \"".implode( ",", $aMembers )."\");\n");
                fwrite( $Config, "\tdefine(\"VANILLA_RAIDLEAD_GROUPS\", \"".implode( ",", $aLeads )."\");\n");
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
                $GroupQuery = $Connector->prepare( "SELECT RoleID, Name FROM `".$aPrefix."Role` ORDER BY Name" );
                $Groups = [];
                
                if ( $GroupQuery->execute() )
                {
                    while ( $Group = $GroupQuery->fetch(PDO::FETCH_ASSOC) )
                    {
                        array_push( $Groups, array(
                            "id"   => $Group["RoleID"], 
                            "name" => $Group["Name"])
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
                                                "WHERE LOWER(`".VANILLA_TABLE_PREFIX."User`.Name) = :Login");
            
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