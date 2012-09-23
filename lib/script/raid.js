// -----------------------------------------------------------------------------
//  CRaidMemberList
// -----------------------------------------------------------------------------

function CRaidMemberList()
{
    this.players = Array();
    this.nextRandomId = -1;
    
    // -------------------------------------------------------------------------
    
    this.AddPlayer = function( a_PlayerXML ) 
    {
        var newPlayer = {
            id         : parseInt(a_PlayerXML.children("id:first").text()), // this is the CharacterId
            name       : a_PlayerXML.children("name:first").text(),
            class      : a_PlayerXML.children("class:first").text(),
            mainchar   : a_PlayerXML.children("mainchar:first").text() == "true",
            activeRole : parseInt( a_PlayerXML.children("role:first").text() ),
            firstRole  : parseInt( a_PlayerXML.children("role1:first").text() ),
            secondRole : parseInt( a_PlayerXML.children("role2:first").text() ),
            status     : a_PlayerXML.children("status:first").text(),
            comment    : a_PlayerXML.children("comment:first").text()
        };
        
        // Random player id generation.
        // As the CharacterId field is unique these have to be generated on load.
        
        if ( newPlayer.id == 0 )
        {
            newPlayer.id = this.nextRandomId;
            --this.nextRandomId;
        }
        
        this.players.push(newPlayer);
        
        this.nextRandomId = Math.min(newPlayer.id, this.nextRandomId);      
    }
    
    // -----------------------------------------------------------------------------
    
    this.AddRandomPlayer = function( a_RoleIdx )
    {
        var newPlayer = {
            id         : this.nextRandomId,
            name       : "Random",
            class      : "random",
            mainchar   : true,
            activeRole : a_RoleIdx,
            firstRole  : a_RoleIdx,
            secondRole : a_RoleIdx,
            status     : "available",
            comment    : ""
        };
        
        --this.nextRandomId;
        this.players.push(newPlayer);
        this.UpdateRoleList( a_RoleIdx );
    }
    
    // -------------------------------------------------------------------------
    
    this.ForEachPlayer = function( a_Callback )
    {
        for ( var pIdx=0; pIdx<this.players.length; ++pIdx )
        {
            a_Callback( this.players[pIdx] );
        }
    }
    
    // -------------------------------------------------------------------------
    
    this.ForEachPlayerWithRole = function( a_Role, a_Callback )
    {
        for ( var pIdx=0; pIdx<this.players.length; ++pIdx )
        {
            if ( this.players[pIdx].activeRole == a_Role )
            {
                a_Callback( this.players[pIdx] );
            }
        }
    }
    
    // -------------------------------------------------------------------------
    
    this.NumPlayersWithRole = function( a_Role )
    {
        var count = 0;
        for ( var pIdx=0; pIdx<this.players.length; ++pIdx )
        {
            if ( this.players[pIdx].activeRole == a_Role )
            {
                ++count;
            }
        }
        
        return count;
    }
    
    // -------------------------------------------------------------------------
    
    this.GetPlayerIndex = function( a_PlayerId )
    {
        for ( var pIdx=0; pIdx<this.players.length; ++pIdx )
        {
            if ( this.players[pIdx].id == a_PlayerId )
            {
                return pIdx;
            }
        }
    
        return -1;
    }
    
    // -------------------------------------------------------------------------
    
    this.GetPlayersForRole = function( a_RoleId, a_Array )
    {
        this.ForEachPlayerWithRole( a_RoleId, function( a_Player ) { 
            if ( a_Player.id < 0 )
            {
                a_Array.push( a_Player.id );
                a_Array.push( a_Player.status );
                a_Array.push( a_Player.name );
            }
            else
            {
                a_Array.push( a_Player.id );
                a_Array.push( a_Player.status );
            }
        });
    }
    
    // -------------------------------------------------------------------------
    
    this.ChangePlayerName = function( a_PlayerId, a_Name )
    {
        var pIdx = this.GetPlayerIndex( a_PlayerId );
        this.players[pIdx].name = a_Name;
    }
    
    // -------------------------------------------------------------------------
    
    this.DisplayPlayerSlot = function( a_Player, a_ClipStatus )
    {
        var HTMLString  = "";        
        var layoutClass = ((a_ClipStatus.clipItemCount % a_ClipStatus.colsPerClip) == 0) ? "break" : "nobreak";
        var bgClass = (a_Player.comment == "") ? "activeSlot" : "activeSlotComment";
        
        HTMLString += "<div class=\""+bgClass+" "+layoutClass+"\" id=\"sp"+a_Player.id+"\">";
        HTMLString += "<div class=\"playerIcon\" style=\"background-image: url(images/classessmall/"+a_Player.class+".png)\">";
        
        if ( !a_Player.mainchar )
            HTMLString += "<div class=\"twinkbadge\"></div>";
        
        HTMLString += "</div>";
        
        if ( a_Player.class == "random" )
            HTMLString += "<input class=\"editableName\" type=\"test\" value=\"" + a_Player.name + "\"/>";
        else
            HTMLString += "<div class=\"playerName\">" + a_Player.name + "</div>";
        
        HTMLString += "</div>";
        HTMLString += this.UpdateClipStatus( a_ClipStatus );        
        
        return HTMLString;
    }
    
    // -------------------------------------------------------------------------
    
    this.DisplayWaitSlot = function( a_Player, a_ClipStatus )
    {
        var HTMLString = "";
        var layoutClass = ((a_ClipStatus.clipItemCount % a_ClipStatus.colsPerClip) == 0) ? "break" : "nobreak";
        var bgClass = (a_Player.comment == "") ? "waitSlot" : "waitSlotComment";
        
        HTMLString += "<div class=\""+bgClass+" "+layoutClass+"\" id=\"sp"+a_Player.id+"\">";
        HTMLString += "<div class=\"playerIcon\" style=\"background-image: url(images/classessmall/"+a_Player.class+".png)\">";
                
        if ( !a_Player.mainchar )
            HTMLString += "<div class=\"twinkbadge\"></div>";
        
        HTMLString += "</div>";
        
        if ( a_Player.class == "random" )
            HTMLString += "<input class=\"editableName\" type=\"test\" value=\"" + a_Player.name + "\"/>";
        else
            HTMLString += "<div class=\"playerName\">" + a_Player.name + "</div>";
            
        HTMLString += "</div>";
        HTMLString += this.UpdateClipStatus( a_ClipStatus );        
        
        return HTMLString;
    }
    
    // -------------------------------------------------------------------------
    
    this.DisplayEmptySlot = function( a_ClipStatus )
    {
        var HTMLString = "";
        var layoutClass = ((a_ClipStatus.clipItemCount % a_ClipStatus.colsPerClip) == 0) ? "break" : "nobreak";
        
        HTMLString += "<div class=\"emptySlot "+layoutClass+"\">";
        HTMLString += "<div class=\"playerIcon\" style=\"background-image: url(images/classessmall/empty.png)\"></div>";
        HTMLString += "</div>";
        
        HTMLString += this.UpdateClipStatus( a_ClipStatus );     
        return HTMLString;
    }
    
    // -------------------------------------------------------------------------
    
    this.DisplayAddRandomSlot = function( a_ClipStatus )
    {
        var HTMLString = "";
        var layoutClass = ((a_ClipStatus.clipItemCount % a_ClipStatus.colsPerClip) == 0) ? "break" : "nobreak";
        
        HTMLString += "<div class=\"randomSlot "+layoutClass+"\">";
        HTMLString += "</div>";
               
        HTMLString += this.UpdateClipStatus( a_ClipStatus );
        return HTMLString;
    }
    
    // -------------------------------------------------------------------------
    
    this.UpdateClipStatus = function( a_ClipStatus )
    {
        var HTMLString = "";
        var maxNumRows = 9;
        
        ++a_ClipStatus.itemCount;
        ++a_ClipStatus.clipItemCount;
        
        if ( a_ClipStatus.itemCount < a_ClipStatus.displayCount )
        {
            if ( a_ClipStatus.clipItemCount == ((a_ClipStatus.colsPerClip*maxNumRows)-1) )
            {
                var layoutClass = (a_ClipStatus.colsPerClip == 1) ? "break" : "nobreak";
                
                HTMLString += "<div class=\"clipchange "+layoutClass+"\" onclick=\"raidShowClip('role"+a_ClipStatus.roleId+"clip"+(a_ClipStatus.currentId+1)+"')\"></div>";
                HTMLString += "</div>";
                HTMLString += "<div class=\"clip\" id=\"role"+a_ClipStatus.roleId+"clip"+(a_ClipStatus.currentId+1)+"\">";
                HTMLString += "<div class=\"clipchange break\" onclick=\"raidShowClip('role"+a_ClipStatus.roleId+"clip"+a_ClipStatus.currentId+"')\"></div>";
                
                ++a_ClipStatus.currentId;
                a_ClipStatus.clipItemCount = 1;
            }
        }
                        
        return HTMLString;
    }
    
    // -------------------------------------------------------------------------
    
    this.DisplayRole = function( a_RoleId, a_Columns, a_RequiredSlots )
    {
        var HTMLString = "<div class=\"roleList\">";
        
        HTMLString += "<h2>"+g_RoleNames[g_RoleIdents[a_RoleId]]+"</h2>";
        HTMLString += "<div class=\"clip\" id=\"role"+a_RoleId+"clip0\">";
        
        var clipStatus = {
            roleId        : a_RoleId,
            colsPerClip   : a_Columns,
            currentId     : 0,
            itemCount     : 0,
            clipItemCount : 0,
            displayCount  : Math.max(this.NumPlayersWithRole(a_RoleId), a_RequiredSlots)
        };
        
        var self = this;
        
        // Display raiding players
        
        this.ForEachPlayerWithRole( a_RoleId, function(a_Player) {
            if ( a_Player.status == "ok" )
            {
                HTMLString += self.DisplayPlayerSlot( a_Player, clipStatus );
            }
        });
        
        // Display waiting players
        
        this.ForEachPlayerWithRole( a_RoleId, function(a_Player) {
            if ( a_Player.status == "available" )
            {
                HTMLString += self.DisplayWaitSlot( a_Player, clipStatus );
            }
        });
        
        // Add a slot to add randoms
        
        var itemsRemain = a_RequiredSlots;
        
        if ( g_User.isRaidlead ) 
        {
		    HTMLString += this.DisplayAddRandomSlot( clipStatus );
            ++itemsRemain;
        }
        
        // Display required, empty slots
        
        while ( clipStatus.itemCount < itemsRemain )
        {
            HTMLString += this.DisplayEmptySlot( clipStatus );
        }
        
        
        HTMLString += "</div>";
        HTMLString += "</div>";
        
        return HTMLString;
    }
    
    // -----------------------------------------------------------------------------
    
    this.BindClipPlayer = function( a_ClipItem )
    {
        var pid = parseInt( a_ClipItem.attr("id").substr(2) );
        var playerList = this;
            
        a_ClipItem.draggable({
            delay          : 100,
            revert         : true,
            revertDuration : 200,
            helper         : "clone",
            start          : function() { playerList.ShowDropTargets(pid, $(this)); },
            stop           : function() { playerList.HideDropTargets(); }
        });
    
        a_ClipItem.children(".editableName").each( function() {
            // Block click events to avoid up-/downgrad
            $(this).click( function(event) { event.stopPropagation(); });
            
            // Editing the text field starts the "edit mode"
            $(this).focus( function() { 
                $(this).prev()
                    .css("background-image", "url(lib/layout/images/remove.png)")
                    .click( function(event) {
                        event.stopPropagation();
                        playerList.RemovePlayer(pid);
                    });
            });
            
            $(this).blur( function() { 
                playerList.ChangePlayerName(pid, $(this).val());
                $(this).prev()
                    .css("background-image", "url(images/classessmall/random.png)")
                    .unbind("click");
            });
        });
            
    }
    
    // -----------------------------------------------------------------------------
    
    this.BindClips = function( a_Parent )
    {
        if ( g_User == null )
            return;
            
        var clips = a_Parent.children(".clip");
        var config = a_Parent.data("config");
        var playerList = this;
        
        if ( g_User.isRaidlead ) 
		{
            // attach event listeners for wait slots
            // click upgrades
            
            clips.children(".waitSlot").each( function() {
                var pid = parseInt( $(this).attr("id").substr(2) );
                $(this).click( function() { playerList.UpgradePlayer(pid); });
                
                playerList.BindClipPlayer( $(this) );
            });
            
            // attach event listeners for wait slots with comment.
            // 1st click opens comment, 2nd click upgrades
            
            clips.children(".waitSlotComment").each( function() {
                var pid     = parseInt( $(this).attr("id").substr(2) );
                var pIdx    = playerList.GetPlayerIndex(pid);
                var name    = playerList.players[pIdx].name;
                var comment = playerList.players[pIdx].comment; 
                var image   = "images/classessmall/" + playerList.players[pIdx].class + ".png";
                var slot    = $(this);
                var icon    = slot.children(".playerIcon");
                
                var onAbortFunction = function() {
                    icon.css("background-image","url("+image+")");
                    slot.unbind("click").click( onClickFunction );
                };
                
                var onClickFunction = function(event) {
                    icon.css("background-image","url(lib/layout/images/move_up.png)");
                    slot.unbind("click").click( function() { playerList.UpgradePlayer(pid); });
                    
                    showAttendeeTooltip( icon, image, name, comment, true, onAbortFunction );                    
                    event.stopPropagation();
                };
                
                slot.click( onClickFunction );
                playerList.BindClipPlayer( slot );
            });
            
            // attach event listeners for active slots
            // click downgrades
            
            clips.children(".activeSlot").each( function() {
                var pid = parseInt( $(this).attr("id").substr(2) );
                $(this).click( function() { playerList.DowngradePlayer(pid); });
                
                playerList.BindClipPlayer( $(this) );
            });     
            
            // attach event listeners for active slots with comment.
            // 1st click opens comment, 2nd click downgrades
            
            clips.children(".activeSlotComment").each( function() {
                var pid     = parseInt( $(this).attr("id").substr(2) );
                var pIdx    = playerList.GetPlayerIndex(pid);
                var name    = playerList.players[pIdx].name;
                var comment = playerList.players[pIdx].comment; 
                var image   = "images/classessmall/" + playerList.players[pIdx].class + ".png";
                var slot    = $(this);
                var icon    = slot.children(".playerIcon");
                
                var onAbortFunction = function() {
                    icon.css("background-image","url("+image+")");
                    slot.unbind("click").click( onClickFunction );
                };
                
                var onClickFunction = function(event) {
                    icon.css("background-image","url(lib/layout/images/move_down.png)");
                    slot.unbind("click").click( function() { playerList.DowngradePlayer(pid); });
                    
                    showAttendeeTooltip( icon, image, name, comment, true, onAbortFunction );                    
                    event.stopPropagation();
                };
                
                slot.click( onClickFunction );
                playerList.BindClipPlayer( slot );
            });
            
            // attach event handlers for "add random player" button
            
            clips.children(".randomSlot").each( function() {
                $(this).click( function() { playerList.AddRandomPlayer(config.id); });
            });
        }
        else
        {
            clips.children(".activeSlotComment, .waitSlotComment").each( function() {
                var pid     = parseInt( $(this).attr("id").substr(2) );
                var pIdx    = playerList.GetPlayerIndex(pid);
                var name    = playerList.players[pIdx].name;
                var comment = playerList.players[pIdx].comment; 
                var image   = "images/classessmall/" + playerList.players[pIdx].class + ".png";
                var slot    = $(this);
                var icon    = slot.children(".playerIcon");
                
                slot.click( function(event) { 
                    showAttendeeTooltip( icon, image, name, comment, true, null );
                    event.stopPropagation();
                });
            });
        }
    }
    
    // -------------------------------------------------------------------------
    
    this.UpdateRoleList = function( a_RoleIdx )
    {
        var roleList = $( $("#raidsetup").children(".roleList")[a_RoleIdx] );
        var roleConf = roleList.data("config");
                
        roleList.replaceWith( this.DisplayRole(roleConf.id, roleConf.columns, roleConf.reqSlots) );
        roleList = $( $("#raidsetup").children(".roleList")[a_RoleIdx] );
        roleList.data("config", roleConf);
        
        $("#"+roleConf.clip).show();
        
        this.BindClips( roleList );
    }

    // -----------------------------------------------------------------------------
    
    this.ShowDropTargets = function( a_PlayerId, a_Source )
    {
        var playerList = $("#raiddetail").data("players");
        var pIdx       = this.GetPlayerIndex( a_PlayerId );
        var player     = this.players[pIdx];
        var roleLists  = $("#raidsetup").children(".roleList");
        var roleIdx    = 0;
        
        roleLists.each( function() { 
            
            if ( ((roleIdx == player.firstRole) || 
                  (roleIdx == player.secondRole)) &&
                  (roleIdx != player.activeRole) )
            {
                var currentRoleIdx = roleIdx;
                $(this).droppable({
                    drop: function() { 
                        a_Source.draggable("option", "revert", false);
                        a_Source.draggable("destroy").detach();
                        playerList.MovePlayer(a_PlayerId, currentRoleIdx);
                    }
                });
            }
            else
            {
                $(this).fadeTo(100, 0.15);
            }
            
            ++roleIdx;
        });
    }
    
    // -----------------------------------------------------------------------------
    
    this.HideDropTargets = function()
    {
        $("#raidsetup").children(".roleList")
            .fadeTo(100, 1.0)
            .droppable("destroy");
    }
    
    // -------------------------------------------------------------------------
    
    this.UpgradePlayer = function( a_PlayerId )
    {
        var pIdx = this.GetPlayerIndex( a_PlayerId );
        this.players[pIdx].status = "ok";
        this.UpdateRoleList( this.players[pIdx].activeRole );
    }
    
    // -------------------------------------------------------------------------
    
    this.DowngradePlayer = function( a_PlayerId )
    {
        var pIdx = this.GetPlayerIndex( a_PlayerId );
        var roleIdx = this.players[pIdx].activeRole;
        
        this.players[pIdx].status = "available";
        this.UpdateRoleList( roleIdx );
    }
    
    // -----------------------------------------------------------------------------
    
    this.MovePlayer = function( a_PlayerId, a_RoleIdx )
    {
        var pIdx     = this.GetPlayerIndex( a_PlayerId );
        var prevRole = this.players[pIdx].activeRole;
        
        this.players[pIdx].activeRole = a_RoleIdx;
        
        this.UpdateRoleList( prevRole );
        this.UpdateRoleList( a_RoleIdx );
    }
    
    // -------------------------------------------------------------------------
    
    this.RemovePlayer = function( a_PlayerId )
    {
        var pIdx = this.GetPlayerIndex( a_PlayerId );
        var role = this.players[pIdx].activeRole;
        
        this.players.splice(pIdx,1);
        this.UpdateRoleList( role );
    }
}

// -----------------------------------------------------------------------------
//  player list functions
// -----------------------------------------------------------------------------

function raidShowClip( a_ClipId )
{
    var clipToShow = $("#"+a_ClipId);
    var roleList = clipToShow.parent();
    var roleConf = roleList.data("config");
    roleConf.clip = a_ClipId;
    
    roleList.children(".clip").hide();
    clipToShow.show();
}

// -----------------------------------------------------------------------------

function validateRoleCounts()
{
    var totalMembers = 0;
    var maxMembers   = $("#raidoptions").data("info").size;
    
    for ( var i=0; i < g_RoleIds.length; ++i )
    {
        var slotElement = $("#slotCount"+i);
        var roleSlots   = parseInt( slotElement.val() );
        var maxAllowed  = maxMembers - (g_RoleIds.length - (i+1));
        
        if ( (totalMembers + roleSlots > maxAllowed) || 
             ((i == g_RoleIds.length-1) && (totalMembers + roleSlots < maxAllowed)) )
        {
            roleSlots = maxAllowed - totalMembers;
        }
        
        slotElement.unbind("change");
        slotElement[0].value = roleSlots;
        slotElement.bind("change", validateRoleCounts );
        
        totalMembers += roleSlots;
    }
}

// -----------------------------------------------------------------------------
//  display functions
// -----------------------------------------------------------------------------

function displayRaidInfo( a_RaidXML, a_AppendTo )
{
    var MonthArray = Array(L("January"), L("February"), L("March"), L("April"), L("May"), L("June"), L("July"), L("August"), L("September"), L("October"), L("November"), L("December"));
    
    var raidImage = a_RaidXML.children("image:first").text();
    var raidName  = a_RaidXML.children("location:first").text();
    var raidSize  = 0;
    var numRoles  = Math.min( g_RoleNames.length, 5 );
    
    a_RaidXML.children("slots").children("required").each( function() {
       raidSize += parseInt($(this).text());
    });
        
    var startDate = a_RaidXML.children("startDate:first").text().split("-");
    var endDate   = a_RaidXML.children("endDate:first").text().split("-");
    
    var startTime = a_RaidXML.children("start:first").text();
    var endTime   = a_RaidXML.children("end:first").text();
    
    var HTMLString = "<div class=\"raidinfo\">";
    HTMLString += "<img src=\"images/raidbig/" + raidImage + "\" class=\"raidicon\">";
    HTMLString += "<div class=\"raidname\">" + raidName + "</div>";
    HTMLString += "<div class=\"raidsize\">" + raidSize + " " + L("Players") + "</div>";
    HTMLString += "<div class=\"datetime\">" + parseInt(startDate[2], 10) + ". " + MonthArray[startDate[1]-1] + ", ";
    HTMLString += startTime + " - " + endTime + "</div>";
    HTMLString += "</div>";
    
    $("#"+a_AppendTo).prepend(HTMLString);
}

// -----------------------------------------------------------------------------

function displayRaidSetup( a_RaidXML )
{
    var playerList = $("#raiddetail").data("players");
    var numRoles   = Math.min( g_RoleNames.length, 5 );
    var roleCounts = a_RaidXML.children("slots:first");
    
    var HTMLString = "<div id=\"raidsetup\"></div>";    
    $("#raiddetail").append(HTMLString);
    
    for ( var i=0; i<numRoles; ++i )
    {
        var numCols  = (i<numRoles-1) ? 1 : 6-i;
        var required = parseInt(roleCounts.children("required").eq(i).text());
        
        HTMLString = playerList.DisplayRole(i, numCols, required);
        $("#raidsetup").append(HTMLString);
        $("#raidsetup").children(":last").data("config", {
            id       : i,
            columns  : numCols,
            reqSlots : required,
            clip     : "role"+i+"clip0",
        });
    }
    
    $("#raidsetup").children(".roleList").each( function() { 
        $(this).children(".clip:first").show();
        playerList.BindClips( $(this) );
    });
    
    displayRaidInfo( a_RaidXML, "setup" );
}

// -----------------------------------------------------------------------------

function displayRaidSlackers( a_RaidXML )
{
    var HTMLString = "<div id=\"slackers\">";
    HTMLString += "</div>";
    
    $("#raiddetail").append(HTMLString);
    
    displayRaidInfo( a_RaidXML, "slackers" );
}

// -----------------------------------------------------------------------------

function displayRaidSettings( a_MessageXML, a_RaidXML )
{
    var HTMLString = "<div id=\"raidoptions\">";
    HTMLString += "<div class=\"settingspanel\"></div>";
    HTMLString += "</div>";
    
    $("#raiddetail").append(HTMLString);
    
    var panel = $("#raidoptions").children(".settingspanel:first");
    
    var Locations       = a_MessageXML.children("locations");
    var LocationInfos   = Locations.children("location"); 
	var LocationImages  = Locations.children("locationimage");
	
	var raidImage       = a_RaidXML.children("image:first").text();
    var raidName        = a_RaidXML.children("location:first").text();
    var raidLocation    = a_RaidXML.children("locationId:first").text();
    var raidSize        = parseInt(a_RaidXML.children("size:first").text());
    var raidComment     = a_RaidXML.children("comment:first").text();
	var raidSlots       = a_RaidXML.children("slots").children("required");
	var raidStatus      = a_RaidXML.children("stage:first").text();
	var raidMode        = a_RaidXML.children("mode:first").text();
	
    var raidStart       = a_RaidXML.children("start:first").text();
    var raidStartHour   = parseInt(raidStart.substr(0,raidStart.indexOf(":")), 10);
    var raidStartMinute = parseInt(raidStart.substr(raidStart.indexOf(":")+1), 10);
    
    var raidEnd         = a_RaidXML.children("end:first").text();
    var raidEndHour     = parseInt(raidEnd.substr(0,raidEnd.indexOf(":")), 10);
    var raidEndMinute   = parseInt(raidEnd.substr(raidEnd.indexOf(":")+1), 10);
    var raidDate        = a_RaidXML.children("startDate:first").text();
    
    $("#raidoptions").data("info", {
        id    : parseInt(a_RaidXML.children("raidId:first").text()),
        year  : parseInt(raidDate.substr(0,4)),
        month : parseInt(raidDate.substr(5,2), 10),
        day   : parseInt(raidDate.substr(8,2), 10),
        size  : parseInt(raidSize)
    });
    
	// Clone raid panel from the new raid settings sheet.
	
	panel.append( $($("#newRaid").children()[1]).clone() );
    
    $("#newRaidSubmit").detach();
    $("#descriptiondummy").detach();
    $("#description").detach();
	
	// Generate location image list
    
    HTMLString = "";
    var numImages = 1;
	var imageList = new Array();
	
	LocationImages.each( function(index) {
		if ( numImages % 11 == 0 )
		{
			HTMLString += "<br/>";
			++numImages;	
		}
			
		HTMLString += "<img src=\"images/raidsmall/" + $(this).text() + "\" onclick=\"applyLocationImage(this)\" style=\"width:32px; height:32px; margin-right:5px;\"/>";
        ++numImages;
	});
	
	$("#locationimagelist").append(HTMLString);    
    $("#locationimagepicker").css("background-image", "url(images/raidbig/"+raidImage+")");
    	
	// Build location chooser
	
	LocationInfos.each( function(index) {
        imageList[index] = $(this).children("image").text();
        
        var selected = ($(this).children("id").text() == raidLocation) ? " selected" : "";
        $("#selectlocation").append("<option value=\"" + $(this).children("id").text() + "\""+selected+">" + $(this).children("name").text() + "</option>");
    });
    
    // Add remainig settings
    
    HTMLString  = "<br/><div style=\"float:left\">";
    HTMLString += "<div class=\"settingLabel\">"+L("Comment")+"</div>";
    HTMLString += "<div class=\"settingField\"><textarea id=\"comment\" class=\"settingEdit\" style=\"width:235px; height:64px\">"+raidComment+"</textarea></div>";
    
    HTMLString += "<div class=\"settingLabel\">"+L("RaidStatus")+"</div>";
    HTMLString += "<div class=\"settingField\"><select id=\"raidstage\">";
    HTMLString += "<option value=\"open\""+((raidStatus=="open") ? " selected" : "")+">"+L("RaidOpen")+"</option>";
    HTMLString += "<option value=\"locked\""+((raidStatus=="locked") ? " selected" : "")+">"+L("RaidLocked")+"</option>";
    HTMLString += "<option value=\"canceled\""+((raidStatus=="canceled") ? " selected" : "")+">"+L("RaidCanceled")+"</option>";
    HTMLString += "</select></div>";
    
    HTMLString += "<div class=\"settingLabel\">"+L("RaidSetupStyle")+"</div>";
    HTMLString += "<div class=\"settingField\"><select id=\"raidmode\">";
    HTMLString += "<option value=\"manual\""+((raidMode=="manual") ? " selected" : "")+">"+L("RaidModeManual")+"</option>";
    HTMLString += "<option value=\"attend\""+((raidMode=="attend") ? " selected" : "")+">"+L("RaidModeAttend")+"</option>";
    HTMLString += "<option value=\"all\""+((raidMode=="all") ? " selected" : "")+">"+L("RaidModeAll")+"</option>";
    HTMLString += "</select></div>";
    
    raidSlots.each( function(index) {
        if ( index < g_RoleIds.length )
        {
            HTMLString += "<div class=\"settingLabel\">"+L("RequiredForRole")+" \""+g_RoleNames[g_RoleIdents[index]]+"\"</div>";
            HTMLString += "<div class=\"settingField\"><input id=\"slotCount"+index+"\" class=\"settingEdit\" style=\"width:24px;\" value=\""+$(this).text()+"\" type=\"text\" onchange=\"validateRoleCounts()\"/></div>";
        }
    });
    
    HTMLString += "</div>";
    panel.append( HTMLString );
    
    $("#raidstage").combobox();
    $("#raidmode").combobox();
    
    // Select/set values for current raid
    
    $("#selectsize").children("option").each( function() {
        if ($(this).val() == raidSize)
            $(this).attr("selected", "selected");
    });
    
    $("#starthour").children("option").each( function() {
        if ($(this).val() == raidStartHour)
            $(this).attr("selected", "selected");
    });
    
    $("#startminute").children("option").each( function() {
        if ($(this).val() == raidStartMinute)
            $(this).attr("selected", "selected");
    });
    
    $("#endhour").children("option").each( function() {
        if ($(this).val() == raidEndHour)
            $(this).attr("selected", "selected");
    });
    
    $("#endminute").children("option").each( function() {
        if ($(this).val() == raidEndMinute)
            $(this).attr("selected", "selected");
    });
    
    // Setup copied UI
    
    $("#selectlocation").combobox();
	$("#selectlocation").combobox( "editable", true );
    $("#selectsize").combobox();
    $("#starthour").combobox();
    $("#startminute").combobox();
    $("#endhour").combobox();
    $("#endminute").combobox();
    
    // Event binding
    
    $("#locationimagepicker")
        .data("imageNames", imageList )
        .data( "selectedImage", raidImage )
        .click( function(event) { 
			showTooltipRaidImageList(); 
			event.stopPropagation(); 
        });
}

// -----------------------------------------------------------------------------

function displayRaid( a_XMLData )
{
    hideTooltip();
    closeSheet();
    
    if ( g_User == null )
        return;
    
    var PlayerList = new CRaidMemberList();
    var HTMLString = "<div id=\"raiddetail\">";
    
    HTMLString += "<div id=\"tablist\" class=\"tabs setup\">";
    HTMLString += "<div id=\"setuptoggle\" class=\"tab_icon icon_setup\"></div>";
    HTMLString += "<div id=\"slackerstoggle\" class=\"tab_icon icon_slackers_off\"></div>";
    
    if ( g_User.isRaidlead)
    {
        HTMLString += "<div id=\"settingstoggle\" class=\"tab_icon icon_settings_off\"></div>";    
    }
    
    HTMLString += "</div>";
    HTMLString += "<button id=\"applyButton\">" + L("Apply") + "</button>";
    HTMLString += "</div>";
    
    $("#body").empty().append(HTMLString);
            
    var Message = $(a_XMLData).children("messagehub:first");
    var Raid    = Message.children("raid:first");
    var RaidId  = parseInt(Raid.children("raidId").text());
    
    $("#raiddetail").data("players",PlayerList);    
    Raid.children("attendee").each(function() {
        PlayerList.AddPlayer($(this));
    });    
    
    displayRaidSetup( Raid );
    displayRaidSlackers( Raid );
    
    if ( g_User.isRaidlead)
    {
        displayRaidSettings( Message, Raid );
    }
    
    // Setup toplevel UI
    
    $("#applyButton").button({ icons: { secondary: "ui-icon-disk" }})
        .click( function() { triggerRaidUpdate(); } )
        .css( "font-size", 11 )
        .css( "position", "absolute" )
        .css( "left", 819 );
    
    $("#setuptoggle").click( function() {
        changeContext( "raid,setup,"+RaidId );
    });
    
    $("#slackerstoggle").click( function() {
        changeContext( "raid,slackers,"+RaidId );
    });
    
    if ( g_User.isRaidlead)
    {
        $("#settingstoggle").click( function() {
            changeContext( "raid,settings,"+RaidId );
        });
    }
        
    loadRaidPanel( Message.children("show").text(), RaidId );
}

// -----------------------------------------------------------------------------

function showRaidPanel( a_Panel, a_Section )
{
    $("#raidsetup").hide();
    $("#slackers").hide();
    $("#raidoptions").hide();
    
    $("#tablist").removeClass("setup");
    $("#tablist").removeClass("slackers");
    $("#tablist").removeClass("settings");

    $("#setuptoggle").removeClass("icon_setup");
    $("#slackerstoggle").removeClass("icon_slackers");
    $("#settingstoggle").removeClass("icon_settings");
    
    $("#setuptoggle").addClass("icon_setup_off");
    $("#slackerstoggle").addClass("icon_slackers_off");
    $("#settingstoggle").addClass("icon_settings_off");

    $(a_Panel).show();
    $("#tablist").addClass(a_Section);
    $("#"+a_Section+"toggle").removeClass("icon_"+a_Section+"_off");
    $("#"+a_Section+"toggle").addClass("icon_"+a_Section);
    
    $("#raidoptions").data("activesection", a_Section );
}

// -----------------------------------------------------------------------------
//  Callbacks
// -----------------------------------------------------------------------------

function loadRaid( a_RaidId, a_PanelName )
{
    reloadUser();
    
    if ( g_User == null ) 
        return;
        
    $("#body").empty();
        
    var Parameters = {
        id : a_RaidId,
        showPanel : a_PanelName
    };
    
    AsyncQuery( "raid_detail", Parameters, displayRaid );

}

// -----------------------------------------------------------------------------

function loadRaidPanel( a_Name, a_RaidId )
{
    if ( g_User == null ) 
        return;
        
    if ( $("#raiddetail").length == 0 )
    {
        loadRaid( a_RaidId, a_Name );
    }
    else
    {
        switch( a_Name )
        {    
        default:
        case "setup":
            showRaidPanel("#raidsetup", "setup");
            break;
        
        case "slackers":
            showRaidPanel("#slackers", "slackers");
            break;
            
        case "settings":
            showRaidPanel("#raidoptions", "settings");
            break;
        }
    }
}

// -----------------------------------------------------------------------------
//  Triggers
// -----------------------------------------------------------------------------

function triggerRaidUpdate()
{
    if ( (g_User == null) || !g_User.isRaidlead ) 
		return;

    var hash = window.location.hash.substring( 1, window.location.hash.length );
    var playerList = $("#raiddetail").data("players");
    var slotCount  = new Array(0,0,0,0,0);
        
    var role1players = new Array();
    var role2players = new Array();
    var role3players = new Array();
    var role4players = new Array();
    var role5players = new Array();
    
    var activePanel = "setup";
    if ( $("#slackers").css("display") )
    
    
    // Build the slot count array
    
    for ( var i=0; i<g_RoleIds.length; ++i )
    {
        var countField = $("#slotCount"+i);        
        if ( countField != null )
            slotCount[i] = countField.val();
    }
    
    // Generate arrays with playerId per role
    
    playerList.GetPlayersForRole(0, role1players);
    playerList.GetPlayersForRole(1, role2players);
    playerList.GetPlayersForRole(2, role3players);
    playerList.GetPlayersForRole(3, role4players);
    playerList.GetPlayersForRole(4, role5players);
    
    // Build parameter set
    
    var Parameters = {
        id           : $("#raidoptions").data("info").id,
        raidImage    : $("#locationimagepicker").data("selectedImage"),
        locationId   : $("#selectlocation").val(),
        locationSize : $("#selectsize").val(),
        locationName : $("#edit_selectlocation").val(),
        startHour    : $("#starthour").val(),
        startMinute  : $("#startminute").val(),
        endHour      : $("#endhour").val(),
        endMinute    : $("#endminute").val(),
        description  : $("#comment").val(),
        month        : $("#raidoptions").data("info").month,
        day          : $("#raidoptions").data("info").day,
        year         : $("#raidoptions").data("info").year,
        mode         : $("#raidmode option:selected").val(),
        stage        : $("#raidstage option:selected").val(),
        
        slotsRole    : slotCount,
        role1        : role1players,
        role2        : role2players,
        role3        : role3players,
        role4        : role4players,
        role5        : role5players,
        showPanel    : $("#raidoptions").data("activesection")
    };
		
	AsyncQuery( "raid_update", Parameters, displayRaid ); 
}