function loginAutoLoad()
{
	var autoLoad = window.location.href.substring( window.location.href.lastIndexOf("#")+1,  window.location.href.length );
	
	switch ( autoLoad )
	{
	case "register":
		changeContext( autoLoad );
		displayRegistration();
		break;
	
	case "login":
	default:
		changeContext( "login" );
		displayLogin();
	}
}

function initLogin()
{	
	$("#button_login")
		.click( onChangeContext );
	
	if ( $("#button_register").size() > 0 )
	{
		$("#button_register")
			.click( onChangeContext );
	}
	
	$(window).bind('hashchange', loginAutoLoad );
	loginAutoLoad();
}

function initMenu()
{
}