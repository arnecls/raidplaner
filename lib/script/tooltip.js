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

    var PageCenterX = $(document).width() / 2;
    var PageCenterY = $(document).height() / 2;

    Container.empty().append( generateRaidTooltip(aRaid, aLocked, aCalendarView) );
    
    $(".icon", Container).click( function() {
        changeContext("raid,setup," + aRaid.id);
    });
    
    $(".attend", Container).change( function() {
        triggerAttend(this, aRaid.id, aCalendarView); 
    });
    
    $(".commentbadge", Container).click( function(aEvent) {
        showTooltipRaidCommentSwitch(aElement, aRaid, aLocked, aCalendarView);
        aEvent.stopPropagation();
    });

    showTooltip();

    var ClassIcons = new Array();
    for (var i=0; i<gUser.characterIds.length; ++i )
    {
        ClassIcons[gUser.characterIds[i]] = "themes/icons/"+gSite.Iconset+"/classessmall/" + gUser.characterClass[i][0] + ".png";
    }

    $(".attend", Container).combobox({ inlineStyle: { "float":"left" }, darkBackground: true, icons: ClassIcons });

    if ( aCalendarView )
    {
        if ( ElementOffset.top < PageCenterY )
        {
            ElementOffset.top += aElement.height() + 12;

            if ( ElementOffset.left < PageCenterX )
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

            if ( ElementOffset.left < PageCenterX )
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

function triggerAttend( aSelectObj, aRaidId, aCalendarView )
{
    if ( gUser == null )
        return;

    var CharId = aSelectObj.value;

    if ( CharId == -1 )
    {
        prompt( L("WhyAbsent"), L("SetAbsent"), L("Cancel"), function(aText) {
            commitAttend( CharId, aRaidId, aCalendarView, aText );
        });
    }
    else
    {
        commitAttend( CharId, aRaidId, aCalendarView, "" );
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
        var PageCenterX = $(document).width() / 2;
        var PageCenterY = $(document).height() / 2;
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
        
        var ClassIdx = 0;
        
        if (gConfig.ClassMode == "multi")
        {
            for (var i=0; i<aPlayer.className.length; ++i)
            {
                if (gConfig.ClassIdx[aPlayer.className[i]] == aPlayer.activeClass)
                {
                    ClassIdx = i;
                    break;
                }
            }
        }
        
        // Setup switch lists
        
        var AvailableRoles = new Array();
        var CharsByRole = new Array();
        
        for (var i=0; i<aPlayer.characters.length; ++i)
        {
            var Char = aPlayer.characters[i];
        
            if ( gConfig.ClassMode == "multi" )
            {
                for (var c=0; c<Char.className.length; ++c)
                {
                    var ClassIdx = gConfig.ClassIdx[Char.className[c]];
                    var ClassDesc = gConfig.Classes[ClassIdx];
                    var RoleIdx = (ClassIdx == null) ? aCurrentRole : gConfig.RoleIdx[ClassDesc.defaultRole];
                    
                    if (CharsByRole[RoleIdx] == undefined)
                        CharsByRole[RoleIdx] = new Array();
                    
                    if (AvailableRoles.indexOf(RoleIdx) == -1)
                        AvailableRoles.push(RoleIdx);
                        
                    CharsByRole[RoleIdx].push({ charIdx: i, classIdx: ClassIdx, roleIdx: RoleIdx });
                }
            }
            else
            {
                var ClassIdx = gConfig.ClassIdx[Char.className[0]];
                
                if (CharsByRole[Char.firstRole] == undefined)
                    CharsByRole[Char.firstRole] = new Array();
                
                if (AvailableRoles.indexOf(Char.firstRole) == -1)
                    AvailableRoles.push(Char.firstRole);
                    
                CharsByRole[Char.firstRole].push({ charIdx: i, classIdx: ClassIdx, roleIdx: Char.firstRole });
                
                if (Char.secondRole != Char.firstRole)
                {
                    if (CharsByRole[Char.secondRole] == undefined)
                    CharsByRole[Char.secondRole] = new Array();
                
                    if (AvailableRoles.indexOf(Char.secondRole) == -1)
                        AvailableRoles.push(Char.secondRole);
                        
                    CharsByRole[Char.secondRole].push({ charIdx: i, classIdx: ClassIdx, roleIdx: Char.secondRole });
                }
            }
        }
        
        // Setup state
        
        var CurrentListIdx = new Array();
        var AvailableRoleIdx = AvailableRoles.indexOf(aCurrentRole);
                
        for (var i=0; i<AvailableRoles.length; ++i)
        {
            CurrentListIdx[AvailableRoles[i]] = 0;
        }
        
        if (aPlayer.className[0] != "random")
        {
            var CharsOfStartRole = CharsByRole[aCurrentRole];
            
            for (var i=0; i<CharsOfStartRole.length; ++i)
            {
                if (CharsOfStartRole[i].charIdx == CharIdx)
                {
                    if ( gConfig.ClassMode == "multi" )
                    {
                        if (CharsOfStartRole[i].classIdx != aPlayer.activeClass)
                            continue;
                    }
                    
                    CurrentListIdx[aCurrentRole] = i;
                    break;
                }
            }
        }
        
        // Layout tooltip
        
        aSlotElement.data("setup_info", { charIdx: CharIdx, classIdx: aPlayer.activeClass });
        
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
                HTMLString += "<img class=\"role\" style=\"opacity: "+Opacity+"\" src=\"lib/layout/images/icon_"+gConfig.Roles[AvailableRoles[RoleIdx]].style+".png\"/>";
            }
        }
        else
        {
            HTMLString += "<img class=\"single_role\" src=\"lib/layout/images/icon_"+gConfig.Roles[aCurrentRole].style+".png\"/>";
        }
        HTMLString += "</div>";
        
        // Char switcher
        
        HTMLString += "<div class=\"char_select"+((CharsByRole[aCurrentRole].length > 1) ? " clickable" : "")+"\">";
        HTMLString += "<div class=\"switch_char_next\"></div>";    
        HTMLString += "<div class=\"mainchar\"></div>";
        HTMLString += "</div>";
        
        if (gUser.isRaidlead && (aPlayer.className[0] != "random") &&
            (aPlayer.status != "unavailable") &&  (aPlayer.status != "undecided"))
        {
            HTMLString += "<button class=\"switch_button\">"+L("Switch")+"</button>";
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
        
        // Switch mechanics
        
        $(".switch_button", Container).click( function() {
            var RoleIdx = AvailableRoles[AvailableRoleIdx];
            var SelectedChar = CharsByRole[RoleIdx][CurrentListIdx[RoleIdx]];
            
            aPlayer.switchTo(SelectedChar.charIdx, RoleIdx, SelectedChar.classIdx);
            onUIDataChange();
            hideTooltip();
        });
        
        var RoleSelectField = $(".role_select", Tooltip);
        var CharSelectField = $(".char_select", Tooltip);
        var MainCharField = $(".mainchar", CharSelectField);
        var NameField = $(".name", Tooltip);
        var DetailField = $(".detail", Tooltip);
        
        var onUpdateCharField = function() 
        {
            var RoleIdx = AvailableRoles[AvailableRoleIdx];
            var SelectedChar = CharsByRole[RoleIdx][CurrentListIdx[RoleIdx]];
            var ClassIdx = SelectedChar.classIdx;
            var ActiveChar = aPlayer.characters[SelectedChar.charIdx];
            var Name = ActiveChar.name;
            
            // Class and name
            
            var ClassImage = (ClassIdx == null) ? "random" : gConfig.Classes[ClassIdx].ident;
            CharSelectField.css("background-image", "url(themes/icons/"+gSite.Iconset+"/classesbig/"+ClassImage+".png)");
            
            NameField.empty().append(Name);
            
            // Additional information
            
            if ( gConfig.ClassMode == "single")
            {            
                var RoleInfo = L(gConfig.Roles[ActiveChar.firstRole].text) + 
                    ((ActiveChar.firstRole != ActiveChar.secondRole)
                        ? ", " + gConfig.Roles[ActiveChar.secondRole].text
                        : "");
                
                DetailField.empty().append(RoleInfo);
            }
            else 
            {
                DetailField.empty();
                
                for (var i=0; i<ActiveChar.className.length; ++i)
                {
                    if (ActiveChar.className[i] == "random")
                    {
                        DetailField.append(L("Random"));
                    }
                    else
                    {
                        var cidx = gConfig.ClassIdx[ActiveChar.className[i]];
                        DetailField.append(((i > 0) ? ", " : "") + gConfig.Classes[cidx].text);
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
            
            if (CharsByRole[RoleIdx].length > 1)
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
                $(aSlotElement).css("backgroundImage", "url(themes/icons/"+gSite.Iconset+"/classessmall/"+ClassImage+".png)");
                $(".playerName",aSlotElement.parent()).empty().append(Name);
            }
        };
        
        onUpdateCharField();
        
        // Function bindings
        
        CharSelectField.click(function() {
            var RoleIdx = AvailableRoles[AvailableRoleIdx];
            CurrentListIdx[RoleIdx] = (CurrentListIdx[RoleIdx] + 1) % CharsByRole[RoleIdx].length;
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

        if ( ElementOffset.left < PageCenterX )
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
    var ClassIdx = gConfig.ClassIdx[aCharacter.charClass[0]];
    var ClassDesc = gConfig.Classes[ClassIdx];
    var DefaultRoleIdx = gConfig.RoleIdx[ ClassDesc.defaultRole ];
    
    if ( gConfig.ClassMode == "multi" )
    {
        aCharacter.role1 = DefaultRoleIdx;
        aCharacter.role2 = DefaultRoleIdx;
    }
    else
    {       
        var AllowedRoles = [];
        for ( i=0; i<ClassDesc.roles.length; ++i )
        {
            AllowedRoles.push(gConfig.RoleIdx[ClassDesc.roles[i]]);
        }
                
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
            aCharacter.role1 = DefaultRoleIdx;
    
        if ( !foundRole2 )
            aCharacter.role2 = DefaultRoleIdx;
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

        var PageCenterX = $(document).width() / 2;
        var PageCenterY = $(document).height() / 2;

        var HTMLString = "<div><div>";

        for ( var i=1; i<gConfig.Classes.length; ++i )
        {
            if ( (i>1) && ((i-1)%5 === 0) )
                HTMLString += "</div><div style=\"clear: left\">";

            HTMLString += "<span class=\"class_select clickable\" id=\"cs_" + gConfig.Classes[i].ident + "\"><img src=\"themes/icons/"+gSite.Iconset+"/classessmall/" + gConfig.Classes[i].ident + ".png\"/><br/>" + gConfig.Classes[i].text + "</span>";
        }

        HTMLString +=  "</div></div>";

        Container.empty().append( HTMLString );

        $(".class_select").click( function() {
            var ClassIdent = $(this).attr("id").substr(3);
            var CharIdx = parseInt( aParentElement.parent().attr("id").substr(4), 10 );
            var CharList = $("#charlist").data("characters");
            var Character = CharList.mCharacters[CharIdx];

            Character.charClass = new Array(ClassIdent);
            
            aParentElement.css("background", "url(themes/icons/"+gSite.Iconset+"/classesbig/" + ClassIdent + ".png)" );
            
            validateRoles(Character);
            aOnChange();
            
            hideTooltip();
            onUIDataChange();
        });

        showTooltip();

        if ( ElementOffset.left < PageCenterX )
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

        var PageCenterX = $(document).width() / 2;
        var PageCenterY = $(document).height() / 2;
        
        var leftAligned = ElementOffset.left < PageCenterX;

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
                var classIdx = gConfig.ClassIdx[Character.charClass[i]];
                if (classIdx == 0)
                    continue;
                    
                if ( (added>0) && (added%6 === 0) )
                    HTMLString += "</div><div style=\"height: 42px; clear: left\">";
                
                var AlignStyle = (leftAligned) ? "float: left" : "float: right";
                HTMLString += "<img class=\"class_selected"+((Character.charClass.length > 1) ? " clickable" : "")+"\" style=\""+AlignStyle+"\" id=\"cs_" + gConfig.Classes[classIdx].ident + "\" src=\"themes/icons/"+gSite.Iconset+"/classessmall/" + gConfig.Classes[classIdx].ident + ".png\"/>";
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
                    var ClassIdx = Character.charClass.indexOf(ClassIdent);
                    
                    Character.charClass.splice(ClassIdx,1);
                    aParentElement.css("background", "url(themes/icons/"+gSite.Iconset+"/classesbig/" + Character.charClass[0] + ".png)" );            
                
                    if (ClassIdx == 0)
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
                        
                        aParentElement.css("background", "url(themes/icons/"+gSite.Iconset+"/classesbig/" + Character.charClass[0] + ".png)" );            
                    
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

        for ( var i=1; i<gConfig.Classes.length; ++i )
        {
            if ( (i>1) && ((i-1)%5 === 0) )
                HTMLString += "</div><div style=\"clear: left\">";

            HTMLString += "<span class=\"class_select clickable\" id=\"cs_" + gConfig.Classes[i].ident + "\"><img src=\"themes/icons/"+gSite.Iconset+"/classessmall/" + gConfig.Classes[i].ident + ".png\"/><br/>" + gConfig.Classes[i].text + "</span>";
        }

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
                Character.charClass[Character.charClass.length] = ClassIdent;
                aParentElement.css("background", "url(themes/icons/"+gSite.Iconset+"/classesbig/" + Character.charClass[0] + ".png)" );            
                
                if (Character.charClass.length == 1)
                    aOnChangeMain();
                    
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

        $("#grp_move").change( function() { moveUserToGroup( UserId, $("#grp_move > option:selected").val()); hideTooltip(); } );
        $("#grp_move").combobox({ darkBackground: true });

        showTooltip();

        ElementOffset.left += RefElement.width() + 5;
        ElementOffset.top  -= 10;
        
        Tooltip.offset( ElementOffset );
        setTooltipArrowLeft();
    }
}