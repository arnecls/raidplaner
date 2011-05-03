<?php
	require_once(dirname(__FILE__)."/../../lib/private/connector.class.php");
	require_once(dirname(__FILE__)."/../../lib/config/config.php");

	header("Content-type: text/xml");
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
    echo "<check>";
    
	$Connector = new Connector(SQL_HOST, $_REQUEST["database"], $_REQUEST["user"], $_REQUEST["password"]);
		
	echo "</check>";
?>