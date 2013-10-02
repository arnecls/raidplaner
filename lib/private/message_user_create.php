<?php

function msgUserCreate( $aRequest )
{
    if ( ALLOW_REGISTRATION )
    {
        $Salt = UserProxy::generateKey128();
        $HashedPassword = NativeBinding::hash( $aRequest["pass"], $Salt, "none" );
        
        if ( !UserProxy::createUser("none", 0, "none", $aRequest["name"], $HashedPassword, $Salt) )
        {
            $Out = Out::getInstance();
            $Out->pushError(L("NameInUse"));
        }
    }
    else
    {
        $Out = Out::getInstance();
        $Out->pushError(L("AccessDenied"));
    }
}

?>