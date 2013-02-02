<?php
    $loader_files = Array(        
        "jquery-ui-1.10.0.custom.min.css",
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
        
        foreach ( $loader_files as $file )
        {
            echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"lib/layout/".$file."\"/>\n";
        }
    }
    else
    {
        // "Release mode"
        // One file to rule them all to speed up loading
        
        header("Content-type: text/css");
        
        foreach ( $loader_files as $loader_current_file )
        {
            readfile($loader_current_file);
            echo "\n";            
        }
    }
?>