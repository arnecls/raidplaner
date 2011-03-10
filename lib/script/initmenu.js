function menuAutoLoad()
{
	if ( ($(window).data("ignore_hashchange") != true) && (g_User != null) )
	{
		var autoLoad = window.location.href.substring( window.location.href.lastIndexOf("#")+1,  window.location.href.length );
		var idIndex  = autoLoad.lastIndexOf(",");
		
		var contextName = (idIndex == -1) ? autoLoad : autoLoad.substr( 0, autoLoad.lastIndexOf(",") );
		var contextId   = (idIndex == -1) ? -1       : autoLoad.substring( autoLoad.lastIndexOf(",")+1, autoLoad.length );
		
		switch ( contextName )
		{
		case "raid":
			if ( contextId > -1 )
				loadRaid( contextId );
			else
			{
				changeContext( contextName );
				loadAllRaids();
			}
			break;
			
		case "profile":
			changeContext( contextName );
			loadProfile();
			break;
			
		case "settings":
			if ( g_User.isAdmin )
			{
				changeContext( contextName );
				loadSettings();
			}
			break;
		
		case "calendar":
		default:
			changeContext( "calendar" );
			loadDefaultCalendar();
		}
	}
		
	$(window).data("ignore_hashchange", false);
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