<?php
    require_once dirname(__FILE__)."/../connector.class.php";
    require_once dirname(__FILE__)."/../../config/config.php";
    
    class NativeBinding
    {
        public static $HashMethod = "native_sha256s";
        public $BindingName = "none";
    
        // -------------------------------------------------------------------------
        
        public function isActive()
        {
            return true;
        }
        
        // -------------------------------------------------------------------------
        
        private function generateInfo( $aUserData )
        {
            $Info = new UserInfo();
            $Info->UserName    = $aUserData["Login"];
            $Info->Password    = $aUserData["Password"];
            $Info->Salt        = $aUserData["Salt"];
            $Info->Group       = $aUserData["Group"];
            $Info->PassBinding = $aUserData["ExternalBinding"];
            
            if (($aUserData["ExternalBinding"] != "none") && 
                ($aUserData["BindingActive"] == "true")) 
            {
                $Info->UserId      = $aUserData["ExternalId"];
                $Info->BindingName = $aUserData["ExternalBinding"];
            }
            else
            {
                $Info->UserId      = $aUserData["UserId"];
                $Info->BindingName = $this->BindingName;
            }
            
            return $Info;
        }
        
        // -------------------------------------------------------------------------
        
        public function getExternalLoginData()
        {
            return null;
        }
        
        // -------------------------------------------------------------------------
        
        public function getUserInfoByName( $aUserName )
        {
            $Connector = Connector::getInstance();
            $UserSt = $Connector->prepare("SELECT * FROM ".RP_TABLE_PREFIX."User ".
                                          "WHERE Login = :Login LIMIT 1");
                                          
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
            $Connector = Connector::getInstance();
            $UserSt = $Connector->prepare("SELECT * FROM ".RP_TABLE_PREFIX."User ".
                                          "WHERE UserId = :UserId LIMIT 1");
                                          
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
        
        public function getMethodFromPass( $aPassword )
        {
            return self::$HashMethod;
        }
        
        // -------------------------------------------------------------------------
        
        public static function hash( $aPassword, $aSalt, $aMethod )
        {
            return hash("sha256", sha1($aPassword).$aSalt);
        }
    }
?>