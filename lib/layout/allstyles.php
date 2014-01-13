<?php
    require_once(dirname(__FILE__)."/../private/tools_site.php");

    header("Content-type: text/css");
    header("Cache-Control: public");

    $LoaderFiles = Array(
        "reset.css",
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
        
    
    if ( isset($_REQUEST["debug"]) )
    {
        // "Debug mode"
        // Load each file separately for easier debugging.

        foreach ( $LoaderFiles as $File )
        {
            echo "@import url(\"".$File."\");\n";
        }
        
        foreach($gSite["Styles"] as $File)
        {
            echo "@import url(\"../../themes/styles/".$File."\");\n";
            
        }
    }
    else
    {
        // "Release mode"
        // One file to rule them all to speed up loading
        
        //$RegexWhitespace = "(?<={)\s+|(?<=;)\s+";
        //$RegexComments   = "\/\*.*\*\/";
        //$RegexEmptyLines = "(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+";        
        //$MinifyRegex = "/".$RegexWhitespace."|".$RegexComments."|".$RegexEmptyLines."/s";

        foreach ( $LoaderFiles as $LoaderCurrentFile )
        {
            readfile($LoaderCurrentFile);
            //$Contents = file_get_contents($LoaderCurrentFile);
            //echo preg_replace($MinifyRegex, "", $Contents);
        }
        
        foreach($gSite["Styles"] as $File)
        {
            readfile("../../themes/styles/".$LoaderCurrentFile);
            //$Contents = file_get_contents("../../themes/styles/".$LoaderCurrentFile);
            //echo preg_replace($MinifyRegex, "", $Contents);
        }
    }
?>