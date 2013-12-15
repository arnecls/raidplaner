function CheckBindingFields(aBinding)
{
    if ( $("#"+aBinding+"_database").val().length == 0 )
    {
        alert(L(aBinding+"_DatabaseEmpty"));
        return false;
    }
    
    if ( $("#"+aBinding+"_user").val().length == 0 )
    {
        alert(L(aBinding+"_UserEmpty"));
        return false;
    }
    
    if ( $("#"+aBinding+"_password").val().length == 0 )
    {
        alert(L(aBinding+"_PasswordEmpty"));
        return false;
    }
    
    if ( $("#"+aBinding+"_password").val() != $("#"+aBinding+"_password_check").val() )
    {
        alert(L(aBinding+"_DBPasswordsMatch"));
        return false;
    }
    
    return true;
}

// ----------------------------------------------------------------------------

function ReloadGroups(aBinding) 
{
    if (CheckBindingFields(aBinding))
    {
        var parameter = {
            binding  : aBinding,
            database : $("#"+aBinding+"_database").val(),
            user     : $("#"+aBinding+"_user").val(),
            password : $("#"+aBinding+"_password").val(),
            prefix   : $("#"+aBinding+"_prefix").val()
        };
        
        $.ajax({
            type     : "POST",
            url      : "query/fetch_groups.php",
            dataType : "json",
            async    : true,
            data     : parameter,
            success  : OnReloadGroups,
            error    : function(aXHR, aStatus, aError) { alert(L("Error") + ":\n\n" + aError); }
        });
    }
}

// ----------------------------------------------------------------------------

function OnReloadGroups( aXHR )
{
    if ( (aXHR.error != null) && (aXHR.error.length > 0) )
    {
        var errorString = "";
        
        $.each(aXHR.error, function(index, value) {
            errorString += value + "\n";
        });
        
        alert(L("ReloadFailed") + ":\n\n" + errorString );
        return;
    }    
    
    HTMLString = "";
    
    // Store old selected
    
    var members = new Array();
    
    $("#"+aXHR.binding+"_member option:selected").each(function() {
        members[members.length] = $(this).val();
    });
    
    var leads = new Array();
    
    $("#"+aXHR.binding+"_raidlead option:selected").each(function() {
        leads[members.length] = $(this).val();
    });
    
    // Rebuild groups
    
    $.each(aXHR.groups, function(index, value) {   
        HTMLString += "<option value=\"" + value.id + "\">" + value.name + "</option>";
    });
    
    $("#"+aXHR.binding+"_member").empty().append( HTMLString );
    $("#"+aXHR.binding+"_raidlead").empty().append( HTMLString );
    
    // Select old values
    
    $("#"+aXHR.binding+"_member option").each(function() {
        for (var i=0; i<members.length; ++i)
        {
            if ($(this).val() == members[i])
            {
                $(this).prop("selected", true);
                break;
            }
        }
    });
    
    $("#"+aXHR.binding+"_raidlead option").each(function() {
        for (var i=0; i<leads.length; ++i)
        {
            if ($(this).val() == leads[i])
            {
                $(this).prop("selected", true);
                break;
            }
        }
    });
    
    // Mark as loaded
    
    $(".config .right select").css("background-color","#cfc")
        .delay(1000).queue(function() {
            $(this).css("background-color","").dequeue();
        });
}

// ----------------------------------------------------------------------------

function LoadSettings(aBinding)
{
    var basepath = window.prompt(L("BindingBasePath"), "");
    
    if (basepath == null)
        return;
    
    var parameter = {
        binding : aBinding,
        path    : basepath
    };
    
    $.ajax({
        type     : "POST",
        url      : "query/fetch_settings.php",
        dataType : "json",
        async    : true,
        data     : parameter,
        success  : OnLoadSettings,
        error    : function(aXHR, aStatus, aError) { alert(L("Error") + ":\n\n" + aError); }
    });
}

// ----------------------------------------------------------------------------

function OnLoadSettings(aXHR)
{
    if ( (aXHR.error != null) && (aXHR.error.length > 0) )
    {
        var errorString = "";
        
        $.each(aXHR.error, function(index, value) {
            errorString += value + "\n";
        });
        
        alert(L("RetrievalFailed") + ":\n\n" + errorString );
        return;
    }
    
    if (aXHR.settings != undefined)
    {
        $("#"+aXHR.binding+"_database").val(aXHR.settings.database);
        $("#"+aXHR.binding+"_user").val(aXHR.settings.user);
        $("#"+aXHR.binding+"_password").val(aXHR.settings.password);
        $("#"+aXHR.binding+"_password_check").val(aXHR.settings.password);
        $("#"+aXHR.binding+"_prefix").val(aXHR.settings.prefix);
        
        if (aXHR.settings.cookie != undefined)
        {
            $("#"+aXHR.binding+"_cookie_ex").val(aXHR.settings.cookie);
        }
        
        $(".config .left input").css("background-color","#cfc")
        .delay(1000).queue(function() {
            $(this).css("background-color","").dequeue();
        });
    }
    else
    {
        alert(L("RetrievalFailed"));
    }
}
    
// ----------------------------------------------------------------------------

function CheckGrouplessBinding(aBinding)
{
    if (CheckBindingFields(aBinding))
    {
        var parameter = {
            binding  : aBinding,
            database : $("#"+aBinding+"_database").val(),
            user     : $("#"+aBinding+"_user").val(),
            password : $("#"+aBinding+"_password").val(),
            prefix   : $("#"+aBinding+"_prefix").val()
        };
        
        $.ajax({
            type     : "POST",
            url      : "query/fetch_groups.php",
            dataType : "json",
            async    : true,
            data     : parameter,
            success  : OnCheckBinding,
            error    : function(aXHR, aStatus, aError) { alert(L("Error") + ":\n\n" + aError); }
        });
    }
}

// ----------------------------------------------------------------------------

function OnCheckBinding( aXHR )
{
    if ( (aXHR.error != null) && (aXHR.error.length > 0) )
    {
        var errorString = "";
        
        $.each(aXHR.error, function(index, value) {
            errorString += value + "\n";
        });
        
        alert(L("ConnectionTestFailed") + ":\n\n" + errorString );
        return;
    }
    
    alert(L("ConnectionTestOk"));
}

// ----------------------------------------------------------------------------

function CheckBindingForm(a_Parameter) 
{
    var bindings = new Array();
    var parameter = new Object();
    
    $(".config").each( function() {
        bindings[bindings.length] = $(this).attr("id");
    });
    
    for (var i=0; i<bindings.length; ++i)
    {
        parameter[bindings[i]+"_check"] = $("#allow_"+bindings[i]+":checked").val() == "on"
    
        if ( parameter[bindings[i]+"_check"] && !CheckBindingFields(bindings[i]) )
            return;
            
        parameter[bindings[i]+"_database"] = $("#"+bindings[i]+"_database").val();
        parameter[bindings[i]+"_user"]     =  $("#"+bindings[i]+"_user").val();
        parameter[bindings[i]+"_password"] =  $("#"+bindings[i]+"_password").val();
        parameter[bindings[i]+"_prefix"]   =  $("#"+bindings[i]+"_prefix").val();
    }
    
    $.ajax({
        type     : "POST",
        url      : "query/install_bindings_check.php",
        dataType : "xml",
        async    : true,
        data     : parameter,
        success  : function(a_XMLData) { OnDbCheckDone(a_XMLData, a_Parameter); },
        error    : function(aXHR, aStatus, aError) { alert(L("Error") + ":\n\n" + aError); }
    });
}

// ----------------------------------------------------------------------------

function OnDbCheckDone( a_XMLData, a_NextPage )
{
    var testResult = $(a_XMLData).children("test");
    
    if ( testResult.children("error").size() > 0 )
    {
        var errorString = "";
        
        testResult.children("error").each( function() {
            errorString += $(this).prev().text() + ": " + $(this).text() + "\n\n";
        });
        
        alert(L("ConnectionTestFailed") + ":\n\n" + errorString );
        return; // ### return, failed ###
    }
    
    var bindings = new Array();
    var parameter = new Object();
    
    $(".config").each( function() {
        bindings[bindings.length] = $(this).attr("id");
    });
    
    for (var i=0; i<bindings.length; ++i)
    {
        parameter[bindings[i]+"_allow"] = $("#allow_"+bindings[i]).val() == "true";
    
        if ( parameter[bindings[i]+"_allow"] )
        {
            parameter[bindings[i]+"_database"]  = $("#"+bindings[i]+"_database").val();
            parameter[bindings[i]+"_user"]      = $("#"+bindings[i]+"_user").val();
            parameter[bindings[i]+"_password"]  = $("#"+bindings[i]+"_password").val();
            parameter[bindings[i]+"_prefix"]    = $("#"+bindings[i]+"_prefix").val();
            parameter[bindings[i]+"_autologin"] = $("#"+bindings[i]+"_autologin").prop("checked");
            parameter[bindings[i]+"_cookie"]    = $("#"+bindings[i]+"_cookie_ex").val();
            parameter[bindings[i]+"_member"]    = new Array();
            parameter[bindings[i]+"_raidlead"]  = new Array();
            
            $("#"+bindings[i]+"_member option:selected").each( function() {
                parameter[bindings[i]+"_member"].push( $(this).val() );
            });
            
            $("#"+bindings[i]+"_raidlead option:selected").each( function() {
                parameter[bindings[i]+"_raidlead"].push( $(this).val() );
            });
        }
    }
    
    $.ajax({
        type     : "POST",
        url      : "query/submit_bindings.php",
        dataType : "xml",
        async    : true,
        data     : parameter,
        success  : function() { open(a_NextPage); },
        error    : function(aXHR, aStatus, aError) { alert(L("Error") + ":\n\n" + aError); }
    });
}

// ----------------------------------------------------------------------------

function showConfig( a_Name )
{
    $(".config").hide();    
    $("#"+a_Name).show();
    $("#binding_name").empty().append(L(a_Name+"_Binding"));
    
    $("#binding_allow").prop( "checked", $("#allow_"+a_Name).val() == "true" );
}

// ----------------------------------------------------------------------------

function toggleCurrentBinding( aCheckBox )
{
    var bindingName = $("#binding_current").children("option:selected").val();
    var enabled = $(aCheckBox).prop("checked");
    
    $("#allow_"+bindingName).val(enabled  ? "true" : "false");
    $("#"+bindingName+" input, "+"#"+bindingName+" select, "+"#"+bindingName+" button").prop("disabled", !enabled);
}