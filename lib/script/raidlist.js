// =============================================================================
//  Raid list
// =============================================================================

function generateRaidList( aXHR )
{
    var HTMLString = "<div id=\"raidlist\">";

    // Outstanding raids

    HTMLString += "<h1>" + L("Upcoming") + "</h1>";
    HTMLString += "<div id=\"nextRaids\">";

    $.each(aXHR.raid, function(index, value) {
        // Query raid status variables

        var Complete = true;
        var RaidIsLocked = false;

        for ( var i=0; Complete && (i < gRoleIds.length); i++ )
        {
            var Required   = parseInt( value["role"+i+"Slots"], 10 );
            var SlotsInUse = parseInt( value["role"+i], 10 );
            Complete = SlotsInUse >= Required;
        }

        var OverlayClass = (Complete) ? "overlayStatusOk" : "overlayStatusOpen";

        if ( value.stage == "canceled" )
        {
            OverlayClass = "overlayStatusCanceled";
            RaidIsLocked = true;
        }
        else if ( value.stage == "locked" )
        {
            OverlayClass = "overlayStatusLocked";
            RaidIsLocked = true;
        }

        // Display raid in list
        
        var startDate = value.startDate;
        var startTime = value.start;
        var endDate   = value.endDate;
        var endTime   = value.end;
        
        HTMLString += "<span class=\"raidSlot\">";

        HTMLString += "<span class=\"locationImg\" id=\"raid" + value.id + "\">";
        HTMLString += "<img src=\"images/raidbig/" + value.image + "\"/>";
        HTMLString += generateRaidTooltip( value, RaidIsLocked, false );
        HTMLString += "<div class=\"overlayStatus " + OverlayClass + "\"></div>";
        HTMLString += "</span>";

        HTMLString += "<span class=\"raidInfo\">";
        HTMLString += "<div class=\"location\">" + value.location + " (" + value.size + ")" + "</div>";
        HTMLString += formatDateStringUTC(startDate, startTime) + "<br/>" + formatTimeStringUTC(startDate,startTime) + " - " + formatTimeStringUTC(endDate,endTime);
        HTMLString += " " + formatDateOffsetUTC(startDate, startTime) + "<br/>";

        HTMLString += "</span>";

        HTMLString += "<span class=\"setupInfo\">";

        if ( Complete )
        {
            HTMLString += "<div class=\"setupInfoSlot\" style=\"background-image: url(lib/layout/images/slot_ok.png)\"></div>";
        }
        else
        {
            for ( i=0; i < gRoleIds.length; i++ )
            {
                var Required   = parseInt( value["role"+i+"Slots"], 10 );
                var SlotsInUse = parseInt( value["role"+i], 10 );

                if ( SlotsInUse < Required )
                {
                    HTMLString += "<div class=\"setupInfoSlot\" style=\"background-image: url("+gRoleImages[i]+")\">";
                    HTMLString += "+"+(Required-SlotsInUse);
                    HTMLString += "</div>";
                }
            }
        }

        HTMLString += "</span>";
        HTMLString += "</span>";
    });


    HTMLString += "</div>";

    // Raid history

    HTMLString += "<br/><h1>" + L("History") + "</h1>";
    HTMLString += "<div id=\"raidHistory\">";

    $.each(aXHR.history, function(index, value) {

        var startDate = value.startDate;
        var startTime = value.start;
        
        HTMLString += "<span class=\"historySlot\" id=\"raid" + value.id + "\">";
        HTMLString += "<img class=\"icon\" src=\"images/raidsmall/" + value.image + "\"/>";

        if ( value.stage == "canceled" )
        {
            HTMLString += "<div class=\"overlayCanceled\"></div>";
        }
        else if ( value.stage == "locked" )
        {
            HTMLString += "<div class=\"overlayLocked\"></div>";
        }

        HTMLString += "<div class=\"name\">" + value.location + "<br/><span style=\"font-size: 80%\">" + formatDateStringUTC(startDate, startTime) + "</span></div>";
        HTMLString += "</span>";
    });

    HTMLString += "</div>";

    $("#body").empty().append(HTMLString);

    var Interval = $("#appwindow").data("animation");
    window.clearInterval(Interval);

    Interval = window.setInterval( function() {
       $(".setupInfo").each( function() {
           var Frame = $(this);

           if ( $(this).children().length > 2 )
           {
               var FirstElement = Frame.children().first();
               var OriginalHeight = FirstElement.height();

               FirstElement.slideUp( 1000, function() {
                   $(this).detach().appendTo(Frame).show();
               });
           }
       });
    }, 3000);

    $("#appwindow").data("animation", Interval );

    $(".locationImg")
        .mouseover( function() { showTooltipRaidInfo( $(this), false, false ); } )
        .click( function( aEvent ) { showTooltipRaidInfo( $(this), false, true ); aEvent.stopPropagation(); } )
        .dblclick( function( aEvent ) { changeContext( "raid,setup," + parseInt( $(this).attr("id").substr(4), 10 ) ); aEvent.stopPropagation(); } );

    $(".historySlot")
        .dblclick( function( aEvent ) { changeContext( "raid,setup," + parseInt( $(this).attr("id").substr(4), 10 ) ); aEvent.stopPropagation(); } );
}

// =============================================================================
//    Raid list display
// =============================================================================

function loadAllRaids()
{
    reloadUser();

    if ( gUser == null )
        return;

    $("#body").empty();

    var Parameters = {
        offset     : 0,
        count      : 30
    };

    asyncQuery( "raid_list", Parameters, generateRaidList );
}