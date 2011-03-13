<?php
	header("Content-type: text/xml");
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
    
	require_once("../lib/private/connector.class.php");
	
	$configFile = fopen( "../lib/config/config.phpbb3.php", "w+" );
	
	fwrite( $configFile, "<?php\n");
	
	fwrite( $configFile, "\tdefine(\"PHPBB3_BINDING\", ".$_REQUEST["allow"].");\n");
	
	fwrite( $configFile, "\tdefine(\"PHPBB3_DATABASE\", \"".$_REQUEST["database"]."\");\n");
	fwrite( $configFile, "\tdefine(\"PHPBB3_USER\", \"".$_REQUEST["user"]."\");\n");
	fwrite( $configFile, "\tdefine(\"PHPBB3_PASS\", \"".$_REQUEST["password"]."\");\n");
	fwrite( $configFile, "\tdefine(\"PHPBB3_TABLE_PREFIX\", \"".$_REQUEST["prefix"]."\");\n");
	
	fwrite( $configFile, "\tdefine(\"PHPBB3_MEMBER_GROUPS\", \"".implode( ",", $_REQUEST["member"] )."\");\n");
	fwrite( $configFile, "\tdefine(\"PHPBB3_RAIDLEAD_GROUPS\", \"".implode( ",", $_REQUEST["raidlead"] )."\");\n");
	
	fwrite( $configFile, "?>");	
	fclose( $configFile );
?>