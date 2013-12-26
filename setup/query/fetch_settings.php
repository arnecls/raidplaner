<?php
    define( "LOCALE_SETUP", true );
    require_once(dirname(__FILE__)."/../../lib/config/config.php");
    require_once(dirname(__FILE__)."/../../lib/private/connector.class.php");
    require_once(dirname(__FILE__)."/../../lib/private/userproxy.class.php");
    require_once(dirname(__FILE__)."/../../lib/private/out.class.php");

    $Out = Out::getInstance();

    header("Content-type: application/json");
    header("Cache-Control: no-cache, max-age=0, s-maxage=0");

    // Check fields

    $BindingName = $_REQUEST["binding"];
    $LocalePrefix = $BindingName."_";

    $Out->pushValue("binding", $BindingName);

    PluginRegistry::ForEachPlugin( function($PluginInstance) use ($BindingName, $Out)
    {
        if ($PluginInstance->getName() == $BindingName)
        {
            $Value = $PluginInstance->getExternalConfig($_REQUEST["path"]);
            if ($Value != null)
            {
                $Out->pushValue("settings", $Value);
            }
            return false;
        }
    });

    $Out->flushJSON();
?>