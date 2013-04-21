<?php
    header("Content-type: text/xml");
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
    echo "<bindings>";    
    
    define( "LOCALE_SETUP", true );
    require_once("../../lib/private/connector.class.php");
    
    $phpbb3_config = fopen( "../../lib/config/config.phpbb3.php", "w+" );
    $eqdkp_config = fopen( "../../lib/config/config.eqdkp.php", "w+" );
    $vb3_config = fopen( "../../lib/config/config.vb3.php", "w+" );
    $mybb_config = fopen( "../../lib/config/config.mybb.php", "w+" );
    $smf_config = fopen( "../../lib/config/config.smf.php", "w+" );
    $vanilla_config = fopen( "../../lib/config/config.vanilla.php", "w+" );
    
    
    // phpbb3
    
    fwrite( $phpbb3_config, "<?php\n");
    fwrite( $phpbb3_config, "\tdefine(\"PHPBB3_BINDING\", ".$_REQUEST["phpbb3_allow"].");\n");
    
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
    fwrite( $eqdkp_config, "\tdefine(\"EQDKP_BINDING\", ".$_REQUEST["eqdkp_allow"].");\n");
    
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
    fwrite( $vb3_config, "\tdefine(\"VB3_BINDING\", ".$_REQUEST["vb3_allow"].");\n");
    
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
    
    // myBB
    
    fwrite( $mybb_config, "<?php\n");
    fwrite( $mybb_config, "\tdefine(\"MYBB_BINDING\", ".$_REQUEST["mybb_allow"].");\n");
    
    if ( $_REQUEST["mybb_allow"] )
    {
        fwrite( $mybb_config, "\tdefine(\"MYBB_DATABASE\", \"".$_REQUEST["mybb_database"]."\");\n");
        fwrite( $mybb_config, "\tdefine(\"MYBB_USER\", \"".$_REQUEST["mybb_user"]."\");\n");
        fwrite( $mybb_config, "\tdefine(\"MYBB_PASS\", \"".$_REQUEST["mybb_password"]."\");\n");
        fwrite( $mybb_config, "\tdefine(\"MYBB_TABLE_PREFIX\", \"".$_REQUEST["mybb_prefix"]."\");\n");
    
        fwrite( $mybb_config, "\tdefine(\"MYBB_MEMBER_GROUPS\", \"".implode( ",", $_REQUEST["mybb_member"] )."\");\n");
        fwrite( $mybb_config, "\tdefine(\"MYBB_RAIDLEAD_GROUPS\", \"".implode( ",", $_REQUEST["mybb_raidlead"] )."\");\n");
    }
    
    fwrite( $mybb_config, "?>");    
    fclose( $mybb_config );
    
    // SMF
    
    fwrite( $smf_config, "<?php\n");
    fwrite( $smf_config, "\tdefine(\"SMF_BINDING\", ".$_REQUEST["smf_allow"].");\n");
    
    if ( $_REQUEST["smf_allow"] )
    {
        fwrite( $smf_config, "\tdefine(\"SMF_DATABASE\", \"".$_REQUEST["smf_database"]."\");\n");
        fwrite( $smf_config, "\tdefine(\"SMF_USER\", \"".$_REQUEST["smf_user"]."\");\n");
        fwrite( $smf_config, "\tdefine(\"SMF_PASS\", \"".$_REQUEST["smf_password"]."\");\n");
        fwrite( $smf_config, "\tdefine(\"SMF_TABLE_PREFIX\", \"".$_REQUEST["smf_prefix"]."\");\n");
    
        fwrite( $smf_config, "\tdefine(\"SMF_MEMBER_GROUPS\", \"".implode( ",", $_REQUEST["smf_member"] )."\");\n");
        fwrite( $smf_config, "\tdefine(\"SMF_RAIDLEAD_GROUPS\", \"".implode( ",", $_REQUEST["smf_raidlead"] )."\");\n");
    }
    
    fwrite( $smf_config, "?>");    
    fclose( $smf_config );
    
    // Vanilla
    
    fwrite( $vanilla_config, "<?php\n");
    fwrite( $vanilla_config, "\tdefine(\"VANILLA_BINDING\", ".$_REQUEST["vanilla_allow"].");\n");
    
    if ( $_REQUEST["smf_allow"] )
    {
        fwrite( $vanilla_config, "\tdefine(\"VANILLA_DATABASE\", \"".$_REQUEST["vanilla_database"]."\");\n");
        fwrite( $vanilla_config, "\tdefine(\"VANILLA_USER\", \"".$_REQUEST["vanilla_user"]."\");\n");
        fwrite( $vanilla_config, "\tdefine(\"VANILLA_PASS\", \"".$_REQUEST["vanilla_password"]."\");\n");
        fwrite( $vanilla_config, "\tdefine(\"VANILLA_TABLE_PREFIX\", \"".$_REQUEST["vanilla_prefix"]."\");\n");
    
        fwrite( $vanilla_config, "\tdefine(\"VANILLA_MEMBER_GROUPS\", \"".implode( ",", $_REQUEST["vanilla_member"] )."\");\n");
        fwrite( $vanilla_config, "\tdefine(\"VANILLA_RAIDLEAD_GROUPS\", \"".implode( ",", $_REQUEST["vanilla_raidlead"] )."\");\n");
    }
    
    fwrite( $vanilla_config, "?>");    
    fclose( $vanilla_config );
    
    echo "</bindings>";    
?>