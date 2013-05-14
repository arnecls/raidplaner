// -----------------------------------------------------------------------------
//  CRaidMemberList
// -----------------------------------------------------------------------------

var PlayerFlagNone      = 0;
var PlayerFlagModified  = 1;
var PlayerFlagNew       = 1 << 1;
var PlayerFlagCharId    = 1 << 2;
var PlayerFlagUserId    = 1 << 3;
var PlayerFlagName      = 1 << 4;
var PlayerFlagComment   = 1 << 5;

function CRaidMemberList()
{
    this.mPlayers = Array();
    this.mRemovedPlayers = Array();
    this.mNextRandomId = -1;
    this.mStage = "";
    this.mMode = "manual";
    
    // -------------------------------------------------------------------------

    this.addPlayer = function( aPlayerXML )
    {
        var newPlayer = {
            id         : parseInt(aPlayerXML.children("id:first").text(), 10),
            userId     : parseInt(aPlayerXML.children("userId:first").text(), 10),
            timestamp  : parseInt(aPlayerXML.children("timestamp:first").text(), 10),
            hasRecord  : aPlayerXML.children("hasId:first").text() == "true",
            charId     : parseInt(aPlayerXML.children("charid:first").text(), 10),
            name       : aPlayerXML.children("name:first").text(),
            className  : aPlayerXML.children("class:first").text(),
            mainchar   : aPlayerXML.children("mainchar:first").text() == "true",
            activeRole : parseInt( aPlayerXML.children("role:first").text(), 10 ),
            firstRole  : parseInt( aPlayerXML.children("role1:first").text(), 10 ),
            secondRole : parseInt( aPlayerXML.children("role2:first").text(), 10 ),
            status     : aPlayerXML.children("status:first").text(),
            comment    : aPlayerXML.children("comment:first").text(),
            flags      : PlayerFlagNone,
            characters : Array()
        };

        aPlayerXML.children("chars:first").children("character").each( function() {
            var newTwink = {
                id         : parseInt($(this).children("id:first").text(), 10),
                mainchar   : $(this).children("mainchar:first").text() == "true",
                name       : $(this).children("name:first").text(),
                className  : $(this).children("class:first").text(),
                firstRole  : parseInt( $(this).children("role1:first").text(), 10 ),
                secondRole : parseInt( $(this).children("role2:first").text(), 10 )
            };

            newPlayer.characters.push(newTwink);
        });

        this.mPlayers.push(newPlayer);
    };

    // -----------------------------------------------------------------------------

    this.addRandomPlayer = function( aRoleIdx )
    {
        var newPlayer = {
            id         : this.mNextRandomId,
            userId     : 0,
            timestamp  : Math.round((new Date()).getTime() / 1000),
            hasRecord  : false,
            charId     : 0,
            name       : "Random",
            className  : "random",
            mainchar   : false,
            activeRole : aRoleIdx,
            firstRole  : aRoleIdx,
            secondRole : aRoleIdx,
            status     : (this.mMode == "all") ? "ok" : "available",
            comment    : "",
            flags      : PlayerFlagModified | PlayerFlagNew | PlayerFlagName,
            characters : Array()
        };

        // New random players have a negative id so we can insert them as new
        // players. Existing players have regular ids

        --this.mNextRandomId;
        this.mPlayers.push(newPlayer);
        this.updateRoleList( aRoleIdx );
    };

    // -------------------------------------------------------------------------

    this.forEachPlayer = function( aCallback )
    {
        for ( var pIdx=0; pIdx<this.mPlayers.length; ++pIdx )
        {
            aCallback( this.mPlayers[pIdx] );
        }
    };

    // -------------------------------------------------------------------------

    this.forEachPlayerWithRole = function( aRole, aCallback )
    {
        for ( var pIdx=0; pIdx<this.mPlayers.length; ++pIdx )
        {
            if ( this.mPlayers[pIdx].activeRole == aRole )
            {
                aCallback( this.mPlayers[pIdx] );
            }
        }
    };

    // -------------------------------------------------------------------------

    this.numPlayersWithRole = function( aRole )
    {
        var count = 0;
        for ( var pIdx=0; pIdx<this.mPlayers.length; ++pIdx )
        {
            if ( this.mPlayers[pIdx].activeRole == aRole )
            {
                ++count;
            }
        }

        return count;
    };

    // -------------------------------------------------------------------------

    this.getPlayerIndex = function( aPlayerId )
    {
        for ( var pIdx=0; pIdx<this.mPlayers.length; ++pIdx )
        {
            if ( this.mPlayers[pIdx].id == aPlayerId )
            {
                return pIdx;
            }
        }

        return -1;
    };

    // -------------------------------------------------------------------------

    this.getModifiedPlayersForRole = function( aRoleId, aArray )
    {
        // Returns an array with the following elements. Elements >= 4 depend
        // on the flags value.
        
        this.forEachPlayerWithRole( aRoleId, function( aPlayer ) {
            
            if ( (aPlayer.flags & PlayerFlagModified) !== 0 )
            {
                aArray.push( aPlayer.id );
                aArray.push( aPlayer.status );
                aArray.push( aPlayer.timestamp );
                aArray.push( aPlayer.flags );
    
                if ( (aPlayer.flags & PlayerFlagCharId) !== 0 )
                    aArray.push( aPlayer.charId );
                    
                if ( (aPlayer.flags & PlayerFlagUserId) !== 0 )
                    aArray.push( aPlayer.userId );
                
                if ( (aPlayer.flags & PlayerFlagName) !== 0 )
                    aArray.push( aPlayer.name );
                    
                if ( (aPlayer.flags & PlayerFlagComment) !== 0 )
                    aArray.push( aPlayer.comment );
            }
        });
    };

    // -------------------------------------------------------------------------

    this.changePlayerName = function( aPlayerId, aName )
    {
        var pIdx = this.getPlayerIndex( aPlayerId );
        
        if (aName != this.mPlayers[pIdx].name)
        {
            this.mPlayers[pIdx].name = aName;            
            this.mPlayers[pIdx].flags |= PlayerFlagModified | PlayerFlagName;
            
            return true;
        }
        
        return false;
    };

    // -------------------------------------------------------------------------

    this.generatePlayerSlot = function( aPlayer, aClipStatus )
    {
        var HTMLString  = "";
        var layoutClass = ((aClipStatus.clipItemCount % aClipStatus.colsPerClip) === 0) ? "break" : "nobreak";
        var bgClass = (aPlayer.comment === "") ? "activeSlot" : "activeSlotComment";

        if ( gUser.isRaidlead )
        {
            if (this.mMode != "all")
                bgClass += " clickable";
            bgClass += " dragable";
        }
        
        HTMLString += "<div class=\""+bgClass+" "+layoutClass+"\" id=\"sp"+aPlayer.id+"\">";
        HTMLString += "<div class=\"playerIcon\" style=\"background-image: url(images/classessmall/"+aPlayer.className+".png)\">";
        HTMLString += "<div class=\"slotMarker\"></div>";
        
        if ( aPlayer.mainchar )
            HTMLString += "<div class=\"mainbadge\"></div>";

        HTMLString += "</div>";

        if ( gUser.isRaidlead && (aPlayer.className == "random") )
            HTMLString += "<input class=\"editableName\" type=\"test\" value=\"" + aPlayer.name + "\"/>";
        else
            HTMLString += "<div class=\"playerName\">" + aPlayer.name + "</div>";

        HTMLString += "</div>";
        HTMLString += this.updateClipStatus( aClipStatus );

        return HTMLString;
    };

    // -------------------------------------------------------------------------

    this.generateSpilledSlot = function( aPlayer, aClipStatus )
    {
        var HTMLString  = "";
        var layoutClass = ((aClipStatus.clipItemCount % aClipStatus.colsPerClip) === 0) ? "break" : "nobreak";
        var bgClass = (aPlayer.comment === "") ? "spilledSlot" : "spilledSlotComment";

        if ( gUser.isRaidlead )
            bgClass += " dragable";
            
        HTMLString += "<div class=\""+bgClass+" "+layoutClass+"\" id=\"sp"+aPlayer.id+"\">";
        HTMLString += "<div class=\"playerIcon\" style=\"background-image: url(images/classessmall/"+aPlayer.className+".png)\">";
        HTMLString += "<div class=\"slotMarker\"></div>";
        
        if ( aPlayer.mainchar )
            HTMLString += "<div class=\"mainbadge\"></div>";

        HTMLString += "</div>";

        if ( gUser.isRaidlead && (aPlayer.className == "random") )
            HTMLString += "<input class=\"editableName\" type=\"test\" value=\"" + aPlayer.name + "\"/>";
        else
            HTMLString += "<div class=\"playerName\">" + aPlayer.name + "</div>";

        HTMLString += "</div>";
        HTMLString += this.updateClipStatus( aClipStatus );

        return HTMLString;
    };

    // -------------------------------------------------------------------------

    this.generateWaitSlot = function( aPlayer, aClipStatus, aBenched )
    {
        var HTMLString = "";
        var layoutClass = ((aClipStatus.clipItemCount % aClipStatus.colsPerClip) === 0) ? "break" : "nobreak";
        var bgClassBase = (aBenched) ? "benchSlot" : "waitSlot";
        var bgClass = (aPlayer.comment === "") ? bgClassBase : bgClassBase+"Comment";

        if ( gUser.isRaidlead )
        {
            bgClass += " dragable";
            if (!aBenched)
                bgClass += " clickable";
        }
            
        HTMLString += "<div class=\""+bgClass+" "+layoutClass+"\" id=\"sp"+aPlayer.id+"\">";
        HTMLString += "<div class=\"playerIcon\" style=\"background-image: url(images/classessmall/"+aPlayer.className+".png)\">";

        if ( aPlayer.mainchar )
            HTMLString += "<div class=\"mainbadge\"></div>";

        HTMLString += "</div>";

        if ( gUser.isRaidlead && (aPlayer.className == "random") )
            HTMLString += "<input class=\"editableName\" type=\"test\" value=\"" + aPlayer.name + "\"/>";
        else
            HTMLString += "<div class=\"playerName\">" + aPlayer.name + "</div>";

        HTMLString += "</div>";
        HTMLString += this.updateClipStatus( aClipStatus );

        return HTMLString;
    };

    // -------------------------------------------------------------------------

    this.generateEmptySlot = function( aClipStatus )
    {
        var HTMLString = "";
        var layoutClass = ((aClipStatus.clipItemCount % aClipStatus.colsPerClip) === 0) ? "break" : "nobreak";

        HTMLString += "<div class=\"emptySlot "+layoutClass+"\" style=\"background-image: url("+g_RoleImages[aClipStatus.roleId]+")\">";
        HTMLString += "</div>";

        HTMLString += this.updateClipStatus( aClipStatus );
        return HTMLString;
    };

    // -------------------------------------------------------------------------

    this.generateAddRandomSlot = function( aClipStatus, aOverEmptySlot )
    {
        var HTMLString = "";
        var layoutClass = ((aClipStatus.clipItemCount % aClipStatus.colsPerClip) === 0) ? "break" : "nobreak";

        if (aOverEmptySlot)
        {
            var roleImage = g_RoleImages[aClipStatus.roleId];
            var roleRndImage = roleImage.substr(0,roleImage.lastIndexOf(".")) + "_rnd" + roleImage.substr(roleImage.lastIndexOf("."));
            
            HTMLString += "<div class=\"randomSlot clickable "+layoutClass+"\" style=\"background-image: url("+roleRndImage+")\">";
            HTMLString += "</div>";
        }
        else
        {
            HTMLString += "<div class=\"randomSlot clickable "+layoutClass+"\">";
            HTMLString += "</div>";
        }
        
        HTMLString += this.updateClipStatus( aClipStatus );
        return HTMLString;
    };

    // -------------------------------------------------------------------------

    this.generateAbsentSlot = function( aPlayer, aClipStatus )
    {
        var HTMLString  = "";
        var bgClass = (aPlayer.comment === "") ? "benchSlot" : "benchSlotComment";

        HTMLString += "<div class=\""+bgClass+" clickable2 nobreak\" id=\"ap"+aPlayer.id+"\">";
        HTMLString += "<div class=\"playerIcon\" id=\"ap_icon"+aPlayer.id+"\" style=\"background-image: url(images/classessmall/"+aPlayer.className+".png)\">";
        HTMLString += "</div>";
        HTMLString += "<div class=\"playerName\">" + aPlayer.name + "</div>";

        HTMLString += "</div>";
        HTMLString += this.updateRowClipStatus( aClipStatus );

        return HTMLString;
    };

    // -------------------------------------------------------------------------

    this.updateClipStatus = function( aClipStatus )
    {
        var HTMLString = "";
        
        ++aClipStatus.itemCount;
        ++aClipStatus.clipItemCount;

        if ( aClipStatus.itemCount < aClipStatus.displayCount )
        {
            if ( aClipStatus.clipItemCount == ((aClipStatus.colsPerClip * aClipStatus.rowsPerClip)-1) )
            {
                HTMLString += this.generateNextClipButton(aClipStatus);
            }
        }

        return HTMLString;
    };
    
    // -------------------------------------------------------------------------
    
    this.generateNextClipButton = function( aClipStatus )
    {
        var HTMLString = "";
        var layoutClass = (aClipStatus.colsPerClip == 1) ? "break" : "nobreak";

        HTMLString += "<div class=\"clipchange clickable "+layoutClass+"\" onclick=\"showRaidClip('role"+aClipStatus.roleId+"clip"+(aClipStatus.currentId+1)+"')\"></div>";
        HTMLString += "</div>";
        HTMLString += "<div class=\"clip\" id=\"role"+aClipStatus.roleId+"clip"+(aClipStatus.currentId+1)+"\">";
        HTMLString += "<div class=\"clipchange clickable break\" onclick=\"showRaidClip('role"+aClipStatus.roleId+"clip"+aClipStatus.currentId+"')\"></div>";

        ++aClipStatus.currentId;
        aClipStatus.clipItemCount = 1;
    
        return HTMLString;
    };
    
    // -------------------------------------------------------------------------

    this.updateRowClipStatus = function( aClipStatus )
    {
        var HTMLString = "";
        var maxNumCols = 6;

        ++aClipStatus.itemCount;
        ++aClipStatus.clipItemCount;

        if ( aClipStatus.clipItemCount == ((aClipStatus.rowsPerClip*maxNumCols)-1) )
        {
            HTMLString += "<div class=\"clipchange nobreak\" onclick=\"showRaidClip('"+aClipStatus.prefix+(aClipStatus.currentId+1)+"')\"></div>";
            HTMLString += "</div>";
            HTMLString += "<div class=\"clip\" id=\""+aClipStatus.prefix+(aClipStatus.currentId+1)+"\">";
            HTMLString += "<div class=\"clipchange nobreak\" onclick=\"showRaidClip('"+aClipStatus.prefix+aClipStatus.currentId+"')\"></div>";

            ++aClipStatus.currentId;
            aClipStatus.clipItemCount = 1;
        }

        return HTMLString;
    };

    // -------------------------------------------------------------------------

    this.generateRoleList = function( aRoleId, aColumns, aRequiredSlots )
    {
        var attendedForRole = 0;
        
        this.forEachPlayerWithRole( aRoleId, function(aPlayer) {
            if ( (aPlayer.status == "ok") || (aPlayer.status == "available") )
                ++attendedForRole;
        });

        var HTMLString = "<div class=\"roleList\">";
        HTMLString += "<h2>"+g_RoleNames[g_RoleIdents[aRoleId]]+" <span class=\"attendance_count\">("+attendedForRole+"/"+aRequiredSlots+")</span></h2>";
        HTMLString += "<div class=\"clip\" id=\"role"+aRoleId+"clip0\">";

        var clipStatus = {
            roleId        : aRoleId,
            colsPerClip   : aColumns,
            rowsPerClip   : 9,
            currentId     : 0,
            itemCount     : 0,
            clipItemCount : 0,
            displayCount  : Math.max(this.numPlayersWithRole(aRoleId), aRequiredSlots)
        };

        var self = this;
        var numActive = 0;

        // Display raiding players

        this.forEachPlayerWithRole( aRoleId, function(aPlayer) {
            if ( aPlayer.status == "ok" )
            {
                if ( numActive >= aRequiredSlots )
                    HTMLString += self.generateSpilledSlot( aPlayer, clipStatus );
                else
                    HTMLString += self.generatePlayerSlot( aPlayer, clipStatus );

                ++numActive;
            }
        });
        
        // Add "line break"
        
        var rowIdx = clipStatus.clipItemCount / aColumns;
        var adjust = (rowIdx - parseInt(rowIdx, 10) === 0) ? 0 : 1;
        var newRow = parseInt(rowIdx, 10)+adjust;
        
        if ( newRow == clipStatus.rowsPerClip )
        {
            HTMLString += this.generateNextClipButton(clipStatus);
        }
        else
        {        
            clipStatus.clipItemCount = newRow * aColumns;
            HTMLString += "<div class=\"separator\"></div>";
        }
        
        // Display waiting players

        var waitingAreBenched = (numActive >= aRequiredSlots) ||
                                (!gUser.isRaidlead && (this.mStage == "locked"));

        this.forEachPlayerWithRole( aRoleId, function(aPlayer) {
            if ( aPlayer.status == "available" )
            {
                HTMLString += self.generateWaitSlot( aPlayer, clipStatus, waitingAreBenched );
            }
        });

        // Add a slot to add randoms

        var itemsRemain = aRequiredSlots;

        if ( gUser.isRaidlead )
        {
            var overEmptySlot = clipStatus.itemCount < itemsRemain;
            HTMLString += this.generateAddRandomSlot( clipStatus, overEmptySlot );
        }

        // Display required, empty slots

        while ( clipStatus.itemCount < itemsRemain )
        {
            HTMLString += this.generateEmptySlot( clipStatus );
        }


        HTMLString += "</div>";
        HTMLString += "</div>";

        return HTMLString;
    };

    // -------------------------------------------------------------------------

    this.generateAbsentList = function( aRows )
    {
        var HTMLString = "<div id=\"absentList\">";

        HTMLString += "<h2 style=\"position: relative; width: 800px\">"+L("AbsentPlayers")+"</h2>";
        HTMLString += "<div class=\"clip\" id=\"absentclip0\">";

        var self = this;
        var clipStatus = {
            rowsPerClip   : aRows,
            currentId     : 0,
            itemCount     : 0,
            clipItemCount : 0,
            prefix        : "absentclip"
        };

        // Display absent players

        this.forEachPlayer( function(aPlayer) {
            if ( aPlayer.status == "unavailable" )
            {
                HTMLString += self.generateAbsentSlot( aPlayer, clipStatus );
            }
        });

        HTMLString += "</div>";
        HTMLString += "</div>";

        return HTMLString;
    };

    // -------------------------------------------------------------------------

    this.generateUndecidedList = function( aRows )
    {
        var HTMLString = "<div id=\"undecidedList\">";

        HTMLString += "<h2 style=\"position: relative; width: 800px\">"+L("UndecidedPlayers")+"</h2>";
        HTMLString += "<div class=\"clip\" id=\"undecidedclip0\">";

        var self = this;
        var clipStatus = {
            rowsPerClip   : aRows,
            currentId     : 0,
            itemCount     : 0,
            clipItemCount : 0,
            prefix        : "undecidedclip"
        };

        // Display undecided players

        this.forEachPlayer( function(aPlayer) {
            if ( aPlayer.status == "undecided" )
            {
                HTMLString += self.generateAbsentSlot( aPlayer, clipStatus );
            }
        });

        HTMLString += "</div>";
        HTMLString += "</div>";

        return HTMLString;
    };

    // -----------------------------------------------------------------------------

    this.bindClipPlayer = function( aClipItem )
    {
        var pid = parseInt( aClipItem.attr("id").substr(2), 10 );
        var playerList = this;

        aClipItem.draggable({
            delay          : 100,
            revert         : true,
            revertDuration : 200,
            helper         : "clone",
            start          : function() { playerList.showDropTargets(pid, $(this)); },
            stop           : function() { playerList.hideDropTargets(); }
        });

        aClipItem.children(".editableName").each( function() {
            // Block click events to avoid up-/downgrade
            $(this).click( function(event) { event.stopPropagation(); });

            // Editing the text field starts the "edit mode"
            $(this).focus( function() {
                $(this).prev()
                    .css("background-image", "url(lib/layout/images/remove.png)")
                    .click( function(event) {
                        event.stopPropagation();
                        playerList.removePlayer(pid);
                    });
            });

            // Leaving the text field resets the "edit mode"
            $(this).blur( function() {
                if (playerList.changePlayerName(pid, $(this).val()))
                {
                    onUIDataChange();
                }
                   
                $(this).prev()
                    .css("background-image", "url(images/classessmall/random.png)")
                    .unbind("click");
            });
        });

        makeTouchable(aClipItem);
    };

    // -----------------------------------------------------------------------------

    this.bindClips = function( aParent )
    {
        if ( gUser == null )
            return;

        var clips = aParent.children(".clip");
        var config = aParent.data("config");
        var playerList = this;

        if ( gUser.isRaidlead )
        {
            var numActiveSlots = clips.children(".activeSlot").length + clips.children(".activeSlotComment").length;
            var upgradeAllowed = numActiveSlots < config.reqSlots;

            // Attach event listeners to wait and bench slots
            // For wait slots click upgrades

            clips.children(".waitSlot").each( function() {
                var pid = parseInt( $(this).attr("id").substr(2), 10 );

                if ( playerList.mMode != "all" )
                    $(this).click( function() { 
                        playerList.upgradePlayer(pid); 
                        onUIDataChange();
                    });

                playerList.bindClipPlayer( $(this) );
            });

            // same for benched slots

            clips.children(".benchSlot").each( function() {
                var pid = parseInt( $(this).attr("id").substr(2), 10 );
                playerList.bindClipPlayer( $(this) );
            });

            // Attach event listeners for wait and bench slots with comment.
            // For wait slots 1st click opens comment, 2nd click upgrades

            clips.children(".waitSlotComment").each( function() {
                var pid     = parseInt( $(this).attr("id").substr(2), 10 );
                var pIdx    = playerList.getPlayerIndex(pid);
                var name    = playerList.mPlayers[pIdx].name;
                var comment = playerList.mPlayers[pIdx].comment;
                var image   = "images/classessmall/" + playerList.mPlayers[pIdx].className + ".png";
                var slot    = $(this);
                var icon    = slot.children(".playerIcon");

                var onAbortFunction = function() {
                    icon.css("background-image","url("+image+")");
                    slot.unbind("click").click( onClickFunction );
                };

                var onClickFunction = function(event) {
                    if ( playerList.mMode != "all" )
                    {
                        icon.css("background-image","url(lib/layout/images/move_up.png)");
                        slot.unbind("click").click( function() { 
                            playerList.upgradePlayer(pid); 
                            onUIDataChange();
                        });
                    }

                    showTooltipAttendee( icon, image, name, comment, true, onAbortFunction );
                    event.stopPropagation();
                };

                slot.click( onClickFunction );
                playerList.bindClipPlayer( slot );
            });

            // same for benched slots

            clips.children(".benchSlotComment").each( function() {
                var pid     = parseInt( $(this).attr("id").substr(2), 10 );
                var pIdx    = playerList.getPlayerIndex(pid);
                var name    = playerList.mPlayers[pIdx].name;
                var comment = playerList.mPlayers[pIdx].comment;
                var image   = "images/classessmall/" + playerList.mPlayers[pIdx].className + ".png";
                var slot    = $(this);
                var icon    = slot.children(".playerIcon");

                slot.click( function(event) {
                    showTooltipAttendee( icon, image, name, comment, true, null );
                    event.stopPropagation();
                });

                playerList.bindClipPlayer( slot );
            });

            // attach event listeners for active slots
            // click downgrades

            clips.children(".activeSlot, .spilledSlot").each( function() {
                var pid = parseInt( $(this).attr("id").substr(2), 10 );

                if ( playerList.mMode != "all" )
                    $(this).click( function() { 
                        playerList.downgradePlayer(pid); 
                        onUIDataChange();
                    });

                playerList.bindClipPlayer( $(this) );
            });

            // attach event listeners for active slots with comment.
            // 1st click opens comment, 2nd click downgrades

            clips.children(".activeSlotComment, .spilledSlotComment").each( function() {
                var pid     = parseInt( $(this).attr("id").substr(2), 10 );
                var pIdx    = playerList.getPlayerIndex(pid);
                var name    = playerList.mPlayers[pIdx].name;
                var comment = playerList.mPlayers[pIdx].comment;
                var image   = "images/classessmall/" + playerList.mPlayers[pIdx].className + ".png";
                var slot    = $(this);
                var icon    = slot.children(".playerIcon");

                var onAbortFunction = function() {
                    icon.css("background-image","url("+image+")");
                    slot.unbind("click").click( onClickFunction );
                };

                var onClickFunction = function(event) {
                    if ( playerList.mMode != "all" )
                    {
                        icon.css("background-image","url(lib/layout/images/move_down.png)");
                        slot.unbind("click").click( function() { 
                            playerList.downgradePlayer(pid); 
                            onUIDataChange();
                        });
                    }

                    showTooltipAttendee( icon, image, name, comment, true, onAbortFunction );
                    event.stopPropagation();
                };

                slot.click( onClickFunction );
                playerList.bindClipPlayer( slot );
            });

            // attach event handlers for "add random player" button

            clips.children(".randomSlot").each( function() {
                $(this).click( function() { 
                    playerList.addRandomPlayer(config.id); 
                    onUIDataChange();
                });
            });
        }
        else
        {
            // Regular users are only able to see the tooltips

            clips.children(".activeSlotComment, .spilledSlotComment, .waitSlotComment, .benchSlotComment").each( function() {
                var pid     = parseInt( $(this).attr("id").substr(2), 10 );
                var pIdx    = playerList.getPlayerIndex(pid);
                var name    = playerList.mPlayers[pIdx].name;
                var comment = playerList.mPlayers[pIdx].comment;
                var image   = "images/classessmall/" + playerList.mPlayers[pIdx].className + ".png";
                var slot    = $(this);
                var icon    = slot.children(".playerIcon");

                slot.click( function(event) {
                    showTooltipAttendee( icon, image, name, comment, true, null );
                    event.stopPropagation();
                });
            });
        }
    };

    // -------------------------------------------------------------------------

    this.updateRoleList = function( aRoleIdx )
    {
        var roleList = $( $("#raidsetup").children(".roleList")[aRoleIdx] );
        var roleConf = roleList.data("config");

        roleList.replaceWith( this.generateRoleList(roleConf.id, roleConf.columns, roleConf.reqSlots) );
        roleList = $( $("#raidsetup").children(".roleList")[aRoleIdx] );

        var clipId = parseInt( roleConf.clip.substr(9), 10 );
        var numClips = roleList.children(".clip").length;

        if ( clipId >= numClips )
        {
            roleConf.clip = roleConf.clip.substr(0,9) + (numClips-1);
        }

        roleList.data("config", roleConf);
        $("#"+roleConf.clip).show();

        this.bindClips( roleList );
    };

    // -----------------------------------------------------------------------------

    this.showDropTargets = function( aPlayerId, aSource )
    {
        var playerList = $("#raiddetail").data("players");
        var pIdx       = this.getPlayerIndex( aPlayerId );
        var player     = this.mPlayers[pIdx];
        var roleLists  = $("#raidsetup").children(".roleList");
        var roleIdx    = 0;

        roleLists.each( function() {

            if ( ((player.className == "random") ||
                  (roleIdx == player.firstRole) ||
                  (roleIdx == player.secondRole)) &&
                  (roleIdx != player.activeRole) )
            {
                var currentRoleIdx = roleIdx;
                $(this).droppable({
                    drop: function() {
                        aSource.draggable("option", "revert", false);
                        aSource.draggable("destroy").detach();
                        playerList.movePlayer(aPlayerId, currentRoleIdx);
                        onUIDataChange();
                    }
                });
            }
            else
            {
                $(this).fadeTo(100, 0.15);
            }

            ++roleIdx;
        });
        
        $("#absent_drop").fadeTo(100, 1.0).droppable({
            hoverClass: "absent_hover",
            drop: function() {                
                if ( player.charId > 0 )
                {
                    prompt(L("AbsentMessage"), L("MarkAsAbesent"), L("Cancel"), function(aComment) {
                        aSource.draggable("option", "revert", false);
                        aSource.draggable("destroy").detach();
                        playerList.absentPlayer(aPlayerId, "["+gUser.name+"] "+aComment);                
                    });
                }
                else
                {
                    aSource.draggable("option", "revert", false);
                    aSource.draggable("destroy").detach();
                    
                    playerList.absentPlayer(aPlayerId, "");
                }
            }            
        });
    };

    // -----------------------------------------------------------------------------

    this.hideDropTargets = function()
    {
        $("#raidsetup").children(".roleList")
            .fadeTo(100, 1.0);
            
        $("#raidsetup").children(".ui-droppable")
            .droppable("destroy");
            
        $("#absent_drop").fadeTo(100, 0.0);
    };

    // -------------------------------------------------------------------------

    this.upgradePlayer = function( aPlayerId )
    {
        var pIdx = this.getPlayerIndex( aPlayerId );
        
        this.mPlayers[pIdx].status = "ok";
        this.mPlayers[pIdx].flags |= PlayerFlagModified;
        
        this.updateRoleList( this.mPlayers[pIdx].activeRole );
    };

    // -------------------------------------------------------------------------

    this.downgradePlayer = function( aPlayerId )
    {
        var pIdx = this.getPlayerIndex( aPlayerId );
        var roleIdx = this.mPlayers[pIdx].activeRole;

        this.mPlayers[pIdx].status = "available";
        this.mPlayers[pIdx].flags |= PlayerFlagModified;
        
        this.updateRoleList( roleIdx );
    };

    // -----------------------------------------------------------------------------

    this.movePlayer = function( aPlayerId, aRoleIdx )
    {
        var pIdx     = this.getPlayerIndex( aPlayerId );
        var prevRole = this.mPlayers[pIdx].activeRole;

        this.mPlayers[pIdx].activeRole = aRoleIdx;
        this.mPlayers[pIdx].flags |= PlayerFlagModified;

        if ( this.mMode == "all" )
            this.mPlayers[pIdx].status = "ok";
        else
            this.mPlayers[pIdx].status = "available";

        this.updateRoleList( prevRole );
        this.updateRoleList( aRoleIdx );
    };

    // -------------------------------------------------------------------------

    this.removePlayer = function( aPlayerId )
    {
        var pIdx = this.getPlayerIndex( aPlayerId );
        var role = this.mPlayers[pIdx].activeRole;
        
        this.mPlayers.splice(pIdx,1);

        if ( aPlayerId > 0 )
            this.mRemovedPlayers.push( aPlayerId );            
            
        this.updateRoleList( role );
        onUIDataChange();
    };

    // -------------------------------------------------------------------------

    this.absentPlayer = function( aPlayerId, aComment )
    {
        var pIdx = this.getPlayerIndex( aPlayerId );
        var player = this.mPlayers[pIdx];
        var role = player.activeRole;
        
        if ( player.charId > 0 )
        {
            var character = player.characters[0];
            
            player.name = character.name;        
            player.charId = character.id;
            player.className = character.className;
            player.mainchar = character.mainchar;
            player.activeRole = character.firstRole;
            player.firstRole = character.firstRole;
            player.secondRole = character.secondRole;
            player.flags = PlayerFlagModified | PlayerFlagComment | PlayerFlagCharId;
            
            player.status  = "unavailable";
            player.comment = aComment;
            
            $("#absentList").replaceWith(this.generateAbsentList(3));
            onUpdateAbsentList(this);
        }
        else
        {
            this.mPlayers.splice(pIdx,1);
            this.mRemovedPlayers.push( aPlayerId );
        }
        
        this.updateRoleList( role );
        hideTooltip();
        onUIDataChange();
    };
}

// -----------------------------------------------------------------------------
//  player list functions
// -----------------------------------------------------------------------------

function showRaidClip( aClipId )
{
    var clipToShow = $("#"+aClipId);
    var roleList = clipToShow.parent();
    var roleConf = roleList.data("config");
    roleConf.clip = aClipId;

    roleList.children(".clip").hide();
    clipToShow.show();
}

// -----------------------------------------------------------------------------

function validateRoleCounts()
{
    var totalMembers = 0;
    var maxMembers   = $("#selectsize").val();

    for ( var i=0; i < g_RoleIds.length; ++i )
    {
        var slotElement = $("#slotCount"+i);
        var roleSlots   = parseInt( slotElement.val(), 10 );
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

function generateRaidInfo( aRaidXML, aAppendTo )
{
    var MonthArray = Array(L("January"), L("February"), L("March"), L("April"), L("May"), L("June"), L("July"), L("August"), L("September"), L("October"), L("November"), L("December"));

    var raidImage = aRaidXML.children("image:first").text();
    var raidName  = aRaidXML.children("location:first").text();
    var raidSize  = 0;
    var numRoles  = Math.min( g_RoleNames.length, 5 );

    aRaidXML.children("slots").children("required").each( function() {
       raidSize += parseInt($(this).text(), 10);
    });

    var startDate = aRaidXML.children("startDate:first").text().split("-");
    var endDate   = aRaidXML.children("endDate:first").text().split("-");

    var startTime = aRaidXML.children("start:first").text();
    var endTime   = aRaidXML.children("end:first").text();

    var HTMLString = "<div class=\"raidinfo\">";
    HTMLString += "<img src=\"images/raidbig/" + raidImage + "\" class=\"raidicon\">";
    HTMLString += "<div class=\"raidname\">" + raidName + "</div>";
    HTMLString += "<div class=\"raidsize\">" + raidSize + " " + L("Players") + "</div>";
    HTMLString += "<div class=\"datetime\">" + parseInt(startDate[2], 10) + ". " + MonthArray[startDate[1]-1] + ", ";
    HTMLString += startTime + " - " + endTime + "</div>";
    HTMLString += "</div>";

    $("#"+aAppendTo).prepend(HTMLString);
}

// -----------------------------------------------------------------------------

function generateRaidSetup( aRaidXML )
{
    var playerList = $("#raiddetail").data("players");
    var numRoles   = Math.min( g_RoleNames.length, 5 );
    var roleCounts = aRaidXML.children("slots:first");

    var HTMLString = "<div id=\"raidsetup\"></div>";
    $("#raiddetail").append(HTMLString);

    for ( var i=0; i<numRoles; ++i )
    {
        var numCols  = g_RoleColumnCount[i];
        var required = parseInt(roleCounts.children("required").eq(i).text(), 10);

        HTMLString = playerList.generateRoleList(i, numCols, required);
        $("#raidsetup").append(HTMLString);
        $("#raidsetup").children(":last").data("config", {
            id       : i,
            columns  : numCols,
            reqSlots : required,
            clip     : "role"+i+"clip0"
        });
    }
    
    $("#raidsetup").children(".roleList").each( function() {
        $(this).children(".clip:first").show();
        playerList.bindClips( $(this) );
    });

    generateRaidInfo( aRaidXML, "raidsetup" );

    if (gUser.isAdmin)
        $("#raidsetup").prepend("<div id=\"absent_drop\">"+L("MakeAbsent")+"</div>");
}

// -----------------------------------------------------------------------------

function onClickAbsentSlot(aEvent, aElement, aPlayer, aUndecided)
{
    var charIdx = aElement.data("setup_info");
                        
    if (charIdx != null)
    {
        var character = aPlayer.characters[charIdx];
        var hasUserId = aPlayer.comment !== "";
        
        aPlayer.status = "available";
        
        aPlayer.name = character.name;
        aPlayer.charId = character.id;
        aPlayer.className = character.className;
        aPlayer.mainchar = character.mainchar;
        aPlayer.activeRole = character.firstRole;
        aPlayer.firstRole = character.firstRole;
        aPlayer.secondRole = character.secondRole;
        aPlayer.comment = L("SetupBy") + gUser.name;
        aPlayer.flags |= PlayerFlagModified | PlayerFlagComment | PlayerFlagCharId;
        
        if ( !aPlayer.hasRecord )
            aPlayer.flags |= PlayerFlagNew;
        
        if ( !hasUserId )
            aPlayer.flags |= PlayerFlagUserId;
        
        // Update lists
        
        var playerList = $("#raiddetail").data("players");
        
        if ( aUndecided )
        {
            $("#undecidedList").replaceWith(playerList.generateUndecidedList(3));
            onUpdateUndecidedList(playerList);
        }
        else
        {
            $("#absentList").replaceWith(playerList.generateAbsentList(3));
            onUpdateAbsentList(playerList);        
        }
        
        playerList.updateRoleList( aPlayer.activeRole );
        
        onUIDataChange();
        hideTooltip();
    }
    else
    {        
        aElement.css("background-image", "url(lib/layout/images/move_up.png)");
        aElement.data("setup_info", 0);
        
        showTooltipSlackers( aElement, aPlayer, false, true );
        
        $("#tooltip").data("onHide", function() {
            aElement.siblings(".playerName:first").empty().append( aPlayer.name );
            aElement.css("background-image", "url(images/classessmall/"+aPlayer.className+".png)");
            aElement.data("setup_info", null);
        });
    }
        
    aEvent.stopPropagation();
}

// -----------------------------------------------------------------------------

function onUpdateAbsentList(aPlayerList)
{
    $("#absentList").data("config", {
        clip : "absentclip0"
    });

    $("#absentList").children(".clip").children(".benchSlotComment, .benchSlot").each( function() {
        var pid     = parseInt( $(this).attr("id").substr(2), 10 );
        var pIdx    = aPlayerList.getPlayerIndex(pid);
        var player  = aPlayerList.mPlayers[pIdx];
        var element = $(this).children(".playerIcon");

        if (gUser.isAdmin)
        {
            $(this).click( function(event) {
                onClickAbsentSlot(event, element, player, false);
            });
        }
        else
        {
            element.mouseover( function() {
                showTooltipSlackers( element, player, false, false );
            });
        }
    });

    $("#absentList").children(".clip:first").show();
}

// -----------------------------------------------------------------------------

function onUpdateUndecidedList(aPlayerList)
{    
    $("#undecidedList").data("config", {
        clip : "undecidedclip0"
    });

    $("#undecidedList").children(".clip").children(".benchSlotComment, .benchSlot").each( function() {
        var pid     = parseInt( $(this).attr("id").substr(2), 10 );
        var pIdx    = aPlayerList.getPlayerIndex(pid);
        var player  = aPlayerList.mPlayers[pIdx];
        var element = $(this).children(".playerIcon");

        if (gUser.isAdmin)
        {
            $(this).click( function(event) {
                onClickAbsentSlot(event, element, player, true);
            });
        }
        else
        {
            element.mouseover( function() {
                showTooltipSlackers( element, player, true, false );
            });
        }
    });
    
    $("#undecidedList").children(".clip:first").show();
}

// -----------------------------------------------------------------------------

function generateRaidSlackers( aRaidXML )
{
    var playerList = $("#raiddetail").data("players");

    var HTMLString = "<div id=\"slackers\">";
    HTMLString += "<div class=\"slackerspanel\"></div>";
    HTMLString += "</div>";

    $("#raiddetail").append(HTMLString);

    HTMLString = playerList.generateAbsentList(3);
    $(".slackerspanel:first").append(HTMLString);

    HTMLString = playerList.generateUndecidedList(3);
    $(".slackerspanel:first").append(HTMLString);
    
    onUpdateAbsentList(playerList);
    onUpdateUndecidedList(playerList);

    generateRaidInfo( aRaidXML, "slackers" );
}

// -----------------------------------------------------------------------------

function generateRaidSettings( aMessageXML, aRaidXML )
{
    var HTMLString = "<div id=\"raidoptions\">";
    HTMLString += "<div class=\"settingspanel\"></div>";
    HTMLString += "</div>";

    $("#raiddetail").append(HTMLString);

    var panel = $("#raidoptions").children(".settingspanel:first");

    var Locations       = aMessageXML.children("locations");
    var LocationInfos   = Locations.children("location");
    var LocationImages  = Locations.children("locationimage");

    var raidImage       = aRaidXML.children("image:first").text();
    var raidName        = aRaidXML.children("location:first").text();
    var raidLocation    = aRaidXML.children("locationId:first").text();
    var raidSize        = parseInt(aRaidXML.children("size:first").text(),10);
    var raidComment     = aRaidXML.children("description:first").text();
    var raidSlots       = aRaidXML.children("slots").children("required");
    var raidStatus      = aRaidXML.children("stage:first").text();
    var raidMode        = aRaidXML.children("mode:first").text();

    var raidStart       = aRaidXML.children("start:first").text();
    var raidStartHour   = parseInt(raidStart.substr(0,raidStart.indexOf(":")), 10);
    var raidStartMinute = parseInt(raidStart.substr(raidStart.indexOf(":")+1), 10);

    var raidEnd         = aRaidXML.children("end:first").text();
    var raidEndHour     = parseInt(raidEnd.substr(0,raidEnd.indexOf(":")), 10);
    var raidEndMinute   = parseInt(raidEnd.substr(raidEnd.indexOf(":")+1), 10);
    var raidDate        = aRaidXML.children("startDate:first").text();

    $("#raidoptions").data("info", {
        id    : parseInt(aRaidXML.children("raidId:first").text(), 10),
        year  : parseInt(raidDate.substr(0,4), 10),
        month : parseInt(raidDate.substr(5,2), 10),
        day   : parseInt(raidDate.substr(8,2), 10),
        size  : parseInt(raidSize, 10)
    });
    
    // Create general raid settings from the sheet
    
    HTMLString  = "<span style=\"display: inline-block; margin-right: 5px; float: left\" class=\"imagepicker\" id=\"locationimagepicker\"><div class=\"imagelist\" id=\"locationimagelist\"></div></span>";
    HTMLString += "<span style=\"display: inline-block; vertical-align: top\">";
    
    HTMLString += "<div style=\"margin-bottom: 10px\">";
    HTMLString += "<select id=\"selectlocation\" onchange=\"onLocationChange(this)\">";
    HTMLString += "<option value=\"0\">"+L("NewDungeon")+"</option>";
    HTMLString += "</select>";
    
    HTMLString += "<span style=\"display: inline-block; width: 3px;\"></span>";
    HTMLString += "<select id=\"selectsize\" style=\"width: 48px\">";
    
    for (var i=0; i<g_GroupSizes.length; ++i)
    {
        HTMLString += "<option value=\""+g_GroupSizes[i]+"\">"+g_GroupSizes[i]+"</option>";
    }
    
    HTMLString += "</select>";                    
    HTMLString += "</div>";
    
    HTMLString += "<div>";
    HTMLString += "<select id=\"starthour\">";
    HTMLString += "</select>";
    
    HTMLString += "<span style=\"display: inline-block; width: 10px; text-align:center; position: relative; top: -5px\">:</span>";
    HTMLString += "<select id=\"startminute\" style=\"width: 48px\">";
    HTMLString += "<option value=\"0\">00</option>";
    HTMLString += "<option value=\"15\">15</option>";
    HTMLString += "<option value=\"30\">30</option>";
    HTMLString += "<option value=\"45\">45</option>";
    HTMLString += "</select>";
    
    HTMLString += "<span style=\"display: inline-block; width: 20px; text-align:center; position: relative; top: -5px\">"+L("to")+"</span>";
    HTMLString += "<select id=\"endhour\">";
    HTMLString += "</select>";
    
    HTMLString += "<span style=\"display: inline-block; width: 10px; text-align:center; position: relative; top: -5px\">:</span>";
    HTMLString += "<select id=\"endminute\" style=\"width: 48px\">";
    HTMLString += "<option value=\"0\">00</option>";
    HTMLString += "<option value=\"15\">15</option>";
    HTMLString += "<option value=\"30\">30</option>";
    HTMLString += "<option value=\"45\">45</option>";
    HTMLString += "</select>";
    
    HTMLString += "</div>";        
    HTMLString += "</span>";
    
    panel.append( HTMLString );
    
    // configure raid settings
    
    HTMLString = "";

    for ( i=4; i>=0; --i )
        HTMLString += "<option value=\"" + i + "\">" + formatHourPrefixed(i) + "</option>";

    for ( i=23; i>4; --i )
        HTMLString += "<option value=\"" + i + "\">" + formatHourPrefixed(i) + "</option>";

    var HourFieldWidth        = (g_TimeFormat == 24) ? 48 : 64;
    var LocationFieldWidth    = (g_TimeFormat == 24) ? 181 : 213;
    var DescriptionFieldWidth = (g_TimeFormat == 24) ? 295 : 327;
    var SheetOverlayWidth     = (g_TimeFormat == 24) ? 540 : 570;

    $("#starthour")
        .css("width", HourFieldWidth)
        .empty().append(HTMLString);

    $("#endhour")
        .css("width", HourFieldWidth)
        .empty().append(HTMLString);

    $("#selectlocation")
        .css("width", LocationFieldWidth);

    $("#descriptiondummy")
        .css("width", DescriptionFieldWidth)
        .css("max-width", DescriptionFieldWidth);

    $("#description")
        .css("width", DescriptionFieldWidth)
        .css("max-width", DescriptionFieldWidth);

    $("#selectsize").change( validateRoleCounts );

    // Generate location image list

    HTMLString = "";
    var numImages = 1;
    var imageList = [];

    LocationImages.each( function(index) {
        if ( numImages % 11 === 0 )
        {
            HTMLString += "<br/>";
            ++numImages;
        }

        HTMLString += "<img src=\"images/raidsmall/" + $(this).text() + "\" onclick=\"applyLocationImage(this, true)\" style=\"width:32px; height:32px; margin-right:5px;\"/>";
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

    HTMLString  = "<br/><div style=\"float:left; margin-top: 15px\">";
    HTMLString += "<div><div class=\"settingLabel\">"+L("Comment")+"</div>";
    HTMLString += "<div class=\"settingField\"><textarea id=\"comment\" class=\"settingEdit\" style=\"width:235px; height:64px\">"+raidComment+"</textarea></div></div>";

    HTMLString += "<div><div class=\"settingLabel\">"+L("RaidStatus")+"</div>";
    HTMLString += "<div class=\"settingField\"><select id=\"raidstage\" style=\"width: 190px\">";
    HTMLString += "<option value=\"open\""+((raidStatus=="open") ? " selected" : "")+">"+L("RaidOpen")+"</option>";
    HTMLString += "<option value=\"locked\""+((raidStatus=="locked") ? " selected" : "")+">"+L("RaidLocked")+"</option>";
    HTMLString += "<option value=\"canceled\""+((raidStatus=="canceled") ? " selected" : "")+">"+L("RaidCanceled")+"</option>";
    HTMLString += "</select></div></div>";

    HTMLString += "<div><div class=\"settingLabel\">"+L("RaidSetupStyle")+"</div>";
    HTMLString += "<div class=\"settingField\"><select id=\"raidmode\" style=\"width: 190px\">";
    HTMLString += "<option value=\"manual\""+((raidMode=="manual") ? " selected" : "")+">"+L("RaidModeManual")+"</option>";
    HTMLString += "<option value=\"attend\""+((raidMode=="attend") ? " selected" : "")+">"+L("RaidModeAttend")+"</option>";
    HTMLString += "<option value=\"all\""+((raidMode=="all") ? " selected" : "")+">"+L("RaidModeAll")+"</option>";
    HTMLString += "</select></div></div>";

    raidSlots.each( function(index) {
        if ( index < g_RoleIds.length )
        {
            HTMLString += "<div><div class=\"settingLabel\">"+L("RequiredForRole")+" \""+g_RoleNames[g_RoleIdents[index]]+"\"</div>";
            HTMLString += "<div class=\"settingField\"><input id=\"slotCount"+index+"\" class=\"settingEdit\" style=\"width:24px;\" value=\""+$(this).text()+"\" type=\"text\"/></div></div>";
        }
    });

    HTMLString += "<div><div class=\"settingLabel\">&nbsp;</div>";
    HTMLString += "<div class=\"settingField\"><button id=\"deleteRaid\">&nbsp;"+L("DeleteRaid")+"</button></div></div>";


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

    $("#selectlocation").combobox({ editable: true });
    $("#selectsize").combobox();
    $("#starthour").combobox();
    $("#startminute").combobox();
    $("#endhour").combobox();
    $("#endminute").combobox();
    $("#deleteRaid").button({ icons: { primary: "ui-icon-circle-close" }})
        .click( function() { triggerRaidDelete(); } )
        .css( "font-size", 11 );

    // Event binding

    $("#locationimagepicker")
        .data("imageNames", imageList )
        .data( "selectedImage", raidImage );        
        
    $("#selectlocation").change( function() {        
        $("#locationimagepicker").unbind("click");        
        if ( $("#selectlocation").context.selectedIndex > 0 )
        {
            $("#locationimagepicker").click( function(event) {
                showTooltipRaidImageList();
                event.stopPropagation();
            });
        }
    });
        
    // Data change bindings
    
    $("#raidoptions select").change( onUIDataChange );
    $("#raidoptions textarea").change( onUIDataChange );
    
    raidSlots.each( function(index) {
        $("#slotCount"+index).change( function() {
            validateRoleCounts();
            onUIDataChange();
        });
    });
}

// -----------------------------------------------------------------------------

function generateRaid( aXMLData )
{
    hideTooltip();
    closeSheet();

    if ( gUser == null )
        return;

    var PlayerList = new CRaidMemberList();
    var HTMLString = "<div id=\"raiddetail\">";

    HTMLString += "<div id=\"tablist\" class=\"tabs setup\">";
    HTMLString += "<div style=\"margin-top: 16px\">";
    HTMLString += "<div id=\"setuptoggle\" class=\"tab_icon icon_setup\"></div>";
    HTMLString += "<div id=\"slackerstoggle\" class=\"tab_icon icon_slackers_off\"></div>";

    if ( gUser.isRaidlead)
    {
        HTMLString += "<div id=\"settingstoggle\" class=\"tab_icon icon_settings_off\"></div>";
    }

    HTMLString += "</div></div>";
    
    if ( gUser.isRaidlead )
        HTMLString += "<button id=\"applyButton\" class=\"apply_changes\" disabled=\"disabled\">" + L("Apply") + "</button>";
    HTMLString += "</div>";

    $("#body").empty().append(HTMLString);

    $("#raiddetail").hide();

    var Message = $(aXMLData).children("messagehub:first");
    var Raid    = Message.children("raid:first");
    var RaidId  = parseInt(Raid.children("raidId").text(),10);

    PlayerList.mStage = Raid.children("stage:first").text();
    PlayerList.mMode  = Raid.children("mode:first").text();

    $("#raiddetail").data("players",PlayerList);
    Raid.children("attendee").each(function() {
        PlayerList.addPlayer($(this));
    });

    generateRaidSetup( Raid );
    generateRaidSlackers( Raid );

    if ( gUser.isRaidlead)
    {
        generateRaidSettings( Message, Raid );
    }

    // Setup toplevel UI

    $("#setuptoggle").click( function() {
        changeContext( "raid,setup,"+RaidId );
    });

    $("#slackerstoggle").click( function() {
        changeContext( "raid,slackers,"+RaidId );
    });

    if ( gUser.isRaidlead)
    {
        $("#settingstoggle").click( function() {
            changeContext( "raid,settings,"+RaidId );
        });

        $("#applyButton").button({ icons: { secondary: "ui-icon-disk" }})
            .click( function() { triggerRaidUpdate(); } )
            .css( "font-size", 11 );
    }

    loadRaidPanel( Message.children("show").text(), RaidId );

    $("#raiddetail").show();
}

// -----------------------------------------------------------------------------

function showRaidPanel( aPanel, aSection )
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

    $(aPanel).show();
    $("#tablist").addClass(aSection);
    $("#"+aSection+"toggle").removeClass("icon_"+aSection+"_off");
    $("#"+aSection+"toggle").addClass("icon_"+aSection);

    $("#raidoptions").data("activesection", aSection );
}

// -----------------------------------------------------------------------------
//  Callbacks
// -----------------------------------------------------------------------------

function loadRaid( aRaidId, aPanelName )
{
    reloadUser();

    if ( gUser == null )
        return;

    $("#body").empty();

    var Parameters = {
        id : aRaidId,
        showPanel : aPanelName
    };

    asyncQuery( "raid_detail", Parameters, generateRaid );

}

// -----------------------------------------------------------------------------

function loadRaidPanel( aName, aRaidId )
{
    if ( gUser == null )
        return;

    if ( $("#raiddetail").length === 0 )
    {
        loadRaid( aRaidId, aName );
    }
    else
    {
        switch( aName )
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

function triggerRaidDelete()
{
    if ( (gUser == null) || !gUser.isRaidlead )
        return;

    confirm( L("ConfirmRaidDelete"), L("DeleteRaid"), L("Cancel"),
        function() {
            var Parameters = {
                id : $("#raidoptions").data("info").id
            };
            
            onAppliedUIDataChange();
            asyncQuery( "raid_delete", Parameters, generateCalendar );            
        });
}

// -----------------------------------------------------------------------------

function triggerRaidUpdate()
{
    if ( (gUser == null) || !gUser.isRaidlead )
        return;

    var hash = window.location.hash.substring( 1, window.location.hash.length );
    var playerList = $("#raiddetail").data("players");
    var slotCount  = new Array(0,0,0,0,0);

    var role1players = [];
    var role2players = [];
    var role3players = [];
    var role4players = [];
    var role5players = [];

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

    playerList.getModifiedPlayersForRole(0, role1players);
    playerList.getModifiedPlayersForRole(1, role2players);
    playerList.getModifiedPlayersForRole(2, role3players);
    playerList.getModifiedPlayersForRole(3, role4players);
    playerList.getModifiedPlayersForRole(4, role5players); 

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
        removed      : playerList.mRemovedPlayers,
        showPanel    : $("#raidoptions").data("activesection")
    };

    onAppliedUIDataChange();
    asyncQuery( "raid_update", Parameters, generateRaid );
}