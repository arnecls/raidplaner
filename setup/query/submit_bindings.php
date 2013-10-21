<?php
    header("Content-type: text/xml");
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
    echo "<bindings>";    
    
    define( "LOCALE_SETUP", true );
    require_once("../../lib/private/connector.class.php");
    
    $Phpbb3_config = fopen( "../../lib/config/config.phpbb3.php", "w+" );
    $Eqdkp_config = fopen( "../../lib/config/config.eqdkp.php", "w+" );
    $Vb3_config = fopen( "../../lib/config/config.vb3.php", "w+" );
    $Mybb_config = fopen( "../../lib/config/config.mybb.php", "w+" );
    $Smf_config = fopen( "../../lib/config/config.smf.php", "w+" );
    $Vanilla_config = fopen( "../../lib/config/config.vanilla.php", "w+" );
    $Joomla_config = fopen( "../../lib/config/config.joomla3.php", "w+" );
    $Drupal_config = fopen( "../../lib/config/config.drupal.php", "w+" );
    $Wp_config = fopen( "../../lib/config/config.wp.php", "w+" );
    
    
    // phpbb3
    
    fwrite( $Phpbb3_config, "<?php\n");
    fwrite( $Phpbb3_config, "\tdefine(\"PHPBB3_BINDING\", ".$_REQUEST["phpbb3_allow"].");\n");
    
    if ( $_REQUEST["phpbb3_allow"] )
    {
        fwrite( $Phpbb3_config, "\tdefine(\"PHPBB3_DATABASE\", \"".$_REQUEST["phpbb3_database"]."\");\n");
        fwrite( $Phpbb3_config, "\tdefine(\"PHPBB3_USER\", \"".$_REQUEST["phpbb3_user"]."\");\n");
        fwrite( $Phpbb3_config, "\tdefine(\"PHPBB3_PASS\", \"".$_REQUEST["phpbb3_password"]."\");\n");
        fwrite( $Phpbb3_config, "\tdefine(\"PHPBB3_TABLE_PREFIX\", \"".$_REQUEST["phpbb3_prefix"]."\");\n");
    
        fwrite( $Phpbb3_config, "\tdefine(\"PHPBB3_MEMBER_GROUPS\", \"".implode( ",", $_REQUEST["phpbb3_member"] )."\");\n");
        fwrite( $Phpbb3_config, "\tdefine(\"PHPBB3_RAIDLEAD_GROUPS\", \"".implode( ",", $_REQUEST["phpbb3_raidlead"] )."\");\n");
    }
    
    fwrite( $Phpbb3_config, "?>");    
    fclose( $Phpbb3_config );
    
    // eqdkp
    
    fwrite( $Eqdkp_config, "<?php\n");
    fwrite( $Eqdkp_config, "\tdefine(\"EQDKP_BINDING\", ".$_REQUEST["eqdkp_allow"].");\n");
    
    if ( $_REQUEST["eqdkp_allow"] )
    {
        fwrite( $Eqdkp_config, "\tdefine(\"EQDKP_DATABASE\", \"".$_REQUEST["eqdkp_database"]."\");\n");
        fwrite( $Eqdkp_config, "\tdefine(\"EQDKP_USER\", \"".$_REQUEST["eqdkp_user"]."\");\n");
        fwrite( $Eqdkp_config, "\tdefine(\"EQDKP_PASS\", \"".$_REQUEST["eqdkp_password"]."\");\n");
        fwrite( $Eqdkp_config, "\tdefine(\"EQDKP_TABLE_PREFIX\", \"".$_REQUEST["eqdkp_prefix"]."\");\n");
    }
    
    fwrite( $Eqdkp_config, "?>");    
    fclose( $Eqdkp_config );
    
    // vBulletin
    
    fwrite( $Vb3_config, "<?php\n");
    fwrite( $Vb3_config, "\tdefine(\"VB3_BINDING\", ".$_REQUEST["vb3_allow"].");\n");
    
    if ( $_REQUEST["vb3_allow"] )
    {
        fwrite( $Vb3_config, "\tdefine(\"VB3_DATABASE\", \"".$_REQUEST["vb3_database"]."\");\n");
        fwrite( $Vb3_config, "\tdefine(\"VB3_USER\", \"".$_REQUEST["vb3_user"]."\");\n");
        fwrite( $Vb3_config, "\tdefine(\"VB3_PASS\", \"".$_REQUEST["vb3_password"]."\");\n");
        fwrite( $Vb3_config, "\tdefine(\"VB3_TABLE_PREFIX\", \"".$_REQUEST["vb3_prefix"]."\");\n");
    
        fwrite( $Vb3_config, "\tdefine(\"VB3_MEMBER_GROUPS\", \"".implode( ",", $_REQUEST["vb3_member"] )."\");\n");
        fwrite( $Vb3_config, "\tdefine(\"VB3_RAIDLEAD_GROUPS\", \"".implode( ",", $_REQUEST["vb3_raidlead"] )."\");\n");
    }
    
    fwrite( $Vb3_config, "?>");    
    fclose( $Vb3_config );
    
    // myBB
    
    fwrite( $Mybb_config, "<?php\n");
    fwrite( $Mybb_config, "\tdefine(\"MYBB_BINDING\", ".$_REQUEST["mybb_allow"].");\n");
    
    if ( $_REQUEST["mybb_allow"] )
    {
        fwrite( $Mybb_config, "\tdefine(\"MYBB_DATABASE\", \"".$_REQUEST["mybb_database"]."\");\n");
        fwrite( $Mybb_config, "\tdefine(\"MYBB_USER\", \"".$_REQUEST["mybb_user"]."\");\n");
        fwrite( $Mybb_config, "\tdefine(\"MYBB_PASS\", \"".$_REQUEST["mybb_password"]."\");\n");
        fwrite( $Mybb_config, "\tdefine(\"MYBB_TABLE_PREFIX\", \"".$_REQUEST["mybb_prefix"]."\");\n");
    
        fwrite( $Mybb_config, "\tdefine(\"MYBB_MEMBER_GROUPS\", \"".implode( ",", $_REQUEST["mybb_member"] )."\");\n");
        fwrite( $Mybb_config, "\tdefine(\"MYBB_RAIDLEAD_GROUPS\", \"".implode( ",", $_REQUEST["mybb_raidlead"] )."\");\n");
    }
    
    fwrite( $Mybb_config, "?>");    
    fclose( $Mybb_config );
    
    // SMF
    
    fwrite( $Smf_config, "<?php\n");
    fwrite( $Smf_config, "\tdefine(\"SMF_BINDING\", ".$_REQUEST["smf_allow"].");\n");
    
    if ( $_REQUEST["smf_allow"] )
    {
        fwrite( $Smf_config, "\tdefine(\"SMF_DATABASE\", \"".$_REQUEST["smf_database"]."\");\n");
        fwrite( $Smf_config, "\tdefine(\"SMF_USER\", \"".$_REQUEST["smf_user"]."\");\n");
        fwrite( $Smf_config, "\tdefine(\"SMF_PASS\", \"".$_REQUEST["smf_password"]."\");\n");
        fwrite( $Smf_config, "\tdefine(\"SMF_TABLE_PREFIX\", \"".$_REQUEST["smf_prefix"]."\");\n");
    
        fwrite( $Smf_config, "\tdefine(\"SMF_MEMBER_GROUPS\", \"".implode( ",", $_REQUEST["smf_member"] )."\");\n");
        fwrite( $Smf_config, "\tdefine(\"SMF_RAIDLEAD_GROUPS\", \"".implode( ",", $_REQUEST["smf_raidlead"] )."\");\n");
    }
    
    fwrite( $Smf_config, "?>");    
    fclose( $Smf_config );
    
    // Vanilla
    
    fwrite( $Vanilla_config, "<?php\n");
    fwrite( $Vanilla_config, "\tdefine(\"VANILLA_BINDING\", ".$_REQUEST["vanilla_allow"].");\n");
    
    if ( $_REQUEST["smf_allow"] )
    {
        fwrite( $Vanilla_config, "\tdefine(\"VANILLA_DATABASE\", \"".$_REQUEST["vanilla_database"]."\");\n");
        fwrite( $Vanilla_config, "\tdefine(\"VANILLA_USER\", \"".$_REQUEST["vanilla_user"]."\");\n");
        fwrite( $Vanilla_config, "\tdefine(\"VANILLA_PASS\", \"".$_REQUEST["vanilla_password"]."\");\n");
        fwrite( $Vanilla_config, "\tdefine(\"VANILLA_TABLE_PREFIX\", \"".$_REQUEST["vanilla_prefix"]."\");\n");
    
        fwrite( $Vanilla_config, "\tdefine(\"VANILLA_MEMBER_GROUPS\", \"".implode( ",", $_REQUEST["vanilla_member"] )."\");\n");
        fwrite( $Vanilla_config, "\tdefine(\"VANILLA_RAIDLEAD_GROUPS\", \"".implode( ",", $_REQUEST["vanilla_raidlead"] )."\");\n");
    }
    
    fwrite( $Vanilla_config, "?>");    
    fclose( $Vanilla_config );
    
    // Joomla
    
    fwrite( $Joomla_config, "<?php\n");
    fwrite( $Joomla_config, "\tdefine(\"JML3_BINDING\", ".$_REQUEST["joomla_allow"].");\n");
    
    if ( $_REQUEST["smf_allow"] )
    {
        fwrite( $Joomla_config, "\tdefine(\"JML3_DATABASE\", \"".$_REQUEST["joomla_database"]."\");\n");
        fwrite( $Joomla_config, "\tdefine(\"JML3_USER\", \"".$_REQUEST["joomla_user"]."\");\n");
        fwrite( $Joomla_config, "\tdefine(\"JML3_PASS\", \"".$_REQUEST["joomla_password"]."\");\n");
        fwrite( $Joomla_config, "\tdefine(\"JML3_TABLE_PREFIX\", \"".$_REQUEST["joomla_prefix"]."\");\n");
    
        fwrite( $Joomla_config, "\tdefine(\"JML3_MEMBER_GROUPS\", \"".implode( ",", $_REQUEST["joomla_member"] )."\");\n");
        fwrite( $Joomla_config, "\tdefine(\"JML3_RAIDLEAD_GROUPS\", \"".implode( ",", $_REQUEST["joomla_raidlead"] )."\");\n");
    }
    
    fwrite( $Joomla_config, "?>");    
    fclose( $Joomla_config );
    
    // Drupal
    
    fwrite( $Drupal_config, "<?php\n");
    fwrite( $Drupal_config, "\tdefine(\"DRUPAL_BINDING\", ".$_REQUEST["drupal_allow"].");\n");
    
    if ( $_REQUEST["smf_allow"] )
    {
        fwrite( $Drupal_config, "\tdefine(\"DRUPAL_DATABASE\", \"".$_REQUEST["drupal_database"]."\");\n");
        fwrite( $Drupal_config, "\tdefine(\"DRUPAL_USER\", \"".$_REQUEST["drupal_user"]."\");\n");
        fwrite( $Drupal_config, "\tdefine(\"DRUPAL_PASS\", \"".$_REQUEST["drupal_password"]."\");\n");
        fwrite( $Drupal_config, "\tdefine(\"DRUPAL_TABLE_PREFIX\", \"".$_REQUEST["drupal_prefix"]."\");\n");
    
        fwrite( $Drupal_config, "\tdefine(\"DRUPAL_MEMBER_GROUPS\", \"".implode( ",", $_REQUEST["drupal_member"] )."\");\n");
        fwrite( $Drupal_config, "\tdefine(\"DRUPAL_RAIDLEAD_GROUPS\", \"".implode( ",", $_REQUEST["drupal_raidlead"] )."\");\n");
    }
    
    fwrite( $Drupal_config, "?>");    
    fclose( $Drupal_config );
    
    // wordpress
    
    fwrite( $Wp_config, "<?php\n");
    fwrite( $Wp_config, "\tdefine(\"WP_BINDING\", ".$_REQUEST["wp_allow"].");\n");
    
    if ( $_REQUEST["wp_allow"] )
    {
        fwrite( $Wp_config, "\tdefine(\"WP_DATABASE\", \"".$_REQUEST["wp_database"]."\");\n");
        fwrite( $Wp_config, "\tdefine(\"WP_USER\", \"".$_REQUEST["wp_user"]."\");\n");
        fwrite( $Wp_config, "\tdefine(\"WP_PASS\", \"".$_REQUEST["wp_password"]."\");\n");
        fwrite( $Wp_config, "\tdefine(\"WP_TABLE_PREFIX\", \"".$_REQUEST["wp_prefix"]."\");\n");
    
        fwrite( $Wp_config, "\tdefine(\"WP_MEMBER_GROUPS\", \"".implode( ",", $_REQUEST["wp_member"] )."\");\n");
        fwrite( $Wp_config, "\tdefine(\"WP_RAIDLEAD_GROUPS\", \"".implode( ",", $_REQUEST["wp_raidlead"] )."\");\n");
    }
    
    fwrite( $Wp_config, "?>");    
    fclose( $Wp_config );
    
    echo "</bindings>";    
?>