// =============================================================================
//  Helper functions
// =============================================================================

function getSlotObj( a_Name )
{
	return $("#slot_" + a_Name ).data("obj_slothelper");
}

// -----------------------------------------------------------------------------

function getBenchObj( a_Name )
{
	return $("#bench_" + a_Name ).data("obj_slothelper");
}

// -----------------------------------------------------------------------------

function addToSlot( a_Node )
{
	var sourceElementId = a_Node.attr("id");	
	var sourceName  = sourceElementId.substr( 0, sourceElementId.indexOf("_"));
	var sourceIndex = parseInt( sourceElementId.substr( sourceElementId.indexOf("_")+1 ) );
	
	var bench     = getBenchObj( sourceName );
	var playerObj = bench.players[ sourceIndex ];
	
	if ( playerObj.id != 0 )
	{
		bench.removePlayer( sourceIndex );		
	}
	
	getSlotObj( sourceName ).addPlayerObj( playerObj );
	
	adjustSlots();
}

// -----------------------------------------------------------------------------

function addToBench( a_Node )
{
	var sourceElementId = a_Node.attr("id");	
	var sourceName  = sourceElementId.substr( 0, sourceElementId.indexOf("_"));
	var sourceIndex = parseInt( sourceElementId.substr( sourceElementId.indexOf("_")+1 ) );
	
	var playerObj = getSlotObj( sourceName ).removePlayer( sourceIndex );
	
	if ( playerObj.id != 0 )
	{
		getBenchObj( sourceName ).addPlayerObj( playerObj );
	}
	
	adjustSlots();
}

// =============================================================================
//  Drag and Drop
// =============================================================================

function makeDroppableFromBench( a_Node )
{
	a_Node.children(".slotrow")
		.children(".slot")
		.droppable({
			disabled	: false,
			hoverClass	: "target",
			drop		: onDropFromBench,
			addClasses	: false
		});
}

// -----------------------------------------------------------------------------

function makeDroppableFromSlot( a_Node )
{
	a_Node.children(".slotrow")
		.children(".slot")
		.droppable({
			disabled	: false,
			hoverClass	: "target",
			drop		: onDropFromSlot,
			addClasses	: false
		});
}

// -----------------------------------------------------------------------------

function clearDroppable( a_Node )
{
	a_Node.children(".slotrow")
		.children(".slot")
		.droppable({ disabled: true })
		.removeClass("ui-state-disabled");
}

// -----------------------------------------------------------------------------

function onDragStop()
{
	clearDroppable( $("#slot_tank").removeClass("notarget") );
	clearDroppable( $("#slot_heal").removeClass("notarget") );
	clearDroppable( $("#slot_dmg").removeClass("notarget") );
	
	$("#benchcontainer")
		.droppable({ disabled: true })
		.removeClass("ui-state-disabled");
}

// -----------------------------------------------------------------------------

function onDropFromBench( a_Event, a_Context )
{
	var sourceElementId = a_Context.draggable.attr("id");
	var slotId  = $(this).attr("id");
	
	var sourceName  = sourceElementId.substr( 0, sourceElementId.indexOf("_"));
	var sourceIndex = parseInt( sourceElementId.substr( sourceElementId.indexOf("_")+1 ) );	
	var slotName    = slotId.substr( 0, slotId.indexOf("_"));
	
	var bench     = getBenchObj( sourceName );	
	var playerObj = bench.players[sourceIndex];
	
	if ( playerObj.id != 0 )
	{
		bench.removePlayer( sourceIndex );
	}
		
	getSlotObj( slotName ).addPlayerObj( playerObj );
	
	onDragStop();	
	adjustSlots();
}

// -----------------------------------------------------------------------------

function onDropFromSlot( a_Event, a_Context )
{
	var sourceElementId = a_Context.draggable.attr("id");
	var slotId  = $(this).attr("id");
	
	var sourceName  = sourceElementId.substr( 0, sourceElementId.indexOf("_"));
	var sourceIndex = parseInt( sourceElementId.substr( sourceElementId.indexOf("_")+1 ) );	
	var slotName    = slotId.substr( 0, slotId.indexOf("_"));
	
	var playerObj   = getSlotObj( sourceName ).removePlayer( sourceIndex );		
	getSlotObj( slotName ).addPlayerObj( playerObj );
	
	onDragStop();	
	adjustSlots();
}

// -----------------------------------------------------------------------------

function onDropToBench( a_Event, a_Context )
{
	var sourceElementId = a_Context.draggable.attr("id");
	var slotId  = $(this).attr("id");
	
	var sourceName  = sourceElementId.substr( 0, sourceElementId.indexOf("_"));
	var sourceIndex = parseInt( sourceElementId.substr( sourceElementId.indexOf("_")+1 ) );
	
	var playerObj = getSlotObj( sourceName ).removePlayer( sourceIndex );		
	
	if ( playerObj.id != 0 )
	{
		getBenchObj( sourceName ).addPlayerObj( playerObj );
	}
	
	onDragStop();	
	adjustSlots();
}

// -----------------------------------------------------------------------------

function onDragStartFromBench()
{
	var elementId = $(this).attr("id");
	var benchName = elementId.substr( 0, elementId.indexOf("_"));
	var benchIndex = parseInt( elementId.substr( elementId.indexOf("_")+1 ) );
	
	var playerInfo = getBenchObj( benchName ).players[ benchIndex ];
	
	$("#slot_tank").addClass("notarget");
	$("#slot_heal").addClass("notarget");
	$("#slot_dmg").addClass("notarget");
	
	makeDroppableFromBench( $("#slot_" + playerInfo.role1 ) );
	makeDroppableFromBench( $("#slot_" + playerInfo.role2 ) );
	
	$("#slot_" + playerInfo.role1 ).removeClass("notarget");
	$("#slot_" + playerInfo.role2 ).removeClass("notarget");	
}

// -----------------------------------------------------------------------------

function onDragStartFromSlot()
{
	var elementId = $(this).attr("id");
	var slotName = elementId.substr( 0, elementId.indexOf("_"));
	var slotIndex = parseInt( elementId.substr( elementId.indexOf("_")+1 ) );
	
	var playerInfo = getSlotObj( slotName ).players[ slotIndex ];
	
	$("#slot_tank").addClass("notarget");
	$("#slot_heal").addClass("notarget");
	$("#slot_dmg").addClass("notarget");
	
	if ( slotName != playerInfo.role1 )
	{
		makeDroppableFromSlot( $("#slot_" + playerInfo.role1 ) );
		$("#slot_" + playerInfo.role1 ).removeClass("notarget");
	}
	
	if ( slotName != playerInfo.role2 )
	{
		makeDroppableFromSlot( $("#slot_" + playerInfo.role2 ) );
		$("#slot_" + playerInfo.role2 ).removeClass("notarget");
	}
	
	$("#benchcontainer").droppable({
		disabled	: false,
		drop		: onDropToBench,
		addClasses	: false
	});
}

// =============================================================================
//  CSlot
// =============================================================================

function CSlot( a_Name, a_Slots )
{
	this.name    = a_Name;
	this.slots   = a_Slots;
	this.players = Array();
	this.unavail = Array();
	
	// -------------------------------------------------------------------------

	this.displayAsSlot = function() 
	{
		var slotsPerRow = 6;
		var HTMLString = "";
	
		var slotField = $("#slot_" + this.name );
	
		for ( i=0; i<this.slots; ++i )
		{
			if ( (i % slotsPerRow) == 0 )
			{
				if (i > 0) HTMLString += "</div>";
				HTMLString += "<div class=\"slotrow\">";
			}
			
			if ( i < this.players.length )
			{
				HTMLString += "<div class=\"slot occupied\" id=\"" + this.name + "_" + i + "\">";
				HTMLString += "<div class=\"class\" style=\"background-image: url('images/classessmall/" + this.players[i].charClass + ".png')\">";
			
				if ( this.players[i].comment != "" )
					HTMLString += "<div class=\"badge\"></div>";
					
				HTMLString += "</div>";
				
				var charName = this.players[i].name;
				
				if ( !this.players[i].mainChar )
				{
					charName = "<img class=\"twinkicon\" src=\"lib/layout/images/twinkbadge.png\"/>"+ charName; 
				}
				
				if ( g_User.isRaidlead && (this.players[i].id == 0) )
				{
					HTMLString += "<input class=\"name_field\" type=\"text\" id=\"" + this.name + "_" + i + "_name\" value=\""+ charName +"\"/>";
				}
				else
				{
					HTMLString += "<div class=\"name\">" + charName + "</div>";
				}
			
				HTMLString += "<div class=\"comment\">" + this.players[i].comment + "</div>";
				HTMLString += "</div>";
			}
			else
			{		
				HTMLString += "<div class=\"slot\" id=\"" + this.name + "_" + i + "\"><div class=\"class\"></div><div class=\"name\"></div></div>";
			}
		}
	
		HTMLString += "</div>";
	
		slotField.empty().append( HTMLString );
	
		if ( g_User.isRaidlead )
		{
			slotField.children(".slotrow").children(".occupied").draggable({ 
				revert			: "invalid",
				revertDuration	: 200,
				opacity			: 0.5, 
				helper			: "clone",
				start			: onDragStartFromSlot,
				stop			: onDragStop 
			});
			
			slotField.children(".slotrow").children(".occupied").children("div")
				.dblclick( function() { addToBench( $(this).parent() ); });
		}
		
		slotField.children(".slotrow").children(".occupied").children("div")
			.mouseover( function() { showCommentTooltip( $(this).parent(), false ); } )
    		.click( function( event ) { showCommentTooltip( $(this).parent(), true ); event.stopPropagation(); } )
    		.mouseout( delayedFadeTooltip );
	}
	
	// -------------------------------------------------------------------------

	this.displayAsBench = function() 
	{
		var benchField = $("#bench_" + this.name );
		var HTMLString = "";
	
		for ( i=0; i<this.players.length; ++i )
		{
			HTMLString += "<span class=\"slot\" id=\"" + this.name + "_" + i + "\">";
			
			if ( this.players[i].comment != "" )
				HTMLString += "<div class=\"badge\"></div>";
			else
				HTMLString += "<div class=\"nobadge\"></div>";
		
			var charName = this.players[i].name;
			
			if ( !this.players[i].mainChar )
				charName = "<img class=\"twinkicon\" src=\"lib/layout/images/twinkbadge.png\"/>" + charName; 
			
			HTMLString += "<img class=\"class\" src=\"images/classessmall/" + this.players[i].charClass + ".png\"/>";
			HTMLString += "<span class=\"name\">" + charName + "</span>";
			HTMLString += "<div class=\"comment\">" + this.players[i].comment + "</div>";
			HTMLString += "</span>";
		}	
	
		benchField.empty().append( HTMLString );
	
		if ( g_User.isRaidlead )
		{
			benchField.children(".slot").draggable({ 
				revert  		: "invalid",
				revertDuration	: 200,
				opacity 		: 0.5, 
				helper			: "clone",
				start			: onDragStartFromBench,
				stop			: onDragStop 
			});
			
			benchField.children(".slot").dblclick( function() { addToSlot( $(this) ); });
		}
		
		HTMLString = "";

		for ( i=0; i<this.unavail.length; ++i )
		{
			HTMLString += "<span class=\"slot\">";
			
			if ( this.unavail[i].comment != "" )
				HTMLString += "<div class=\"badge\"></div>";
			else
				HTMLString += "<div class=\"nobadge\"></div>";
				
			var charName = this.unavail[i].name;
			if ( !this.unavail[i].mainChar )
				charName = "<img class=\"twinkicon\" src=\"lib/layout/images/twinkbadge.png\"/>" + charName;
			
			HTMLString += "<img class=\"class unavailable\" src=\"images/classessmall/" + this.unavail[i].charClass + ".png\"/>";
			HTMLString += "<span class=\"name unavailable\">" + charName + "</span>";
			HTMLString += "<div class=\"comment\">" + this.unavail[i].comment + "</div>";
			HTMLString += "</span>";
		}
		
		benchField.append( HTMLString );
					
		benchField.children(".slot")
			.mouseover( function() { showCommentTooltip( $(this), false ); } )
			.click( function( event ) { showCommentTooltip( $(this), true ); event.stopPropagation(); } )
			.mouseout( delayedFadeTooltip );
    			
		this.updateBenchCount();
	}
	
	// -------------------------------------------------------------------------
	
	this.displayAsList = function( a_ListName )
	{
		var benchField = $("#" + this.name );		
		var HTMLString = "";
		
		if ( this.players.length > 0 )
		{
			var HTMLString = a_ListName + ": ";
			
			for ( i=0; i<this.players.length; ++i )
			{
				HTMLString += "<span class=\"slot\">";
				HTMLString += "<div class=\"name\">" + this.players[i].name;
				
				if ( i+1 == this.players.length )
					HTMLString += "</div>"
				else
					HTMLString += ",</div>";
					
				HTMLString += "</span>";
			}
		}
		
		benchField.empty().append( HTMLString );
	}
	
	// -------------------------------------------------------------------------
	
	this.updateBenchCount = function()
	{
		if ( g_User.isRaidlead )
		{
			if ( parseInt( this.players.length ) > 1 )
			{
				$("#count_" + this.name ).empty().append( this.players.length-1 );
			}	
			else
			{
				$("#count_" + this.name ).empty();
			}
		}
		else
		{
			if ( parseInt( this.players.length ) > 0 )
			{
				$("#count_" + this.name ).empty().append( this.players.length );
			}	
			else
			{
				$("#count_" + this.name ).empty();
			}
		}
				
		if ( parseInt( this.unavail.length ) > 0 )
		{
			$("#count_" + this.name ).append( "<div class=\"unavail\">" + this.unavail.length + "</div>" );
		}
	}
	
	// -------------------------------------------------------------------------
	
	this.refresh = function()
	{
		getSlotObj( this.name ).displayAsSlot();
		getBenchObj( this.name ).displayAsBench();
		hideTooltip();
	}
		
	// -------------------------------------------------------------------------
	
	this.resize = function( a_NumSlots )
	{
		this.slots = a_NumSlots;
		
		var benchObj = getBenchObj( this.name );
		
		while ( this.players.length > this.slots )
		{
			var player = this.players[ this.players.length-1 ];
			
			if ( player.id != 0)
			{
				benchObj.addPlayerObj( player );
			}
			
			this.players.pop();
		}
	}
	
	// -------------------------------------------------------------------------
	
	this.addPlayerObj = function ( a_Player )
	{
		this.players.push( a_Player );
	}
		
	// -------------------------------------------------------------------------
	
	this.addPlayer = function( a_Id, a_Name, a_Class, a_Mainchar, a_Role, a_Role1, a_Role2, a_Status, a_Comment )
	{
		if ( a_Status == "unavailable" )
		{
			this.unavail.push({
				id		  : a_Id,
				name      : a_Name,
				charClass : a_Class,
				mainChar  : a_Mainchar,
				role      : a_Role,
				role1     : a_Role1,
				role2     : a_Role2,
				status    : a_Status,
				comment   : a_Comment
			});
		}
		else
		{
			this.players.push({
				id		  : a_Id,
				name      : a_Name,
				charClass : a_Class,
				mainChar  : a_Mainchar,
				role      : a_Role,
				role1     : a_Role1,
				role2     : a_Role2,
				status    : a_Status,
				comment   : a_Comment
			});
		}
	}
	
	// -------------------------------------------------------------------------
	
	this.removePlayer = function( a_Index )
	{
		var player = this.players[ a_Index ];
		this.players.splice( a_Index, 1 );
		
		return player;
	}
	
	// -------------------------------------------------------------------------
	
	this.getPlayerIds = function( a_Array )
	{
		for ( i=0; i<this.players.length; ++i )
		{
			if ( this.players[i].id == 0 )
			{
				a_Array.push( "0." + $("#" + this.name + "_" + i + "_name").val() );
			}
			else
			{
				a_Array.push( this.players[i].id );
			}
		}
	}
}

// =============================================================================
//	Refresh slots
// =============================================================================

function adjustSlots()
{
	var tankSlotObj = getSlotObj("tank");
	var healSlotObj = getSlotObj("heal");
	var dmgSlotObj  = getSlotObj("dmg");
	
	var raidSize  = tankSlotObj.slots + healSlotObj.slots + dmgSlotObj.slots;
	var numTanks  = tankSlotObj.slots;
	var numHealer = healSlotObj.slots;
	
	if ( $("#tankcount").children("option").size() != 0 )
	{
		numTanks  = parseInt( $("#tankcount").val() );
		numHealer = parseInt( $("#healcount").val() );
	}
	
	var numDamage = raidSize - ( numTanks + numHealer );
	
	tankSlotObj.resize( numTanks );
	healSlotObj.resize( numHealer );
	dmgSlotObj.resize( numDamage );
	
	// adjust slot display
	
	tankSlotObj.refresh();
	healSlotObj.refresh();
	dmgSlotObj.refresh();
	
	// adjust select fields
	
	if ( g_User.isRaidlead )
	{
		$("#tankcount").empty();
		$("#healcount").empty();
		
		var maxTankSlots = Math.min( numTanks + numDamage, 7 );
		var maxHealSlots = Math.min( numHealer + numDamage, 13 );
		
		for ( i=1; i < maxTankSlots; ++i )
			$("#tankcount").append( "<option value=\"" + i + "\"" + ((i==numTanks) ? " selected" : "") + ">" + i +"</option>" );
		
		for ( i=1; i < maxHealSlots; ++i )
			$("#healcount").append( "<option value=\"" + i + "\"" + ((i==numHealer) ? " selected" : "") + ">" + i +"</option>" );
	}
	
	$("#tankcount").combobox( "destroy" );
	$("#tankcount").combobox();
	
	$("#healcount").combobox( "destroy" );
	$("#healcount").combobox();
}

// =============================================================================
//  Display raid
// =============================================================================

function displayRaid( a_XMLData )
{
	hideTooltip();
	closeSheet();
	
	$("#body").empty();
	
	var Message = $(a_XMLData).children("messagehub");
	var raidData = Message.children("raid");
	var raidSize = raidData.children("size").text();
	
	var HTMLString = "";
	
	$("#body").append("<div id=\"raiddetail\"></div>")
	
	if ( g_User.isRaidlead )
	{
		// setup raid edit / info field
	
		$("#raiddetail").append( $("#newRaid").children().clone() );
		$("#newRaidSubmit").remove();
		$("#raiddatepicker").remove();
		$("#descriptiondummy").remove();
		$("#description").show();
		
		// setup callbacks
				
		$("#newlocationdummy").focus( function() { 
			$("#newlocationdummy").hide();
			$("#newlocation").show().focus(); 
		});
		
		$("#newlocation").blur( function() { 
			if ( $("#newlocation").val() == "" ) {
				$("#newlocation").hide();
				$("#newlocationdummy").show(); 
			}
		});
		
		var buttonFieldHTMLString = "";
		
		var openSelected = ( raidData.children("stage").text() == "open" ) ? " selected" : "";
		var lockedSelected = ( raidData.children("stage").text() == "locked" ) ? " selected" : "";
		var canceledSelected = ( raidData.children("stage").text() == "canceled" ) ? " selected" : "";
		
		buttonFieldHTMLString += "<select class=\"stageselect\" id=\"stage\">";
		buttonFieldHTMLString += "<option value=\"open\"" + openSelected + ">" + L("Raid open") + "</option>";
		buttonFieldHTMLString += "<option value=\"locked\"" + lockedSelected + ">" + L("Raid locked") + "</option>";
		buttonFieldHTMLString += "<option value=\"canceled\"" + canceledSelected + ">" + L("Raid canceled") + "</option>";
		buttonFieldHTMLString += "<option value=\"delete\">" + L("Delete raid") + "</option>";
		buttonFieldHTMLString += "</select>";
		buttonFieldHTMLString += "<div class=\"buttonfield\"><button id=\"submitchanges\">" + L("Apply changes") + "</button></div>";
		buttonFieldHTMLString += "<input type=\"hidden\" id=\"raidId\" value=\"" + raidData.children("raidId").text() + "\">" ;
		buttonFieldHTMLString += "<input type=\"hidden\" id=\"startDate\" value=\"" + raidData.children("startDate").text() + "\">";		
		
		$("#raiddetail").append( buttonFieldHTMLString );
	
		$("#submitchanges").button({ icons: { secondary: "ui-icon-disk" }}).css( "font-size", 11 );
			
		// setup location and imagepicker
		
		var Locations = Message.children("locations").children("location");
		var LocationImages = Message.children("locations").children("locationimage");
		var imageList = new Array();
		
		Locations.each( function(index) {
			imageList[index] = $(this).children("image").text();
			$("#selectlocation").append("<option value=\"" + $(this).children("id").text() + "\">" + $(this).children("name").text() + "</option>");
		});
		
		$("#locationimagepicker").data("imageNames", imageList );
			
		LocationImages.each( function(index) {
			if ( ( $("#locationimagelist").children().size() + 1 ) % 11 == 0 )
				$("#locationimagelist").append( "<br/>" );
				
			$("#locationimagelist").append( "<img src=\"images/raidsmall/" + $(this).text() + "\" onclick=\"applyLocationImage(this)\" style=\"width: 32px; height: 32px; margin-right: 5px;\"/>" );
		});
		
		// set default values
		
		var locationId = raidData.children("locationId").text();
		var locationImage = raidData.children("image").text();
		var startHour   = parseInt( raidData.children("start").text().substr(0,2) );
		var startMinute = parseInt( raidData.children("start").text().substr(3,2) );
		var endHour     = parseInt( raidData.children("end").text().substr(0,2) );
		var endMinute   = parseInt( raidData.children("end").text().substr(3,2) );
		
		$("#locationimagepicker").data("selectedImage", locationImage );
		
		$("#selectlocation").children("option").removeAttr("selected");
		$("#selectlocation").children("option").each( function() {
			if ($(this).val() == locationId)
				$(this).attr("selected", "selected");
		});
		
		$("#selectsize").children("option").removeAttr("selected");
		$("#selectsize").children("option").each( function() {
			if ($(this).val() == raidSize)
				$(this).attr("selected", "selected");
		});
		
		$("#starthour").children("option").removeAttr("selected");
		$("#starthour").children("option").each( function() {
			if ($(this).val() == startHour)
				$(this).attr("selected", "selected");
		});
		
		$("#startminute").children("option").removeAttr("selected");
		$("#startminute").children("option").each( function() {
			if ($(this).val() == startMinute)
				$(this).attr("selected", "selected");
		});
		
		$("#endhour").children("option").removeAttr("selected");
		$("#endhour").children("option").each( function() {
			if ($(this).val() == endHour)
				$(this).attr("selected", "selected");
		});
		
		$("#endminute").children("option").removeAttr("selected");
		$("#endminute").children("option").each( function() {
			if ($(this).val() == endMinute)
				$(this).attr("selected", "selected");
		});
		
		$("#newlocationdummy").hide();
		
		$("#locationimagepicker").css("background-image", "url(images/raidbig/" + locationImage + ")");
		
		$("#description").text( raidData.children("description").text() )
			.addClass( "descriptionbox" );
		
		$("#submitchanges").click( triggerRaidUpdate );
		
		$("#selectlocation").combobox();
		$("#selectsize").combobox();
		$("#starthour").combobox();
		$("#startminute").combobox();
		$("#endhour").combobox();
		$("#endminute").combobox();
		$("#stage").combobox();
	}
	else
	{
		// add info for non-raidlead users
	
		var location = raidData.children("location").text();
		var locationImage = raidData.children("image").text();
				
		HTMLString += "<div class=\"icon\" style=\"background: url('images/raidbig/" + locationImage + "')\"></div>";
        HTMLString += "<div style=\"float: left; margin-bottom: 30px\">";
        HTMLString += "<div class=\"location\">" + location + " (" + raidSize + ")</div>";
        HTMLString += "<div class=\"time\">" + raidData.children("start").text() + " - " + raidData.children("end").text() + "</div>";
        HTMLString += "<div class=\"description\">" + raidData.children("description").text() + "</div>";
        HTMLString +="</div>"
	}
	
	HTMLString += "<div class=\"separatortop\"></div>";
	
	// add setup slots
	
	HTMLString += "<span class=\"slotfield slotfield6\" id=\"tanks\">";
	HTMLString += "<span class=\"type\">" + L("Tanks") + "</span>";
	
	if ( g_User.isRaidlead )
	{	
		HTMLString += "<select style=\"width: 42px\" class=\"slotcount\" id=\"tankcount\"></select>";
	}
	
	HTMLString += "<div id=\"slot_tank\" class=\"slotcontainer\" style=\"clear: both\"></div>";
	HTMLString += "</span>";
	
	HTMLString += "<span class=\"slotfield slotfield12\" id=\"healer\">";
	HTMLString += "<span class=\"type\">" + L("Healers") + "</span>";
	
	if ( g_User.isRaidlead )
	{
		HTMLString += "<select style=\"width: 42px\" class=\"slotcount\" id=\"healcount\"></select>";
	}
	
	HTMLString += "<div id=\"slot_heal\" class=\"slotcontainer\" style=\"clear: both\"></div>";
	HTMLString += "</span>";
	
	HTMLString += "<span class=\"slotfield slotfield24\" id=\"damage\">";
	HTMLString += "<div class=\"type\">" + L("Damage") + "</div>";
	HTMLString += "<div id=\"slot_dmg\" class=\"slotcontainer\" style=\"clear: both\"></div>";
	HTMLString += "</span>";
	
	// add bench
	
	HTMLString += "<div class=\"separatorbottom\"></div>";
	
	HTMLString += "<div id=\"benchBlock\" class=\"blockTank\">";
	HTMLString += "<div id=\"tankbenchtoggle\" class=\"tankbench tankbenchactive\"><div class=\"onBench shadow_inlay_hard\" id=\"count_tank\"></div></div>";
	HTMLString += "<div id=\"healbenchtoggle\" class=\"healbench\"><div class=\"onBench\" id=\"count_heal\"></div></div>";
	HTMLString += "<div id=\"dmgbenchtoggle\" class=\"dmgbench\"><div class=\"onBench\" id=\"count_dmg\"></div></div>";
	HTMLString += "</div>";
	
	HTMLString += "<div id=\"benchcontainer\">";
	HTMLString += "<div id=\"bench_tank\" class=\"bench\"></div>";
	HTMLString += "<div id=\"bench_heal\" class=\"bench\"></div>";
	HTMLString += "<div id=\"bench_dmg\" class=\"bench\"></div>";
	HTMLString += "</div>";	
	
	$("#raiddetail").append(HTMLString);

	var tankSlots = parseInt( raidData.children("tankSlots").text(), 10 );
	var dmgSlots  = parseInt( raidData.children("dmgSlots").text(), 10 );
	var healSlots = parseInt( raidData.children("healSlots").text(), 10 );

	// Setup helper objects
	
	$("#slot_tank").data("obj_slothelper",  new CSlot("tank", tankSlots) );
	$("#slot_heal").data("obj_slothelper",  new CSlot("heal", healSlots) );
	$("#slot_dmg").data("obj_slothelper",   new CSlot("dmg", dmgSlots) );
		
	$("#bench_tank").data("obj_slothelper", new CSlot("tank", 0) );
	$("#bench_heal").data("obj_slothelper", new CSlot("heal", 0) );
	$("#bench_dmg").data("obj_slothelper",  new CSlot("dmg", 0) );
	
	// setup players
	
	var attendees = raidData.children("attendee");
	
	attendees.each( function() {
		var player = $(this);
		
		if ( player.children("status").text() == "ok" )
		{
			helper = getSlotObj( player.children("role").text() );
		}
		else
		{
			helper = getBenchObj( player.children("role").text() );
		}
		
		if ( (parseInt( player.children("id").text() ) == 0) && 
		     (player.children("name").text().length == 0) )
		{
			var reservedName    = (player.children("comment").text().length > 0) ? player.children("comment").text() : L("Reserved");
			var reservedComment = "";
			var separatorIndex  = reservedName.indexOf(" ");
			
			if ( separatorIndex > 0 )
			{			
				reservedComment = reservedName.substring(separatorIndex+1, reservedName.length);
				
				if ( !g_User.isRaidlead )
					reservedName = reservedName.substring(0, separatorIndex);
			}
			
			helper.addPlayer(
				0,
				reservedName,
				"random",
				"true",
				player.children("role").text(),
				player.children("role").text(),
				player.children("role").text(),
				"ok",
				reservedComment
			);
		}
		else
		{		
			helper.addPlayer(
				player.children("id").text(),
				player.children("name").text(),
				player.children("class").text(),
				player.children("mainchar").text() == "true",
				player.children("role").text(),
				player.children("role1").text(),
				player.children("role2").text(),
				player.children("status").text(),
				player.children("comment").text()
			);
		}
	});
	
	// setup reserved slots
	
	if ( g_User.isRaidlead )
	{
		getBenchObj("tank").addPlayer(
			0,
			L("Reserved"),
			"random",
			"true",
			"tank",
			"tank",
			"tank",
			"ok",
			""
		);
		
		getBenchObj("heal").addPlayer(
			0,
			L("Reserved"),
			"random",
			"true",
			"heal",
			"heal",
			"heal",
			"ok",
			""
		);
		
		getBenchObj("dmg").addPlayer(
			0,
			L("Reserved"),
			"random",
			"true",
			"dmg",
			"dmg",
			"dmg",
			"ok",
			""
		);
	}
	else
	{
		$("#benchBlock").css( "margin-top", 10 );
		$("#benchcontainer").css( "margin-top", 10 );
	}
	
	// setup buttons
	
	$("#bench_heal").hide();	
	$("#bench_dmg").hide();
	
	$("#tankbenchtoggle").click( function() {
		$("#bench_heal").hide();	
		$("#bench_dmg").hide();
		$("#bench_tank").show();
		
		$("#benchBlock").removeClass("blockTank");
		$("#benchBlock").removeClass("blockHeal");
		$("#benchBlock").removeClass("blockDmg");
		$("#benchBlock").addClass("blockTank");
		
		$("#healbenchtoggle").removeClass( "healbenchactive" );
		$("#dmgbenchtoggle").removeClass( "dmgbenchactive" );
		$("#tankbenchtoggle").addClass( "tankbenchactive" );
	});
	
	$("#dmgbenchtoggle").click( function() {
		$("#bench_heal").hide();	
		$("#bench_dmg").show();
		$("#bench_tank").hide();
		
		$("#benchBlock").removeClass("blockTank");
		$("#benchBlock").removeClass("blockHeal");
		$("#benchBlock").removeClass("blockDmg");
		$("#benchBlock").addClass("blockDmg");
		
		$("#healbenchtoggle").removeClass( "healbenchactive" );
		$("#dmgbenchtoggle").addClass( "dmgbenchactive" );
		$("#tankbenchtoggle").removeClass( "tankbenchactive" );
	});
	
	$("#healbenchtoggle").click( function() {
		$("#bench_heal").show();	
		$("#bench_dmg").hide();
		$("#bench_tank").hide();
		
		$("#benchBlock").removeClass("blockTank");
		$("#benchBlock").removeClass("blockHeal");
		$("#benchBlock").removeClass("blockDmg");
		$("#benchBlock").addClass("blockHeal");
		
		$("#healbenchtoggle").addClass( "healbenchactive" );
		$("#dmgbenchtoggle").removeClass( "dmgbenchactive" );
		$("#tankbenchtoggle").removeClass( "tankbenchactive" );
	});
	
	adjustSlots();
	
	if ( g_User.isRaidlead )
	{			
		$("#tankcount").change( adjustSlots );
		$("#healcount").change( adjustSlots );
	}
}

// =============================================================================
//	Update raid
// =============================================================================

function triggerRaidUpdate( selectObj )
{
	if ( g_User == null ) 
		return;
	
	if ( g_User.isRaidlead )
	{
		if ( $("#stage").val() == "delete" )
		{
			triggerRaidDelete( $("#raidId").val() );
		}
		else
		{
			var tanksIn = Array();
			var healersIn = Array();
			var damageIn = Array();
			var playersOut = Array();
			
			getSlotObj("tank").getPlayerIds( tanksIn );
			getSlotObj("heal").getPlayerIds( healersIn );
			getSlotObj("dmg").getPlayerIds( damageIn );
			
			getBenchObj("tank").getPlayerIds( playersOut );
			getBenchObj("heal").getPlayerIds( playersOut );
			getBenchObj("dmg").getPlayerIds( playersOut );
			
			var raidImage = $("#locationimagepicker").data( "selectedImage" );
            
            var startDateString = $("#startDate").val();
            var startYear  = parseInt( startDateString.substr( 0, 4 ), 10 );
            var startMonth = parseInt( startDateString.substr( 5, 2 ), 10 );
            var startDay   = parseInt( startDateString.substr( 8, 2 ), 10 );
				
			var Parameters = {
				id           : $("#raidId").val(),
				raidImage	 : raidImage,
				locationId	 : $("#selectlocation").val(),
				locationSize : $("#selectsize").val(),
				locationName : $("#edit_selectlocation").val(),
				startHour	 : $("#starthour").val(),
				startMinute  : $("#startminute").val(),
				endHour		 : $("#endhour").val(),
				endMinute	 : $("#endminute").val(),
				description	 : $("#description").val(),
				month		 : startMonth,
				day			 : startDay,
				year		 : startYear,
				stage		 : $("#stage").val(),
				tankSlots	 : $("#tankcount").val(),
				healSlots    : $("#healcount").val(),
			
				tanks        : tanksIn,
				healers      : healersIn,
				damage       : damageIn,
				onBench      : playersOut
			};
		
			AsyncQuery( "raid_update", Parameters, displayRaid );
		}
	}
}

// =============================================================================
//	Delete raid
// =============================================================================

function triggerRaidDelete( a_RaidId )
{
	if ( g_User == null ) 
		return;
	
	if ( g_User.isRaidlead )
	{
		confirm( L("Do you really want to delete this Raid?"), 
				 L("Delete raid"), L("Cancel"), 
				 function() {
				 	var date = new Date( $("#startDate").val() );
		
					var Parameters = {
						id 		: a_RaidId,
						month	: date.getMonth()+1,
						year	: date.getFullYear()
					};
				
					AsyncQuery( "raid_delete", Parameters, displayCalendar );
				 }
		);		
	}
}

// =============================================================================
//	Single raid display
// =============================================================================

function loadRaid( a_RaidId )
{
	reloadUser();
	
	if ( g_User == null ) 
		return;
		
	$("#body").empty();
		
    var Parameters = {
        id : a_RaidId
    };
    
	AsyncQuery( "raid_detail", Parameters, displayRaid );

}