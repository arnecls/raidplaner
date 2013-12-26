<?php
    
    // Helper class for loaded plugins
    class PluginRegistry
    {
        public static $Classes = array();
        public static $Instances = array();
        
        // ---------------------------------------------------------------------
        
        public static function Init()
        {
            foreach(self::$Classes as $PluginName)
            {
                $Plugin = new ReflectionClass($PluginName);
                $PluginInstance = $Plugin->newInstance();
                array_push(self::$Instances, $PluginInstance);
            }
        }
        
        // ---------------------------------------------------------------------
        
        public static function ForEachPlugin($aFunction)
        {
            if (sizeof(self::$Classes) == 0)
                return;
            
            if (sizeof(self::$Instances) == 0)
                self::Init();    
            
            foreach(self::$Instances as $PluginInstance)
            {
                if ($aFunction($PluginInstance) === false)
                    return;
            }
        }
    }
    
    // -------------------------------------------------------------------------
    
    // load files from the bindings folder
    if ($FolderHandle = opendir(dirname(__FILE__)."/bindings")) 
    {
        while (($PluginFile = readdir($FolderHandle)) !== false) 
        {
            $FileParts = explode(".",$PluginFile);            
            if (strtolower($FileParts[sizeof($FileParts)-1]) == "php")
                require_once dirname(__FILE__)."/bindings/".$PluginFile;    
        }
    }
?>