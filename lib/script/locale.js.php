<?php
    if (!defined("UNIFIED_SCRIPT")) 
    {
        header("Content-type: text/javascript");
        header("Cache-Control: public");
    }
    
    define( "LOCALE_MAIN", true );
    require_once(dirname(__FILE__)."/../private/locale.php");
?>

var g_Locale = [];

<?php
    while ( list( $Key, $Value) = each($gLocale) )
    {
        if ($Value != null)
        {
            $Flags = (PHP_VERSION_ID >= 50400) ? ENT_COMPAT | ENT_XHTML : ENT_COMPAT;
            $Encoded = htmlentities(str_replace("\"","\\\"", $Value), $Flags, 'UTF-8');
            echo "g_Locale[\"".$Key."\"] = \"".$Encoded."\";\n";
        }
    }
?>

function L( a_Key ) {
    if ( g_Locale[a_Key] == null )
        return "LOCA_MISSING_"+a_Key;

    return g_Locale[a_Key];
}