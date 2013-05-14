function CGroup( a_Name )
{
    this.mUsers = Array();
    this.mName  = a_Name;

    $("#" + a_Name).data( "helper", this );

    this.addUser = function( a_Id, a_Login, a_BindingActive, a_Binding )
    {
        this.mUsers.push( {
            id             : a_Id,
            login          : a_Login,
            binding        : a_Binding,
            bindingActive  : a_BindingActive,
            changedLinkage : false,
        });
    };

    // -------------------------------------------------------------------------

    this.addUserObject = function( a_Object )
    {
        this.mUsers.push( a_Object );
    };

    // -------------------------------------------------------------------------

    this.removeUser = function( a_Index )
    {
        var user = this.mUsers[ a_Index ];
        this.mUsers.splice( a_Index, 1 );

        return user;
    };

    // -------------------------------------------------------------------------

    this.removeUserById = function( a_Id )
    {
        for ( var i=0; i<this.mUsers.length; ++i )
        {
            var user = this.mUsers[i];
            if ( user.id == a_Id )
            {
                this.mUsers.splice( i, 1 );
                return user;
            }
        }

        return null;
    };
    
    // -------------------------------------------------------------------------
    
    this.unlinkUser = function( a_Id )
    {
        for ( var i=0; i<this.mUsers.length; ++i )
        {
            var user = this.mUsers[i];
            if ( user.id == a_Id )
            {
                user.bindingActive = false;
                user.changedLinkage = true;
                return;
            }
        }
    };

    // -------------------------------------------------------------------------

    this.getUserIdArray = function()
    {
        var idArray = [];
        for ( var i=0; i<this.mUsers.length; ++i)
        {
            idArray.push( this.mUsers[i].id );
        }

        return idArray;
    };

    // -------------------------------------------------------------------------

    this.getUserIdxById = function( a_Id )
    {
        for ( var i=0; i<this.mUsers.length; ++i )
        {
            var user = this.mUsers[i];
            if ( user.id == a_Id )
                return i;
        }

        return -1;
    };

    // -------------------------------------------------------------------------

    this.updateLinkageArrays = function(a_UnlinkedArray, a_RelinkedArray)
    {
        for ( var i=0; i<this.mUsers.length; ++i)
        {
            if ( this.mUsers[i].changedLinkage )
            {
                if (this.mUsers[i].bindingActive)
                    a_RelinkedArray.push( this.mUsers[i].id );
                else
                    a_UnlinkedArray.push( this.mUsers[i].id );
            }
        }
    };

    // -------------------------------------------------------------------------

    this.refreshList = function()
    {
        var HTMLString = "";

        for (i=0; i<this.mUsers.length; ++i)
        {
            var bindingString = this.mUsers[i].binding;
            var bindingClass  = (this.mUsers[i].bindingActive) ? "binding_active" : "binding";
            var isReserved    = (this.mUsers[i].id == 1);
            var isLocked      = isReserved || (this.mUsers[i].bindingActive && (bindingString != "none"));

            HTMLString += "<div class=\"user\" id=\"" + this.mUsers[i].id + "\">";

            if ( isReserved )
                HTMLString += "<span class=\"userFunction\" style=\"cursor: default\"></span>";
            else
                HTMLString += "<span class=\"userFunction functionDelete\"></span>";

            HTMLString += "<span class=\"userFunction functionEdit\"></span>";

            if ( isLocked )
                HTMLString += "<div "+((!isReserved) ? "class=\"userLinked\" " : "")+"index=\"" + i + "\">";
            else
                HTMLString += "<div class=\"userDrag\" index=\"" + i + "\">";

            HTMLString += "<img src=\"lib/layout/images/icon_user.png\" class=\"userImage\"/>";

            if ( bindingString == "none" )
            {
                HTMLString += "<div class=\"userNameLocal\">";
                HTMLString += "<span style=\"font-weight: bold\">" + this.mUsers[i].login + "</span>";
                HTMLString += "</div>";
            }
            else
            {
                HTMLString += "<div class=\"userName\">";
                HTMLString += "<span style=\"font-weight: bold\">" + this.mUsers[i].login + "</span><br/>";
                HTMLString += "<span class=\"" + bindingClass + "\">" + bindingString + "</span>";
                HTMLString += "</div>";
            }

            HTMLString += "</div>";
            HTMLString += "</div>";
        }
        
        // Fix for chrome dirty-rect bug
        
        var fixHeight = 380 - 38 * this.mUsers.length;
        if ( fixHeight > 0 )
            HTMLString += "<div style=\"height:"+fixHeight+"px\"></div>";

        // setup
    
        var listElement = $("#" + this.mName);
        listElement.empty().append( HTMLString );

        var draggableFields = listElement.children(".user").children(".userDrag");
        var linkedFields    = listElement.children(".user").children(".userLinked");
        draggableFields.data( "handle", this.mName );

        // Setup UI

        $(".functionDelete").click( function() {
            var id   = $(this).parent().attr("id");
            var host = $(this).parent().parent().data("helper");

            confirm( L("ConfirmDeleteUser"), L("DeleteUser"), L("Cancel"), function() {
                var user = host.removeUserById( id );
                host.refreshList();
                onUIDataChange();

                $("#groups").data("helper").addUserObject( user );
            });
        });

        $(".functionEdit").click( function() {
            var userId = $(this).parent().attr("id");
            changeContext( "settings," + userId );
        });

        draggableFields.draggable({
            revert            : "invalid",
            revertDuration    : 200,
            opacity           : 0.5,
            helper            : "clone",
            stop              : refreshSource
        }).each( function() {
            makeTouchable($(this));
        });

        draggableFields.click( function(event) {
            showUserTooltip($(this).parent(), true);
            event.stopPropagation();
        });

        linkedFields.click( function(event) {
            showUnlinkTooltip($(this).parent(), true);
            event.stopPropagation();
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

function refreshSource( a_Event, a_Context )
{
    var handle = $(this).data("handle");
    var sourceHelper = $("#" + handle).data( "helper" );
    
    sourceHelper.refreshList();
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
        targetHelper.refreshList();    
        onUIDataChange();
    }
}

// -----------------------------------------------------------------------------

function onLinkUserReturn( a_XMLData )
{
    var Message = $(a_XMLData).children("messagehub");
    var userId  = Message.children("userid").text();
    var group   = Message.children("group").text();
    var binding = Message.children("binding").text();
    
    var toGroup = "groupBanned";
    
    switch (group)
    {
    case "member":
        toGroup = "groupMember";
        break;
    
    case "raidlead":
        toGroup = "groupRaidlead";
        break;
    
    case "admin":
        toGroup = "groupAdmin";
        break;
    }
    
    var sourceHelper = $("#" + userId).parent().data( "helper" );    
    
    if ( sourceHelper != null )
    {
        var targetHelper = $("#" + toGroup).data( "helper" );
        var userIdx = sourceHelper.getUserIdxById( parseInt(userId, 10) );
        var user = sourceHelper.mUsers[userIdx];
        
        user.bindingActive = true;
        user.changedLinkage = true;
        user.binding = binding;
        
        if ( sourceHelper != targetHelper )
        {
            sourceHelper.removeUser( userIdx );
            targetHelper.addUserObject( user );
            targetHelper.refreshList();
        }
        
        sourceHelper.refreshList();
        onUIDataChange();
    }
    else
    {
        notify(L("SyncFailed"));
    }
    
    hideTooltip();
}

// -----------------------------------------------------------------------------

function moveUserToGroup( a_UserId, a_ToGroup )
{
    if ( a_ToGroup == "groupSync" )
    {
        linkUser(a_UserId);
    }
    else
    {
        var sourceHelper = $("#" + a_UserId).parent().data( "helper" );
        var targetHelper = $("#" + a_ToGroup).data( "helper" );
    
        if ( sourceHelper != targetHelper )
        {
            var user = sourceHelper.removeUserById( a_UserId );
            targetHelper.addUserObject( user );
    
            sourceHelper.refreshList();
            targetHelper.refreshList();    
            onUIDataChange();
        }
    }
}

// -----------------------------------------------------------------------------

function unlinkUser( a_UserId )
{
    var sourceHelper = $("#" + a_UserId).parent().data("helper");
    sourceHelper.unlinkUser(a_UserId);
    sourceHelper.refreshList();
    onUIDataChange();
}
    
// -------------------------------------------------------------------------

function linkUser( a_Id )
{
    var Parameters = {
        userId : a_Id   
    };
    
    asyncQuery( "user_link", Parameters, onLinkUserReturn );
}

// -----------------------------------------------------------------------------

function calculateShortTime( a_Time )
{
    var result = {
        time   : a_Time,
        metric : 0
    };

    if ( (result.time / 60 > 0) && (result.time % 60 === 0) )
    {
        result.time /= 60;
        ++result.metric;

        if ( (result.time / 60 > 0) && (result.time % 60 === 0) )
        {
            result.time /= 60;
            ++result.metric;

            if ( (result.time / 24 > 0) && (result.time % 24 === 0) )
            {
                result.time /= 24;
                ++result.metric;

                if ( (result.time / 7 > 0) && (result.time % 7 === 0) )
                {
                    result.time /= 7;
                    ++result.metric;

                    if ( (result.time / 4 > 0) && (result.time % 4 === 0) )
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
    confirm(L("ConfirmDeleteLocation")+"<br>"+L("NoteDeleteRaidsToo"),
        L("DeleteLocationRaids"), L("Cancel"),
        function() {
            var locationId = $(a_Element).attr("id");
            locationId = parseInt(locationId.substring(9,locationId.length), 10);

            $("#locationsettings").data("removed").push( locationId );
            $(a_Element).detach();    
            onUIDataChange();
        });
}

// -----------------------------------------------------------------------------

function showPanel( a_ShowBox, a_Section )
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

    $(a_ShowBox).show();
    $("#tablist").addClass(a_Section);
    $("#"+a_Section+"toggle").removeClass("icon_"+a_Section+"_off");
    $("#"+a_Section+"toggle").addClass("icon_"+a_Section);

    if ( (a_Section == "about") || (a_Section == "stats") )
        $("#applyButton").hide();
    else
        $("#applyButton").show();
}

// -----------------------------------------------------------------------------

function generateSettingsUsers( a_Message )
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

    var banned   = new CGroup("groupBanned");
    var member   = new CGroup("groupMember");
    var raidlead = new CGroup("groupRaidlead");
    var admin    = new CGroup("groupAdmin");
    var removed  = new CGroup("groups");

    a_Message.children("user").each( function() {
        if ( $(this).children("group").text() == "none" )
        {
            banned.addUser( $(this).children("id").text(),
                $(this).children("login").text(),
                $(this).children("bindingActive").text() == "true",
                $(this).children("binding").text() );
        }
        else if ( $(this).children("group").text() == "member" )
        {
            member.addUser( $(this).children("id").text(),
                $(this).children("login").text(),
                $(this).children("bindingActive").text() == "true",
                $(this).children("binding").text() );
        }
        else if ( $(this).children("group").text() == "raidlead" )
        {
            raidlead.addUser( $(this).children("id").text(),
                $(this).children("login").text(),
                $(this).children("bindingActive").text() == "true",
                $(this).children("binding").text() );
        }
        else if ( $(this).children("group").text() == "admin" )
        {
            admin.addUser( $(this).children("id").text(),
                $(this).children("login").text(),
                $(this).children("bindingActive").text() == "true",
                $(this).children("binding").text() );
        }
    });

    banned.refreshList();
    member.refreshList();
    raidlead.refreshList();
    admin.refreshList();

    // hide

    $("#groups").hide();
}

// -----------------------------------------------------------------------------

function generateSettingsLocation( a_Message )
{
    var HTMLString = "<div id=\"locationsettings\">";
    HTMLString += "<div class=\"imagelist\" id=\"locationimagelist\">";

    var numImages = 0;

    a_Message.children("locationimage").each( function(index) {

        if ( (numImages + 1) % 11 === 0 )
        {
            HTMLString += "<br/>";
            ++numImages;
        }

        HTMLString += "<img src=\"images/raidsmall/" + $(this).text() + "\" onclick=\"applyLocationImageExternal(this, true)\" style=\"width: 32px; height: 32px; margin-right: 5px;\"/>";
        ++numImages;
    });

    HTMLString += "</div>";
    HTMLString += "</div>";

    $("#settings").append(HTMLString);

    a_Message.children("location").each( function() {

        var locationId = parseInt( $(this).children("id").text(), 10 );

        HTMLString  = "<div class=\"location\" id=\"location_" + locationId + "\">";
        HTMLString += "<span class=\"imagepicker locationimg\" style=\"background-image: url(images/raidsmall/" + $(this).children("image").text() + ")\"></span>";
        HTMLString += "<input class=\"locationname\" type=\"text\" value=\"" + $(this).children("name").text() + "\"/>";
        HTMLString += "<div class=\"deletecmd\" onclick=\"deleteLocation($(this).parent())\"></div>";
        HTMLString += "</div>";

       $("#locationsettings").append(HTMLString);
       $("#location_" + locationId).children(".locationimg:first").data("selectedImage", $(this).children("image").text());
    });


    // setup location

    $("#locationsettings .imagepicker")
        .click( function(event) {
            $("#locationimagelist").data("external", $(this));
            showTooltipRaidImageListAtElement($(this));
            event.stopPropagation();
        });

    $("#locationsettings").data("removed", []);
    
    $(".locationname").change( onUIDataChange ); 

    // hide

    $("#locationimagelist").hide();
    $("#locationsettings").hide();
}

// -----------------------------------------------------------------------------


function generateSettingsRaid( a_Message )
{
    var purgeRaidTime   = 0;
    var purgeRaidMetric = 0;
    var lockRaidTime    = 0;
    var lockRaidMetric  = 0;
    var settings        = [];

    settings["RaidStartHour"]   = { text : "", number : 19 };
    settings["RaidStartMinute"] = { text : "", number : 30 };
    settings["RaidEndHour"]     = { text : "", number : 23 };
    settings["RaidEndMinute"]   = { text : "", number : 0 };
    settings["RaidSize"]        = { text : "", number : 10 };
    settings["Theme"]           = { text : "default", number : 0 };
    settings["Site"]            = { text : "", number : 0 };
    settings["TimeFormat"]      = { text : "", number : 0 };

    a_Message.children("setting").each( function() {

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
            number : parseInt( $(this).children("intValue").text(), 10 )
        };
    });

    var HTMLString = "<div id=\"raidsettings\">";

    HTMLString += "<div class=\"propDeleteRaid\">";
    HTMLString += "<span class=\"propLabel\">" + L("DeleteRaids") + "</span>";
    HTMLString += "<input class=\"timeField\" type=\"text\" id=\"purgeTime\" value=\"\"/>";
    HTMLString += "<select class=\"metricField\" id=\"purgeMetric\">";
    HTMLString += "<option" + ((purgeRaidMetric === 0) ? " selected" : "" ) + ">" + L("Seconds") + "</option>";
    HTMLString += "<option" + ((purgeRaidMetric == 1) ? " selected" : "" ) + ">" + L("Minutes") + "</option>";
    HTMLString += "<option" + ((purgeRaidMetric == 2) ? " selected" : "" ) + ">" + L("Hours") + "</option>";
    HTMLString += "<option" + ((purgeRaidMetric == 3) ? " selected" : "" ) + ">" + L("Days") + "</option>";
    HTMLString += "<option" + ((purgeRaidMetric == 4) ? " selected" : "" ) + ">" + L("Weeks") + "</option>";
    HTMLString += "<option" + ((purgeRaidMetric == 5) ? " selected" : "" ) + ">" + L("Month") + "</option>";
    HTMLString += "</select>";
    HTMLString += "<span class=\"propLabel2\">" + L("AfterDone") + "</span>"
    HTMLString += "</div>";

    HTMLString += "<div class=\"propLockRaid\">";
    HTMLString += "<span class=\"propLabel\">" + L("LockRaids") + "</span>";
    HTMLString += "<input class=\"timeField\" type=\"text\" id=\"lockTime\" value=\"\"/>";
    HTMLString += "<select class=\"metricField\" id=\"lockMetric\">";
    HTMLString += "<option" + ((lockRaidMetric === 0) ? " selected" : "" ) + ">" + L("Seconds") + "</option>";
    HTMLString += "<option" + ((lockRaidMetric == 1) ? " selected" : "" ) + ">" + L("Minutes") + "</option>";
    HTMLString += "<option" + ((lockRaidMetric == 2) ? " selected" : "" ) + ">" + L("Hours") + "</option>";
    HTMLString += "<option" + ((lockRaidMetric == 3) ? " selected" : "" ) + ">" + L("Days") + "</option>";
    HTMLString += "<option" + ((lockRaidMetric == 4) ? " selected" : "" ) + ">" + L("Weeks") + "</option>";
    HTMLString += "<option" + ((lockRaidMetric == 5) ? " selected" : "" ) + ">" + L("Month") + "</option>";
    HTMLString += "</select>";
    HTMLString += "<span class=\"propLabel2\">" + L("BeforeStart") + "</span>"
    HTMLString += "</div>";

    HTMLString += "<div class=\"propTimeFormat\">";
    HTMLString += "<span class=\"propLabel\">" + L("TimeFormat") + "</span>";
    HTMLString += "<select id=\"timeFormat\" style=\"width: " + ((g_TimeFormat == 24) ? 48 : 64) + "px\">";
    HTMLString += "<option value=\"12\"" + ((settings["TimeFormat"].number == 12) ? " selected" : "" ) + ">12h</option>";
    HTMLString += "<option value=\"24\"" + ((settings["TimeFormat"].number == 24) ? " selected" : "" ) + ">24h</option>";
    HTMLString += "</select>";
    HTMLString += "</div>";
    
    HTMLString += "<div class=\"propStartOfWeek\">";
    HTMLString += "<span class=\"propLabel\">" + L("StartOfWeek") + "</span>";
    HTMLString += "<select id=\"startOfWeek\" style=\"width: 160px\">";
    HTMLString += "<option value=\"0\"" + ((settings["StartOfWeek"].number === 0) ? " selected" : "" ) + ">"+L("Sunday")+"</option>";
    HTMLString += "<option value=\"1\"" + ((settings["StartOfWeek"].number == 1) ? " selected" : "" ) + ">"+L("Monday")+"</option>";
    HTMLString += "<option value=\"2\"" + ((settings["StartOfWeek"].number == 2) ? " selected" : "" ) + ">"+L("Tuesday")+"</option>";
    HTMLString += "<option value=\"3\"" + ((settings["StartOfWeek"].number == 3) ? " selected" : "" ) + ">"+L("Wednesday")+"</option>";
    HTMLString += "<option value=\"4\"" + ((settings["StartOfWeek"].number == 4) ? " selected" : "" ) + ">"+L("Thursday")+"</option>";
    HTMLString += "<option value=\"5\"" + ((settings["StartOfWeek"].number == 5) ? " selected" : "" ) + ">"+L("Friday")+"</option>";
    HTMLString += "<option value=\"6\"" + ((settings["StartOfWeek"].number == 6) ? " selected" : "" ) + ">"+L("Saturday")+"</option>";
    HTMLString += "</select>";
    HTMLString += "</div>";

    HTMLString += "<div class=\"propRaidStart\">";
    HTMLString += "<span class=\"propLabel\">" + L("DefaultStartTime") + "</span>";
    HTMLString += "<select id=\"starthour\" style=\"width: " + ((g_TimeFormat == 24) ? 48 : 64) + "px\">";

    for ( i=4; i>=0; --i )
        HTMLString += "<option value=\"" + i + "\"" + ((settings["RaidStartHour"].number == i) ? " selected" : "" ) + ">" + formatHourPrefixed(i) + "</option>";

    for ( i=23; i>4; --i )
        HTMLString += "<option value=\"" + i + "\"" + ((settings["RaidStartHour"].number == i) ? " selected" : "" ) + ">" + formatHourPrefixed(i) + "</option>";

    HTMLString += "</select><span>&nbsp;:&nbsp;</span>";
    HTMLString += "<select id=\"startminute\" style=\"width: 48px\">";
    HTMLString += "<option value=\"0\"" + ((settings["RaidStartMinute"].number === 0) ? " selected" : "" ) + ">00</option>";
    HTMLString += "<option value=\"15\"" + ((settings["RaidStartMinute"].number == 15) ? " selected" : "" ) + ">15</option>";
    HTMLString += "<option value=\"30\"" + ((settings["RaidStartMinute"].number == 30) ? " selected" : "" ) + ">30</option>";
    HTMLString += "<option value=\"45\"" + ((settings["RaidStartMinute"].number == 45) ? " selected" : "" ) + ">45</option>";
    HTMLString += "</select>";
    HTMLString += "</div>";

    HTMLString += "<div class=\"propRaidEnd\">";
    HTMLString += "<span class=\"propLabel\">" + L("DefaultEndTime") + "</span>";
    HTMLString += "<select id=\"endhour\" style=\"width: " + ((g_TimeFormat == 24) ? 48 : 64) + "px\">";

    for ( i=4; i>=0; --i )
        HTMLString += "<option value=\"" + i + "\"" + ((settings["RaidEndHour"].number == i) ? " selected" : "" ) + ">" + formatHourPrefixed(i) + "</option>";

    for ( i=23; i>4; --i )
        HTMLString += "<option value=\"" + i + "\"" + ((settings["RaidEndHour"].number == i) ? " selected" : "" ) + ">" + formatHourPrefixed(i) + "</option>";

    HTMLString += "</select><span>&nbsp;:&nbsp;</span>";
    HTMLString += "<select id=\"endminute\" style=\"width: 48px\">";
    HTMLString += "<option value=\"0\"" + ((settings["RaidEndMinute"].number === 0) ? " selected" : "" ) + ">00</option>";
    HTMLString += "<option value=\"15\"" + ((settings["RaidEndMinute"].number == 15) ? " selected" : "" ) + ">15</option>";
    HTMLString += "<option value=\"30\"" + ((settings["RaidEndMinute"].number == 30) ? " selected" : "" ) + ">30</option>";
    HTMLString += "<option value=\"45\"" + ((settings["RaidEndMinute"].number == 45) ? " selected" : "" ) + ">45</option>";
    HTMLString += "</select>";
    HTMLString += "</div>";

    HTMLString += "<div class=\"propRaidSize\">";
    HTMLString += "<span class=\"propLabel\">" + L("DefaultRaidSize") + "</span>";
    HTMLString += "<select id=\"raidsize\" style=\"width: " + ((g_TimeFormat == 24) ? 48 : 64) + "px\">";

    for (var i=0; i<g_GroupSizes.length; ++i)
    {
        HTMLString += "<option value=\""+g_GroupSizes[i]+"\"" + ((settings["RaidSize"].number == g_GroupSizes[i]) ? " selected" : "" ) + ">"+g_GroupSizes[i]+"</option>";
    }

    HTMLString += "</select>";
    HTMLString += "</div>";

    HTMLString += "<div class=\"propRaidMode\">";
    HTMLString += "<span class=\"propLabel\">" + L("DefaultRaidMode") + "</span>";
    HTMLString += "<select id=\"raidmode\" style=\"width: 160px\">";
    HTMLString += "<option value=\"manual\""+((settings["RaidMode"].text=="manual") ? " selected" : "")+">"+L("RaidModeManual")+"</option>";
    HTMLString += "<option value=\"attend\""+((settings["RaidMode"].text=="attend") ? " selected" : "")+">"+L("RaidModeAttend")+"</option>";
    HTMLString += "<option value=\"all\""+((settings["RaidMode"].text=="all") ? " selected" : "")+">"+L("RaidModeAll")+"</option>";
    HTMLString += "</select>";
    HTMLString += "</div>";

    HTMLString += "<div class=\"propSite\">";
    HTMLString += "<span class=\"propLabel\">" + L("BannerPage") + "</span>";
    HTMLString += "<input class=\"urlField\" type=\"text\" id=\"site\" style=\"width: 155px\" value=\"" + settings["Site"].text + "\"/>";
    HTMLString += "</div>";

    HTMLString += "<div class=\"propTheme\">";
    HTMLString += "<span class=\"propLabel\">" + L("Theme") + "</span>";
    HTMLString += "<select id=\"theme\" style=\"width: 160px\">";

    a_Message.children("theme").each( function() {
        var name = $(this).children("name:first").text();
        var file = $(this).children("file:first").text();
        HTMLString += "<option value=\"" + file + "\"" + ((settings["Theme"].text == file) ? " selected" : "" ) + ">" + name + "</option>";
    });

    HTMLString += "</select>";
    HTMLString += "</div>";

    HTMLString += "</div>";

    $("#settings").append(HTMLString);

    // setup raid settings

    $("#purgeTime").val( purgeRaidTime );
    $("#lockTime").val( lockRaidTime );
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

function generateSettingsStats( a_Message )
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

    var numRaids = parseInt( a_Message.children("numRaids").text(), 10 );
    var barSize  = 740;

    HTMLString += "<div style=\"clear: left; padding-top: 15px\">";

    a_Message.children("attendance").each( function() {

        HTMLString += "<div style=\"clear: left\">";
        HTMLString += "<div class=\"name\">" + $(this).children("name").text() + "</div>";
        HTMLString += "<div class=\"bar\"><span class=\"start_bar\"></span>";

        var numOk      = parseInt( $(this).children("ok").text(), 10 );
        var numAvail   = parseInt( $(this).children("available").text(), 10 );
        var numUnavail = parseInt( $(this).children("unavailable").text(), 10 );
        var numMissed  = numRaids - (numOk + numAvail + numUnavail);

        var sizeOk      = (numOk / numRaids) * barSize;
        var sizeAvail   = (numAvail / numRaids) * barSize;
        var sizeUnavail = (numUnavail / numRaids) * barSize;
        var sizeMissed  = (numMissed / numRaids) * barSize;

        if (numOk > 0)      HTMLString += "<span class=\"ok\" style=\"width: " + sizeOk.toFixed() + "px\"><div class=\"count\">" + numOk + "</div></span>";
        if (numAvail > 0)   HTMLString += "<span class=\"available\" style=\"width: " + sizeAvail.toFixed() + "px\"><div class=\"count\">" + numAvail + "</div></span>";
        if (numUnavail > 0) HTMLString += "<span class=\"unavailable\" style=\"width: " + sizeUnavail.toFixed() + "px\"><div class=\"count\">" + numUnavail + "</div></span>";
        if (numMissed > 0)  HTMLString += "<span class=\"missed\" style=\"width: " + sizeMissed.toFixed() + "px\"><div class=\"count\">" + numMissed + "</div></span>";
        if (numRaids === 0)  HTMLString += "<span class=\"missed\" style=\"width: " + barSize + "px\"><div class=\"count\">&nbsp;</div></span>";

        HTMLString += "<span class=\"end\"></span></div></div>";
    });

    HTMLString += "</div>";
    HTMLString += "</div>";

    $("#settings").append(HTMLString);
    $("#statistics").hide();
}

// -----------------------------------------------------------------------------

function generateSettingsAbout( a_Message )
{
    var HTMLString = "<div id=\"about\">";
    var patchLevel = parseInt(Math.ceil((g_SiteVersion - parseInt(g_SiteVersion, 10))*10), 10);
    var patchLevelChar = (patchLevel === 0) ? "" : String.fromCharCode( "a".charCodeAt(0) + (patchLevel-1) );

    HTMLString += "<div class=\"version\">";
    HTMLString += "Version " + parseInt(g_SiteVersion / 100, 10) + "." + parseInt((g_SiteVersion % 100) / 10, 10) + "." + parseInt(g_SiteVersion % 10, 10) + patchLevelChar + "<br/>";
    HTMLString += "<button id=\"update_check\">" + L("UpdateCheck") + "</button>";
    HTMLString += "</div>";

    $("#settings").append(HTMLString);
    $("#update_check").button({ icons: { secondary: "ui-icon-arrowreturnthick-1-n" }})
        .click( function() { triggerUpdateCheck(); } )
        .css( "font-size", 11 )
        .css( "margin-top", 15 );
}

// -----------------------------------------------------------------------------

function generateSettings( a_XMLData )
{
    var Message = $(a_XMLData).children("messagehub");
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

    generateSettingsUsers( Message );
    generateSettingsLocation( Message );
    generateSettingsRaid( Message );
    generateSettingsStats( Message );
    generateSettingsAbout( Message );

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

    loadSettingsPanel( Message.children("show").text() );

    $("#settings").show();
}

// -----------------------------------------------------------------------------

function triggerSettingsUpdate()
{
    if ( (gUser == null) || !gUser.isAdmin )
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
    
    var unlinkedArray = [];
    var relinkedArray = [];
    
    banned.updateLinkageArrays(unlinkedArray, relinkedArray);
    member.updateLinkageArrays(unlinkedArray, relinkedArray);
    raidlead.updateLinkageArrays(unlinkedArray, relinkedArray);
    admin.updateLinkageArrays(unlinkedArray, relinkedArray);    

    var purgeRaidTime = calculateUnixTime( $("#purgeTime").val(), $("#purgeMetric")[0].selectedIndex );
    var lockRaidTime  = calculateUnixTime( $("#lockTime").val(), $("#lockMetric")[0].selectedIndex );

    var locationIdData    = [];
    var locationNameData  = [];
    var locationImageData = [];

    $("#locationsettings").children(".location").each( function() {

        var locationId = $(this).attr("id");
        locationId = parseInt(locationId.substring(9,locationId.length), 10);

        locationIdData.push( locationId );
        locationNameData.push( $(this).children(".locationname").val() );
        locationImageData.push( $(this).children(".imagepicker").data("selectedImage") );
    });

    var hash    = window.location.hash.substring( 1, window.location.hash.length );
    var idIndex = hash.lastIndexOf(",");
    
    var parameters = {
        banned          : bannedArray,
        member          : memberArray,
        raidlead        : raidleadArray,
        admin           : adminArray,        
        removed         : removedArray,
        unlinked        : unlinkedArray,
        relinked        : relinkedArray,
        locationIds     : locationIdData,
        locationNames   : locationNameData,
        locationImages  : locationImageData,
        locationRemoved : $("#locationsettings").data("removed"),
        purgeTime       : purgeRaidTime,
        lockTime        : lockRaidTime,
        timeFormat      : $("#timeFormat").val(),
        startOfWeek     : $("#startOfWeek").val(),
        raidStartHour   : $("#starthour").val(),
        raidStartMinute : $("#startminute").val(),
        raidEndHour     : $("#endhour").val(),
        raidEndMinute   : $("#endminute").val(),
        raidSize        : $("#raidsize").val(),
        raidMode        : $("#raidmode").val(),
        site            : $("#site").val(),
        theme           : $("#theme").val(),
        showPanel       : hash.substr(idIndex+1)
    };

    onAppliedUIDataChange();
    
    asyncQuery( "settings_update", parameters, function() {
        $.getScript("lib/script/config.js.php?version=" + g_SiteVersion, function() {
            loadSettings(parameters.showPanel);
            onChangeConfig();
        });
    });
}

// -----------------------------------------------------------------------------

function onUpdateCheckReturn( a_JSONData )
{
    $("#update_message").detach();

    if ( g_SiteVersion < a_JSONData.version )
    {
        $("#about .version").css("color", "#AA0000");
        $("#update_check").before( "<div id=\"update_message\"><a href=\"http://code.google.com/p/ppx-raidplaner/downloads/list\" style=\"font-size: 12px\">" + L("VisitProjectPage") + "</a><br/><div>" );

        notify( L("NewVersionAvailable") + "<br/><span style=\"font-size: 26px\">" + a_JSONData.major + "." + a_JSONData.minor + "." + a_JSONData.patch + "</span>" );
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

function loadSettings( a_Name )
{
    reloadUser();

    if ( (gUser == null) || !gUser.isAdmin )
        return;

    $("#body").empty();

    var Parameters = {
       showPanel : a_Name
    };

    asyncQuery( "query_settings", Parameters, generateSettings );
}

// -----------------------------------------------------------------------------

function loadSettingsPanel( a_Name )
{
    if ( $("#settings").length === 0 )
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

        case "about":
            showPanel("#about", "about");
            break;
        }
    }
}

// -----------------------------------------------------------------------------

function loadForeignProfile( a_Id )
{
    reloadUser();

    if ( (gUser == null) || !gUser.isAdmin )
        return;

    $("#body").empty();

    var Parameters = {
        id : a_Id
    };

    asyncQuery( "query_profile", Parameters, renderForeignProfile );
}