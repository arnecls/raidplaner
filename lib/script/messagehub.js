function asyncQuery(a_ActionName, a_Parameter, a_Callback)
{
    a_Parameter.Action = a_ActionName;

    $.ajax({
        type     : "POST",
        url      : "lib/messagehub.php",
        dataType : "xml",
        async    : true,
        data     : a_Parameter,
        success  : a_Callback
    });
}

// -----------------------------------------------------------------------------

function onAsyncDone( a_Event, a_XHR, a_Options )
{
    $("#ajaxblocker").clearQueue().hide();
    var errors = $(a_XHR.responseXML).children("messagehub").children("error");

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

function onAsyncError( a_Event, a_XHR, a_Options, a_Error )
{
    $("#ajaxblocker").clearQueue().hide();

    var errors = $(a_XHR.responseXML).children("messagehub").children("error");

    var message = L("RequestError") + "<br/><br/>";
    message    += a_Error + "<br/>";

    if ( errors.size() > 0 )
    {
        errors.each( function( index, element ) {
            message += "<br/>"+element.text();
        });
    }

    notify( message );
}