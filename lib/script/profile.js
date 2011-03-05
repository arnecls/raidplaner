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

function displayProfile( a_XMLData )
{
	var Message = $(a_XMLData).children("messagehub");
	var HTMLString = "<div id=\"profile\">";
	
	HTMLString += "<h1>" + L("Your characters") + "</h1>";
	
	HTMLString += "<div id=\"charlist\">";
	HTMLString += "<span class=\"charslot_add\"></span>";
	HTMLString += "</div>";
	HTMLString += "<button class=\"button_profile\" type=\"button\">" + L("Apply changes") + "</button>";    
	HTMLString += "</div>";
	
	$("#body").empty().append(HTMLString);
	
	setupCharacters( Message );
	
	$(".button_profile").button({ icons: { secondary: "ui-icon-disk" }})
		.click( function() { triggerProfileUpdate( false ); } )
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
	HTMLString += "<button class=\"button_profile\" type=\"button\">" + L("Apply changes") + "</button>";    
	HTMLString += "</div>";
	
	$("#body").empty().append(HTMLString);
	
	setupCharacters( Message );
	
	$("#charlist").data( "userid", Message.children("userid").text() );
	
	$(".button_profile").button({ icons: { secondary: "ui-icon-disk" }})
		.click( function() { triggerProfileUpdate( true ); } )
		.css( "font-size", 11 );
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