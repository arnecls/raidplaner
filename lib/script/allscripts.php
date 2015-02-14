<?php
    require_once(dirname(__FILE__)."/../private/tools_site.php");

    define('COMBINED_NAME', 'raidplaner.js');

    $Loader_files = Array(
        "jquery-2.1.1.min.js",
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
    elseif (PHP_SAPI == "cli")
    {
        // "Combine mode"
        // Load each file, combine in correct order and minify to output raidplaner.js

        echo "combine mode\n\n\n";

        echo "loading javascript... \n";

        $sJavascript = "";
        $sJarFile = 'yuicompressor-2.4.8.jar';

        foreach ( $Loader_files as $sFileName ) {
            if (preg_match("/\.js$/", $sFileName)) {
                $sJsFileContent = file_get_contents($sFileName);
                if (!preg_match("/\/\*!/", $sJsFileContent))
                {
                    $sJavascript .= "/*!\n * {$sFileName} [" . date("Y-m-d H:i:s") . "]\n*/\n";
                }

                $sJavascript .= $sJsFileContent;
                $sJavascript .= "\n\n\n";
            }
        }

        if (!$sJarFile || !file_exists($sJarFile))
        {
            die("yuicompressor not found");
        }

        echo "ready, saving to " . COMBINED_NAME ."\n";

        file_put_contents(COMBINED_NAME, $sJavascript);

        echo "starting minify\n";

        $sCmd = "java -Xmx32m -jar " . escapeshellarg(__DIR__ . DIRECTORY_SEPARATOR . $sJarFile) . ' '
            . COMBINED_NAME . " --charset UTF-8 --type js --nomunge";

        exec($sCmd . ' 2>&1', $aOutput);

        $sMinifiedJavascript = implode("\n", $aOutput);

        file_put_contents(COMBINED_NAME, $sMinifiedJavascript);

        echo "done, saved to " . COMBINED_NAME ."\n";
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
