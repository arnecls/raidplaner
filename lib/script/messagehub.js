function AsyncQuery(a_ActionName, a_Parameter, a_Callback)
{
    a_Parameter.Action = a_ActionName;
    
    $.ajax({
        type     : "GET",
		url      : "lib/messagehub.php",
		dataType : "xml",
		async    : true,
		data     : a_Parameter,
		success  : a_Callback
    });
}

function Query(_ActionName, a_Parameter)
{
    _Parameter.Action = a_ActionName;
    
    return $.ajax({
        type     : "POST",
		url      : "lib/messagehub.php",
		dataType : "xml",
		async    : false,
		data     : a_Parameter,
		success  : null
    });
}