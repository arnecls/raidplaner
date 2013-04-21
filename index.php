<?php
    define( "LOCALE_MAIN", true );
    require_once("lib/private/locale.php");
    require_once("lib/private/tools_site.php");
    require_once("lib/private/gameconfig.php");
		     
    $siteVersion = 98;
    
    if ( !isset($_REQUEST["nocheck"]) )
        include_once("oldbrowser.php");
    
    if ( !file_exists("lib/config/config.php") || !CheckVersion($siteVersion) )
    {
        include_once("runsetup.php");
        die();
    }
    
    require_once("lib/private/userproxy.class.php");
    require_once("lib/private/tools_site.php");
    
    UserProxy::GetInstance(); // Init user
    loadSiteSettings();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
    <head>
        <title>Raidplaner</title>
        <meta http-equiv="X-UA-Compatible" content="IE=Edge">
        <meta name="keywords" content="raidplaner, ppx">
        
        <link rel="icon" href="favicon.png" type="image/png">
        
        <?php
            define("STYLE_DEBUG", true);
            
            if (defined("STYLE_DEBUG") && STYLE_DEBUG)
                include_once("lib/layout/_layout.css.php");
            else
                echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"lib/layout/_layout.css.php?version=".$siteVersion."\"/>";
        ?>
        
        <!--[if IE 9]>
        <link rel="stylesheet" type="text/css" href="lib/layout/shadowIE.css?version=<?php echo $siteVersion; ?>"/>
        <![endif]-->
        
        <!--[if IE 8]>
        <link rel="stylesheet" type="text/css" href="lib/layout/tooltipIE.css?version=<?php echo $siteVersion; ?>"/>
        <link rel="stylesheet" type="text/css" href="lib/layout/shadowIE.css?version=<?php echo $siteVersion; ?>"/>
        <link rel="stylesheet" type="text/css" href="lib/layout/sheetIE.css?version=<?php echo $siteVersion; ?>"/>
        <![endif]-->
        
        <!--[if IE 7]>
        <link rel="stylesheet" type="text/css" href="lib/layout/tooltipIE.css?version=<?php echo $siteVersion; ?>"/>
        <link rel="stylesheet" type="text/css" href="lib/layout/shadowIE.css?version=<?php echo $siteVersion; ?>"/>
        <link rel="stylesheet" type="text/css" href="lib/layout/sheetIE.css?version=<?php echo $siteVersion; ?>"/>
        <![endif]-->
        
        
        <script type="text/javascript" src="lib/script/locale.js.php?version=<?php echo $siteVersion; ?>"></script>
        <script type="text/javascript" src="lib/script/_session.js.php?version=<?php echo $siteVersion; ?>"></script>
        
        <?php
            define("SCRIPT_DEBUG", true);
            
            if (defined("SCRIPT_DEBUG") && SCRIPT_DEBUG)
                include_once("lib/script/_scripts.js.php");
            else
                echo "<script type=\"text/javascript\" src=\"lib/script/_scripts.js.php?version=".$siteVersion.".&r=".((RegisteredUser()) ? 1 : 0)."\"></script>";
        ?>
        
        <?php
            if ( isset($_REQUEST["user"]) && 
                 isset($_REQUEST["pass"]) && 
                 !RegisteredUser() )
            {
                echo "<script type=\"text/javascript\">g_AfterInit = function() { notify(L(\"WrongPassword\")); };</script>";
            }
        ?>
        
    </head>
   
    <body style="background: <?php echo $g_Site["BGColor"] ?> <?php echo ($g_Site["Background"] == "none") ? "none" : "url(images/background/".$g_Site["Background"].")" ?> <?php echo $g_Site["BGRepeat"] ?>">
        <div id="appwindow"<?php if ($g_Site["PortalMode"]) echo " class=\"portalmode\""; ?>>
            <?php
                if (strtolower($g_Site["Banner"]) != "disable")
                {
                    $bannerImage = (strtolower($g_Site["Banner"]) != "none") 
                        ? "url(images/banner/".$g_Site["Banner"].")"
                        : "none";
                        
                    if ( $g_Site["BannerLink"] == "" )
                        echo "<div id=\"logo\" style=\"background-image: ".$bannerImage."\"></div>";
                    else
                        echo "<a id=\"logo\" href=\"".$g_Site["BannerLink"]."\" style=\"background-image: ".$bannerImage.")\"></a>";
                }
            ?>
            
            <div id="menu">
                <?php if ( RegisteredUser() ) { ?>
                
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
                
                    <?php if ( ValidAdmin() ) { ?>
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
                    if ( !ValidUser() && RegisteredUser() )
                    {
                        echo "<div id=\"lockMessage\">";
                        echo L("AccountIsLocked")."<br/>";
                        echo L("ContactAdminToUnlock");
                        echo "</div>";
                    }
                ?>
            </div>
            <span id="version"><?php echo "version ".intVal($siteVersion / 100).".".intVal(($siteVersion % 100) / 10).".".intVal($siteVersion % 10); ?></span>
        </div>
        
        <div id="eventblocker"></div>
        <div id="dialog"></div>
        <div id="ajaxblocker">
            <div class="background ui-corner-all">
                <img src="lib/layout/images/busy.gif"/><br/><br/>
                <?php echo L("Busy"); ?>
            </div>
        </div>
        
        <?php if ( RegisteredUser() ) { ?>
        
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
