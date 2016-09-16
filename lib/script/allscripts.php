<?php
    require_once(dirname(__FILE__)."/../private/tools_site.php");

    $Loader_files = Array(
        "jquery-2.2.3.min.js",
        "jquery-ui.min.js",
        "ZeroClipboard.min.js",
        "crypto/md5.js",
        "crypto/sha1.js",
        "crypto/sha256.js",
        "crypto/sha512.js",
        "crypto/tripledes.js",
        "crypto/bcrypt.js",
        "mobile.js",
        "combobox.js",
        "hash.js",
        "time.js",
        "messagehub.js",
        "main.js",
        "menu.js",
        "login.js",
        "register.js",
        "tooltip.js",
        "sheet.js",
        "calendar.js",
        "raid.js",
        "raidlist.js",
        "profile.js",
        "settings.js" );

    if ( defined("SCRIPT_DEBUG") && SCRIPT_DEBUG )
    {
        // "Debug mode"
        // Load each file separately for easier debugging.

        foreach ( $Loader_files as $File )
        {
            echo "<script type=\"text/javascript\" src=\"lib/script/".$File."?v=".$gVersion."\"></script>\n";
        }
    }
    else
    {
        // "Release mode"
        // One file to rule them all to speed up loading

        header("Content-type: text/javascript");
        header("Cache-Control: public");
        define("UNIFIED_SCRIPT", true);

        foreach ( $Loader_files as $Loader_current_file )
        {
            // Only parse files with php content.
            // If we parse all files, php might terminate execution.

            if (substr($Loader_current_file, -4) !== ".php")
            {
                readfile($Loader_current_file);
            }
            else
            {
                require_once($Loader_current_file);
            }

            echo "\n";

        }
    }
?>
