function onChangeContext( event )
{	
	var name = event.currentTarget.id;	
	name = name.substring( name.indexOf("_")+1, name.length );
	
	changeContext( name );
}

// -----------------------------------------------------------------------------

function changeContext( a_Name )
{
    var idIndex = a_Name.lastIndexOf(",");
	var name    = (idIndex == -1) ? a_Name : a_Name.substring( 0, idIndex );
	var hash    = window.location.hash.substring( 1, window.location.hash.length );
	
	$(".menu_button").removeClass("on");	
	$("#button_" + name).addClass("on");
	
	hideTooltip();
	
	if ( hash != a_Name )
	{
		var url = window.location.href.substr(0, window.location.href.lastIndexOf("#"));
	
		window.location.href = url + "#" + a_Name;
		return true;
	}
	
	return false;
}

// -----------------------------------------------------------------------------

function reloadUser()
{
	$.getScript("lib/script/user.js.php");
	$.getScript("lib/script/calendarsession.js.php");
}

// -----------------------------------------------------------------------------

function confirm( a_Text, a_Yes, a_No, a_Callback )
{
	$("#dialog").dialog("destroy");
	$("#dialog").empty().append( a_Text );
	var dialogWidget = $("#dialog").dialog({
		resizable: false,
		draggable: false,
		width: 400,
		title: L("Notification"),
		dialogClass: "dialogFix"
	});	
	
	dialogWidget.dialog( "option", "buttons", [{ 
			text: a_Yes,
			click: function() { 
				$( this ).dialog( "close" ); 
				$("#eventblocker").css("visibility", "hidden"); 
				a_Callback(); 
			}
		},
		{ 
			text: a_No,
			click: function() { 
				$( this ).dialog( "close" );
			}
		}]
	);
	
	dialogWidget.dialog({
   		close: function() { $("#eventblocker").css("visibility", "hidden"); }
	});
	
	$(".ui-dialog-buttonset").children().css("font-size", 11);
	
	$(".ui-dialog-buttonset").children().last().focus();	
	$("#eventblocker").css("visibility", "visible");
}

// -----------------------------------------------------------------------------

function notify( a_Text )
{
	$("#dialog").dialog("destroy");
	$("#dialog").empty().append( a_Text );
	var dialogWidget = $("#dialog").dialog({
		resizable: false,
		draggable: false,
		title: L("Notification"),
		width: 400,
		dialogClass: "dialogFix"
	});	
	
	dialogWidget.dialog( "option", "buttons", [{ 
		text: "Ok",
		click: function() { 
			$( this ).dialog( "close" ); 
		}
	}]);
	
	dialogWidget.dialog({
   		close: function() { $("#eventblocker").css("visibility", "hidden"); }
	});
	
	$(".ui-dialog-buttonset").children().css("font-size", 11);
	
	$(".ui-dialog-buttonset").children().last().focus();
	$("#eventblocker").css("visibility", "visible"); 
}

// -----------------------------------------------------------------------------

function prompt( a_Text, a_Ok, a_Cancel, a_Callback )
{
	var HTMLString = "<br/><input id=\"prompt_text\" class=\"prompt_text\"/>";

	$("#dialog").dialog("destroy");
	$("#dialog").empty()
		.append( a_Text )
		.append( HTMLString );
	
	var dialogWidget = $("#dialog").dialog({
		resizable: false,
		draggable: false,
		width: 400,
		title: L("Input required"),
		dialogClass: "dialogFix"
	});	
	
	dialogWidget.dialog( "option", "buttons", [{ 
			text: a_Ok,
			click: function() { 
				$( this ).dialog( "close" ); 
				$("#eventblocker").css("visibility", "hidden"); 
				a_Callback(); 
			}
		},
		{ 
			text: a_Cancel,
			click: function() { 
				$( this ).dialog( "close" ); 
			}
		}]
	);
	
	dialogWidget.dialog({
   		close: function() { $("#eventblocker").css("visibility", "hidden"); }
	});
	
	$(".ui-dialog-buttonset").children().css("font-size", 11);
	
	$(".ui-dialog-buttonset").children().last().focus();	
	$("#eventblocker").css("visibility", "visible");
}

// -----------------------------------------------------------------------------

function onAjaxDone( a_Event, a_XHR, a_Options )
{
	$("#ajaxblocker").clearQueue().hide();	
	var errors = $(a_XHR.responseXML).children("messagehub").children("error");
	
	if ( errors.size() > 0 )
	{
		var message = L("A request returned an error.") + "<br/>";
		
		errors.each( function() {
			message += "<br/><br/>" + $(this).text();
		});
		
		
		notify( message );
	}
}

// -----------------------------------------------------------------------------

function onAjaxError( a_Event, a_XHR, a_Options, a_Error )
{
	$("#ajaxblocker").clearQueue().hide();	
	
	var errors = $(a_XHR.responseXML).children("messagehub").children("error");
	
	var message = L("A request returned an error.") + "<br/><br/>";
	message    += a_Error + "<br/>";
		
	if ( errors.size() > 0 )
	{		
		errors.each( function( index, element ) {
			message += "<br/>"+element.text();
		});
	}
	
	notify( message );
}

// -----------------------------------------------------------------------------

jQuery(document).ready( function() {

	if ( $("#tooltip").size() > 0)
		$("#tooltip").hide();

	if ( $("#closesheet").size() > 0)
		$("#closesheet").click( closeSheet );
	
	if ( $("#sheetoverlay").size() > 0)
	{
		$("#sheetoverlay").hide();
		$("#newlocation").hide();
		$("#description").hide();
	}
	
	$("#dialog").hide();
	
	$("#ajaxblocker").hide();
	
	$("#ajaxblocker").ajaxSend( function() {
		$("#ajaxblocker").delay(2000).show(1);
	});
	
	$("#ajaxblocker").ajaxComplete( onAjaxDone );	
	$("#ajaxblocker").ajaxError( onAjaxError );
	
	$(".button_logout").button({ 
		icons: { secondary: "ui-icon-eject" }
	}).css( "font-size", 11 );
	
	$(window).resize( hideTooltip );
	
	initMenu();
	initLogin();	
});