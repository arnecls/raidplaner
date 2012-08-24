// -----------------------------------------------------------------------------
//	Generic tooltip functions
// -----------------------------------------------------------------------------

function delayedFadeTooltip()
{
	if ( $("#tooltip").data("sticky") == false )
		$("#tooltip").delay(200).fadeOut(100);
}

// -----------------------------------------------------------------------------

function startFadeTooltip()
{
	$("#tooltip").fadeOut(100);
}


// -----------------------------------------------------------------------------

function hideTooltip()
{
	$("#tooltip").stop(true, true).clearQueue().hide().css("opacity", "0");
}

// -----------------------------------------------------------------------------

function showTooltip()
{
	var tooltip = $("#tooltip");
    tooltip.stop(true, true).clearQueue().show().css("opacity", "1");
    
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
    
    if ( $.browser.msie )
    {
        resetTooltipArrows();
        
        if ( $.browser.version < 8 )
        {
            $("#tooltip").width( "auto" );     

			var firstChild = $("#info_text").children().first();
			
			if ( firstChild.data("src") )
				$("#tooltip").width( firstChild.data("src").width() + 46 );
			else			
				$("#tooltip").width( $("#info_text").children().first().width() + 26 + 18 + 20 );
        }
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
    $("#info_arrow_tl") .width( "auto" );
    $("#info_arrow_tr") .width( "auto" );
    $("#info_arrow_bl") .width( "auto" );
    $("#info_arrow_br") .width( "auto" );
    $("#info_arrow_ml") .height( "auto" );
    $("#info_arrow_ml2").height( "auto" );
}

// -----------------------------------------------------------------------------

function setTooltipArrowTopLeft()
{
    if ( $.browser.msie )
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
    if ( $.browser.msie )
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
    if ( $.browser.msie )
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
    if ( $.browser.msie )
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
    if ( $.browser.msie )
    {
    	resetTooltipArrows();
        
    	$("#info_arrow_ml").height( 45 );
    	$("#info_arrow_ml2").height( $("#info_arrow_ml").parent().height() - 45 );
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
	$("#info_arrow_ml").removeClass( "left" )   .addClass( "arrow" ) .css( "width", 26 );
}

// -----------------------------------------------------------------------------
//	RaidInfo tooltip
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
		tooltip.css("z-index", 10);
		
		var selectField = infoClone.children(".functions").children("select");
		
		if ( selectField.size() > 0 )
			selectField.attr( "id", "active" + selectField.attr("id").substr(6) );
			
		selectField.combobox();
		
		elementOffset.left += raidElement.width() / 2;
		elementOffset.top  += raidElement.height() / 2;
		
		if ( a_CalendarView )
		{	
			if ( elementOffset.top < pageCenterY )
			{
				elementOffset.top += raidElement.height() / 2 - 5;
				
				if ( elementOffset.left < pageCenterX )
				{
					elementOffset.left -= 46;
					
					tooltip.offset( elementOffset );
					setTooltipArrowTopLeft();
				}
				else
				{
					elementOffset.left -= tooltip.width() + raidElement.width() / 2 - 80;
					
					$("#tooltip").offset( elementOffset );
					setTooltipArrowTopRight();
				}	
			}
			else
			{
				elementOffset.top -= tooltip.height() + raidElement.height() / 2 - 10;
				
				if ( elementOffset.left < pageCenterX )
				{
					elementOffset.left -= 46;
					
					tooltip.offset( elementOffset );
					setTooltipArrowBottomLeft();
				}
				else
				{
					elementOffset.left -= tooltip.width() + raidElement.width() / 2 - 56;
					
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
		
	selectField.combobox();
}

// -----------------------------------------------------------------------------

function displayRaidTooltipComment( a_RaidId )
{	
	var raidElement = $("#raid"+ a_RaidId);
	var container = $("#info_text");
	var oldWidth = $("#tooltip").width();
	
	var infoClone = raidElement.children(".comment").clone();
	container.empty().append( infoClone );
	
	container.children(".comment").children(".text").css( "width", oldWidth - 54 );
	container.children(".comment").children("button").button({ icons: { secondary: "ui-icon-disk" }})
		.css( "font-size", 11 )
		.css( "height", 24 );
}

// -----------------------------------------------------------------------------

function commitAttend( a_Index, a_Raid, a_CalendarView, a_CommentFromPrompt )
{
	var Parameters = {
		attendanceIndex : a_Index,
	    fallback		: 0,
	    raidId			: a_Raid,
	    comment			: a_CommentFromPrompt ? $("#prompt_text").val() : ""
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
//	RaidImageList tooltip
// -----------------------------------------------------------------------------

function applyLocationImage( a_ImageObj )
{
	var image = a_ImageObj.src.substr( a_ImageObj.src.lastIndexOf("/") + 1 );
	$("#locationimagepicker").css( "background-image", "url(images/raidbig/" + image + ")" );
	$("#locationimagepicker").data( "selectedImage", image );
	
	startFadeTooltip();
}

// -----------------------------------------------------------------------------

function applyLocationImageExternal( a_ImageObj )
{
	var image = a_ImageObj.src.substr( a_ImageObj.src.lastIndexOf("/") + 1 );
	var element = $("#locationimagelist").data("external");
	
	element.css( "background-image", "url(images/raidsmall/" + image + ")" );
	element.data( "selectedImage", image );
	
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
		
		if ( $.browser.msie && ($.browser.version < 8) )
			container.children().first().data( "src", $("#locationimagelist") );
	    
		tooltip.css("z-index", 130);		
		showNewTooltip();
		
		var elementOffset = a_Element.offset();
		
		elementOffset.left -= (a_Element.width() > 32) ? 14 : 28;
		elementOffset.top += a_Element.height() - 4;
		
		tooltip.offset( elementOffset );
		setTooltipArrowTopLeft();
	}
}

// -----------------------------------------------------------------------------

function showTooltipRaidImageList()
{	
	showTooltipRaidImageListAtElement( $("#locationimagepicker") );
}

// -----------------------------------------------------------------------------
//  comment tooltip
// -----------------------------------------------------------------------------

function showAttendeeTooltip( a_SlotElement, a_Text, a_MakeSticky )
{
	if ( !toggleStickyState(a_SlotElement, a_MakeSticky)  )
	{
		var tooltip = $("#tooltip");
	    
		var container = $("#info_text");
		var elementOffset = a_SlotElement.offset();
		var pageCenterX = $(document).width() / 2;
		var pageCenterY = $(document).height() / 2;
		
		container.empty().append( a_Text );
		
		showNewTooltip();
		tooltip.css("z-index", 10);		
		
		elementOffset.left += a_SlotElement.width() / 2;
		elementOffset.top  += a_SlotElement.height() / 2;
		
		elementOffset.top -= tooltip.height() + 4;
		
		if ( elementOffset.left < pageCenterX )
		{
			elementOffset.left -= 46;
			
			tooltip.offset( elementOffset );
			setTooltipArrowBottomLeft();
		}
		else
		{
			elementOffset.left -= tooltip.width() + a_SlotElement.width() / 2 - 56;
			
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
		
		var HTMLString = "<span>";
		
		for ( i=1; i<g_Classes.length; ++i )
		{
			HTMLString += "<span class=\"class_select\" id=\"cs_" + g_Classes[i].ident + "\"><img src=\"images/classessmall/" + g_Classes[i].ident + ".png\"/><br/>" + g_Classes[i].text + "</span>";
			if ( i==6 )
				HTMLString += "<br/>"; 
		}
	    
	    HTMLString +=  "</span>";
			
		container.empty().append( HTMLString );
		
		$(".class_select").click( function() { 
			var classIdent = $(this).attr("id").substr(3);
			var defaultRoleIdent = g_Classes[g_ClassIdx[classIdent]].roles[0];
			
			a_ParentElement.css("background", "url(images/classesbig/" + classIdent + ".png)" );
			
			a_ParentElement.parent().data("charData").charClass = classIdent;
			a_ParentElement.parent().data("charData").role1 = defaultRoleIdent;
			a_ParentElement.parent().data("charData").role2 = defaultRoleIdent;
			
			a_ParentElement.parent().children("span").children(".role").attr("src", "images/classessmall/"+defaultRoleIdent+".png" );
			
			hideTooltip(); 
		});
			
		showNewTooltip();
		tooltip.css("z-index", 10);
			
		elementOffset.top  += a_ParentElement.height() / 2 - 50;
		elementOffset.left += a_ParentElement.width() / 2 + 16;
		
		tooltip.offset( elementOffset );
		setTooltipArrowLeft();
	}
}

// -----------------------------------------------------------------------------

function showTooltipRoleList( a_ParentElement, a_Role1 )
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
		
		var classString = a_ParentElement.parent().parent().data("charData").charClass;
		
		var HTMLString = "<span>";
		
		var classData = g_Classes[g_ClassIdx[classString]];
		
		for ( var i=0; i<classData.roles.length; ++i )
		{
			var roleIdent = classData.roles[i];
			HTMLString += "<span class=\"class_select\" id=\"cs_" + roleIdent + "\"><img src=\"images/classessmall/" + roleIdent + ".png\"/><br/>" + g_RoleNames[ roleIdent ] + "</span>";
		}
	    
	    HTMLString +=  "</span>";
	    
		container.empty().append( HTMLString );
		
		$(".class_select").click( function() { 
			var roleIdent = $(this).attr("id").substr(3);
			
			a_ParentElement.attr("src", "images/classessmall/" + roleIdent + ".png" );
			
			if ( a_Role1 )
				a_ParentElement.parent().parent().data("charData").role1 = g_RoleIds[roleIdent];
			else
				a_ParentElement.parent().parent().data("charData").role2 = g_RoleIds[roleIdent];
			
			hideTooltip(); 
		});
			
		showNewTooltip();
		tooltip.css("z-index", 10);
			
		elementOffset.top  += a_ParentElement.height() / 2 - 43;
		elementOffset.left += a_ParentElement.width() / 2;
		
		tooltip.offset( elementOffset );
		setTooltipArrowLeft();
	}
}

// -----------------------------------------------------------------------------
//  comment tooltip
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
		var HTMLString = "<div style=\"width: 145px\">";
		var currentGrp = 0;
		
		switch ( grpName )
		{
		case "groupBanned":
			HTMLString += "<div class=\"grp_move_right\" style=\"left: 84px\"></div>";
			HTMLString += "<div class=\"grp_move_right\" style=\"left: 120px\"></div>";
			HTMLString += "<div class=\"grp_move_right\" style=\"left: 156px\"></div>";
			currentGrp = 0;
			break;
			
		case "groupMember":
			HTMLString += "<div class=\"grp_move_left\" style=\"left: 48px\"></div>";
			HTMLString += "<div class=\"grp_move_right\" style=\"left: 120px\"></div>";
			HTMLString += "<div class=\"grp_move_right\" style=\"left: 156px\"></div>";
			currentGrp = 1;
			break;
			
		case "groupRaidlead":
			HTMLString += "<div class=\"grp_move_left\" style=\"left: 48px\"></div>";
			HTMLString += "<div class=\"grp_move_left\" style=\"left: 84px\"></div>";
			HTMLString += "<div class=\"grp_move_right\" style=\"left: 156px\"></div>";
			currentGrp = 2;
			break;
			
		case "groupAdmin":
			HTMLString += "<div class=\"grp_move_left\" style=\"left: 48px\"></div>";
			HTMLString += "<div class=\"grp_move_left\" style=\"left: 84px\"></div>";
			HTMLString += "<div class=\"grp_move_left\" style=\"left: 120px\"></div>";
			currentGrp = 3;
			break;
			
		default:
			break;
		}
		
		HTMLString += L("MoveUser") + "<br/>";
		HTMLString += "<img id=\"userTo0\" class=\"" + ((currentGrp == 0) ? "grpicon_off" : "grpicon") + "\" src=\"lib/layout/images/icon_grp0.png\"/>";
		HTMLString += "<img id=\"userTo1\" class=\"" + ((currentGrp == 1) ? "grpicon_off" : "grpicon") + "\" src=\"lib/layout/images/icon_grp1.png\"/>";
		HTMLString += "<img id=\"userTo2\" class=\"" + ((currentGrp == 2) ? "grpicon_off" : "grpicon") + "\" src=\"lib/layout/images/icon_grp2.png\"/>";
		HTMLString += "<img id=\"userTo3\" class=\"" + ((currentGrp == 3) ? "grpicon_off" : "grpicon") + "\" src=\"lib/layout/images/icon_grp3.png\"/>";
		HTMLString += "</div>";
		
		container.empty().append( HTMLString );
			
		var userId = a_UserElement.attr("id");
		
		$("#userTo0").click( function() { moveUserToGroup( userId, "groupBanned"); hideTooltip(); } );
		$("#userTo1").click( function() { moveUserToGroup( userId, "groupMember"); hideTooltip(); } );
		$("#userTo2").click( function() { moveUserToGroup( userId, "groupRaidlead"); hideTooltip(); } );
		$("#userTo3").click( function() { moveUserToGroup( userId, "groupAdmin"); hideTooltip(); } );
		
		showNewTooltip();
		tooltip.css("z-index", 10);		
		
		elementOffset.left += refElement.width() - 10;
		elementOffset.top  += (refElement.height() / 2) - (tooltip.height() / 2) + 10;
		
		tooltip.offset( elementOffset );
		setTooltipArrowLeft();
	}
}