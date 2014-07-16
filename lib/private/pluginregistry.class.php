<?php
    
    require_once dirname(__FILE__).'/binding.class.php';
    require_once dirname(__FILE__).'/plugin.class.php';
    
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
                
                if (!is_subclass_of($PluginInstance, 'Binding') &&
                    !is_subclass_of($PluginInstance, 'Plugin'))
                {
                    continue;
                }
                
                array_push(self::$Instances, $PluginInstance);
            }
        }

        // ---------------------------------------------------------------------

        public static function ForEachBinding($aFunction)
        {
            if (count(self::$Classes) == 0)
                return;

            if (count(self::$Instances) == 0)
                self::Init();

            foreach(self::$Instances as $PluginInstance)
            {
                if (!is_subclass_of($PluginInstance, 'Binding'))
                    continue;
                    
                if ($aFunction($PluginInstance) === false)
                    return;
            }
        }

        // ---------------------------------------------------------------------

        public static function ForEachPlugin($aFunction)
        {
            if (count(self::$Classes) == 0)
                return;

            if (count(self::$Instances) == 0)
                self::Init();

            foreach(self::$Instances as $PluginInstance)
            {
                if (!is_subclass_of($PluginInstance, 'Plugin'))
                    continue;
                    
                $aFunction($PluginInstance);
            }
        }
    }

    // -------------------------------------------------------------------------

    // load files from the bindings folder
    if ($FolderHandle = opendir(dirname(__FILE__).'/bindings'))
    {
        while (($PluginFile = readdir($FolderHandle)) !== false)
        {
            $FileParts = explode('.',$PluginFile);

            if (strtolower($FileParts[count($FileParts)-1]) == 'php')
                require_once dirname(__FILE__).'/bindings/'.$PluginFile;

        }
    }
    
    // load files from the plugins folder
    if ($FolderHandle = opendir(dirname(__FILE__).'/plugins'))
    {
        while (($PluginFile = readdir($FolderHandle)) !== false)
        {
            $FileParts = explode('.',$PluginFile);

            if (strtolower($FileParts[count($FileParts)-1]) == 'php')
                require_once dirname(__FILE__).'/plugins/'.$PluginFile;

        }
    }
?>