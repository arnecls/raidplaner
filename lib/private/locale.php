<?php
    require_once(dirname(__FILE__)."/tools_string.php");
    require_once(dirname(__FILE__)."/out.class.php");

    // -------------------------------------------------------------------------

    function getLocaleName()
    {
        if ( isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]) )
        {
            $LanguageString = strtolower( substr($_SERVER["HTTP_ACCEPT_LANGUAGE"], 0, 2) );
            
            if ( file_exists(dirname(__FILE__)."/locale/".$LanguageString.".php") )
                return $LanguageString;
        }
        
        return "en";
    }
    
    $gLocale = array();
    include_once( dirname(__FILE__)."/locale/".getLocaleName().".php" );
    
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
        global $gGame;
        
        loadGameSettings();
        $EncodedLocale = Array();
        $LocaleName = getLocaleName();
        
        $Flags = (PHP_VERSION_ID >= 50400) ? ENT_COMPAT | ENT_XHTML : ENT_COMPAT;
        $Flags = (PHP_VERSION_ID >= 50300) ? $Flags | ENT_IGNORE : $Flags;
        
        // Hardcoded strings
                
        foreach ( $gLocale as $Key => $Value )
        {
            if ($Value != null)
            {
                $Encoded = htmlentities(getUTF8($Value), $Flags, 'UTF-8');
                $EncodedLocale[$Key] = $Encoded;
            }
        }
        
        // Game based strings
        
        if (isset($gGame["Locales"][$LocaleName]))
        {
            foreach($gGame["Locales"][$LocaleName] as $Key => $Value)
            {
                if ($Value != null)
                {
                    $Encoded = htmlentities(getUTF8($Value), $Flags, 'UTF-8');
                    $EncodedLocale[$Key] = $Encoded;
                }
            }
        }

        Out::getInstance()->pushValue("locale", $EncodedLocale);
    }
?>