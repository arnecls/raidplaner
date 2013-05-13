<?php

function msgUserCreate( $aRequest )
{
    if ( ALLOW_REGISTRATION )
    {
        $Salt = UserProxy::generateKey128();
        $HashedPassword = NativeBinding::hash( $aRequest["pass"], $Salt, "none" );
        
        if ( !UserProxy::createUser("none", 0, "none", $aRequest["name"], $HashedPassword, $Salt) )
        {
            echo "<error>".L("NameInUse")."</error>";
        }
    }
    else
    {
        echo "<error>".L("AccessDenied")."</error>";
    }
}

?>