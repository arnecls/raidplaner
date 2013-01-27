<?php
    header("Content-type: text/javascript");
    define( "LOCALE_SETUP", true );
    require_once(dirname(__FILE__)."/../../lib/private/locale.php");
?>

function checkForm() 
{
    if ( $("#password").val().length == 0 )
    {
        alert("<?php echo L("DatabasePasswordEmpty"); ?>");
        return;
    }
    
    if ( $("#admin_password").val().length == 0 )
    {
        alert("<?php echo L("AdminPasswordEmpty"); ?>");
        return;
    }
    
    if ( $("#password").val() != $("#password_check").val() )
    {
        alert("<?php echo L("DatabasePasswordNoMatch"); ?>");
    }
    
    if ( $("#admin_password").val() != $("#admin_password_check").val() )
    {
        alert("<?php echo L("AdminPasswordNoMatch"); ?>");
        return;
    }
    
    var parameter = {
        host     : $("#host").val(),
        database : $("#database").val(),
        user     : $("#user").val(),
        password : $("#password").val()
    };
    
    $.ajax({
        type     : "POST",
        url      : "query/setup_db_check.php",
        dataType : "xml",
        async    : true,
        data     : parameter,
        success  : dbCheckDone
    });
}

function dbCheckDone( a_XMLData )
{
    var testResult = $(a_XMLData).children("test");
    
    if ( testResult.children("error").size() > 0 )
    {
        var errorString = "";
        
        testResult.children("error").each( function() {
            errorString += $(this).text() + "\n";
        });
        
        alert("<?php echo L("ConnectionTestFailed"); ?>:\n\n" + errorString );
    }
    else
    {
        var parameter = {
            host      : $("#host").val(),
            database  : $("#database").val(),
            user      : $("#user").val(),
            password  : $("#password").val(),
            prefix    : $("#prefix").val(),
            adminpass : $("#admin_password").val(),
            register  : $("#allow_registration")[0].checked
        };
        
        $.ajax({
            type     : "POST",
            url      : "query/setup_db_done.php",
            dataType : "xml",
            async    : true,
            data     : parameter,
            success  : loadBindings
        });
    }
}