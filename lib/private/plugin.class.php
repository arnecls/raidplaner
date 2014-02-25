<?php

    require_once dirname(__FILE__)."/api.php";

    // Baseclass for API based plugins.
    // A class derived from this plugin can be placed in the folder 
    // "lib/private/plugins". It will automatically be loaded when adding
    // array_push(PluginRegistry::$Classes, "MyPluginClassName") to the file.
    // If "MyPluginClassName" is not derived from Plugin, it will not be loaded.
    abstract class Plugin
    {
        // This function will be called whenever a new raid has been created.
        // Use Api::queryRaid to retrieve information about the raid.
        abstract public function onRaidCreate($aRaidId);
        
        // This function will be called whenever a new raid has been modified.
        // Use Api::queryRaid to retrieve information about the raid.
        abstract public function onRaidModify($aRaidId);
        
        // This function will be called before a raid will be deleted.
        // Use Api::queryRaid to retrieve information about the raid.
        abstract public function onRaidRemove($aRaidId);
    }
?>