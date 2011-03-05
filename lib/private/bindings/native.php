<?php
	function BindNativeUser( $_User )
	{
		$password = $_User["Password"];
		
		if ( isset($_User["cleartext"]) && $_User["cleartext"] )
		{
			$password = sha1( $password );
		}
			
		return UserProxy::TryLoginUser( $_User["Login"], $password, "none" );
	}
?>