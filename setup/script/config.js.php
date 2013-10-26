<?php
    header("Content-type: text/javascript");
    define( "LOCALE_SETUP", true );
    require_once(dirname(__FILE__)."/../../lib/private/locale.php");
?>

function checkConfigForm( a_OnSuccess, a_Parameter ) 
{
    if ( $("#password").val().length == 0 )
    {
        alert("<?php echo L("DatabasePasswordEmpty"); ?>");
        return;
    }
    
    if ( $("#password").val() != $("#password_check").val() )
    {
        alert("<?php echo L("DatabasePasswordNoMatch"); ?>");
    }
    
    var parameter = {
        host     : $("#host").val(),
        database : $("#database").val(),
        user     : $("#user").val(),
        password : $("#password").val()
    };
    
    $.ajax({
        type     : "POST",
        url      : "query/install_config_check.php",
        dataType : "xml",
        async    : true,
        data     : parameter,
        success  : function(a_XMLData) { a_OnSuccess(a_XMLData,a_Parameter); }
    });
}

// ----------------------------------------------------------------------------

function OnCheckConfigConnection( a_XMLData )
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
        alert("<?php echo L("ConnectionTestOk"); ?>");
    }
}

// ----------------------------------------------------------------------------

function ShowDBErrors(a_XMLData)
{
    var result = $(a_XMLData).children("database");
    var errors = result.children("error");
    
    if (errors.size() > 0)
    {
        var Message = "";
        errors.each(function() {
            Message += "\n" + $(this).text();
        });
        
        return confirm(Message);
    }
    
    return true;
}

// ----------------------------------------------------------------------------

function OnConfigSubmit( a_XMLData, a_NextPage )
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
            register  : $("#allow_registration:checked").val() == "on",
            groupsync : $("#allow_group_sync:checked").val() == "on",
            public    : $("#allow_public_mode:checked").val() == "on",
            cleartext : $("#allow_cleartext:checked").val() == "on"
        };
        
        $.ajax({
            type     : "POST",
            url      : "query/submit_config.php",
            dataType : "xml",
            async    : true,
            data     : parameter,
            success  : function(aXHR) { if (ShowDBErrors(aXHR)) open(a_NextPage); }
        });
    }
}