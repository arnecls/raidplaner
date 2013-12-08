function CGroup( aName )
{
    this.mUsers = Array();
    this.mName  = aName;

    $("#" + aName).data( "helper", this );

    this.addUser = function( aId, aLogin, aBindingActive, aBinding, aSyncActive )
    {
        this.mUsers.push( {
            id             : aId,
            login          : aLogin,
            binding        : aBinding,
            bindingActive  : aBindingActive,
            changedLinkage : false,
            locked         : aSyncActive && aBindingActive && (aBinding != "none")
        });
    };

    // -------------------------------------------------------------------------

    this.addUserObject = function( aObject )
    {
        this.mUsers.push( aObject );
    };

    // -------------------------------------------------------------------------

    this.removeUser = function( aIndex )
    {
        var User = this.mUsers[ aIndex ];
        this.mUsers.splice( aIndex, 1 );

        return User;
    };

    // -------------------------------------------------------------------------

    this.removeUserById = function( aId )
    {
        for ( var i=0; i<this.mUsers.length; ++i )
        {
            var User = this.mUsers[i];
            if ( User.id == aId )
            {
                this.mUsers.splice( i, 1 );
                return User;
            }
        }

        return null;
    };
    
    // -------------------------------------------------------------------------
    
    this.unlinkUser = function( aId )
    {					
        for ( var i=0; i<this.mUsers.length; ++i )
        {
            var User = this.mUsers[i];
            if ( User.id == aId )
            {
                User.bindingActive = false;
                User.changedLinkage = true;
                User.locked = false;
                return;
            }
        }
    };

    // -------------------------------------------------------------------------

    this.getUserIdArray = function()
    {
        var UserIdArray = [];
        for ( var i=0; i<this.mUsers.length; ++i)
        {
            UserIdArray.push( this.mUsers[i].id );
        }

        return UserIdArray;
    };

    // -------------------------------------------------------------------------

    this.getUserIdxById = function( aId )
    {
        for ( var i=0; i<this.mUsers.length; ++i )
        {
            var User = this.mUsers[i];
            if ( User.id == aId )
                return i;
        }

        return -1;
    };

    // -------------------------------------------------------------------------

    this.updateLinkageArrays = function(aUnlinkedArray, aRelinkedArray)
    {
        for ( var i=0; i<this.mUsers.length; ++i)
        {
            if ( this.mUsers[i].changedLinkage )
            {
                if (this.mUsers[i].bindingActive)
                    aRelinkedArray.push( this.mUsers[i].id );
                else
                    aUnlinkedArray.push( this.mUsers[i].id );
            }
        }
    };

    // -------------------------------------------------------------------------

    this.refreshList = function()
    {
        var HTMLString = "";

        for (var i=0; i<this.mUsers.length; ++i)
        {
            var BindingString = this.mUsers[i].binding;
            var BindingClass  = (this.mUsers[i].bindingActive) ? "binding_active" : "binding";
            var IsReserved    = (this.mUsers[i].id == 1);
            var IsLocked      = IsReserved || this.mUsers[i].locked;

            HTMLString += "<div class=\"user\" id=\"" + this.mUsers[i].id + "\">";

            if ( IsReserved )
                HTMLString += "<span class=\"userFunction\" style=\"cursor: default\"></span>";
            else
                HTMLString += "<span class=\"userFunction functionDelete\"></span>";

            HTMLString += "<span class=\"userFunction functionEdit\"></span>";

            if ( IsLocked )
            {
                HTMLString += "<div "+((!IsReserved) ? "class=\"userLinked\" " : "")+"index=\"" + i + "\">";
                HTMLString += "<div style=\"background-image: url(lib/layout/images/icon_exuser.png)\" class=\"userImage\">";
                HTMLString += "<div class=\"overlayLocked\"></div>"
                HTMLString += "</div>";
            }
            else
            {
                HTMLString += "<div class=\"userDrag\" index=\"" + i + "\">";
                HTMLString += "<div style=\"background-image: url(lib/layout/images/icon_user.png)\" class=\"userImage\"></div>";
            }
            
            if ( BindingString == "none" )
            {
                HTMLString += "<div class=\"userNameLocal\">";
                HTMLString += "<span style=\"font-weight: bold\">" + this.mUsers[i].login + "</span>";
                HTMLString += "</div>";
            }
            else
            {
                HTMLString += "<div class=\"userName\">";
                HTMLString += "<span style=\"font-weight: bold\">" + this.mUsers[i].login + "</span><br/>";
                HTMLString += "<span class=\"" + BindingClass + "\">" + BindingString + "</span>";
                HTMLString += "</div>";
            }

            HTMLString += "</div>";
            HTMLString += "</div>";
        }
        
        // Fix for chrome dirty-rect bug
        
        var FixHeight = 380 - 38 * this.mUsers.length;
        if ( FixHeight > 0 )
            HTMLString += "<div style=\"height:"+FixHeight+"px\"></div>";

        // setup
    
        var ListElement = $("#" + this.mName);
        ListElement.empty().append( HTMLString );

        var DraggableFields = $(".user > .userDrag", ListElement);
        var LinkedFields    = $(".user > .userLinked", ListElement);
        
        DraggableFields.data( "handle", this.mName );
        
        DraggableFields.addClass("clickable");
        LinkedFields.addClass("clickable");
        
        // Setup UI

        $(".functionDelete").click( function() {
            var UserId = $(this).parent().attr("id");
            var Host   = $(this).parent().parent().data("helper");

            confirm( L("ConfirmDeleteUser"), L("DeleteUser"), L("Cancel"), function() {
                var User = Host.removeUserById( UserId );
                Host.refreshList();
                onUIDataChange();

                $("#groups").data("helper").addUserObject( User );
            });
        });

        $(".functionEdit").click( function() {
            var UserId = $(this).parent().attr("id");
            changeContext( "settings," + UserId );
        });

        DraggableFields.draggable({
            revert            : "invalid",
            revertDuration    : 200,
            opacity           : 0.5,
            helper            : "clone",
            stop              : refreshSource
        }).each( function() {
            makeTouchable($(this));
        });

        DraggableFields.click( function(aEvent) {
            showUserTooltip($(this).parent(), true);
            aEvent.stopPropagation();
        });

        LinkedFields.click( function(aEvent) {
            showUnlinkTooltip($(this).parent(), true);
            aEvent.stopPropagation();
        });

        $(".groupSlot").droppable({
            disabled    : false,
            hoverClass  : "groupTarget",
            drop        : onUserDrop,
            addClasses  : false
        });        
    };
}

// -----------------------------------------------------------------------------

function refreshSource( aEvent, aContext )
{
    var Handle = $(this).data("handle");
    var SourceHelper = $("#" + Handle).data( "helper" );
    
    SourceHelper.refreshList();
}

// -----------------------------------------------------------------------------

function onUserDrop( aEvent, aContext )
{
    var Index = aContext.draggable.attr("index");
    var Handle = aContext.draggable.data("handle");

    var SourceHelper = $("#" + Handle).data( "helper" );
    var TargetHelper = $(aEvent.target).children(".center").data( "helper" );

    if ( SourceHelper != TargetHelper )
    {
        var User = SourceHelper.removeUser( Index );
        TargetHelper.addUserObject( User );
        TargetHelper.refreshList();    
        onUIDataChange();
    }
}

// -----------------------------------------------------------------------------

function onLinkUserReturn( aXHR )
{
    var UserId  = aXHR.userid;
    var Group   = aXHR.group;
    var Binding = aXHR.binding;
    
    var ToGroup = "groupBanned";
    
    switch (Group)
    {
    case "member":
        ToGroup = "groupMember";
        break;
    
    case "raidlead":
        ToGroup = "groupRaidlead";
        break;
    
    case "admin":
        ToGroup = "groupAdmin";
        break;
        
    default:
        break;
    }
    
    var SourceHelper = $("#" + UserId).parent().data( "helper" );    
    
    if ( SourceHelper != null )
    {
        var TargetHelper = $("#" + ToGroup).data( "helper" );
        var UserIdx = SourceHelper.getUserIdxById( parseInt(UserId, 10) );
        var User = SourceHelper.mUsers[UserIdx];
        
        User.bindingActive = true;
        User.changedLinkage = true;
        User.binding = Binding;        
        User.locked = aXHR.syncActive;
        
        if ( SourceHelper != TargetHelper )
        {
            SourceHelper.removeUser( UserIdx );
            TargetHelper.addUserObject( User );
            TargetHelper.refreshList();
        }
        
        SourceHelper.refreshList();
        onUIDataChange();
    }
    else
    {
        notify(L("SyncFailed"));
    }
    
    hideTooltip();
}

// -----------------------------------------------------------------------------

function moveUserToGroup( aUserId, aToGroup )
{
    if ( aToGroup == "groupSync" )
    {
        linkUser(aUserId);
    }
    else
    {
        var SourceHelper = $("#" + aUserId).parent().data( "helper" );
        var TargetHelper = $("#" + aToGroup).data( "helper" );
    
        if ( SourceHelper != TargetHelper )
        {
            var User = SourceHelper.removeUserById( aUserId );
            TargetHelper.addUserObject( User );
    
            SourceHelper.refreshList();
            TargetHelper.refreshList();    
            onUIDataChange();
        }
    }
}

// -----------------------------------------------------------------------------

function unlinkUser( aUserId )
{
    var SourceHelper = $("#" + aUserId).parent().data("helper");
    SourceHelper.unlinkUser(aUserId);
    SourceHelper.refreshList();
    onUIDataChange();
}
    
// -------------------------------------------------------------------------

function linkUser( aId )
{
    var Parameters = {
        userId : aId   
    };
    
    asyncQuery( "user_link", Parameters, onLinkUserReturn );
}

// -----------------------------------------------------------------------------

function calculateShortTime( aTime )
{
    var Result = {
        time   : aTime,
        metric : 0
    };

    if ( (Math.abs(Result.time / 60) > 0) && (Result.time % 60 === 0) )
    {
        Result.time /= 60;
        ++Result.metric;

        if ( (Math.abs(Result.time / 60) > 0) && (Result.time % 60 === 0) )
        {
            Result.time /= 60;
            ++Result.metric;

            if ( (Math.abs(Result.time / 24) > 0) && (Result.time % 24 === 0) )
            {
                Result.time /= 24;
                ++Result.metric;

                if ( (Math.abs(Result.time / 7) > 0) && (Result.time % 7 === 0) )
                {
                    Result.time /= 7;
                    ++Result.metric;

                    if ( (Math.abs(Result.time / 4 > 0)) && (Result.time % 4 === 0) )
                    {
                        Result.time /= 4;
                        ++Result.metric;
                    }
                }
            }
        }
    }

    return Result;
}

// -----------------------------------------------------------------------------

function calculateUnixTime( aTime, aTimeMetric )
{
    switch( aTimeMetric )
    {
    case 1:
        return aTime * 60;
    case 2:
        return aTime * 60 * 60;
    case 3:
        return aTime * 60 * 60 * 24;
    case 4:
        return aTime * 60 * 60 * 24 * 7;
    case 5:
        return aTime * 60 * 60 * 24 * 7 * 4;
    default:
        break;
    }

    return aTime;
}

// -----------------------------------------------------------------------------

function deleteLocation( aElement )
{
    confirm(L("ConfirmDeleteLocation")+"<br>"+L("NoteDeleteRaidsToo"),
        L("DeleteLocationRaids"), L("Cancel"),
        function() {
            var LocationId = $(aElement).attr("id");
            LocationId = parseInt(LocationId.substring(9,LocationId.length), 10);

            $("#locationsettings").data("removed").push( LocationId );
            $(aElement).detach();    
            onUIDataChange();
        });
}

// -----------------------------------------------------------------------------

function showPanel( aShowBox, aSection )
{
    $("#groups").hide();
    $("#locationsettings").hide();
    $("#raidsettings").hide();
    $("#statistics").hide();
    $("#about").hide();

    $("#tablist").removeClass("users");
    $("#tablist").removeClass("locations");
    $("#tablist").removeClass("settings");
    $("#tablist").removeClass("stats");
    $("#tablist").removeClass("about");

    $("#userstoggle").removeClass("icon_users");
    $("#locationstoggle").removeClass("icon_locations");
    $("#settingstoggle").removeClass("icon_settings");
    $("#statstoggle").removeClass("icon_stats");
    $("#abouttoggle").removeClass("icon_about");

    $("#userstoggle").addClass("icon_users_off");
    $("#locationstoggle").addClass("icon_locations_off");
    $("#settingstoggle").addClass("icon_settings_off");
    $("#statstoggle").addClass("icon_stats_off");
    $("#abouttoggle").addClass("icon_about_off");

    $(aShowBox).show();
    $("#tablist").addClass(aSection);
    $("#"+aSection+"toggle").removeClass("icon_"+aSection+"_off");
    $("#"+aSection+"toggle").addClass("icon_"+aSection);

    if ( (aSection == "about") || (aSection == "stats") )
        $("#applyButton").hide();
    else
        $("#applyButton").show();
}

// -----------------------------------------------------------------------------

function generateSettingsUsers( aXHR )
{
    var HTMLString = "<div id=\"groups\">";

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

    $("#settings").append(HTMLString);

    // setup user lists

    var Banned   = new CGroup("groupBanned");
    var Member   = new CGroup("groupMember");
    var Raidlead = new CGroup("groupRaidlead");
    var Admin    = new CGroup("groupAdmin");
    var Removed  = new CGroup("groups");
    
    var syncActive = aXHR.syncActive;

    $.each(aXHR.user, function(index, value) {
        var AddToGroup = null;
        
        switch (value.group)
        {
        case "none":
            AddToGroup = Banned;
            break;
            
        case "member":
            AddToGroup = Member;
            break;
            
        case "raidlead":
            AddToGroup = Raidlead;
            break;
            
        case "admin":
            AddToGroup = Admin;
            break;
            
        default:
            break;
        }
        
        if (AddToGroup != null)
        {
            AddToGroup.addUser(value.id,
                value.login,
                value.bindingActive,
                value.binding,
                syncActive );
        }
    });

    Banned.refreshList();
    Member.refreshList();
    Raidlead.refreshList();
    Admin.refreshList();

    // hide

    $("#groups").hide();
}

// -----------------------------------------------------------------------------

function generateSettingsLocation( aXHR )
{
    var HTMLString = "<div id=\"locationsettings\">";
    HTMLString += "<div class=\"imagelist\" id=\"locationimagelist\">";

    var NumImages = 0;

    $.each(aXHR.locationimage, function(index, value) {

        if ( (NumImages + 1) % 11 === 0 )
        {
            HTMLString += "<br/>";
            ++NumImages;
        }

        HTMLString += "<img class=\"clickable\" src=\"images/raidsmall/" + value + "\" onclick=\"applyLocationImageExternal(this, true)\" style=\"width: 32px; height: 32px; margin-right: 5px;\"/>";
        ++NumImages;
    });

    HTMLString += "</div>";
    HTMLString += "</div>";

    $("#settings").append(HTMLString);

    $.each(aXHR.location, function(index, value) {

        var LocationId = value.id;
        var LocationImage = value.image;

        HTMLString  = "<div class=\"location\" id=\"location_" + LocationId + "\">";
        HTMLString += "<span class=\"imagepicker locationimg clickable\" style=\"background-image: url(images/raidsmall/" + LocationImage + ")\"></span>";
        HTMLString += "<input class=\"locationname\" type=\"text\" value=\"" + value.name + "\"/>";
        HTMLString += "<div class=\"deletecmd clickable\" onclick=\"deleteLocation($(this).parent())\"></div>";
        HTMLString += "</div>";

       $("#locationsettings").append(HTMLString);
       $("#location_" + LocationId +" > .locationimg:first").data("selectedImage", LocationImage);
    });


    // setup location

    $("#locationsettings .imagepicker")
        .click( function(aEvent) {
            $("#locationimagelist").data("external", $(this));
            showTooltipRaidImageListAtElement($(this));
            aEvent.stopPropagation();
        });

    $("#locationsettings").data("removed", []);
    
    $(".locationname").change( onUIDataChange ); 

    // hide

    $("#locationimagelist").hide();
    $("#locationsettings").hide();
}

// -----------------------------------------------------------------------------


function generateSettingsRaid( aXHR )
{
    var PurgeRaidTime   = 0;
    var PurgeRaidMetric = 0;
    var LockRaidTime    = 0;
    var LockRaidMetric  = 0;
    var Settings        = [];

    Settings["RaidStartHour"]   = { text : "", number : 19 };
    Settings["RaidStartMinute"] = { text : "", number : 30 };
    Settings["RaidEndHour"]     = { text : "", number : 23 };
    Settings["RaidEndMinute"]   = { text : "", number : 0 };
    Settings["RaidSize"]        = { text : "", number : 10 };
    Settings["Theme"]           = { text : "default", number : 0 };
    Settings["Site"]            = { text : "", number : 0 };
    Settings["HelpPage"]        = { text : "", number : 0 };
    Settings["TimeFormat"]      = { text : "", number : 0 };

    $.each(aXHR.setting, function(index, value) {
    
        var SettingName = value.name;

        if ( SettingName == "PurgeRaids" )
        {
            var ShortTime = calculateShortTime( value.intValue );
            PurgeRaidTime = ShortTime.time;
            PurgeRaidMetric = ShortTime.metric;
        }
        else if ( SettingName == "LockRaids" )
        {
            var ShortTime = calculateShortTime( value.intValue );
            LockRaidTime = ShortTime.time;
            LockRaidMetric = ShortTime.metric;
        }

        Settings[ SettingName ] = {
            text   : value.textValue,
            number : value.intValue
        };
    });

    var HTMLString = "<div id=\"raidsettings\">";

    HTMLString += "<div class=\"propLine\">";
    HTMLString += "<span class=\"propLabel\">" + L("DeleteRaids") + "</span>";
    HTMLString += "<input class=\"timeField\" type=\"text\" id=\"purgeTime\" value=\"\"/>";
    HTMLString += "<select class=\"metricField\" id=\"purgeMetric\">";
    HTMLString += "<option" + ((PurgeRaidMetric === 0) ? " selected" : "" ) + ">" + L("Seconds") + "</option>";
    HTMLString += "<option" + ((PurgeRaidMetric == 1) ? " selected" : "" ) + ">" + L("Minutes") + "</option>";
    HTMLString += "<option" + ((PurgeRaidMetric == 2) ? " selected" : "" ) + ">" + L("Hours") + "</option>";
    HTMLString += "<option" + ((PurgeRaidMetric == 3) ? " selected" : "" ) + ">" + L("Days") + "</option>";
    HTMLString += "<option" + ((PurgeRaidMetric == 4) ? " selected" : "" ) + ">" + L("Weeks") + "</option>";
    HTMLString += "<option" + ((PurgeRaidMetric == 5) ? " selected" : "" ) + ">" + L("Month") + "</option>";
    HTMLString += "</select>";
    HTMLString += "<span class=\"propLabel2\">" + L("AfterDone") + "</span>";
    HTMLString += "</div>";

    HTMLString += "<div class=\"propLineLast\">";
    HTMLString += "<span class=\"propLabel\">" + L("LockRaids") + "</span>";
    HTMLString += "<input class=\"timeField\" type=\"text\" id=\"lockTime\" value=\"\"/>";
    HTMLString += "<select class=\"metricField\" id=\"lockMetric\">";
    HTMLString += "<option" + ((LockRaidMetric === 0) ? " selected" : "" ) + ">" + L("Seconds") + "</option>";
    HTMLString += "<option" + ((LockRaidMetric == 1) ? " selected" : "" ) + ">" + L("Minutes") + "</option>";
    HTMLString += "<option" + ((LockRaidMetric == 2) ? " selected" : "" ) + ">" + L("Hours") + "</option>";
    HTMLString += "<option" + ((LockRaidMetric == 3) ? " selected" : "" ) + ">" + L("Days") + "</option>";
    HTMLString += "<option" + ((LockRaidMetric == 4) ? " selected" : "" ) + ">" + L("Weeks") + "</option>";
    HTMLString += "<option" + ((LockRaidMetric == 5) ? " selected" : "" ) + ">" + L("Month") + "</option>";
    HTMLString += "</select>";
    HTMLString += "<span class=\"propLabel2\">" + L("BeforeStart") + "</span>";
    HTMLString += "</div>";

    HTMLString += "<div class=\"propLine\">";
    HTMLString += "<span class=\"propLabel\">" + L("TimeFormat") + "</span>";
    HTMLString += "<select id=\"timeFormat\" style=\"width: " + ((gTimeFormat == 24) ? 48 : 64) + "px\">";
    HTMLString += "<option value=\"12\"" + ((Settings["TimeFormat"].number == 12) ? " selected" : "" ) + ">12h</option>";
    HTMLString += "<option value=\"24\"" + ((Settings["TimeFormat"].number == 24) ? " selected" : "" ) + ">24h</option>";
    HTMLString += "</select>";
    HTMLString += "</div>";
    
    HTMLString += "<div class=\"propLineLast\">";
    HTMLString += "<span class=\"propLabel\">" + L("StartOfWeek") + "</span>";
    HTMLString += "<select id=\"startOfWeek\" style=\"width: 160px\">";
    HTMLString += "<option value=\"0\"" + ((Settings["StartOfWeek"].number === 0) ? " selected" : "" ) + ">"+L("Sunday")+"</option>";
    HTMLString += "<option value=\"1\"" + ((Settings["StartOfWeek"].number == 1) ? " selected" : "" ) + ">"+L("Monday")+"</option>";
    HTMLString += "<option value=\"2\"" + ((Settings["StartOfWeek"].number == 2) ? " selected" : "" ) + ">"+L("Tuesday")+"</option>";
    HTMLString += "<option value=\"3\"" + ((Settings["StartOfWeek"].number == 3) ? " selected" : "" ) + ">"+L("Wednesday")+"</option>";
    HTMLString += "<option value=\"4\"" + ((Settings["StartOfWeek"].number == 4) ? " selected" : "" ) + ">"+L("Thursday")+"</option>";
    HTMLString += "<option value=\"5\"" + ((Settings["StartOfWeek"].number == 5) ? " selected" : "" ) + ">"+L("Friday")+"</option>";
    HTMLString += "<option value=\"6\"" + ((Settings["StartOfWeek"].number == 6) ? " selected" : "" ) + ">"+L("Saturday")+"</option>";
    HTMLString += "</select>";
    HTMLString += "</div>";

    HTMLString += "<div class=\"propLine\">";
    HTMLString += "<span class=\"propLabel\">" + L("DefaultStartTime") + "</span>";
    HTMLString += "<select id=\"starthour\" style=\"width: " + ((gTimeFormat == 24) ? 48 : 64) + "px\">";

    for ( var i=4; i>=0; --i )
        HTMLString += "<option value=\"" + i + "\"" + ((Settings["RaidStartHour"].number == i) ? " selected" : "" ) + ">" + formatHourPrefixed(i) + "</option>";

    for ( i=23; i>4; --i )
        HTMLString += "<option value=\"" + i + "\"" + ((Settings["RaidStartHour"].number == i) ? " selected" : "" ) + ">" + formatHourPrefixed(i) + "</option>";

    HTMLString += "</select><span>&nbsp;:&nbsp;</span>";
    HTMLString += "<select id=\"startminute\" style=\"width: 48px\">";
    HTMLString += "<option value=\"0\"" + ((Settings["RaidStartMinute"].number === 0) ? " selected" : "" ) + ">00</option>";
    HTMLString += "<option value=\"15\"" + ((Settings["RaidStartMinute"].number == 15) ? " selected" : "" ) + ">15</option>";
    HTMLString += "<option value=\"30\"" + ((Settings["RaidStartMinute"].number == 30) ? " selected" : "" ) + ">30</option>";
    HTMLString += "<option value=\"45\"" + ((Settings["RaidStartMinute"].number == 45) ? " selected" : "" ) + ">45</option>";
    HTMLString += "</select>";
    HTMLString += "</div>";

    HTMLString += "<div class=\"propLine\">";
    HTMLString += "<span class=\"propLabel\">" + L("DefaultEndTime") + "</span>";
    HTMLString += "<select id=\"endhour\" style=\"width: " + ((gTimeFormat == 24) ? 48 : 64) + "px\">";

    for ( i=4; i>=0; --i )
        HTMLString += "<option value=\"" + i + "\"" + ((Settings["RaidEndHour"].number == i) ? " selected" : "" ) + ">" + formatHourPrefixed(i) + "</option>";

    for ( i=23; i>4; --i )
        HTMLString += "<option value=\"" + i + "\"" + ((Settings["RaidEndHour"].number == i) ? " selected" : "" ) + ">" + formatHourPrefixed(i) + "</option>";

    HTMLString += "</select><span>&nbsp;:&nbsp;</span>";
    HTMLString += "<select id=\"endminute\" style=\"width: 48px\">";
    HTMLString += "<option value=\"0\"" + ((Settings["RaidEndMinute"].number === 0) ? " selected" : "" ) + ">00</option>";
    HTMLString += "<option value=\"15\"" + ((Settings["RaidEndMinute"].number == 15) ? " selected" : "" ) + ">15</option>";
    HTMLString += "<option value=\"30\"" + ((Settings["RaidEndMinute"].number == 30) ? " selected" : "" ) + ">30</option>";
    HTMLString += "<option value=\"45\"" + ((Settings["RaidEndMinute"].number == 45) ? " selected" : "" ) + ">45</option>";
    HTMLString += "</select>";
    HTMLString += "</div>";

    HTMLString += "<div class=\"propLine\">";
    HTMLString += "<span class=\"propLabel\">" + L("DefaultRaidSize") + "</span>";
    HTMLString += "<select id=\"raidsize\" style=\"width: " + ((gTimeFormat == 24) ? 48 : 64) + "px\">";

    for ( i=0; i<gGroupSizes.length; ++i)
    {
        HTMLString += "<option value=\""+gGroupSizes[i]+"\"" + ((Settings["RaidSize"].number == gGroupSizes[i]) ? " selected" : "" ) + ">"+gGroupSizes[i]+"</option>";
    }

    HTMLString += "</select>";
    HTMLString += "</div>";

    HTMLString += "<div class=\"propLineLast\">";
    HTMLString += "<span class=\"propLabel\">" + L("DefaultRaidMode") + "</span>";
    HTMLString += "<select id=\"raidmode\" style=\"width: 160px\">";
    HTMLString += "<option value=\"manual\""+((Settings["RaidMode"].text=="manual") ? " selected" : "")+">"+L("RaidModeManual")+"</option>";
    HTMLString += "<option value=\"overbook\""+((Settings["RaidMode"].text=="overbook") ? " selected" : "")+">"+L("RaidModeOverbook")+"</option>";
    HTMLString += "<option value=\"attend\""+((Settings["RaidMode"].text=="attend") ? " selected" : "")+">"+L("RaidModeAttend")+"</option>";
    HTMLString += "<option value=\"all\""+((Settings["RaidMode"].text=="all") ? " selected" : "")+">"+L("RaidModeAll")+"</option>";
    HTMLString += "</select>";
    HTMLString += "</div>";

    HTMLString += "<div class=\"propLine\">";
    HTMLString += "<span class=\"propLabel\">" + L("BannerPage") + "</span>";
    HTMLString += "<input class=\"urlField\" type=\"text\" id=\"site\" style=\"width: 155px\" value=\"" + Settings["Site"].text + "\"/>";
    HTMLString += "</div>";
    
    HTMLString += "<div class=\"propLine\">";
    HTMLString += "<span class=\"propLabel\">" + L("HelpPage") + "</span>";
    HTMLString += "<input class=\"urlField\" type=\"text\" id=\"helpPage\" style=\"width: 155px\" value=\"" + Settings["HelpPage"].text + "\"/>";
    HTMLString += "</div>";

    HTMLString += "<div class=\"propLine\">";
    HTMLString += "<span class=\"propLabel\">" + L("Theme") + "</span>";
    HTMLString += "<select id=\"theme\" style=\"width: 160px\">";

    $.each(aXHR.theme, function(index, value) {
        var Name = value.name;
        var File = value.file;
        HTMLString += "<option value=\"" + File + "\"" + ((Settings["Theme"].text == File) ? " selected" : "" ) + ">" + Name + "</option>";
    });

    HTMLString += "</select>";
    HTMLString += "</div>";

    HTMLString += "</div>";

    $("#settings").append(HTMLString);

    // setup raid settings

    $("#purgeTime").val( PurgeRaidTime );
    $("#lockTime").val( LockRaidTime );
    $("#purgeMetric").combobox({ inlineStyle: {top: 3} });
    $("#lockMetric").combobox({ inlineStyle: {top: 3} });
    $("#timeFormat").combobox();
    $("#startOfWeek").combobox();
    $("#starthour").combobox();
    $("#startminute").combobox();
    $("#endhour").combobox();
    $("#endminute").combobox();
    $("#raidsize").combobox();
    $("#raidmode").combobox();
    $("#theme").combobox();
    
    // Change notifiers
    
    $("#raidsettings input").change( onUIDataChange );
    $("#raidsettings select").change( onUIDataChange );

    // hide

    $("#raidsettings").hide();
}

// -----------------------------------------------------------------------------

function generateSettingsStats( aXHR )
{
    var HTMLString = "<div id=\"statistics\">";

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

    var BarSize  = 740;

    HTMLString += "<div style=\"clear: left; padding-top: 15px\">";

    $.each(aXHR.attendance, function(index, value) {

        HTMLString += "<div style=\"clear: left\">";
        HTMLString += "<div class=\"name\">" + value.name + "</div>";
        HTMLString += "<div class=\"bar\"><span class=\"start_bar\"></span>";

        var NumOk      = parseInt(value.ok);
        var NumAvail   = parseInt(value.available);
        var NumUnavail = parseInt(value.unavailable);
        var NumMissed  = parseInt(value.undecided);
        var NumRaids   = NumOk + NumAvail + NumUnavail + NumMissed;
    
        var SizeOk      = (NumOk / NumRaids) * BarSize;
        var SizeAvail   = (NumAvail / NumRaids) * BarSize;
        var SizeUnavail = (NumUnavail / NumRaids) * BarSize;
        var SizeMissed  = (NumMissed / NumRaids) * BarSize;

        if (NumOk > 0)      HTMLString += "<span class=\"ok\" style=\"width: " + SizeOk.toFixed() + "px\"><div class=\"count\">" + NumOk + "</div></span>";
        if (NumAvail > 0)   HTMLString += "<span class=\"available\" style=\"width: " + SizeAvail.toFixed() + "px\"><div class=\"count\">" + NumAvail + "</div></span>";
        if (NumUnavail > 0) HTMLString += "<span class=\"unavailable\" style=\"width: " + SizeUnavail.toFixed() + "px\"><div class=\"count\">" + NumUnavail + "</div></span>";
        if (NumMissed > 0)  HTMLString += "<span class=\"missed\" style=\"width: " + SizeMissed.toFixed() + "px\"><div class=\"count\">" + NumMissed + "</div></span>";
        if (NumRaids === 0) HTMLString += "<span class=\"missed\" style=\"width: " + BarSize + "px\"><div class=\"count\">&nbsp;</div></span>";

        HTMLString += "<span class=\"end\"></span></div></div>";
    });

    HTMLString += "</div>";
    HTMLString += "</div>";

    $("#settings").append(HTMLString);
    $("#statistics").hide();
}

// -----------------------------------------------------------------------------

function generateSettingsAbout( aXHR )
{
    var HTMLString = "<div id=\"about\">";
    var PatchLevel = parseInt(Math.round((gSiteVersion - parseInt(gSiteVersion))*10));
    var PatchLevelChar = (PatchLevel === 0) ? "" : String.fromCharCode( "a".charCodeAt(0) + (PatchLevel-1) );

    HTMLString += "<div class=\"version\">";
    HTMLString += "Version " + parseInt(gSiteVersion / 100, 10) + "." + parseInt((gSiteVersion % 100) / 10, 10) + "." + parseInt(gSiteVersion % 10, 10) + PatchLevelChar + "<br/>";
    HTMLString += "<button id=\"update_check\">" + L("UpdateCheck") + "</button>";
    HTMLString += "</div>";

    $("#settings").append(HTMLString);
    $("#update_check").button({ icons: { secondary: "ui-icon-arrowreturnthick-1-n" }})
        .click( function() { triggerUpdateCheck(); } )
        .css({"font-size": 11, "margin-top": 15 });
}

// -----------------------------------------------------------------------------

function generateSettings( aXHR )
{
    var HTMLString = "<div id=\"settings\">";

    // Tabs

    HTMLString += "<h1>" + L("Settings") + "</h1>";

    HTMLString += "<div id=\"tablist\" class=\"tabs users\">";
    HTMLString += "<div style=\"margin-top: 16px\">";
    HTMLString += "<div id=\"userstoggle\" class=\"tab_icon icon_users\"></div>";
    HTMLString += "<div id=\"locationstoggle\" class=\"tab_icon icon_locations_off\"></div>";
    HTMLString += "<div id=\"settingstoggle\" class=\"tab_icon icon_settings_off\"></div>";
    HTMLString += "<div id=\"statstoggle\" class=\"tab_icon icon_stats_off\"></div>";
    HTMLString += "<div id=\"abouttoggle\" class=\"tab_icon icon_about_off\"></div>";
    HTMLString += "</div></div>";
    HTMLString += "<button id=\"applyButton\" class=\"apply_changes\" disabled=\"disabled\">" + L("Apply") + "</button>";

    $("#body").empty().append(HTMLString);

    $("#settings").hide();

    // User settings

    generateSettingsUsers( aXHR );
    generateSettingsLocation( aXHR );
    generateSettingsRaid( aXHR );
    generateSettingsStats( aXHR );
    generateSettingsAbout( aXHR );

    // Setup toplevel UI

    $("#applyButton").button({ icons: { secondary: "ui-icon-disk" }})
        .click( function() { triggerSettingsUpdate(); } )
        .css( "font-size", 11 );
        
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

    $("#abouttoggle").click( function() {
        changeContext( "settings,about" );
    });

    loadSettingsPanel( aXHR.show );

    $("#settings").show();
}

// -----------------------------------------------------------------------------

function triggerSettingsUpdate()
{
    if ( (gUser == null) || !gUser.isAdmin )
        return;

    var Banned   = $("#groupBanned").data("helper");
    var Member   = $("#groupMember").data("helper");
    var Raidlead = $("#groupRaidlead").data("helper");
    var Admin    = $("#groupAdmin").data("helper");
    var Removed  = $("#groups").data("helper");

    var BannedArray   = Banned.getUserIdArray();
    var MemberArray   = Member.getUserIdArray();
    var RaidleadArray = Raidlead.getUserIdArray();
    var AdminArray    = Admin.getUserIdArray();
    var RemovedArray  = Removed.getUserIdArray();
    
    var UnlinkedArray = [];
    var RelinkedArray = [];
    
    Banned.updateLinkageArrays(UnlinkedArray, RelinkedArray);
    Member.updateLinkageArrays(UnlinkedArray, RelinkedArray);
    Raidlead.updateLinkageArrays(UnlinkedArray, RelinkedArray);
    Admin.updateLinkageArrays(UnlinkedArray, RelinkedArray);    

    var PurgeRaidTime = calculateUnixTime( $("#purgeTime").val(), $("#purgeMetric")[0].selectedIndex );
    var LockRaidTime  = calculateUnixTime( $("#lockTime").val(), $("#lockMetric")[0].selectedIndex );

    var LocationIdData    = [];
    var LocationNameData  = [];
    var LocationImageData = [];

    $("#locationsettings").children(".location").each( function() {

        var LocationId = $(this).attr("id");
        LocationId = parseInt(LocationId.substring(9,LocationId.length), 10);

        LocationIdData.push( LocationId );
        LocationNameData.push( $(this).children(".locationname").val() );
        LocationImageData.push( $(this).children(".imagepicker").data("selectedImage") );
    });

    var Hash    = window.location.hash.substring( 1, window.location.hash.length );
    var IdIndex = Hash.lastIndexOf(",");
    
    var Parameters = {
        banned          : BannedArray,
        member          : MemberArray,
        raidlead        : RaidleadArray,
        admin           : AdminArray,        
        removed         : RemovedArray,
        unlinked        : UnlinkedArray,
        relinked        : RelinkedArray,
        locationIds     : LocationIdData,
        locationNames   : LocationNameData,
        locationImages  : LocationImageData,
        locationRemoved : $("#locationsettings").data("removed"),
        purgeTime       : PurgeRaidTime,
        lockTime        : LockRaidTime,
        timeFormat      : $("#timeFormat").val(),
        startOfWeek     : $("#startOfWeek").val(),
        raidStartHour   : $("#starthour").val(),
        raidStartMinute : $("#startminute").val(),
        raidEndHour     : $("#endhour").val(),
        raidEndMinute   : $("#endminute").val(),
        raidSize        : $("#raidsize").val(),
        raidMode        : $("#raidmode").val(),
        site            : $("#site").val(),
        helpPage        : $("#helpPage").val(),
        theme           : $("#theme").val(),
        showPanel       : Hash.substr(IdIndex+1)
    };

    onAppliedUIDataChange();
    
    asyncQuery( "settings_update", Parameters, function() {
        $.getScript("lib/script/config.js.php?version=" + gSiteVersion, function() {
            loadSettings(Parameters.showPanel);
            onChangeConfig();
        });
    });
}

// -----------------------------------------------------------------------------

function onUpdateCheckReturn( aJSONData )
{
    $("#update_message").detach();
    $("#ajaxblocker").clearQueue().hide();

    if ( gSiteVersion < aJSONData.version )
    {
        $("#about .version").css("color", "#AA0000");
        $("#update_check").before( "<div id=\"update_message\"><a href=\"http://code.google.com/p/ppx-raidplaner/downloads/list\" style=\"font-size: 12px\">" + L("VisitProjectPage") + "</a><br/><div>" );
        
        if ( aJSONData.hotfix == undefined )
             aJSONData.hotfix = "";
        
        notify( L("NewVersionAvailable") + "<br/><span style=\"font-size: 26px\">" + aJSONData.major + "." + aJSONData.minor + "." + aJSONData.patch + aJSONData.hotfix + "</span>" );
    }
    else
    {
        $("#about .version").css("color", "#00AA00");
        $("#update_check").before( "<div id=\"update_message\"><span style=\"font-size: 12px; color: #666\">" + L("UpToDate") + "</span><br/></div>" );

        notify( L("UpToDate") );
    }
}

// -----------------------------------------------------------------------------

function triggerUpdateCheck()
{
    var Parameters = {
    };

    $.ajax({
        type        : "GET",
        url         : "http://www.packedpixel.de/raidplaner_version.php",
        dataType    : "json",
        async       : true,
        data        : Parameters,
        success     : onUpdateCheckReturn,
        crossDomain : true
    });
}

// -----------------------------------------------------------------------------

function loadSettings( aName )
{
    reloadUser();

    if ( (gUser == null) || !gUser.isAdmin )
        return;

    $("#body").empty();

    var Parameters = {
       showPanel : aName
    };

    asyncQuery( "query_settings", Parameters, generateSettings );
}

// -----------------------------------------------------------------------------

function loadSettingsPanel( aName )
{
    if ( $("#settings").length === 0 )
    {
        loadSettings( aName );
    }
    else
    {
        switch( aName )
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

        case "about":
            showPanel("#about", "about");
            break;
        }
    }
}

// -----------------------------------------------------------------------------

function loadForeignProfile( aId )
{
    reloadUser();

    if ( (gUser == null) || !gUser.isAdmin )
        return;

    $("#body").empty();

    var Parameters = {
        id : aId
    };

    asyncQuery( "query_profile", Parameters, generateForeignProfile );
}