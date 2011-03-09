function loginAutoLoad()
{
	if ( $(window).data("ignore_hashchange") != true )
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
		
	$(window).data("ignore_hashchange", false);
}

function initLogin()
{	
	$("#button_login")
		.click( onChangeContext )
		.data( "loadFunction", "displayLogin()" );
	
	if ( $("#button_register").size() > 0 )
	{
		$("#button_register")
			.click( onChangeContext )
			.data( "loadFunction", "displayRegistration()" );
	}
	
	$(window).bind('hashchange', loginAutoLoad );
	loginAutoLoad();
}

function initMenu()
{
}