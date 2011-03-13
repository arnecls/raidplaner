<?php
	require_once(dirname(__FILE__)."/../lib/private/connector.class.php");
	require_once(dirname(__FILE__)."/../lib/config/config.php");

	header("Content-type: text/xml");
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
    echo "<grouplist>";
    
	$Connector = new Connector(SQL_HOST, $_REQUEST["database"], $_REQUEST["user"], $_REQUEST["password"]); 
	$Groups = $Connector->prepare( "SELECT group_id, group_name FROM `".$_REQUEST["prefix"]."groups` ORDER BY group_name" );
	
	if ( $Groups->execute() )
	{
		while ( $Group = $Groups->fetch() )
		{
			echo "<group>";
			echo "<id>".$Group["group_id"]."</id>";
			echo "<name>".$Group["group_name"]."</name>";
			echo "</group>";
		}
	}
	else
	{
		postErrorMessage( $Groups );
	}
		
	echo "</grouplist>";
?>