// -----------------------------------------------------------------------------
//    Calendar functions
// -----------------------------------------------------------------------------

function matchesDate( aRaid, aCompareDate)
{
    var raidDate = getDateFromUTCString(aRaid.startDate, aRaid.start)

    return ((raidDate.getFullYear() == aCompareDate.getFullYear()) &&
            (raidDate.getMonth() == aCompareDate.getMonth()) &&
            (raidDate.getDate() == aCompareDate.getDate()));
}

// -----------------------------------------------------------------------------

function generateRaidTooltip( aRaid, aDateIsInThePast, aCalendarView )
{
    var HTMLString = "";

    var StatusValue = aRaid.status;
    var RoleId      = aRaid.role;
    var RaidImg     = aRaid.image;
    var StatusText  = "";
    var StatusClass = "status";
    var RoleIcon    = "";

    if ( StatusValue == "available" )
    {
        StatusText = L("Benched");
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
        StatusText = L("RaidingAs") + " " + L(gGame.Roles[RoleId].name);
        StatusClass += "ok";
        RoleIcon = gGame.Roles[RoleId].style;
    }
    else if ( aDateIsInThePast )
    {
        StatusText = L("NotSignedUp");
    }

    var AttIdx   = aRaid.attendanceIndex;
    var RaidId   = aRaid.id;
    var RaidSize = aRaid.size;

    HTMLString += "<span class=\"tooltip\">";

    if ( !aDateIsInThePast &&
         (gUser.characterIds.length > 0) )
    {
        HTMLString += "<div class=\"commentbadge clickable\"></div>";
    }
    
    var StartTime = aRaid.start;
    var StartDate = aRaid.startDate;
    var EndTime   = aRaid.end;
    var EndDate   = aRaid.endDate;
    var RaidName  = aRaid.location;

    var NameFontSize = 12;

    if (RaidName.length < 30)
        NameFontSize = 16;
    else if (RaidName.length < 45)
        NameFontSize = 14;

    HTMLString += "<div class=\"icon clickable\" style=\"background: url('themes/icons/"+gSite.Iconset+"/raidbig/" + RaidImg + "')\">";
    
    if (aCalendarView)
    {
        var TopWanted = new Array();
        
        $.each(gGame.Roles, function(aRoleId, aRole)
        {
            var Required = aRaid.slotMax[aRoleId];
            var Occupied = aRaid.slotCount[aRoleId];
    
            if ( Occupied < Required )
            {
                var WantedCount = Required-Occupied;
                var Percent = parseInt((WantedCount / Required) * 100);
                
                TopWanted.push({order: Percent, count: WantedCount, style: aRole.style});
            }
        });
        
        if (TopWanted.length == 0)
        {
            HTMLString += "<div class=\"wantedbadge\"><span class=\"wrap ok\"></span><span class=\"count role_ok ok\"></span></div>";
                
        }
        else
        {        
            TopWanted.sort(function(aLhs, aRhs) {
                return aRhs.order - aLhs.order;
            });
            
            for (var i=0; i<2 && i<TopWanted.length; ++i)
            {
                HTMLString += "<div class=\"wantedbadge\" style=\"top: -"+(i*3)+"px; z-index: "+(5-i)+"\"><span class=\"wrap lfm\"></span><span class=\"count "+TopWanted[i].style+" lfm\">+"+TopWanted[i].count+"</span></div>";
            }
        }
    }
    
    HTMLString += "</div>";
    HTMLString += "<div class=\"textBlock\">";
    HTMLString += "<div class=\"location\" style=\"font-size: "+NameFontSize+"px\">" + RaidName + " (" + RaidSize + ")</div>";
    HTMLString += "<div class=\"time\">" + formatTimeStringUTC(StartDate,StartTime) + " - " + formatTimeStringUTC(EndDate,EndTime);
    HTMLString += " " + formatDateOffsetUTC(StartDate, StartTime) + "</div>";
    HTMLString += "<div class=\"attends\">" + aRaid.attended + " / " + RaidSize + " " + L("Players") + "</div>";
    HTMLString += "<div class=\"description\">" + aRaid.description + "</div>";
    HTMLString += "</div>"; // info
    HTMLString += "<div class=\"functions\">";
    
    var HasCharacters = false;
    for (var i=0; !HasCharacters && (i < gUser.characterIds.length); ++i)
    {
        if (gUser.characterGames[i] == gGame.GameId)
            HasCharacters = true;
    }

    if ( !aDateIsInThePast && HasCharacters )
    {
        HTMLString += "<select class=\"attend\" style=\"width: 130px\">";

        if (AttIdx === 0)
        {
            HTMLString += "<option value=\"0\" selected>" + L("NotSignedUp") + "</option>";
        }

        for ( var i=0; i<gUser.characterIds.length; ++i )
        {
            if (gUser.characterGames[i] == gGame.GameId)
                HTMLString += "<option value=\"" + gUser.characterIds[i] + "\"" + ((AttIdx == gUser.characterIds[i]) ? " selected" : "") + ">" + gUser.characterNames[i] + "</option>";
        }

        HTMLString += "<option value=\"-1\"" + ((AttIdx == -1) ? " selected" : "") + ">" + L("Absent") + "</option>";
        HTMLString += "</select>";
    }

    offsetText = ( aDateIsInThePast ) ? "" : " style=\"margin-left: 10px\"";

    if (StatusText != "")
    {
        HTMLString += "<span class=\"attendstatus leftgradient_" + RoleIcon + " " + StatusClass + "\"" + offsetText + ">";
        HTMLString += "<span class=\"attendtext\">" + StatusText + "</span>";
        
        if (RoleIcon != "")
            HTMLString += "<img class=\"attendedrole\" src=\"lib/layout/images/icon_"+RoleIcon+".png\"/>";
            
        HTMLString += "</span>";
    }
    
    
    HTMLString += "</div>"; // functions
    HTMLString += "</span>"; // tooltip

    return HTMLString;
}

// -----------------------------------------------------------------------------

function generateRaidCommentTooltip( aRaid )
{
    var HTMLString = "";
    
    HTMLString += "<span class=\"comment\">";
    HTMLString += "<div class=\"infobadge clickable\"></div>";
    HTMLString += "<button class=\"submit\">" + L("SaveComment") + "</button>";
    HTMLString += "<textarea class=\"text\">" + aRaid.comment.replace(/<\/br>/g,"\n") + "</textarea>";
    HTMLString += "</span>"; // comment
    
    return HTMLString;
}

// -----------------------------------------------------------------------------

function generateCalendar(aXHR)
{
    startFadeTooltip();
    closeSheet();

    var WeekDayArray = Array(L("Sunday"), L("Monday"), L("Tuesday"), L("Wednesday"), L("Thursday"), L("Friday"), L("Saturday"));
    var MonthArray   = Array(L("January"), L("February"), L("March"), L("April"), L("May"), L("June"), L("July"), L("August"), L("September"), L("October"), L("November"), L("December"));

    var HTMLString = "<div id=\"calendarPositioner\"><table id=\"calendar\" cellspacing=\"0\" border=\"0\">";

    var StartDay    = parseInt( aXHR.startDay, 10 );
    var StartMonth  = parseInt( aXHR.startMonth, 10 ) - 1;
    var StartYear   = parseInt( aXHR.startYear, 10 );
    var StartOfWeek = parseInt( aXHR.startOfWeek, 10 );

    var TimeIter    = new Date( StartYear, StartMonth, StartDay );
    var ActiveMonth = parseInt( aXHR.displayMonth, 10 ) - 1;
    var ActiveYear  = parseInt( aXHR.displayYear, 10 );

    var VacationStart = (gUser.settings.VacationStart == undefined) ? 0 : gUser.settings.VacationStart.number * 1000;
    var VacationEnd   = (gUser.settings.VacationEnd == undefined) ? 0 : gUser.settings.VacationEnd.number * 1000;

    // build weekdays

    HTMLString += "<tr>";

    for (var WeekDayIdx=0; WeekDayIdx<7; ++WeekDayIdx)
    {
        HTMLString += "<td class=\"weekday\">" + WeekDayArray[(WeekDayIdx+StartOfWeek)%7] + "</td>";
    }

    HTMLString += "</tr>";

    // build days

    var RaidIdx  = 0;
    var NumRaids = (aXHR.raid == null) ? 0 : aXHR.raid.length;
    var Now = new Date();
    var Today = new Date(Now.getFullYear(), Now.getMonth(), Now.getDate()); // Normalize
    
    var CountRaids = function(index, value) {
        if (matchesDate(value, DayDate)) ++NumRaidsOnDay;
    };

    for (var WeekIdx=0; WeekIdx<6; ++WeekIdx)
    {
        HTMLString += "<tr class=\"row\">";

        for (var ColIdx=0; ColIdx<7; ++ColIdx)
        {
            var DateIsInThePast = TimeIter < Today;
			var ShadowClass = (TimeIter.getMonth() == ActiveMonth) ? "shadow_gray" : "shadow_white";
            var DayClass = (TimeIter.getMonth() == ActiveMonth) ? "dayOfMonth" : "dayOfOther";
            var FieldClass = "day";
            var VacationClass = "vacation";

            if ( ( TimeIter.getMonth() == Today.getMonth() ) && ( TimeIter.getDate() == Today.getDate() ) )
            {
                FieldClass = "today ";
                VacationClass = "vacation_today"
            }
            else if (ColIdx === 0)
            {
                FieldClass = "dayFirst";
            }
            
            if ((TimeIter.getTime() >= VacationStart) && (TimeIter.getTime() <= VacationEnd))
                HTMLString += "<td class=\"" + FieldClass + " calendarDay " + VacationClass + "\" id=\"" + TimeIter.getTime() + "\">";
            else
                HTMLString += "<td class=\"" + FieldClass + " calendarDay\" id=\"" + TimeIter.getTime() + "\">";
            
            HTMLString += "<div class=\"day_overflow\">";
            HTMLString += "<div class=\"" + DayClass + " " + ShadowClass + "\">" + TimeIter.getDate() + "</div>";
            
            // Print the raids on this day

            while ( (RaidIdx < aXHR.raid.length) &&
                    matchesDate(aXHR.raid[RaidIdx], TimeIter))
            {
                var CurrentRaid = aXHR.raid[RaidIdx];

                var StatusValue  = CurrentRaid.status;
                var RaidId       = CurrentRaid.id;
                var RaidSize     = CurrentRaid.size;
                var RaidImg      = CurrentRaid.image;
                var RaidIsLocked = CurrentRaid.stage == "locked";

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

                HTMLString += "<span id=\"raid" + RaidId + "\" index=\""+RaidIdx+"\" locked=\""+(DateIsInThePast || RaidIsLocked)+"\" class=\"raid clickable\" style=\"background: url('themes/icons/"+gSite.Iconset+"/raidsmall/" + RaidImg + "')\" class=\"raidimg\">";
                HTMLString += "<div class=\""+ StatusClass +" shadow_inlay\">" + RaidSize + "</div>";

                if ( CurrentRaid.stage == "canceled" )
                {
                    HTMLString += "<div class=\"overlayCanceled\"></div>";
                    RaidIsLocked = true;
                }
                else if ( RaidIsLocked )
                {
                    HTMLString += "<div class=\"overlayLocked\"></div>";
                    RaidIsLocked = true;
                }

                HTMLString += "</span>"; // raid

                ++RaidIdx;
            }

            HTMLString += "</div>";
            HTMLString += "</td>";

            TimeIter.setDate(TimeIter.getDate() + 1);
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

    $(".raid").each(function() {
        var RaidIdx = parseInt($(this).attr("index"), 10);
        var RaidIsLocked = $(this).attr("locked") == "true";       
        var RaidData = aXHR.raid[RaidIdx];
        
        $(this).mouseover( function() { 
            showTooltipRaidInfo( $(this), RaidData, RaidIsLocked, true, false ); 
        });
        
        $(this).click( function( aEvent ) { 
            showTooltipRaidInfo( $(this), RaidData, RaidIsLocked, true, true ); 
            aEvent.stopPropagation(); 
        });
        
        $(this).dblclick( function( aEvent ) { 
            changeContext( "raid,setup," + RaidData.id ); 
            aEvent.stopPropagation(); 
        }); 
    });        

    if ( gUser.isRaidlead )
    {
        $(".calendarDay").dblclick( function(aEvent) {
            var DayDate = new Date( parseInt( $(this).attr("id"), 10 ) );
            loadNewRaidSheetForDay( DayDate.getDate(), DayDate.getMonth()+1, DayDate.getFullYear() );
            aEvent.stopPropagation();
        });

        $(".calendarDay").each( function() {
            if ( $("div > *", this).length == 1 )
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

    asyncQuery( "query_newRaidData", Parameters, function(aXHR) { showSheetNewRaid(aXHR, 0, 0, 0); } );
}

// -----------------------------------------------------------------------------

function loadNewRaidSheetForDay( aDay, aMonth, aYear )
{
    hideTooltip();

    var Parameters = {
    };

    asyncQuery( "query_newRaidData", Parameters, function(aXHR) { showSheetNewRaid(aXHR, aDay, aMonth, aYear); } );
}

// -----------------------------------------------------------------------------

function loadCalendar( aMonthBase0, aYear, aOffset )
{
    reloadUser();

    if ( gUser == null )
        return;

    loadCalendarUnchecked( aMonthBase0, aYear, aOffset );
}

// -----------------------------------------------------------------------------

function loadCalendarUnchecked( aMonthBase0, aYear, aOffset )
{
    $("#body").empty();

    var ShowYear  = aYear;
    var ShowMonth = aMonthBase0 + aOffset;

    while (ShowMonth < 0)
    {
        --ShowYear;
        ShowMonth += 12;

    }

    while (ShowMonth > 11)
    {
        ++ShowYear;
        ShowMonth -= 12;

    }

    var Parameters = {

        Month      : ShowMonth+1,
        Year       : ShowYear
    };

    asyncQuery( "query_calendar", Parameters, generateCalendar );
}

// -----------------------------------------------------------------------------

function loadDefaultCalendar()
{
    reloadUser();

    if ( gUser == null )
        return;

    if ( gUser.calendar != null )
    {
        loadCalendarUnchecked(gUser.calendar.month-1, gUser.calendar.year, 0);
    }
    else
    {
        var Today = new Date();
        loadCalendarUnchecked( Today.getUTCMonth(), Today.getUTCFullYear(), 0 );
    }
}
