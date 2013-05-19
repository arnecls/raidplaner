// =============================================================================
//  Raid list
// =============================================================================

function generateRaidList( aXMLData )
{
    var Message = $(aXMLData).children("messagehub");
    var HTMLString = "<div id=\"raidlist\">";

    // Outstanding raids

    HTMLString += "<h1>" + L("Upcoming") + "</h1>";
    HTMLString += "<div id=\"nextRaids\">";

    Message.children("raids").children("raid").each( function() {

        var DateStr = $(this).children("startDate").text();
        var Year  = DateStr.substr(0,4);
        var Month = DateStr.substr(5,2);
        var Day   = DateStr.substr(8,2);

        $(this).children("location").text();

        // Query raid status variables

        var Complete = true;
        var RaidIsLocked = false;

        for ( var i=0; Complete && (i < gRoleIds.length); i++ )
        {
            var Required   = parseInt( $(this).children("role"+i+"Slots").text(), 10 );
            var SlotsInUse = parseInt( $(this).children("role"+i).text(), 10 );
            Complete = SlotsInUse >= Required;
        }

        var OverlayClass = (Complete) ? "overlayStatusOk" : "overlayStatusOpen";

        if ( $(this).children("stage").text() == "canceled" )
        {
            OverlayClass = "overlayStatusCanceled";
            RaidIsLocked = true;
        }
        else if ( $(this).children("stage").text() == "locked" )
        {
            OverlayClass = "overlayStatusLocked";
            RaidIsLocked = true;
        }

        // Display raid in list

        HTMLString += "<span class=\"raidSlot\">";

        HTMLString += "<span class=\"locationImg\" id=\"raid" + $(this).children("id").text() + "\">";
        HTMLString += "<img src=\"images/raidbig/" + $(this).children("image").text() + "\"/>";
        HTMLString += generateRaidTooltip( $(this), RaidIsLocked, false );
        HTMLString += "<div class=\"overlayStatus " + OverlayClass + "\"></div>";
        HTMLString += "</span>";

        HTMLString += "<span class=\"raidInfo\">";
        HTMLString += "<div class=\"location\">" + $(this).children("location").text() + " (" + $(this).children("size").text() + ")" + "</div>";
        HTMLString += Day + "." + Month + ". " + Year + "<br/>";
        HTMLString += formatTimeString($(this).children("start").text()) + " - " + formatTimeString($(this).children("end").text()) + "<br/>";

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
                var Required   = parseInt( $(this).children("role"+i+"Slots").text(), 10 );
                var SlotsInUse = parseInt( $(this).children("role"+i).text(), 10 );

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

    Message.children("raidList").children("raid").each( function(aIndex) {

        var DateStr = $(this).children("startDate").text();
        var Year  = DateStr.substr(0,4);
        var Month = DateStr.substr(5,2);
        var Day   = DateStr.substr(8,2);

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

        HTMLString += "<div class=\"name\">" + $(this).children("location").text() + "<br/><span style=\"font-size: 80%\">" + Day + "." + Month + ". " + Year + "</span></div>";
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
        offset : 0,
        count  : 30
    };

    asyncQuery( "raid_list", Parameters, generateRaidList );
}