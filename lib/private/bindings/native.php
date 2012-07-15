<?php
    function BindNativeUser( $User )
    {
        $password = $User["Password"];
        
        if ( isset($User["cleartext"]) && $User["cleartext"] )
        {
            $password = sha1( $password );
        }
            
        return UserProxy::TryLoginUser( $User["Login"], $password, "none" );
    }
?>