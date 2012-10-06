function switchPassField()
{
    $("#loginpass").after("<input id=\"loginpass\" type=\"password\" class=\"textactive\" name=\"pass\"/>");
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

    HTMLString += "<form class=\"login\" method=\"post\" action=\"index.php\" accept-charset=\"ISO-8859-1\">";
    HTMLString += "<input type=\"hidden\" name=\"nocheck\"/>";
    HTMLString += "<input type=\"hidden\" id=\"sticky_value\" name=\"sticky\" value=\"false\"/>";
    HTMLString += "<div>";
    HTMLString += "<input id=\"loginname\" type=\"text\" class=\"text\" name=\"user\" value=\"" + L("Username") + "\"/>";
    HTMLString += "</div>";
    HTMLString += "<div>";
    HTMLString += "<input id=\"loginpass\" type=\"text\" class=\"text\" value=\"" + L("Password") + "\"/>";
    HTMLString += "</div>";
    HTMLString += "<div class=\"buttonfield\">";
    HTMLString += "<button id=\"dologin\" style=\"margin-left: 5px\" onclick=\"submit()\" class=\"button_login\">" + L("Login") + "</button>";
    HTMLString += "<span id=\"sticky\" style=\"margin-left: 5px\" class=\"button_sticky\"></span>";
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