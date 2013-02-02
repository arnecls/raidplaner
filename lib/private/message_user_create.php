<?php

function msgUserCreate( $Request )
{
    if ( ALLOW_REGISTRATION )
    {
        $Salt = UserProxy::GenerateKey128();
        $HashedPassword = NativeBinding::Hash( $Request["pass"], $Salt );
        
        if ( !UserProxy::CreateUser("none", 0, "none", $Request["name"], $HashedPassword, $Salt) )
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