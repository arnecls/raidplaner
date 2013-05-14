var g_TouchStartTimeMs = 0;

function touchConvert( aEvent )
{
    aEvent.preventDefault();

    var type = null;
    var touch = aEvent.changedTouches[0];
    var currentTimeMs = new Date().getTime();
    var newEvent = document.createEvent("MouseEvent");
    var triggerClick = false;

    switch ( aEvent.type )
    {
    case "touchstart":
        g_TouchStartTimeMs = currentTimeMs;
        type = "mousedown";
        break;

    case "touchmove":
        type = "mousemove";
        break;

    case "touchcancel":
    case "touchend":
        triggerClick = ( currentTimeMs - g_TouchStartTimeMs < 500 );
        type = "mouseup";
        break;

    default:
        return;
    }

    newEvent.initMouseEvent( type, true, true,
        aEvent.target.ownerDocument.defaultView, 0,
        touch.screenX, touch.screenY,
        touch.clientX, touch.clientY,
        aEvent.ctrlKey, aEvent.altKey, aEvent.shirtKey, aEvent.metaKey,
        0, null);

    aEvent.target.dispatchEvent( newEvent );

    if ( triggerClick )
    {
        var clickEvent = document.createEvent("MouseEvent");

        clickEvent.initMouseEvent( "click", true, true,
            aEvent.target.ownerDocument.defaultView, 0,
            touch.screenX, touch.screenY,
            touch.clientX, touch.clientY,
            aEvent.ctrlKey, aEvent.altKey, aEvent.shirtKey, aEvent.metaKey,
            0, null);

        aEvent.target.dispatchEvent( clickEvent );
    }
}

// -----------------------------------------------------------------------------

function makeTouchable( aNode )
{
    if ( aNode.context.addEventListener )
    {
        aNode.context.addEventListener( "touchstart", touchConvert, true );
        aNode.context.addEventListener( "touchmove", touchConvert, true );
        aNode.context.addEventListener( "touchend", touchConvert, true );
    }
    else if ( aNode.context.attachEvent )
    {
        aNode.context.attachEvent( "touchstart", touchConvert );
        aNode.context.attachEvent( "touchmove", touchConvert );
        aNode.context.attachEvent( "touchend", touchConvert );
    }
}

// -----------------------------------------------------------------------------

function onTouch( aNode, aFunction )
{
    if ( aNode.context.addEventListener )
    {
        aNode.context.addEventListener( "touchend", aFunction, true );
    }
    else if ( aNode.context.attachEvent )
    {
        aNode.context.attachEvent( "touchend", aFunction );
    }
}