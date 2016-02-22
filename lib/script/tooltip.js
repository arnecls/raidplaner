// -----------------------------------------------------------------------------
//    Generic tooltip functions
// -----------------------------------------------------------------------------

function delayedFadeTooltip()
{
    var Tooltip = $("#tooltip");
    if ( Tooltip.data("sticky") === false )
    {
        Tooltip.delay(200).fadeOut(100);
    }
}

// -----------------------------------------------------------------------------

function startFadeTooltip()
{
    var Tooltip = $("#tooltip");
    Tooltip.fadeOut(100);

    if ( Tooltip.data("onHide") != null )
    {
       Tooltip.data("onHide")();
       Tooltip.data("onHide", null);
    }
}

// -----------------------------------------------------------------------------

function hideTooltip()
{
    var Tooltip = $("#tooltip");
    Tooltip.stop(true, true).clearQueue().hide();

    if ( Tooltip.data("onHide") != null )
    {
       Tooltip.data("onHide")();
       Tooltip.data("onHide", null);
    }
}

// -----------------------------------------------------------------------------

function showTooltip()
{
    var Tooltip = $("#tooltip");

    if ( Tooltip.data("onHide") != null )
    {
       Tooltip.data("onHide")();
       Tooltip.data("onHide", null);
    }

    Tooltip.stop(true, true).clearQueue().show();
    Tooltip.unbind("mouseover");
    Tooltip.unbind("mouseout");

    if ( !Tooltip.data("sticky") )
    {
        Tooltip.mouseover( showTooltip );
        Tooltip.mouseout( delayedFadeTooltip );
    }
}

// -----------------------------------------------------------------------------

function toggleStickyState( aElement, aMakeSticky )
{
    var Tooltip = $("#tooltip");

    var ElementId  = aElement.attr("id");
    var TooltipId  = Tooltip.data("id");
    var MatchingId = ElementId == TooltipId;

    if ( Tooltip.is(":visible") )
    {
        // Tooltip is (partially) visible, toggle sticky state if requested

        if ( aMakeSticky )
        {
            if ( Tooltip.data("sticky") )
            {
                Tooltip.data("sticky", false);
                Tooltip.fadeOut(100);
            }
            else
            {
                // This may happen during fade out
                aElement.unbind("mouseout");
                Tooltip.data("sticky", true);
                showTooltip();
            }
        }
        else
        {
            if ( !Tooltip.data("sticky") )
            {
                aElement.unbind("mouseout");
                aElement.mouseout( delayedFadeTooltip );

                if ( !MatchingId )
                {
                    Tooltip.data("id", ElementId);
                    return false; // ### return, switching tooltips by mouseover ###
                }
            }

            showTooltip();
        }

        return true; // ### return, visible ###
    }

    // Tooltip is not visible, init sticky state

    aElement.unbind("mouseout");
    Tooltip.data("id", ElementId);

    if ( aMakeSticky )
    {
        Tooltip.data("sticky", true);
    }
    else
    {
        Tooltip.data("sticky", false);
        aElement.mouseout( delayedFadeTooltip );
    }

    return false;
}

// -----------------------------------------------------------------------------

function setTooltipArrowTopLeft()
{
    $("#tooltip_arrow")
        .removeClass("top bottom left right side_left side_right")
        .addClass("top left");
}

// -----------------------------------------------------------------------------

function setTooltipArrowTopRightBadge()
{
    $("#tooltip_arrow")
        .removeClass("top bottom left right side_left side_right")
        .addClass("top right_badge");
}

// -----------------------------------------------------------------------------

function setTooltipArrowBottomLeft()
{
    $("#tooltip_arrow")
        .removeClass("top bottom left right side_left side_right")
        .addClass("bottom left");
}

// -----------------------------------------------------------------------------

function setTooltipArrowBottomRight()
{
    $("#tooltip_arrow")
        .removeClass("top bottom left right side_left side_right")
        .addClass("bottom right");
}

// -----------------------------------------------------------------------------

function setTooltipArrowLeft()
{
    $("#tooltip_arrow")
        .removeClass("top bottom left right side_left side_right")
        .addClass("side_left");
}

// -----------------------------------------------------------------------------

function setTooltipArrowRight()
{
    $("#tooltip_arrow")
        .removeClass("top bottom left right side_left side_right")
        .addClass("side_right");
}

// -----------------------------------------------------------------------------

function getPageCenter()
{
    var referenceElement = $("#body");
    var offset = referenceElement.offset();

    return {
        X: offset.left + referenceElement.width() * 0.5,
        Y: offset.top  + referenceElement.height() * 0.5
    }
}

// -----------------------------------------------------------------------------
//    RaidInfo tooltip
// -----------------------------------------------------------------------------

function showTooltipRaidInfo( aElement, aRaid, aLocked, aCalendarView, aMakeSticky )
{
    if ( gUser == null )
        return;

    if ( !toggleStickyState(aElement, aMakeSticky) )
    {
        showTooltipRaidInfoSwitch( aElement, aRaid, aLocked, aCalendarView );
    }
}

// -----------------------------------------------------------------------------

function showTooltipRaidInfoSwitch( aElement, aRaid, aLocked, aCalendarView )
{
    var Container = $("#info_text");
    var Tooltip = $("#tooltip");
    var ElementOffset = aElement.offset();
    var PageCenter = getPageCenter();

    Container.empty().append( generateRaidTooltip(aRaid, aLocked, aCalendarView) );

    $(".icon", Container).click( function() {
        changeContext("raid,setup," + aRaid.id);
    });

    $(".attend", Container).change( function() {
        triggerAttend(this.value, -1, aRaid.id, aCalendarView);
    });

    $(".attend_sub", Container).change( function() {
        triggerAttend($(".attend", Container).val(), this.value, aRaid.id, aCalendarView);
    });

    $(".commentbadge", Container).click( function(aEvent) {
        showTooltipRaidCommentSwitch(aElement, aRaid, aLocked, aCalendarView);
        aEvent.stopPropagation();
    });

    showTooltip();

    // Primary selection

    var ClassIcons = new Array();
    var SelectedCharId = $(".attend", Container).val();
    var SelectedCharIdx = -1;

    for (var i=0; i<gUser.characterIds.length; ++i )
    {
        if (gUser.characterGames[i] == gGame.GameId)
        {
            if (gUser.characterIds[i] == SelectedCharId)
                SelectedCharIdx = i;

            var ClassDesc = gGame.Classes[gUser.characterClass[i][0]];
            ClassIcons[gUser.characterIds[i]] = "themes/icons/"+gSite.Iconset+"/classessmall/" + ClassDesc.style + ".png";
        }
    }

    $(".attend", Container).combobox({
        inlineStyle    : { "float":"left" },
        darkBackground : true,
        icons          : ClassIcons
    });

    // Secondary selection

    var SubIcons = new Array();
    if (SelectedCharIdx >=0 )
    {
        if (gGame.ClassMode == "multi")
        {
            for (var i=0; i<gUser.characterClass[SelectedCharIdx].length; ++i )
            {
                var ClassDesc = gGame.Classes[gUser.characterClass[SelectedCharIdx][i]];
                SubIcons[i] = "themes/icons/"+gSite.Iconset+"/classessmall/" + ClassDesc.style + ".png";
            }
        }
        else
        {
            SubIcons[0] = "lib/layout/images/icon_" + gGame.Roles[gUser.role1[SelectedCharIdx]].style + ".png";
            SubIcons[1] = "lib/layout/images/icon_" + gGame.Roles[gUser.role2[SelectedCharIdx]].style + ".png";
        }

        $(".attend_sub", Container).combobox({
            inlineStyle    : { "float": "left", "margin-left": 5 },
            darkBackground : true,
            iconAsValue    : true,
            icons          : SubIcons
        });
    }

    if ( aCalendarView )
    {
        if ( ElementOffset.top < PageCenter.Y )
        {
            ElementOffset.top += aElement.height() + 12;

            if ( ElementOffset.left < PageCenter.X )
            {
                ElementOffset.left -= 10;

                Tooltip.offset( ElementOffset );
                setTooltipArrowTopLeft();
            }
            else
            {
                ElementOffset.left -= Tooltip.width() - aElement.width() / 2 - 40;

                Tooltip.offset( ElementOffset );
                setTooltipArrowTopRightBadge();
            }
        }
        else
        {
            ElementOffset.top -= Tooltip.height() + 30;

            if ( ElementOffset.left < PageCenter.X )
            {
                ElementOffset.left -= 10;

                Tooltip.offset( ElementOffset );
                setTooltipArrowBottomLeft();
            }
            else
            {
                ElementOffset.left -= Tooltip.width() - aElement.width() / 2 - 8;

                Tooltip.offset( ElementOffset );
                setTooltipArrowBottomRight();
            }
        }
    }
    else
    {
        ElementOffset.left += aElement.width() + 10;

        Tooltip.offset( ElementOffset );
        setTooltipArrowLeft();
    }
}

// -----------------------------------------------------------------------------

function showTooltipRaidCommentSwitch( aElement, aRaid, aLocked, aCalendarView )
{
    var Container = $("#info_text");
    var OldWidth = $("#tooltip").width();
    var OldHeight = $("#tooltip").height();

    Container.empty().append( generateRaidCommentTooltip(aRaid) );

    $(".infobadge", Container).click( function(aEvent) {
        showTooltipRaidInfoSwitch( aElement, aRaid, aLocked, aCalendarView );
        aEvent.stopPropagation();
    });

    $(".text", Container)
        .css( "width", OldWidth - 6 )
        .css( "height", OldHeight - 47 );

    $("button", Container).click( function() {
        var text = $(".text", Container).val();
        triggerUpdateComment(text, aRaid.id, aCalendarView);
    });

    $("button", Container).button({ icons: { secondary: "ui-icon-disk" }});
}

// -----------------------------------------------------------------------------

function commitAttend( aCharId, aSubId, aRaid, aCalendarView, aComment )
{
    var Parameters = {
        attendanceId    : aCharId,
        attendanceSubId : aSubId,
        fallback        : 0,
        raidId          : aRaid,
        comment         : aComment
    };

    asyncQuery( "raid_attend", Parameters, ( aCalendarView ) ? generateCalendar : loadAllRaids );
}

// -----------------------------------------------------------------------------

function triggerAttend( aCharId, aSubId, aRaidId, aCalendarView )
{
    if ( gUser == null )
        return;

    if ( aCharId == -1 )
    {
        prompt( L("WhyAbsent"), L("SetAbsent"), L("Cancel"), function(aText) {
            commitAttend( aCharId, aSubId, aRaidId, aCalendarView, aText );
        });
    }
    else if ( aCharId != 0 )
    {
        commitAttend( aCharId, aSubId, aRaidId, aCalendarView, "" );
    }
    else
    {
        return // ### return, clicked first option ###
    }

    startFadeTooltip();
}

// -----------------------------------------------------------------------------

function triggerUpdateComment( aText, aRaidId, aCalendarView )
{
    if ( gUser == null )
        return;

    var Parameters = {
        raidId     : aRaidId,
        comment    : aText
    };

    asyncQuery( "comment_update", Parameters, ( aCalendarView ) ? generateCalendar : loadAllRaids );
}

// -----------------------------------------------------------------------------
//    RaidImageList tooltip
// -----------------------------------------------------------------------------

function applyLocationImage( aImageObj, aUIDataChange )
{
    var Image = aImageObj.src.substr( aImageObj.src.lastIndexOf("/") + 1 );
    $("#locationimagepicker").css( "background-image", "url(themes/icons/"+gSite.Iconset+"/raidbig/" + Image + ")" );
    $("#locationimagepicker").data( "selectedImage", Image );

    if (aUIDataChange)
        onUIDataChange();

    startFadeTooltip();
}

// -----------------------------------------------------------------------------

function applyLocationImageExternal( aImageObj, aUIDataChange )
{
    var Image = aImageObj.src.substr( aImageObj.src.lastIndexOf("/") + 1 );
    var Element = $("#locationimagelist").data("external");

    Element.css( "background-image", "url(themes/icons/"+gSite.Iconset+"/raidsmall/" + Image + ")" );
    Element.data( "selectedImage", Image );

    if (aUIDataChange)
        onUIDataChange();

    startFadeTooltip();
}

// -----------------------------------------------------------------------------

function onLocationChange( aSelectObj )
{
    $("#locationimagepicker")
        .unbind("click")
        .removeClass("clickable");

    if ( aSelectObj.selectedIndex === 0)
    {
        $(aSelectObj).combobox("editable", true);

        $("#locationimagepicker")
            .addClass("clickable")
            .click( function(aEvent) { showTooltipRaidImageList(); aEvent.stopPropagation(); } );

        $("#locationimagepicker").css( "background-image", "url(lib/layout/images/icon_choose.png)" );
    }
    else
    {
        $(aSelectObj).combobox("editable", false);

        var ImageName = $("#locationimagepicker").data("imageNames")[aSelectObj.selectedIndex - 1];

        $("#locationimagepicker").css( "background-image", "url(themes/icons/"+gSite.Iconset+"/raidbig/"+ ImageName + ")" );
    }
}

// -----------------------------------------------------------------------------

function showTooltipRaidImageListAtElement( aElement )
{
    var Tooltip = $("#tooltip");
    Tooltip.data("sticky", true);

    if ( Tooltip.is(":visible") )
    {
        Tooltip.fadeOut(100);
    }
    else
    {
        var Container = $("#info_text");
        Container.empty().append( $("#locationimagelist > *").clone() );

        showTooltip();

        var ElementOffset = aElement.offset();

        ElementOffset.left += (aElement.width() > 32) ? 5 : -10;
        ElementOffset.top  += aElement.height() + 10;

        Tooltip.offset( ElementOffset );
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

function showTooltipCharChooser( aSlotElement, aPlayer, aCurrentRole, aMakeSticky, aOnHide )
{
    // The declaration of the aPlayer struct can be found in raid.js : AddPlayer()
    if ( !toggleStickyState(aSlotElement.parent(), aMakeSticky)  )
    {
        var Tooltip = $("#tooltip");

        var Container = $("#info_text");
        var ElementOffset = aSlotElement.offset();
        var PageCenter = getPageCenter();
        var HTMLString = "";

        // Get the current character's index

        var CharIdx = 0;

        for (var i=0; i<aPlayer.characters.length; ++i)
        {
            if (aPlayer.characters[i].id == aPlayer.charId)
            {
                CharIdx = i;
                break;
            }
        }

        // Get the current character's class index

        var PlayerClassIdx = 0;

        if (gGame.ClassMode == "multi")
        {
            for (var i=0; i<aPlayer.className.length; ++i)
            {
                if (aPlayer.className[i] == aPlayer.activeClass)
                {
                    PlayerClassIdx = i;
                    break;
                }
            }
        }

        // Setup switch lists

        var AvailableRoles = new Array(); // "role","role",...
        var CharsByRole = new Array();    // "role" => [charIdx,classId,roleId]

        for (var i=0; i<aPlayer.characters.length; ++i)
        {
            var Char = aPlayer.characters[i];

            if ( gGame.ClassMode == "multi" )
            {
                for (var c=0; c<Char.className.length; ++c)
                {
                    var ClassId = Char.className[c];
                    var ClassDesc = gGame.Classes[ClassId];
                    var RoleId = (ClassDesc == null) ? aCurrentRole : ClassDesc.defaultRole;

                    if (CharsByRole[RoleId] == undefined)
                        CharsByRole[RoleId] = new Array();

                    if (AvailableRoles.indexOf(RoleId) == -1)
                        AvailableRoles.push(RoleId);

                    CharsByRole[RoleId].push({ charIdx: i, classId: ClassId, roleId: RoleId });
                }
            }
            else
            {
                var ClassId = Char.className[0];

                if (CharsByRole[Char.firstRole] == undefined)
                    CharsByRole[Char.firstRole] = new Array();

                if (AvailableRoles.indexOf(Char.firstRole) == -1)
                    AvailableRoles.push(Char.firstRole);

                CharsByRole[Char.firstRole].push({ charIdx: i, classId: ClassId, roleId: Char.firstRole });

                if (Char.secondRole != Char.firstRole)
                {
                    if (CharsByRole[Char.secondRole] == undefined)
                        CharsByRole[Char.secondRole] = new Array();

                    if (AvailableRoles.indexOf(Char.secondRole) == -1)
                        AvailableRoles.push(Char.secondRole);

                    CharsByRole[Char.secondRole].push({ charIdx: i, classId: ClassId, roleId: Char.secondRole });
                }
            }
        }

        // Setup state

        var CurrentListIdx = new Array(); // "role" => CharsByRole["role"][index]
        var AvailableRoleIdx = AvailableRoles.indexOf(aCurrentRole);

        for (var i=0; i<AvailableRoles.length; ++i)
        {
            CurrentListIdx[AvailableRoles[i]] = 0;
        }

        if (aPlayer.className[0] != "___")
        {
            var CharsOfStartRole = CharsByRole[aCurrentRole];

            for (var i=0; i<CharsOfStartRole.length; ++i)
            {
                if (CharsOfStartRole[i].charIdx == CharIdx)
                {
                    if ( gGame.ClassMode == "multi" )
                    {
                        if (CharsOfStartRole[i].classId != aPlayer.activeClass)
                            continue;
                    }

                    CurrentListIdx[aCurrentRole] = i;
                    break;
                }
            }
        }

        // Layout tooltip

        aSlotElement.data("setup_info", { charIdx: CharIdx, classId: aPlayer.activeClass, roleId: aCurrentRole });

        // Role carousel

        HTMLString += "<div style=\"width: 116px; display: inline-block\">";
        HTMLString += "<div class=\"role_select\">";

        if (AvailableRoles.length > 1)
        {
            HTMLString += "<div class=\"switch_role_up clickable\"></div><div class=\"switch_role_down clickable\"></div>";
            var StartRoleIdx = AvailableRoleIdx + AvailableRoles.length - 1;
            for (var i=0; i<AvailableRoles.length; ++i)
            {
                var RoleIdx = (StartRoleIdx+i) % AvailableRoles.length;
                var Opacity = (AvailableRoles[RoleIdx] == aCurrentRole) ? 1.0 : 0.25;
                HTMLString += "<img class=\"role\" style=\"opacity: "+Opacity+"\" src=\"lib/layout/images/icon_"+gGame.Roles[AvailableRoles[RoleIdx]].style+".png\"/>";
            }
        }
        else
        {
            HTMLString += "<img class=\"single_role\" src=\"lib/layout/images/icon_"+gGame.Roles[aCurrentRole].style+".png\"/>";
        }
        HTMLString += "</div>";

        // Char switcher

        HTMLString += "<div class=\"char_select"+((CharsByRole[aCurrentRole].length > 1) ? " clickable" : "")+"\">";
        HTMLString += "<div class=\"switch_char_next\"></div>";
        HTMLString += "<div class=\"mainchar\"></div>";
        HTMLString += "</div>";

        if (gUser.isRaidlead && (aPlayer.className[0] != "___") &&
            (aPlayer.status != "unavailable") && (aPlayer.status != "undecided"))
        {
            HTMLString += "<button class=\"switch_button\">"+L("Switch")+"</button>";
            HTMLString += "<button class=\"absent_button\">"+L("Retire")+"</button>";
        }

        HTMLString += "</div>";

        // Player infos

        HTMLString += "<div class=\"player_info\">";
        HTMLString += "<div class=\"name\"></div>";
        HTMLString += "<div class=\"detail\"></div>";

        if ( aPlayer.comment.length === 0 )
        {
            switch(aPlayer.status)
            {
            case "undecided":
                HTMLString += L("Undecided");
                break;

            case "unavailable":
                HTMLString += L("AbsentNoReason");
                break;

            default:
                break;
            }
        }
        else
        {
            var FilteredText = aPlayer.comment.replace(/(http[s]*:\/\/\S+)/g, "<a href=\"$1\" target=\"_blank\">$1</a>");
            HTMLString += FilteredText;
        }

        HTMLString += "</div>";

        Container.empty().append( HTMLString );

        $(".switch_button", Container).button({
            icons: { primary: "ui-icon-transferthick-e-w" }
        });

        $(".absent_button", Container).button({
            icons: { primary: "ui-icon-cancel" }
        });

        // Switch mechanics

        $(".switch_button", Container).click( function() {
            var RoleId = AvailableRoles[AvailableRoleIdx];
            var SelectedChar = CharsByRole[RoleId][CurrentListIdx[RoleId]];

            aPlayer.switchTo(SelectedChar.charIdx, RoleId, SelectedChar.classId);
            onUIDataChange();
            hideTooltip();
        });

        $(".absent_button", Container).click( function() {
            var RoleId = AvailableRoles[AvailableRoleIdx];
            var SelectedChar = CharsByRole[RoleId][CurrentListIdx[RoleId]];

            aPlayer.setAbsent();
            hideTooltip();
        });

        var RoleSelectField = $(".role_select", Tooltip);
        var CharSelectField = $(".char_select", Tooltip);
        var MainCharField = $(".mainchar", CharSelectField);
        var NameField = $(".name", Tooltip);
        var DetailField = $(".detail", Tooltip);

        var onUpdateCharField = function()
        {
            var RoleId = AvailableRoles[AvailableRoleIdx];
            var SelectedChar = CharsByRole[RoleId][CurrentListIdx[RoleId]];
            var ActiveChar = aPlayer.characters[SelectedChar.charIdx];
            var Name = ActiveChar.name;

            var ClassId = SelectedChar.classId;
            var ClassStyle = (ClassId != "___")
                ? gGame.Classes[ClassId].style
                : "random";

            // Class and name

            CharSelectField.css("background-image", "url(themes/icons/"+gSite.Iconset+"/classesbig/"+ClassStyle+".png)");

            NameField.empty().append(Name);

            // Additional information

            if ( gGame.ClassMode == "single")
            {
                var RoleInfo = L(gGame.Roles[ActiveChar.firstRole].name) +
                    ((ActiveChar.firstRole != ActiveChar.secondRole)
                        ? ", " + L(gGame.Roles[ActiveChar.secondRole].name)
                        : "");

                DetailField.empty().append(RoleInfo);
            }
            else
            {
                DetailField.empty();

                for (var i=0; i<ActiveChar.className.length; ++i)
                {
                    if (ActiveChar.className[i] == "___")
                    {
                        DetailField.append(L("Random"));
                    }
                    else
                    {
                        var classId = ActiveChar.className[i];
                        DetailField.append(((i > 0) ? ", " : "") + L(gGame.Classes[classId].name));
                    }
                }
            }

            // Main badge

            if (aPlayer.characters[SelectedChar.charIdx].mainchar)
                MainCharField.show();
            else
                MainCharField.hide();

            // Char chooser functionaliy

            CharSelectField.removeClass("clickable");

            if (CharsByRole[RoleId].length > 1)
            {
                $(".switch_char_next", Tooltip).show();
                CharSelectField.addClass("clickable");
            }
            else
            {
                $(".switch_char_next", Tooltip).hide();
            }

            aSlotElement.data("setup_info", SelectedChar);

            // Write back for undecided and absent

            if (gUser.isRaidlead && ((aPlayer.status == "unavailable") || (aPlayer.status == "undecided")))
            {
                $(aSlotElement).css("backgroundImage", "url(themes/icons/"+gSite.Iconset+"/classessmall/"+ClassStyle+".png)");
                $(".playerName",aSlotElement.parent()).empty().append(Name);
            }
        };

        onUpdateCharField();

        // Function bindings

        CharSelectField.click(function() {
            var RoleId = AvailableRoles[AvailableRoleIdx];
            CurrentListIdx[RoleId] = (CurrentListIdx[RoleId] + 1) % CharsByRole[RoleId].length;
            onUpdateCharField();
        });

        $(".switch_role_up", Tooltip).click(function() {
            AvailableRoleIdx = (AvailableRoleIdx + AvailableRoles.length - 1) % AvailableRoles.length;

            $(".role:last", RoleSelectField).clone().prependTo(RoleSelectField);
            $(".role", RoleSelectField).css("top", -48);

            $(".role", RoleSelectField).slice(3).animate({top: -16}, 300);
            $(".role:eq(1)", RoleSelectField).animate({opacity: 1, top: -16}, 300);
            $(".role:eq(2)", RoleSelectField).animate({opacity: 0.25, top: -16}, 300);

            $(".role:eq(0)", RoleSelectField).animate({top: -16}, 300, function() {
                onUpdateCharField();
                $(".role:last", RoleSelectField).detach();
            });
        });

        $(".switch_role_down", Tooltip).click(function() {
            AvailableRoleIdx = (AvailableRoleIdx + 1) % AvailableRoles.length;

            $(".role:first", RoleSelectField).clone().appendTo(RoleSelectField);

            $(".role", RoleSelectField).slice(3).animate({top: -48}, 300);
            $(".role:eq(1)", RoleSelectField).animate({opacity: 0.25, top: -48}, 300);
            $(".role:eq(2)", RoleSelectField).animate({opacity: 1, top: -48}, 300);

            $(".role:eq(0)", RoleSelectField).animate({top: -48}, 300, function() {
                onUpdateCharField();

                $(".role:first", RoleSelectField).detach();
                $(".role").css("top", -16);
            });
        });

        // Show tooltip

        showTooltip();
        Tooltip.data("onHide", aOnHide);

        ElementOffset.left += aSlotElement.width() / 2 + 14;
        ElementOffset.top  -= Tooltip.height() + 30;

        if ( ElementOffset.left < PageCenter.X )
        {
            ElementOffset.left -= 40;

            Tooltip.offset( ElementOffset );
            setTooltipArrowBottomLeft();
        }
        else
        {
            ElementOffset.left -= Tooltip.width() + 6;

            Tooltip.offset( ElementOffset );
            setTooltipArrowBottomRight();
        }
    }
}

// -----------------------------------------------------------------------------
//  Class/role selector tooltip
// -----------------------------------------------------------------------------

function validateRoles(aCharacter)
{
    var ClassId = aCharacter.charClass[0];
    var ClassDesc = gGame.Classes[ClassId];

    if ( gGame.ClassMode == "multi" )
    {
        aCharacter.role1 = ClassDesc.defaultRole;
        aCharacter.role2 = ClassDesc.defaultRole;
    }
    else
    {
        var AllowedRoles = ClassDesc.roles;
        var foundRole1 = false;
        var foundRole2 = false;

        for ( i=0; i<AllowedRoles.length; ++i )
        {
            if ( AllowedRoles[i] == aCharacter.role1 )
                foundRole1 = true;

            if ( AllowedRoles[i] == aCharacter.role2 )
                foundRole2 = true;
        }

        if ( !foundRole1 )
            aCharacter.role1 = ClassDesc.defaultRole;

        if ( !foundRole2 )
            aCharacter.role2 = ClassDesc.defaultRole;
    }
}

// -----------------------------------------------------------------------------

function showTooltipClassList( aParentElement, aOnChange )
{
    var Tooltip = $("#tooltip");
    Tooltip.data("sticky", true);

    if ( Tooltip.is(":visible") )
    {
        Tooltip.fadeOut(100);
    }
    else
    {
        var Container = $("#info_text");
        var ElementOffset = aParentElement.offset();
        var PageCenter = getPageCenter();

        var HTMLString = "<div><div>";

        var ClassCount = 0;
        $.each(gGame.Classes, function(aClassId, aClassDesc)
        {
            if ( (ClassCount > 0) && (ClassCount % 5 == 0) )
                HTMLString += "</div><div style=\"clear: left\">";

            HTMLString += "<span class=\"class_select clickable\" id=\"cs_" + aClassId + "\"><img src=\"themes/icons/"+gSite.Iconset+"/classessmall/" + aClassDesc.style + ".png\"/><br/>" + L(aClassDesc.name) + "</span>";
            ++ClassCount;
        });

        HTMLString +=  "</div></div>";

        Container.empty().append( HTMLString );

        $(".class_select").click( function() {
            var ClassId = $(this).attr("id").substr(3);
            var CharIdx = parseInt( aParentElement.parent().attr("id").substr(4), 10 );
            var CharList = $("#charlist").data("characters");
            var Character = CharList.mCharacters[CharIdx];

            Character.charClass = new Array(ClassId);

            aParentElement.css("background", "url(themes/icons/"+gSite.Iconset+"/classesbig/" + gGame.Classes[ClassId].style + ".png)" );

            validateRoles(Character);
            aOnChange();

            hideTooltip();
            onUIDataChange();
        });

        showTooltip();

        if ( ElementOffset.left < PageCenter.X )
        {
            ElementOffset.left += aParentElement.width() + 10;
            setTooltipArrowLeft();
        }
        else
        {
            ElementOffset.left -= Tooltip.width() + 30;
            setTooltipArrowRight();
        }

        Tooltip.offset( ElementOffset );
    }
}

// -----------------------------------------------------------------------------

function showTooltipClassListMultiSelect( aParentElement, aOnChangeMain )
{
    var Tooltip = $("#tooltip");
    Tooltip.data("sticky", true);

    if ( Tooltip.is(":visible") )
    {
        Tooltip.fadeOut(100);
    }
    else
    {
        var Container = $("#info_text");
        var ElementOffset = aParentElement.offset();
        var PageCenter = getPageCenter();

        var leftAligned = ElementOffset.left < PageCenter.X;

        var CharIdx = parseInt( aParentElement.parent().attr("id").substr(4), 10 );
        var CharList = $("#charlist").data("characters");
        var Character = CharList.mCharacters[CharIdx];

        var HTMLString = "";

        // Selected update

        var rebuildSelected = function() {
            var HTMLString = "";

            if (leftAligned)
            {
                if (Character.charClass.length > 1)
                    HTMLString += "<img class=\"shuffle clickable\" src=\"lib/layout/images/move_left.png\"/>";
                else
                    HTMLString += "<img class=\"shuffle\" style=\"opacity: 0.5\" src=\"lib/layout/images/move_left.png\"/>";
            }

            HTMLString += "<span style=\"display: inline-block\"><div style=\"height: 42px\">";

            var added = 0;

            for ( var i=0; i<Character.charClass.length; ++i )
            {
                var classId = Character.charClass[i];
                if (classId == "empty")
                    continue;

                if ( (added>0) && (added % 7 === 0) )
                    HTMLString += "</div><div style=\"height: 42px; clear: left\">";

                var AlignStyle = (leftAligned) ? "float: left" : "float: right";
                HTMLString += "<img class=\"class_selected"+((Character.charClass.length > 1) ? " clickable" : "")+"\" style=\""+AlignStyle+"\" id=\"cs_" + classId + "\" src=\"themes/icons/"+gSite.Iconset+"/classessmall/" + gGame.Classes[classId].style + ".png\"/>";
                ++added;
            }

            HTMLString += "</div></span>";

            if (!leftAligned)
            {
                if (Character.charClass.length > 1)
                    HTMLString += "<img class=\"shuffle clickable\" src=\"lib/layout/images/move_right.png\"/>";
                else
                    HTMLString += "<img class=\"shuffle\" style=\"opacity: 0.5\" src=\"lib/layout/images/move_right.png\"/>";
            }

            $(".multi_selected").empty().append(HTMLString);

            $(".class_selected").click( function() {
                var isAnimating = $("#tooltip .multi_selected .class_selected:animated").length > 0;
                if ((Character.charClass.length > 1) && !isAnimating)
                {
                    var ClassIdent = $(this).attr("id").substr(3);
                    var CharClassIdx = Character.charClass.indexOf(ClassIdent);

                    Character.charClass.splice(CharClassIdx,1);
                    aParentElement.css("background", "url(themes/icons/"+gSite.Iconset+"/classesbig/" + gGame.Classes[Character.charClass[0]].style + ".png)" );

                    if (CharClassIdx == 0)
                    {
                        validateRoles(Character);
                        aOnChangeMain();
                    }

                    rebuildSelected();
                    onUIDataChange();
                }
            });

            $(".shuffle").click( function() {
                var isAnimating = $("#tooltip .multi_selected .class_selected:animated").length > 0;
                if ((Character.charClass.length > 1) && !isAnimating)
                {
                    $("#tooltip .multi_selected .class_selected:first").fadeOut(200, function() {
                        Character.charClass[Character.charClass.length] = Character.charClass[0];
                        Character.charClass.splice(0,1);

                        aParentElement.css("background", "url(themes/icons/"+gSite.Iconset+"/classesbig/" + gGame.Classes[Character.charClass[0]].style + ".png)" );

                        validateRoles(Character);
                        aOnChangeMain();

                        rebuildSelected();
                        onUIDataChange();

                        $("#tooltip .multi_selected .class_selected:last").hide().fadeIn(300);
                    });
                }
            });
        }

        // Available

        if (leftAligned)
            HTMLString += "<div class=\"multi_selected\"></div>";
        else
            HTMLString += "<div class=\"multi_selected\" style=\"text-align: right\"></div>";

        HTMLString += "<div style=\"clear: left\"><div>";

        var ClassCount = 0;
        $.each(gGame.Classes, function(aClassId, aClassDesc)
        {
            if ( (ClassCount > 0) && (ClassCount % 5 == 0) )
                HTMLString += "</div><div style=\"clear: left\">";

            HTMLString += "<span class=\"class_select clickable\" id=\"cs_" + aClassId + "\"><img src=\"themes/icons/"+gSite.Iconset+"/classessmall/" + aClassDesc.style + ".png\"/><br/>" + L(aClassDesc.name) + "</span>";
            ++ClassCount;
        });

        HTMLString += "</div></div>";

        Container.empty().append( HTMLString );
        rebuildSelected();

        $(".class_select").click( function() {
            var ClassIdent = $(this).attr("id").substr(3);

            if (Character.charClass[0] == "empty")
            {
                Character.charClass.splice(0,1);
            }

            if (Character.charClass.indexOf(ClassIdent) == -1)
            {
                Character.charClass.push(ClassIdent);
                aParentElement.css("background", "url(themes/icons/"+gSite.Iconset+"/classesbig/" + gGame.Classes[Character.charClass[0]].style + ".png)" );

                if (Character.charClass.length == 1)
				{
					validateRoles(Character);
                    aOnChangeMain();
				}

                rebuildSelected();
                onUIDataChange();
            }
        });

        showTooltip();

        if ( leftAligned )
        {
            ElementOffset.left += aParentElement.width() + 10;
            setTooltipArrowLeft();
        }
        else
        {
            ElementOffset.left -= Tooltip.width() + 30;
            setTooltipArrowRight();
        }

        Tooltip.offset( ElementOffset );
    }
}

// -----------------------------------------------------------------------------
//  user settings tooltip
// -----------------------------------------------------------------------------

function showUnlinkTooltip( aUserElement, aSticky )
{
    if ( gUser == null )
        return;

    var Tooltip = $("#tooltip");
    Tooltip.data( "sticky", aSticky );

    if ( Tooltip.is(":visible") )
    {
        Tooltip.fadeOut(100);
    }
    else
    {
        var Container = $("#info_text");

        var RefElement = $(".userLinked > .userImage", aUserElement);
        var ElementOffset = RefElement.offset();
        var HTMLString = "<div style=\"width: 200px\">";

        HTMLString += "<img id=\"unlink\" class=\"removeLink clickable\" src=\"lib/layout/images/remove.png\"/>";
        HTMLString += "<div style=\"padding-top: 2px; margin-right: 4px;\">" + L("UnlinkUser") + "</div>";
        HTMLString += "</div>";

        Container.empty().append( HTMLString );

        var UserId = aUserElement.attr("id");

        $("#unlink").click( function() { unlinkUser(UserId); hideTooltip(); } );

        showTooltip();

        ElementOffset.left += RefElement.width() + 5;
        ElementOffset.top  -= 10;

        Tooltip.offset( ElementOffset );
        setTooltipArrowLeft();
    }
}

// -----------------------------------------------------------------------------

function showUserTooltip( aUserElement, aSticky )
{
    if ( gUser == null )
        return;

    var Tooltip = $("#tooltip");
    Tooltip.data( "sticky", aSticky );

    if ( Tooltip.is(":visible") )
    {
        Tooltip.fadeOut(100);
    }
    else
    {
        var Container = $("#info_text");

        var RefElement = $(".userDrag > .userImage", aUserElement);
        var ElementOffset = RefElement.offset();
        var GrpName = aUserElement.parent().attr("id");
        var HTMLString = "<div style=\"margin-bottom: 10px\">"+L("MoveUser")+"</div>";

        HTMLString += "<select id=\"grp_move\" style=\"width: 160px\">";
        HTMLString += "<option value=\"groupBanned\""+((GrpName=="groupBanned") ? " selected" : "")+">"+L("Locked")+"</option>";
        HTMLString += "<option value=\"groupMember\""+((GrpName=="groupMember") ? " selected" : "")+">"+L("Members")+"</option>";
        HTMLString += "<option value=\"groupRaidlead\""+((GrpName=="groupRaidlead") ? " selected" : "")+">"+L("Raidleads")+"</option>";
        HTMLString += "<option value=\"groupAdmin\""+((GrpName=="groupAdmin") ? " selected" : "")+">"+L("Administrators")+"</option>";
        HTMLString += "<option value=\"-\">---</option>";
        HTMLString += "<option value=\"groupSync\">"+L("LinkUser")+"</option>";
        HTMLString += "</select>";

        Container.empty().append( HTMLString );

        var UserId = aUserElement.attr("id");

        $("#grp_move").change( function() {
            moveUserToGroup( UserId, $("#grp_move > option:selected").val());
            hideTooltip();
        });

        $("#grp_move").combobox({ darkBackground: true });

        showTooltip();

        ElementOffset.left += RefElement.width() + 5;
        ElementOffset.top  -= 10;

        Tooltip.offset( ElementOffset );
        setTooltipArrowLeft();
    }
}
