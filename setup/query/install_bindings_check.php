<?php
    header("Content-type: text/xml");
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
    
    define( "LOCALE_SETUP", true );
    
    require_once(dirname(__FILE__)."/../../lib/config/config.php");
    require_once("../../lib/private/connector.class.php");
    require_once("../../lib/private/userproxy.class.php");
    
    $Out = Out::getInstance();
    
    echo "<test>";
    
    PluginRegistry::ForEachPlugin( function($PluginInstance) use ($Out)
    {
        $Binding = $PluginInstance->getName();
        
        if ( $_REQUEST[$Binding."_check"] == "true" )
        {
            echo "<name>".$Binding."</name>";
            
            try
            {
                $PluginInstance->getGroups($_REQUEST[$Binding."_database"], $_REQUEST[$Binding."_prefix"], $_REQUEST[$Binding."_user"], $_REQUEST[$Binding."_password"], true);
            }
            catch (PDOException $Exception)
            {
                $Out->pushError($Exception->getMessage());
            }
            
            $Out->FlushXML("");
        }
    });
    
    echo "</test>";
?>