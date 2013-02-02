function switchPassField()
{
    $("#loginpass").after("<input id=\"loginpass\" type=\"password\" class=\"textactive\"/>");
    $("#loginpass:first").detach();

    $("#loginpass").focus();
    $("#loginpass").blur( function() {
        if ( $(this).val() == "" )
        {
            $(this).unbind("blur"); // avoid  additional call once entered
            $(this).detach();
            $("#loginname").after("<input id=\"loginpass\" type=\"text\" class=\"text\" value=\"" + L("Password") + "\">");
            $("#loginpass").focus( switchPassField );
        }
    });
}

// -----------------------------------------------------------------------------

function displayLogin()
{
    var HTMLString = "";

    HTMLString += "<form id=\"loginform\" class=\"login\" method=\"post\" onsubmit=\"return startLogin()\" action=\"index.php\" accept-charset=\"ISO-8859-1\">";
    HTMLString += "<input type=\"hidden\" name=\"nocheck\"/>";
    HTMLString += "<input type=\"hidden\" id=\"sticky_value\" name=\"sticky\" value=\"false\"/>";
    HTMLString += "<div>";
    HTMLString += "<input id=\"loginname\" type=\"text\" class=\"text\" name=\"user\" value=\"" + L("Username") + "\"/>";
    HTMLString += "</div>";
    HTMLString += "<div>";
    HTMLString += "<input id=\"loginpass\" type=\"text\" class=\"text\" value=\"" + L("Password") + "\"/>";
    HTMLString += "<input id=\"password\" name=\"pass\" type=\"hidden\"value=\"\"/>";
    HTMLString += "</div>";
    HTMLString += "<div class=\"buttonfield\">";
    HTMLString += "<button id=\"dologin\" style=\"margin-left: 5px\" onclick=\"return startLogin()\" class=\"button_login\">" + L("Login") + "</button>";
    HTMLString += "<button id=\"sticky\" style=\"margin-left: 5px\" class=\"button_sticky\"></button>";
    HTMLString += "</div>";
    HTMLString += "</form>";

    $("#body").empty().append(HTMLString);

    // textactive

    $("#loginname").focus( function() {
        $("#loginname").removeClass("text").addClass("textactive");

        if ( $("#loginname").val() == L("Username") )
            $("#loginname").val("");
    });

    $("#loginname").blur( function() {
        if ( $("#loginname").val() == "" )
        {
            $("#loginname").removeClass("textactive").addClass("text");
            $("#loginname").val(L("Username"));
        }
    });

    $("#loginpass").focus( switchPassField );
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

// -----------------------------------------------------------------------------

function finishLogin( a_XMLData )
{
    var message = $(a_XMLData).children("messagehub");
    
    if ( message.children("error").length > 0 )
    {
        notify( message.children("error").text() );
        
        $("input").removeAttr("disabled");
        $("button").button("option", "disabled", false);
    }
    else
    {    
        var salt   = message.children("salt").text();
        var key    = message.children("pubkey").text();
        var method = message.children("method").text();
        
        var encodedPass = hash( key, method, $("#loginpass").val(), salt );
        
        $("#loginform").data("submitted", true);
        $("input").removeAttr("disabled");
        $("button").button("option", "disabled", false);
        
        $("#password").val(encodedPass);
        $("#loginform").submit();
    }
}

// -----------------------------------------------------------------------------

function startLogin()
{
    if ( !$("#loginform").data("submitted") )
    {
        $("input").attr("disabled","disabled");
        $("button").button("option", "disabled", true);
        
        var Parameters = {
            Login : $("#loginname").val()
        };
        
        AsyncQuery( "query_credentials", Parameters, finishLogin );
        return false;
    }
}