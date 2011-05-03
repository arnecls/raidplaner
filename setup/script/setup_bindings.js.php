<?php
	define( "LOCALE_SETUP", true );
	require_once(dirname(__FILE__)."/../../lib/private/locale.php");
?>

function onReloadPHPBB3( a_XMLData )
{
	var groups = $(a_XMLData).children("grouplist");
	
	if ( groups.children("error").size() > 0 )
	{
		var errorString = "";
		
		groups.children("error").each( function() {
			errorString += $(this).text() + "\n";
		});
		
		alert("<?php echo L("Reload failed"); ?>:\n\n" + errorString );
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

function onReloadVB3( a_XMLData )
{
	var groups = $(a_XMLData).children("grouplist");
	
	if ( groups.children("error").size() > 0 )
	{
		var errorString = "";
		
		groups.children("error").each( function() {
			errorString += $(this).text() + "\n";
		});
		
		alert("<?php echo L("Reload failed"); ?>:\n\n" + errorString );
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

function onCheckEQDKP( a_XMLData )
{
	var groups = $(a_XMLData).children("check");
	
	if ( groups.children("error").size() > 0 )
	{
		var errorString = "";
		
		groups.children("error").each( function() {
			errorString += $(this).text() + "\n";
		});
		
		alert("<?php echo L("Connection test failed"); ?>:\n\n" + errorString );
		return;
	}
	
	alert("<?php echo L("Connection test succeeded"); ?>");
}

function reloadPHPBB3Groups() 
{
	if ( $("#phpbb3_password").val().length == 0 )
	{
		alert("<?php echo L("PHPBB Database password must not be empty."); ?>");
		return;
	}
	
	if ( $("#phpbb3_password").val() != $("#phpbb3_password_check").val() )
	{
		alert("<?php echo L("PHPBB Database passwords did not match."); ?>");
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
		success  : onReloadPHPBB3
	});
}

function reloadVB3Groups() 
{
	if ( $("#vb3_password").val().length == 0 )
	{
		alert("<?php echo L("vBulletin Database password must not be empty."); ?>");
		return;
	}
	
	if ( $("#vb3_password").val() != $("#vb3_password_check").val() )
	{
		alert("<?php echo L("vBulletin Database passwords did not match."); ?>");
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
		success  : onReloadVB3
	});
}

function checkEQDKP()
{
	if ( $("#eqdkp_password").val().length == 0 )
	{
		alert("<?php echo L("EQDKP Database password must not be empty."); ?>");
		return;
	}
	
	if ( $("#eqdkp_password").val() != $("#eqdkp_password_check").val() )
	{
		alert("<?php echo L("EQDKP Database passwords did not match."); ?>");
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
		url      : "query/test_eqdkp.php",
		dataType : "xml",
		async    : true,
		data     : parameter,
		success  : onCheckEQDKP
	});
}

function checkForm() 
{
	if ( $("#allow_phpbb3").attr("checked") )
	{
		if ( $("#phpbb3_password").val().length == 0 )
		{
			alert("<?php echo L("PHPBB Database password must not be empty."); ?>");
			return;
		}
		
		if ( $("#phpbb3_password").val() != $("#phpbb3_password_check").val() )
		{
			alert("<?php echo L("PHPBB Database passwords did not match."); ?>");
			return;
		}
	}
		
	if ( $("#allow_eqdkp").attr("checked") )
	{	
		if ( $("#eqdkp_password").val().length == 0 )
		{
			alert("<?php echo L("EQDKP Database password must not be empty."); ?>");
			return;
		}
		
		if ( $("#eqdkp_password").val() != $("#eqdkp_password_check").val() )
		{
			alert("<?php echo L("EQDKP Database passwords did not match."); ?>");
			return;
		}
	}
	
	if ( $("#allow_vb3").attr("checked") )
	{	
		if ( $("#vb3_password").val().length == 0 )
		{
			alert("<?php echo L("vBulletin Database password must not be empty."); ?>");
			return;
		}
		
		if ( $("#vb3_password").val() != $("#vb3_password_check").val() )
		{
			alert("<?php echo L("vBulletin Database passwords did not match."); ?>");
			return;
		}
	}
		
	var parameter = {
		phpbb3_check    : $("#allow_phpbb3").attr("checked"),
		phpbb3_database : $("#phpbb3_database").val(),
		phpbb3_user     : $("#phpbb3_user").val(),
		phpbb3_password : $("#phpbb3_password").val(),
		phpbb3_prefix   : $("#phpbb3_prefix").val(),

		vb3_check    : $("#allow_vb3").attr("checked"),
		vb3_database : $("#vb3_database").val(),
		vb3_user     : $("#vb3_user").val(),
		vb3_password : $("#vb3_password").val(),
		vb3_prefix   : $("#vb3_prefix").val(),
	
		eqdkp_check    : $("#allow_eqdkp").attr("checked"),
		eqdkp_database : $("#eqdkp_database").val(),
		eqdkp_user     : $("#eqdkp_user").val(),
		eqdkp_password : $("#eqdkp_password").val(),
		eqdkp_prefix   : $("#eqdkp_prefix").val()
	};
	
	$.ajax({
		type     : "POST",
		url      : "query/setup_bindings_check.php",
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
			errorString += $(this).prev().text() + ": " + $(this).text() + "\n\n";
		});
		
		alert("<?php echo L("Connection test failed"); ?>:\n\n" + errorString );
		return;
	}
	
	var phpbb3_memberGroups = new Array();
	
	$("#phpbb3_member").children("option").each( function() {
		if ( $(this)[0].selected )
			phpbb3_memberGroups.push( $(this).attr("value") );
	});
	
	var phpbb3_raidLeadGroups = new Array();
	
	$("#phpbb3_raidlead").children("option").each( function() {
		if ( $(this)[0].selected )
			phpbb3_raidLeadGroups.push( $(this).attr("value") );
	});
	
	var vb3_memberGroups = new Array();
	
	$("#vb3_member").children("option").each( function() {
		if ( $(this)[0].selected )
			vb3_memberGroups.push( $(this).attr("value") );
	});
	
	var vb3_raidLeadGroups = new Array();
	
	$("#vb3_raidlead").children("option").each( function() {
		if ( $(this)[0].selected )
			vb3_raidLeadGroups.push( $(this).attr("value") );
	});
	
	var parameter = {
		phpbb3_allow    : $("#allow_phpbb3").attr("checked"),
		phpbb3_database : $("#phpbb3_database").val(),
		phpbb3_user     : $("#phpbb3_user").val(),
		phpbb3_password : $("#phpbb3_password").val(),
		phpbb3_prefix   : $("#phpbb3_prefix").val(),
		phpbb3_member   : phpbb3_memberGroups,
		phpbb3_raidlead : phpbb3_raidLeadGroups,

		vb3_allow    : $("#allow_vb3").attr("checked"),
		vb3_database : $("#vb3_database").val(),
		vb3_user     : $("#vb3_user").val(),
		vb3_password : $("#vb3_password").val(),
		vb3_prefix   : $("#vb3_prefix").val(),
		vb3_member   : vb3_memberGroups,
		vb3_raidlead : vb3_raidLeadGroups,
	
		eqdkp_allow    : $("#allow_eqdkp").attr("checked"),
		eqdkp_database : $("#eqdkp_database").val(),
		eqdkp_user     : $("#eqdkp_user").val(),
		eqdkp_password : $("#eqdkp_password").val(),
		eqdkp_prefix   : $("#eqdkp_prefix").val()
	};
	
	$.ajax({
		type     : "POST",
		url      : "query/setup_bindings_done.php",
		dataType : "xml",
		async    : true,
		data     : parameter,
		success  : loadCleanup
	});
}

$(document).ready( function() {
	$("#eqdkp").hide();
	$("#vbulletin").hide();
});

function showConfig( a_Name )
{
	$("#phpbb3").hide();
	$("#eqdkp").hide();
	$("#vbulletin").hide();
	
	$(".tab_active").removeClass("tab_active").addClass("tab_inactive");
	
	$("#"+a_Name).show();
	
	$("#button_"+a_Name).removeClass("tab_inactive").addClass("tab_active");
}