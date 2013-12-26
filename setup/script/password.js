function checkPasswordForm( a_NextPage )

{
    if ( $("#password").val().length == 0 )
    {
        alert(L("AdminPasswordEmpty"));
        return;
    }

    if ( $("#password").val() != $("#password_check").val() )
    {
        alert(L("AdminPasswordNoMatch"));
        return;
    }

    var parameter = {
        password : $("#password").val()
    };

    $.ajax({
        type     : "POST",
        url      : "query/submit_password.php",
        dataType : "xml",
        async    : true,
        data     : parameter,
        success  : function(a_XMLData) { OnPasswordSubmit(a_XMLData, a_NextPage); },
        error    : function(aXHR, aStatus, aError) { alert(L("Error") + ":\n\n" + aError); }
    });
}

// ----------------------------------------------------------------------------

function OnPasswordSubmit( a_XMLData, a_NextPage )
{
    var testResult = $(a_XMLData).children("database");

    if ( testResult.children("error").size() > 0 )
    {
        var errorString = "";

        testResult.children("error").each( function() {
            errorString += $(this).text() + "\n";
        });

        alert(L("Error") + ":\n\n" + errorString );
    }
    else
    {
        open(a_NextPage);
    }
}