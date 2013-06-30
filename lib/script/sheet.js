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

function showSheetNewRaid( aXMLData, aDay, aMonth, aYear )
{
    if ( gUser == null )
        return;

    var sheet = $("#sheetoverlay");
    var container = $("#sheet_body");
    
    var Message = $(aXMLData).children("messagehub:first");
    var Locations = Message.children("location");
    var LocationImages = Message.children("locationimage");
    
    HTMLString  = "<span style=\"display: inline-block; vertical-align: top; margin-right: 20px\">";
    HTMLString += "<div id=\"raiddatepicker\"></div>";
    HTMLString += "</span>";
    
    HTMLString += "<span style=\"display: inline-block; vertical-align: top\">";
    HTMLString += "<span style=\"display: inline-block; margin-right: 5px; float: left\" class=\"imagepicker clickable\" id=\"locationimagepicker\"><div class=\"imagelist\" id=\"locationimagelist\"></div></span>";
    HTMLString += "<span style=\"display: inline-block; vertical-align: top\">";
    
    HTMLString += "<div style=\"margin-bottom: 10px\">";
    HTMLString += "<select id=\"selectlocation\" onchange=\"onLocationChange(this)\">";
    HTMLString += "<option value=\"0\">"+L("NewDungeon")+"</option>";
    HTMLString += "</select>";
    
    HTMLString += "<span style=\"display: inline-block; width: 3px;\"></span>";
    HTMLString += "<select id=\"selectsize\" style=\"width: 48px\">";
    
    for (var i=0; i<gGroupSizes.length; ++i)
    {
        HTMLString += "<option value=\""+gGroupSizes[i]+"\">"+gGroupSizes[i]+"</option>";
    }
    
    HTMLString += "</select>";                    
    HTMLString += "</div>";
    
    HTMLString += "<div>";
    HTMLString += "<select id=\"starthour\">";
    HTMLString += "</select>";
    
    HTMLString += "<span style=\"display: inline-block; width: 10px; text-align:center; position: relative; top: -5px\">:</span>";
    HTMLString += "<select id=\"startminute\" style=\"width: 48px\">";
    HTMLString += "<option value=\"0\">00</option>";
    HTMLString += "<option value=\"15\">15</option>";
    HTMLString += "<option value=\"30\">30</option>";
    HTMLString += "<option value=\"45\">45</option>";
    HTMLString += "</select>";
    
    HTMLString += "<span style=\"display: inline-block; width: 20px; text-align:center; position: relative; top: -5px\">"+L("to")+"</span>";
    HTMLString += "<select id=\"endhour\">";
    HTMLString += "</select>";
    
    HTMLString += "<span style=\"display: inline-block; width: 10px; text-align:center; position: relative; top: -5px\">:</span>";
    HTMLString += "<select id=\"endminute\" style=\"width: 48px\">";
    HTMLString += "<option value=\"0\">00</option>";
    HTMLString += "<option value=\"15\">15</option>";
    HTMLString += "<option value=\"30\">30</option>";
    HTMLString += "<option value=\"45\">45</option>";
    HTMLString += "</select>";
    
    HTMLString += "</div>";
    HTMLString += "</span>";
    
    HTMLString += "<div style=\"margin-top: 20px; clear: left\">";
    HTMLString += "<textarea id=\"descriptiondummy\" class=\"textdummy description\">"+L("Description")+"</textarea>";
    HTMLString += "<textarea id=\"description\" class=\"textinput description\" style=\"display:none\"></textarea>";
    HTMLString += "</div>";
    
    HTMLString += "<div style=\"margin-top: 10px\" id=\"submit_options\">";
    HTMLString += "<select id=\"selectmode\" style=\"width: 180px\">";
    HTMLString += "<option value=\"manual\">"+L("RaidModeManual")+"</option>";
    HTMLString += "<option value=\"attend\">"+L("RaidModeAttend")+"</option>";
    HTMLString += "<option value=\"all\">"+L("RaidModeAll")+"</option>";
    HTMLString += "</select>";
    HTMLString += "<button id=\"newRaidSubmit\" style=\"float:right\">"+L("CreateRaid")+"</button>";               
    HTMLString += "</div>";
    
    HTMLString += "</span>";

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

    sheet.css("margin-top", -130);
    sheet.css("margin-left", -310);

    var imageList = [];

    Locations.each( function(index) {
        imageList[index] = $(this).children("image").text();
        $("#selectlocation").append("<option value=\"" + $(this).children("id").text() + "\">" + $(this).children("name").text() + "</option>");
    });

    $("#locationimagepicker").data("imageNames", imageList );

    HTMLString = "<span><div>";
    var numImages = 1;

    LocationImages.each( function(index) {
        if ( numImages % 11 === 0 )
        {
            HTMLString += "<br/>";
            ++numImages;
        }

        HTMLString += "<img class=\"clickable\" src=\"images/raidsmall/" + $(this).text() + "\" onclick=\"applyLocationImage(this, false)\" style=\"width: 32px; height: 32px; margin-right: 5px;\"/>";
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

    var HourFieldWidth        = (gTimeFormat == 24) ? 48 : 64;
    var LocationFieldWidth    = (gTimeFormat == 24) ? 181 : 213;
    var DescriptionFieldWidth = (gTimeFormat == 24) ? 295 : 327;
    var SheetOverlayWidth     = (gTimeFormat == 24) ? 540 : 570;

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
        
    container.css("width", SheetOverlayWidth);
        
    $("#submit_options")
        .css("width", DescriptionFieldWidth+4);

    // Set the default settings

    var defaultSettings = [];
    defaultSettings["RaidStartHour"] = 19;
    defaultSettings["RaidStartMinute"] = 15;
    defaultSettings["RaidEndHour"] = 23;
    defaultSettings["RaidEndMinute"] = 0;
    defaultSettings["RaidSize"] = 10;
    defaultSettings["StartOfWeek"] = 1;

    Message.children("settings").children().each( function() {
        defaultSettings[$(this)[0].tagName] = $(this).text();
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

    $("#selectmode").children("option").each( function () {
        if ($(this).val() == defaultSettings["RaidMode"])
        $(this).attr("selected", "selected");
    });

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

    $("#selectlocation").combobox({ editable: true, darkBackground: true });
    $("#selectsize").combobox({ darkBackground: true });
    $("#starthour").combobox({ darkBackground: true });
    $("#startminute").combobox({ darkBackground: true });
    $("#endhour").combobox({ darkBackground: true });
    $("#endminute").combobox({ darkBackground: true });
    $("#stage").combobox({ darkBackground: true });
    $("#selectmode").combobox({ inlineStyle: { float: "left" }, darkBackground: true});
    $("#newRaidSubmit").button({ icons: { secondary: "ui-icon-disk" }}).height(20).css("font-size", 11);
}

// -----------------------------------------------------------------------------

function triggerNewRaid()
{
    var raidImage = $("#locationimagepicker").data( "selectedImage" );
    var date = new Date( $("#raiddatepicker").datepicker( "getDate" ) );

    var parameters = {
        raidImage    : raidImage,
        timeOffset   : date.getTimezoneOffset(),
        locationId   : $("#selectlocation").val(),
        locationSize : $("#selectsize").val(),
        locationName : $("#selectlocation_edit").val(),
        startHour    : $("#starthour").val(),
        startMinute  : $("#startminute").val(),
        endHour      : $("#endhour").val(),
        endMinute    : $("#endminute").val(),
        mode         : $("#selectmode").val(),
        description  : $("#description").val(),
        month        : date.getMonth()+1,
        day          : date.getDate(),
        year         : date.getFullYear()
    };

    asyncQuery( "raid_create", parameters, generateCalendar );
}