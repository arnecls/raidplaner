<?php
    define( "LOCALE_MAIN", true );
    require_once("lib/private/locale.php");
    require_once("lib/private/tools_site.php");
    require_once("lib/private/gameconfig.php");
             
    $gSiteVersion = 98.1;
    
    if ( !isset($_REQUEST["nocheck"]) )
        include_once("oldbrowser.php");
    
    if ( !file_exists("lib/config/config.php") || !checkVersion($gSiteVersion) )
    {
        include_once("runsetup.php");
        die();
    }
    
    require_once("lib/private/userproxy.class.php");
    require_once("lib/private/tools_site.php");
    
    UserProxy::getInstance(); // Init user
    loadSiteSettings();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
    <head>
        <title>Raidplaner</title>
        <meta http-equiv="X-UA-Compatible" content="IE=Edge"/>
        <meta name="keywords" content="raidplaner, ppx"/>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
        
        <link rel="icon" href="favicon.png" type="image/png"/>
        
        <?php
            //define("STYLE_DEBUG", true);
            
            if (defined("STYLE_DEBUG") && STYLE_DEBUG)
                include_once("lib/layout/_layout.css.php");
            else
                echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"lib/layout/_layout.css.php?version=".$gSiteVersion."\"/>";
        ?>
        
        <!--[if IE 9]>
        <link rel="stylesheet" type="text/css" href="lib/layout/shadowIE.css?version=<?php echo $gSiteVersion; ?>"/>
        <![endif]-->
        
        <!--[if IE 8]>
        <link rel="stylesheet" type="text/css" href="lib/layout/tooltipIE.css?version=<?php echo $gSiteVersion; ?>"/>
        <link rel="stylesheet" type="text/css" href="lib/layout/shadowIE.css?version=<?php echo $gSiteVersion; ?>"/>
        <link rel="stylesheet" type="text/css" href="lib/layout/sheetIE.css?version=<?php echo $gSiteVersion; ?>"/>
        <![endif]-->
        
        <!--[if IE 7]>
        <link rel="stylesheet" type="text/css" href="lib/layout/tooltipIE.css?version=<?php echo $gSiteVersion; ?>"/>
        <link rel="stylesheet" type="text/css" href="lib/layout/shadowIE.css?version=<?php echo $gSiteVersion; ?>"/>
        <link rel="stylesheet" type="text/css" href="lib/layout/sheetIE.css?version=<?php echo $gSiteVersion; ?>"/>
        <![endif]-->
        
        <script type="text/javascript" src="lib/script/locale.js.php?version=<?php echo $gSiteVersion; ?>"></script>
        <script type="text/javascript" src="lib/script/_session.js.php?version=<?php echo $gSiteVersion; ?>"></script>
        <script type="text/javascript" src="lib/script/config.js.php?version=<?php echo $gSiteVersion; ?>"></script>
                
        <?php
            //define("SCRIPT_DEBUG", true);
            
            if (defined("SCRIPT_DEBUG") && SCRIPT_DEBUG)
                include_once("lib/script/_scripts.js.php");
            else
                echo "<script type=\"text/javascript\" src=\"lib/script/_scripts.js.php?version=".$gSiteVersion.".&r=".((registeredUser()) ? 1 : 0)."\"></script>";
        ?>
        
        <?php
            if ( isset($_REQUEST["user"]) && 
                 isset($_REQUEST["pass"]) && 
                 !registeredUser() )
            {
                echo "<script type=\"text/javascript\">gAfterInit = function() { notify(L(\"WrongPassword\")); };</script>";
            }
        ?>
        
    </head>
   
    <body style="background: <?php echo $gSite["BGColor"] ?> <?php echo ($gSite["Background"] == "none") ? "none" : "url(images/background/".$gSite["Background"].")" ?> <?php echo $gSite["BGRepeat"] ?>">
        <div id="appwindow"<?php if ($gSite["PortalMode"]) echo " class=\"portalmode\""; ?>>
            <?php
                if (strtolower($gSite["Banner"]) != "disable")
                {
                    $BannerImage = (strtolower($gSite["Banner"]) != "none") 
                        ? "url(images/banner/".$gSite["Banner"].")"
                        : "none";
                        
                    if ( $gSite["BannerLink"] == "" )
                        echo "<div id=\"logo\" style=\"background-image: ".$BannerImage."\"></div>";
                    else
                        echo "<a id=\"logo\" href=\"".$gSite["BannerLink"]."\" style=\"background-image: ".$BannerImage.")\"></a>";
                }
            ?>
            
            <div id="menu">
                <?php if ( registeredUser() ) { ?>
                
                <span class="logout">
                    <form id="logout" method="post" action="index.php">
                        <input type="hidden" name="nocheck"/>
                        <input type="hidden" name="logout"/>
                        <button onclick="return onLogOut()" class="button_logout"><?php echo L("Logout"); ?></button>
                    </form>
                </span>
                <span id="button_calendar" class="menu_button"><?php echo L("Calendar"); ?></span>
                <span id="button_raid" class="menu_button"><?php echo L("Raid"); ?></span>
                <span id="button_profile" class="menu_button"><?php echo L("Profile"); ?></span>
                
                    <?php if ( validAdmin() ) { ?>
                <span id="button_settings_users" class="menu_button"><?php echo L("Settings"); ?></span>
                    <?php } ?>
                
                <?php } else { ?>
                
                <span id="button_login" class="menu_button"><?php echo L("Login"); ?></span>
                    <?php if ( ALLOW_REGISTRATION ) { ?>
                <span id="button_register" class="menu_button"><?php echo L("Register"); ?></span>
                    <?php } ?>
                
                <?php } ?>
            </div>
            <div id="body">
                <?php 
                    if ( !validUser() && registeredUser() )
                    {
                        echo "<div id=\"lockMessage\">";
                        echo L("AccountIsLocked")."<br/>";
                        echo L("ContactAdminToUnlock");
                        echo "</div>";
                    }
                ?>
            </div>
            
            <span id="version"><?php echo "version ".intVal($gSiteVersion / 100).".".intVal(($gSiteVersion % 100) / 10).".".intVal($gSiteVersion % 10).(($gSiteVersion - intval($gSiteVersion) > 0) ? chr(round(($gSiteVersion - intval($gSiteVersion)) * 10) + ord("a")-1) : ""); ?></span>
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
        
        <?php if ( registeredUser() ) { ?>
        
        <table id="tooltip" cellspacing="0" border="0">
            <tr class="top">
                <td class="left"></td>
                <td class="center" id="info_arrow_tl"></td>
                <td class="center" id="info_arrow_tr"></td>
                <td class="right"></td>
            </tr>
            <tr class="middle">
                <td class="left" id="info_arrow_ml"></td>
                <td class="center" colspan="2" rowspan="2" id="info_text"></td>
                <td class="right"></td>
            </tr>
            <tr class="middle2">
                <td class="left" id="info_arrow_ml2"></td>
                <td class="right"></td>
            </tr>
            <tr class="bottom">
                <td class="left"></td>
                <td class="center" id="info_arrow_bl"></td>    
               <td class="center" id="info_arrow_br"></td>
                <td class="right"></td>
            </tr>
        </table>
        
        <table id="sheetoverlay" cellspacing="0" border="0">
            <tr class="top">
                <td class="left" id="closesheet"></td>
                <td class="center"></td>                
                <td class="right"></td>
            </tr>
            <tr class="middle">
                <td class="left"></td>
                <td class="center" id="sheet_body"></td>
                <td class="right"></td>
            </tr>
            <tr class="bottom">
                <td class="left"></td>
                <td class="center"></td>
                <td class="right"></td>
            </tr>
        </table>
        
        <?php } else { ?>
        <div class="preload"><?php include("lib/private/resources.php"); ?></div>
        <?php } ?>
        
    </body>
</html>
