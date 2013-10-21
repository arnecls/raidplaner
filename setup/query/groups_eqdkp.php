<?php
    define( "LOCALE_SETUP", true );
    require_once(dirname(__FILE__)."/../../lib/private/connector.class.php");
    require_once(dirname(__FILE__)."/../../lib/config/config.php");

    header("Content-type: text/xml");
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
    echo "<grouplist>";
    
    $Out = Out::getInstance();
    $TestConnectionEQDKP = new Connector( SQL_HOST, $_REQUEST["database"], $_REQUEST["user"], $_REQUEST["password"] );
    
    $Out->flushXML("");
    echo "</grouplist>";
?>