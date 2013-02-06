<?php
    require_once dirname(__FILE__)."/../connector.class.php";
    require_once dirname(__FILE__)."/../../config/config.php";
    
    class NativeBinding
    {
        public static $HashMethod = "native_sha256s";
        public $BindingName = "none";
    
        // -------------------------------------------------------------------------
        
        public function __construct( $Name )
        {
            $this->BindingName = $Name;
        }
        
        // -------------------------------------------------------------------------
        
        public function IsActive()
        {
            return true;
        }
        
        // -------------------------------------------------------------------------
        
        private function GenerateInfo( $UserData )
        {
            $info = new UserInfo();
            $info->UserName    = $UserData["Login"];
            $info->Password    = $UserData["Password"];
            $info->Salt        = $UserData["Salt"];
            $info->Group       = $UserData["Group"];
            $info->PassBinding = $UserData["ExternalBinding"];
            
            if (($UserData["ExternalBinding"] != "none") && 
                ($UserData["BindingActive"] == "true")) 
            {
                $info->UserId      = $UserData["ExternalId"];
                $info->BindingName = $UserData["ExternalBinding"];
            }
            else
            {
                $info->UserId      = $UserData["UserId"];
                $info->BindingName = $this->BindingName;
            }
            
            return $info;
        }
        
        // -------------------------------------------------------------------------
        
        public function GetUserInfoByName( $UserName )
        {
            $Connector = Connector::GetInstance();
            $UserSt = $Connector->prepare("SELECT * FROM ".RP_TABLE_PREFIX."User ".
                                          "WHERE Login = :Login LIMIT 1");
                                          
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
            $Connector = Connector::GetInstance();
            $UserSt = $Connector->prepare("SELECT * FROM ".RP_TABLE_PREFIX."User ".
                                          "WHERE UserId = :UserId LIMIT 1");
                                          
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
        
        public function GetMethodFromPass( $Password )
        {
            return self::$HashMethod;
        }
        
        // -------------------------------------------------------------------------
        
        public static function Hash( $Password, $Salt, $Method )
        {
            return hash("sha256", sha1($Password).$Salt);
        }
    }
?>