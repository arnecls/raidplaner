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
	
	loadDefaultCalendar();
}

function initLogin()
{
}