function displayLogin()
{
	var HTMLString = "";
	
	HTMLString += "<form class=\"login\" method=\"post\" action=\"index.php\" accept-charset=\"ISO-8859-1\">";
	HTMLString += "<input type=\"hidden\" name=\"nocheck\"/>";
	HTMLString += "<div>";
	HTMLString += "<input id=\"logindummy1\" type=\"text\" class=\"text\" value=\"" + L("Username") + "\"/>";
	HTMLString += "<input id=\"loginname\" type=\"text\" class=\"textactive\" name=\"user\"/>";
	HTMLString += "</div>";
	HTMLString += "<div>";
	HTMLString += "<input id=\"logindummy2\" type=\"text\" class=\"text\" value=\"" + L("Password") + "\"/>";
	HTMLString += "<input id=\"loginpass\" type=\"password\" class=\"textactive\" name=\"pass\"/>";
	HTMLString += "</div>";
	HTMLString += "<button id=\"dologin\" onclick=\"submit()\" style=\"margin-left: 5px\" class=\"button_login\">" + L("Login") + "</button>";
	HTMLString += "</form>";
	
	$("#body").empty().append(HTMLString);

	$("#loginpass").hide();
	$("#loginname").hide();
	$("#logindummy1").show();
	$("#logindummy2").show();
			
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
	
	$(".button_login").button().css( "font-size", 11 );
}