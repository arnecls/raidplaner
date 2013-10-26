<?php
    header("Content-type: text/javascript");
    define( "LOCALE_SETUP", true );
    require_once(dirname(__FILE__)."/../../lib/private/locale.php");
?>

function checkPasswordForm( a_NextPage ) 
{
    if ( $("#password").val().length == 0 )
    {
        alert("<?php echo L("AdminPasswordEmpty"); ?>");
        return;
    }
    
    if ( $("#password").val() != $("#password_check").val() )
    {
        alert("<?php echo L("AdminPasswordNoMatch"); ?>");
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
        error    : function(aXHR, aStatus, aError) { alert("<?php echo L("Error"); ?>:\n\n" + aError); }
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
        
        alert("<?php echo L("Error"); ?>:\n\n" + errorString );
    }
    else
    {
        open(a_NextPage);
    }
}