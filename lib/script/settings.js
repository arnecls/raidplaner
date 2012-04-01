function CGroup( a_Name )
{
	this.users = Array();
	this.name  = a_Name;
	
	$("#" + a_Name).data( "helper", this );
	
	this.addUser = function( a_Id, a_Login, a_Binding ) 
	{
		this.users.push( {
			id      : a_Id,
			login   : a_Login,
			binding : a_Binding
		});
	}
	
	// -------------------------------------------------------------------------
	
	this.addUserObject = function( a_Object ) 
	{
		this.users.push( a_Object );
	}
	
	// -------------------------------------------------------------------------
	
	this.removeUser = function( a_Index )
	{
		var user = this.users[ a_Index ];
		this.users.splice( a_Index, 1 );
		
		return user;
	}
	
	// -------------------------------------------------------------------------
	
	this.getUserIdArray = function()
	{
		var idArray = new Array();
		for (i=0; i<this.users.length; ++i)
		{
			idArray.push( this.users[i].id );
		}
		
		return idArray;
	}

	// -------------------------------------------------------------------------
	
	this.refreshList = function()
	{
		var HTMLString = "";
			
		for (i=0; i<this.users.length; ++i)
		{
			var bindingString = this.users[i].binding;
			
			HTMLString += "<div class=\"user\" id=\"" + this.users[i].id + "\">";
			
			if ( this.users[i].id == 1)
				HTMLString += "<span class=\"userFunction\" style=\"cursor: default\"></span>";
			else
				HTMLString += "<span class=\"userFunction functionDelete\"></span>";
			
			HTMLString += "<span class=\"userFunction functionEdit\"></span>";
			
			if ( this.users[i].id == 1)
				HTMLString += "<div index=\"" + i + "\">";
			else
				HTMLString += "<div class=\"userDrag\" index=\"" + i + "\">";
			
			HTMLString += "<img src=\"lib/layout/images/icon_user.png\" class=\"userImage\"/>";
			
			if ( bindingString == "none" )
			{
				HTMLString += "<div class=\"userNameLocal\">";
				HTMLString += "<span style=\"font-weight: bold\">" + this.users[i].login + "</span>";
				HTMLString += "</div>";
			}
			else
			{
				HTMLString += "<div class=\"userName\">";
				HTMLString += "<span style=\"font-weight: bold\">" + this.users[i].login + "</span><br/>";
				HTMLString += "<span style=\"font-size: 0.75em; color: #148cdc\">" + bindingString + "</span>";
				HTMLString += "</div>";
			}
			
			HTMLString += "</div>";
			HTMLString += "</div>";
		}
		
		$("#" + a_Name).empty().append( HTMLString );
		$("#" + a_Name).children(".user").children(".userDrag").data( "handle", this.name );
	
		// Setup UI
			
		$(".functionDelete").click( function() {
			var index    = $(this).siblings(".userDrag").attr("index");
			var hostName = $(this).siblings(".userDrag").data("handle");
					
			confirm( L("Do you really want to delete this user?"), L("Delete user"), L("Cancel"), function() {
				var host = $( "#" + hostName ).data("helper");
				var user = host.removeUser( index );
				host.refreshList();
				
				$("#groups").data("helper").addUserObject( user );
			});
		});
		
		$(".functionEdit").click( function() {
			var userId = $(this).parent().attr("id");
			
			changeContext( "settings," + userId );
		});
	
		$(".userDrag").draggable({ 
			revert			: "invalid",
			revertDuration	: 200,
			opacity			: 0.5, 
			helper			: "clone"
		});
		
		$(".groupSlot").droppable({
			disabled	: false,
			hoverClass	: "groupTarget",
			drop		: onUserDrop,
			addClasses	: false
		});
	}	
}

// -----------------------------------------------------------------------------

function onUserDrop( a_Event, a_Context )
{
	var index = a_Context.draggable.attr("index");
	var handle = a_Context.draggable.data("handle");
	
	var sourceHelper = $("#" + handle).data( "helper" );
	var targetHelper = $(a_Event.target).children(".center").data( "helper" );
	
	if ( sourceHelper != targetHelper )
	{
		var user = sourceHelper.removeUser( index );
		targetHelper.addUserObject( user );
	
		sourceHelper.refreshList();
		targetHelper.refreshList();
	}
}

// -----------------------------------------------------------------------------

function calculateShortTime( a_Time )
{
	var result = {
		time   : a_Time,
		metric : 0
	}
	
	if ( (result.time / 60 > 0) && (result.time % 60 == 0) )
	{
		result.time /= 60;
		++result.metric;
		
		if ( (result.time / 60 > 0) && (result.time % 60 == 0) )
		{
			result.time /= 60;
			++result.metric;
	
			if ( (result.time / 24 > 0) && (result.time % 24 == 0) )
			{
				result.time /= 24;
				++result.metric;
				
				if ( (result.time / 7 > 0) && (result.time % 7 == 0) )
				{
					result.time /= 7;
					++result.metric;
				
					if ( (result.time / 4 > 0) && (result.time % 4 == 0) )
					{
						result.time /= 4;
						++result.metric;
					}
				}
			}
		}
	}

	return result;
}

// -----------------------------------------------------------------------------

function calculateUnixTime( a_Time, a_TimeMetric )
{
	switch( a_TimeMetric )
	{
	case 1:
		return a_Time * 60;
	case 2:
		return a_Time * 60 * 60;
	case 3:
		return a_Time * 60 * 60 * 24;
	case 4:
		return a_Time * 60 * 60 * 24 * 7;
	case 5:
		return a_Time * 60 * 60 * 24 * 7 * 4;
	}
	
	return a_Time;
}

// -----------------------------------------------------------------------------

function deleteLocation( a_Element )
{
	confirm(L("Do you really want to delete this location?")+"<br>"+L("This will also delete all raids at this location."), 
	        L("Delete location and raids"), L("Cancel"), 
	        function() {
	        	var locationId = $(a_Element).attr("id");
	        	locationId = parseInt(locationId.substring(9,locationId.length));
	        	
	        	$("#locationsettings").data("removed").push( locationId );
	        	$(a_Element).detach(); 
	        });
}

// -----------------------------------------------------------------------------

function showPanel( a_ShowBox, a_Section )
{
    $("#groups").hide();
	$("#locationsettings").hide();
	$("#raidsettings").hide();
	$("#statistics").hide();
	
	$("#tablist").removeClass("users");
	$("#tablist").removeClass("locations");
	$("#tablist").removeClass("settings");
	$("#tablist").removeClass("stats");

    $("#userstoggle").removeClass("icon_users");
	$("#locationstoggle").removeClass("icon_locations");
	$("#settingstoggle").removeClass("icon_settings");
	$("#statstoggle").removeClass("icon_stats");
	
	$("#userstoggle").addClass("icon_users_off");
	$("#locationstoggle").addClass("icon_locations_off");
	$("#settingstoggle").addClass("icon_settings_off");
	$("#statstoggle").addClass("icon_stats_off");

    $(a_ShowBox).show();
	$("#tablist").addClass(a_Section);
    $("#"+a_Section+"toggle").removeClass("icon_"+a_Section+"_off");
    $("#"+a_Section+"toggle").addClass("icon_"+a_Section);
}

// -----------------------------------------------------------------------------

function displaySettings( a_XMLData )
{
	var Message = $(a_XMLData).children("messagehub");
	var HTMLString = "<div id=\"settings\">";
	
	var purgeRaidTime   = 0;
	var purgeRaidMetric = 0;
	var lockRaidTime    = 0;
	var lockRaidMetric  = 0;
	var settings        = new Array();
	
	settings["RaidStartHour"]   = { text : "", number : 19 };
	settings["RaidStartMinute"] = { text : "", number : 30 };
	settings["RaidEndHour"]     = { text : "", number : 23 };
	settings["RaidEndMinute"]   = { text : "", number : 0 };
	settings["RaidSize"]        = { text : "", number : 10 };
	settings["Banner"]          = { text : "cata", number : 0 };
	settings["Site"]            = { text : "", number : 0 };
	
	Message.children("setting").each( function() {
		
		if ( $(this).children("name").text() == "PurgeRaids" )
		{
			var shortTime = calculateShortTime( parseInt( $(this).children("intValue").text(), 10 ) );
			purgeRaidTime = shortTime.time;
			purgeRaidMetric = shortTime.metric;
		}
		else if ( $(this).children("name").text() == "LockRaids" )
		{
			var shortTime = calculateShortTime( parseInt( $(this).children("intValue").text(), 10 ) );
			lockRaidTime = shortTime.time;
			lockRaidMetric = shortTime.metric;
		}
		
		settings[ $(this).children("name").text() ] = { 
			text   : $(this).children("textValue").text(),
			number : parseInt( $(this).children("intValue").text() )
		};
	});
	
	$("#logo").attr("class", "logo_" + settings["Banner"].text);
	
	// Tabs
	
	HTMLString += "<h1>" + L("Settings") + "</h1>";
	
	HTMLString += "<div id=\"tablist\" class=\"tabs users\">"
	HTMLString += "<div id=\"userstoggle\" class=\"tab_icon icon_users\"></div>"
	HTMLString += "<div id=\"locationstoggle\" class=\"tab_icon icon_locations_off\"></div>"
	HTMLString += "<div id=\"settingstoggle\" class=\"tab_icon icon_settings_off\"></div>"
	HTMLString += "<div id=\"statstoggle\" class=\"tab_icon icon_stats_off\"></div>"
	HTMLString += "</div>"
	
	// User settings
	
	HTMLString += "<div id=\"groups\">";
	
	// NoGroup
	
	HTMLString += "<span class=\"groupSlot\">";
	HTMLString += "<div class=\"top\">" + L("Locked") + "</div>";
	HTMLString += "<div id=\"groupBanned\" class=\"center\">";
	HTMLString += "</div>";
	HTMLString += "<div class=\"bottom\"></div>";
	HTMLString += "</span>";
	
	// Member
	
	HTMLString += "<span class=\"groupSlot\">";
	HTMLString += "<div class=\"top\">" + L("Members") + "</div>";
	HTMLString += "<div id=\"groupMember\" class=\"center\">";
	HTMLString += "</div>";
	HTMLString += "<div class=\"bottom\"></div>";
	HTMLString += "</span>";
	
	// Raidlead
	
	HTMLString += "<span class=\"groupSlot\">";
	HTMLString += "<div class=\"top\">" + L("Raidleads") + "</div>";
	HTMLString += "<div id=\"groupRaidlead\" class=\"center\">";
	HTMLString += "</div>";
	HTMLString += "<div class=\"bottom\"></div>";
	HTMLString += "</span>";
	
	// Admin
	
	HTMLString += "<span class=\"groupSlot\" style=\"margin-right: 0px\">";
	HTMLString += "<div class=\"top\">" + L("Administrators") + "</div>";
	HTMLString += "<div id=\"groupAdmin\" class=\"center\">";
	HTMLString += "</div>";
	HTMLString += "<div class=\"bottom\"></div>";
	HTMLString += "</span>";
	
	HTMLString += "</div>";
	
	// Location settings
	
	HTMLString += "<div id=\"locationsettings\">";
	HTMLString += "<div class=\"imagelist\" id=\"locationimagelist\">";
	
	var numImages = 0;
	
	Message.children("locationimage").each( function(index) {
	
		if ( (numImages + 1) % 11 == 0 )
		{
			HTMLString += "<br/>";
			++numImages;
		}
			
		HTMLString += "<img src=\"images/raidsmall/" + $(this).text() + "\" onclick=\"applyLocationImageExternal(this)\" style=\"width: 32px; height: 32px; margin-right: 5px;\"/>";
		++numImages;
	});
	
	HTMLString += "</div>";
	
	Message.children("location").each( function() {
	
		var locationId = parseInt( $(this).children("id").text() );
	
		HTMLString += "<div class=\"location\" id=\"location_" + locationId + "\">";
		HTMLString += "<span class=\"imagepicker locationimg\" style=\"background-image: url(images/raidsmall/" + $(this).children("image").text() + ")\"></span>";
		HTMLString += "<input class=\"locationname\" type=\"text\" value=\"" + $(this).children("name").text() + "\"/>";
		HTMLString += "<div class=\"deletecmd\" onclick=\"deleteLocation($(this).parent())\"></div>";
		HTMLString += "</div>"; 
	});
	
	HTMLString += "</div>";
		
	// Raidplaner settings
	
	HTMLString += "<div id=\"raidsettings\">";
	
	HTMLString += "<div class=\"propDeleteRaid\">";
	HTMLString += "<span class=\"propLabel\">" + L("Delete raids") + "</span>";
	HTMLString += "<input class=\"timeField\" type=\"text\" id=\"purgeTime\" value=\"\"/>";
	HTMLString += "<select class=\"metricField\" id=\"purgeMetric\">";
	HTMLString += "<option" + ((purgeRaidMetric == 0) ? " selected" : "" ) + ">" + L("Second(s)") + "</option>";
	HTMLString += "<option" + ((purgeRaidMetric == 1) ? " selected" : "" ) + ">" + L("Minute(s)") + "</option>";
	HTMLString += "<option" + ((purgeRaidMetric == 2) ? " selected" : "" ) + ">" + L("Hour(s)") + "</option>";
	HTMLString += "<option" + ((purgeRaidMetric == 3) ? " selected" : "" ) + ">" + L("Day(s)") + "</option>";
	HTMLString += "<option" + ((purgeRaidMetric == 4) ? " selected" : "" ) + ">" + L("Week(s)") + "</option>";
	HTMLString += "<option" + ((purgeRaidMetric == 5) ? " selected" : "" ) + ">" + L("Month") + "</option>";
	HTMLString += "</select>";
	HTMLString += "<span class=\"propLabel2\">" + L("after a raid is done") + "</span>"
	HTMLString += "</div>";
	
	HTMLString += "<div class=\"propLockRaid\">";
	HTMLString += "<span class=\"propLabel\">" + L("Lock raids") + "</span>";
	HTMLString += "<input class=\"timeField\" type=\"text\" id=\"lockTime\" value=\"\"/>";
	HTMLString += "<select class=\"metricField\" id=\"lockMetric\">";
	HTMLString += "<option" + ((lockRaidMetric == 0) ? " selected" : "" ) + ">" + L("Second(s)") + "</option>";
	HTMLString += "<option" + ((lockRaidMetric == 1) ? " selected" : "" ) + ">" + L("Minute(s)") + "</option>";
	HTMLString += "<option" + ((lockRaidMetric == 2) ? " selected" : "" ) + ">" + L("Hour(s)") + "</option>";
	HTMLString += "<option" + ((lockRaidMetric == 3) ? " selected" : "" ) + ">" + L("Day(s)") + "</option>";
	HTMLString += "<option" + ((lockRaidMetric == 4) ? " selected" : "" ) + ">" + L("Week(s)") + "</option>";
	HTMLString += "<option" + ((lockRaidMetric == 5) ? " selected" : "" ) + ">" + L("Month") + "</option>";
	HTMLString += "</select>";
	HTMLString += "<span class=\"propLabel2\">" + L("before a raid starts") + "</span>"
	HTMLString += "</div>";
	
	HTMLString += "<div class=\"propRaidStart\">";
	HTMLString += "<span class=\"propLabel\">" + L("Default raid start time") + "</span>";
	HTMLString += "<select id=\"starthour\" style=\"width: 48px\">";
	
	for ( i=4; i>=0; --i )
		HTMLString += "<option value=\"" + i + "\"" + ((settings["RaidStartHour"].number == i) ? " selected" : "" ) + ">" + i + "</option>";
		
	for ( i=23; i>4; --i )
		HTMLString += "<option value=\"" + i + "\"" + ((settings["RaidStartHour"].number == i) ? " selected" : "" ) + ">" + i + "</option>";
	
	HTMLString += "</select><span>&nbsp;:&nbsp;</span>";
	HTMLString += "<select id=\"startminute\" style=\"width: 48px\">";
	HTMLString += "<option value=\"0\"" + ((settings["RaidStartMinute"].number == 0) ? " selected" : "" ) + ">00</option>";
	HTMLString += "<option value=\"15\"" + ((settings["RaidStartMinute"].number == 15) ? " selected" : "" ) + ">15</option>";
	HTMLString += "<option value=\"30\"" + ((settings["RaidStartMinute"].number == 30) ? " selected" : "" ) + ">30</option>";
	HTMLString += "<option value=\"45\"" + ((settings["RaidStartMinute"].number == 45) ? " selected" : "" ) + ">45</option>";
	HTMLString += "</select>";
	HTMLString += "</div>";
	
	HTMLString += "<div class=\"propRaidEnd\">";
	HTMLString += "<span class=\"propLabel\">" + L("Default raid end time") + "</span>";	
	HTMLString += "<select id=\"endhour\" style=\"width: 48px\">";
	
	for ( i=4; i>=0; --i )
		HTMLString += "<option value=\"" + i + "\"" + ((settings["RaidEndHour"].number == i) ? " selected" : "" ) + ">" + i + "</option>";
		
	for ( i=23; i>4; --i )
		HTMLString += "<option value=\"" + i + "\"" + ((settings["RaidEndHour"].number == i) ? " selected" : "" ) + ">" + i + "</option>";
	
	HTMLString += "</select><span>&nbsp;:&nbsp;</span>";
	HTMLString += "<select id=\"endminute\" style=\"width: 48px\">";
	HTMLString += "<option value=\"0\"" + ((settings["RaidEndMinute"].number == 0) ? " selected" : "" ) + ">00</option>";
	HTMLString += "<option value=\"15\"" + ((settings["RaidEndMinute"].number == 15) ? " selected" : "" ) + ">15</option>";
	HTMLString += "<option value=\"30\"" + ((settings["RaidEndMinute"].number == 30) ? " selected" : "" ) + ">30</option>";
	HTMLString += "<option value=\"45\"" + ((settings["RaidEndMinute"].number == 45) ? " selected" : "" ) + ">45</option>";
	HTMLString += "</select>";
	HTMLString += "</div>";
	
	HTMLString += "<div class=\"propRaidSize\">";
	HTMLString += "<span class=\"propLabel\">" + L("Default raid size") + "</span>";
	HTMLString += "<select id=\"raidsize\" style=\"width: 48px\">";
	HTMLString += "<option value=\"10\"" + ((settings["RaidSize"].number == 10) ? " selected" : "" ) + ">10</option>";
	HTMLString += "<option value=\"25\"" + ((settings["RaidSize"].number == 25) ? " selected" : "" ) + ">25</option>";
	HTMLString += "</select>";
	HTMLString += "</div>";
	
	HTMLString += "<div class=\"propSite\">";
	HTMLString += "<span class=\"propLabel\">" + L("Banner click landing page") + "</span>";
	HTMLString += "<input class=\"urlField\" type=\"text\" id=\"site\" style=\"width: 152px\" value=\"" + settings["Site"].text + "\"/>";
	HTMLString += "</div>";
	
	HTMLString += "<div class=\"propLogo\">";
	HTMLString += "<span class=\"propLabel\">" + L("Site banner") + "</span>";
	HTMLString += "<select id=\"banner\" style=\"width: 160px\">";
	HTMLString += "<option value=\"wotlk\"" + ((settings["Banner"].text == "wotlk") ? " selected" : "" ) + ">Wrath of the Lichking</option>";
	HTMLString += "<option value=\"cata\"" + ((settings["Banner"].text == "cata") ? " selected" : "" ) + ">Cataclysm</option>";
	HTMLString += "<option value=\"mop\"" + ((settings["Banner"].text == "mop") ? " selected" : "" ) + ">Mists of Pandaria</option>";
	HTMLString += "</select>";
	HTMLString += "</div>";
	
	HTMLString += "</div>";
	
	// Statistics setup
	
	HTMLString += "<div id=\"statistics\">";
	
	HTMLString += "<div class=\"labels\">";
	
	HTMLString += "<div class=\"box ok\"><span class=\"start\"></span><span class=\"end\"></span></div>";
	HTMLString += "<div class=\"label\">" + L("Attended") + "</div>";
	
	HTMLString += "<div class=\"box available\"><span class=\"start\"></span><span class=\"end\"></span></div>";
	HTMLString += "<div class=\"label\">" + L("Queued") + "</div>";
	
	HTMLString += "<div class=\"box unavailable\"><span class=\"start\"></span><span class=\"end\"></span></div>";
	HTMLString += "<div class=\"label\">" + L("Absent") + "</div>";
	
	HTMLString += "<div class=\"box missed\"><span class=\"start\"></span><span class=\"end\"></span></div>";
	HTMLString += "<div class=\"label\">" + L("Missed") + "</div>";
	
	HTMLString += "</div>";
	
	var numRaids = parseInt( Message.children("numRaids").text() );
	var barSize  = 750;
	
	Message.children("attendance").each( function() {
		
		HTMLString += "<div class=\"name\">" + $(this).children("name").text() + "</div>";
		HTMLString += "<div class=\"bar\"><span class=\"start_bar\"></span>";
		
		var numOk      = parseInt( $(this).children("ok").text() );
		var numAvail   = parseInt( $(this).children("available").text() );
		var numUnavail = parseInt( $(this).children("unavailable").text() );
		var numMissed  = numRaids - (numOk + numAvail + numUnavail);
	
		var sizeOk      = (numOk / numRaids) * barSize;
		var sizeAvail   = (numAvail / numRaids) * barSize;
		var sizeUnavail = (numUnavail / numRaids) * barSize;
		var sizeMissed  = (numMissed / numRaids) * barSize;
		
		if (numOk > 0)      HTMLString += "<span class=\"ok\" style=\"width: " + sizeOk.toFixed() + "px\"><div class=\"count\">" + numOk + "</div></span>";
		if (numAvail > 0)   HTMLString += "<span class=\"available\" style=\"width: " + sizeAvail.toFixed() + "px\"><div class=\"count\">" + numAvail + "</div></span>";
		if (numUnavail > 0) HTMLString += "<span class=\"unavailable\" style=\"width: " + sizeUnavail.toFixed() + "px\"><div class=\"count\">" + numUnavail + "</div></span>";
		if (numMissed > 0)  HTMLString += "<span class=\"missed\" style=\"width: " + sizeMissed.toFixed() + "px\"><div class=\"count\">" + numMissed + "</div></span>";
		if (numRaids == 0)  HTMLString += "<span class=\"missed\" style=\"width: " + barSize + "px\"><div class=\"count\">&nbsp;</div></span>";
	
		HTMLString += "<span class=\"end\"></span></div>";
	});
	
	HTMLString += "</div>";
		
	// Initial visibility setup
	
	HTMLString += "<button id=\"applyButton\">" + L("Apply changes") + "</button>";
		
	$("#body").empty().append(HTMLString);
	
	$("#locationimagelist").hide();
	$("#groups").hide();
	$("#locationsettings").hide();
	$("#statistics").hide();
	
	$("#purgeMetric").combobox();
	$("#lockMetric").combobox();	
	$("#starthour").combobox();
	$("#startminute").combobox();
	$("#endhour").combobox();
	$("#endminute").combobox();
	$("#raidsize").combobox();
	$("#banner").combobox();
	
	$("#raidsettings").hide();
	
	// Fill user lists
	
	var banned   = new CGroup("groupBanned");
	var member   = new CGroup("groupMember");
	var raidlead = new CGroup("groupRaidlead");
	var admin    = new CGroup("groupAdmin");
	var removed  = new CGroup("groups");
	
	Message.children("user").each( function() { 
		if ( $(this).children("group").text() == "none" )
		{
			banned.addUser( $(this).children("id").text(),
				$(this).children("login").text(),
				$(this).children("binding").text() );
		}
		else if ( $(this).children("group").text() == "member" )
		{
			member.addUser( $(this).children("id").text(),
				$(this).children("login").text(),
				$(this).children("binding").text() );
		}
		else if ( $(this).children("group").text() == "raidlead" )
		{
			raidlead.addUser( $(this).children("id").text(),
				$(this).children("login").text(),
				$(this).children("binding").text() );
		}
		else if ( $(this).children("group").text() == "admin" )
		{
			admin.addUser( $(this).children("id").text(),
				$(this).children("login").text(),
				$(this).children("binding").text() );
		}
	});
	
	banned.refreshList();
	member.refreshList();
	raidlead.refreshList();
	admin.refreshList();
	
	// Setup location
	
	$("#locationsettings .imagepicker")
		.click( function() { 
			$("#locationimagelist").data("external", $(this));
			showTooltipRaidImageListAtElement($(this)); 
			event.stopPropagation(); 
		});
		
	$("#locationsettings").data("removed", new Array());
	
	// Setup UI
	
	$("#purgeTime").val( purgeRaidTime );
	$("#lockTime").val( lockRaidTime );
	
	$("#applyButton").button({ icons: { secondary: "ui-icon-disk" }})
		.click( function() { triggerSettingsUpdate(); } )
		.css( "font-size", 11 );
	
	// setup tab switching
	
	$("#userstoggle").click( function() {
	    changeContext( "settings,users" );
	});
	
	$("#locationstoggle").click( function() {
	    changeContext( "settings,location" );
	});
	
	$("#settingstoggle").click( function() {
	    changeContext( "settings,general" );
	});
	
	$("#statstoggle").click( function() {
        changeContext( "settings,stats" );
	});
	
	loadSettingsPanel( Message.children("show").text() );
}

// -----------------------------------------------------------------------------

function triggerSettingsUpdate()
{
	if ( g_User == null ) 
		return;
		
	var banned   = $("#groupBanned").data("helper");
	var member   = $("#groupMember").data("helper");
	var raidlead = $("#groupRaidlead").data("helper");
	var admin    = $("#groupAdmin").data("helper");
	var removed  = $("#groups").data("helper");

	var bannedArray   = banned.getUserIdArray();
	var memberArray   = member.getUserIdArray();
	var raidleadArray = raidlead.getUserIdArray();
	var adminArray    = admin.getUserIdArray();
	var removedArray  = removed.getUserIdArray();
	
	var purgeRaidTime = calculateUnixTime( $("#purgeTime").val(), $("#purgeMetric")[0].selectedIndex );
	var lockRaidTime  = calculateUnixTime( $("#lockTime").val(), $("#lockMetric")[0].selectedIndex );
	
	var locationIdData    = new Array();
	var locationNameData  = new Array();
	var locationImageData = new Array();
	
	$("#locationsettings").children(".location").each( function() {
		
		var locationId = $(this).attr("id");
	    locationId = parseInt(locationId.substring(9,locationId.length));
	        	
		locationIdData.push( locationId );
		locationNameData.push( $(this).children(".locationname").val() );
		locationImageData.push( $(this).children(".imagepicker").data("selectedImage") );
	});
	
	var parameters = {
		banned	  			: bannedArray,
		member	  			: memberArray,
		raidlead  			: raidleadArray,
		admin     			: adminArray,
		removed   			: removedArray,
		locationIds			: locationIdData,
		locationNames		: locationNameData,
		locationImages		: locationImageData,
		locationRemoved		: $("#locationsettings").data("removed"),
		purgeTime 			: purgeRaidTime,
		lockTime  			: lockRaidTime,
		raidStartHour		: $("#starthour").val(),
		raidStartMinute		: $("#startminute").val(),
		raidEndHour	    	: $("#endhour").val(),
		raidEndMinute		: $("#endminute").val(),
		raidSize			: $("#raidsize").val(),
		site				: $("#site").val(),
		banner				: $("#banner").val()
	};
	
	AsyncQuery( "settings_update", parameters, displaySettings ); 
}

// -----------------------------------------------------------------------------

function loadSettings( a_Name )
{
	reloadUser();
	
	if ( (g_User == null) || !g_User.isAdmin ) 
		return;
		
	$("#body").empty();
		
	var Parameters = {
	   showPanel : a_Name
	};
		
	AsyncQuery( "query_settings", Parameters, displaySettings );
}

// -----------------------------------------------------------------------------

function loadSettingsPanel( a_Name )
{
    if ( $("#settings").length == 0 )
    {
        loadSettings( a_Name );
    }
    else
    {
        switch( a_Name )
        {
        default:
        case "users":
            showPanel("#groups", "users");
    		break;
    		
        case "location":
            showPanel("#locationsettings", "locations");
    		break;
    		
        case "general":
            showPanel("#raidsettings", "settings");
    		break;
    		
        case "stats":
            showPanel("#statistics", "stats");
    		break;
        }
    }
}

// -----------------------------------------------------------------------------

function loadForeignProfile( a_Id )
{
	reloadUser();
	
	if ( (g_User == null) || !g_User.isAdmin ) 
		return;
		
	$("#body").empty();
	
	var Parameters = {
		id : a_Id
	};

	AsyncQuery( "query_profile", Parameters, displayForeignProfile );
}