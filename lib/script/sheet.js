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

function onLocationChange( a_SelectObj )
{
    $("#locationimagepicker").unbind("click");

    if ( a_SelectObj.selectedIndex == 0)
    {
        $(a_SelectObj).combobox( "editable", true );

        $("#locationimagepicker").click( function(event) { showTooltipRaidImageList(); event.stopPropagation(); } );
        $("#locationimagepicker").css( "background-image", "url(images/raidbig/unknown.png)" );
    }
    else
    {
        $(a_SelectObj).combobox( "editable", false );

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
    var numImages = 1;

    LocationImages.each( function(index) {
        if ( numImages % 11 == 0 )
        {
            HTMLString += "<br/>";
            ++numImages;
        }

        HTMLString += "<img src=\"images/raidsmall/" + $(this).text() + "\" onclick=\"applyLocationImage(this)\" style=\"width: 32px; height: 32px; margin-right: 5px;\"/>";
        ++numImages;
    });

    HTMLString += "</div></span>";
    $("#locationimagelist").append( HTMLString );

    $("#locationimagepicker")
        .data( "selectedImage", "unknown.png" )
        .click( function(event) { showTooltipRaidImageList(); event.stopPropagation(); } );

    // Set the default settings

    var defaultSettings = new Array();
    defaultSettings["RaidStartHour"] = 19;
    defaultSettings["RaidStartMinute"] = 15;
    defaultSettings["RaidEndHour"] = 23;
    defaultSettings["RaidEndMinute"] = 0;
    defaultSettings["RaidSize"] = 10;

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

    $("#newRaidSubmit").click( triggerNewRaid );
    $("#eventblocker").show().click( startFadeTooltip );

    sheet.show();

    $("#newRaid").click( startFadeTooltip );
    $("#sheetoverlay").click( startFadeTooltip );

    $("#selectlocation").combobox();
    $("#selectlocation").combobox( "editable", true );
    $("#selectsize").combobox();
    $("#starthour").combobox();
    $("#startminute").combobox();
    $("#endhour").combobox();
    $("#endminute").combobox();
    $("#stage").combobox();
    $("#selectmode").combobox();
    $("#newRaidSubmit").button({ icons: { secondary: "ui-icon-disk" }}).height(24).css("font-size", 11);
}

// -----------------------------------------------------------------------------

function triggerNewRaid()
{
    var raidImage = $("#locationimagepicker").data( "selectedImage" );
    var date = new Date( $("#raiddatepicker").datepicker( "getDate" ) );

    var parameters = {
        raidImage     : raidImage,
        locationId     : $("#selectlocation").val(),
        locationSize : $("#selectsize").val(),
        locationName : $("#edit_selectlocation").val(),
        startHour     : $("#starthour").val(),
        startMinute  : $("#startminute").val(),
        endHour         : $("#endhour").val(),
        endMinute     : $("#endminute").val(),
        mode         : $("#selectmode").val(),
        description     : $("#description").val(),
        month         : date.getMonth()+1,
        day             : date.getDate(),
        year         : date.getFullYear()
    };

    AsyncQuery( "raid_create", parameters, displayCalendar );
}