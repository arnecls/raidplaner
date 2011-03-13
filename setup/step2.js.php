<?php
	define( "LOCALE_SETUP", true );
	require_once(dirname(__FILE__)."/../lib/private/locale.php");
?>

function onReload( a_XMLData )
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
	
	$("#member").empty().append( HTMLString );
	$("#raidlead").empty().append( HTMLString );
}

function reloadGroups() 
{
	if ( $("#password").val().length == 0 )
	{
		alert("<?php echo L("Database password must not be empty."); ?>");
		return;
	}
	
	if ( $("#password").val() != $("#password_check").val() )
	{
		alert("<?php echo L("Database passwords did not match."); ?>");
		return;
	}
	
	var parameter = {
		database : $("#database").val(),
		user     : $("#user").val(),
		password : $("#password").val(),
		prefix   : $("#prefix").val()
	};
	
	$.ajax({
		type     : "POST",
		url      : "step2_reload.php",
		dataType : "xml",
		async    : true,
		data     : parameter,
		success  : onReload
	});
}

function checkForm() 
{
	if ( $("#password").val().length == 0 )
	{
		alert("<?php echo L("Database password must not be empty."); ?>");
		return;
	}
	
	if ( $("#password").val() != $("#password_check").val() )
	{
		alert("<?php echo L("Database passwords did not match."); ?>");
		return;
	}
	
	var parameter = {
		database : $("#database").val(),
		user     : $("#user").val(),
		password : $("#password").val(),
		prefix   : $("#prefix").val()
	};
	
	$.ajax({
		type     : "POST",
		url      : "step2_check.php",
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
		
		alert("<?php echo L("Connection test failed"); ?>:\n\n" + errorString );
		return;
	}
	
	var memberGroups = new Array();
	
	$("#member").children("option").each( function() {
		if ( $(this)[0].selected )
			memberGroups.push( $(this).attr("value") );
	});
	
	var raidLeadGroups = new Array();
	
	$("#raidlead").children("option").each( function() {
		if ( $(this)[0].selected )
			raidLeadGroups.push( $(this).attr("value") );
	});
	
	var parameter = {
		database  : $("#database").val(),
		user      : $("#user").val(),
		password  : $("#password").val(),
		prefix    : $("#prefix").val(),
		allow	  : $("#allow_phpbb3")[0].checked,
		member    : memberGroups,
		raidlead  : raidLeadGroups
	};
	
	$.ajax({
		type     : "POST",
		url      : "step2_done.php",
		dataType : "xml",
		async    : true,
		data     : parameter,
		success  : loadStep3
	});
}

function loadStep3()
{
	var url = window.location.href.substring( 0, window.location.href.lastIndexOf("/") );
	window.location.href = url + "/step3.php";
}