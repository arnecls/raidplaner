<?php
    header("Content-type: text/javascript");
    define( "LOCALE_SETUP", true );
    require_once(dirname(__FILE__)."/../../lib/private/locale.php");
?>

function open( a_Page )
{
    var url = window.location.href.substring( 0, window.location.href.lastIndexOf("/") );
    window.location.href = url + "/" + a_Page;
}