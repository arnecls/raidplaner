<?php
    require_once dirname(__FILE__)."/../connector.class.php";
    require_once dirname(__FILE__)."/../../config/config.php";
    
    
    function HashNativePasswordWithSalt( $Password, $Salt )
    {
        return hash("sha256", sha1($Password).$Salt);
    }
    
    // -------------------------------------------------------------------------
    
    function HashNativePasswordForName( $UserName, $Password )
    {
        $Connector = Connector::GetInstance();
        $UserSt = $Connector->prepare("SELECT Hash FROM ".RP_TABLE_PREFIX."User WHERE Login = :Login AND ExternalBinding = 'none' LIMIT 1");
        $UserSt->BindValue( "Login", $UserName, PDO::PARAM_STR );
        
        $HashedPassword = null;
        
        if ( $UserSt->execute() && $UserSt->rowCount() > 0 )
        {
            $UserData = $UserSt->fetch( PDO::FETCH_ASSOC );
            $HashedPassword = HashNativePasswordWithSalt($Password, $UserData["Hash"]);
        }
            
        $UserSt->closeCursor();
        return $HashedPassword;
    }
    
    // -------------------------------------------------------------------------
    
    function HashNativePasswordForId( $UserId, $Password )
    {
        $Connector = Connector::GetInstance();
        $UserSt = $Connector->prepare("SELECT Hash FROM ".RP_TABLE_PREFIX."User WHERE UserId = :UserId AND ExternalBinding = 'none' LIMIT 1");
        $UserSt->BindValue( "UserId", $UserId, PDO::PARAM_STR );
        
        $HashedPassword = null;
        
        if ( $UserSt->execute() && $UserSt->rowCount() > 0 )
        {
            $UserData = $UserSt->fetch( PDO::FETCH_ASSOC );
            $HashedPassword = HashNativePasswordWithSalt($Password, $UserData["Hash"]);
        }
            
        $UserSt->closeCursor();
        return $HashedPassword;
    }
    
    // -------------------------------------------------------------------------
        
    function BindNativeUser( $User )
    {
        $password = $User["Password"];
        
        if ( isset($User["cleartext"]) && 
             $User["cleartext"] )
        {
            $password = HashNativePasswordForName($User["Login"], $User["Password"]);
        }
            
        return UserProxy::TryLoginUser( $User["Login"], $password, "none" );
    }
?>