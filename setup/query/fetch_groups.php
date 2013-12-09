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
    
    if ($_REQUEST["database"] == "")
    {
        $Out->pushError(L($LocalePrefix."DatabaseEmpty"));
    }
    else if ($_REQUEST["user"] == "")
    {
        $Out->pushError(L($LocalePrefix."UserEmpty"));
    }
    else if ($_REQUEST["password"] == "")
    {
        $Out->pushError(L($LocalePrefix."PasswordEmpty"));
    }
    else
    {    
        foreach(PluginRegistry::$Classes as $PluginName)
        {
            $Plugin = new ReflectionClass($PluginName);
            $PluginInstance = $Plugin->newInstance();
            
            if ($PluginInstance->BindingName == $BindingName)
            {
                try
                {
                    $Groups = $PluginInstance->getGroups($_REQUEST["database"], $_REQUEST["prefix"], $_REQUEST["user"], $_REQUEST["password"], true);
                    $Out->pushValue("groups", $Groups);
                }
                catch (PDOException $Exception)
                {
                    $Out->pushError($Exception->getMessage());
                }
                
                break;
            }
        }
    }
    
    $Out->flushJSON();
?>