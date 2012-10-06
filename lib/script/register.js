function validateRegistration()
{
    if ( ($("#loginname").val() == "") ||
         ($("#loginname").val() == L("Username")) )
    {
        notify( L("EnterValidUsername") );
        return false;
    }

    if ( ($("#loginpass").val() == "") ||
         ($("#loginpass").val() == L("Password")) )
    {
        notify( L("EnterNonEmptyPassword") );
        return false;
    }

    if ( $("#loginpass").val() != $("#loginpass_repeat").val() )
    {
        notify( L("PasswordsNotMatch") );
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
            notify( L("RegistrationDone") + "<br/>" + L("ContactAdminToUnlock") );
            changeContext("login");
            displayLogin();
        }
    });
}

// -----------------------------------------------------------------------------

function switchRegisterPassField()
{
    $("#loginpass").after("<input id=\"loginpass\" type=\"password\" class=\"textactive\" name=\"pass\"/>");
    $("#loginpass:first").detach();

    $("#loginpass").focus();
    $("#loginpass").blur( function() {
        if ( $(this).val() == "" )
        {
            $(this).unbind("blur"); // avoid  additional call once entered
            $(this).detach();
            $("#loginname").after("<input id=\"loginpass\" type=\"text\" class=\"text\" value=\"" + L("Password") + "\">")
            $("#loginpass").focus( switchRegisterPassField );
        }
    });
}

// -----------------------------------------------------------------------------

function switchRegisterPassRepeatField()
{
    $("#loginpass_repeat").after("<input id=\"loginpass_repeat\" type=\"password\" class=\"textactive\" name=\"pass_repeat\"/>");
    $("#loginpass_repeat:first").detach();

    $("#loginpass_repeat").focus();
    $("#loginpass_repeat").blur( function() {
        if ( $(this).val() == "" )
        {
            $(this).unbind("blur"); // avoid  additional call once entered
            $(this).detach();
            $("#loginpass").after("<input id=\"loginpass_repeat\" type=\"text\" class=\"text\" value=\"" + L("RepeatPassword") + "\">")
            $("#loginpass_repeat").focus( switchRegisterPassRepeatField );
        }
    });
}

// -----------------------------------------------------------------------------

function displayRegistration()
{
    var HTMLString = "";

    HTMLString += "<div class=\"login\">";
    HTMLString += "<input type=\"hidden\" name=\"register\"/>";
    HTMLString += "<input type=\"hidden\" name=\"nocheck\"/>";
    HTMLString += "<div>";
    HTMLString += "<input id=\"loginname\" type=\"text\" class=\"text\" value=\"" + L("Username") + "\"/>";
    HTMLString += "</div>";
    HTMLString += "<div>";
    HTMLString += "<input id=\"loginpass\" type=\"text\" class=\"text\" value=\"" + L("Password") + "\"/>";
    HTMLString += "</div>";
    HTMLString += "<div>";
    HTMLString += "<input id=\"loginpass_repeat\" type=\"text\" class=\"text\" value=\"" + L("RepeatPassword") + "\"/>";
    HTMLString += "</div>";
    HTMLString += "<button id=\"doregister\" onclick=\"validateRegistration()\" style=\"margin-left: 5px\" class=\"button_register\">" + L("Register") + "</button>";
    HTMLString += "</div>";

    $("#body").empty().append(HTMLString);

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

    $("#loginpass").focus( switchRegisterPassField );
    $("#loginpass_repeat").focus( switchRegisterPassRepeatField );

    $("#doregister").button().css( "font-size", 11 );
}