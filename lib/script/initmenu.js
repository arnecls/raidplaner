function menuAutoLoad()
{
	if ( g_User != null )
	{
		var autoLoad = window.location.href.substring( window.location.href.lastIndexOf("#")+1,  window.location.href.length );
		var idIndex  = autoLoad.lastIndexOf(",");
		
		var contextName = (idIndex == -1) ? autoLoad : autoLoad.substr( 0, autoLoad.lastIndexOf(",") );
		var contextId   = (idIndex == -1) ? -1       : autoLoad.substring( autoLoad.lastIndexOf(",")+1, autoLoad.length );
		
		switch ( contextName )
		{
		case "raid":
			if ( contextId > -1 )
			{
				loadRaid( contextId );
			}
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
				if ( contextId > -1 )
				{
					loadForeignProfile( contextId );
				}
				else
				{
					changeContext( contextName );
					loadSettings();
				}				
			}
			break;
		
		case "calendar":
		default:
			changeContext( "calendar" );
			loadDefaultCalendar();
		}
	}
}

function initMenu()
{	
	$("#button_calendar")
		.click( onChangeContext );
		
	$("#button_raid")
		.click( onChangeContext );
	
	$("#button_profile")
		.click( onChangeContext );
		
	$("#button_settings")
		.click( onChangeContext );
			
	$("#button_calendar").addClass("on");
	
	$(window).bind('hashchange', menuAutoLoad );
	
	if ( g_User.characterIds.length == 0 )
		changeContext( "profile" );
	else
		changeContext( "calendar" );
}

function initLogin()
{
}