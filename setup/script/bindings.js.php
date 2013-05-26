<?php
    header("Content-type: text/javascript");
    define( "LOCALE_SETUP", true );
    require_once(dirname(__FILE__)."/../../lib/private/locale.php");
?>

function OnReloadPHPBB3( a_XMLData )
{
    var groups = $(a_XMLData).children("grouplist");
    
    if ( groups.children("error").size() > 0 )
    {
        var errorString = "";
        
        groups.children("error").each( function() {
            errorString += $(this).text() + "\n";
        });
        
        alert("<?php echo L("ReloadFailed"); ?>:\n\n" + errorString );
        return;
    }    
    
    HTMLString = "";
    
    groups.children("group").each( function() {
        var id = $(this).children("id").text();
        var name = $(this).children("name").text();
        
        HTMLString += "<option value=\"" + id + "\">" + name + "</option>";
    });
    
    $("#phpbb3_member").empty().append( HTMLString );
    $("#phpbb3_raidlead").empty().append( HTMLString );
}

// ----------------------------------------------------------------------------

function OnReloadVB3( a_XMLData )
{
    var groups = $(a_XMLData).children("grouplist");
    
    if ( groups.children("error").size() > 0 )
    {
        var errorString = "";
        
        groups.children("error").each( function() {
            errorString += $(this).text() + "\n";
        });
        
        alert("<?php echo L("ReloadFailed"); ?>:\n\n" + errorString );
        return;
    }    
    
    HTMLString = "";
    
    groups.children("group").each( function() {
        var id = $(this).children("id").text();
        var name = $(this).children("name").text();
        
        HTMLString += "<option value=\"" + id + "\">" + name + "</option>";
    });
    
    $("#vb3_member").empty().append( HTMLString );
    $("#vb3_raidlead").empty().append( HTMLString );
}

// ----------------------------------------------------------------------------

function OnReloadMyBB( a_XMLData )
{
    var groups = $(a_XMLData).children("grouplist");
    
    if ( groups.children("error").size() > 0 )
    {
        var errorString = "";
        
        groups.children("error").each( function() {
            errorString += $(this).text() + "\n";
        });
        
        alert("<?php echo L("ReloadFailed"); ?>:\n\n" + errorString );
        return;
    }    
    
    HTMLString = "";
    
    groups.children("group").each( function() {
        var id = $(this).children("id").text();
        var name = $(this).children("name").text();
        
        HTMLString += "<option value=\"" + id + "\">" + name + "</option>";
    });
    
    $("#mybb_member").empty().append( HTMLString );
    $("#mybb_raidlead").empty().append( HTMLString );
}

// ----------------------------------------------------------------------------

function OnReloadSMF( a_XMLData )
{
    var groups = $(a_XMLData).children("grouplist");
    
    if ( groups.children("error").size() > 0 )
    {
        var errorString = "";
        
        groups.children("error").each( function() {
            errorString += $(this).text() + "\n";
        });
        
        alert("<?php echo L("ReloadFailed"); ?>:\n\n" + errorString );
        return;
    }    
    
    HTMLString = "<option value=\"0\">Board default</option>";
    
    groups.children("group").each( function() {
        var id = $(this).children("id").text();
        var name = $(this).children("name").text();
        
        HTMLString += "<option value=\"" + id + "\">" + name + "</option>";
    });
    
    $("#smf_member").empty().append( HTMLString );
    $("#smf_raidlead").empty().append( HTMLString );
}

// ----------------------------------------------------------------------------

function OnReloadVanilla( a_XMLData )
{
    var groups = $(a_XMLData).children("grouplist");
    
    if ( groups.children("error").size() > 0 )
    {
        var errorString = "";
        
        groups.children("error").each( function() {
            errorString += $(this).text() + "\n";
        });
        
        alert("<?php echo L("ReloadFailed"); ?>:\n\n" + errorString );
        return;
    }
    
    HTMLString = "";
    
    groups.children("group").each( function() {
        var id = $(this).children("id").text();
        var name = $(this).children("name").text();
        
        HTMLString += "<option value=\"" + id + "\">" + name + "</option>";
    });
    
    $("#vanilla_member").empty().append( HTMLString );
    $("#vanilla_raidlead").empty().append( HTMLString );
}

// ----------------------------------------------------------------------------

function OnReloadJoomla( a_XMLData )
{
    var groups = $(a_XMLData).children("grouplist");
    
    if ( groups.children("error").size() > 0 )
    {
        var errorString = "";
        
        groups.children("error").each( function() {
            errorString += $(this).text() + "\n";
        });
        
        alert("<?php echo L("ReloadFailed"); ?>:\n\n" + errorString );
        return;
    }
    
    HTMLString = "";
    
    groups.children("group").each( function() {
        var id = $(this).children("id").text();
        var name = $(this).children("name").text();
        
        HTMLString += "<option value=\"" + id + "\">" + name + "</option>";
    });
    
    $("#joomla_member").empty().append( HTMLString );
    $("#joomla_raidlead").empty().append( HTMLString );
}

// ----------------------------------------------------------------------------

function OnReloadDrupal( a_XMLData )
{
    var groups = $(a_XMLData).children("grouplist");
    
    if ( groups.children("error").size() > 0 )
    {
        var errorString = "";
        
        groups.children("error").each( function() {
            errorString += $(this).text() + "\n";
        });
        
        alert("<?php echo L("ReloadFailed"); ?>:\n\n" + errorString );
        return;
    }
    
    HTMLString = "";
    
    groups.children("group").each( function() {
        var id = $(this).children("id").text();
        var name = $(this).children("name").text();
        
        HTMLString += "<option value=\"" + id + "\">" + name + "</option>";
    });
    
    $("#drupal_member").empty().append( HTMLString );
    $("#drupal_raidlead").empty().append( HTMLString );
}

// ----------------------------------------------------------------------------

function OnCheckEQDKP( a_XMLData )
{
    var groups = $(a_XMLData).children("grouplist");
    
    if ( groups.children("error").size() > 0 )
    {
        var errorString = "";
        
        groups.children("error").each( function() {
            errorString += $(this).text() + "\n";
        });
        
        alert("<?php echo L("ConnectionTestFailed"); ?>:\n\n" + errorString );
        return;
    }
    
    alert("<?php echo L("ConnectionTestOk"); ?>");
}

// ----------------------------------------------------------------------------

function ReloadPHPBB3Groups() 
{
    if ( $("#phpbb3_password").val().length == 0 )
    {
        alert("<?php echo L("PHPBBPasswordEmpty"); ?>");
        return;
    }
    
    if ( $("#phpbb3_password").val() != $("#phpbb3_password_check").val() )
    {
        alert("<?php echo L("PHPBBDBPasswordsMatch"); ?>");
        return;
    }
    
    var parameter = {
        database : $("#phpbb3_database").val(),
        user     : $("#phpbb3_user").val(),
        password : $("#phpbb3_password").val(),
        prefix   : $("#phpbb3_prefix").val()
    };
    
    $.ajax({
        type     : "POST",
        url      : "query/groups_phpbb3.php",
        dataType : "xml",
        async    : true,
        data     : parameter,
        success  : OnReloadPHPBB3
    });
}

// ----------------------------------------------------------------------------

function ReloadVB3Groups() 
{
    if ( $("#vb3_password").val().length == 0 )
    {
        alert("<?php echo L("VBulletinPasswordEmpty"); ?>");
        return;
    }
    
    if ( $("#vb3_password").val() != $("#vb3_password_check").val() )
    {
        alert("<?php echo L("VBulletinDBPasswordsMatch"); ?>");
        return;
    }
    
    var parameter = {
        database : $("#vb3_database").val(),
        user     : $("#vb3_user").val(),
        password : $("#vb3_password").val(),
        prefix   : $("#vb3_prefix").val()
    };
    
    $.ajax({
        type     : "POST",
        url      : "query/groups_vb3.php",
        dataType : "xml",
        async    : true,
        data     : parameter,
        success  : OnReloadVB3
    });
}

// ----------------------------------------------------------------------------

function ReloadMyBBGroups() 
{
    if ( $("#mybb_password").val().length == 0 )
    {
        alert("<?php echo L("MyBBPasswordEmpty"); ?>");
        return;
    }
    
    if ( $("#vb3_password").val() != $("#vb3_password_check").val() )
    {
        alert("<?php echo L("MyBBPasswordsMatch"); ?>");
        return;
    }
    
    var parameter = {
        database : $("#mybb_database").val(),
        user     : $("#mybb_user").val(),
        password : $("#mybb_password").val(),
        prefix   : $("#mybb_prefix").val()
    };
    
    $.ajax({
        type     : "POST",
        url      : "query/groups_mybb.php",
        dataType : "xml",
        async    : true,
        data     : parameter,
        success  : OnReloadMyBB
    });
}

// ----------------------------------------------------------------------------

function ReloadSMFGroups() 
{
    if ( $("#smf_password").val().length == 0 )
    {
        alert("<?php echo L("SMFPasswordEmpty"); ?>");
        return;
    }
    
    if ( $("#smf_password").val() != $("#smf_password_check").val() )
    {
        alert("<?php echo L("SMFPasswordsMatch"); ?>");
        return;
    }
    
    var parameter = {
        database : $("#smf_database").val(),
        user     : $("#smf_user").val(),
        password : $("#smf_password").val(),
        prefix   : $("#smf_prefix").val()
    };
    
    $.ajax({
        type     : "POST",
        url      : "query/groups_smf.php",
        dataType : "xml",
        async    : true,
        data     : parameter,
        success  : OnReloadSMF
    });
}

// ----------------------------------------------------------------------------

function ReloadVanillaGroups() 
{
    if ( $("#vanilla_password").val().length == 0 )
    {
        alert("<?php echo L("VanillaPasswordEmpty"); ?>");
        return;
    }
    
    if ( $("#vanilla_password").val() != $("#vanilla_password_check").val() )
    {
        alert("<?php echo L("VanillaDBPasswordsMatch"); ?>");
        return;
    }
    
    var parameter = {
        database : $("#vanilla_database").val(),
        user     : $("#vanilla_user").val(),
        password : $("#vanilla_password").val(),
        prefix   : $("#vanilla_prefix").val()
    };
    
    $.ajax({
        type     : "POST",
        url      : "query/groups_vanilla.php",
        dataType : "xml",
        async    : true,
        data     : parameter,
        success  : OnReloadVanilla
    });
}

// ----------------------------------------------------------------------------

function ReloadJoomlaGroups() 
{
    if ( $("#joomla_password").val().length == 0 )
    {
        alert("<?php echo L("JoomlaPasswordEmpty"); ?>");
        return;
    }
    
    if ( $("#joomla_password").val() != $("#joomla_password_check").val() )
    {
        alert("<?php echo L("JoomlaDBPasswordsMatch"); ?>");
        return;
    }
    
    var parameter = {
        database : $("#joomla_database").val(),
        user     : $("#joomla_user").val(),
        password : $("#joomla_password").val(),
        prefix   : $("#joomla_prefix").val()
    };
    
    $.ajax({
        type     : "POST",
        url      : "query/groups_joomla.php",
        dataType : "xml",
        async    : true,
        data     : parameter,
        success  : OnReloadJoomla
    });
}

// ----------------------------------------------------------------------------

function ReloadDrupalGroups() 
{
    if ( $("#drupal_password").val().length == 0 )
    {
        alert("<?php echo L("DrupalPasswordEmpty"); ?>");
        return;
    }
    
    if ( $("#drupal_password").val() != $("#drupal_password_check").val() )
    {
        alert("<?php echo L("DrupalDBPasswordsMatch"); ?>");
        return;
    }
    
    var parameter = {
        database : $("#drupal_database").val(),
        user     : $("#drupal_user").val(),
        password : $("#drupal_password").val(),
        prefix   : $("#drupal_prefix").val()
    };
    
    $.ajax({
        type     : "POST",
        url      : "query/groups_drupal.php",
        dataType : "xml",
        async    : true,
        data     : parameter,
        success  : OnReloadDrupal
    });
}

// ----------------------------------------------------------------------------

function CheckEQDKP()
{
    if ( $("#eqdkp_password").val().length == 0 )
    {
        alert("<?php echo L("EQDKPPasswordEmpty"); ?>");
        return;
    }
    
    if ( $("#eqdkp_password").val() != $("#eqdkp_password_check").val() )
    {
        alert("<?php echo L("EQDKPDBPasswordsMatch"); ?>");
        return;
    }
    
    var parameter = {
        database : $("#eqdkp_database").val(),
        user     : $("#eqdkp_user").val(),
        password : $("#eqdkp_password").val(),
        prefix   : $("#eqdkp_prefix").val()
    };
    
    $.ajax({
        type     : "POST",
        url      : "query/groups_eqdkp.php",
        dataType : "xml",
        async    : true,
        data     : parameter,
        success  : OnCheckEQDKP
    });
}

// ----------------------------------------------------------------------------

function CheckBindingForm(a_Parameter) 
{
    var usePhpBB = $("#allow_phpbb3:checked").val() == "on";
    var useEQDKP = $("#allow_eqdkp:checked").val() == "on";
    var useVBulletin = $("#allow_vb3:checked").val() == "on";
    var useMyBB = $("#allow_mybb:checked").val() == "on";
    var useSMF = $("#allow_smf:checked").val() == "on";
    var useVanilla = $("#allow_vanilla:checked").val() == "on";
    var useJoomla = $("#allow_joomla:checked").val() == "on";
    var useDrupal = $("#allow_drupal:checked").val() == "on";
    
    if ( usePhpBB )
    {
        if ( $("#phpbb3_password").val().length == 0 )
        {
            alert("<?php echo L("PHPBBPasswordEmpty"); ?>");
            return;
        }
        
        if ( $("#phpbb3_password").val() != $("#phpbb3_password_check").val() )
        {
            alert("<?php echo L("PHPBBDBPasswordsMatch"); ?>");
            return;
        }
    }
        
    if ( useEQDKP )
    {    
        if ( $("#eqdkp_password").val().length == 0 )
        {
            alert("<?php echo L("EQDKPPasswordEmpty"); ?>");
            return;
        }
        
        if ( $("#eqdkp_password").val() != $("#eqdkp_password_check").val() )
        {
            alert("<?php echo L("EQDKPDBPasswordsMatch"); ?>");
            return;
        }
    }
    
    if ( useVBulletin )
    {    
        if ( $("#vb3_password").val().length == 0 )
        {
            alert("<?php echo L("VBulletinPasswordEmpty"); ?>");
            return;
        }
        
        if ( $("#vb3_password").val() != $("#vb3_password_check").val() )
        {
            alert("<?php echo L("VBulletinDBPasswordsMatch"); ?>");
            return;
        }
    }
    
    if ( useMyBB )
    {    
        if ( $("#mybb_password").val().length == 0 )
        {
            alert("<?php echo L("MyBBPasswordEmpty"); ?>");
            return;
        }
        
        if ( $("#mybb_password").val() != $("#mybb_password_check").val() )
        {
            alert("<?php echo L("MyBBDBPasswordsMatch"); ?>");
            return;
        }
    }
    
    if ( useSMF )
    {    
        if ( $("#smf_password").val().length == 0 )
        {
            alert("<?php echo L("SMFPasswordEmpty"); ?>");
            return;
        }
        
        if ( $("#smf_password").val() != $("#smf_password_check").val() )
        {
            alert("<?php echo L("SMFDBPasswordsMatch"); ?>");
            return;
        }
    }
    
    if ( useVanilla )
    {    
        if ( $("#vanilla_password").val().length == 0 )
        {
            alert("<?php echo L("VanillaPasswordEmpty"); ?>");
            return;
        }
        
        if ( $("#vanilla_password").val() != $("#vanilla_password_check").val() )
        {
            alert("<?php echo L("VanillaDBPasswordsMatch"); ?>");
            return;
        }
    }
    
    if ( useJoomla )
    {    
        if ( $("#joomla_password").val().length == 0 )
        {
            alert("<?php echo L("JoomlaPasswordEmpty"); ?>");
            return;
        }
        
        if ( $("#joomla_password").val() != $("#joomla_password_check").val() )
        {
            alert("<?php echo L("JoomlaDBPasswordsMatch"); ?>");
            return;
        }
    }
    
    if ( useDrupal )
    {    
        if ( $("#drupal_password").val().length == 0 )
        {
            alert("<?php echo L("DrupalPasswordEmpty"); ?>");
            return;
        }
        
        if ( $("#drupal_password").val() != $("#drupal_password_check").val() )
        {
            alert("<?php echo L("DrupalDBPasswordsMatch"); ?>");
            return;
        }
    }
        
    var parameter = {
        phpbb3_check    : usePhpBB,
        phpbb3_database : $("#phpbb3_database").val(),
        phpbb3_user     : $("#phpbb3_user").val(),
        phpbb3_password : $("#phpbb3_password").val(),
        phpbb3_prefix   : $("#phpbb3_prefix").val(),
    
        eqdkp_check    : useEQDKP,
        eqdkp_database : $("#eqdkp_database").val(),
        eqdkp_user     : $("#eqdkp_user").val(),
        eqdkp_password : $("#eqdkp_password").val(),
        eqdkp_prefix   : $("#eqdkp_prefix").val(),

        vb3_check    : useVBulletin,
        vb3_database : $("#vb3_database").val(),
        vb3_user     : $("#vb3_user").val(),
        vb3_password : $("#vb3_password").val(),
        vb3_prefix   : $("#vb3_prefix").val(),
    
        mybb_check    : useMyBB,
        mybb_database : $("#mybb_database").val(),
        mybb_user     : $("#mybb_user").val(),
        mybb_password : $("#mybb_password").val(),
        mybb_prefix   : $("#mybb_prefix").val(),
    
        smf_check    : useSMF,
        smf_database : $("#smf_database").val(),
        smf_user     : $("#smf_user").val(),
        smf_password : $("#smf_password").val(),
        smf_prefix   : $("#smf_prefix").val(),
    
        vanilla_check    : useVanilla,
        vanilla_database : $("#vanilla_database").val(),
        vanilla_user     : $("#vanilla_user").val(),
        vanilla_password : $("#vanilla_password").val(),
        vanilla_prefix   : $("#vanilla_prefix").val(),
    
        joomla_check    : useJoomla,
        joomla_database : $("#joomla_database").val(),
        joomla_user     : $("#joomla_user").val(),
        joomla_password : $("#joomla_password").val(),
        joomla_prefix   : $("#joomla_prefix").val(),
    
        drupal_check    : useDrupal,
        drupal_database : $("#drupal_database").val(),
        drupal_user     : $("#drupal_user").val(),
        drupal_password : $("#drupal_password").val(),
        drupal_prefix   : $("#drupal_prefix").val()
    };
    
    $.ajax({
        type     : "POST",
        url      : "query/install_bindings_check.php",
        dataType : "xml",
        async    : true,
        data     : parameter,
        success  : function(a_XMLData) { OnDbCheckDone(a_XMLData, a_Parameter); }
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
        
        alert("<?php echo L("ConnectionTestFailed"); ?>:\n\n" + errorString );
        return;
    }
    
    // phpbb
    
    var phpbb3_memberGroups = [];
    
    $("#phpbb3_member option:selected").each( function() {
        phpbb3_memberGroups.push( $(this).val() );
    });
    
    var phpbb3_raidLeadGroups = [];
    
    $("#phpbb3_raidlead option:selected").each( function() {
        phpbb3_raidLeadGroups.push( $(this).val() );
    });
    
    // vbulletin
    
    var vb3_memberGroups = [];
    
    $("#vb3_member option:selected").each( function() {
        vb3_memberGroups.push( $(this).val() );
    });
    
    var vb3_raidLeadGroups = [];
    
    $("#vb3_raidlead option:selected").each( function() {
        vb3_raidLeadGroups.push( $(this).val() );
    });
    
    // mybb
    
    var mybb_memberGroups = [];
    
    $("#mybb_member option:selected").each( function() {
        mybb_memberGroups.push( $(this).val() );
    });
    
    var mybb_raidLeadGroups = [];
    
    $("#mybb_raidlead option:selected").each( function() {
        mybb_raidLeadGroups.push( $(this).val() );
    });
    
    // smf
    
    var smf_memberGroups = [];
    
    $("#smf_member option:selected").each( function() {
        smf_memberGroups.push( $(this).val() );
    });
    
    var smf_raidLeadGroups = [];
    
    $("#smf_raidlead option:selected").each( function() {
        smf_raidLeadGroups.push( $(this).val() );
    });
    
    // vanilla
    
    var vanilla_memberGroups = [];
    
    $("#vanilla_member option:selected").each( function() {
        vanilla_memberGroups.push( $(this).val() );
    });
    
    var vanilla_raidLeadGroups = [];
    
    $("#vanilla_raidlead option:selected").each( function() {
        vanilla_raidLeadGroups.push( $(this).val() );
    });
    
    // joomla
    
    var joomla_memberGroups = [];
    
    $("#joomla_member option:selected").each( function() {
        joomla_memberGroups.push( $(this).val() );
    });
    
    var joomla_raidLeadGroups = [];
    
    $("#joomla_raidlead option:selected").each( function() {
        joomla_raidLeadGroups.push( $(this).val() );
    });
    
    // dupal
    
    var drupal_memberGroups = [];
    
    $("#drupal_member option:selected").each( function() {
        drupal_memberGroups.push( $(this).val() );
    });
    
    var drupal_raidLeadGroups = [];
    
    $("#drupal_raidlead option:selected").each( function() {
        drupal_raidLeadGroups.push( $(this).val() );
    });
    
    var parameter = {
        phpbb3_allow    : $("#allow_phpbb3:checked").val() == "on",
        phpbb3_database : $("#phpbb3_database").val(),
        phpbb3_user     : $("#phpbb3_user").val(),
        phpbb3_password : $("#phpbb3_password").val(),
        phpbb3_prefix   : $("#phpbb3_prefix").val(),
        phpbb3_member   : phpbb3_memberGroups,
        phpbb3_raidlead : phpbb3_raidLeadGroups,
    
        eqdkp_allow    : $("#allow_eqdkp:checked").val() == "on",
        eqdkp_database : $("#eqdkp_database").val(),
        eqdkp_user     : $("#eqdkp_user").val(),
        eqdkp_password : $("#eqdkp_password").val(),
        eqdkp_prefix   : $("#eqdkp_prefix").val(),

        vb3_allow    : $("#allow_vb3:checked").val() == "on",
        vb3_database : $("#vb3_database").val(),
        vb3_user     : $("#vb3_user").val(),
        vb3_password : $("#vb3_password").val(),
        vb3_prefix   : $("#vb3_prefix").val(),
        vb3_member   : vb3_memberGroups,
        vb3_raidlead : vb3_raidLeadGroups,

        mybb_allow    : $("#allow_mybb:checked").val() == "on",
        mybb_database : $("#mybb_database").val(),
        mybb_user     : $("#mybb_user").val(),
        mybb_password : $("#mybb_password").val(),
        mybb_prefix   : $("#mybb_prefix").val(),
        mybb_member   : mybb_memberGroups,
        mybb_raidlead : mybb_raidLeadGroups,

        smf_allow    : $("#allow_smf:checked").val() == "on",
        smf_database : $("#smf_database").val(),
        smf_user     : $("#smf_user").val(),
        smf_password : $("#smf_password").val(),
        smf_prefix   : $("#smf_prefix").val(),
        smf_member   : smf_memberGroups,
        smf_raidlead : smf_raidLeadGroups,

        vanilla_allow    : $("#allow_vanilla:checked").val() == "on",
        vanilla_database : $("#vanilla_database").val(),
        vanilla_user     : $("#vanilla_user").val(),
        vanilla_password : $("#vanilla_password").val(),
        vanilla_prefix   : $("#vanilla_prefix").val(),
        vanilla_member   : vanilla_memberGroups,
        vanilla_raidlead : vanilla_raidLeadGroups,

        joomla_allow    : $("#allow_joomla:checked").val() == "on",
        joomla_database : $("#joomla_database").val(),
        joomla_user     : $("#joomla_user").val(),
        joomla_password : $("#joomla_password").val(),
        joomla_prefix   : $("#joomla_prefix").val(),
        joomla_member   : joomla_memberGroups,
        joomla_raidlead : joomla_raidLeadGroups,

        drupal_allow    : $("#allow_drupal:checked").val() == "on",
        drupal_database : $("#drupal_database").val(),
        drupal_user     : $("#drupal_user").val(),
        drupal_password : $("#drupal_password").val(),
        drupal_prefix   : $("#drupal_prefix").val(),
        drupal_member   : drupal_memberGroups,
        drupal_raidlead : drupal_raidLeadGroups
    };
    
    $.ajax({
        type     : "POST",
        url      : "query/submit_bindings.php",
        dataType : "xml",
        async    : true,
        data     : parameter,
        success  : function() { open(a_NextPage); }
    });
}

// ----------------------------------------------------------------------------

function showConfig( a_Name )
{
    $(".config").hide();    
    $(".tab_active").removeClass("tab_active").addClass("tab_inactive");
    
    $("#"+a_Name).show();    
    $("#button_"+a_Name).removeClass("tab_inactive").addClass("tab_active");
}