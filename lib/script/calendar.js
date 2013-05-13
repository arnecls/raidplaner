// -----------------------------------------------------------------------------
//    Calendar functions
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

    var StatusValue = a_RaidDataXML.children("status").text();
    var Role        = g_RoleIdents[parseInt(a_RaidDataXML.children("role").text(), 10)];
    var raidImg     = a_RaidDataXML.children("image").text();
    var StatusText  = "";
    var StatusClass = "status";

    if ( StatusValue == "available" )
    {
        StatusText = L("QueuedAs") + g_RoleNames[Role];
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
        StatusText = L("Raiding") + g_RoleNames[Role];
        StatusClass += "ok";
    }
    else if ( a_DateIsInThePast )
    {
        StatusText = L("NotSignedUp");
    }

    var attIdx   = parseInt( a_RaidDataXML.children("attendanceIndex").text(), 10 );
    var raidId   = a_RaidDataXML.children("id").text();
    var raidSize = a_RaidDataXML.children("size").text();

    HTMLString += "<span class=\"tooltip\">";

    if ( !a_DateIsInThePast )
    {
        HTMLString += "<div class=\"commentbadge\" onClick=\"displayRaidTooltipComment( " + raidId + " )\"></div>";
    }

    HTMLString += "<div class=\"icon\" onclick=\"changeContext('raid,setup," + raidId + "')\" style=\"background: url('images/raidbig/" + raidImg + "')\"></div>";
    HTMLString += "<div class=\"textBlock\">";
    HTMLString += "<div class=\"location\">" + a_RaidDataXML.children("location").text() + " (" + raidSize + ")</div>";
    HTMLString += "<div class=\"time\">" + formatTimeString(a_RaidDataXML.children("start").text()) + " - " + formatTimeString(a_RaidDataXML.children("end").text()) + "</div>";
    HTMLString += "<div class=\"description\">" + a_RaidDataXML.children("description").text() + "</div>";
    HTMLString += "</div>" // info
    HTMLString += "<div class=\"functions\">"

    if ( !a_DateIsInThePast && (gUser.characterIds.length > 0) )
    {
        HTMLString += "<select id=\"attend" + raidId + "\" onchange=\"triggerAttend(this, " + a_CalendarView + ")\" style=\"width: 130px\">";

        if (attIdx === 0)
        {
            HTMLString += "<option value=\"0\" selected>" + L("NotSignedUp") + "</option>";
        }

        for ( i=0; i<gUser.characterIds.length; ++i )
        {
            HTMLString += "<option value=\"" + gUser.characterIds[i] + "\"" + ((attIdx == gUser.characterIds[i]) ? " selected" : "") + ">" + gUser.characterNames[i] + "</option>";
        }

        HTMLString += "<option value=\"-1\"" + ((attIdx == -1) ? " selected" : "") + ">" + L("Absent") + "</option>";
        HTMLString += "</select>";
    }

    offsetText = ( a_DateIsInThePast ) ? "" : " style=\"margin-left: 10px\"";

    HTMLString += "<span class=\"text" + StatusClass + "\"" + offsetText + ">" + StatusText + "</span>";
    HTMLString += "</div>"; // functions
    HTMLString += "</span>"; // tooltip

    if ( !a_DateIsInThePast )
    {
        HTMLString += "<span class=\"comment\">";
        HTMLString += "<div class=\"infobadge\" onclick=\"displayRaidTooltipInfo( " + raidId + " )\"></div>";
        HTMLString += "<button class=\"submit\" onclick=\"triggerUpdateComment($(this), " + raidId + ", " + a_CalendarView + ")\">" + L("SaveComment") + "</button>";
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

    var WeekDayArray = Array(L("Sunday"), L("Monday"), L("Tuesday"), L("Wednesday"), L("Thursday"), L("Friday"), L("Saturday"));
    var MonthArray   = Array(L("January"), L("February"), L("March"), L("April"), L("May"), L("June"), L("July"), L("August"), L("September"), L("October"), L("November"), L("December"));

    var HTMLString = "<div id=\"calendarPositioner\"><table id=\"calendar\" cellspacing=\"0\" border=\"0\">";

    var Message = $(a_XMLData).children("messagehub");

    var StartDay    = parseInt( Message.children("startDay").text(), 10 );
    var StartMonth  = parseInt( Message.children("startMonth").text(), 10 ) - 1;
    var StartYear   = parseInt( Message.children("startYear").text(), 10 );
    var StartOfWeek = parseInt( Message.children("startOfWeek").text(), 10 );

    var TimeIter    = Date.UTC( StartYear, StartMonth, StartDay, 0, 0, 0 );
    var ActiveMonth = parseInt( Message.children("displayMonth").text(), 10 ) - 1;
    var ActiveYear  = parseInt( Message.children("displayYear").text(), 10 );

    // build weekdays

    HTMLString += "<tr>";

    for (WeekDayIdx=0; WeekDayIdx<7; ++WeekDayIdx)
    {
        HTMLString += "<td class=\"weekday\">" + WeekDayArray[(WeekDayIdx+StartOfWeek)%7] + "</td>";
    }

    HTMLString += "</tr>";

    // build days

    Raids = Message.children("raids").children("raid");

    var RaidIdx  = 0;
    var NumRaids = Raids.length;
    var Today = new Date();
    
    var needsIEShadowFix = (/msie/.test(navigator.userAgent.toLowerCase())) &&
                           (parseInt(navigator.appVersion.match(/MSIE ([0-9]+)/)[1],10) < 10);

    for (WeekIdx=0; WeekIdx<6; ++WeekIdx)
    {
        HTMLString += "<tr class=\"row\">";

        for (ColIdx=0; ColIdx<7; ++ColIdx)
        {
            var DayDate  = new Date(TimeIter);
            
            var ShadowClass = (DayDate.getMonth() == ActiveMonth) ? "shadow_gray" : "shadow_white";
            var DayClass = (DayDate.getMonth() == ActiveMonth) ? "dayOfMonth" : "dayOfOther";
            
            var DateIsInThePast = (DayDate.getFullYear() < Today.getFullYear()) || ((DayDate.getMonth() <= Today.getMonth()) && (DayDate.getDate() < Today.getDate()));
            var FieldClass = "day";

            if ( ( DayDate.getMonth() == Today.getMonth() ) && ( DayDate.getDate() == Today.getDate() ) )
                FieldClass = "today ";
            else if (ColIdx === 0)
                FieldClass = "dayFirst";

            HTMLString += "<td class=\"" + FieldClass + " calendarDay\" id=\"" + DayDate.getTime() + "\">";
            HTMLString += "<div class=\"day_overflow\">";
            
            if ( needsIEShadowFix )
            {
                HTMLString += "<div class=\"" + ShadowClass + "\">" + DayDate.getDate() + "</div>";
                HTMLString += "<div class=\"" + DayClass + " " + ShadowClass + "_iefix\">" + DayDate.getDate() + "</div>";
            }
            else
            {
                HTMLString += "<div class=\"" + DayClass + " " + ShadowClass + "\">" + DayDate.getDate() + "</div>";
            }            
            
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

            HTMLString += "</div>";
            HTMLString += "</td>";

            TimeIter += 86400000; // one day
        }

        HTMLString += "</tr>";
    }

    HTMLString += "</table></div>"

    HTMLString += "<div id=\"calendarFunctions\">";
    HTMLString += "<button class=\"button button_prev_month\" onclick=\"loadCalendar(" + ActiveMonth + ", " + ActiveYear + ", -1)\"></button>";
    HTMLString += "<button class=\"button button_next_month\" onclick=\"loadCalendar(" + ActiveMonth + ", " + ActiveYear + ", 1)\"></button>";

    if ( gUser.isRaidlead )
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

    $(".button").css( "margin-right", "4px" );

    $(".raid")
        .mouseover( function() { showTooltipRaidInfo( $(this), true, false ); } )
        .click( function( event ) { showTooltipRaidInfo( $(this), true, true ); event.stopPropagation(); } )
        .dblclick( function( event ) { changeContext( "raid,setup," + parseInt( $(this).attr("id").substr(4) ) ); event.stopPropagation(); } );

    if ( gUser.isRaidlead )
    {
        $(".calendarDay").dblclick( function() {
            var dayDate = new Date( parseInt( $(this).attr("id") ) );

            loadNewRaidSheetForDay( dayDate.getDate(), dayDate.getMonth()+1, dayDate.getFullYear() );
        });
        
        $(".calendarDay").each( function() {
            if ( $(this).children("div").children().length == 1 )
            {
                var dayDate = new Date( parseInt( $(this).attr("id") ) );
            
                onTouch( $(this), function() {
                    loadNewRaidSheetForDay( dayDate.getDate(), dayDate.getMonth()+1, dayDate.getFullYear() );
                });
            }
        });
    }
}

// -----------------------------------------------------------------------------
//    Load
// -----------------------------------------------------------------------------

function loadNewRaidSheet()
{
    hideTooltip();

    var Parameters = {
    };

    AsyncQuery( "query_newRaidData", Parameters, function( a_XMLData ) { showSheetNewRaid( a_XMLData, 0, 0, 0 ); } );
}

// -----------------------------------------------------------------------------

function loadNewRaidSheetForDay( a_Day, a_Month, a_Year )
{
    hideTooltip();

    var Parameters = {
    };

    AsyncQuery( "query_newRaidData", Parameters, function( a_XMLData ) { showSheetNewRaid( a_XMLData, a_Day, a_Month, a_Year ); } );
}

// -----------------------------------------------------------------------------

function loadCalendar( a_MonthBase0, a_Year, a_Offset )
{
    reloadUser();

    if ( gUser == null )
        return;

    $("#body").empty();

    var startDate = new Date( a_Year, a_MonthBase0, 1 );
    startDate.setMonth( a_MonthBase0 + a_Offset );

    var Parameters = {
        Month : startDate.getMonth()+1,
        Year  : startDate.getFullYear()
    };

    AsyncQuery( "query_calendar", Parameters, displayCalendar );
}

// -----------------------------------------------------------------------------

function loadCalendarForToday()
{
    var Today = new Date();
    loadCalendar( Today.getMonth(), Today.getFullYear(), 0 );
}