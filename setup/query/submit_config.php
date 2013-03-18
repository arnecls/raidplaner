<?php
    header("Content-type: text/xml");
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
    echo "<database>";
    
    define( "LOCALE_SETUP", true );
    require_once("../../lib/private/connector.class.php");
    require_once("tools_install.php");
        
    // Write config file
    
    $configFile = fopen( "../../lib/config/config.php", "w+" );
    
    fwrite( $configFile, "<?php\n");
    
    fwrite( $configFile, "\tdefine(\"SQL_HOST\", \"".$_REQUEST["host"]."\");\n");    
    fwrite( $configFile, "\tdefine(\"RP_DATABASE\", \"".$_REQUEST["database"]."\");\n");
    fwrite( $configFile, "\tdefine(\"RP_USER\", \"".$_REQUEST["user"]."\");\n");
    fwrite( $configFile, "\tdefine(\"RP_PASS\", \"".$_REQUEST["password"]."\");\n");
    fwrite( $configFile, "\tdefine(\"RP_TABLE_PREFIX\", \"".$_REQUEST["prefix"]."\");\n");    
    fwrite( $configFile, "\tdefine(\"ALLOW_REGISTRATION\", ".$_REQUEST["register"].");\n");   
    fwrite( $configFile, "\tdefine(\"USE_CLEARTEXT_PASSWORDS\", ".$_REQUEST["cleartext"].");\n");
    
    fwrite( $configFile, "?>");    
    fclose( $configFile );
    
    require_once("../../lib/config/config.php");
    
    // Create tables if necessary
    
    InstallDB($_REQUEST["prefix"]);
    
    // Add default values for settings table
        
    InstallDefaultSettings($_REQUEST["prefix"]);
    
    echo "</database>";
?>