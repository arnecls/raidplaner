function open( a_Page )
{
    var url = window.location.href.substring( 0, window.location.href.lastIndexOf("/") );
    window.location.href = url + "/" + a_Page;
}