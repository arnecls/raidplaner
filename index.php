<?php
    define( "LOCALE_MAIN", true );
    require_once("lib/private/locale.php");
    require_once("lib/private/gameconfig.php");
    
    if ( !isset($_REQUEST["nocheck"]) )
    {
        include_once("oldbrowser.php");
    }
    
    if ( !file_exists("lib/config/config.php") )
    {
        die( L("RaidplanerNotConfigured")."<br>".L("PleaseRunSetup") );
    }
    
    require_once("lib/private/users.php");
    require_once("lib/private/site.php");
    
    UserProxy::GetInstance(); // Init user
    loadSiteSettings();
		     
    $siteVersion = 97;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
    <head>
        <title>Raidplaner</title>
        <meta http-equiv="X-UA-Compatible" content="IE=Edge">
        
        <link rel="icon" href="favicon.png" type="image/png">
        <link rel="stylesheet" type="text/css" href="lib/layout/_layout.css.php?version=<?php echo $siteVersion; ?>"/>
        
        <?php
            //define("STYLE_DEBUG", true);
            //include_once("lib/layout/_layout.css.php");
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
        <!--<script type="text/javascript" src="lib/script/_scripts.js.php?version=<?php echo $siteVersion; ?>&r=<?php echo (RegisteredUser()) ? 1 : 0; ?>"></script>-->
        
        <?php
            define("SCRIPT_DEBUG", true);
            include_once("lib/script/_scripts.js.php");
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
        <div id="appwindow">
            <?php 
                if ( $g_Site["BannerLink"] == "" )
                    echo "<div id=\"logo\" style=\"background-image: url(images/banner/".$g_Site["Banner"].")\"></div>";
                else
                    echo "<a id=\"logo\" href=\"".$g_Site["BannerLink"]."\" style=\"background-image: url(images/banner/".$g_Site["Banner"].")\"></a>";
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
        
        <?php } ?>        
        <?php if ( ValidRaidlead() ) { ?>
        
        <div id="sheetNewRaid">
            <div id="newRaid" style="width:580px">
                <span style="display: inline-block; vertical-align: top; margin-right: 20px">
                    <div id="raiddatepicker"></div>
                </span>  
                <span style="display: inline-block; vertical-align: top">
                    <span style="display: inline-block; margin-right: 5px; float: left" class="imagepicker" id="locationimagepicker"><div class="imagelist" id="locationimagelist"></div></span>
                    <span style="display: inline-block; vertical-align: top">
                        <div style="margin-bottom: 10px">
                            <select id="selectlocation" onchange="onLocationChange(this)">
                                <option value="0"><?php echo L("NewDungeon"); ?></option>
                            </select>
                            <span style="display: inline-block; width: 3px;"></span>
                            <select id="selectsize" style="width: 48px">
                                <?php
                                    while ( list($groupSize,$slots) = each($s_GroupSizes) )
                                    {
                                        echo "<option value=\"".$groupSize."\">".$groupSize."</option>";
                                    }
                                ?>
                            </select>                    
                        </div>
                        <div>
                            <select id="starthour">
                            </select>
                            <span style="display: inline-block; width: 4px; text-align:center; position: relative; top: -5px">:</span>
                            <select id="startminute" style="width: 48px">
                                <option value="0">00</option>
                                <option value="15">15</option>
                                <option value="30">30</option>
                                <option value="45">45</option>
                            </select>
                            <span style="display: inline-block; width: 20px; text-align:center; position: relative; top: -5px"><?php echo L("to"); ?></span>
                            <select id="endhour">
                            </select>
                            <span style="display: inline-block; width: 4px; text-align:center; position: relative; top: -5px">:</span>
                            <select id="endminute" style="width: 48px">
                                <option value="0">00</option>
                                <option value="15">15</option>
                                <option value="30">30</option>
                                <option value="45">45</option>
                            </select>
                        </div>
                    </span>
                    <div style="margin-top: 20px; clear: left">
                        <textarea id="descriptiondummy" class="textdummy description"><?php echo L("Description"); ?></textarea>
                        <textarea id="description" class="textinput description"></textarea>
                    </div>
                    <div style="margin-top: 10px" id="newraidsubmit">
                        <select id="selectmode" style="width: 180px; float: left">
                            <option value="manual"><?php echo L("RaidModeManual"); ?></option>
                            <option value="attend"><?php echo L("RaidModeAttend"); ?></option>
                            <option value="all"><?php echo L("RaidModeAll"); ?></option>
                        </select>
                        <button id="newRaidSubmit" style="float:right"><?php echo L("CreateRaid"); ?></button>                 
                    </div>
                    
                </span>
            </div>            
        </div>    
        <?php } ?>
        <?php if ( !RegisteredUser() ) { ?>
        <div class="preload"><?php include("lib/private/resources.php"); ?></div>
        <?php } ?>
        
    </body>
</html>
