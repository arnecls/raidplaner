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
				echo "<error>".L("Password cannot be changed.")."</error>";
			}
		
			msgQueryProfile( $_REQUEST );
		}
		else
		{
			// User changes password
			
			if ( !UserProxy::ChangePassword(intval($_SESSION["User"]["UserId"]), sha1($Request["passNew"]), sha1($Request["passOld"])) )
			{
				echo "<error>".L("Wrong password")."</error>";
			}
		
			msgQueryProfile( Array() );
		}
    }
    else
    {
        echo "<error>".L("Access denied")."</error>";
    }
}

?>