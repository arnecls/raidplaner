var gUnappliedChanges = false;
var gUser = null;
var gConfig = null;
var gSite = null;
var gLocale = null;

// -----------------------------------------------------------------------------

function L( aKey )

{
    if ( (gLocale == null) || (gLocale[aKey] == null) )
        return "LOCA_MISSING_" + aKey;

    return gLocale[aKey];
}

// -----------------------------------------------------------------------------

function dispatch(aFunction)
{
    setTimeout(function() { setTimeout(aFunction, 1); }, 1);
}

// -----------------------------------------------------------------------------

function tryAutoLogin()
{
    blockingQuery("try_auto_login", null, function(aXHR) {});
}

// -----------------------------------------------------------------------------

function reloadLocale()
{
    blockingQuery("query_locale", null, function(aXHR) {
        gLocale = aXHR.locale;
    });
}

// -----------------------------------------------------------------------------

function reloadUser()
{
    gUser = null;
    
    blockingQuery("query_user", null, function(aXHR) {
        if (aXHR.registeredUser)
        {
            gUser = aXHR;
        }
        else
        {
            initMenu();
        }
    });
}

// -----------------------------------------------------------------------------

function reloadConfig()
{
    blockingQuery("query_config", null, function(aXHR) {
        gConfig = aXHR.config;
        gSite   = aXHR.site;

        onChangeConfig();
    });
}

// -----------------------------------------------------------------------------

function hash_fnv( aValue )
{
	var init = 0x811c9dc5;
	var hval = init;
	var str = aValue.toString();
	
	for ( var i = 0; i < str.length; ++i )
	{
		hval ^= str.charCodeAt(i);
		hval += (hval << 1) + (hval << 4) + (hval << 7) + (hval << 8) + (hval << 24);
	}
	
	return hval >>> 0;
}

// -----------------------------------------------------------------------------

function hash_object( aObject )
{
    var stringified = "";
    for (var property in aObject)
    {
        stringified += aObject[property].toString();
    }
    
    return hash_fnv( stringified );
}

// -----------------------------------------------------------------------------

function onChangeConfig()
{
    // Reload CSS
    
    var configHash = hash_object(gSite);
    
    $('link[rel="stylesheet"]').each(function () {
        var optionsIdx = this.href.indexOf("?");
        if (optionsIdx > -1)
        {
            var base = this.href.substr(0, optionsIdx);
            var options = this.href.substr(optionsIdx+1).split("&");
            var refString = base + "?v=" + configHash;
            
            for (var i=0; i<options.length; ++i)
            {
                var paramName = options[i].split("=")[0];
                if (paramName != "v")
                    refString += "&" + options[i];
            }
            
            this.href = refString;
        }
        else
        {
            this.href = this.href + "?v=" + configHash;
        }
    });
    
    // Update logo

    $("#banner").detach();

    if ( gSite.Banner.toLowerCase() != "disable"  )
    {
        if ( gSite.BannerLink !== "" )
            $("#menu").before("<a id=\"banner\" href=\"" + gSite.BannerLink + "\"></a>");
        else
            $("#menu").before("<div id=\"banner\"></div>");

        var bannerImage = (gSite.Banner.toLowerCase() != "none")
            ? "url(themes/banner/" + gSite.Banner + ")"
            : "none";

        $("#banner").css("background-image", bannerImage);
    }

    // Update help button

    if ( gSite.HelpLink != "" )
        $("#help").show();
    else
        $("#help").hide();
    
    // Update logout button
    
    if ( gSite.Logout )
        $("#logout").css("display","inline-block");
    else
        $("#logout").hide();
    
    // Update appwindow class

    $("#appwindow").removeClass("portalmode");
    if (gSite.PortalMode)
        $("#appwindow").addClass("portalmode");

    // Update theme

    if ( gSite.Background == "none" )
        $("body").css("background", "none" );
    else
        $("body").css("background", gSite.BGColor + " url(themes/backgrounds/" + gSite.Background + ") " + gSite.BGRepeat );
}

// -----------------------------------------------------------------------------

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

function forceChangeContext( aName )
{
    if (!changeContext(aName))
        menuAutoLoad();
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
        .last().focus();

    $("#eventblocker").show();
}

// -----------------------------------------------------------------------------

function openLink(aSite)
{
    window.open(aSite);
    return false;
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

$(document).ready( function() {

    $("#tooltip").hide();
    $("#sheetoverlay").hide();
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

    $(window).bind('hashchange', menuAutoLoad );
    $(window).resize( onResize );

    reloadLocale();
    reloadConfig();
    tryAutoLogin();
    reloadUser();
    initMenu();
    
    $("#closesheet").click( closeSheet );
    $("#body, #banner, #menu").click( startFadeTooltip );

    // IE needs some time to layout the content
    dispatch(onResize);
});
