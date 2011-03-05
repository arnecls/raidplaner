function initLogin()
{	
	$("#button_login").addClass("on");
	displayLogin();
	
	$("#button_login")
		.click( onChangeContext )
		.data( "loadFunction", "displayLogin()" );
	
	if ( $("#button_register").size() > 0 )
	{
		$("#button_register")
			.click( onChangeContext )
			.data( "loadFunction", "displayRegistration()" );
	}
}

function initMenu()
{
}