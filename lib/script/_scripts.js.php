<?php
    require_once(dirname(__FILE__)."/../private/users.php");
    UserProxy::GetInstance(); // Init user
    
    // Scripts that are always loaded
    
    $loader_files_base = Array( 
        "jquery-1.9.0.min.js",
        "jquery-ui-1.10.0.custom.min.js",
        "jquery.ba-hashchange.min.js",
        "config.js.php",
        "messagehub.js",
        "mobile.js",
        "tooltip.js",
        "main.js" );
        
    // Conditional scripts
    // When using release mode, this script should be loaded with an additional,
    // unused paramter so that the browser may correctly cache the two different
    // versions.
        
    if ( RegisteredUser() )
    {
        $loader_files_opt = Array(
            "sheet.js",
            "calendar.js",
            "raid.js",
            "raidlist.js",
            "profile.js",
            "initmenu.js",
            "combobox.js",
            "settings.js",
            "crypto/sha1.js",
            "crypto/sha256.js" );
    }
    else
    {
        $loader_files_opt = Array(
            "login.js",
            "initlogin.js",
            "register.js",
            "crypto/md5.js",
            "crypto/sha1.js",
            "crypto/sha256.js",
            "crypto/sha512.js",
            "crypto/tripledes.js",
            "crypto/bCrypt.js",
            "hash.js");
    }
            
    $loader_files = array_merge( $loader_files_base, $loader_files_opt );
    
    // Load the files, depending on which mode is requested
    
    if ( defined("SCRIPT_DEBUG") )
    {
        // "Debug mode"
        // Load each file separately for easier debugging.
        
        foreach ( $loader_files as $file )
        {
            echo "<script type=\"text/javascript\" src=\"lib/script/".$file."?version=".$siteVersion."\"></script>\n";
        }
    }
    else
    {
        // "Release mode"
        // One file to rule them all to speed up loading
        
        header("Content-type: text/javascript");
        define("UNIFIED_SCRIPT", true);
        
        foreach ( $loader_files as $loader_current_file )
        {
            // Only parse files with php content.
            // If we parse all files, php might terminate execution.
                
            if ( strpos($loader_current_file, ".php") === false )
            {
                readfile($loader_current_file);
            }
            else
            {                
                require_once($loader_current_file);
            }
            
            echo "\n";            
        }
    }
?>