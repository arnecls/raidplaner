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
                
        $.each(gGame.Roles, function(aRoleId, aRole)
        {
            var Required = value.slotMax[aRoleId];
            var Occupied = value.slotCount[aRoleId];
            Complete = Complete && (Occupied >= Required);
        });

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

        HTMLString += "<span class=\"raidSlot box_inlay\">";

        HTMLString += "<span id=\"raid" + value.id + "\" class=\"locationImg clickable\" index=\""+index+"\" locked=\""+RaidIsLocked+"\" >";
        HTMLString += "<img src=\"themes/icons/"+gSite.Iconset+"/raidbig/" + value.image + "\"/>";
        HTMLString += "<div class=\"overlayStatus " + OverlayClass + "\"></div>";
        HTMLString += "</span>";

        HTMLString += "<span class=\"raidInfo\">";
        HTMLString += "<div class=\"location\">" + value.location + " (" + value.size + ")" + "</div>";
        HTMLString += formatDateStringUTC(startDate, startTime) + "<br/>" + formatTimeStringUTC(startDate,startTime) + " - " + formatTimeStringUTC(endDate,endTime);
        HTMLString += " " + formatDateOffsetUTC(startDate, startTime) + "<br/>";
        HTMLString += "<div style=\"line-height: 2.5em\">" + value.attended + " / " + value.size + " " + L("Players") + "</div>";
        
        HTMLString += "</span>";

        HTMLString += "<span class=\"setupInfo\">";

        if ( Complete )
        {
            HTMLString += "<div class=\"setupInfoSlot\" style=\"background-image: url(lib/layout/images/slot_ok.png)\"></div>";
        }
        else
        {
            $.each(gGame.Roles, function(aRoleId, aRole)
            {
                var Required = value.slotMax[aRoleId];
                var Occupied = value.slotCount[aRoleId];

                if ( Occupied < Required )
                {
                    HTMLString += "<div class=\"setupInfoSlot\" style=\"background-image: url(lib/layout/images/"+aRole.style+".png)\">";
                    HTMLString += "+"+(Required-Occupied);
                    HTMLString += "</div>";
                }
            });
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

        HTMLString += "<span class=\"historySlot clickable\" id=\"raid" + value.id + "\">";
        HTMLString += "<img class=\"icon clickable\" src=\"themes/icons/"+gSite.Iconset+"/raidsmall/" + value.image + "\"/>";

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

    $(".locationImg").each(function() {
        var RaidIdx = parseInt($(this).attr("index"), 10);
        var RaidIsLocked = $(this).attr("locked") == "true";       
        var RaidData = aXHR.raid[RaidIdx];
        
        $(this).mouseover( function() { 
            showTooltipRaidInfo( $(this), RaidData, RaidIsLocked, false, false ); 
        });
        
        $(this).click( function( aEvent ) { 
            showTooltipRaidInfo( $(this), RaidData, RaidIsLocked, false, true ); 
            aEvent.stopPropagation(); 
        });
        
        $(this).dblclick( function( aEvent ) { 
            changeContext( "raid,setup," + parseInt( $(this).attr("id").substr(4), 10 ) ); 
            aEvent.stopPropagation(); 
        }); 
    });        

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