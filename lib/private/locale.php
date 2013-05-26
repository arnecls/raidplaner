<?php
    $gLocale = Array();

    if ( !isset( $_SERVER["HTTP_ACCEPT_LANGUAGE"] ) )
    {
        global $gLocale;
        include_once( dirname(__FILE__)."/locale/en.php" );
    }
    else
    {
        $LanguageString = strtolower( substr( $_SERVER["HTTP_ACCEPT_LANGUAGE"], 0, 2 ) );

        if ( file_exists( dirname(__FILE__)."/locale/".$LanguageString.".php" ) )
        {
            global $gLocale;
            include_once( dirname(__FILE__)."/locale/".$LanguageString.".php" );
        }
        else
        {
            global $gLocale;
            include_once( dirname(__FILE__)."/locale/en.php" );
        }
    }

    function L( $aKey )
    {
        global $gLocale;

        if ( !isset($gLocale[$aKey]) || ($gLocale[$aKey] == null) )
            return "LOCA_MISSING_".$aKey;

        return $gLocale[$aKey];
    }
?>