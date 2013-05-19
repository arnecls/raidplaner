// -----------------------------------------------------------------------------
//    Generic tooltip functions
// -----------------------------------------------------------------------------

function delayedFadeTooltip()
{
    var tooltip = $("#tooltip");
    if ( tooltip.data("sticky") === false )
    {
        tooltip.delay(200).fadeOut(100);
    }
}

// -----------------------------------------------------------------------------

function startFadeTooltip()
{
    var tooltip = $("#tooltip");
    tooltip.fadeOut(100);

    if ( tooltip.data("onHide") != null )
    {
       tooltip.data("onHide")();
       tooltip.data("onHide", null);
    }
}


// -----------------------------------------------------------------------------

function hideTooltip()
{
    var tooltip = $("#tooltip");
    tooltip.stop(true, true).clearQueue().hide();

    if ( tooltip.data("onHide") != null )
    {
       tooltip.data("onHide")();
       tooltip.data("onHide", null);
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

function showNewTooltip()
{
    showTooltip();

    if ( /msie/.test(navigator.userAgent.toLowerCase()) )
    {
        resetTooltipArrows();
    }
}

// -----------------------------------------------------------------------------

function toggleStickyState( aElement, aMakeSticky )
{
    var tooltip = $("#tooltip");

    var elementId  = aElement.attr("id");
    var tooltipId  = tooltip.data("id");
    var matchingId = elementId == tooltipId;

    if ( tooltip.is(":visible") )
    {
        // Tooltip is (partially) visible, toggle sticky state if requested

        if ( aMakeSticky )
        {
            if ( tooltip.data("sticky") )
            {
                tooltip.data("sticky", false);
                tooltip.fadeOut(100);
            }
            else
            {
                // This may happen during fade out
                aElement.unbind("mouseout");
                tooltip.data("sticky", true);
                showTooltip();
            }
        }
        else
        {
            if ( !tooltip.data("sticky") )
            {
                aElement.unbind("mouseout");
                aElement.mouseout( delayedFadeTooltip );

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

    aElement.unbind("mouseout");
    tooltip.data("id", elementId);

    if ( aMakeSticky )
    {
        tooltip.data("sticky", true);
    }
    else
    {
        tooltip.data("sticky", false);
        aElement.mouseout( delayedFadeTooltip );
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

function showTooltipRaidInfo( aParentElement, aCalendarView, aMakeSticky )
{
    if ( gUser == null )
        return;

    if ( !toggleStickyState(aParentElement, aMakeSticky) )
    {
        var raidElement = aParentElement;
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

        if ( aCalendarView )
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

                    tooltip.offset( elementOffset );
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

function showTooltipRaidInfoById( aRaidId )
{
    var RaidElement = $("#raid"+ aRaidId);
    var Container = $("#info_text");

    var InfoClone = RaidElement.children(".tooltip").clone();
    Container.empty().append( InfoClone );

    var SelectField = InfoClone.children(".functions").children("select");

    if ( SelectField.size() > 0 )
        SelectField.attr( "id", "active" + SelectField.attr("id").substr(6) );

    SelectField.combobox({ darkBackground: true });
}

// -----------------------------------------------------------------------------

function showTooltipRaidComment( aRaidId )
{
    var RaidElement = $("#raid"+ aRaidId);
    var Container = $("#info_text");
    var OldWidth = $("#tooltip").width();

    var InfoClone = RaidElement.children(".comment").clone();
    Container.empty().append( InfoClone );

    Container.children(".comment").children(".text").css( "width", OldWidth - 43 );
    Container.children(".comment").children("button").button({ icons: { secondary: "ui-icon-disk" }})
        .css( "font-size", 11 )
        .css( "height", 24 );
}

// -----------------------------------------------------------------------------

function commitAttend( aCharId, aRaid, aCalendarView, aComment )
{
    var Parameters = {
        attendanceIndex : aCharId,
        fallback        : 0,
        raidId          : aRaid,
        comment         : aComment
    };

    asyncQuery( "raid_attend", Parameters, ( aCalendarView ) ? generateCalendar : loadAllRaids );
}

// -----------------------------------------------------------------------------

function triggerAttend( aSelectObj, aCalendarView )
{
    if ( gUser == null )
        return;
        
    var CharId = aSelectObj.value;

    if ( CharId == -1 )
    {
        prompt( L("WhyAbsent"), L("SetAbsent"), L("Cancel"), function(aText) {
            commitAttend( CharId, aSelectObj.id.substr(6), aCalendarView, aText );
        });
    }
    else
    {
        commitAttend( CharId, aSelectObj.id.substr(6), aCalendarView, "" );
    }

    startFadeTooltip();
}

// -----------------------------------------------------------------------------

function triggerUpdateComment( aButtonElement, aRaidId, aCalendarView )
{
    if ( gUser == null )
        return;

    var Parameters = {
        raidId  : aRaidId,
        comment : aButtonElement.parent().children(".text").val()
    };

    asyncQuery( "comment_update", Parameters, ( aCalendarView ) ? generateCalendar : loadAllRaids );
}

// -----------------------------------------------------------------------------
//    RaidImageList tooltip
// -----------------------------------------------------------------------------

function applyLocationImage( aImageObj, aUIDataChange )
{
    var image = aImageObj.src.substr( aImageObj.src.lastIndexOf("/") + 1 );
    $("#locationimagepicker").css( "background-image", "url(images/raidbig/" + image + ")" );
    $("#locationimagepicker").data( "selectedImage", image );
    
    if (aUIDataChange)
        onUIDataChange();

    startFadeTooltip();
}

// -----------------------------------------------------------------------------

function applyLocationImageExternal( aImageObj, aUIDataChange )
{
    var image = aImageObj.src.substr( aImageObj.src.lastIndexOf("/") + 1 );
    var element = $("#locationimagelist").data("external");

    element.css( "background-image", "url(images/raidsmall/" + image + ")" );
    element.data( "selectedImage", image );
    
    if (aUIDataChange)
        onUIDataChange();
    
    startFadeTooltip();
}

// -----------------------------------------------------------------------------

function showTooltipRaidImageListAtElement( aElement )
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

        var elementOffset = aElement.offset();

        elementOffset.left -= (aElement.width() > 32) ? 6 : 24;
        elementOffset.top += aElement.height() - 2;

        tooltip.offset( elementOffset );
        setTooltipArrowTopLeft();
    }
}

// -----------------------------------------------------------------------------

function showTooltipRaidImageList(aTriggerDataChange)
{
    showTooltipRaidImageListAtElement( $("#locationimagepicker") );
}

// -----------------------------------------------------------------------------
//  comment tooltip
// -----------------------------------------------------------------------------

function showTooltipAttendee( aSlotElement, aImage, aName, aText, aMakeSticky, aOnHide )
{
    if ( !toggleStickyState(aSlotElement, aMakeSticky)  )
    {
        var tooltip = $("#tooltip");
        tooltip.data("onHide", aOnHide);

        var container = $("#info_text");
        var elementOffset = aSlotElement.offset();
        var pageCenterX = $(document).width() / 2;
        var pageCenterY = $(document).height() / 2;
                
        var filteredText = aText.replace(/(http:\/\/\S+)/g, "<a href=\"$1\" target=\"_blank\">$1</a>");
        
        var HTMLString = "<div class=\"playerTooltipIcon\" style=\"background-image: url('"+aImage+"')\"></div>";
        HTMLString += "<div class=\"playerTooltipText\"><div class=\"name\">"+aName+"</div>"+filteredText+"</div>";

        container.empty().append( HTMLString );

        showNewTooltip();

        elementOffset.left += aSlotElement.width() / 2 + 1;
        elementOffset.top  += aSlotElement.height() / 2 - (tooltip.height() + 15);

        if ( elementOffset.left < pageCenterX )
        {
            elementOffset.left -= 40;

            tooltip.offset( elementOffset );
            setTooltipArrowBottomLeft();
        }
        else
        {
            elementOffset.left -= tooltip.width() + aSlotElement.width() / 2 - 49;

            tooltip.offset( elementOffset );
            setTooltipArrowBottomRight();
        }
    }
}

// -----------------------------------------------------------------------------

function showTooltipSlackers( aSlotElement, aPlayer, aUndecided, aMakeSticky )
{
    // The declaration of the aPlayer struct can be found in raid.js : AddPlayer()

    if ( !toggleStickyState(aSlotElement, aMakeSticky)  )
    {
        var tooltip = $("#tooltip");
        tooltip.data("onHide", null);

        var container = $("#info_text");
        var elementOffset = aSlotElement.offset();
        var pageCenterX = $(document).width() / 2;
        var pageCenterY = $(document).height() / 2;
        var HTMLString = "";
        
        var filteredText = aPlayer.comment.replace(/(http:\/\/\S+)/g, "<a href=\"$1\" target=\"_blank\">$1</a>");

        var iconClass = "playerTooltipIconLarge";
        if ( aPlayer.characters.length > 1 )
          iconClass += " clickable";

        HTMLString += "<div style=\"float: left\">";
        HTMLString += "<div class=\""+iconClass+"\" style=\"background-image: url('images/classesbig/"+aPlayer.className+".png')\">";

        if ( aPlayer.characters.length > 1 )
        {
            HTMLString += "<div class=\"twinkcount\">1/"+(aPlayer.characters.length)+"</div>";
        }

        HTMLString += "<div class=\"mainbadge\"></div>";
        HTMLString += "</div>";
        
        HTMLString += "<div style=\"float: left\">";
        HTMLString += "<div class=\"playerTooltipRoleIcon\" style=\"background-image: url('images/roles/"+gRoleIdents[aPlayer.firstRole]+".png')\"><div class=\"mainbadge\"></div></div>";
        HTMLString += "<div class=\"playerTooltipRoleIcon\" style=\"background-image: url('images/roles/"+gRoleIdents[aPlayer.secondRole]+".png')\"></div>";
        HTMLString += "</div>";
                
        HTMLString += "</div>";
        HTMLString += "<div class=\"playerTooltipText\">";
        HTMLString += "<div class=\"name\">"+aPlayer.name+"</div>";

        if ( aPlayer.comment.length === 0 )
            HTMLString += (aUndecided) ? L("Undecided") : L("AbsentNoReason") + "</div>";
        else
            HTMLString += filteredText+"</div>";

        container.empty().append( HTMLString );

        // Configure character chooser

        if ( aPlayer.characters.length > 1 )
        {
            $(".playerTooltipIconLarge").data("index",0);
            $(".playerTooltipIconLarge").click( function() {
                var charIdx = $(this).data("index")+1;

                if ( charIdx >= aPlayer.characters.length )
                    charIdx = 0;

                $(this).data("index",charIdx);
                $(this).css("background-image", "url(images/classesbig/"+aPlayer.characters[charIdx].className+".png)");
                $(this).siblings().children(".playerTooltipRoleIcon").first().css("background-image", "url(images/roles/"+gRoleIdents[aPlayer.characters[charIdx].firstRole]+".png)");
                $(this).siblings().children(".playerTooltipRoleIcon").last().css("background-image", "url(images/roles/"+gRoleIdents[aPlayer.characters[charIdx].secondRole]+".png)");

                HTMLString = "<div class=\"twinkcount\">"+(charIdx+1)+"/"+(aPlayer.characters.length)+"</div>";
                
                if ( charIdx === 0 )
                    HTMLString += "<div class=\"mainbadge\"></div>";
                
                $(this).empty().append( HTMLString );                
                $(".playerTooltipText .name").empty().append( aPlayer.characters[charIdx].name );
                
                if ( gUser.isAdmin && (aSlotElement.data("setup_info") != null) )
                {
                    aSlotElement.siblings(".playerName:first").empty().append( aPlayer.characters[charIdx].name );
                    aSlotElement.data("setup_info", charIdx);
                }                
            });
        }
        
        // Show tooltip

        showNewTooltip();

        elementOffset.left += aSlotElement.width() / 2 + 1;
        elementOffset.top  += aSlotElement.height() / 2 - (tooltip.height() + 15);

        if ( elementOffset.left < pageCenterX )
        {
            elementOffset.left -= 40;

            tooltip.offset( elementOffset );
            setTooltipArrowBottomLeft();
        }
        else
        {
            elementOffset.left -= tooltip.width() + aSlotElement.width() / 2 - 49;

            tooltip.offset( elementOffset );
            setTooltipArrowBottomRight();
        }
    }
}

// -----------------------------------------------------------------------------
//  Class/role selector tooltip
// -----------------------------------------------------------------------------

function showTooltipClassList( aParentElement )
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
        var elementOffset = aParentElement.offset();

        var pageCenterX = $(document).width() / 2;
        var pageCenterY = $(document).height() / 2;

        var HTMLString = "<span><div>";

        for ( var i=1; i<gClasses.length; ++i )
        {
            if ( (i>1) && ((i-1)%5 === 0) )
                HTMLString += "</div><div style=\"clear: left\">";

            HTMLString += "<span class=\"class_select\" id=\"cs_" + gClasses[i].ident + "\"><img src=\"images/classessmall/" + gClasses[i].ident + ".png\"/><br/>" + gClasses[i].text + "</span>";
        }

        HTMLString +=  "</div></span>";

        container.empty().append( HTMLString );

        $(".class_select").click( function() {
            var classIdent = $(this).attr("id").substr(3);
            var classDesc = gClasses[gClassIdx[classIdent]];
            var defaultRoleId = gRoleIds[ classDesc.defaultRole ];
            
            var allowedRoles = [];
            for ( i=0; i<classDesc.roles.length; ++i )
            {
                allowedRoles.push(gRoleIds[classDesc.roles[i]]);
            }
            
            aParentElement.css("background", "url(images/classesbig/" + classIdent + ".png)" );
            
            var roleDiv = aParentElement.nextAll(".role_group");
            
            var charIdx = parseInt( aParentElement.parent().attr("id").substr(4), 10 );
            var charList = $("#charlist").data("characters");
            var character = charList.mCharacters[charIdx];
            
            character.charClass = classIdent;
            
            if ( allowedRoles.indexOf(character.role1) == -1 )
                character.role1 = defaultRoleId;
            
            if ( allowedRoles.indexOf(character.role2) == -1 )
                character.role2 = defaultRoleId;
            
            roleDiv.children(".newrole1").css("background-image", "url(images/roles/" + gRoleIdents[character.role1] + ".png)");
            roleDiv.children(".newrole2").css("background-image", "url(images/roles/" + gRoleIdents[character.role2] + ".png)");
            
            hideTooltip();                
            onUIDataChange();
        });

        showNewTooltip();
        
        elementOffset.top  += aParentElement.height() / 2 - 43;
        elementOffset.left += aParentElement.width() / 2 + 22;

        tooltip.offset( elementOffset );
        setTooltipArrowLeft();
    }
}

// -----------------------------------------------------------------------------

function showTooltipRoleList( aParentElement, aEditRole1 )
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
        var elementOffset = aParentElement.offset();

        var pageCenterX = $(document).width() / 2;
        var pageCenterY = $(document).height() / 2;

        var charIdx = parseInt( aParentElement.parent().parent().attr("id").substr(4), 10 );
        var charList = $("#charlist").data("characters");        
        var classString = charList.mCharacters[charIdx].charClass;

        if ( (aEditRole1 && (charList.mCharacters[charIdx].role1 < 0)) ||
             (charList.mCharacters[charIdx].role2 < 0) )
        {
            return; // ### return, invalid role ###
        }
        
        var HTMLString = "<span>";
        var classData = gClasses[gClassIdx[classString]];

        for ( var i=0; i<classData.roles.length; ++i )
        {
            var roleIdent = classData.roles[i];
            HTMLString += "<span class=\"class_select\" id=\"cs_" + roleIdent + "\"><img src=\"images/roles/" + roleIdent + ".png\"/><br/>" + gRoleNames[ roleIdent ] + "</span>";
        }

        HTMLString +=  "</span>";

        container.empty().append( HTMLString );

        $(".class_select").click( function() {
            var roleIdent = $(this).attr("id").substr(3);

            aParentElement.css("background-image", "url(images/roles/" + roleIdent + ".png)" );
            
            if ( aEditRole1 )
                charList.mCharacters[charIdx].role1 = gRoleIds[roleIdent];
            else
                charList.mCharacters[charIdx].role2 = gRoleIds[roleIdent];

            hideTooltip();                
            onUIDataChange();
        });

        showNewTooltip();

        elementOffset.top  += aParentElement.height() / 2 - 38;
        elementOffset.left += aParentElement.width() / 2 + 10;

        tooltip.offset( elementOffset );
        setTooltipArrowLeft();
    }
}

// -----------------------------------------------------------------------------
//  user settings tooltip
// -----------------------------------------------------------------------------

function showUnlinkTooltip( aUserElement, aSticky )
{
    if ( gUser == null )
        return;

    var tooltip = $("#tooltip");
    tooltip.data( "sticky", aSticky );

    if ( tooltip.is(":visible") )
    {
        tooltip.fadeOut(100);
    }
    else
    {
        var container = $("#info_text");
        
        var refElement = aUserElement.children(".userLinked").children("img");
        var elementOffset = refElement.offset();
        var HTMLString = "<div style=\"width: 200px\">";
        
        HTMLString += "<img id=\"unlink\" class=\"removeLink\" src=\"lib/layout/images/remove.png\"/>";
        HTMLString += "<div style=\"padding-top: 2px; margin-right: 4px;\">" + L("UnlinkUser") + "</div>";
        HTMLString += "</div>";

        container.empty().append( HTMLString );

        var userId = aUserElement.attr("id");

        $("#unlink").click( function() { unlinkUser(userId); hideTooltip(); } );

        showNewTooltip();

        elementOffset.left += refElement.width();
        elementOffset.top  += (refElement.height() / 2) - (tooltip.height() / 2) + 4;

        tooltip.offset( elementOffset );
        setTooltipArrowLeft();
    }
}

// -----------------------------------------------------------------------------

function showUserTooltip( aUserElement, aSticky )
{
    if ( gUser == null )
        return;

    var tooltip = $("#tooltip");
    tooltip.data( "sticky", aSticky );

    if ( tooltip.is(":visible") )
    {
        tooltip.fadeOut(100);
    }
    else
    {
        var container = $("#info_text");

        var refElement = aUserElement.children(".userDrag").children("img");
        var elementOffset = refElement.offset();
        var grpName = aUserElement.parent().attr("id");
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

        var userId = aUserElement.attr("id");
        
        $("#grp_move").change( function() { moveUserToGroup( userId, $("#grp_move").children("option:selected").val()); hideTooltip(); } );
        $("#grp_move").combobox({ darkBackground: true });

        showNewTooltip();

        elementOffset.left += refElement.width();
        elementOffset.top  += (refElement.height() / 2) - (tooltip.height() / 2) + 4;

        tooltip.offset( elementOffset );
        setTooltipArrowLeft();
    }
}