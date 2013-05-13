<?php
    if (!defined("UNIFIED_SCRIPT")) header("Content-type: text/javascript");

    define( "LOCALE_MAIN", true );
    require_once(dirname(__FILE__)."/../private/locale.php");
?>

var g_Locale = new Array();

<?php
    while ( list( $Key, $Value) = each($gLocale) )
    {
        if ($Value != null)
            echo "g_Locale[\"".$Key."\"] = \"".str_replace("\"","\\\"", $Value)."\";\n";
    }
?>

function L( a_Key ) {
    if ( g_Locale[a_Key] == null )
        return "LOCA_MISSING_"+a_Key;

    return g_Locale[a_Key];
};