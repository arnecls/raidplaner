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
    $("#tooltip").stop(true, true).clearQueue().show().css("opacity", "1");
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
            $("#tooltip").width( $("#info_text").children().first().width() + 26 + 18 + 20 );
        }
    }
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

function showTooltipRaidInfo( a_ParentElement, a_CalendarView, a_Sticky )
{	
	if ( g_User == null ) 
		return;
		
	$("#tooltip").data("sticky", a_Sticky);
		
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

// -----------------------------------------------------------------------------
//	edit comment tooltip
// -----------------------------------------------------------------------------

function showTooltipEditComment( a_RaidId, a_CalendarView )
{
	if ( g_User == null ) 
		return;
		
	var raidElement = $("#raid"+ a_RaidId);
	var container = $("#info_text");
	var tooltip = $("#tooltip");
	var elementOffset = raidElement.offset();
	
	var oldWidth = tooltip.width();
	
	var pageCenterX = $(document).width() / 2;
	var pageCenterY = $(document).height() / 2;
	
	var infoClone = raidElement.children(".comment").clone();
	container.empty().append( infoClone );
	
	//showTooltip();
	tooltip.css("z-index", 10);
	
	container.children(".comment").children(".text").css( "width", oldWidth - 54 );
	container.children(".comment").children("button").button({ icons: { secondary: "ui-icon-disk" }})
		.css( "font-size", 11 )
		.css( "height", 24 );
	
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

// -----------------------------------------------------------------------------

function triggerAttend( selectObj, a_CalendarView )
{
	if ( g_User == null ) 
		return;
	
	var comment = "";
	var unattend = false;
	
	if ( selectObj.value == -1 )
	{
		comment = window.prompt("Warum musst du dich abmelden?");
		
		if ( comment != null )
			unattend = true; 
	} 
	
	if ( (selectObj.value > 0) || unattend )
	{
		var Parameters = {
			attendanceIndex : selectObj.value,
	        fallback		: 0,
	        raidId			: selectObj.id.substr(6),
	        comment			: comment
	    };
	    
	    AsyncQuery( "raid_attend", Parameters, ( a_CalendarView ) ? displayCalendar : loadAllRaids );
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

function showTooltipRaidImageList()
{	
	var tooltip = $("#tooltip");
	
	if ( tooltip.is(":visible") )
	{
		tooltip.fadeOut(100);
	}
	else
	{	
		var container = $("#info_text");
		container.empty().append( $("#locationimagelist").children().clone() );
        
		showNewTooltip();
		tooltip.css("z-index", 130);
		
		tooltip.show();
		
		var elementOffset = $("#locationimagepicker").offset();
		
		elementOffset.left -= 14;
		elementOffset.top += 60;
		
		tooltip.offset( elementOffset );
		setTooltipArrowTopLeft();
	}
}

// -----------------------------------------------------------------------------
//  comment tooltip
// -----------------------------------------------------------------------------

function showCommentTooltip( a_SlotElement, a_Sticky )
{
	if ( g_User == null ) 
		return;
		
	$("#tooltip").data("sticky", a_Sticky);
		
	var commentText = a_SlotElement.children(".comment").text();
	
	if ( commentText.length > 0 )
	{		
		var container = $("#info_text");
		var tooltip = $("#tooltip");
		
		var refElement = a_SlotElement.children(".class");
		
		var elementOffset = refElement.offset();
		
		var pageCenterX = $(document).width() / 2;
		var pageCenterY = $(document).height() / 2;
		
		container.empty().append( commentText );
		
		showNewTooltip();
		tooltip.css("z-index", 10);		
		
		elementOffset.left += refElement.width() / 2;
		elementOffset.top  += refElement.height() / 2;
		
		elementOffset.top -= tooltip.height() + refElement.height() / 2 - 5;
		
		if ( elementOffset.left < pageCenterX )
		{
			elementOffset.left -= 46;
			
			tooltip.offset( elementOffset );
			setTooltipArrowBottomLeft();
		}
		else
		{
			elementOffset.left -= tooltip.width() + refElement.width() / 2 - 56;
			
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
	var container = $("#info_text");
	var tooltip = $("#tooltip");
	var elementOffset = a_ParentElement.offset();
	
	var pageCenterX = $(document).width() / 2;
	var pageCenterY = $(document).height() / 2;
	
	var classes = Array( "deathknight", "druid", "hunter", "mage", "paladin", "priest", "rogue", "shaman", "warlock", "warrior" );
	var className = Array( L("Deathknight"), L("Druid"), L("Hunter"), L("Mage"), L("Paladin"), L("Priest"), L("Rogue"), L("Schaman"), L("Warlock"), L("Warrior") );
	
	var HTMLString = "<span>";
	
	for ( i=0; i<classes.length; ++i )
	{
		HTMLString += "<span class=\"class_select\" id=\"cs_" + classes[i] + "\"><img src=\"images/classessmall/" + classes[i] + ".png\"/><br/>" + className[i] + "</span>";
	}
    
    HTMLString +=  "</span>";
		
	container.empty().append( HTMLString );
	
	$(".class_select").click( function() { 
		var className = $(this).attr("id").substr(3);
		
		a_ParentElement.css("background", "url(images/classesbig/" + className + ".png)" );
		
		a_ParentElement.parent().data("charData").charClass = className;
		a_ParentElement.parent().data("charData").role1 = "dmg";
		a_ParentElement.parent().data("charData").role2 = "dmg";
		
		a_ParentElement.parent().children(".role").attr("src", "images/classessmall/dmg.png" );
		
		hideTooltip(); 
	});
		
	showNewTooltip();
	tooltip.css("z-index", 10);
		
	elementOffset.top  += a_ParentElement.height() / 2 - 50;
	elementOffset.left += a_ParentElement.width() / 2 + 16;
	
	tooltip.offset( elementOffset );
	setTooltipArrowLeft();
}

// -----------------------------------------------------------------------------

function showTooltipRoleList( a_ParentElement, a_Role1 )
{	
	$("#tooltip").data("sticky", true);
	
	var container = $("#info_text");
	var tooltip = $("#tooltip");
	var elementOffset = a_ParentElement.offset();
	
	var pageCenterX = $(document).width() / 2;
	var pageCenterY = $(document).height() / 2;
	
	var roles = Array( "tank", "heal", "dmg" );
	
	var roleToName = Array();
	roleToName["tank"] = L("Tank");
	roleToName["heal"] = L("Healer");
	roleToName["dmg"]  = L("Damage");
	
	var classToRole = Array();
		
	classToRole["empty"]	   = Array("dmg");
	classToRole["deathknight"] = Array("tank","dmg");
	classToRole["druid"]       = Array("tank","heal","dmg");
	classToRole["hunter"]      = Array("dmg");
	classToRole["mage"]        = Array("dmg");
	classToRole["paladin"]     = Array("tank","heal","dmg");
	classToRole["priest"] 	   = Array("heal","dmg");
	classToRole["rogue"]       = Array("dmg");
	classToRole["shaman"]      = Array("heal","dmg");
	classToRole["warlock"]     = Array("dmg");
	classToRole["warrior"]     = Array("tank","dmg");
	
	
	var classString = a_ParentElement.parent().parent().data("charData").charClass;
	
	var HTMLString = "<span>";
	
	for ( i=0; i<classToRole[classString].length; ++i )
	{
		HTMLString += "<span class=\"class_select\" id=\"cs_" + classToRole[classString][i] + "\"><img src=\"images/classessmall/" + classToRole[classString][i] + ".png\"/><br/>" + roleToName[ classToRole[classString][i] ] + "</span>";
	}
    
    HTMLString +=  "</span>";
    
	container.empty().append( HTMLString );
	
	$(".class_select").click( function() { 
		var roleName = $(this).attr("id").substr(3);
		
		a_ParentElement.attr("src", "images/classessmall/" + roleName + ".png" );
		
		if ( a_Role1 )
			a_ParentElement.parent().parent().data("charData").role1 = roleName;
		else
			a_ParentElement.parent().parent().data("charData").role2 = roleName;
		
		hideTooltip(); 
	});
		
	showNewTooltip();
	tooltip.css("z-index", 10);
		
	elementOffset.top  += a_ParentElement.height() / 2 - 43;
	elementOffset.left += a_ParentElement.width() / 2;
	
	tooltip.offset( elementOffset );
	setTooltipArrowLeft();
}