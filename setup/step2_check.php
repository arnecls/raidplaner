<?php
	header("Content-type: text/xml");
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
    
	require_once("../lib/private/connector.class.php");
	require_once(dirname(__FILE__)."/../lib/config/config.php");
	
	echo "<test>";
	$TestConnection = new Connector( SQL_HOST, $_REQUEST["database"], $_REQUEST["user"], $_REQUEST["password"] );
	echo "</test>";
?>