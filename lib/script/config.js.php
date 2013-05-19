<?php
    if (!defined("UNIFIED_SCRIPT")) header("Content-type: text/javascript");

    require_once("../private/connector.class.php");
    require_once("../private/gameconfig.php");
    require_once("../private/tools_site.php");
    
    loadSiteSettings();
?>

var g_SiteVersion = <?php echo floatval($_REQUEST["version"]) ?>;
var g_BannerLink = "<?php echo $gSite["BannerLink"]; ?>";
var g_TimeFormat = <?php echo $gSite["TimeFormat"]; ?>;

var g_Theme = {
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

    if ( g_Theme.banner.toLowerCase() != "disable"  )
    {
        if ( g_BannerLink !== "" )
            $("#menu").before("<a id=\"logo\" href=\"" + g_BannerLink + "\"></a>");
        else
            $("#menu").before("<div id=\"logo\"></div>");
            
        var bannerImage = (g_Theme.banner.toLowerCase() != "none")
            ? "url(images/banner/" + g_Theme.banner + ")"
            : "none";

        $("#logo").css("background-image", bannerImage);
    }
    
    // Update appwindow class
    
    $("#appwindow").removeClass("portalmode");
    if (g_Theme.portalmode)
        $("#appwindow").addClass("portalmode");

    // Update theme

    if ( g_Theme.background == "none" )
        $("body").css("background", "none" );
    else
        $("body").css("background", g_Theme.bgcolor + " url(images/background/" + g_Theme.background + ") " + g_Theme.bgrepeat );
}

// -----------------------------------------------------------------------------

function formatTime(a_Hour, a_Minute)
{
    if ( g_TimeFormat == 12 )
    {
        var numericHour = parseInt(a_Hour, 10);
        var postFix = " pm";

        if ( numericHour < 12 )
            postFix = " am";
        else
            numericHour -= 12;


        if ( numericHour === 0 )
            return "12:" + a_Minute + postFix;

        return numericHour + ":" + a_Minute + postFix;
    }

    return a_Hour + ":" + a_Minute;
}

// -----------------------------------------------------------------------------

function formatTimeString( a_String )
{
     var separatorIndex = a_String.indexOf(":");

    var hour   = a_String.substr( 0, separatorIndex );
    var minute = a_String.substr( separatorIndex+1 );

    return formatTime( hour, minute );
}

// -----------------------------------------------------------------------------

function formatHourPrefixed( a_Hour )
{
    if ( g_TimeFormat == 12 )
    {
        var numericHour = parseInt(a_Hour, 10);
        var preFix = "pm ";

        if ( numericHour < 12 )
            preFix = "am ";
        else
            numericHour -= 12;


        if ( numericHour === 0 )
            return preFix + "12";

        return preFix + numericHour;
    }

    return a_Hour;
}