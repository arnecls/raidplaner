<?php
	define( "UNIFIED_SCRIPT", true );

	require_once("../private/users.php");
	UserProxy::GetInstance(); // Init user
	
	header("Content-type: text/javascript");

	include_once("jquery-1.7.2.min.js");
	include_once("jquery-ui-1.8.18.custom.min.js");
	include_once("jquery.ba-hashchange.min.js");
	
	include_once("config.js.php");
	include_once("locale.js.php");
	include_once("messagehub.js");
	include_once("mobile.js");
	include_once("combobox.js");
	include_once("tooltip.js");
	include_once("sheet.js");
	include_once("main.js");
	
	if ( RegisteredUser() )
	{
		if ( ValidAdmin() )
		{
			include_once("settings.js");
		}
		
		include_once("calendar.js");
		include_once("raid.js");
		include_once("raidlist.js");
		include_once("profile.js");
		include_once("initmenu.js");		
	}
	else
	{
		include_once("login.js");
		
		if ( ALLOW_REGISTRATION )
		{
			include_once("register.js");
		}
		
		include_once("initlogin.js");
	}
?>