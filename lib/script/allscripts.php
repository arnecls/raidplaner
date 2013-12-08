<?php
    require_once(dirname(__FILE__)."/../private/tools_site.php");
    
    $Loader_files = Array( 
		//"jquery-1.10.2.min.js",
		"jquery-2.0.3.min.js",
		"jquery-ui-1.10.3.custom.min.js",
		"jquery.ba-hashchange.min.js",
		"crypto/md5.js",
		"crypto/sha1.js",
		"crypto/sha256.js",
		"crypto/sha512.js",
		"crypto/tripledes.js",
		"crypto/bcrypt.js",
		"time.js",
		"messagehub.js",
		"mobile.js",
		"tooltip.js",
		"main.js",
		"hash.js",
		"calendar.js",
		"sheet.js",
		"raid.js",
		"raidlist.js",
		"profile.js",
		"initmenu.js",
		"combobox.js",
		"settings.js",
		"login.js",
		"register.js" );
		
	if ( defined("SCRIPT_DEBUG") && SCRIPT_DEBUG )
	{
		// "Debug mode"
		// Load each file separately for easier debugging.
	
		foreach ( $Loader_files as $File )
		{
			echo "<script type=\"text/javascript\" src=\"lib/script/".$File."?version=".$gSite["Version"]."\"></script>\n";
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