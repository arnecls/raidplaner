<?php
    header("Content-type: text/xml");
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
    echo "<bindings>";    
    
    define( "LOCALE_SETUP", true );
    require_once("../../lib/private/connector.class.php");
    require_once("../../lib/private/userproxy.class.php");
    
    foreach(PluginRegistry::$Classes as $PluginName)
    {
        $Plugin = new ReflectionClass($PluginName);
        $PluginInstance = $Plugin->newInstance();
        $Binding = $PluginInstance->BindingName;
        
        $PluginInstance->writeConfig(
            $_REQUEST[$Binding."_allow"] == "true", 
            $_REQUEST[$Binding."_database"], 
            $_REQUEST[$Binding."_prefix"], 
            $_REQUEST[$Binding."_user"], 
            $_REQUEST[$Binding."_password"], 
            $_REQUEST[$Binding."_member"], 
            $_REQUEST[$Binding."_raidlead"], 
            $_REQUEST[$Binding."_cookie"]);
    }
    
    echo "</bindings>";    
?>