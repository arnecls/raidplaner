<?php
    define( "LOCALE_SETUP", true );
    require_once(dirname(__FILE__)."/../../lib/private/locale.php");
    require_once(dirname(__FILE__)."/../../lib/private/out.class.php");

    $Out = Out::getInstance();

    header("Content-type: application/json");
    
    if (!unlink(dirname(__FILE__)))
    {
        $Out->pushError(L("FailedRemoveSetup"));
    }
    
    $Out->flushJSON();
?>