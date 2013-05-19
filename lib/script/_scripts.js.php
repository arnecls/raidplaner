<?php
    require_once(dirname(__FILE__)."/../private/userproxy.class.php");
    UserProxy::getInstance(); // Init user
    
    // Scripts that are always loaded
    
    $Loader_files_base = Array( 
        "jquery-1.9.1.min.js",
        "jquery-ui-1.10.0.custom.min.js",
        "jquery.ba-hashchange.min.js",
        "crypto/md5.js",
        "crypto/sha1.js",
        "crypto/sha256.js",
        "crypto/sha512.js",
        "crypto/tripledes.js",
        "crypto/bCrypt.js",
        "locale.js.php",        
        "config.js.php",
        "messagehub.js",
        "mobile.js",
        "tooltip.js",
        "main.js",
        "hash.js" );
        
    // Conditional scripts
    // When using release mode, this script should be loaded with an additional,
    // unused paramter so that the browser may correctly cache the two different
    // versions.
        
    if ( registeredUser() )
    {
        $Loader_files_opt = Array(
            "calendar.js",
            "sheet.js",
            "raid.js",
            "raidlist.js",
            "profile.js",
            "initmenu.js",
            "combobox.js",
            "settings.js" );
    }
    else
    {
        $Loader_files_opt = Array(
            "login.js",
            "initlogin.js",
            "register.js");
    }
            
    $Loader_files = array_merge( $Loader_files_base, $Loader_files_opt );
    
    // Load the files, depending on which mode is requested
    
    if ( defined("SCRIPT_DEBUG") )
    {
        // "Debug mode"
        // Load each file separately for easier debugging.
        
        foreach ( $Loader_files as $File )
        {
            echo "<script type=\"text/javascript\" src=\"lib/script/".$File."?version=".$gSiteVersion."\"></script>\n";
        }
    }
    else
    {
        // "Release mode"
        // One file to rule them all to speed up loading
        
        header("Content-type: text/javascript");
        define("UNIFIED_SCRIPT", true);
        
        foreach ( $Loader_files as $Loader_current_file )
        {
            // Only parse files with php content.
            // If we parse all files, php might terminate execution.
                
            if ( strpos($Loader_current_file, ".php") === false )
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