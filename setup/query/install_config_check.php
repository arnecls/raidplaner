<?php
    header("Content-type: text/xml");
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
    
    define( "LOCALE_SETUP", true );
    require_once("../../lib/private/connector.class.php");
    require_once("../../lib/private/out.class.php");
    
    $Out = Out::getInstance();
    $TestConnection = new Connector( $_REQUEST["host"], $_REQUEST["database"], $_REQUEST["user"], $_REQUEST["password"] );
    $Out->writeXML("test");
?>