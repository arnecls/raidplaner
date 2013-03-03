// =============================================================================
//  Raid list
// =============================================================================

function displayRaidList( a_XMLData )
{
    var Message = $(a_XMLData).children("messagehub");
    var HTMLString = "<div id=\"raidlist\">";

    // Outstanding raids

    HTMLString += "<h1>" + L("Upcoming") + "</h1>";
    HTMLString += "<div id=\"nextRaids\">";

    Message.children("raids").children("raid").each( function() {

        var dateStr = $(this).children("startDate").text();
        var year  = dateStr.substr(0,4);
        var month = dateStr.substr(5,2);
        var day   = dateStr.substr(8,2);

        $(this).children("location").text();

        // Query raid status variables

        var complete = true;
        var raidIsLocked = false;

        for ( var i=0; complete && (i < g_RoleIds.length); i++ )
        {
            var required   = parseInt( $(this).children("role"+i+"Slots").text() );
            var slotsInUse = parseInt( $(this).children("role"+i).text() );
            complete = slotsInUse >= required;
        }

        var overlayClass = (complete) ? "overlayStatusOk" : "overlayStatusOpen";

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

        // Display raid in list

        HTMLString += "<span class=\"raidSlot\">";

        HTMLString += "<span class=\"locationImg\" id=\"raid" + $(this).children("id").text() + "\">";
        HTMLString += "<img src=\"images/raidbig/" + $(this).children("image").text() + "\"/>";
        HTMLString += DisplayRaidTooltip( $(this), raidIsLocked, false );
        HTMLString += "<div class=\"overlayStatus " + overlayClass + "\"></div>";
        HTMLString += "</span>";

        HTMLString += "<span class=\"raidInfo\">";
        HTMLString += "<div class=\"location\">" + $(this).children("location").text() + " (" + $(this).children("size").text() + ")" + "</div>";
        HTMLString += day + "." + month + ". " + year + "<br/>";
        HTMLString += formatTimeString($(this).children("start").text()) + " - " + formatTimeString($(this).children("end").text()) + "<br/>";

        HTMLString += "</span>";

        HTMLString += "<span class=\"setupInfo\">";

        if ( complete )
        {
            HTMLString += "<div class=\"setupInfoSlot\" style=\"background-image: url(lib/layout/images/slot_ok.png)\"></div>";
        }
        else
        {
            for ( var i=0; i < g_RoleIds.length; i++ )
            {
                var required   = parseInt( $(this).children("role"+i+"Slots").text() );
                var slotsInUse = parseInt( $(this).children("role"+i).text() );

                if ( slotsInUse < required )
                {
                    HTMLString += "<div class=\"setupInfoSlot\" style=\"background-image: url("+g_RoleImages[i]+")\">";
                    HTMLString += "+"+(required-slotsInUse);
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

    var interval = $("#appwindow").data("animation");
    window.clearInterval(interval);

    interval = window.setInterval( function() {
       $(".setupInfo").each( function() {
           var frame = $(this);

           if ( $(this).children().length > 2 )
           {
               var firstElement = frame.children().first();
               var originalHeight = firstElement.height();

               firstElement.slideUp( 1000, function() {
                   $(this).detach().appendTo(frame).show();
               });
           }
       });
    }, 3000);

    $("#appwindow").data("animation", interval );

    $(".locationImg")
        .mouseover( function() { showTooltipRaidInfo( $(this), false, false ); } )
        .click( function( event ) { showTooltipRaidInfo( $(this), false, true ); event.stopPropagation(); } )
        .dblclick( function( event ) { changeContext( "raid,setup," + parseInt( $(this).attr("id").substr(4) ) ); event.stopPropagation(); } );

    $(".historySlot")
        .dblclick( function( event ) { changeContext( "raid,setup," + parseInt( $(this).attr("id").substr(4) ) ); event.stopPropagation(); } )
}

// =============================================================================
//    Raid list display
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