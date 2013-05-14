// -----------------------------------------------------------------------------
//    Calendar functions
// -----------------------------------------------------------------------------

function matchesDate( aRaidNode, aCompareDate)
{
    var StartDate = aRaidNode.children("startDate").text();

    var Year  = parseInt(StartDate.substr(0,4), 10);
    var Month = parseInt(StartDate.substr(5,2), 10);
    var Day   = parseInt(StartDate.substr(8,2), 10);

    return ((Year == aCompareDate.getFullYear()) &&
            (Month == aCompareDate.getMonth() + 1) &&
            (Day == aCompareDate.getDate()));
}

// -----------------------------------------------------------------------------

function generateRaidTooltip( aRaidDataXML, aDateIsInThePast, aCalendarView )
{
    var HTMLString = "";

    var StatusValue = aRaidDataXML.children("status").text();
    var Role        = g_RoleIdents[parseInt(aRaidDataXML.children("role").text(), 10)];
    var RaidImg     = aRaidDataXML.children("image").text();
    var StatusText  = "";
    var StatusClass = "status";

    if ( StatusValue == "available" )
    {
        StatusText = L("QueuedAs") + g_RoleNames[Role];
        StatusClass += "wait";
    }
    else if ( StatusValue == "unavailable" )
    {
        if ( aDateIsInThePast )
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
    else if ( aDateIsInThePast )
    {
        StatusText = L("NotSignedUp");
    }

    var AttIdx   = parseInt( aRaidDataXML.children("attendanceIndex").text(), 10 );
    var RaidId   = aRaidDataXML.children("id").text();
    var RaidSize = aRaidDataXML.children("size").text();

    HTMLString += "<span class=\"tooltip\">";

    if ( !aDateIsInThePast )
    {
        HTMLString += "<div class=\"commentbadge\" onClick=\"showTooltipRaidComment( " + RaidId + " )\"></div>";
    }

    HTMLString += "<div class=\"icon\" onclick=\"changeContext('raid,setup," + RaidId + "')\" style=\"background: url('images/raidbig/" + RaidImg + "')\"></div>";
    HTMLString += "<div class=\"textBlock\">";
    HTMLString += "<div class=\"location\">" + aRaidDataXML.children("location").text() + " (" + RaidSize + ")</div>";
    HTMLString += "<div class=\"time\">" + formatTimeString(aRaidDataXML.children("start").text()) + " - " + formatTimeString(aRaidDataXML.children("end").text()) + "</div>";
    HTMLString += "<div class=\"description\">" + aRaidDataXML.children("description").text() + "</div>";
    HTMLString += "</div>"; // info
    HTMLString += "<div class=\"functions\">";

    if ( !aDateIsInThePast && (gUser.characterIds.length > 0) )
    {
        HTMLString += "<select id=\"attend" + RaidId + "\" onchange=\"triggerAttend(this, " + aCalendarView + ")\" style=\"width: 130px\">";

        if (AttIdx === 0)
        {
            HTMLString += "<option value=\"0\" selected>" + L("NotSignedUp") + "</option>";
        }

        for ( i=0; i<gUser.characterIds.length; ++i )
        {
            HTMLString += "<option value=\"" + gUser.characterIds[i] + "\"" + ((AttIdx == gUser.characterIds[i]) ? " selected" : "") + ">" + gUser.characterNames[i] + "</option>";
        }

        HTMLString += "<option value=\"-1\"" + ((AttIdx == -1) ? " selected" : "") + ">" + L("Absent") + "</option>";
        HTMLString += "</select>";
    }

    offsetText = ( aDateIsInThePast ) ? "" : " style=\"margin-left: 10px\"";

    HTMLString += "<span class=\"text" + StatusClass + "\"" + offsetText + ">" + StatusText + "</span>";
    HTMLString += "</div>"; // functions
    HTMLString += "</span>"; // tooltip

    if ( !aDateIsInThePast )
    {
        HTMLString += "<span class=\"comment\">";
        HTMLString += "<div class=\"infobadge\" onclick=\"showTooltipRaidInfoById( " + RaidId + " )\"></div>";
        HTMLString += "<button class=\"submit\" onclick=\"triggerUpdateComment($(this), " + RaidId + ", " + aCalendarView + ")\">" + L("SaveComment") + "</button>";
        HTMLString += "<textarea class=\"text\">" + aRaidDataXML.children("comment").text() + "</textarea>";
        HTMLString += "</span>"; // comment
    }

    return HTMLString;
}

// -----------------------------------------------------------------------------

function generateCalendar( aXMLData )
{
    startFadeTooltip();
    closeSheet();

    var WeekDayArray = Array(L("Sunday"), L("Monday"), L("Tuesday"), L("Wednesday"), L("Thursday"), L("Friday"), L("Saturday"));
    var MonthArray   = Array(L("January"), L("February"), L("March"), L("April"), L("May"), L("June"), L("July"), L("August"), L("September"), L("October"), L("November"), L("December"));

    var HTMLString = "<div id=\"calendarPositioner\"><table id=\"calendar\" cellspacing=\"0\" border=\"0\">";

    var Message = $(aXMLData).children("messagehub");

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
    
    var NeedsIEShadowFix = (/msie/.test(navigator.userAgent.toLowerCase())) &&
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
            
            if ( NeedsIEShadowFix )
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
                if (matchesDate($(this), DayDate)) ++NumRaidsOnDay;
            });

            var NextRaidIdx = RaidIdx + NumRaidsOnDay;
            var ItemPercent = 100 / NumRaidsOnDay;

            while (RaidIdx < NextRaidIdx)
            {
                var RaidNode = $(Raids[RaidIdx]);

                var StatusValue  = RaidNode.children("status").text();
                var RaidId       = RaidNode.children("id").text();
                var RaidSize     = RaidNode.children("size").text();
                var RaidImg      = RaidNode.children("image").text();
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

                HTMLString += "<span id=\"raid" + RaidId + "\" class=\"raid\" style=\"background: url('images/raidsmall/" + RaidImg + "')\" class=\"raidimg\">";
                HTMLString += "<div class=\""+ StatusClass +" shadow_inlay\">" + RaidSize + "</div>";

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

                HTMLString += generateRaidTooltip( RaidNode, DateIsInThePast || RaidIsLocked, true );

                HTMLString += "</span>"; // raid

                RaidIdx += 1;
            }

            HTMLString += "</div>";
            HTMLString += "</td>";

            TimeIter += 86400000; // one day
        }

        HTMLString += "</tr>";
    }

    HTMLString += "</table></div>";

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
        .click( function( aEvent ) { showTooltipRaidInfo( $(this), true, true ); aEvent.stopPropagation(); } )
        .dblclick( function( aEvent ) { changeContext( "raid,setup," + parseInt( $(this).attr("id").substr(4), 10 ) ); aEvent.stopPropagation(); } );

    if ( gUser.isRaidlead )
    {
        $(".calendarDay").dblclick( function() {
            var DayDate = new Date( parseInt( $(this).attr("id"), 10 ) );

            loadNewRaidSheetForDay( DayDate.getDate(), DayDate.getMonth()+1, DayDate.getFullYear() );
        });
        
        $(".calendarDay").each( function() {
            if ( $(this).children("div").children().length == 1 )
            {
                var DayDate = new Date( parseInt( $(this).attr("id"), 10 ) );
            
                onTouch( $(this), function() {
                    loadNewRaidSheetForDay( DayDate.getDate(), DayDate.getMonth()+1, DayDate.getFullYear() );
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

    asyncQuery( "query_newRaidData", Parameters, function( aXMLData ) { showSheetNewRaid( aXMLData, 0, 0, 0 ); } );
}

// -----------------------------------------------------------------------------

function loadNewRaidSheetForDay( aDay, aMonth, aYear )
{
    hideTooltip();

    var Parameters = {
    };

    asyncQuery( "query_newRaidData", Parameters, function( aXMLData ) { showSheetNewRaid( aXMLData, aDay, aMonth, aYear ); } );
}

// -----------------------------------------------------------------------------

function loadCalendar( aMonthBase0, aYear, aOffset )
{
    reloadUser();

    if ( gUser == null )
        return;

    $("#body").empty();

    var StartDate = new Date( aYear, aMonthBase0, 1 );
    StartDate.setMonth( aMonthBase0 + aOffset );

    var Parameters = {
        Month : StartDate.getMonth()+1,
        Year  : StartDate.getFullYear()
    };

    asyncQuery( "query_calendar", Parameters, generateCalendar );
}

// -----------------------------------------------------------------------------

function loadCalendarForToday()
{
    var Today = new Date();
    loadCalendar( Today.getMonth(), Today.getFullYear(), 0 );
}