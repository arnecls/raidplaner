<?php
    header("Content-type: text/xml");
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
    echo "<bindings>";    
    
    define( "LOCALE_SETUP", true );
    require_once("../../lib/private/connector.class.php");
    
    $phpbb3_config = fopen( "../../lib/config/config.phpbb3.php", "w+" );
    $eqdkp_config = fopen( "../../lib/config/config.eqdkp.php", "w+" );
    $vb3_config = fopen( "../../lib/config/config.vb3.php", "w+" );
    
    // phpbb3
    
    fwrite( $phpbb3_config, "<?php\n");
    fwrite( $phpbb3_config, "\tdefine(\"PHPBB3_BINDING\", ".(($_REQUEST["phpbb3_check"]) ? "true" : "false").");\n");
    
    if ( $_REQUEST["phpbb3_allow"] )
    {
        fwrite( $phpbb3_config, "\tdefine(\"PHPBB3_DATABASE\", \"".$_REQUEST["phpbb3_database"]."\");\n");
        fwrite( $phpbb3_config, "\tdefine(\"PHPBB3_USER\", \"".$_REQUEST["phpbb3_user"]."\");\n");
        fwrite( $phpbb3_config, "\tdefine(\"PHPBB3_PASS\", \"".$_REQUEST["phpbb3_password"]."\");\n");
        fwrite( $phpbb3_config, "\tdefine(\"PHPBB3_TABLE_PREFIX\", \"".$_REQUEST["phpbb3_prefix"]."\");\n");
    
        fwrite( $phpbb3_config, "\tdefine(\"PHPBB3_MEMBER_GROUPS\", \"".implode( ",", $_REQUEST["phpbb3_member"] )."\");\n");
        fwrite( $phpbb3_config, "\tdefine(\"PHPBB3_RAIDLEAD_GROUPS\", \"".implode( ",", $_REQUEST["phpbb3_raidlead"] )."\");\n");
    }
    
    fwrite( $phpbb3_config, "?>");    
    fclose( $phpbb3_config );
    
    // eqdkp
    
    fwrite( $eqdkp_config, "<?php\n");
    fwrite( $eqdkp_config, "\tdefine(\"EQDKP_BINDING\", ".(($_REQUEST["eqdkp_check"]) ? "true" : "false").");\n");
    
    if ( $_REQUEST["eqdkp_allow"] )
    {
        fwrite( $eqdkp_config, "\tdefine(\"EQDKP_DATABASE\", \"".$_REQUEST["eqdkp_database"]."\");\n");
        fwrite( $eqdkp_config, "\tdefine(\"EQDKP_USER\", \"".$_REQUEST["eqdkp_user"]."\");\n");
        fwrite( $eqdkp_config, "\tdefine(\"EQDKP_PASS\", \"".$_REQUEST["eqdkp_password"]."\");\n");
        fwrite( $eqdkp_config, "\tdefine(\"EQDKP_TABLE_PREFIX\", \"".$_REQUEST["eqdkp_prefix"]."\");\n");
    }
    
    fwrite( $eqdkp_config, "?>");    
    fclose( $eqdkp_config );
    
    // vBulletin
    
    fwrite( $vb3_config, "<?php\n");
    fwrite( $vb3_config, "\tdefine(\"VB3_BINDING\", ".(($_REQUEST["vb3_check"]) ? "true" : "false").");\n");
    
    if ( $_REQUEST["vb3_allow"] )
    {
        fwrite( $vb3_config, "\tdefine(\"VB3_DATABASE\", \"".$_REQUEST["vb3_database"]."\");\n");
        fwrite( $vb3_config, "\tdefine(\"VB3_USER\", \"".$_REQUEST["vb3_user"]."\");\n");
        fwrite( $vb3_config, "\tdefine(\"VB3_PASS\", \"".$_REQUEST["vb3_password"]."\");\n");
        fwrite( $vb3_config, "\tdefine(\"VB3_TABLE_PREFIX\", \"".$_REQUEST["vb3_prefix"]."\");\n");
    
        fwrite( $vb3_config, "\tdefine(\"VB3_MEMBER_GROUPS\", \"".implode( ",", $_REQUEST["vb3_member"] )."\");\n");
        fwrite( $vb3_config, "\tdefine(\"VB3_RAIDLEAD_GROUPS\", \"".implode( ",", $_REQUEST["vb3_raidlead"] )."\");\n");
    }
    
    fwrite( $vb3_config, "?>");    
    fclose( $vb3_config );
    
    echo "</bindings>";    
?>