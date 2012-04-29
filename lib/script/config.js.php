<?php
	header("Content-type: text/javascript");
	require_once("../private/connector.class.php");

	$Connector = Connector::GetInstance();
	$Settings = $Connector->prepare("Select `Name`, `TextValue`, `IntValue` FROM `".RP_TABLE_PREFIX."Setting` WHERE ".
		"Name=\"Site\" OR ".
		"Name=\"Banner\" OR ".
		"Name=\"TimeFormat\"" );

    if ( $Settings->execute() )
    {
    	$Site = "";
    	$Banner = "cataclysm";
    	$TimeFormat = 24;
    	
        while ( $Data = $Settings->fetch( PDO::FETCH_ASSOC ) )
        {
        	switch( $Data["Name"] )
        	{
        	case "Site":
        		$Site = $Data["TextValue"];
        		break;
        	
        	case "Banner":
        		$Banner = $Data["TextValue"];
        		break;
        	
        	case "TimeFormat":
        		$TimeFormat = $Data["IntValue"];
        		break;
        	
        	default:
        		break;
        	};
        }
    }
    	
    $Settings->closeCursor();
    
    if ( !file_exists("../../images/banner/".$Banner.".jpg") )
    	$Banner = "cataclysm";
?>

var g_SiteVersion = <?php echo intval($_REQUEST["version"]) ?>;
var g_Banner = "images/banner/<?php echo $Banner; ?>.jpg";
var g_BannerLink = "<?php echo $Site; ?>";
var g_TimeFormat = <?php echo $TimeFormat; ?>;

// -----------------------------------------------------------------------------

function onChangeConfig()
{
	// Create logo
	
	$("#logo").detach();
	
	if ( g_BannerLink != "" )
		$("#menu").before("<a id=\"logo\" href=\"" + g_BannerLink + "\"></a>");
	else
		$("#menu").before("<div id=\"logo\"></div>");
	
	$("#logo").css("background-image", "url(" + g_Banner + ")");
	
	// Create raid time fields
	
	if ( g_User.isAdmin || g_User.isRaidlead )
	{	
		var HTMLString = "";
		
		for ( i=4; i>=0; --i )
			HTMLString += "<option value=\"" + i + "\">" + formatHourPrefixed(i) + "</option>";
			
		for ( i=23; i>4; --i )
			HTMLString += "<option value=\"" + i + "\">" + formatHourPrefixed(i) + "</option>";
		
		var HourFieldWidth        = (g_TimeFormat == 24) ? 48 : 64;
		var LocationFieldWidth    = (g_TimeFormat == 24) ? 192 : 224;
		var DescriptionFieldWidth = (g_TimeFormat == 24) ? 310 : 342;
		
		$("#starthour")
			.css("width", HourFieldWidth)
			.empty().append(HTMLString);
			
		$("#endhour")
			.css("width", HourFieldWidth)
			.empty().append(HTMLString);
		
		$("#selectlocation")
			.css("width", LocationFieldWidth);
		
		$("#descriptiondummy")
			.css("width", DescriptionFieldWidth)
			.css("max-width", DescriptionFieldWidth);
		
		$("#description")
			.css("width", DescriptionFieldWidth)
			.css("max-width", DescriptionFieldWidth);
	}
}

// -----------------------------------------------------------------------------

function formatTime(a_Hour, a_Minute)
{
	if ( g_TimeFormat == 12 )
	{
		var numericHour = parseInt(a_Hour);
		var postFix = " pm";
		
		if ( numericHour < 12 )
			postFix = " am";
		else
			numericHour -= 12;
		
		
		if ( numericHour == 0 )
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
		var numericHour = parseInt(a_Hour);
		var preFix = "pm ";
		
		if ( numericHour < 12 )
			preFix = "am ";
		else
			numericHour -= 12;
		
		
		if ( numericHour == 0 )
			return preFix + "12";
		
		return preFix + numericHour;
	}
	
	return a_Hour;
}