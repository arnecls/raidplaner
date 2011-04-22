<?php

function uniord($c) 
{
    $h = ord($c{0});
    
    if ($h <= 0x7F) 
    {
        return $h;
    }
     
    if ($h < 0xC2) 
    {
        return false;
    } 
    
    if ($h <= 0xDF) 
    {
        return ($h & 0x1F) << 6 | (ord($c{1}) & 0x3F);
    } 
    
    if ($h <= 0xEF) 
    {
        return ($h & 0x0F) << 12 | (ord($c{1}) & 0x3F) << 6
                                 | (ord($c{2}) & 0x3F);
    } 
    
    if ($h <= 0xF4) 
    {
        return ($h & 0x0F) << 18 | (ord($c{1}) & 0x3F) << 12
                                 | (ord($c{2}) & 0x3F) << 6
                                 | (ord($c{3}) & 0x3F);
    } 
    
    return false;
}

function xmlSpecialChar( $character )
{
	$utf8Key = mb_convert_encoding( $character, "UTF-8" );	 	
	return "&#".uniord($utf8Key).";"; 
}

function xmlentities( $string, $compat, $charset ) 
{
	$string = htmlentities( $string, $compat, $charset );
	$htmlTranslationTable = get_html_translation_table( HTML_ENTITIES, $compat );
 	
 	$translationTable = array();
 	
 	$translationTable["@"] = xmlSpecialChar("@");
 	$translationTable["["] = xmlSpecialChar("[");
 	$translationTable["]"] = xmlSpecialChar("]");
 	$translationTable["'"] = xmlSpecialChar("'");
 	
	foreach ( $htmlTranslationTable as $key => $value)
	{ 
	 	$translationTable[ $value ] = xmlSpecialChar($key);
	}
	
	$translated = strtr( $string, $translationTable );
	
	if ($translated === false)
		return $string;

	return $translated;
}

function requestToXML( $string, $compat, $charset )
{
	return xmlentities( stripcslashes( urldecode( $string ) ), $compat, $charset );
}

?>