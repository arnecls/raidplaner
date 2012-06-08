<?php
	$g_Locale = Array();

	if ( !isset( $_SERVER["HTTP_ACCEPT_LANGUAGE"] ) )
	{
		global $g_Locale;
		include_once( dirname(__FILE__)."/locale/en.php" );
	}
	else
	{
		$languageString = strtolower( substr( $_SERVER["HTTP_ACCEPT_LANGUAGE"], 0, 2 ) );
		
		if ( file_exists( dirname(__FILE__)."/locale/".$languageString.".php" ) )
		{
			global $g_Locale;
			include_once( dirname(__FILE__)."/locale/".$languageString.".php" );
		}
		else
		{
			global $g_Locale;
			include_once( dirname(__FILE__)."/locale/en.php" );
		}
	}

	function L( $a_Key )
	{
		global $g_Locale;
		
		if ( !isset($g_Locale[$a_Key]) || ($g_Locale[$a_Key] == null) )
			return "LOCA_MISSING_".$a_Key;
			
		return $g_Locale[$a_Key];
	}
?>