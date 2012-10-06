function loadSetupDb()
{
    var url = window.location.href.substring( 0, window.location.href.lastIndexOf("/") );
    window.location.href = url + "/setup_db.php";
}

function loadBindings()
{
    var url = window.location.href.substring( 0, window.location.href.lastIndexOf("/") );
    window.location.href = url + "/setup_bindings.php";
}

function loadCleanup()
{
    var url = window.location.href.substring( 0, window.location.href.lastIndexOf("/") );
    window.location.href = url + "/done.php";
}