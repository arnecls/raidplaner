function menuAutoLoad()
{
	if ( $(window).data("ignore_hashchange") != true )
	{
		var autoLoad = window.location.href.substring( window.location.href.lastIndexOf("#")+1,  window.location.href.length );
		
		switch ( autoLoad )
		{
		case "raid":
			changeContext( autoLoad );
			loadAllRaids();
			break;
			
		case "profile":
			changeContext( autoLoad );
			loadProfile();
			break;
			
		case "settings":
			changeContext( autoLoad );
			loadSettings();
			break;
		
		case "calendar":
		default:
			changeContext( "calendar" );
			loadDefaultCalendar();
		}
	}
		
	$(window).data("ignore_hash", false);
}

function initMenu()
{	
	$("#button_calendar")
		.click( onChangeContext )
		.data( "loadFunction", "loadDefaultCalendar()" );
	
	$("#button_raid")
		.click( onChangeContext )
		.data( "loadFunction", "loadAllRaids()" );
	
	$("#button_profile")
		.click( onChangeContext )
		.data( "loadFunction", "loadProfile()" );
		
	$("#button_settings")
		.click( onChangeContext )
		.data( "loadFunction", "loadSettings()" );
			
	$("#button_calendar").addClass("on");
	
	$(window).bind('hashchange', menuAutoLoad );
	menuAutoLoad();
}

function initLogin()
{
}