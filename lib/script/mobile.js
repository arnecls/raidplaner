var g_TouchStartTimeMs = 0;

function touchConvert( a_Event )
{
    a_Event.preventDefault();
    
    var type = null;
    var touch = a_Event.changedTouches[0];
    var currentTimeMs = new Date().getTime();
    var newEvent = document.createEvent("MouseEvent");
    var triggerClick = false;
    
    switch ( a_Event.type )
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
    	event.target.ownerDocument.defaultView, 0,  
    	touch.screenX, touch.screenY, 
    	touch.clientX, touch.clientY,  
    	a_Event.ctrlKey, a_Event.altKey, a_Event.shirtKey, a_Event.metaKey, 
    	0, null);
    	
	a_Event.target.dispatchEvent( newEvent );
	
	if ( triggerClick )
	{
		var clickEvent = document.createEvent("MouseEvent");
		
		clickEvent.initMouseEvent( "click", true, true, 
	    	event.target.ownerDocument.defaultView, 0,  
	    	touch.screenX, touch.screenY, 
	    	touch.clientX, touch.clientY,  
	    	a_Event.ctrlKey, a_Event.altKey, a_Event.shirtKey, a_Event.metaKey, 
	    	0, null);
    	
		a_Event.target.dispatchEvent( clickEvent );
	}
}

// -----------------------------------------------------------------------------

function makeTouchable( a_Node ) 
{
	a_Node.context.addEventListener("touchstart", touchConvert, true);
    a_Node.context.addEventListener("touchmove", touchConvert, true);
    a_Node.context.addEventListener("touchend", touchConvert, true);   
}