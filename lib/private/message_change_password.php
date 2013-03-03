<?php

function msgChangePassword( $Request )
{
    if ( ValidUser() && ($_REQUEST["id"] != 0) )
    {
        if ( UserProxy::GetInstance()->ValidateCredentials($Request["passOld"]) )
        {
            // User authenticated with valid password
            // change the password of the given id. ChangePassword does a check
            // for validity (e.g. only admin may change other user's passwords)
            
            $Salt = UserProxy::GenerateKey128();
            $HashedPassword = NativeBinding::Hash( $Request["passNew"], $Salt, "none" );
        
            if ( !UserProxy::ChangePassword($_REQUEST["id"], $HashedPassword, $Salt) )
            {
                echo "<error>".L("PasswordLocked")."</error>";
            }
            
            msgQueryProfile( $_REQUEST );
        }
        else
        {
            echo "<error>".L("WrongPassword")."</error>";
        }
    }
    else
    {
        echo "<error>".L("AccessDenied")."</error>";
    }
}

?>