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
    var errors = $(aXHR.responseXML).children("messagehub").children("error");

    if ( errors.size() > 0 )
    {
        var message = L("RequestError") + "<br/>";

        errors.each( function() {
            message += "<br/><br/>" + $(this).text();
        });


        notify( message );
    }
}

// -----------------------------------------------------------------------------

function onAsyncError( aEvent, aXHR, aOptions, aError )
{
    $("#ajaxblocker").clearQueue().hide();

    var errors = $(aXHR.responseXML).children("messagehub").children("error");

    var message = L("RequestError") + "<br/><br/>";
    message    += aError + "<br/>";

    if ( errors.size() > 0 )
    {
        errors.each( function( index, element ) {
            message += "<br/>"+element.text();
        });
    }

    notify( message );
}