<?php

    function msgQueryConfig( $aRequest )
    {
        global $gSite;
        global $gGame;
    
        $Out = Out::getInstance();
        loadGameSettings();
    
        $Config = array();
        $Config["AllowRegistration"] = defined("ALLOW_REGISTRATION") && ALLOW_REGISTRATION;
    
        // Push
        
        unset($gGame["Locales"]);
        
        $Out->pushValue("site", array_merge($gSite, $Config));
        $Out->pushValue("game", $gGame);
    }

?>