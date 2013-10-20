<?php
    $LoaderFiles = Array(        
        "jquery-ui-1.10.3.custom.min.css",
        "default.css",
        "combobox.css",
        "calendar.css",
        "raid.css",
        "raidlist.css",
        "profile.css",
        "tooltip.css",
        "shadow.css",
        "sheet.css",
        "settings.css");
        
    if ( defined("STYLE_DEBUG") )
    {
        // "Debug mode"
        // Load each file separately for easier debugging.
        
        foreach ( $LoaderFiles as $File )
        {
            echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"lib/layout/".$File."\"/>\n";
        }
    }
    else
    {
        // "Release mode"
        // One file to rule them all to speed up loading
        
        header("Content-type: text/css");
		header("Cache-Control: public");
        
        foreach ( $LoaderFiles as $LoaderCurrentFile )
        {
            readfile($LoaderCurrentFile);
            echo "\n";            
        }
    }
?>