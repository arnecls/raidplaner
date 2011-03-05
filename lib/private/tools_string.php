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

function xmlentities( $string, $compat, $charset ) 
{
	$string = htmlentities( $string, $compat );
	$htmlTranslationTable = get_html_translation_table( HTML_ENTITIES, $compat );
 	
 	$translationTable = array();
 	
	foreach ( $htmlTranslationTable as $key => $value)
	{ 
	 	$utf8Key = mb_convert_encoding($key, "UTF-8");	 	
		$translationTable[ $value ] = "&#".uniord($utf8Key).";"; 
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