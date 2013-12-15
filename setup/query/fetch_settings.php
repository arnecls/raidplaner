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
    
    foreach(PluginRegistry::$Classes as $PluginName)
    {
        $Plugin = new ReflectionClass($PluginName);
        $PluginInstance = $Plugin->newInstance();
        
        if ($PluginInstance->BindingName == $BindingName)
        {
            $Value = $PluginInstance->queryExternalConfig($_REQUEST["path"]);
            if ($Value != null)
            {
                $Out->pushValue("settings", $Value);
            }
            break;
        }
    }
    
    $Out->flushJSON();
?>