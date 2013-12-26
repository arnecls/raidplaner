<?php
    header("Content-type: text/javascript");
    define( "LOCALE_SETUP", true );
    require_once(dirname(__FILE__)."/../../lib/private/locale.php");
?>

var gLocale = {
    <?php
        $first = true;
        while (list($Key, $Value) = each($gLocale))
        {
            if ($first)

                $first = false;
            else

                echo ",\n    ";

            $Flags = (PHP_VERSION_ID >= 50400) ? ENT_COMPAT | ENT_XHTML : ENT_COMPAT;
            echo $Key." : \"".htmlentities($Value, $Flags, 'UTF-8')."\"";

        }

    ?>

};

// -----------------------------------------------------------------------------

function L( aKey )

{
    if ( gLocale[aKey] == null )
        return "LOCA_MISSING_" + aKey;

    return gLocale[aKey];
}