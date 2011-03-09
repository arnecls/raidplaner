// -----------------------------------------------------------------------------
//	Calendar functions
// -----------------------------------------------------------------------------

function MatchesDate( a_RaidNode, a_CompareDate)
{
    var StartDate = a_RaidNode.children("startDate").text();
    
    var Year  = parseInt(StartDate.substr(0,4), 10);
    var Month = parseInt(StartDate.substr(5,2), 10);
    var Day   = parseInt(StartDate.substr(8,2), 10);
    
    return ((Year == a_CompareDate.getFullYear()) &&
            (Month == a_CompareDate.getMonth() + 1) &&
            (Day == a_CompareDate.getDate()));
}

// -----------------------------------------------------------------------------

function DisplayRaidTooltip( a_RaidDataXML, a_DateIsInThePast, a_CalendarView )
{
	var HTMLString = "";
	
	var RoleName = Array();		
	RoleName["tank"] = "Tank";
	RoleName["heal"] = "Heiler";
	RoleName["dmg"]  = "DD";
	
	var StatusValue = a_RaidDataXML.children("status").text();
    var Role        = a_RaidDataXML.children("role").text();
    var raidImg     = a_RaidDataXML.children("image").text();
    var StatusText  = "";
    var StatusClass = "status";
    
    if ( StatusValue == "available" )
    {
    	StatusText = L("Queued as ") + RoleName[Role];
    	StatusClass += "wait";
    }
    else if ( StatusValue == "unavailable" )
    {
    	if ( a_DateIsInThePast )
    	{
    		StatusText = L("Absent");
    		StatusClass += "absent";
    	}
    }
    else if ( StatusValue == "ok" )
    {
    	StatusText = L("Raiding as ") + RoleName[Role];
    	StatusClass += "ok";
    }
    else if ( a_DateIsInThePast )
    {
    	StatusText = L("Not signed up");
    }
    
    var attIdx   = parseInt( a_RaidDataXML.children("attendanceIndex").text() );
    var raidId   = a_RaidDataXML.children("id").text();
    var raidSize = a_RaidDataXML.children("size").text();
    
    HTMLString += "<span class=\"tooltip\">";
    
    if ( !a_DateIsInThePast && (attIdx != 0) )
    {
    	HTMLString += "<div class=\"commentbadge\" onClick=\"showTooltipEditComment( " + raidId + ", " + a_CalendarView + " )\"></div>";
    }
    
    HTMLString += "<div class=\"icon\" style=\"background: url('images/raidbig/" + raidImg + "')\"></div>";
    HTMLString += "<div style=\"float: left\">";
    HTMLString += "<div class=\"location\">" + a_RaidDataXML.children("location").text() + " (" + raidSize + ")</div>";
    HTMLString += "<div class=\"time\">" + a_RaidDataXML.children("start").text() + " - " + a_RaidDataXML.children("end").text() + "</div>";
    HTMLString += "<div class=\"description\">" + a_RaidDataXML.children("description").text() + "</div>";
    HTMLString += "</div>" // info
    HTMLString += "<div class=\"functions\">"
    
    if ( !a_DateIsInThePast && (g_User.characterIds.length > 0) )
    {
        HTMLString += "<select id=\"attend" + raidId + "\" onchange=\"triggerAttend(this, " + a_CalendarView + ")\" style=\"width: 130px\">";
        
        if (attIdx == 0)
        {
        	HTMLString += "<option value=\"0\" selected>" + L("Not signed up") + "</option>";
        }
        
        for ( i=0; i<g_User.characterIds.length; ++i )
        {
        	HTMLString += "<option value=\"" + g_User.characterIds[i] + "\"" + ((attIdx == g_User.characterIds[i]) ? " selected" : "") + ">" + g_User.characterNames[i] + "</option>";
        }
        
        HTMLString += "<option value=\"-1\"" + ((attIdx == -1) ? " selected" : "") + ">" + L("Absent") + "</option>";
        HTMLString += "</select>";
    }
    
    offsetText = ( a_DateIsInThePast ) ? "" : " style=\"margin-left: 10px\"";
    
    HTMLString += "<span class=\"text" + StatusClass + "\"" + offsetText + ">" + StatusText + "</span>";
    HTMLString += "</div>"; // functions
    HTMLString += "</span>"; // tooltip    
    
    if ( !a_DateIsInThePast && (attIdx != 0) )
    {
        HTMLString += "<span class=\"comment\">";
        HTMLString += "<div class=\"infobadge\" onclick=\"showTooltipRaidInfoForId( " + raidId + ", " + a_CalendarView + " )\"></div>";
        HTMLString += "<button class=\"submit\" onclick=\"triggerUpdateComment($(this), " + raidId + ", " + a_CalendarView + ")\">" + L("Save comment") + "</button>";
        HTMLString += "<textarea class=\"text\">" + a_RaidDataXML.children("comment").text() + "</textarea>";
        HTMLString += "</span>"; // comment
	}
	
	return HTMLString;
}

// -----------------------------------------------------------------------------

function displayCalendar( a_XMLData )
{
	startFadeTooltip();
	closeSheet();
	
	var WeekDayArray = Array(L("Monday"), L("Tuesday"), L("Wedensday"), L("Thursday"), L("Friday"), L("Saturday"), L("Sunday"));
    var MonthArray   = Array(L("January"), L("February"), L("March"), L("April"), L("May"), L("June"), L("July"), L("August"), L("September"), L("October"), L("November"), L("December"));
    
    var HTMLString = "<div id=\"calendarPositioner\"><table id=\"calendar\" cellspacing=\"0\" border=\"0\">";
        
    var Message = $(a_XMLData).children("messagehub");
    
    var StartDay    = parseInt( Message.children("startDay").text() );
    var StartMonth  = parseInt( Message.children("startMonth").text() ) - 1;
    var StartYear   = parseInt( Message.children("startYear").text() );
    
    var TimeIter    = Date.UTC( StartYear, StartMonth, StartDay, 0, 0, 0 );
    var ActiveMonth = parseInt( Message.children("displayMonth").text() );
    var ActiveYear  = parseInt( Message.children("displayYear").text() );
    
    // build weekdays 
    
    HTMLString += "<tr>";
    
    for (WeekDayIdx=0; WeekDayIdx<7; ++WeekDayIdx)
    {
        HTMLString += "<td class=\"weekday\">" + WeekDayArray[WeekDayIdx] + "</td>";
    }
    
    HTMLString += "</tr>";
    
    // build days
        
    Raids = Message.children("raids").children("raid");
    
    var RaidIdx  = 0;
    var NumRaids = Raids.length;
    var Today = new Date(); 
    
    for (WeekIdx=0; WeekIdx<6; ++WeekIdx)
    {
        HTMLString += "<tr class=\"row\">";
        
        for (WeekDayIdx=0; WeekDayIdx<7; ++WeekDayIdx)
        {
            var DayDate  = new Date(TimeIter);
            var DayClass = (DayDate.getMonth() == ActiveMonth) ? "dayOfMonth shadow_gray" : "dayOfOther shadow_white";
            var DateIsInThePast = (DayDate.getFullYear() < Today.getFullYear()) || ((DayDate.getMonth() <= Today.getMonth()) && (DayDate.getDate() < Today.getDate()));
            var FieldClass = "day";
            
            if ( ( DayDate.getMonth() == Today.getMonth() ) && ( DayDate.getDate() == Today.getDate() ) )
            	FieldClass = "today ";
            else if (WeekDayIdx == 0)
            	FieldClass = "dayFirst";
            	
            HTMLString += "<td class=\"" + FieldClass + " calendarDay\" id=\"" + DayDate.getTime() + "\">";
            HTMLString += "<div class=\"" + DayClass + "\">" + DayDate.getDate() + "</div>";
             
            var NumRaidsOnDay = 0;
            
            $(Raids).each( function() { 
                if (MatchesDate($(this), DayDate)) ++NumRaidsOnDay;
            });
            
            var NextRaidIdx = RaidIdx + NumRaidsOnDay;
            var ItemPercent = 100 / NumRaidsOnDay;
            
            while (RaidIdx < NextRaidIdx)
            {
                var RaidNode = $(Raids[RaidIdx]);
                                
                var StatusValue  = RaidNode.children("status").text();
                var raidId       = RaidNode.children("id").text();
                var raidSize     = RaidNode.children("size").text();
                var raidImg      = RaidNode.children("image").text();
                var RaidIsLocked = false;
                
                var StatusClass = "status";
                
                if ( StatusValue == "available" )
                {
                	StatusClass += "wait";
                }
                else if ( StatusValue == "unavailable" )
                {
                	StatusClass += "absent";
                }
                else if ( StatusValue == "ok" )
                {
                	StatusClass += "ok";                
                }                
                
                HTMLString += "<span id=\"raid" + raidId + "\" class=\"raid\" style=\"background: url('images/raidsmall/" + raidImg + "')\" class=\"raidimg\">";
                HTMLString += "<div class=\""+ StatusClass +" shadow_inlay\">" + raidSize + "</div>";
                
                if ( RaidNode.children("stage").text() == "canceled" )
                {
                	HTMLString += "<div class=\"overlayCanceled\"></div>";
                	RaidIsLocked = true;
                }                
                else if ( RaidNode.children("stage").text() == "locked" )
                {
                	HTMLString += "<div class=\"overlayLocked\"></div>";
                	RaidIsLocked = true;
                }
                
                HTMLString += DisplayRaidTooltip( RaidNode, DateIsInThePast || RaidIsLocked, true );
                				                
                HTMLString += "</span>"; // raid
                
                RaidIdx += 1;
            }
            
            HTMLString += "</td>";
            
            TimeIter += 86400000; // one day
        }
        
        HTMLString += "</tr>";
    }
    
    HTMLString += "</table></div>"
    
    HTMLString += "<div id=\"calendarFunctions\">";
    HTMLString += "<button class=\"button button_prev_month\" onclick=\"loadCalendar(" + ActiveMonth + ", " + ActiveYear + ", -1)\"></button>";
    HTMLString += "<button class=\"button button_next_month\" onclick=\"loadCalendar(" + ActiveMonth + ", " + ActiveYear + ", 1)\"></button>";
    
    if ( g_User.isRaidlead )
    	HTMLString += "<button class=\"button button_new_raid\" onclick=\"loadNewRaidSheet()\"></button>";
    
    HTMLString += "<span id=\"month\">" + MonthArray[ActiveMonth] + " " + ActiveYear + "</span>";
    HTMLString += "</div>";
    
    $("#body").empty().append(HTMLString);
    
    $(".button_prev_month").button({ icons: { primary: "ui-icon-carat-1-w" }, text: false });
    $(".button_next_month").button({ icons: { primary: "ui-icon-carat-1-e" }, text: false });
    $(".button_new_raid").button({ icons: { primary: "ui-icon-plus" }, text: false });
    
    $(".button").children()
        .css( "float", "left" )
        .css( "position", "static" )
        .css( "margin-top", "3px" )
        .css( "margin-left", "6px" );
        
    if ( $.browser.msie && ($.browser.version < 8) )
    {
        // there's nothing like brute force ...
        $(".button").children().css( "margin-top", "1px" )
    }
    
    $(".raid")
    	.mouseover( function() { showTooltipRaidInfo( $(this), true, false ); } )
    	.click( function( event ) { showTooltipRaidInfo( $(this), true, true ); event.stopPropagation(); } )
    	.mouseout( delayedFadeTooltip )
    	.dblclick( function( event ) { loadRaid( parseInt( $(this).attr("id").substr(4) ) ); event.stopPropagation(); } );
    
    $("#body").add("#logo").add("#menu")
    	.click( startFadeTooltip );
        
    if ( g_User.isRaidlead )
    {
	    $(".calendarDay").dblclick( function() {
	    	var dayDate = new Date( parseInt( $(this).attr("id") ) );
	    	
	    	loadNewRaidSheetForDay( dayDate.getDate(), dayDate.getMonth()+1, dayDate.getFullYear() );
	    });
	}
    
    $("#tooltip").mouseover( showTooltip );
    $("#tooltip").mouseout( delayedFadeTooltip );
}

// -----------------------------------------------------------------------------
//	Load
// -----------------------------------------------------------------------------

function loadNewRaidSheet()
{
	hideTooltip();
	
	var Parameters = {
	};
	
	AsyncQuery( "query_locations", Parameters, function( a_XMLData ) { showSheetNewRaid( a_XMLData, 0, 0, 0 ); } );
}

// -----------------------------------------------------------------------------

function loadNewRaidSheetForDay( a_Day, a_Month, a_Year )
{
	hideTooltip();
	
	var Parameters = {
	};
	
	AsyncQuery( "query_locations", Parameters, function( a_XMLData ) { showSheetNewRaid( a_XMLData, a_Day, a_Month, a_Year ); } );
}

// -----------------------------------------------------------------------------

function loadCalendar( a_Month, a_Year, a_Offset )
{
	reloadUser();
	
	if ( g_User == null ) 
		return;
		
	$("#body").empty();
	
	var FirstDayInMonth = new Date( a_Year, a_Month, 1 );
	FirstDayInMonth.setMonth( a_Month + a_Offset );
	
   	var FirstWeekDay = FirstDayInMonth.getDay() - 1;
    
    if (FirstWeekDay < 0) FirstWeekDay = 6;
    
    var DayIter = 1-FirstWeekDay;
    
    var StartDate = new Date( FirstDayInMonth.getFullYear(), FirstDayInMonth.getMonth(), DayIter);
    var EndDate   = new Date( FirstDayInMonth.getFullYear(), FirstDayInMonth.getMonth(), DayIter + 42);
    
    var Parameters = { 
        StartDay     : StartDate.getDate(),
        StartMonth   : StartDate.getMonth() + 1,
        StartYear    : StartDate.getFullYear(),
        
        EndDay       : EndDate.getDate(),
        EndMonth     : EndDate.getMonth() + 1,
        EndYear      : EndDate.getFullYear(),
        
        DisplayMonth : FirstDayInMonth.getMonth(),
        DisplayYear  : FirstDayInMonth.getFullYear()
    };
    
    AsyncQuery( "raid_calendar", Parameters, displayCalendar );
}

// -----------------------------------------------------------------------------

function loadCalendarForToday()
{
	var Today = new Date();
	loadCalendar( Today.getMonth(), Today.getFullYear(), 0 );
}