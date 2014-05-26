<?php
    define("LOCALE_MAIN", true);
    define("STYLE_DEBUG", false);
    define("SCRIPT_DEBUG", false);

    require_once("lib/private/locale.php");
    require_once("lib/private/tools_site.php");

    // Old browser check

    if (!isset($_GET["nocheck"]))
        include_once("oldbrowser.php");

    // Update or setup required check

    if ( !file_exists("lib/config/config.php") || !checkVersion($gVersion) )
    {
        include_once("runsetup.php");
        die();
    }

    // Site framework

    loadSiteSettings();
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
    <head>
        <title>Raidplaner</title>
        <meta http-equiv="X-UA-Compatible" content="IE=Edge"/>
        <meta name="keywords" content="raidplaner, ppx"/>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>

        <link rel="icon" href="favicon.png" type="image/png"/>
        <link rel="stylesheet" type="text/css" href="lib/layout/allstyles.php?v=<?php echo $gSite["Version"].((STYLE_DEBUG) ? "&debug" : ""); ?>"/>

        <?php // Load scripts

            if (defined("SCRIPT_DEBUG") && SCRIPT_DEBUG)
            {
                include_once("lib/script/allscripts.php");
            }
            else
            {
                echo "<script type=\"text/javascript\" src=\"lib/script/raidplaner.js?v=".$gSite["Version"]."\"></script>";
            }
        ?>
    </head>

    <body>
        <div id="appwindow"<?php if ($gSite["PortalMode"]) echo " class=\"portalmode\""; ?>>
            <div id="banner"></div>
            <div id="menu"></div>
            <div id="body"></div>

            <span id="version"><?php echo "version ".intVal($gSite["Version"] / 100).".".intVal(($gSite["Version"] % 100) / 10).".".intVal($gSite["Version"] % 10).(($gSite["Version"] - intval($gSite["Version"]) > 0) ? chr(round(($gSite["Version"] - intval($gSite["Version"])) * 10) + ord("a")-1) : ""); ?></span>
        </div>

        <div id="eventblocker"></div>
        <div id="dialog"></div>
        <div id="ajaxblocker">
            <div class="background"></div>
            <div class="notification ui-corner-all">
                <img src="lib/layout/images/busy.gif"/><br/><br/>
                <?php echo L("Busy"); ?>
            </div>
        </div>
        <div id="tooltip">
            <div id="tooltip_arrow"></div>
            <div id="info_text"></div>
        </div>
        <div id="sheetoverlay">
            <div id="closesheet" class="clickable"></div>
            <div id="sheet_body"></div>
        </div>
    </body>
</html>
