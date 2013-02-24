<?php
    header("Content-type: text/javascript");
    define( "LOCALE_SETUP", true );
    require_once(dirname(__FILE__)."/../../lib/private/locale.php");
?>

function checkPasswordForm( a_Parameter ) 
{
    if ( $("#password").val().length == 0 )
    {
        alert("<?php echo L("AdminPasswordEmpty"); ?>");
        return;
    }
    
    if ( $("#password").val() != $("#password_check").val() )
    {
        alert("<?php echo L("AdminPasswordNoMatch"); ?>");
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
        success  : function(a_XMLData) { OnPasswordSubmit(a_XMLData,a_Parameter); }
    });
}

// ----------------------------------------------------------------------------

function OnPasswordSubmit( a_XMLData, a_NextPage )
{
    var testResult = $(a_XMLData).children("test");
    
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