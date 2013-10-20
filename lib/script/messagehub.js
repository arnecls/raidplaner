function asyncQuery(aActionName, aParameter, aCallback)
{
    aParameter.Action = aActionName;

    $.ajax({
        type     : "POST",
        url      : "lib/messagehub.php",
        dataType : "json",
        async    : true,
        data     : aParameter,
        success  : aCallback
    });
}

// -----------------------------------------------------------------------------

function getXHRErrors( aXHR )
{
    if ( (aXHR.error != null) && (aXHR.error.length > 0) )
    {
        var Message = "";
        $.each(aXHR.error, function(index, value) { Message += value; });
        return Message;
    }
    
    return null;
}

// -----------------------------------------------------------------------------

function onAsyncDone( aEvent, aXHR, aOptions )
{
    $("#ajaxblocker").clearQueue().hide();
    
    var Errors = getXHRErrors(aXHR);    
    if ( Errors != null )
        notify( L("RequestError") + "<br/><br/>" + Errors );
}

// -----------------------------------------------------------------------------

function onAsyncError( aEvent, aXHR, aOptions, aError )
{
    $("#ajaxblocker").clearQueue().hide();

    var Message = L("RequestError") + "<br/><br/>" + aError;
    var Errors = getXHRErrors(aXHR);
    
    if ( Errors != null )
        Message += Errors;

    notify( Message );
}