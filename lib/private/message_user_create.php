<?php

function msgUserCreate( $aRequest )
{
    require_once dirname(__FILE__)."/../config/config.php";
    $Out = Out::getInstance();

    if ( ALLOW_REGISTRATION )
    {
        $Salt = UserProxy::generateKey128();
        $HashedPassword = NativeBinding::hash( $aRequest["pass"], $Salt, "none" );
        
        $PublicMode = defined("PUBLIC_MODE") && PUBLIC_MODE;
        $DefaultGroup = ($PublicMode) ? "member" : "none";
        
        $Out->pushValue("publicmode", $PublicMode);
        
        if ( !UserProxy::createUser($DefaultGroup, 0, "none", $aRequest["name"], $HashedPassword, $Salt) )
        {
            $Out->pushError(L("NameInUse"));
        }
    }
    else
    {
        $Out->pushError(L("AccessDenied"));
    }
}

?>