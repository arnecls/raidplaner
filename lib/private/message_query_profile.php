<?php

function msgQueryProfile( $Request )
{
	if ( ValidUser() )
    {
		$userId = intval( $_SESSION["User"]["UserId"] );
	
		if ( ValidAdmin() && isset( $_REQUEST["id"] ) )
		{
			$userId = intval( $_REQUEST["id"] );
		}
		
    	$Connector = Connector::GetInstance();
    	
    	$Characters = $Connector->prepare(	"Select ".RP_TABLE_PREFIX."Character.* ".
    										"FROM `".RP_TABLE_PREFIX."Character` ".
    										"WHERE UserId = :UserId ORDER BY Mainchar, Name");
    	
    	$Characters->bindValue( ":UserId", $userId, PDO::PARAM_INT );
        
        if ( !$Characters->execute() )
        {
        	postErrorMessage( $Characters );
        }
        else
        {
        	$userName = "unknown";
        	
        	while ( $Data = $Characters->fetch( PDO::FETCH_ASSOC ) )
	        {
	        	$userName = $Data["Login"];
	        	
	        	echo "<character>";
	        	echo "<id>".$Data["CharacterId"]."</id>";
	        	echo "<name>".$Data["Name"]."</name>";
	        	echo "<class>".$Data["Class"]."</class>";
	        	echo "<mainchar>".$Data["Mainchar"]."</mainchar>";
	        	echo "<role1>".$Data["Role1"]."</role1>";
	        	echo "<role2>".$Data["Role2"]."</role2>";
	        	echo "</character>";
	        }
	        
	        if ( ValidAdmin() && isset( $_REQUEST["id"] ) )
	        {
	        	$Users = $Connector->prepare( "SELECT Login FROM `".RP_TABLE_PREFIX."User` WHERE UserId = :UserId LIMIT 1" );
	        	$Users->bindValue( ":UserId", $userId, PDO::PARAM_INT );
	        	
	        	if ( !$Users->execute() )
		        {
		        	postErrorMessage( $User );
		        }
		        else
		        {
		        	$Data = $Users->fetch( PDO::FETCH_ASSOC );
		        	
		        	echo "<userid>".$userId."</userid>";
	        		echo "<name>".$Data["Login"]."</name>";
		        }
		        
		        $Users->closeCursor();
	        }
	    }
	    	
        $Characters->closeCursor();
    }
    else
    {
        echo "<error>".L("Access denied")."</error>";
    }
}
   
?>