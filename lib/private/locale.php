<?php
    require_once(dirname(__FILE__)."/tools_string.php");
    require_once(dirname(__FILE__)."/out.class.php");

    $gLocale = array();

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

    // -------------------------------------------------------------------------

    function L( $aKey )
    {
        global $gLocale;

        if ( !isset($gLocale[$aKey]) || ($gLocale[$aKey] == null) )
            return "LOCA_MISSING_".$aKey;

        return $gLocale[$aKey];
    }

    // -------------------------------------------------------------------------

    function msgQueryLocale( $aRequest )
    {
        global $gLocale;
        $EncodedLocale = array();

        while ( list( $Key, $Value) = each($gLocale) )
        {
            if ($Value != null)
            {
                $Flags = (PHP_VERSION_ID >= 50400) ? ENT_COMPAT | ENT_XHTML : ENT_COMPAT;

                $Encoded = htmlentities($Value, $Flags, 'UTF-8');
                $EncodedLocale[$Key] = $Encoded;
            }
        }

        Out::getInstance()->pushValue("locale", $EncodedLocale);
    }
?>