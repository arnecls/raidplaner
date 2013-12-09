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
            
            echo $Key." : \"".htmlentities($Value, ENT_COMPAT | ENT_HTML401, 'UTF-8')."\"";   
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