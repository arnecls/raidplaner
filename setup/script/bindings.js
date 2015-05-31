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

function LoadBindingData(aBinding)
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
            url      : "query/fetch_bindingdata.php",
            dataType : "json",
            async    : true,
            data     : parameter,
            success  : OnLoadBindingData,
            error    : function(aXHR, aStatus, aError) { alert(L("Error") + ":\n\n" + aError); }
        });
    }
}

// ----------------------------------------------------------------------------

function OnLoadBindingData( aXHR )
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

    // Load group data

    if (aXHR.groups != undefined)
    {
        // Store old selection

        var selections = new Array();
        $("input[name^=\""+aXHR.binding+"_groups\"]").each(function() {
            var groupId = $(this).val()
            selections[groupId] = $("input[name=\""+aXHR.binding+"_group_"+groupId+"\"]:checked").val();
        });

        // Rebuild groups

        HTMLString = "";
        $.each(aXHR.groups, function(index, value) {
            HTMLString += "<div class=\"group\">";
            HTMLString += "<span class=\"group_name\">" + value.name + "</span>";
            HTMLString += "<input type=\"hidden\" name=\""+aXHR.binding+"_groups[]\" value=\""+ value.id +"\"/>";
            HTMLString += "<input type=\"radio\" name=\""+aXHR.binding+"_group_"+ value.id +"\" class=\"group_map\" value=\"none\"/>";
            HTMLString += "<input type=\"radio\" name=\""+aXHR.binding+"_group_"+ value.id +"\" class=\"group_map\" value=\"member\"/>";
            HTMLString += "<input type=\"radio\" name=\""+aXHR.binding+"_group_"+ value.id +"\" class=\"group_map\" value=\"privileged\"/>";
            HTMLString += "<input type=\"radio\" name=\""+aXHR.binding+"_group_"+ value.id +"\" class=\"group_map\" value=\"raidlead\"/>";
            HTMLString += "<input type=\"radio\" name=\""+aXHR.binding+"_group_"+ value.id +"\" class=\"group_map\" value=\"admin\"/>";
            HTMLString += "</div>";
        });

        $("#"+aXHR.binding+"_grouplist").empty().append(HTMLString);

        // Restore selection

        $("input[name^=\""+aXHR.binding+"_groups\"]").each(function() {
            var groupId = $(this).val()
            if (selections[groupId] != undefined) {
                $("input[name=\""+aXHR.binding+"_group_"+groupId+"\"][value=\""+selections[groupId]+"\"]").attr("checked","checked");
            } else {
                $("input[name=\""+aXHR.binding+"_group_"+groupId+"\"][value=\"none\"]").attr("checked","checked");
            }
        });
    }

    // Load forum data

    if (aXHR.forums != undefined)
    {
        var selectedForum = $("#"+aXHR.binding+"_postto option:selected").val();
        var selectedUser = $("#"+aXHR.binding+"_postas option:selected").val();

        // Forums

        HTMLString = "<option value=\"0\">" + L("DisablePosting") + "</option>";

        $.each(aXHR.forums, function(index, value) {
            HTMLString += "<option value=\"" + value.id + "\">" + value.name + "</option>";
        });

        $("#"+aXHR.binding+"_postto").empty().append( HTMLString );
        $("#"+aXHR.binding+"_postto option[value="+selectedForum+"]").prop("selected", true);

        // Users

        HTMLString = "";

        $.each(aXHR.users, function(index, value) {
            HTMLString += "<option value=\"" + value.id + "\">" + value.name + "</option>";
        });

        $("#"+aXHR.binding+"_postas").empty().append( HTMLString );
        $("#"+aXHR.binding+"_postas option[value="+selectedUser+"]").prop("selected", true);
    }

    // Mark as loaded

    $(".config .right select").css("background-color","#cfc")
        .delay(1000).queue(function() {
            $(this).css("background-color","").dequeue();
        });

    $(".config .groups").css("background-color","#cfc")
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
        $("#"+aXHR.binding+"_ver_major").val(parseInt(aXHR.settings.version / 10000));
        $("#"+aXHR.binding+"_ver_minor").val(parseInt((aXHR.settings.version / 100) % 100));
        $("#"+aXHR.binding+"_ver_patch").val(parseInt(aXHR.settings.version % 100));

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
        parameter[bindings[i]+"_user"]     = $("#"+bindings[i]+"_user").val();
        parameter[bindings[i]+"_password"] = $("#"+bindings[i]+"_password").val();
        parameter[bindings[i]+"_prefix"]   = $("#"+bindings[i]+"_prefix").val();
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
            parameter[bindings[i]+"_database"]   = $("#"+bindings[i]+"_database").val();
            parameter[bindings[i]+"_user"]       = $("#"+bindings[i]+"_user").val();
            parameter[bindings[i]+"_password"]   = $("#"+bindings[i]+"_password").val();
            parameter[bindings[i]+"_prefix"]     = $("#"+bindings[i]+"_prefix").val();
            parameter[bindings[i]+"_ver_major"]  = $("#"+bindings[i]+"_ver_major").val();
            parameter[bindings[i]+"_ver_minor"]  = $("#"+bindings[i]+"_ver_minor").val();
            parameter[bindings[i]+"_ver_patch"]  = $("#"+bindings[i]+"_ver_patch").val();
            parameter[bindings[i]+"_autologin"]  = $("#"+bindings[i]+"_autologin").prop("checked");
            parameter[bindings[i]+"_cookie"]     = $("#"+bindings[i]+"_cookie_ex").val();
            parameter[bindings[i]+"_postto"]     = $("#"+bindings[i]+"_postto option:selected").val();
            parameter[bindings[i]+"_postas"]     = $("#"+bindings[i]+"_postas option:selected").val();
            parameter[bindings[i]+"_member"]     = new Array();
            parameter[bindings[i]+"_privileged"] = new Array();
            parameter[bindings[i]+"_raidlead"]   = new Array();
            parameter[bindings[i]+"_admin"]      = new Array();

            $("input[name^=\""+bindings[i]+"_groups\"]").each(function() {
                var groupId = $(this).val()
                var mapping = $("input[name=\""+bindings[i]+"_group_"+groupId+"\"]").val()

                switch (mapping) {
                case "member":
                    parameter[bindings[i]+"_member"].push( groupId );
                case "privileged":
                    parameter[bindings[i]+"_privileged"].push( groupId );
                case "raidlead":
                    parameter[bindings[i]+"_raidlead"].push( groupId );
                case "admin":
                    parameter[bindings[i]+"_admin"].push( groupId );
                default:
                    break;
                }
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