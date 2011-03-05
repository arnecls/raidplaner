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
			HTMLString += "<span class=\"userFunction functionDelete\"></span>";
			HTMLString += "<span class=\"userFunction functionEdit\"></span>";
			HTMLString += "<div class=\"userDrag\" index=\"" + i + "\">";
			HTMLString += "<img src=\"images/classessmall/random.png\" class=\"userImage\"/>";
			
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
			
			var Parameters = {
				id : userId
			};
		
			AsyncQuery( "query_profile", Parameters, displayForeignProfile );
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

function displaySettings( a_XMLData )
{
	var Message = $(a_XMLData).children("messagehub");
	var HTMLString = "<div id=\"settings\">";
	
	var purgeRaidTime   = 0;
	var purgeRaidMetric = 0;
	var lockRaidTime    = 0;
	var lockRaidMetric  = 0;
	
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
	});
	
	// Raidplaner settings
	
	HTMLString += "<h1>" + L("Settings") + "</h1>";
		
	HTMLString += "<div class=\"propDeleteRaid\">";
	HTMLString += "<div class=\"propLabel\">" + L("Delete raids") + "</div>";
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
	HTMLString += "<div class=\"propLabel\">" + L("Lock raids") + "</div>";
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
	
	// User settings
	
	HTMLString += "<h1>" + L("Users") + "</h1>";
	
	// NoGroup
	
	HTMLString += "<div id=\"groups\">";
	HTMLString += "<span class=\"groupSlot\">";
	HTMLString += "<div class=\"top\">" + L("Locked") + "</div>";
	HTMLString += "<div id=\"groupBanned\" class=\"center\">";
	HTMLString += "</div>";
	HTMLString += "<div class=\"bottom\"></div>";
	HTMLString += "</span>";
	
	// Member
	
	HTMLString += "<div id=\"groups\">";
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
	HTMLString += "<div>";
	
	HTMLString += "<button id=\"applyButton\">" + L("Apply changes") + "</button>";
	
	$("#body").empty().append(HTMLString);
	
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
	
	// Setup UI
	
	$("#purgeTime").val( purgeRaidTime );
	$("#lockTime").val( lockRaidTime );
	
	$("#applyButton").button({ icons: { secondary: "ui-icon-disk" }})
		.click( function() { triggerSettingsUpdate(); } )
		.css( "font-size", 11 );
	
	$("#purgeMetric").combobox();
	$("#lockMetric").combobox();
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
	
	var parameters = {
		banned	  : bannedArray,
		member	  : memberArray,
		raidlead  : raidleadArray,
		admin     : adminArray,
		removed   : removedArray,
		purgeTime : purgeRaidTime,
		lockTime  : lockRaidTime
	};
	
	AsyncQuery( "settings_update", parameters, displaySettings ); 
}

// -----------------------------------------------------------------------------

function loadSettings()
{
	reloadUser();
	
	if ( g_User == null ) 
		return;
		
	$("#body").empty();
		
	var Parameters = {
	};
		
	AsyncQuery( "query_settings", Parameters, displaySettings );
}