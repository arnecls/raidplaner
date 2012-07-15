<?php

function msgChangePassword( $Request )
{
    if ( ValidUser() )
    {
        if ( ValidAdmin() && 
             ($_REQUEST["id"] != $_SESSION["User"]["UserId"]) &&
             ($_REQUEST["id"] != 0) )
        {
            // Admin changes other user's password
            
            if ( !UserProxy::ChangePassword(intval($_REQUEST["id"]), sha1($Request["passNew"]), sha1($Request["passOld"])) )
            {
                echo "<error>".L("PasswordLocked")."</error>";
            }
        
            msgQueryProfile( $_REQUEST );
        }
        else
        {
            // User changes password
            
            if ( !UserProxy::ChangePassword(intval($_SESSION["User"]["UserId"]), sha1($Request["passNew"]), sha1($Request["passOld"])) )
            {
                echo "<error>".L("WrongPassword")."</error>";
            }
        
            msgQueryProfile( Array() );
        }
    }
    else
    {
        echo "<error>".L("AccessDenied")."</error>";
    }
}

?>