<?php
    header("Content-type: text/xml");
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
    echo "<database>";
    
    define( "LOCALE_SETUP", true );
    require_once("../../lib/private/connector.class.php");
    require_once("tools_install.php");
        
    // Write config file
    
    $ConfigFile = fopen( "../../lib/config/config.php", "w+" );
    
    fwrite( $ConfigFile, "<?php\n");
    
    fwrite( $ConfigFile, "\tdefine(\"SQL_HOST\", \"".$_REQUEST["host"]."\");\n");    
    fwrite( $ConfigFile, "\tdefine(\"RP_DATABASE\", \"".$_REQUEST["database"]."\");\n");
    fwrite( $ConfigFile, "\tdefine(\"RP_USER\", \"".$_REQUEST["user"]."\");\n");
    fwrite( $ConfigFile, "\tdefine(\"RP_PASS\", \"".$_REQUEST["password"]."\");\n");
    fwrite( $ConfigFile, "\tdefine(\"RP_TABLE_PREFIX\", \"".$_REQUEST["prefix"]."\");\n");    
    fwrite( $ConfigFile, "\tdefine(\"ALLOW_REGISTRATION\", ".$_REQUEST["register"].");\n");      
    fwrite( $ConfigFile, "\tdefine(\"ALLOW_GROUP_SYNC\", ".$_REQUEST["groupsync"].");\n");    
    fwrite( $ConfigFile, "\tdefine(\"PUBLIC_MODE\", ".$_REQUEST["public"].");\n"); 
    fwrite( $ConfigFile, "\tdefine(\"USE_CLEARTEXT_PASSWORDS\", ".$_REQUEST["cleartext"].");\n");
    
    fwrite( $ConfigFile, "?>");    
    fclose( $ConfigFile );
    
    require_once("../../lib/config/config.php");
    
    $Out = Out::getInstance();
    
    // Create tables if necessary
    
    InstallDB($_REQUEST["prefix"]);
    
    // Add default values for settings table
        
    InstallDefaultSettings($_REQUEST["prefix"]);
    $Out->flushXML("");
    
    
    echo "</database>";
?>