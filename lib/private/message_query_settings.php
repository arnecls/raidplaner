<?php

function msgQuerySettings( $Request )
{
	if ( ValidAdmin() )
    {    
		$Connector = Connector::GetInstance();
    	
    	$Users = $Connector->prepare("Select * FROM `".RP_TABLE_PREFIX."User` ORDER BY Login, `Group`");
    	
        if ( !$Users->execute() )
        {
        	postErrorMessage( $Users );
        }
        else
        {	        
	        while ( $Data = $Users->fetch() )
	        {
	        	echo "<user>";
	        	echo "<id>".$Data["UserId"]."</id>";
	        	echo "<login>".xmlentities( $Data["Login"], ENT_COMPAT, "UTF-8" )."</login>";
	        	echo "<binding>".$Data["ExternalBinding"]."</binding>";
	        	echo "<group>".$Data["Group"]."</group>";
	        	echo "</user>";
	        }
	    }
	    	
        $Users->closeCursor();
        
        $Settings = $Connector->prepare("Select * FROM `".RP_TABLE_PREFIX."Setting` ORDER BY Name");
    	
        if ( !$Settings->execute() )
        {
        	postErrorMessage( $Settings );
        }
        else
        {	        
	        while ( $Data = $Settings->fetch() )
	        {
	        	echo "<setting>";
	        	echo "<id>".$Data["SettingId"]."</id>";
	        	echo "<name>".$Data["Name"]."</name>";
	        	echo "<intValue>".$Data["IntValue"]."</intValue>";
	        	echo "<textValue>".$Data["TextValue"]."</textValue>";
	        	echo "</setting>";
	        }
	    }
	    	
        $Settings->closeCursor();
    }
    else
    {
        echo "<error>".L("Access denied")."</error>";
    }
}
   
?>