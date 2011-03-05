<?php
	header("Content-type: text/javascript");
	require_once(dirname(__FILE__)."/../private/locale.php");
?>

var g_Locale = new Array();

<?php
	while ( list( $key, $value) = each($g_Locale) )
	{
		if ($value != null)
			echo "g_Locale[\"".$key."\"] = \"".$value."\";\n";
	}
?>

var L = function( a_Key ) {
	if ( g_Locale[a_Key] == null )
		return a_Key;		
	return g_Locale[a_Key]; 
};