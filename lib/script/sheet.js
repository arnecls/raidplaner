// -----------------------------------------------------------------------------
//    Generic sheet functions
// -----------------------------------------------------------------------------

function closeSheet()
{
    hideTooltip();
    if ($("#sheetoverlay").is(":visible"))
    {
        $("#sheetoverlay").fadeOut(100, function() {
            $(this).hide();
            $("#eventblocker").hide();
            $("#sheet_body").empty();
        });
    }
}

// -----------------------------------------------------------------------------
//    NewRaid sheet
// -----------------------------------------------------------------------------

function showSheetNewRaid( aXHR, aDay, aMonth, aYear )
{
    if ( gUser == null )
        return;

    var sheet = $("#sheetoverlay");
    var container = $("#sheet_body");
    var HTMLString = "";

    // Left

    if (gUser.isRaidlead) {
        HTMLString += "<div style=\"text-align: center; position: relative; top: -10px; padding-bottom:10px; border-bottom: 1px solid #333\" id=\"typeToogle\">";
        HTMLString += "<input type=\"radio\" name=\"typeToggle\" id=\"toggle_raid\" value=\"raid\" checked=\"checked\"><label for=\"toggle_raid\">"+L("Raid")+"</label>";
        HTMLString += "<input type=\"radio\" name=\"typeToggle\" id=\"toggle_event\" value=\"event\"><label for=\"toggle_event\">"+L("Event")+"</label>";
        HTMLString += "</div>";
    }

    HTMLString += "<span style=\"display: inline-block; vertical-align: top; margin-right: 20px\">";
    HTMLString += "<div id=\"raiddatepicker\"></div>";
    HTMLString += "</span>";

    // Right

    HTMLString += "<span style=\"display: inline-block; vertical-align: top\">";
    HTMLString += "<span style=\"display: inline-block; margin-right: 5px; float: left\" class=\"imagepicker clickable\" id=\"locationimagepicker\"><div class=\"imagelist\" id=\"locationimagelist\"></div></span>";
    HTMLString += "<span style=\"display: inline-block; vertical-align: top; margin-top: 6px\">";

    HTMLString += "<div style=\"margin-bottom: 10px\">";
    HTMLString += "<select id=\"selectlocation\" onchange=\"onLocationChange(this)\">";
    HTMLString += "<option value=\"0\">"+L("NewDungeon")+"</option>";
    HTMLString += "</select>";

    HTMLString += "<span style=\"display: inline-block; width: 3px;\"></span>";
    HTMLString += "<select id=\"selectsize\" style=\"width: 48px\">";

    $.each(gGame.Groups, function(aSize, aGroup)
    {
        HTMLString += "<option value=\""+aSize+"\">"+aSize+"</option>";
    });

    HTMLString += "</select>";

    HTMLString += "</div>";

    HTMLString += "<div>";
    HTMLString += "<select id=\"starthour\">";
    HTMLString += "</select>";

    HTMLString += "<span class=\"filltext\" style=\"width: 10px\">:</span>";
    HTMLString += "<select id=\"startminute\" style=\"width: 48px\">";
    HTMLString += "<option value=\"0\">00</option>";
    HTMLString += "<option value=\"15\">15</option>";
    HTMLString += "<option value=\"30\">30</option>";
    HTMLString += "<option value=\"45\">45</option>";
    HTMLString += "</select>";

    HTMLString += "<span class=\"filltext\" style=\"width: 20px\">"+L("to")+"</span>";
    HTMLString += "<select id=\"endhour\">";
    HTMLString += "</select>";

    HTMLString += "<span class=\"filltext\" style=\"width: 10px\">:</span>";
    HTMLString += "<select id=\"endminute\" style=\"width: 48px\">";
    HTMLString += "<option value=\"0\">00</option>";
    HTMLString += "<option value=\"15\">15</option>";
    HTMLString += "<option value=\"30\">30</option>";
    HTMLString += "<option value=\"45\">45</option>";
    HTMLString += "</select>";

    HTMLString += "</div>";
    HTMLString += "</span>";

    HTMLString += "<div style=\"margin-top: 16px; clear: left\">";
    HTMLString += "<textarea id=\"descriptiondummy\" class=\"textdummy description\">"+L("Description")+"</textarea>";
    HTMLString += "<textarea id=\"description\" class=\"textinput description\" style=\"display:none\"></textarea>";
    HTMLString += "</div>";
    HTMLString += "</div>";

    HTMLString += "</span>";

    // Bottom

    HTMLString += "<div style=\"margin-top: 10px; clear: left\">";
    HTMLString += "<span style=\"width: 230px; display: inline-block\">";

    if (gUser.isRaidlead) {
        HTMLString += "<input type=\"text\" id=\"repeat_amount\" value=\"0\" class=\"textinput\" style=\"padding-left: 3px; color: #ccc; width: 31px; height: 16px\">";

        HTMLString += "<select id=\"repeat_stride\" style=\"width: 180px\">";
        HTMLString += "<option value=\"once\">"+L("RepeatOnce")+"</option>";
        HTMLString += "<option value=\"day\">"+L("RepeatDay")+"</option>";
        HTMLString += "<option value=\"week\">"+L("RepeatWeek")+"</option>";
        HTMLString += "<option value=\"month\">"+L("RepeatMonth")+"</option>";
        HTMLString += "</select>";
    } else {
        HTMLString += "<input type=\"hidden\" id=\"repeat_amount\" value=\"0\">";
        HTMLString += "<input type=\"hidden\" id=\"repeat_stride\" value=\"once\">";
    }

    HTMLString += "</span>";
    HTMLString += "<span style=\"display: inline-block; width: 301px; float: right\" id=\"submit_options\">";

    if (gUser.isRaidlead) {
        HTMLString += "<select id=\"selectmode\" style=\"width: 180px\">";
        HTMLString += "<option value=\"manual\">"+L("RaidModeManual")+"</option>";
        HTMLString += "<option value=\"overbook\">"+L("RaidModeOverbook")+"</option>";
        HTMLString += "<option value=\"attend\">"+L("RaidModeAttend")+"</option>";
        HTMLString += "<option value=\"all\">"+L("RaidModeAll")+"</option>";
        HTMLString += "<option value=\"optout\">"+L("RaidModeOptOut")+"</option>";
        HTMLString += "</select>";
        HTMLString += "<button id=\"newRaidSubmit\" style=\"float:right\">"+L("CreateRaid")+"</button>";
    } else {
        HTMLString += "<input type=\"hidden\" id=\"selectmode\" value=\"all\">";
        HTMLString += "<button id=\"newRaidSubmit\" style=\"float:right\">"+L("CreateEvent")+"</button>";
    }

    HTMLString += "</span>";
    HTMLString += "</div>";

    container.empty().append( HTMLString );

    $("#descriptiondummy").focus( function() {
        $("#descriptiondummy").hide();
        $("#description").show().focus();
    });

    $("#description").blur( function() {
        if ( $("#description").val() === "" ) {
            $("#description").hide();
            $("#descriptiondummy").show();
        }
    });

    var imageList = [];

    $.each( aXHR.location, function(index, value) {
        imageList[index] = value.image;
        $("#selectlocation").append("<option value=\"" + value.id + "\">" + value.name + "</option>");
    });

    $("#locationimagepicker").data("imageNames", imageList );

    HTMLString = "<span><div>";
    var numImages = 1;

    $.each( aXHR.locationimage, function(index, value) {
        if ( numImages % 11 === 0 )
        {
            HTMLString += "<br/>";
            ++numImages;
        }

        HTMLString += "<img class=\"clickable\" src=\"themes/icons/"+gSite.Iconset+"/raidsmall/" + value + "\" onclick=\"applyLocationImage(this, false)\" style=\"width: 32px; height: 32px; margin-right: 5px;\"/>";
        ++numImages;
    });

    HTMLString += "</div></span>";
    $("#locationimagelist").append( HTMLString );

    $("#locationimagepicker")
        .data( "selectedImage", "unknown.png" )
        .click( function(aEvent) { showTooltipRaidImageList(); aEvent.stopPropagation(); } );

    // Fill in configurable values

    HTMLString = "";

    for ( i=4; i>=0; --i )
        HTMLString += "<option value=\"" + i + "\">" + formatHourPrefixed(i) + "</option>";

    for ( i=23; i>4; --i )
        HTMLString += "<option value=\"" + i + "\">" + formatHourPrefixed(i) + "</option>";

    var HourFieldWidth        = (gSite.TimeFormat == 24) ? 48 : 64;
    var LocationFieldWidth    = (gSite.TimeFormat == 24) ? 181 : 213;
    var DescriptionFieldWidth = (gSite.TimeFormat == 24) ? 295 : 327;
    var SheetOverlayWidth     = (gSite.TimeFormat == 24) ? 540 : 570;

    $("#starthour")
        .css("width", HourFieldWidth)
        .empty().append(HTMLString);

    $("#endhour")
        .css("width", HourFieldWidth)
        .empty().append(HTMLString);

    $("#selectlocation")
        .css("width", LocationFieldWidth);

    $("#descriptiondummy")
        .css({ "width": DescriptionFieldWidth, "max-width": DescriptionFieldWidth });

    $("#description")
        .css({ "width": DescriptionFieldWidth, "max-width": DescriptionFieldWidth });

    // Set the default settings

    var defaultSettings = [];
    defaultSettings["RaidStartHour"] = 19;
    defaultSettings["RaidStartMinute"] = 15;
    defaultSettings["RaidEndHour"] = 23;
    defaultSettings["RaidEndMinute"] = 0;
    defaultSettings["RaidSize"] = 10;
    defaultSettings["StartOfWeek"] = 1;

    $.each(aXHR.setting, function(index, setting) {
        defaultSettings[setting.name] = setting.value;
    });

    $("#starthour").children("option").each( function () {
        if ($(this).val() == defaultSettings["RaidStartHour"])
        $(this).attr("selected", "selected");
    });

    $("#startminute").children("option").each( function () {
        if ($(this).val() == defaultSettings["RaidStartMinute"])
        $(this).attr("selected", "selected");
    });

    $("#endhour").children("option").each( function () {
        if ($(this).val() == defaultSettings["RaidEndHour"])
        $(this).attr("selected", "selected");
    });

    $("#endminute").children("option").each( function () {
        if ($(this).val() == defaultSettings["RaidEndMinute"])
        $(this).attr("selected", "selected");
    });

    $("#selectsize").children("option").each( function () {
        if ($(this).val() == defaultSettings["RaidSize"])
        $(this).attr("selected", "selected");
    });

    if (gUser.isRaidlead) {
        $("#selectmode").children("option").each( function () {
            if ($(this).val() == defaultSettings["RaidMode"])
            $(this).attr("selected", "selected");
        });
    }

    // UI setup

    var dayNames = [L("Sun"), L("Mon"), L("Tue"), L("Wed"), L("Thu"), L("Fri"), L("Sat")];
    var monthNames = [L("January"), L("February"), L("March"), L("April"), L("May"), L("June"), L("July"), L("August"), L("September"), L("October"), L("November"), L("December")];

    if ( aDay > 0 )
    {
        $("#raiddatepicker").datepicker( {
            dayNamesShort: dayNames,
            dayNamesMin: dayNames,
            monthNames: monthNames,
            firstDay: defaultSettings["StartOfWeek"],
            inline: true,
            defaultDate: aMonth + "/" + aDay + "/" + aYear
        });
    }
    else
    {
        $("#raiddatepicker").datepicker( {
            dayNamesShort: dayNames,
            dayNamesMin: dayNames,
            monthNames: monthNames,
            firstDay: defaultSettings["StartOfWeek"],
            inline: true
        });
    }

    $("#newRaidSubmit").click( triggerNewRaid );
    $("#eventblocker").show().click( startFadeTooltip );

    sheet.show();

    $("#newRaid").click( startFadeTooltip );
    $("#sheetoverlay").click( startFadeTooltip );

    if (gUser.isRaidlead) {
        $("#typeToogle").buttonset();
        $("#repeat_stride").combobox({ inlineStyle: { "float": "right" }, darkBackground: true });
        $("#selectmode").combobox({ inlineStyle: { "float": "left" }, darkBackground: true});

        $("#typeToogle").change(function() {
            if ($("input[name=\"typeToggle\"]:checked").val() == "event") {
                $("#repeat_amount").hide();
                $("#repeat_stride_combobox").hide();
                $("#selectmode_combobox").hide();
                $("#newRaidSubmit").button({label:L("CreateEvent")})
            } else {
                $("#repeat_amount").show();
                $("#repeat_stride_combobox").show();
                $("#selectmode_combobox").show();
                $("#newRaidSubmit").button({label:L("CreateRaid")})
            }
        });
    }

    $("#selectlocation").combobox({ editable: true, darkBackground: true });
    $("#selectsize").combobox({ darkBackground: true });
    $("#starthour").combobox({ darkBackground: true });
    $("#startminute").combobox({ darkBackground: true });
    $("#endhour").combobox({ darkBackground: true });
    $("#endminute").combobox({ darkBackground: true });
    $("#stage").combobox({ darkBackground: true });
    $("#newRaidSubmit").button({ icons: { secondary: "ui-icon-disk" }});
}

// -----------------------------------------------------------------------------

function triggerNewRaid()
{
    var raidImage = $("#locationimagepicker").data( "selectedImage" );
    var startDate = new Date( $("#raiddatepicker").datepicker("getDate") );
    var endDate   = new Date( startDate );

    startDate.setHours( parseInt($("#starthour").val(),10) );
    startDate.setMinutes( parseInt($("#startminute").val(),10) );

    endDate.setHours( parseInt($("#endhour").val(),10) );
    endDate.setMinutes( parseInt($("#endminute").val(),10) );

    if ( startDate.getHours() > endDate.getHours() )
    {
        endDate.setTime( endDate.getTime() + 1000 * 60 * 60 * 24 );
        endDate.setHours( parseInt($("#endhour").val(),10) ); // because crossing DST "breaks" the hour
    }

    var raidType = (gUser.isRaidlead) ? $("input[name=\"typeToggle\"]:checked").val() : "event";

    var parameters = {
        raidImage    : raidImage,
        type         : raidType,
        locationId   : $("#selectlocation").val(),
        locationSize : $("#selectsize").val(),
        locationName : $("#selectlocation_edit").val(),
        startYear    : startDate.getFullYear(),
        startMonth   : startDate.getMonth()+1,
        startDay     : startDate.getDate(),
        startHour    : startDate.getHours(),
        startMinute  : startDate.getMinutes(),
        startOffset  : startDate.getTimezoneOffset(),
        endYear      : endDate.getFullYear(),
        endMonth     : endDate.getMonth()+1,
        endDay       : endDate.getDate(),
        endHour      : endDate.getHours(),
        endMinute    : endDate.getMinutes(),
        endOffset    : endDate.getTimezoneOffset(),
        mode         : $("#selectmode").val(),
        description  : $("#description").val(),
        repeat       : $("#repeat_amount").val(),
        stride       : $("#repeat_stride").val()
    };

    asyncQuery( "raid_create", parameters, generateCalendar );
}