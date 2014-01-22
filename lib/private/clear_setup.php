<?php
    define( "LOCALE_SETUP", true );
    require_once(dirname(__FILE__)."/locale.php");
    require_once(dirname(__FILE__)."/out.class.php");

    $Out = Out::getInstance();

    header("Content-type: application/json");
    
    if (!@unlink(dirname(__FILE__)."/../../setup"))
    {
        $Out->pushError(L("FailedRemoveSetup"));
    }
    
    $Out->flushJSON();
?>