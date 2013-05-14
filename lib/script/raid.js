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

    this.addPlayer = function( a_PlayerXML )
    {
        var newPlayer = {
            id         : parseInt(a_PlayerXML.children("id:first").text(), 10),
            userId     : parseInt(a_PlayerXML.children("userId:first").text(), 10),
            timestamp  : parseInt(a_PlayerXML.children("timestamp:first").text(), 10),
            hasRecord  : a_PlayerXML.children("hasId:first").text() == "true",
            charId     : parseInt(a_PlayerXML.children("charid:first").text(), 10),
            name       : a_PlayerXML.children("name:first").text(),
            className  : a_PlayerXML.children("class:first").text(),
            mainchar   : a_PlayerXML.children("mainchar:first").text() == "true",
            activeRole : parseInt( a_PlayerXML.children("role:first").text(), 10 ),
            firstRole  : parseInt( a_PlayerXML.children("role1:first").text(), 10 ),
            secondRole : parseInt( a_PlayerXML.children("role2:first").text(), 10 ),
            status     : a_PlayerXML.children("status:first").text(),
            comment    : a_PlayerXML.children("comment:first").text(),
            flags      : PlayerFlagNone,
            characters : Array()
        };

        a_PlayerXML.children("chars:first").children("character").each( function() {
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

    this.addRandomPlayer = function( a_RoleIdx )
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
            activeRole : a_RoleIdx,
            firstRole  : a_RoleIdx,
            secondRole : a_RoleIdx,
            status     : (this.mMode == "all") ? "ok" : "available",
            comment    : "",
            flags      : PlayerFlagModified | PlayerFlagNew | PlayerFlagName,
            characters : Array()
        };

        // New random players have a negative id so we can insert them as new
        // players. Existing players have regular ids

        --this.mNextRandomId;
        this.mPlayers.push(newPlayer);
        this.updateRoleList( a_RoleIdx );
    };

    // -------------------------------------------------------------------------

    this.forEachPlayer = function( a_Callback )
    {
        for ( var pIdx=0; pIdx<this.mPlayers.length; ++pIdx )
        {
            a_Callback( this.mPlayers[pIdx] );
        }
    };

    // -------------------------------------------------------------------------

    this.forEachPlayerWithRole = function( a_Role, a_Callback )
    {
        for ( var pIdx=0; pIdx<this.mPlayers.length; ++pIdx )
        {
            if ( this.mPlayers[pIdx].activeRole == a_Role )
            {
                a_Callback( this.mPlayers[pIdx] );
            }
        }
    };

    // -------------------------------------------------------------------------

    this.numPlayersWithRole = function( a_Role )
    {
        var count = 0;
        for ( var pIdx=0; pIdx<this.mPlayers.length; ++pIdx )
        {
            if ( this.mPlayers[pIdx].activeRole == a_Role )
            {
                ++count;
            }
        }

        return count;
    };

    // -------------------------------------------------------------------------

    this.getPlayerIndex = function( a_PlayerId )
    {
        for ( var pIdx=0; pIdx<this.mPlayers.length; ++pIdx )
        {
            if ( this.mPlayers[pIdx].id == a_PlayerId )
            {
                return pIdx;
            }
        }

        return -1;
    };

    // -------------------------------------------------------------------------

    this.getModifiedPlayersForRole = function( a_RoleId, a_Array )
    {
        // Returns an array with the following elements. Elements >= 4 depend
        // on the flags value.
        
        this.forEachPlayerWithRole( a_RoleId, function( a_Player ) {
            
            if ( (a_Player.flags & PlayerFlagModified) !== 0 )
            {
                a_Array.push( a_Player.id );
                a_Array.push( a_Player.status );
                a_Array.push( a_Player.timestamp );
                a_Array.push( a_Player.flags );
    
                if ( (a_Player.flags & PlayerFlagCharId) !== 0 )
                    a_Array.push( a_Player.charId );
                    
                if ( (a_Player.flags & PlayerFlagUserId) !== 0 )
                    a_Array.push( a_Player.userId );
                
                if ( (a_Player.flags & PlayerFlagName) !== 0 )
                    a_Array.push( a_Player.name );
                    
                if ( (a_Player.flags & PlayerFlagComment) !== 0 )
                    a_Array.push( a_Player.comment );
            }
        });
    };

    // -------------------------------------------------------------------------

    this.changePlayerName = function( a_PlayerId, a_Name )
    {
        var pIdx = this.getPlayerIndex( a_PlayerId );
        
        if (a_Name != this.mPlayers[pIdx].name)
        {
            this.mPlayers[pIdx].name = a_Name;            
            this.mPlayers[pIdx].flags |= PlayerFlagModified | PlayerFlagName;
            
            return true;
        }
        
        return false;
    };

    // -------------------------------------------------------------------------

    this.generatePlayerSlot = function( a_Player, a_ClipStatus )
    {
        var HTMLString  = "";
        var layoutClass = ((a_ClipStatus.clipItemCount % a_ClipStatus.colsPerClip) === 0) ? "break" : "nobreak";
        var bgClass = (a_Player.comment === "") ? "activeSlot" : "activeSlotComment";

        if ( gUser.isRaidlead )
        {
            if (this.mMode != "all")
                bgClass += " clickable";
            bgClass += " dragable";
        }
        
        HTMLString += "<div class=\""+bgClass+" "+layoutClass+"\" id=\"sp"+a_Player.id+"\">";
        HTMLString += "<div class=\"playerIcon\" style=\"background-image: url(images/classessmall/"+a_Player.className+".png)\">";
        HTMLString += "<div class=\"slotMarker\"></div>";
        
        if ( a_Player.mainchar )
            HTMLString += "<div class=\"mainbadge\"></div>";

        HTMLString += "</div>";

        if ( gUser.isRaidlead && (a_Player.className == "random") )
            HTMLString += "<input class=\"editableName\" type=\"test\" value=\"" + a_Player.name + "\"/>";
        else
            HTMLString += "<div class=\"playerName\">" + a_Player.name + "</div>";

        HTMLString += "</div>";
        HTMLString += this.updateClipStatus( a_ClipStatus );

        return HTMLString;
    };

    // -------------------------------------------------------------------------

    this.generateSpilledSlot = function( a_Player, a_ClipStatus )
    {
        var HTMLString  = "";
        var layoutClass = ((a_ClipStatus.clipItemCount % a_ClipStatus.colsPerClip) === 0) ? "break" : "nobreak";
        var bgClass = (a_Player.comment === "") ? "spilledSlot" : "spilledSlotComment";

        if ( gUser.isRaidlead )
            bgClass += " dragable";
            
        HTMLString += "<div class=\""+bgClass+" "+layoutClass+"\" id=\"sp"+a_Player.id+"\">";
        HTMLString += "<div class=\"playerIcon\" style=\"background-image: url(images/classessmall/"+a_Player.className+".png)\">";
        HTMLString += "<div class=\"slotMarker\"></div>";
        
        if ( a_Player.mainchar )
            HTMLString += "<div class=\"mainbadge\"></div>";

        HTMLString += "</div>";

        if ( gUser.isRaidlead && (a_Player.className == "random") )
            HTMLString += "<input class=\"editableName\" type=\"test\" value=\"" + a_Player.name + "\"/>";
        else
            HTMLString += "<div class=\"playerName\">" + a_Player.name + "</div>";

        HTMLString += "</div>";
        HTMLString += this.updateClipStatus( a_ClipStatus );

        return HTMLString;
    };

    // -------------------------------------------------------------------------

    this.generateWaitSlot = function( a_Player, a_ClipStatus, a_Benched )
    {
        var HTMLString = "";
        var layoutClass = ((a_ClipStatus.clipItemCount % a_ClipStatus.colsPerClip) === 0) ? "break" : "nobreak";
        var bgClassBase = (a_Benched) ? "benchSlot" : "waitSlot";
        var bgClass = (a_Player.comment === "") ? bgClassBase : bgClassBase+"Comment";

        if ( gUser.isRaidlead )
        {
            bgClass += " dragable";
            if (!a_Benched)
                bgClass += " clickable";
        }
            
        HTMLString += "<div class=\""+bgClass+" "+layoutClass+"\" id=\"sp"+a_Player.id+"\">";
        HTMLString += "<div class=\"playerIcon\" style=\"background-image: url(images/classessmall/"+a_Player.className+".png)\">";

        if ( a_Player.mainchar )
            HTMLString += "<div class=\"mainbadge\"></div>";

        HTMLString += "</div>";

        if ( gUser.isRaidlead && (a_Player.className == "random") )
            HTMLString += "<input class=\"editableName\" type=\"test\" value=\"" + a_Player.name + "\"/>";
        else
            HTMLString += "<div class=\"playerName\">" + a_Player.name + "</div>";

        HTMLString += "</div>";
        HTMLString += this.updateClipStatus( a_ClipStatus );

        return HTMLString;
    };

    // -------------------------------------------------------------------------

    this.generateEmptySlot = function( a_ClipStatus )
    {
        var HTMLString = "";
        var layoutClass = ((a_ClipStatus.clipItemCount % a_ClipStatus.colsPerClip) === 0) ? "break" : "nobreak";

        HTMLString += "<div class=\"emptySlot "+layoutClass+"\" style=\"background-image: url("+g_RoleImages[a_ClipStatus.roleId]+")\">";
        HTMLString += "</div>";

        HTMLString += this.updateClipStatus( a_ClipStatus );
        return HTMLString;
    };

    // -------------------------------------------------------------------------

    this.generateAddRandomSlot = function( a_ClipStatus, a_OverEmptySlot )
    {
        var HTMLString = "";
        var layoutClass = ((a_ClipStatus.clipItemCount % a_ClipStatus.colsPerClip) === 0) ? "break" : "nobreak";

        if (a_OverEmptySlot)
        {
            var roleImage = g_RoleImages[a_ClipStatus.roleId];
            var roleRndImage = roleImage.substr(0,roleImage.lastIndexOf(".")) + "_rnd" + roleImage.substr(roleImage.lastIndexOf("."));
            
            HTMLString += "<div class=\"randomSlot clickable "+layoutClass+"\" style=\"background-image: url("+roleRndImage+")\">";
            HTMLString += "</div>";
        }
        else
        {
            HTMLString += "<div class=\"randomSlot clickable "+layoutClass+"\">";
            HTMLString += "</div>";
        }
        
        HTMLString += this.updateClipStatus( a_ClipStatus );
        return HTMLString;
    };

    // -------------------------------------------------------------------------

    this.generateAbsentSlot = function( a_Player, a_ClipStatus )
    {
        var HTMLString  = "";
        var bgClass = (a_Player.comment === "") ? "benchSlot" : "benchSlotComment";

        HTMLString += "<div class=\""+bgClass+" clickable2 nobreak\" id=\"ap"+a_Player.id+"\">";
        HTMLString += "<div class=\"playerIcon\" id=\"ap_icon"+a_Player.id+"\" style=\"background-image: url(images/classessmall/"+a_Player.className+".png)\">";
        HTMLString += "</div>";
        HTMLString += "<div class=\"playerName\">" + a_Player.name + "</div>";

        HTMLString += "</div>";
        HTMLString += this.updateRowClipStatus( a_ClipStatus );

        return HTMLString;
    };

    // -------------------------------------------------------------------------

    this.updateClipStatus = function( a_ClipStatus )
    {
        var HTMLString = "";
        
        ++a_ClipStatus.itemCount;
        ++a_ClipStatus.clipItemCount;

        if ( a_ClipStatus.itemCount < a_ClipStatus.displayCount )
        {
            if ( a_ClipStatus.clipItemCount == ((a_ClipStatus.colsPerClip * a_ClipStatus.rowsPerClip)-1) )
            {
                HTMLString += this.generateNextClipButton(a_ClipStatus);
            }
        }

        return HTMLString;
    };
    
    // -------------------------------------------------------------------------
    
    this.generateNextClipButton = function( a_ClipStatus )
    {
        var HTMLString = "";
        var layoutClass = (a_ClipStatus.colsPerClip == 1) ? "break" : "nobreak";

        HTMLString += "<div class=\"clipchange clickable "+layoutClass+"\" onclick=\"showRaidClip('role"+a_ClipStatus.roleId+"clip"+(a_ClipStatus.currentId+1)+"')\"></div>";
        HTMLString += "</div>";
        HTMLString += "<div class=\"clip\" id=\"role"+a_ClipStatus.roleId+"clip"+(a_ClipStatus.currentId+1)+"\">";
        HTMLString += "<div class=\"clipchange clickable break\" onclick=\"showRaidClip('role"+a_ClipStatus.roleId+"clip"+a_ClipStatus.currentId+"')\"></div>";

        ++a_ClipStatus.currentId;
        a_ClipStatus.clipItemCount = 1;
    
        return HTMLString;
    };
    
    // -------------------------------------------------------------------------

    this.updateRowClipStatus = function( a_ClipStatus )
    {
        var HTMLString = "";
        var maxNumCols = 6;

        ++a_ClipStatus.itemCount;
        ++a_ClipStatus.clipItemCount;

        if ( a_ClipStatus.clipItemCount == ((a_ClipStatus.rowsPerClip*maxNumCols)-1) )
        {
            HTMLString += "<div class=\"clipchange nobreak\" onclick=\"showRaidClip('"+a_ClipStatus.prefix+(a_ClipStatus.currentId+1)+"')\"></div>";
            HTMLString += "</div>";
            HTMLString += "<div class=\"clip\" id=\""+a_ClipStatus.prefix+(a_ClipStatus.currentId+1)+"\">";
            HTMLString += "<div class=\"clipchange nobreak\" onclick=\"showRaidClip('"+a_ClipStatus.prefix+a_ClipStatus.currentId+"')\"></div>";

            ++a_ClipStatus.currentId;
            a_ClipStatus.clipItemCount = 1;
        }

        return HTMLString;
    };

    // -------------------------------------------------------------------------

    this.generateRoleList = function( a_RoleId, a_Columns, a_RequiredSlots )
    {
        var attendedForRole = 0;
        
        this.forEachPlayerWithRole( a_RoleId, function(a_Player) {
            if ( (a_Player.status == "ok") || (a_Player.status == "available") )
                ++attendedForRole;
        });

        var HTMLString = "<div class=\"roleList\">";
        HTMLString += "<h2>"+g_RoleNames[g_RoleIdents[a_RoleId]]+" <span class=\"attendance_count\">("+attendedForRole+"/"+a_RequiredSlots+")</span></h2>";
        HTMLString += "<div class=\"clip\" id=\"role"+a_RoleId+"clip0\">";

        var clipStatus = {
            roleId        : a_RoleId,
            colsPerClip   : a_Columns,
            rowsPerClip   : 9,
            currentId     : 0,
            itemCount     : 0,
            clipItemCount : 0,
            displayCount  : Math.max(this.numPlayersWithRole(a_RoleId), a_RequiredSlots)
        };

        var self = this;
        var numActive = 0;

        // Display raiding players

        this.forEachPlayerWithRole( a_RoleId, function(a_Player) {
            if ( a_Player.status == "ok" )
            {
                if ( numActive >= a_RequiredSlots )
                    HTMLString += self.generateSpilledSlot( a_Player, clipStatus );
                else
                    HTMLString += self.generatePlayerSlot( a_Player, clipStatus );

                ++numActive;
            }
        });
        
        // Add "line break"
        
        var rowIdx = clipStatus.clipItemCount / a_Columns;
        var adjust = (rowIdx - parseInt(rowIdx, 10) === 0) ? 0 : 1;
        var newRow = parseInt(rowIdx, 10)+adjust;
        
        if ( newRow == clipStatus.rowsPerClip )
        {
            HTMLString += this.generateNextClipButton(clipStatus);
        }
        else
        {        
            clipStatus.clipItemCount = newRow * a_Columns;
            HTMLString += "<div class=\"separator\"></div>";
        }
        
        // Display waiting players

        var waitingAreBenched = (numActive >= a_RequiredSlots) ||
                                (!gUser.isRaidlead && (this.mStage == "locked"));

        this.forEachPlayerWithRole( a_RoleId, function(a_Player) {
            if ( a_Player.status == "available" )
            {
                HTMLString += self.generateWaitSlot( a_Player, clipStatus, waitingAreBenched );
            }
        });

        // Add a slot to add randoms

        var itemsRemain = a_RequiredSlots;

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

    this.generateAbsentList = function( a_Rows )
    {
        var HTMLString = "<div id=\"absentList\">";

        HTMLString += "<h2 style=\"position: relative; width: 800px\">"+L("AbsentPlayers")+"</h2>";
        HTMLString += "<div class=\"clip\" id=\"absentclip0\">";

        var self = this;
        var clipStatus = {
            rowsPerClip   : a_Rows,
            currentId     : 0,
            itemCount     : 0,
            clipItemCount : 0,
            prefix        : "absentclip"
        };

        // Display absent players

        this.forEachPlayer( function(a_Player) {
            if ( a_Player.status == "unavailable" )
            {
                HTMLString += self.generateAbsentSlot( a_Player, clipStatus );
            }
        });

        HTMLString += "</div>";
        HTMLString += "</div>";

        return HTMLString;
    };

    // -------------------------------------------------------------------------

    this.generateUndecidedList = function( a_Rows )
    {
        var HTMLString = "<div id=\"undecidedList\">";

        HTMLString += "<h2 style=\"position: relative; width: 800px\">"+L("UndecidedPlayers")+"</h2>";
        HTMLString += "<div class=\"clip\" id=\"undecidedclip0\">";

        var self = this;
        var clipStatus = {
            rowsPerClip   : a_Rows,
            currentId     : 0,
            itemCount     : 0,
            clipItemCount : 0,
            prefix        : "undecidedclip"
        };

        // Display undecided players

        this.forEachPlayer( function(a_Player) {
            if ( a_Player.status == "undecided" )
            {
                HTMLString += self.generateAbsentSlot( a_Player, clipStatus );
            }
        });

        HTMLString += "</div>";
        HTMLString += "</div>";

        return HTMLString;
    };

    // -----------------------------------------------------------------------------

    this.bindClipPlayer = function( a_ClipItem )
    {
        var pid = parseInt( a_ClipItem.attr("id").substr(2), 10 );
        var playerList = this;

        a_ClipItem.draggable({
            delay          : 100,
            revert         : true,
            revertDuration : 200,
            helper         : "clone",
            start          : function() { playerList.showDropTargets(pid, $(this)); },
            stop           : function() { playerList.hideDropTargets(); }
        });

        a_ClipItem.children(".editableName").each( function() {
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

        makeTouchable(a_ClipItem);
    };

    // -----------------------------------------------------------------------------

    this.bindClips = function( a_Parent )
    {
        if ( gUser == null )
            return;

        var clips = a_Parent.children(".clip");
        var config = a_Parent.data("config");
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

    this.updateRoleList = function( a_RoleIdx )
    {
        var roleList = $( $("#raidsetup").children(".roleList")[a_RoleIdx] );
        var roleConf = roleList.data("config");

        roleList.replaceWith( this.generateRoleList(roleConf.id, roleConf.columns, roleConf.reqSlots) );
        roleList = $( $("#raidsetup").children(".roleList")[a_RoleIdx] );

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

    this.showDropTargets = function( a_PlayerId, a_Source )
    {
        var playerList = $("#raiddetail").data("players");
        var pIdx       = this.getPlayerIndex( a_PlayerId );
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
                        a_Source.draggable("option", "revert", false);
                        a_Source.draggable("destroy").detach();
                        playerList.movePlayer(a_PlayerId, currentRoleIdx);
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
                    prompt(L("AbsentMessage"), L("MarkAsAbesent"), L("Cancel"), function(a_Comment) {
                        a_Source.draggable("option", "revert", false);
                        a_Source.draggable("destroy").detach();
                        playerList.absentPlayer(a_PlayerId, "["+gUser.name+"] "+a_Comment);                
                    });
                }
                else
                {
                    a_Source.draggable("option", "revert", false);
                    a_Source.draggable("destroy").detach();
                    
                    playerList.absentPlayer(a_PlayerId, "");
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

    this.upgradePlayer = function( a_PlayerId )
    {
        var pIdx = this.getPlayerIndex( a_PlayerId );
        
        this.mPlayers[pIdx].status = "ok";
        this.mPlayers[pIdx].flags |= PlayerFlagModified;
        
        this.updateRoleList( this.mPlayers[pIdx].activeRole );
    };

    // -------------------------------------------------------------------------

    this.downgradePlayer = function( a_PlayerId )
    {
        var pIdx = this.getPlayerIndex( a_PlayerId );
        var roleIdx = this.mPlayers[pIdx].activeRole;

        this.mPlayers[pIdx].status = "available";
        this.mPlayers[pIdx].flags |= PlayerFlagModified;
        
        this.updateRoleList( roleIdx );
    };

    // -----------------------------------------------------------------------------

    this.movePlayer = function( a_PlayerId, a_RoleIdx )
    {
        var pIdx     = this.getPlayerIndex( a_PlayerId );
        var prevRole = this.mPlayers[pIdx].activeRole;

        this.mPlayers[pIdx].activeRole = a_RoleIdx;
        this.mPlayers[pIdx].flags |= PlayerFlagModified;

        if ( this.mMode == "all" )
            this.mPlayers[pIdx].status = "ok";
        else
            this.mPlayers[pIdx].status = "available";

        this.updateRoleList( prevRole );
        this.updateRoleList( a_RoleIdx );
    };

    // -------------------------------------------------------------------------

    this.removePlayer = function( a_PlayerId )
    {
        var pIdx = this.getPlayerIndex( a_PlayerId );
        var role = this.mPlayers[pIdx].activeRole;
        
        this.mPlayers.splice(pIdx,1);

        if ( a_PlayerId > 0 )
            this.mRemovedPlayers.push( a_PlayerId );            
            
        this.updateRoleList( role );
        onUIDataChange();
    };

    // -------------------------------------------------------------------------

    this.absentPlayer = function( a_PlayerId, a_Comment )
    {
        var pIdx = this.getPlayerIndex( a_PlayerId );
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
            player.comment = a_Comment;
            
            $("#absentList").replaceWith(this.generateAbsentList(3));
            onUpdateAbsentList(this);
        }
        else
        {
            this.mPlayers.splice(pIdx,1);
            this.mRemovedPlayers.push( a_PlayerId );
        }
        
        this.updateRoleList( role );
        hideTooltip();
        onUIDataChange();
    };
}

// -----------------------------------------------------------------------------
//  player list functions
// -----------------------------------------------------------------------------

function showRaidClip( a_ClipId )
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

function generateRaidInfo( a_RaidXML, a_AppendTo )
{
    var MonthArray = Array(L("January"), L("February"), L("March"), L("April"), L("May"), L("June"), L("July"), L("August"), L("September"), L("October"), L("November"), L("December"));

    var raidImage = a_RaidXML.children("image:first").text();
    var raidName  = a_RaidXML.children("location:first").text();
    var raidSize  = 0;
    var numRoles  = Math.min( g_RoleNames.length, 5 );

    a_RaidXML.children("slots").children("required").each( function() {
       raidSize += parseInt($(this).text(), 10);
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

function generateRaidSetup( a_RaidXML )
{
    var playerList = $("#raiddetail").data("players");
    var numRoles   = Math.min( g_RoleNames.length, 5 );
    var roleCounts = a_RaidXML.children("slots:first");

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

    generateRaidInfo( a_RaidXML, "raidsetup" );

    if (gUser.isAdmin)
        $("#raidsetup").prepend("<div id=\"absent_drop\">"+L("MakeAbsent")+"</div>");
}

// -----------------------------------------------------------------------------

function onClickAbsentSlot(a_Event, a_Element, a_Player, a_Undecided)
{
    var charIdx = a_Element.data("setup_info");
                        
    if (charIdx != null)
    {
        var character = a_Player.characters[charIdx];
        var hasUserId = a_Player.comment !== "";
        
        a_Player.status = "available";
        
        a_Player.name = character.name;
        a_Player.charId = character.id;
        a_Player.className = character.className;
        a_Player.mainchar = character.mainchar;
        a_Player.activeRole = character.firstRole;
        a_Player.firstRole = character.firstRole;
        a_Player.secondRole = character.secondRole;
        a_Player.comment = L("SetupBy") + gUser.name;
        a_Player.flags |= PlayerFlagModified | PlayerFlagComment | PlayerFlagCharId;
        
        if ( !a_Player.hasRecord )
            a_Player.flags |= PlayerFlagNew;
        
        if ( !hasUserId )
            a_Player.flags |= PlayerFlagUserId;
        
        // Update lists
        
        var playerList = $("#raiddetail").data("players");
        
        if ( a_Undecided )
        {
            $("#undecidedList").replaceWith(playerList.generateUndecidedList(3));
            onUpdateUndecidedList(playerList);
        }
        else
        {
            $("#absentList").replaceWith(playerList.generateAbsentList(3));
            onUpdateAbsentList(playerList);        
        }
        
        playerList.updateRoleList( a_Player.activeRole );
        
        onUIDataChange();
        hideTooltip();
    }
    else
    {        
        a_Element.css("background-image", "url(lib/layout/images/move_up.png)");
        a_Element.data("setup_info", 0);
        
        showTooltipSlackers( a_Element, a_Player, false, true );
        
        $("#tooltip").data("onHide", function() {
            a_Element.siblings(".playerName:first").empty().append( a_Player.name );
            a_Element.css("background-image", "url(images/classessmall/"+a_Player.className+".png)");
            a_Element.data("setup_info", null);
        });
    }
        
    a_Event.stopPropagation();
}

// -----------------------------------------------------------------------------

function onUpdateAbsentList(a_PlayerList)
{
    $("#absentList").data("config", {
        clip : "absentclip0"
    });

    $("#absentList").children(".clip").children(".benchSlotComment, .benchSlot").each( function() {
        var pid     = parseInt( $(this).attr("id").substr(2), 10 );
        var pIdx    = a_PlayerList.getPlayerIndex(pid);
        var player  = a_PlayerList.mPlayers[pIdx];
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

function onUpdateUndecidedList(a_PlayerList)
{    
    $("#undecidedList").data("config", {
        clip : "undecidedclip0"
    });

    $("#undecidedList").children(".clip").children(".benchSlotComment, .benchSlot").each( function() {
        var pid     = parseInt( $(this).attr("id").substr(2), 10 );
        var pIdx    = a_PlayerList.getPlayerIndex(pid);
        var player  = a_PlayerList.mPlayers[pIdx];
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

function generateRaidSlackers( a_RaidXML )
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

    generateRaidInfo( a_RaidXML, "slackers" );
}

// -----------------------------------------------------------------------------

function generateRaidSettings( a_MessageXML, a_RaidXML )
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
    var raidSize        = parseInt(a_RaidXML.children("size:first").text(),10);
    var raidComment     = a_RaidXML.children("description:first").text();
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
        id    : parseInt(a_RaidXML.children("raidId:first").text(), 10),
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

function generateRaid( a_XMLData )
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

    var Message = $(a_XMLData).children("messagehub:first");
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

    if ( gUser == null )
        return;

    $("#body").empty();

    var Parameters = {
        id : a_RaidId,
        showPanel : a_PanelName
    };

    asyncQuery( "raid_detail", Parameters, generateRaid );

}

// -----------------------------------------------------------------------------

function loadRaidPanel( a_Name, a_RaidId )
{
    if ( gUser == null )
        return;

    if ( $("#raiddetail").length === 0 )
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