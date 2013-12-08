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
        var RaidElement = aParentElement;
        var Container = $("#info_text");
        var Tooltip = $("#tooltip");
        var ElementOffset = RaidElement.offset();

        var PageCenterX = $(document).width() / 2;
        var PageCenterY = $(document).height() / 2;

        var InfoClone = RaidElement.children(".tooltip").clone();
        Container.empty().append( InfoClone );

        showNewTooltip();
        
        var SelectField = $(".functions > select", InfoClone);

        if ( SelectField.size() > 0 )
            SelectField.attr( "id", "active" + SelectField.attr("id").substr(6) );
        
        var ClassIcons = new Array();
        for (var i=0; i<gUser.characterIds.length; ++i )
        {
            ClassIcons[gUser.characterIds[i]] = "images/classessmall/" + gUser.characterClass[i] + ".png";
        }

        SelectField.combobox({ darkBackground: true, icons: ClassIcons });

        ElementOffset.left += RaidElement.width() / 2;
        ElementOffset.top  += RaidElement.height() / 2;

        if ( aCalendarView )
        {
            if ( ElementOffset.top < PageCenterY )
            {
                ElementOffset.top += RaidElement.height() / 2;

                if ( ElementOffset.left < PageCenterX )
                {
                    ElementOffset.left -= 40;

                    Tooltip.offset( ElementOffset );
                    setTooltipArrowTopLeft();
                }
                else
                {
                    ElementOffset.left -= Tooltip.width() + RaidElement.width() / 2 - 76;

                    Tooltip.offset( ElementOffset );
                    setTooltipArrowTopRight();
                }
            }
            else
            {
                ElementOffset.top -= Tooltip.height() + RaidElement.height() / 2;

                if ( ElementOffset.left < PageCenterX )
                {
                    ElementOffset.left -= 40;

                    Tooltip.offset( ElementOffset );
                    setTooltipArrowBottomLeft();
                }
                else
                {
                    ElementOffset.left -= Tooltip.width() + RaidElement.width() / 2 - 49;

                    Tooltip.offset( ElementOffset );
                    setTooltipArrowBottomRight();
                }
            }
        }
        else
        {
            ElementOffset.left += RaidElement.width() / 2 - 5;
            ElementOffset.top  -= RaidElement.height() / 2 + 10;
            
            Tooltip.offset( ElementOffset );
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

    var SelectField = $(".functions > select", InfoClone);

    if ( SelectField.size() > 0 )
        SelectField.attr( "id", "active" + SelectField.attr("id").substr(6) );
        
    var ClassIcons = new Array();
    for (var i=0; i<gUser.characterIds.length; ++i )
    {
        ClassIcons[gUser.characterIds[i]] = "images/classessmall/" + gUser.characterClass[i] + ".png";
    }

    SelectField.combobox({ darkBackground: true, icons: ClassIcons });
}

// -----------------------------------------------------------------------------

function showTooltipRaidComment( aRaidId )
{
    var RaidElement = $("#raid"+ aRaidId);
    var Container = $("#info_text");
    var OldWidth = $("#tooltip").width();

    var InfoClone = RaidElement.children(".comment").clone();
    Container.empty().append( InfoClone );

    $(".comment > .text", Container).css( "width", OldWidth - 43 );
    $(".comment > button", Container).button({ icons: { secondary: "ui-icon-disk" }})
        .css({ "font-size" : 11, "height" : 24 });
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
        raidId     : aRaidId,
        comment    : aButtonElement.parent().children(".text").val()
    };

    asyncQuery( "comment_update", Parameters, ( aCalendarView ) ? generateCalendar : loadAllRaids );
}

// -----------------------------------------------------------------------------
//    RaidImageList tooltip
// -----------------------------------------------------------------------------

function applyLocationImage( aImageObj, aUIDataChange )
{
    var Image = aImageObj.src.substr( aImageObj.src.lastIndexOf("/") + 1 );
    $("#locationimagepicker").css( "background-image", "url(images/raidbig/" + Image + ")" );
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

    Element.css( "background-image", "url(images/raidsmall/" + Image + ")" );
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
            
        $("#locationimagepicker").css( "background-image", "url(images/raidbig/unknown.png)" );
    }
    else
    {
        $(aSelectObj).combobox("editable", false);

        var ImageName = $("#locationimagepicker").data("imageNames")[aSelectObj.selectedIndex - 1];

        $("#locationimagepicker").css( "background-image", "url(images/raidbig/"+ ImageName + ")" );
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

        showNewTooltip();

        var ElementOffset = aElement.offset();

        ElementOffset.left -= (aElement.width() > 32) ? 6 : 24;
        ElementOffset.top += aElement.height() - 2;

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

function showTooltipCharChooser( aSlotElement, aPlayer, aMakeSticky, aOnHide )
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
        
        aSlotElement.data("setup_info", CharIdx);
        
        // Configure the tooltip
        
        var IconClass = "playerTooltipIconLarge";
        if ( aPlayer.characters.length > 1 )
          IconClass += " clickable";

        HTMLString += "<div class=\""+IconClass+"\" style=\"background-image: url('images/classesbig/"+aPlayer.className+".png')\">";

        if ( aPlayer.characters.length > 1 )
        {
            HTMLString += "<div class=\"twinkcount\">1/"+(aPlayer.characters.length)+"</div>";
        }

        if ( (aPlayer.characters[CharIdx] != undefined) && aPlayer.characters[CharIdx].mainchar )
            HTMLString += "<div class=\"mainbadge\"></div>";
        
        HTMLString += "</div>";
        
        HTMLString += "<div style=\"float: left\">";
        HTMLString += "<div class=\"playerTooltipRoleIcon\" style=\"background-image: url('images/roles/"+gConfig.RoleIdents[aPlayer.firstRole]+".png')\"><div class=\"mainbadge\"></div></div>";
        HTMLString += "<div class=\"playerTooltipRoleIcon\" style=\"background-image: url('images/roles/"+gConfig.RoleIdents[aPlayer.secondRole]+".png')\"></div>";
        HTMLString += "</div>";
        
        HTMLString += "<div class=\"playerTooltipText\">";
        HTMLString += "<div class=\"name\">"+aPlayer.name+"</div>";

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
            
            HTMLString += "</div>";
        }
        else
        {
            var FilteredText = aPlayer.comment.replace(/(http[s]*:\/\/\S+)/g, "<a href=\"$1\" target=\"_blank\">$1</a>");
            HTMLString += FilteredText + "</div>";
        }
        
        Container.empty().append( HTMLString );

        // Configure character chooser
        
        if ( aPlayer.characters.length > 1 )
        {
            $(".playerTooltipIconLarge").click( function() {
                var CharIdx = aSlotElement.data("setup_info")+1;

                if ( CharIdx >= aPlayer.characters.length )
                    CharIdx = 0;

                aSlotElement.data("setup_info", CharIdx);
                $(this).css("background-image", "url(images/classesbig/"+aPlayer.characters[CharIdx].className+".png)");
                
                $(this).siblings().children(".playerTooltipRoleIcon:first").css("background-image", "url(images/roles/"+gConfig.RoleIdents[aPlayer.characters[CharIdx].firstRole]+".png)");
                $(this).siblings().children(".playerTooltipRoleIcon:last").css("background-image", "url(images/roles/"+gConfig.RoleIdents[aPlayer.characters[CharIdx].secondRole]+".png)");

                HTMLString = "<div class=\"twinkcount\">"+(CharIdx+1)+"/"+(aPlayer.characters.length)+"</div>";
                
                if ( aPlayer.characters[CharIdx].mainchar )
                    HTMLString += "<div class=\"mainbadge\"></div>";
                
                $(this).empty().append( HTMLString );                
                $(".playerTooltipText .name").empty().append( aPlayer.characters[CharIdx].name );
                
                if ( gUser.isRaidlead )
                {
                    aSlotElement.siblings(".playerName:first").empty().append( aPlayer.characters[CharIdx].name );
                }                
            });
        }
        
        // Show tooltip

        showNewTooltip();
        Tooltip.data("onHide", aOnHide);

        ElementOffset.left += aSlotElement.width() / 2 + 1;
        ElementOffset.top  += aSlotElement.height() / 2 - (Tooltip.height() + 15);

        if ( ElementOffset.left < PageCenterX )
        {
            ElementOffset.left -= 40;

            Tooltip.offset( ElementOffset );
            setTooltipArrowBottomLeft();
        }
        else
        {
            ElementOffset.left -= Tooltip.width() + aSlotElement.width() / 2 - 49;

            Tooltip.offset( ElementOffset );
            setTooltipArrowBottomRight();
        }
    }
}

// -----------------------------------------------------------------------------
//  Class/role selector tooltip
// -----------------------------------------------------------------------------

function showTooltipClassList( aParentElement )
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

        var HTMLString = "<span><div>";

        for ( var i=1; i<gConfig.Classes.length; ++i )
        {
            if ( (i>1) && ((i-1)%5 === 0) )
                HTMLString += "</div><div style=\"clear: left\">";

            HTMLString += "<span class=\"class_select\" id=\"cs_" + gConfig.Classes[i].ident + "\"><img src=\"images/classessmall/" + gConfig.Classes[i].ident + ".png\"/><br/>" + gConfig.Classes[i].text + "</span>";
        }

        HTMLString +=  "</div></span>";

        Container.empty().append( HTMLString );

        $(".class_select").click( function() {
            var ClassIdent = $(this).attr("id").substr(3);
            var ClassDesc = gConfig.Classes[gConfig.ClassIdx[ClassIdent]];
            var DefaultRoleId = gConfig.RoleIds[ ClassDesc.defaultRole ];
            
            var AllowedRoles = [];
            for ( i=0; i<ClassDesc.roles.length; ++i )
            {
                AllowedRoles.push(gConfig.RoleIds[ClassDesc.roles[i]]);
            }
            
            aParentElement.css("background", "url(images/classesbig/" + ClassIdent + ".png)" );
            
            var RoleDiv = aParentElement.nextAll(".role_group");
            
            var CharIdx = parseInt( aParentElement.parent().attr("id").substr(4), 10 );
            var CharList = $("#charlist").data("characters");
            var Character = CharList.mCharacters[CharIdx];
            
            Character.charClass = ClassIdent;
            var foundRole1 = false;
            var foundRole2 = false;
            
            for ( i=0; i<AllowedRoles.length; ++i )
            {
                if ( AllowedRoles[i] == Character.role1 )
                    foundRole1 = true;
                    
                if ( AllowedRoles[i] == Character.role2 )
                    foundRole2 = true;
            }
            
            if ( !foundRole1 )
                Character.role1 = DefaultRoleId;
            
            if ( !foundRole2 )
                Character.role2 = DefaultRoleId;
            
            RoleDiv.children(".newrole1").css("background-image", "url(images/roles/" + gConfig.RoleIdents[Character.role1] + ".png)");
            RoleDiv.children(".newrole2").css("background-image", "url(images/roles/" + gConfig.RoleIdents[Character.role2] + ".png)");
            
            hideTooltip();                
            onUIDataChange();
        });

        showNewTooltip();
        
        ElementOffset.top  += aParentElement.height() / 2 - 43;
        ElementOffset.left += aParentElement.width() / 2 + 22;

        Tooltip.offset( ElementOffset );
        setTooltipArrowLeft();
    }
}

// -----------------------------------------------------------------------------

function showTooltipRoleList( aParentElement, aEditRole1 )
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

        var CharIdx = parseInt( aParentElement.parent().parent().attr("id").substr(4), 10 );
        var CharList = $("#charlist").data("characters");        
        var ClassString = CharList.mCharacters[CharIdx].charClass;

        if ( (aEditRole1 && (CharList.mCharacters[CharIdx].role1 < 0)) ||
             (CharList.mCharacters[CharIdx].role2 < 0) )
        {
            return; // ### return, invalid role ###
        }
        
        var HTMLString = "<span>";
        var ClassData = gConfig.Classes[gConfig.ClassIdx[ClassString]];

        for ( var i=0; i<ClassData.roles.length; ++i )
        {
            var RoleIdent = ClassData.roles[i];
            HTMLString += "<span class=\"class_select\" id=\"cs_" + RoleIdent + "\"><img src=\"images/roles/" + RoleIdent + ".png\"/><br/>" + gConfig.RoleNames[ RoleIdent ] + "</span>";
        }

        HTMLString +=  "</span>";

        Container.empty().append( HTMLString );

        $(".class_select").click( function() {
            var RoleIdent = $(this).attr("id").substr(3);

            aParentElement.css("background-image", "url(images/roles/" + RoleIdent + ".png)" );
            
            if ( aEditRole1 )
                CharList.mCharacters[CharIdx].role1 = gConfig.RoleIds[RoleIdent];
            else
                CharList.mCharacters[CharIdx].role2 = gConfig.RoleIds[RoleIdent];

            hideTooltip();                
            onUIDataChange();
        });

        showNewTooltip();

        ElementOffset.top  += aParentElement.height() / 2 - 38;
        ElementOffset.left += aParentElement.width() / 2 + 10;

        Tooltip.offset( ElementOffset );
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

        showNewTooltip();

        ElementOffset.left += RefElement.width();
        ElementOffset.top  += (RefElement.height() / 2) - (Tooltip.height() / 2) + 4;

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

        showNewTooltip();

        ElementOffset.left += RefElement.width();
        ElementOffset.top  += (RefElement.height() / 2) - (Tooltip.height() / 2) + 4;

        Tooltip.offset( ElementOffset );
        setTooltipArrowLeft();
    }
}