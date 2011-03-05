// =============================================================================
//  Raid list
// =============================================================================

function displayRaidList( a_XMLData )
{	
	reloadUser();
	
	if ( g_User == null ) 
		return;
		
	var Message = $(a_XMLData).children("messagehub");
	var HTMLString = "<div id=\"raidlist\">";
	
	// Outstanding raids
	
	HTMLString += "<h1>" + L("Upcoming raids") + "</h1>";
	HTMLString += "<div id=\"nextRaids\">";
	
	Message.children("raids").children("raid").each( function() {
	
		var dateStr = $(this).children("startDate").text();
		var year  = dateStr.substr(0,4);
		var month = dateStr.substr(5,2);
		var day   = dateStr.substr(8,2);
	
		$(this).children("location").text();
		
		var tankCount = parseInt( $(this).children("tankCount").text() );
		var healCount = parseInt( $(this).children("healCount").text() );
		var dmgCount  = parseInt( $(this).children("dmgCount").text() );
		
		var overlayClass = "overlayStatusOpen";
		var complete = true;
		var raidIsLocked = false;
				
		tankColor = "#eeee00";
		healColor = "#eeee00";
		dmgColor  = "#eeee00";
		
		if ( tankCount == 0 ) tankColor = "#ee0000";
		if ( healCount == 0 ) healColor = "#ee0000";
		if ( dmgCount == 0 )  dmgColor  = "#ee0000";
		
		if ( tankCount == parseInt($(this).children("tankSlots").text()) ) { tankColor = "#00ee00"; } else { complete = false; }
		if ( healCount == parseInt($(this).children("healSlots").text()) ) { healColor = "#00ee00"; } else { complete = false; }
		if ( dmgCount ==  parseInt($(this).children("dmgSlots").text()) )  { dmgColor  = "#00ee00"; } else { complete = false; }
		
		if ( complete )
		{
			overlayClass = "overlayStatusOk";
		}
		
		if ( $(this).children("stage").text() == "canceled" )
		{
			overlayClass = "overlayStatusCanceled";
			raidIsLocked = true;
		}
		else if ( $(this).children("stage").text() == "locked" )
		{
			overlayClass = "overlayStatusLocked";
			raidIsLocked = true;
		}
		
		HTMLString += "<span class=\"raidSlot\">";
		
		HTMLString += "<span class=\"locationImg\" id=\"raid" + $(this).children("id").text() + "\">";
		HTMLString += "<img src=\"images/raidbig/" + $(this).children("image").text() + "\"/>";
		HTMLString += DisplayRaidTooltip( $(this), raidIsLocked, false );
		HTMLString += "<div class=\"overlayStatus " + overlayClass + "\"></div>";
		HTMLString += "</span>";
		
		HTMLString += "<span class=\"raidInfo\">";
		HTMLString += "<div class=\"location\">" + $(this).children("location").text() + " (" + $(this).children("size").text() + ")" + "</div>";
		HTMLString += day + "." + month + ". " + year + "<br/>";
		HTMLString += $(this).children("start").text() + " - " + $(this).children("end").text() + "<br/>";
		        	
		HTMLString += "</span>";
		
		HTMLString += "<span class=\"setupInfo\">";
		HTMLString += "<div class=\"setupTank\" style=\"color: " + tankColor + "\"><div class=\"count\">" + tankCount + "</div></div>";
		HTMLString += "<div class=\"setupHeal\" style=\"color: " + healColor + "\"><div class=\"count\">" + healCount + "</div></div>";
		HTMLString += "<div class=\"setupDmg\" style=\"color: " + dmgColor + "\"><div class=\"count\">" + dmgCount + "</div></div>";
		HTMLString += "<div class=\"setupBench\" style=\"color: #cccccc\"><div class=\"count\">" + $(this).children("benchCount").text() + "</div></div>";
		HTMLString += "</span>";
				
		HTMLString += "</span>";
	});
	
		
	HTMLString += "</div>";
	
	// Raid history
	
	HTMLString += "<br/><h1>" + L("Raid history") + "</h1>";
	HTMLString += "<div id=\"raidHistory\">";
	
	Message.children("raidList").children("raid").each( function(index) {
		
		var dateStr = $(this).children("startDate").text();
		var year  = dateStr.substr(0,4);
		var month = dateStr.substr(5,2);
		var day   = dateStr.substr(8,2);
		
		HTMLString += "<span class=\"historySlot\" id=\"raid" + $(this).children("id").text() + "\">";
		HTMLString += "<img class=\"icon\" src=\"images/raidsmall/" + $(this).children("image").text() + "\"/>";
		
		if ( $(this).children("stage").text() == "canceled" )
        {
        	HTMLString += "<div class=\"overlayCanceled\"></div>";
        }
		else if ( $(this).children("stage").text() == "locked" )
        {
        	HTMLString += "<div class=\"overlayLocked\"></div>";
        }
		
		HTMLString += "<div class=\"name\">" + $(this).children("location").text() + "<br/><span style=\"font-size: 80%\">" + day + "." + month + ". " + year + "</span></div>";
		HTMLString += "</span>";	
	});
	
	HTMLString += "</div>";
	
	$("#body").empty().append(HTMLString);
	
	$("#body").add("#logo").add("#menu")
    	.click( startFadeTooltip );
	
	$(".locationImg")
    	.mouseover( function() { showTooltipRaidInfo( $(this), false, false ); } )
    	.click( function( event ) { showTooltipRaidInfo( $(this), false, true ); event.stopPropagation(); } )
    	.mouseout( delayedFadeTooltip )
		.dblclick( function( event ) { loadRaid( parseInt( $(this).attr("id").substr(4) ) ); event.stopPropagation(); } );		
		
	$(".historySlot")
    	.dblclick( function( event ) { loadRaid( parseInt( $(this).attr("id").substr(4) ) ); event.stopPropagation(); } )
}

// =============================================================================
//	Raid list display
// =============================================================================

function loadAllRaids()
{
	reloadUser();
	
	if ( g_User == null ) 
		return;
	
	$("#body").empty();
	
    var Parameters = {
        offset : 0,
        count  : 30
    };
    
    AsyncQuery( "raid_list", Parameters, displayRaidList );
}