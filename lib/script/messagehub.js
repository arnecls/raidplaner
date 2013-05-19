function asyncQuery(aActionName, aParameter, aCallback)
{
    aParameter.Action = aActionName;

    $.ajax({
        type     : "POST",
        url      : "lib/messagehub.php",
        dataType : "xml",
        async    : true,
        data     : aParameter,
        success  : aCallback
    });
}

// -----------------------------------------------------------------------------

function onAsyncDone( aEvent, aXHR, aOptions )
{
    $("#ajaxblocker").clearQueue().hide();
    var Errors = $(aXHR.responseXML).children("messagehub").children("error");

    if ( Errors.size() > 0 )
    {
        var Message = L("RequestError") + "<br/>";
        var buildError = function() {
            Message += "<br/><br/>" + $(this).text();
        };
        
        Errors.each(buildError);


        notify( Message );
    }
}

// -----------------------------------------------------------------------------

function onAsyncError( aEvent, aXHR, aOptions, aError )
{
    $("#ajaxblocker").clearQueue().hide();

    var Errors = $(aXHR.responseXML).children("messagehub").children("error");

    var Message = L("RequestError") + "<br/><br/>";
    Message += aError + "<br/>";

    if ( Errors.size() > 0 )
    {
        var buildError = function( aIndex, aElement ) {
            Message += "<br/>"+aElement.text();
        };
        
        Errors.each(buildError);
    }

    notify( Message );
}