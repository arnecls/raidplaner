<?php

function xmlSpecialChar( $character )
{
    $utf8 = mb_convert_encoding($character, "UTF-8");
    $char = mb_convert_encoding($utf8, "UCS-4BE", "UTF-8");
    
    $val = unpack("N",$char);
    
    return "&#".$val[1].";";
}

function xmlentities( $string, $compat, $charset )
{
    $validString = htmlentities($string, $compat, $charset);
    
    // if the given charset did not work use fallback

    if ( $validString == "" )
        $validString = htmlentities( $string, $compat | ENT_IGNORE, "ISO8859-15" );

    $htmlTranslationTable = get_html_translation_table( HTML_ENTITIES, $compat );

    $translationTable = array();

    $translationTable["@"] = xmlSpecialChar("@");
    $translationTable["["] = xmlSpecialChar("[");
    $translationTable["]"] = xmlSpecialChar("]");
    $translationTable["'"] = xmlSpecialChar("'");

    while ( list($key,$value) = each($htmlTranslationTable) )
    {
         $translationTable[$value] = xmlSpecialChar($key);
    }
    
    $translated = strtr( $validString, $translationTable );

    if ($translated === false)
        return $validString;

    return $translated;
}

function requestToXML( $string, $compat, $charset )
{
    return xmlentities( stripcslashes(urldecode($string)), $compat, $charset );
}

function LeadingZero10( $Value )
{
    if ($Value < 10)
        return "0".$Value;

    return $Value;
}

?>