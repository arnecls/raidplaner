var g_NewCharIndex = 0;

function generateCharacterSlot( a_Id, a_Name, a_Mainchar, a_Class, a_Role1, a_Role2 )
{
	HTMLString = "<span class=\"charslot\" id=\"char" + a_Id + "\">";	
	
	HTMLString += "<div class=\"clearchar\"></div>";
	HTMLString += "<div class=\"class\" style=\"background: url(images/classesbig/" + a_Class + ".png)\">";
	
	if ( a_Mainchar )
		HTMLString += "<div class=\"badge mainchar\"></div>";
	else
		HTMLString += "<div class=\"badge twink\"></div>";

	HTMLString += "</div>";
	HTMLString += "<div class=\"name\">" + a_Name + "</div>";	
	HTMLString += "<span><img class=\"role role1\" style=\"margin-left:24px\" src=\"images/classessmall/" + a_Role1 + ".png\"/></span>";
	HTMLString += "<span><img class=\"role role2\" src=\"images/classessmall/" + a_Role2 + ".png\"/></span>";
		
	HTMLString += "</span>";
	
	return HTMLString;
}

// -----------------------------------------------------------------------------

function generateNewCharacterSlot()
{
	HTMLString = "<span class=\"charslot newchar\" id=\"newchar" + g_NewCharIndex + "\">";	
	
	HTMLString += "<div class=\"clearchar clearalways\"></div>";
	HTMLString += "<div class=\"class newclass\" style=\"background: url(images/classesbig/empty.png)\">";
	
	if ( $(".charslot").length == 0 )
		HTMLString += "<div class=\"badge mainchar\"></div>";
	else
		HTMLString += "<div class=\"badge twink\"></div>";
	
	HTMLString += "</div>";
	HTMLString += "<input type=\"text\" class=\"newname\">";	
	HTMLString += "<span><img class=\"role newrole1\" style=\"margin-left:24px\" src=\"images/classessmall/dmg.png\"/></span>";
	HTMLString += "<span><img class=\"role newrole2\" src=\"images/classessmall/dmg.png\"/></span>";
		
	HTMLString += "</span>";
	
	return HTMLString;
}

// -----------------------------------------------------------------------------

function addCharacter()
{
	$(".charslot_add").before( generateNewCharacterSlot() );
	
	isOnlyChar = $(".charslot").length == 1;
	
	var charData = {
		id        : 0,
		charClass : "empty",
		mainChar  : isOnlyChar,
		role1     : "dmg",
		role2     : "dmg"
	};

	$("#newchar" + g_NewCharIndex).data( "charData", charData );
	
	++g_NewCharIndex;
	
	$(".badge").click( function( event ) { toggleMainChar( $(this) ); event.stopPropagation(); } );	
	$(".newrole1").click( function( event ) { showTooltipRoleList( $(this), true ); event.stopPropagation(); } );
	$(".newrole2").click( function( event ) { showTooltipRoleList( $(this), false ); event.stopPropagation(); } );
	$(".newclass").click( function( event ) { showTooltipClassList( $(this) ); event.stopPropagation(); } );
	$(".clearalways").click( function() { removeCharacter( $(this), true ); } );
	
	$("#charlist").animate( { "scrollLeft" : $(".charslot_add").outerWidth() * $(".charslot").length }, 500 );
}

// -----------------------------------------------------------------------------

function removeCharacter( a_Node, a_Always )
{
	if ( a_Always )
	{
		var slotNode = a_Node.parent();
		
		if ( slotNode.data( "charData" ).mainChar )
		{
			toggleMainChar( $(".charslot").children(".class").children(".badge").first() );
		}
		
		slotNode.detach();
	}
	else
	{
		confirm( L("Do you really want to delete this character?") + "<br>" + L("All existing attendances will be removed, too."), 
				 L("Delete character"), L("Cancel"), 
				 function() {
				 	removeCharacter( a_Node, true );
				 }
		);
	}
}

// -----------------------------------------------------------------------------

function toggleMainChar( a_BadgeNode )
{
	var slotNode = a_BadgeNode.parent().parent();
	
	if ( !slotNode.data( "charData" ).mainChar )
	{
		$(".charslot").each( function() {
			
			$(this).children(".class").children(".badge")
				.removeClass( "mainchar" )
				.removeClass( "twink" )
				.addClass( "twink" );
				
			$(this).data( "charData" ).mainChar = false;
		});
			
		a_BadgeNode.removeClass( "twink" ).addClass( "mainchar" );
			
		slotNode.data( "charData" ).mainChar = true;
	}
}

// -----------------------------------------------------------------------------

function setupCharacters( a_Message )
{
	a_Message.children("character").each( function() {
		
		$(".charslot_add").before( generateCharacterSlot(
			$(this).children("id").text(),
			$(this).children("name").text(),
			($(this).children("mainchar").text() == "true" ),
			$(this).children("class").text(),
			$(this).children("role1").text(),
			$(this).children("role2").text()) );
			
		var charData = {
			id        : $(this).children("id").text(),
			charClass : $(this).children("class").text(),
			mainChar  : ($(this).children("mainchar").text() == "true"),
			role1     : $(this).children("role1").text(),
			role2     : $(this).children("role2").text()
		};
			
		$("#char" + $(this).children("id").text() ).data( "charData", charData );
	});
	
	$(".badge").click( function() { toggleMainChar( $(this) ); } );	
	$(".role1").click( function( event ) { showTooltipRoleList( $(this), true ); event.stopPropagation(); } );
	$(".role2").click( function( event ) { showTooltipRoleList( $(this), false ); event.stopPropagation(); } );
	
	$(".charslot_add").click( function() { addCharacter(); } );
	$(".clearchar").click( function() { removeCharacter( $(this), false ); } );
}

// -----------------------------------------------------------------------------

function setupAttendance( a_Message )
{
	var numRaids   = parseInt( a_Message.children("attendance").children("raids").text() );
	var numOk      = parseInt( a_Message.children("attendance").children("ok").text() );
	var numAvail   = parseInt( a_Message.children("attendance").children("available").text() );
	var numUnavail = parseInt( a_Message.children("attendance").children("unavailable").text() );
	var numMissed  = numRaids - (numOk + numAvail + numUnavail);
	
	var numDmg     = parseInt( a_Message.children("attendance").children("dmg").text() );
	var numHeal    = parseInt( a_Message.children("attendance").children("heal").text() );
	var numTank    = parseInt( a_Message.children("attendance").children("tank").text() );
	var numRoles   = numDmg + numHeal + numTank;
	
	var barSize = 750;
	
	var sizeOk      = (numOk / numRaids) * barSize;
	var sizeAvail   = (numAvail / numRaids) * barSize;
	var sizeUnavail = (numUnavail / numRaids) * barSize;
	var sizeMissed  = (numMissed / numRaids) * barSize;
	
	var sizeDmg  = (numDmg / numOk) * barSize;
	var sizeHeal = (numHeal / numOk) * barSize;
	var sizeTank = (numTank / numOk) * barSize;
	
	HTMLString = "<div class=\"attendanceCount\" style=\"" + barSize + "px\"><span class=\"start\"></span>";
	
	if (numOk > 0)      HTMLString += "<span class=\"ok\" style=\"width: " + sizeOk.toFixed() + "px\"><div class=\"count\">" + numOk + "</div></span>";
	if (numAvail > 0)   HTMLString += "<span class=\"available\" style=\"width: " + sizeAvail.toFixed() + "px\"><div class=\"count\">" + numAvail + "</div></span>";
	if (numUnavail > 0) HTMLString += "<span class=\"unavailable\" style=\"width: " + sizeUnavail.toFixed() + "px\"><div class=\"count\">" + numUnavail + "</div></span>";
	if (numMissed > 0)  HTMLString += "<span class=\"missed\" style=\"width: " + sizeMissed.toFixed() + "px\"><div class=\"count\">" + numMissed + "</div></span>";
	if (numRaids == 0)  HTMLString += "<span class=\"missed\" style=\"width: " + barSize + "px\"><div class=\"count\">&nbsp;</div></span>";
	
	HTMLString += "<span class=\"end\"></span></div>";
	HTMLString += "<div class=\"label\">" + L("Raid attendance") + "</div>";
	HTMLString += "<div class=\"attendanceType\" style=\"" + barSize + "px\"><span class=\"start\"></span>";
	
	if (numDmg > 0)    HTMLString += "<span class=\"dmg\" style=\"width: " + sizeDmg.toFixed() + "px\"><div class=\"count\">" + numDmg + "</div></span>";
	if (numHeal > 0)   HTMLString += "<span class=\"heal\" style=\"width: " + sizeHeal.toFixed() + "px\"><div class=\"count\">" + numHeal + "</div></span>";
	if (numTank > 0)   HTMLString += "<span class=\"tank\" style=\"width: " + sizeTank.toFixed() + "px\"><div class=\"count\">" + numTank + "</div></span>";
	if (numRoles == 0) HTMLString += "<span class=\"missed\" style=\"width: " + barSize + "px\"><div class=\"count\">&nbsp;</div></span>";
	
	HTMLString += "<span class=\"end\"></span></div>";
	HTMLString += "<div class=\"label\">" + L("Roles in attended raids") + "</div>";
	
	HTMLString += "<div class=\"labels\">";
	
	HTMLString += "<div>";
	HTMLString += "<div class=\"box ok\"><span class=\"start\"></span><span class=\"end\"></span></div>";
	HTMLString += "<div class=\"label\">" + numOk + "x " + L("Attended") + "</div>";
	
	HTMLString += "<div class=\"box available\"><span class=\"start\"></span><span class=\"end\"></span></div>";
	HTMLString += "<div class=\"label\">" + numAvail + "x " + L("Queued") + "</div>";
	
	HTMLString += "<div class=\"box unavailable\"><span class=\"start\"></span><span class=\"end\"></span></div>";
	HTMLString += "<div class=\"label\">" + numUnavail + "x " + L("Absent") + "</div>";
	
	HTMLString += "<div class=\"box missed\"><span class=\"start\"></span><span class=\"end\"></span></div>";
	HTMLString += "<div class=\"label\">" + numMissed + "x " + L("Missed") + "</div>";
	HTMLString += "</div>";
	
	HTMLString += "<div style=\"clear: left; padding-top: 5px\">";
	HTMLString += "<div><div class=\"box dmg\"><span class=\"start\"></span><span class=\"end\"></span></div>";
	HTMLString += "<div class=\"label\">" + numDmg + "x " + L("Damage") + "</div>";
	
	HTMLString += "<div class=\"box heal\"><span class=\"start\"></span><span class=\"end\"></span></div>";
	HTMLString += "<div class=\"label\">" + numHeal + "x " + L("Healer") + "</div>";
	
	HTMLString += "<div class=\"box tank\"><span class=\"start\"></span><span class=\"end\"></span></div>";
	HTMLString += "<div class=\"label\">" + numTank + "x " + L("Tank") + "</div>";
	HTMLString += "</div>";
	
	HTMLString += "</div>";
	
	$("#attendance").empty().append(HTMLString);
}

// -----------------------------------------------------------------------------

function displayProfile( a_XMLData )
{
	var Message = $(a_XMLData).children("messagehub");
	var HTMLString = "<div id=\"profile\">";
	
	HTMLString += "<h1>" + L("Your characters") + "</h1>";
	
	HTMLString += "<div id=\"charlist\">";
	HTMLString += "<span class=\"charslot_add\"></span>";
	HTMLString += "</div>";
	HTMLString += "<button id=\"profile_apply\" class=\"button_profile\" type=\"button\">" + L("Apply changes") + "</button>";
	
	if ( Message.children("binding").text() == "none" )
	{
		HTMLString += "<button id=\"profile_password\" class=\"button_profile\" type=\"button\">" + L("Change password") + "</button>";
	}
	
	HTMLString += "<h1 style=\"margin-top: 50px\">" + L("Raid attendance") + "</h1>";
	HTMLString += "<div id=\"attendance\">";
	HTMLString += "</div>";
	
	HTMLString += "</div>";
	
	$("#body").empty().append(HTMLString);
	
	setupCharacters( Message );
	setupAttendance( Message );
	
	$("#profile_apply").button({ icons: { secondary: "ui-icon-disk" }})
		.click( function() { triggerProfileUpdate( false ); } )
		.css( "font-size", 11 );
		
	$("#profile_password").button({ icons: { secondary: "ui-icon-locked" }})
		.click( function() { displayChangePassword( false ); } )
		.css( "font-size", 11 );
}

// -----------------------------------------------------------------------------

function displayForeignProfile( a_XMLData )
{
	var Message = $(a_XMLData).children("messagehub");
	var HTMLString = "<div id=\"profile\">";
	
	HTMLString += "<h1>" + L("Edit characters for") + " " + Message.children("name").text() + "</h1>";
	
	HTMLString += "<div id=\"charlist\">";
	HTMLString += "<span class=\"charslot_add\"></span>";
	HTMLString += "</div>";
	HTMLString += "<button id=\"profile_apply\" class=\"button_profile\" type=\"button\">" + L("Apply changes") + "</button>";
	
	if ( Message.children("binding").text() == "none" )
	{
		HTMLString += "<button id=\"profile_password\" class=\"button_profile\" type=\"button\">" + L("Change password") + "</button>";
	} 
	
	HTMLString += "<h1 style=\"margin-top: 50px\">" + L("Raid attendance") + "</h1>";
	HTMLString += "<div id=\"attendance\">";
	HTMLString += "</div>";
		  
	HTMLString += "</div>";
	
	$("#body").empty().append(HTMLString);
	
	setupCharacters( Message );
	setupAttendance( Message );
	
	$("#charlist").data( "userid", Message.children("userid").text() );
	
	$("#profile_apply").button({ icons: { secondary: "ui-icon-disk" }})
		.click( function() { triggerProfileUpdate( true ); } )
		.css( "font-size", 11 );
		
	$("#profile_password").button({ icons: { secondary: "ui-icon-locked" }})
		.click( function() { displayChangePassword( true ); } )
		.css( "font-size", 11 );
}

// -----------------------------------------------------------------------------

function displayChangePassword( a_ForeignUser )
{
	if ( g_User == null ) 
		return;
	
	var foreignUserId  = 0;
	var requireOldPass = true;
	
	if ( a_ForeignUser )
	{	
		foreignUserId = parseInt( $("#charlist").data("userid") );
		
		if ( foreignUserId != g_User.id )
			requireOldPass = false;
	}
		
	var HTMLString = "";
	
	HTMLString += "<div class=\"login\">";
	
	if ( requireOldPass )
	{
		HTMLString += "<div>";
		HTMLString += "<input id=\"logindummy1\" type=\"text\" class=\"text\" value=\"" + L("Old password") + "\"/>";
		HTMLString += "<input id=\"loginold\" type=\"password\" class=\"textactive\" name=\"old_pass\"/>";
		HTMLString += "</div>";
	}
	
	HTMLString += "<div>";
	HTMLString += "<input id=\"logindummy2\" type=\"text\" class=\"text\" value=\"" + L("Password") + "\"/>";
	HTMLString += "<input id=\"loginpass\" type=\"password\" class=\"textactive\" name=\"new_pass\"/>";
	HTMLString += "</div>";
	HTMLString += "<div>";
	HTMLString += "<input id=\"logindummy3\" type=\"text\" class=\"text\" value=\"" + L("Repeat password") + "\"/>";
	HTMLString += "<input id=\"loginpass_repeat\" type=\"password\" class=\"textactive\" name=\"pass_repeat\"/>";
	HTMLString += "</div>";
	HTMLString += "<button id=\"dochange\" style=\"margin-left: 5px; margin-top: 10px; font-size: 11px\">" + L("Change password") + "</button>";
	HTMLString += "</div>";
	
	$("#body").empty().append(HTMLString);
	
	if ( requireOldPass )
	{
		// Old password
		
		$("#loginold").hide();
		$("#logindummy1").show();
	
		$("#logindummy1").focus( function() { 
			$("#logindummy1").hide(); 
			$("#loginold").show().focus(); 
		});
	
		$("#loginold").blur( function() { 
			if ( $("#loginold").val() == "" ) {
				$("#loginold").hide();
				$("#logindummy1").show(); 
			}
		});
	}
	
	// Password
	
	$("#loginpass").hide();
	$("#logindummy2").show();
	
	$("#logindummy2").focus( function() { 
		$("#logindummy2").hide(); 
		$("#loginpass").show().focus(); 
	});
		
	$("#loginpass").blur( function() { 
		if ( $("#loginpass").val() == "" ) {
			$("#loginpass").hide();
			$("#logindummy2").show(); 
		}
	});
	
	// Repeat
	
	$("#loginpass_repeat").hide();
	$("#logindummy3").show();
	
	$("#logindummy3").focus( function() { 
		$("#logindummy3").hide(); 
		$("#loginpass_repeat").show().focus(); 
	});
		
	$("#loginpass_repeat").blur( function() { 
		if ( $("#loginpass_repeat").val() == "" ) {
			$("#loginpass_repeat").hide();
			$("#logindummy3").show(); 
		}
	});
	
	$("#dochange").button().click( function() {
	
		if ( $("#loginpass").val() != $("#loginpass_repeat").val() )
		{
			notify( L("Passwords did not match.") );
		}
		else
		{
			var parameters = {
				passOld : $("#loginold").val(),
				passNew : $("#loginpass").val(),
				id      : (a_ForeignUser) ? foreignUserId : 0	
			};
						
			if ( a_ForeignUser )
			{
				AsyncQuery( "change_password", parameters, displayForeignProfile );
			}
			else
			{			
				AsyncQuery( "change_password", parameters, displayProfile );
			}
		}
	});
}

// -----------------------------------------------------------------------------

function triggerProfileUpdate( a_ForeignUser )
{
	if ( g_User == null ) 
		return;
	
	var idArray       = Array();
	var nameArray     = Array();
	var classArray    = Array();
	var mainCharArray = Array();
	var role1Array    = Array();
	var role2Array    = Array();
	
	var invalidData = false;
	
	
	$(".charslot").each( function () {
		
		var charData = $(this).data("charData");
		
		idArray.push( charData.id );
		classArray.push( charData.charClass );
		mainCharArray.push( charData.mainChar );
		role1Array.push( charData.role1 );
		role2Array.push( charData.role2 );
		
		if ( $(this).hasClass("newchar") )
		{
			if ( charData.charClass == "empty" )
			{
				notify( L("Error.") + "<br>" + L("A new character has no class assigned.") );
				invalidData = true;
			}			
			else if ( $(this).children(".newname").val().length == 0 )
			{
				notify( L("Error.") + "<br>" + L("A new character has no name assigned.") );
				invalidData = true;
			}
			
			nameArray.push( $(this).children(".newname").val() );
		}
		else
		{
			nameArray.push("");
		}
	});
	
	if ( !invalidData )
	{
	
		if ( a_ForeignUser )
		{	
			var parameters = {
				userid    : $("#charlist").data("userid"),
				id	      : idArray,
				name      : nameArray,
				charClass : classArray,
				mainChar  : mainCharArray,
				role1     : role1Array,
				role2     : role2Array
			};
			
			AsyncQuery( "profile_update", parameters, displaySettings );
		}
		else
		{
			var parameters = {
				id	      : idArray,
				name      : nameArray,
				charClass : classArray,
				mainChar  : mainCharArray,
				role1     : role1Array,
				role2     : role2Array
			};
			
			AsyncQuery( "profile_update", parameters, displayProfile );
		}
	}
}

// -----------------------------------------------------------------------------

function loadProfile()
{
	reloadUser();
	
	if ( g_User == null ) 
		return;
		
	$("#body").empty();
	
	var Parameters = {
	};
		
	AsyncQuery( "query_profile", Parameters, displayProfile );
}