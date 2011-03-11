function displayLogin()
{
	var HTMLString = "";
	
	HTMLString += "<form class=\"login\" method=\"post\" action=\"index.php\" accept-charset=\"ISO-8859-1\">";
	HTMLString += "<input type=\"hidden\" name=\"nocheck\"/>";
	HTMLString += "<input type=\"hidden\" id=\"sticky_value\" name=\"sticky\" value=\"false\"/>";
	HTMLString += "<div>";
	HTMLString += "<input id=\"logindummy1\" type=\"text\" class=\"text\" value=\"" + L("Username") + "\"/>";
	HTMLString += "<input id=\"loginname\" type=\"text\" class=\"textactive\" name=\"user\"/>";
	HTMLString += "</div>";
	HTMLString += "<div>";
	HTMLString += "<input id=\"logindummy2\" type=\"text\" class=\"text\" value=\"" + L("Password") + "\"/>";
	HTMLString += "<input id=\"loginpass\" type=\"password\" class=\"textactive\" name=\"pass\"/>";
	HTMLString += "</div>";
	HTMLString += "<div class=\"buttonfield\">";
	HTMLString += "<button id=\"dologin\" style=\"margin-left: 5px\" class=\"button_login\">" + L("Login") + "</button>";
	HTMLString += "<span id=\"sticky\" style=\"margin-left: 5px\" class=\"button_sticky\"></span>";
	HTMLString += "</div>";
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
	
	$("#dologin").button().css( "font-size", 11 );
		
	$("#sticky").button({ icons: { primary: "ui-icon-unlocked" } })
		.css( "font-size", 11 )
		.click( function( event ) {
			if ( $("#sticky_value").val() == "true" )
			{
				$(this).button( "option", "icons", { primary: "ui-icon-unlocked" } );
				$("#sticky_value").val( "false" );
			}
			else
			{
				$(this).button( "option", "icons", { primary: "ui-icon-locked" } );
				$("#sticky_value").val( "true" );
			}		
		});
}