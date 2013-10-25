function asyncQuery(aActionName, aParameter, aCallback)
{
    aParameter.Action = aActionName;

    $.ajax({
        type     : "POST",
        url      : "lib/messagehub.php",
        dataType : "json",
        async    : true,
        data     : aParameter,
        complete : onAsyncDone,
        success  : aCallback,
        error    : onAsyncError
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

function onAsyncDone( aXHR )
{
    $("#ajaxblocker").clearQueue().hide();
    
    var Errors = getXHRErrors(aXHR.responseJSON);    
    if ( Errors != null )
        notify( L("RequestError") + "<br/><br/>" + Errors );
}

// -----------------------------------------------------------------------------

function onAsyncError( aXHR, aStatus, aError )
{
    $("#ajaxblocker").clearQueue().hide();

    var Message = L("RequestError") + "<br/><br/>Status " + aStatus + "<br/>" + aError;   
    notify( Message );
}