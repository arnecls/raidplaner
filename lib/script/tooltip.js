// -----------------------------------------------------------------------------
//    Generic tooltip functions
// -----------------------------------------------------------------------------

function delayedFadeTooltip()
{
    if ( $("#tooltip").data("sticky") == false )
    {
        $("#tooltip").delay(200).fadeOut(100);
    }
}

// -----------------------------------------------------------------------------

function startFadeTooltip()
{
    $("#tooltip").fadeOut(100);

    if ( $("#tooltip").data("onHide") != null )
    {
       $("#tooltip").data("onHide")();
       $("#tooltip").data("onHide", null);
    }
}


// -----------------------------------------------------------------------------

function hideTooltip()
{
	$("#tooltip").stop(true, true).clearQueue().hide();

    if ( $("#tooltip").data("onHide") != null )
    {
       $("#tooltip").data("onHide")();
       $("#tooltip").data("onHide", null);
    }
}

// -----------------------------------------------------------------------------

function showTooltip()
{
    var tooltip = $("#tooltip");
    tooltip.stop(true, true).clearQueue().show();

    tooltip.unbind("mouseover");
    tooltip.unbind("mouseout");

    if ( !tooltip.data("sticky") )
    {
        tooltip.mouseover( showTooltip );
        tooltip.mouseout( delayedFadeTooltip );
    }
}

// -----------------------------------------------------------------------------

function keepTooltipAlive()
{
    var tooltip = $("#tooltip");
    if ( tooltip.is(":visible") )
        tooltip.stop(true, true).clearQueue().show();
}

// -----------------------------------------------------------------------------

function showNewTooltip()
{
    showTooltip();

    if ( /msie/.test(navigator.userAgent.toLowerCase()) )
    {
        resetTooltipArrows();
	}
}

// -----------------------------------------------------------------------------

function toggleStickyState( a_Element, a_MakeSticky )
{
    var tooltip = $("#tooltip");

    var elementId  = a_Element.attr("id");
    var tooltipId  = $("#tooltip").data("id");
    var matchingId = elementId == tooltipId;

    if ( tooltip.is(":visible") )
    {
        // Tooltip is (partially) visible, toggle sticky state if requested

        if ( a_MakeSticky )
        {
            if ( tooltip.data("sticky") )
            {
                tooltip.data("sticky", false);
                tooltip.fadeOut(100);
            }
            else
            {
                // This may happen during fade out
                a_Element.unbind("mouseout");
                tooltip.data("sticky", true);
                showTooltip();
            }
        }
        else
        {
            if ( !tooltip.data("sticky") )
            {
                a_Element.unbind("mouseout");
                a_Element.mouseout( delayedFadeTooltip );

                if ( !matchingId )
                {
                    tooltip.data("id", elementId);
                    return false; // ### return, switching tooltips by mouseover ###
                }
            }

            showTooltip();
        }

        return true; // ### return, visible ###
    }

    // Tooltip is not visible, init sticky state

    a_Element.unbind("mouseout");
    tooltip.data("id", elementId);

    if ( a_MakeSticky )
    {
        tooltip.data("sticky", true);
    }
    else
    {
        tooltip.data("sticky", false);
        a_Element.mouseout( delayedFadeTooltip );
    }

    return false;
}

// -----------------------------------------------------------------------------

function resetTooltipArrows()
{
    $("#info_arrow_tl").width( "auto" );
    $("#info_arrow_tr").width( "auto" );
    $("#info_arrow_bl").width( "auto" );
    $("#info_arrow_br").width( "auto" );
    $("#info_arrow_ml").height( "auto" );
    $("#info_arrow_ml2").height( "auto" );
}

// -----------------------------------------------------------------------------

function setTooltipArrowTopLeft()
{
    if ( /msie/.test(navigator.userAgent.toLowerCase()) )
    {
        resetTooltipArrows();

        $("#info_arrow_tl").width( 36 );
        $("#info_arrow_tr").width( $("#info_text").width() - 36 );
    }
    else
    {
        $("#info_arrow_tl").width( 36 );
        $("#info_arrow_tr").width( "auto" );
        $("#info_arrow_bl").width( 36 );
        $("#info_arrow_br").width( "auto" );
    }

    $("#info_arrow_tl").removeClass( "center" ) .addClass( "arrow" );
    $("#info_arrow_tr").removeClass( "arrowtr" ).addClass( "center" );
    $("#info_arrow_bl").removeClass( "arrow" )  .addClass( "center" );
    $("#info_arrow_br").removeClass( "arrow" )  .addClass( "center" );
    $("#info_arrow_ml").removeClass( "arrow" )  .addClass( "left" );
}

// -----------------------------------------------------------------------------

function setTooltipArrowTopRight()
{
    if ( /msie/.test(navigator.userAgent.toLowerCase()) )
    {
        resetTooltipArrows();

        $("#info_arrow_tl").width( $("#info_text").width() - 62 );
        $("#info_arrow_tr").width( 62 );
    }
    else
    {
        $("#info_arrow_tl").width( "auto" );
        $("#info_arrow_tr").width( 62 );
        $("#info_arrow_bl").width( "auto" );
        $("#info_arrow_br").width( 62 );
    }

    $("#info_arrow_tl").removeClass( "arrow" ) .addClass( "center" );
    $("#info_arrow_tr").removeClass( "center" ).addClass( "arrowtr" );
    $("#info_arrow_bl").removeClass( "arrow" ) .addClass( "center" );
    $("#info_arrow_br").removeClass( "arrow" ) .addClass( "center" );
    $("#info_arrow_ml").removeClass( "arrow" ) .addClass( "left" );
}

// -----------------------------------------------------------------------------

function setTooltipArrowBottomLeft()
{
    if ( /msie/.test(navigator.userAgent.toLowerCase()) )
    {
        resetTooltipArrows();

        $("#info_arrow_tl").width( 36 );
        $("#info_arrow_tr").width( $("#info_text").width() - 36 );
        $("#info_arrow_bl").width( 36 );
        $("#info_arrow_br").width( $("#info_text").width() - 36 );
    }
    else
    {
        $("#info_arrow_tl").width( 36 );
        $("#info_arrow_tr").width( "auto" );
        $("#info_arrow_bl").width( 36 );
        $("#info_arrow_br").width( "auto" );
    }

    $("#info_arrow_tl").removeClass( "arrow" )  .addClass( "center" );
    $("#info_arrow_tr").removeClass( "arrowtr" ).addClass( "center" );
    $("#info_arrow_bl").removeClass( "center" ) .addClass( "arrow" );
    $("#info_arrow_br").removeClass( "arrow" )  .addClass( "center" );
    $("#info_arrow_ml").removeClass( "arrow" )  .addClass( "left" );
}

// -----------------------------------------------------------------------------

function setTooltipArrowBottomRight()
{
    if ( /msie/.test(navigator.userAgent.toLowerCase()) )
    {
        resetTooltipArrows();

        $("#info_arrow_tl").width( $("#info_text").width() - 36 );
        $("#info_arrow_tr").width( 36 );
        $("#info_arrow_bl").width( $("#info_text").width() - 36 );
        $("#info_arrow_br").width( 36 );
    }
    else
    {
        $("#info_arrow_tl").width( "auto" );
        $("#info_arrow_tr").width( 36 );
        $("#info_arrow_bl").width( "auto" );
        $("#info_arrow_br").width( 36 );
    }

    $("#info_arrow_tl").removeClass( "arrow" )  .addClass( "center" );
    $("#info_arrow_tr").removeClass( "arrowtr" ).addClass( "center" );
    $("#info_arrow_bl").removeClass( "arrow" )  .addClass( "center" );
    $("#info_arrow_br").removeClass( "center" ) .addClass( "arrow" );
    $("#info_arrow_ml").removeClass( "arrow" )  .addClass( "left" );
}

// -----------------------------------------------------------------------------

function setTooltipArrowLeft()
{
    if ( /msie/.test(navigator.userAgent.toLowerCase()) )
    {
        resetTooltipArrows();

        $("#info_arrow_ml").height( 45 );
        $("#info_arrow_ml2").height( $("#info_text").height() - 45 );
    }
    else
    {
        $("#info_arrow_ml").height( 45 );
        $("#info_arrow_ml").height( "auto" );
    }

    $("#info_arrow_tl").removeClass( "arrow" )  .addClass( "center" ).css( "width", "auto" );
    $("#info_arrow_tr").removeClass( "arrowtr" ).addClass( "center" ).css( "width", "auto" );
    $("#info_arrow_bl").removeClass( "arrow" )  .addClass( "center" ).css( "width", "auto" );
    $("#info_arrow_br").removeClass( "arrow" )  .addClass( "center" ).css( "width", "auto" );
    $("#info_arrow_ml").removeClass( "left" )   .addClass( "arrow" ) .css( "width", 21 );
}

// -----------------------------------------------------------------------------
//    RaidInfo tooltip
// -----------------------------------------------------------------------------

function showTooltipRaidInfoForId( a_RaidId, a_CalendarView )
{
    showTooltipRaidInfo( $("#raid" + a_RaidId), a_CalendarView, $("#tooltip").data("sticky") );
}

// -----------------------------------------------------------------------------

function showTooltipRaidInfo( a_ParentElement, a_CalendarView, a_MakeSticky )
{
    if ( g_User == null )
        return;

    if ( !toggleStickyState(a_ParentElement, a_MakeSticky) )
    {
        var raidElement = a_ParentElement;
        var container = $("#info_text");
        var tooltip = $("#tooltip");
        var elementOffset = raidElement.offset();

        var pageCenterX = $(document).width() / 2;
        var pageCenterY = $(document).height() / 2;

        var infoClone = raidElement.children(".tooltip").clone();
        container.empty().append( infoClone );

        showNewTooltip();
        
        var selectField = infoClone.children(".functions").children("select");

        if ( selectField.size() > 0 )
            selectField.attr( "id", "active" + selectField.attr("id").substr(6) );

        selectField.combobox({ darkBackground: true });

        elementOffset.left += raidElement.width() / 2;
        elementOffset.top  += raidElement.height() / 2;

        if ( a_CalendarView )
        {
            if ( elementOffset.top < pageCenterY )
            {
                elementOffset.top += raidElement.height() / 2;

                if ( elementOffset.left < pageCenterX )
                {
                    elementOffset.left -= 40;

                    tooltip.offset( elementOffset );
                    setTooltipArrowTopLeft();
                }
                else
                {
                    elementOffset.left -= tooltip.width() + raidElement.width() / 2 - 76;

                    $("#tooltip").offset( elementOffset );
                    setTooltipArrowTopRight();
                }
            }
            else
            {
                elementOffset.top -= tooltip.height() + raidElement.height() / 2;

                if ( elementOffset.left < pageCenterX )
                {
                    elementOffset.left -= 40;

                    tooltip.offset( elementOffset );
                    setTooltipArrowBottomLeft();
                }
                else
                {
                    elementOffset.left -= tooltip.width() + raidElement.width() / 2 - 49;

                    tooltip.offset( elementOffset );
                    setTooltipArrowBottomRight();
                }
            }
        }
        else
        {
            elementOffset.left += raidElement.width() / 2 - 5;
            elementOffset.top  -= raidElement.height() / 2 + 10;
			
			tooltip.offset( elementOffset );
            setTooltipArrowLeft();
        }
    }
}

// -----------------------------------------------------------------------------

function displayRaidTooltipInfo( a_RaidId )
{
    var raidElement = $("#raid"+ a_RaidId);
    var container = $("#info_text");

    var infoClone = raidElement.children(".tooltip").clone();
    container.empty().append( infoClone );

    var selectField = infoClone.children(".functions").children("select");

    if ( selectField.size() > 0 )
        selectField.attr( "id", "active" + selectField.attr("id").substr(6) );

    selectField.combobox({ darkBackground: true });
}

// -----------------------------------------------------------------------------

function displayRaidTooltipComment( a_RaidId )
{
    var raidElement = $("#raid"+ a_RaidId);
    var container = $("#info_text");
    var oldWidth = $("#tooltip").width();

    var infoClone = raidElement.children(".comment").clone();
    container.empty().append( infoClone );

    container.children(".comment").children(".text").css( "width", oldWidth - 43 );
    container.children(".comment").children("button").button({ icons: { secondary: "ui-icon-disk" }})
        .css( "font-size", 11 )
        .css( "height", 24 );
}

// -----------------------------------------------------------------------------

function commitAttend( a_Index, a_Raid, a_CalendarView, a_CommentFromPrompt )
{
    var Parameters = {
        attendanceIndex : a_Index,
        fallback        : 0,
        raidId          : a_Raid,
        comment         : a_CommentFromPrompt ? $("#prompt_text").val() : ""
    };

    AsyncQuery( "raid_attend", Parameters, ( a_CalendarView ) ? displayCalendar : loadAllRaids );
}

// -----------------------------------------------------------------------------

function triggerAttend( selectObj, a_CalendarView )
{
    if ( g_User == null )
        return;

    var comment = "";
    var unattend = false;

    if ( selectObj.value == -1 )
    {
        prompt( L("WhyAbsent"), L("SetAbsent"), L("Cancel"), function() {
            commitAttend( selectObj.value, selectObj.id.substr(6), a_CalendarView, true );
        });
    }
    else
    {
        commitAttend( selectObj.value, selectObj.id.substr(6), a_CalendarView, false );
    }

    startFadeTooltip();
}

// -----------------------------------------------------------------------------

function triggerUpdateComment( a_ButtonElement, a_RaidId, a_CalendarView )
{
    if ( g_User == null )
        return;

    var Parameters = {
        raidId  : a_RaidId,
        comment : a_ButtonElement.parent().children(".text").val()
    };

    AsyncQuery( "comment_update", Parameters, ( a_CalendarView ) ? displayCalendar : loadAllRaids );
}

// -----------------------------------------------------------------------------
//    RaidImageList tooltip
// -----------------------------------------------------------------------------

function applyLocationImage( a_ImageObj, a_UIDataChange )
{
    var image = a_ImageObj.src.substr( a_ImageObj.src.lastIndexOf("/") + 1 );
    $("#locationimagepicker").css( "background-image", "url(images/raidbig/" + image + ")" );
    $("#locationimagepicker").data( "selectedImage", image );
    
    if (a_UIDataChange)
        onUIDataChange();

    startFadeTooltip();
}

// -----------------------------------------------------------------------------

function applyLocationImageExternal( a_ImageObj, a_UIDataChange )
{
    var image = a_ImageObj.src.substr( a_ImageObj.src.lastIndexOf("/") + 1 );
    var element = $("#locationimagelist").data("external");

    element.css( "background-image", "url(images/raidsmall/" + image + ")" );
    element.data( "selectedImage", image );
    
    if (a_UIDataChange)
        onUIDataChange();
    
    startFadeTooltip();
}

// -----------------------------------------------------------------------------

function showTooltipRaidImageListAtElement( a_Element )
{
    var tooltip = $("#tooltip");
    tooltip.data("sticky", true);

    if ( tooltip.is(":visible") )
    {
        tooltip.fadeOut(100);
    }
    else
    {
        var container = $("#info_text");
        container.empty().append( $("#locationimagelist").children().clone() );

        showNewTooltip();

        var elementOffset = a_Element.offset();

        elementOffset.left -= (a_Element.width() > 32) ? 6 : 24;
        elementOffset.top += a_Element.height() - 2;

        tooltip.offset( elementOffset );
        setTooltipArrowTopLeft();
    }
}

// -----------------------------------------------------------------------------

function showTooltipRaidImageList(a_TriggerDataChange)
{
    showTooltipRaidImageListAtElement( $("#locationimagepicker") );
}

// -----------------------------------------------------------------------------
//  comment tooltip
// -----------------------------------------------------------------------------

function showAttendeeTooltip( a_SlotElement, a_Image, a_Name, a_Text, a_MakeSticky, a_OnHide )
{
    if ( !toggleStickyState(a_SlotElement, a_MakeSticky)  )
    {
        var tooltip = $("#tooltip");
        tooltip.data("onHide", a_OnHide);

        var container = $("#info_text");
        var elementOffset = a_SlotElement.offset();
        var pageCenterX = $(document).width() / 2;
        var pageCenterY = $(document).height() / 2;

        var HTMLString = "<div class=\"playerTooltipIcon\" style=\"background-image: url('"+a_Image+"')\"></div>";
        HTMLString    += "<div class=\"playerTooltipText\"><div class=\"name\">"+a_Name+"</div>"+a_Text+"</div>";

        container.empty().append( HTMLString );

        showNewTooltip();

        elementOffset.left += a_SlotElement.width() / 2 + 1;
        elementOffset.top  += a_SlotElement.height() / 2 - (tooltip.height() + 15);

        if ( elementOffset.left < pageCenterX )
        {
            elementOffset.left -= 40;

            tooltip.offset( elementOffset );
            setTooltipArrowBottomLeft();
        }
        else
        {
            elementOffset.left -= tooltip.width() + a_SlotElement.width() / 2 - 49;

            tooltip.offset( elementOffset );
            setTooltipArrowBottomRight();
        }
    }
}

// -----------------------------------------------------------------------------

function showSlackersTooltip( a_SlotElement, a_Player, a_Undecided, a_MakeSticky )
{
    // The declaration of the a_Player struct can be found in raid.js : AddPlayer()

    if ( !toggleStickyState(a_SlotElement, a_MakeSticky)  )
    {
        var tooltip = $("#tooltip");
        tooltip.data("onHide", null);

        var container = $("#info_text");
        var elementOffset = a_SlotElement.offset();
        var pageCenterX = $(document).width() / 2;
        var pageCenterY = $(document).height() / 2;
        var HTMLString = "";

        var iconClass = "playerTooltipIconLarge";
        if ( a_Player.twinks.length > 0 )
          iconClass += " clickable";

        HTMLString += "<div style=\"float: left\">"
        HTMLString += "<div class=\""+iconClass+"\" style=\"background-image: url('images/classesbig/"+a_Player.className+".png')\">";

        if ( a_Player.twinks.length > 0 )
        {
            HTMLString += "<div class=\"twinkcount\">1/"+(a_Player.twinks.length+1)+"</div>";
        }

        HTMLString += "<div class=\"mainbadge\"></div>";
        HTMLString += "</div>";
        HTMLString += "<div style=\"float: left\">";
        HTMLString += "<div class=\"playerTooltipRoleIcon\" style=\"background-image: url('images/roles/"+g_RoleIdents[a_Player.firstRole]+".png')\"></div>";
        HTMLString += "<div class=\"playerTooltipRoleIcon secondaryAlpha\" style=\"background-image: url('images/roles/"+g_RoleIdents[a_Player.secondRole]+".png')\"></div>";
        HTMLString += "</div>";
        HTMLString += "<div class=\"playerTooltipText\">";
        HTMLString += "<div class=\"name\">"+a_Player.name+"</div>";

        if ( a_Player.comment.length == 0 )
            HTMLString += (a_Undecided) ? L("Undecided") : L("AbsentNoReason") + "</div>";
        else
            HTMLString += a_Player.comment+"</div>";

        container.empty().append( HTMLString );

        // Configure character chooser

        if ( a_Player.twinks.length > 0 )
        {
            var characters = Array();

            characters.push({
                id         : a_Player.id,
                name       : a_Player.name,
                className  : a_Player.className,
                firstRole  : a_Player.firstRole,
                secondRole : a_Player.secondRole
            });

            for ( var i=0; i<a_Player.twinks.length; ++i )
            {
                characters.push( a_Player.twinks[i] );
            }

            $(".playerTooltipIconLarge").data("index",0);


            $(".playerTooltipIconLarge").click( function() {
                var charIdx = $(this).data("index")+1;

                if ( charIdx >= characters.length )
                    charIdx = 0;

                $(this).data("index",charIdx);
                $(this).css("background-image", "url(images/classesbig/"+characters[charIdx].className+".png)");
                $(this).siblings().children(".playerTooltipRoleIcon").first().css("background-image", "url(images/roles/"+g_RoleIdents[characters[charIdx].firstRole]+".png)");
                $(this).siblings().children(".playerTooltipRoleIcon").last().css("background-image", "url(images/roles/"+g_RoleIdents[characters[charIdx].secondRole]+".png)");

                HTMLString = "<div class=\"twinkcount\">"+(charIdx+1)+"/"+(a_Player.twinks.length+1)+"</div>";
                
                if ( charIdx == 0 )
                    HTMLString += "<div class=\"mainbadge\"></div>";
                
                $(this).empty().append( HTMLString );                
                $(".playerTooltipText .name").empty().append( characters[charIdx].name );
            });
        }

        // Show tooltip

        showNewTooltip();

        elementOffset.left += a_SlotElement.width() / 2 + 1;
        elementOffset.top  += a_SlotElement.height() / 2 - (tooltip.height() + 15);

        if ( elementOffset.left < pageCenterX )
        {
            elementOffset.left -= 40;

            tooltip.offset( elementOffset );
            setTooltipArrowBottomLeft();
        }
        else
        {
            elementOffset.left -= tooltip.width() + a_SlotElement.width() / 2 - 49;

            tooltip.offset( elementOffset );
            setTooltipArrowBottomRight();
        }
    }
}

// -----------------------------------------------------------------------------
//  Class/role selector tooltip
// -----------------------------------------------------------------------------

function showTooltipClassList( a_ParentElement )
{
    var tooltip = $("#tooltip");
    tooltip.data("sticky", true);

    if ( tooltip.is(":visible") )
    {
        tooltip.fadeOut(100);
    }
    else
    {
        var container = $("#info_text");
        var elementOffset = a_ParentElement.offset();

        var pageCenterX = $(document).width() / 2;
        var pageCenterY = $(document).height() / 2;

        var HTMLString = "<span><div>";

        for ( i=1; i<g_Classes.length; ++i )
        {
            if ( (i>1) && ((i-1)%5 == 0) )
                HTMLString += "</div><div style=\"clear: left\">";

            HTMLString += "<span class=\"class_select\" id=\"cs_" + g_Classes[i].ident + "\"><img src=\"images/classessmall/" + g_Classes[i].ident + ".png\"/><br/>" + g_Classes[i].text + "</span>";
        }

        HTMLString +=  "</div></span>";

        container.empty().append( HTMLString );

        $(".class_select").click( function() {
            var classIdent = $(this).attr("id").substr(3);
            var classDesc = g_Classes[g_ClassIdx[classIdent]];
            var defaultRoleId = g_RoleIds[ classDesc.defaultRole ];
            var defaultRoleIdent = classDesc.defaultRole;
            
            var allowedRoles = new Array();
            for ( var i=0; i<classDesc.roles.length; ++i )
            {
                allowedRoles.push(g_RoleIds[classDesc.roles[i]]);
                roleIdents.push(classDesc.roles[i]);
            }
            
            a_ParentElement.css("background", "url(images/classesbig/" + classIdent + ".png)" );
            
            var roleDiv = a_ParentElement.nextAll("div:first");
            
            var charIdx = parseInt( a_ParentElement.parent().attr("id").substr(4) );
            var charList = $("#charlist").data("characters");
            var character = charList.Characters[charIdx];
            
            character.charClass = classIdent;
            
            if ( allowedRoles.indexOf(character.role1) == -1 )
            {
                character.role1 = defaultRoleId;
                roleDiv.children(".newrole1").css("background-image", "url(images/roles/" + defaultRoleIdent + ".png)");
            }
            
            if ( allowedRoles.indexOf(character.role2) == -1 )
            {
                character.role2 = defaultRoleId;
                roleDiv.children(".newrole2").css("background-image", "url(images/roles/" + defaultRoleIdent + ".png)");
            }
            
            hideTooltip();                
            onUIDataChange();
        });

        showNewTooltip();
        
        elementOffset.top  += a_ParentElement.height() / 2 - 43;
        elementOffset.left += a_ParentElement.width() / 2 + 22;

        tooltip.offset( elementOffset );
        setTooltipArrowLeft();
    }
}

// -----------------------------------------------------------------------------

function showTooltipRoleList( a_ParentElement, a_EditRole1 )
{
    var tooltip = $("#tooltip");
    tooltip.data("sticky", true);

    if ( tooltip.is(":visible") )
    {
        tooltip.fadeOut(100);
    }
    else
    {
        var container = $("#info_text");
        var tooltip = $("#tooltip");
        var elementOffset = a_ParentElement.offset();

        var pageCenterX = $(document).width() / 2;
        var pageCenterY = $(document).height() / 2;

        var charIdx = parseInt( a_ParentElement.parent().parent().attr("id").substr(4) );
        var charList = $("#charlist").data("characters");        
        var classString = charList.Characters[charIdx].charClass;

        var HTMLString = "<span>";

        var classData = g_Classes[g_ClassIdx[classString]];

        for ( var i=0; i<classData.roles.length; ++i )
        {
            var roleIdent = classData.roles[i];
            HTMLString += "<span class=\"class_select\" id=\"cs_" + roleIdent + "\"><img src=\"images/roles/" + roleIdent + ".png\"/><br/>" + g_RoleNames[ roleIdent ] + "</span>";
        }

        HTMLString +=  "</span>";

        container.empty().append( HTMLString );

        $(".class_select").click( function() {
            var roleIdent = $(this).attr("id").substr(3);

            a_ParentElement.css("background-image", "url(images/roles/" + roleIdent + ".png)" );
            
            if ( a_EditRole1 )
                charList.Characters[charIdx].role1 = g_RoleIds[roleIdent];
            else
                charList.Characters[charIdx].role2 = g_RoleIds[roleIdent];

            hideTooltip();                
            onUIDataChange();
        });

        showNewTooltip();

        elementOffset.top  += a_ParentElement.height() / 2 - 38;
        elementOffset.left += a_ParentElement.width() / 2 + 10;

        tooltip.offset( elementOffset );
        setTooltipArrowLeft();
    }
}

// -----------------------------------------------------------------------------
//  user settings tooltip
// -----------------------------------------------------------------------------

function showUnlinkTooltip( a_UserElement, a_Sticky )
{
    if ( g_User == null )
        return;

    var tooltip = $("#tooltip");
    tooltip.data( "sticky", a_Sticky );

    if ( tooltip.is(":visible") )
    {
        tooltip.fadeOut(100);
    }
    else
    {
        var container = $("#info_text");
        var tooltip = $("#tooltip");

        var refElement = a_UserElement.children(".userLinked").children("img");
        var elementOffset = refElement.offset();
        var HTMLString = "<div style=\"width: 200px\">";
        
        HTMLString += "<img id=\"unlink\" class=\"removeLink\" src=\"lib/layout/images/remove.png\"/>";
        HTMLString += "<div style=\"padding-top: 2px; margin-right: 4px;\">" + L("UnlinkUser") + "</div>";
        HTMLString += "</div>";

        container.empty().append( HTMLString );

        var userId = a_UserElement.attr("id");

        $("#unlink").click( function() { unlinkUser(userId); hideTooltip(); } );

        showNewTooltip();

        elementOffset.left += refElement.width();
        elementOffset.top  += (refElement.height() / 2) - (tooltip.height() / 2) + 4;

        tooltip.offset( elementOffset );
        setTooltipArrowLeft();
    }
}

// -----------------------------------------------------------------------------

function showUserTooltip( a_UserElement, a_Sticky )
{
    if ( g_User == null )
        return;

    var tooltip = $("#tooltip");
    tooltip.data( "sticky", a_Sticky );

    if ( tooltip.is(":visible") )
    {
        tooltip.fadeOut(100);
    }
    else
    {
        var container = $("#info_text");
        var tooltip = $("#tooltip");

        var refElement = a_UserElement.children(".userDrag").children("img");
        var elementOffset = refElement.offset();
        var grpName = a_UserElement.parent().attr("id");
        var HTMLString = "<div style=\"margin-bottom: 10px\">"+L("MoveUser")+"</div>";
        
        HTMLString += "<select id=\"grp_move\" style=\"width: 160px\">";
        HTMLString += "<option value=\"groupBanned\""+((grpName=="groupBanned") ? " selected" : "")+">"+L("Locked")+"</option>";
        HTMLString += "<option value=\"groupMember\""+((grpName=="groupMember") ? " selected" : "")+">"+L("Members")+"</option>";
        HTMLString += "<option value=\"groupRaidlead\""+((grpName=="groupRaidlead") ? " selected" : "")+">"+L("Raidleads")+"</option>";
        HTMLString += "<option value=\"groupAdmin\""+((grpName=="groupAdmin") ? " selected" : "")+">"+L("Administrators")+"</option>";
        HTMLString += "<option value=\"-\">---</option>";
        HTMLString += "<option value=\"groupSync\">"+L("LinkUser")+"</option>";
        HTMLString += "</select>";
        
        container.empty().append( HTMLString );

        var userId = a_UserElement.attr("id");
        
        $("#grp_move").change( function() { moveUserToGroup( userId, $("#grp_move").children("option:selected").val()); hideTooltip(); } );
        $("#grp_move").combobox({ darkBackground: true });

        showNewTooltip();

        elementOffset.left += refElement.width();
        elementOffset.top  += (refElement.height() / 2) - (tooltip.height() / 2) + 4;

        tooltip.offset( elementOffset );
        setTooltipArrowLeft();
    }
}