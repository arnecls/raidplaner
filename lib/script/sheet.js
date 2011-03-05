// -----------------------------------------------------------------------------
//	Generic sheet functions
// -----------------------------------------------------------------------------

function closeSheet()
{
    hideTooltip();
	if ($("#sheetoverlay").is(":visible"))
	{
		$("#sheetoverlay").fadeOut(100, function() { 
			$(this).hide();
			$("#eventblocker").css("visibility", "hidden");
			$("#sheet_body").empty();            
		});
	}
}


// -----------------------------------------------------------------------------
//	NewRaid sheet
// -----------------------------------------------------------------------------

function onLocationChange( a_SelectObj )
{
	if ( a_SelectObj.selectedIndex == 0)
	{
		$(a_SelectObj).combobox( "editable", true );
			
		$("#locationimagepicker").click( showTooltipRaidImageList );
		$("#locationimagepicker").css( "background-image", "url(images/raidbig/unknown.png)" );
	}
	else
	{
		$(a_SelectObj).combobox( "editable", false );
		
		$("#locationimagepicker").unbind("click");
		
		var ImageName = $("#locationimagepicker").data("imageNames")[a_SelectObj.selectedIndex - 1]; 
		
		$("#locationimagepicker").css( "background-image", "url(images/raidbig/"+ ImageName + ")" );
	}
}

// -----------------------------------------------------------------------------

function showSheetNewRaid( a_XMLData, a_Day, a_Month, a_Year )
{
	if ( g_User == null ) 
		return;
	
	var container = $("#sheet_body");
	var sheet = $("#sheetoverlay");
	var Message = $(a_XMLData).children("messagehub");
	var Locations = Message.children("location"); 
	var LocationImages = Message.children("locationimage"); 
	
	container.empty().append( $("#newRaid").clone() );
	
	$("#descriptiondummy").focus( function() { 
			$("#descriptiondummy").hide();
			$("#description").show().focus(); 
	});
	
	$("#description").blur( function() { 
		if ( $("#description").val() == "" ) {
			$("#description").hide();
			$("#descriptiondummy").show(); 
		}
	});
	
	if ( a_Day > 0 )
	{
		$("#raiddatepicker").datepicker( {
			dayNamesShort: ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa'],
			monthNames: ['Januar','Februar','M&auml;rtz','April','Mai','Juni','Juli','August','September','Oktober','November','Dezember'],
			firstDay: 1,
			inline: true,
			defaultDate: a_Month + "/" + a_Day + "/" + a_Year		
		});
	}
	else
	{
		$("#raiddatepicker").datepicker( {
			dayNamesShort: ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa'],
			monthNames: ['Januar','Februar','M&auml;rtz','April','Mai','Juni','Juli','August','September','Oktober','November','Dezember'],
			firstDay: 1,
			inline: true			
		});
	}
		
	sheet.css( "margin-left", -sheet.width() / 2 );
	sheet.css( "margin-top", -sheet.height() / 2 );
	
	var imageList = new Array();
	
	Locations.each( function(index) {
		imageList[index] = $(this).children("image").text();
		$("#selectlocation").append("<option value=\"" + $(this).children("id").text() + "\">" + $(this).children("name").text() + "</option>");
	});
	
	$("#locationimagepicker").data("imageNames", imageList );
    
    var HTMLString = "<span><div>";
    var numImages = 0;
		
	LocationImages.each( function(index) {
		if ( ( numImages + 1 ) % 11 == 0 )
			HTMLString += "</div><div>";
			
		HTMLString += "<img src=\"images/raidsmall/" + $(this).text() + "\" onclick=\"applyLocationImage(this)\" style=\"width: 32px; height: 32px; margin-right: 5px;\"/>";
        ++numImages;
	});
    
    HTMLString += "</div></span>";
    $("#locationimagelist").append( HTMLString );
	
	$("#locationimagepicker")
		.data( "selectedImage", "unknown.png" )
		.click( showTooltipRaidImageList )
    	.mouseout( delayedFadeTooltip );
    	
    $("#newRaidSubmit").click( triggerNewRaid );
    
    $("#eventblocker").css("visibility", "visible");
	sheet.show();
	
	$("#selectlocation").combobox();
	$("#selectlocation").combobox( "editable", true );
	$("#selectsize").combobox();
	$("#starthour").combobox();
	$("#startminute").combobox();
	$("#endhour").combobox();
	$("#endminute").combobox();
	$("#stage").combobox();
	$("#newRaidSubmit").button({ icons: { secondary: "ui-icon-disk" }}).height(24).css("font-size", 11);
}

// -----------------------------------------------------------------------------

function triggerNewRaid()
{
	var raidImage = $("#locationimagepicker").data( "selectedImage" );
	var date = new Date( $("#raiddatepicker").datepicker( "getDate" ) );
	
	var parameters = {
		raidImage	 : raidImage,
		locationId	 : $("#selectlocation").val(),
		locationSize : $("#selectsize").val(),
		locationName : $("#edit_selectlocation").val(),
		startHour	 : $("#starthour").val(),
		startMinute  : $("#startminute").val(),
		endHour		 : $("#endhour").val(),
		endMinute	 : $("#endminute").val(),
		description	 : $("#description").val(),
		month		 : date.getMonth()+1,
		day			 : date.getDate(),
		year		 : date.getFullYear()
	};
	
	AsyncQuery( "raid_create", parameters, displayCalendar );
}