function validateRegistration()
{
	if ( $("#loginname").val() == "" )
	{
		notify( L("You must enter a valid username.") );
		return false;
	}
	
	if ( $("#loginpass").val() == "" )
	{
		notify( L("You must enter a non-empty password.") );
		return false;
	}

	if ( $("#loginpass").val() != $("#loginpass_repeat").val() )
	{
		notify( L("Passwords did not match.") );
		return false;
	}
	
	var Parameters = {
		name : $("#loginname").val(),
		pass : $("#loginpass").val()
	};
	
	AsyncQuery( "user_create", Parameters, function( a_XMLData ) {
		var Message = $(a_XMLData).children("messagehub");
		
		if ( Message.children("error").size() == 0 )
		{
			notify( L("Registration complete.") + "<br/>" + L("Please contact your admin to get your account unlocked.") );
			changeContext("login");
			displayLogin();
		}
	});
}

function displayRegistration()
{
	var HTMLString = "";
	
	HTMLString += "<div class=\"login\">";
	HTMLString += "<input type=\"hidden\" name=\"register\"/>";
	HTMLString += "<input type=\"hidden\" name=\"nocheck\"/>";
	HTMLString += "<div>";
	HTMLString += "<input id=\"logindummy1\" type=\"text\" class=\"text\" value=\"" + L("Username") + "\"/>";
	HTMLString += "<input id=\"loginname\" type=\"text\" class=\"textactive\" name=\"user\"/>";
	HTMLString += "</div>";
	HTMLString += "<div>";
	HTMLString += "<input id=\"logindummy2\" type=\"text\" class=\"text\" value=\"" + L("Password") + "\"/>";
	HTMLString += "<input id=\"loginpass\" type=\"password\" class=\"textactive\" name=\"pass\"/>";
	HTMLString += "</div>";
	HTMLString += "<div>";
	HTMLString += "<input id=\"logindummy3\" type=\"text\" class=\"text\" value=\"" + L("Repeat password") + "\"/>";
	HTMLString += "<input id=\"loginpass_repeat\" type=\"password\" class=\"textactive\" name=\"pass_repeat\"/>";
	HTMLString += "</div>";
	HTMLString += "<button id=\"doregister\" onclick=\"validateRegistration()\" style=\"margin-left: 5px\" class=\"button_register\">" + L("Register") + "</button>";
	HTMLString += "</div>";
	
	$("#body").empty().append(HTMLString);
	
	$("#loginname").hide();
	$("#loginpass").hide();
	$("#loginpass_repeat").hide();
	
	$("#logindummy1").show();
	$("#logindummy2").show();
	$("#logindummy3").show();
	
	$("#logindummy1").focus( function() { 
		$("#logindummy1").hide(); 
		$("#loginname").show().focus(); 
	});
	
	$("#loginname").blur( function() { 
		if ( $("#loginname").val() == "" ) {
			$("#loginname").hide();
			$("#logindummy1").show(); 
		}
	});
	
	$("#logindummy2").focus( function() { 
		$("#logindummy2").hide(); 
		$("#loginpass").show().focus(); 
	});
		
	$("#loginpass").blur( function() { 
		if ( $("#loginpass").val() == "" ) {
			$("#loginpass").hide();
			$("#logindummy2").show(); 
		}
	});
	
	$("#logindummy3").focus( function() { 
		$("#logindummy3").hide(); 
		$("#loginpass_repeat").show().focus(); 
	});
		
	$("#loginpass_repeat").blur( function() { 
		if ( $("#loginpass_repeat").val() == "" ) {
			$("#loginpass_repeat").hide();
			$("#logindummy3").show(); 
		}
	});
	
	$("#doregister").button().css( "font-size", 11 );
}