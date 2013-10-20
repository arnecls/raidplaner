<?php
    if (!defined("UNIFIED_SCRIPT")) 
    {
        header("Content-type: text/javascript");
        header("Cache-Control: public");
    }
    
    require_once("../private/connector.class.php");
    require_once("../private/gameconfig.php");
    require_once("../private/tools_site.php");
    
    loadSiteSettings();
?>

var gSiteVersion = <?php echo floatval($_REQUEST["version"]) ?>;
var gBannerLink = "<?php echo $gSite["BannerLink"]; ?>";
var gHelpLink = "<?php echo $gSite["HelpLink"]; ?>";
var gTimeFormat = <?php echo $gSite["TimeFormat"]; ?>;

var gTheme = {
    background : "<?php echo $gSite["Background"]; ?>",
    banner     : "<?php echo $gSite["Banner"]; ?>",
    bgrepeat   : "<?php echo $gSite["BGRepeat"]; ?>",
    bgcolor    : "<?php echo $gSite["BGColor"]; ?>",
    portalmode : "<?php echo $gSite["PortalMode"]; ?>"
};

var gRoleNames       = new Array(<?php echo sizeof($gRoles); ?>);
var gRoleIds         = new Array(<?php echo sizeof($gRoles); ?>);
var gRoleIdents      = new Array(<?php echo sizeof($gRoles); ?>);
var gRoleImages      = new Array(<?php echo sizeof($gRoles); ?>);
var gRoleColumnCount = new Array(<?php echo sizeof($gRoles); ?>);
var gClasses         = new Array(<?php echo sizeof($gClasses); ?>);
var gClassIdx        = new Array(<?php echo sizeof($gClasses); ?>);
var gGroupSizes      = new Array(<?php
    for ($i=0; list($Count,$RoleSizes) = each($gGroupSizes); ++$i)
    {
        if ($i>0) echo ",";
        echo $Count;
    }
    reset($gGroupSizes);
?>);

<?php
    for ( $i=0; list($RoleIdent,$RoleName) = each($gRoles); ++$i )
    {
        echo "gRoleNames[\"".$RoleIdent."\"] = L(\"".$RoleName."\");\n";
        echo "gRoleIds[\"".$RoleIdent."\"] = ".$i.";\n";
        echo "gRoleIdents[".$i."] = \"".$RoleIdent."\";\n";
        echo "gRoleImages[".$i."] = \"".$gRoleImages[$i]."\";\n";
        echo "gRoleColumnCount[".$i."] = \"".$gRoleColumnCount[$i]."\";\n";
    }
    reset($gRoles);
?>

<?php

    for ( $i=0; list($ClassIdent,$ClassConfig) = each($gClasses); ++$i )
    {
        echo "gClassIdx[\"".$ClassIdent."\"] = ".$i."; ";
        echo "gClasses[".$i."] = {";
        echo "ident : \"".$ClassIdent."\", ";
        echo "text : L(\"".$ClassConfig[0]."\"), ";
        echo "defaultRole : \"".$ClassConfig[1]."\", ";
        echo "roles : Array(";

        for ( $r=0; $r < sizeof($ClassConfig[2]); ++$r )
        {
            if ($r > 0) echo ",";
            echo "\"".$ClassConfig[2][$r]."\"";
        }

        echo ")};\n";
    }
    reset($gClasses);
?>

// -----------------------------------------------------------------------------

function onChangeConfig()
{
    // Update logo

    $("#logo").detach();

    if ( gTheme.banner.toLowerCase() != "disable"  )
    {
        if ( gBannerLink !== "" )
            $("#menu").before("<a id=\"logo\" href=\"" + gBannerLink + "\"></a>");
        else
            $("#menu").before("<div id=\"logo\"></div>");
            
        var bannerImage = (gTheme.banner.toLowerCase() != "none")
            ? "url(images/banner/" + gTheme.banner + ")"
            : "none";

        $("#logo").css("background-image", bannerImage);
    }
    
    // Update help button
    
    $("#help").detach();
    
    if ( gHelpLink != "" )
    {
        var HTMLString = "<span id=\"help\">";
        HTMLString    += "<button onclick=\"openLink('"+gHelpLink+"')\" class=\"button_help\"></button>";
        HTMLString    += "</span>";
        
        $(".logout").after(HTMLString);
        $(".button_help").button({
            icons: { secondary: "ui-icon-help" }
        }).css( "font-size", 11 );
    }
    
    // Update appwindow class
    
    $("#appwindow").removeClass("portalmode");
    if (gTheme.portalmode)
        $("#appwindow").addClass("portalmode");

    // Update theme

    if ( gTheme.background == "none" )
        $("body").css("background", "none" );
    else
        $("body").css("background", gTheme.bgcolor + " url(images/background/" + gTheme.background + ") " + gTheme.bgrepeat );
}