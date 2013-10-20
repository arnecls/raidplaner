var gUnappliedChanges = false;

function onUIDataChange()
{
    gUnappliedChanges = true;
    $(".apply_changes").button("option", "disabled", false);
}

// -----------------------------------------------------------------------------

function onAppliedUIDataChange()
{
    gUnappliedChanges = false;
    $(".apply_changes").button("option", "disabled", true);
}

// -----------------------------------------------------------------------------

function onChangeContext( aEvent )
{
    var Name = aEvent.currentTarget.id;
    var NameParts = Name.split("_");    
    Name = NameParts[1];
    
    for ( var i=2; i<NameParts.length; ++i )
    {
        Name += ","+NameParts[i];
    }
       
    if ( gUnappliedChanges )
    {
        var hash = window.location.hash.substring( 1, window.location.hash.length );
        if ( hash != Name )
        {        
            confirm(L("UnappliedChanges"), L("DiscardChanges"), L("Cancel"), function() {
                changeContext( Name );
                onAppliedUIDataChange();
            });
        }
    }
    else
    {    
        changeContext( Name );
    }
}

// -----------------------------------------------------------------------------

function changeContext( aName )
{
    var NameParts = aName.split(",");
    var Name = NameParts[0];
    
    if ( Name == "settings" )
        Name = "settings_users";
    
    var hash = window.location.hash.substring( 1, window.location.hash.length );

    $(".menu_button").removeClass("on");
    $("#button_" + Name).addClass("on");

    hideTooltip();

    if ( hash != aName )
    {
        var Url = window.location.href.substr( 0, window.location.href.lastIndexOf("#") );
        window.location.href = Url + "#" + aName;
        return true;
    }

    return false;
}

// -----------------------------------------------------------------------------

function checkUser()
{
    if ( gUser == null )
    {
        var Url = window.location.href.substr( 0, window.location.href.lastIndexOf("#") );
        window.location.href = Url;
        return false;
    }
    
    return true;
}

// -----------------------------------------------------------------------------

function reloadUser()
{
    $.ajax({
        type: "GET",
        url: "lib/script/_session.js.php?version=" + gSiteVersion,
        dataType: "script",
        success: checkUser,
        cache: false
    });
}

// -----------------------------------------------------------------------------

function confirm( aText, aYes, aNo, aCallback )
{
    $("#dialog").empty().append( aText );
    
    var DialogWidget = $("#dialog").dialog({
        resizable: false,
        draggable: false,
        width: 400,
        title: L("Notification"),
        dialogClass: "dialogFix"
    });

    DialogWidget.dialog( "option", "buttons", [{
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

    DialogWidget.dialog({
           close: function() { $("#eventblocker").hide(); }
    });

    $(".ui-dialog-buttonset > *")
        .css("font-size", 11)
        .last().focus();
    
    $("#eventblocker").show();
}

// -----------------------------------------------------------------------------

function notify( aText )
{
    $("#dialog").empty().append( aText );
    
    var DialogWidget = $("#dialog").dialog({
        resizable: false,
        draggable: false,
        title: L("Notification"),
        width: 400,
        dialogClass: "dialogFix"
    });

    DialogWidget.dialog( "option", "buttons", [{
        text: "Ok",
        click: function() {
            $( this ).dialog( "close" );
        }
    }]);

    DialogWidget.dialog({
           close: function() { $("#eventblocker").hide(); }
    });

    $(".ui-dialog-buttonset > *")
        .css("font-size", 11)
        .last().focus();
    
    $("#eventblocker").show();
}

// -----------------------------------------------------------------------------

function prompt( aText, aOk, aCancel, aCallback )
{
    var HTMLString = "<br/><input id=\"prompt_text\" class=\"prompt_text\"/>";

    $("#dialog").empty()
        .append( aText )
        .append( HTMLString );

    var DialogWidget = $("#dialog").dialog({
        resizable: false,
        draggable: false,
        width: 400,
        title: L("InputRequired"),
        dialogClass: "dialogFix"
    });

    DialogWidget.dialog( "option", "buttons", [{
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

    DialogWidget.dialog({
           close: function() { $("#eventblocker").hide(); }
    });

    $(".ui-dialog-buttonset > *")
        .css("font-size", 11)
        .last().focus();
        
    $("#eventblocker").show();
}

// -----------------------------------------------------------------------------

function onResize()
{
    hideTooltip();
    
    var AppHeight = $("#appwindow").height();
    var AppOffset = $("#appwindow").offset().top;
    var DocHeight = $(window).height();
    
    if (DocHeight < AppHeight)
    {
        // scroll into view only of not working on an input
        // element. This fixes iOS password field scrolling out of view
        
        if ( $("input:focus").length === 0 )
        {
            var scrollMenuToView = $("#menu").offset().top - AppOffset;        
            var scrollBottomToView = AppHeight - DocHeight;
            
            $("#appwindow").offset({top: 0});
            $(window).scrollTop(Math.min(scrollBottomToView, scrollMenuToView) );
        }
    }
    else
    {
        // center the Appwindow vertically
        // could be done via css, but we need to cover the above fallback, too.
            
        $("#appwindow").offset({top: DocHeight/2 - AppHeight/2});
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

    $(document).ajaxSend( function() {
        $("#ajaxblocker").clearQueue().hide();
            
        if ( $("#body *").size() == 0)
        {
            $("#ajaxblocker").delay(300).queue(function() {
                $("#body").append("<img id=\"spinner\" style=\"position: relative; top: 50%; margin-top: -32px\" src=\"lib/layout/images/busy.gif\"/>");
                $(this).dequeue();
            });
        }
        else if ( $("#spinner").size() == 0 )
        {
            $("#ajaxblocker").delay(800).show(200);
        }
    });

    $(document).ajaxComplete( onAsyncDone );
    $(document).ajaxError( onAsyncError );

    $(".button_logout").button({
        icons: { secondary: "ui-icon-eject" }
    }).css( "font-size", 11 );
    
    $(".button_help").button({
        icons: { secondary: "ui-icon-help" }
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