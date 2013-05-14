var g_UnappliedChanges = false;

function onUIDataChange()
{
    g_UnappliedChanges = true;
    $(".apply_changes").button("option", "disabled", false);
}

// -----------------------------------------------------------------------------

function onAppliedUIDataChange()
{
    g_UnappliedChanges = false;
    $(".apply_changes").button("option", "disabled", true);
}

// -----------------------------------------------------------------------------

function onChangeContext( event )
{
    var name = event.currentTarget.id;
    var nameParts = name.split("_");    
    name = nameParts[1];
    
    for ( i=2; i<nameParts.length; ++i )
    {
        name += ","+nameParts[i];
    }
       
    if ( g_UnappliedChanges )
    {
        confirm(L("UnappliedChanges"), L("DiscardChanges"), L("Cancel"), function() {
            changeContext( name );
            onAppliedUIDataChange();
        });
    }
    else
    {    
        changeContext( name );
    }
}

// -----------------------------------------------------------------------------

function changeContext( aName )
{
    var nameParts = aName.split(",");
    var name = nameParts[0];
    
    if ( name == "settings" )
        name = "settings_users";
    
    var hash = window.location.hash.substring( 1, window.location.hash.length );

    $(".menu_button").removeClass("on");
    $("#button_" + name).addClass("on");

    hideTooltip();

    if ( hash != aName )
    {
        var url = window.location.href.substr( 0, window.location.href.lastIndexOf("#") );
        window.location.href = url + "#" + aName;
        return true;
    }

    return false;
}

// -----------------------------------------------------------------------------

function checkUser()
{
    if ( gUser == null )
    {
        var url = window.location.href.substr( 0, window.location.href.lastIndexOf("#") );
        window.location.href = url;
        return false;
    }
    
    return true;
}

// -----------------------------------------------------------------------------

function reloadUser()
{
    $.ajax({
        type: "GET",
        url: "lib/script/_session.js.php?version=" + g_SiteVersion,
        dataType: "script",
        success: checkUser,
        cache: false
    });
}

// -----------------------------------------------------------------------------

function confirm( aText, aYes, aNo, aCallback )
{
    $("#dialog").empty().append( aText );
    
    var dialogWidget = $("#dialog").dialog({
        resizable: false,
        draggable: false,
        width: 400,
        title: L("Notification"),
        dialogClass: "dialogFix"
    });

    dialogWidget.dialog( "option", "buttons", [{
            text: aYes,
            click: function() {
                $( this ).dialog( "close" );
                $("#eventblocker").hide();
                aCallback();
            }
        },
        {
            text: aNo,
            click: function() {
                $( this ).dialog( "close" );
            }
        }]
    );

    dialogWidget.dialog({
           close: function() { $("#eventblocker").hide(); }
    });

    $(".ui-dialog-buttonset").children().css("font-size", 11);

    $(".ui-dialog-buttonset").children().last().focus();
    $("#eventblocker").show();
}

// -----------------------------------------------------------------------------

function notify( aText )
{
    $("#dialog").empty().append( aText );
    
    var dialogWidget = $("#dialog").dialog({
        resizable: false,
        draggable: false,
        title: L("Notification"),
        width: 400,
        dialogClass: "dialogFix"
    });

    dialogWidget.dialog( "option", "buttons", [{
        text: "Ok",
        click: function() {
            $( this ).dialog( "close" );
        }
    }]);

    dialogWidget.dialog({
           close: function() { $("#eventblocker").hide(); }
    });

    $(".ui-dialog-buttonset").children().css("font-size", 11);

    $(".ui-dialog-buttonset").children().last().focus();
    $("#eventblocker").show();
}

// -----------------------------------------------------------------------------

function prompt( aText, aOk, aCancel, aCallback )
{
    var HTMLString = "<br/><input id=\"prompt_text\" class=\"prompt_text\"/>";

    $("#dialog").empty()
        .append( aText )
        .append( HTMLString );

    var dialogWidget = $("#dialog").dialog({
        resizable: false,
        draggable: false,
        width: 400,
        title: L("InputRequired"),
        dialogClass: "dialogFix"
    });

    dialogWidget.dialog( "option", "buttons", [{
            text: aOk,
            click: function() {
                $( this ).dialog( "close" );
                $("#eventblocker").hide();
                aCallback($("#prompt_text").val());
            }
        },
        {
            text: aCancel,
            click: function() {
                $( this ).dialog( "close" );
            }
        }]
    );

    dialogWidget.dialog({
           close: function() { $("#eventblocker").hide(); }
    });

    $(".ui-dialog-buttonset").children().css("font-size", 11);

    $(".ui-dialog-buttonset").children().last().focus();
    $("#eventblocker").show();
}

// -----------------------------------------------------------------------------

function onResize()
{
    hideTooltip();
    
    var appHeight = $("#appwindow").height();
    var appOffset = $("#appwindow").offset().top;
    var docHeight = $(window).height();
    
    if (docHeight < appHeight)
    {
        // scroll into view only of not working on an input
        // element. This fixes iOS password field scrolling out of view
        
        if ( $("input:focus").length === 0 )
        {
            var scrollMenuToView = $("#menu").offset().top - appOffset;        
            var scrollBottomToView = appHeight - docHeight;
            
            $("#appwindow").offset({top: 0});
            $(window).scrollTop(Math.min(scrollBottomToView, scrollMenuToView) );
        }
    }
    else
    {
        // center the appwindow vertically
        // could be done via css, but we need to cover the above fallback, too.
            
        $("#appwindow").offset({top: docHeight/2 - appHeight/2});
    }
}

// -----------------------------------------------------------------------------

var gAfterInit = null;

$(document).ready( function() {

    onChangeConfig();

    if ( $("#tooltip").size() > 0)
        $("#tooltip").hide();

    if ( $("#closesheet").size() > 0)
        $("#closesheet").click( closeSheet );

    if ( $("#sheetoverlay").size() > 0)
    {
        $("#sheetoverlay").hide();
        $("#newlocation").hide();
        $("#description").hide();
    }

    $("#dialog").hide();

    $("#ajaxblocker").hide();

    $("#ajaxblocker").ajaxSend( function() {
        $("#ajaxblocker").delay(2000).show(1);
    });

    $("#ajaxblocker").ajaxComplete( onAsyncDone );
    $("#ajaxblocker").ajaxError( onAsyncError );

    $(".button_logout").button({
        icons: { secondary: "ui-icon-eject" }
    }).css( "font-size", 11 );

    $(window).resize( onResize );

    $("#body").add("#logo").add("#menu")
        .click( startFadeTooltip );

    initMenu();
    initLogin();
    onResize();
    
    if ( gAfterInit != null )
        gAfterInit();
});