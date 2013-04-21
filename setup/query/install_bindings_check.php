<?php
    header("Content-type: text/xml");
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
    
    define( "LOCALE_SETUP", true );
    require_once("../../lib/private/connector.class.php");
    require_once(dirname(__FILE__)."/../../lib/config/config.php");
    
    echo "<test>";
    
    if ( $_REQUEST["phpbb3_check"] == "true" )
    {
        echo "<name>PHPBB3</name>";
        $TestConnectionPHPBB3 = new Connector( SQL_HOST, $_REQUEST["phpbb3_database"], $_REQUEST["phpbb3_user"], $_REQUEST["phpbb3_password"] );
    }
    
    if ( $_REQUEST["eqdkp_check"] == "true" )
    {
        echo "<name>EQDKP</name>";
        $TestConnectionEQDKP = new Connector( SQL_HOST, $_REQUEST["eqdkp_database"], $_REQUEST["eqdkp_user"], $_REQUEST["eqdkp_password"] );
    }
    
    if ( $_REQUEST["vb3_check"] == "true" )
    {
        echo "<name>vBulletin</name>";
        $TestConnectionVB3 = new Connector( SQL_HOST, $_REQUEST["vb3_database"], $_REQUEST["vb3_user"], $_REQUEST["vb3_password"] );
    }
    
    if ( $_REQUEST["mybb_check"] == "true" )
    {
        echo "<name>myBB</name>";
        $TestConnectionMYBB = new Connector( SQL_HOST, $_REQUEST["mybb_database"], $_REQUEST["mybb_user"], $_REQUEST["mybb_password"] );
    }
    
    if ( $_REQUEST["smf_check"] == "true" )
    {
        echo "<name>SMF</name>";
        $TestConnectionSMF = new Connector( SQL_HOST, $_REQUEST["smf_database"], $_REQUEST["smf_user"], $_REQUEST["smf_password"] );
    }
    
    if ( $_REQUEST["vanilla_check"] == "true" )
    {
        echo "<name>Vanilla</name>";
        $TestConnectionVanilla = new Connector( SQL_HOST, $_REQUEST["vanilla_database"], $_REQUEST["vanilla_user"], $_REQUEST["vanilla_password"] );
    }
    
    echo "</test>";
?>