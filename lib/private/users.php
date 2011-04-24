<?php
    require_once dirname(__FILE__)."/connector.class.php";
    require_once dirname(__FILE__)."/../config/config.php";
    require_once dirname(__FILE__)."/bindings/native.php";
    require_once dirname(__FILE__)."/bindings/phpbb3.php";
    
    class UserProxy
	{
		private static $Instance = null;
		private static $StickyLifeTime = 2419200; // 60 * 60 * 24 * 7 * 4; // 1 month
		private static $StickyCookieName = "ppx_rp_sticky";
		
		private static $Bindings = array(
			"Default" => array( "Function" => "BindNativeUser", "Available" => true ),
			"PHPBB3"  => array( "Function" => "BindPHPBB3User", "Available" => PHPBB3_BINDING )
		);
		
		private static $Hash = null;
		private static $CryptName = "rijndael-256";
		
		// --------------------------------------------------------------------------------------------
	
		public function __construct()
		{
			assert(self::$Instance == NULL);
			
		    session_name("ppx_raidplaner");
		    
		    ini_set("session.cookie_httponly", true);
		    ini_set("session.hash_function", 1);
		    
		    session_start();
		    
		    if (isset($_REQUEST["logout"]))
            {            	
                // explicit unlog
                unset($_SESSION["User"]);
                unset($_SESSION["Calendar"]);
                
                unset($_COOKIE[self::$StickyCookieName]);
                setcookie( self::$StickyCookieName, null, 0, "", "", false, true );
            }
            
            if (isset($_SESSION["User"]) && isset($_SESSION["User"]["UserId"]))
            {
            	// Check if session matches database
                if ($this->CheckSessionCRC()) 
                {
                	$this->UpdateCharacters();
                	return; // ### valid
                }                
            }
            else 
            {
            	$LoginUser = null;
            	
            	if (isset($_REQUEST["user"]) && isset($_REQUEST["pass"]))
                {
                	$LoginUser = array( "Login"     => $_REQUEST["user"],
                    	                "Password"  => $_REQUEST["pass"],
                    	                "cleartext" => true );
                }
                else if ( isset($_COOKIE[self::$StickyCookieName]) )
               	{
               		// Reconstruct login data from cookie + database hash
               		
               		$Connector = Connector::GetInstance();
                        
               		$CookieData = unserialize( base64_decode($_COOKIE[self::$StickyCookieName]) );
               		
               		$UserSt = $Connector->prepare( "SELECT Hash FROM `".RP_TABLE_PREFIX."User` WHERE UserId = :UserId" );
               		
               		$UserSt->bindValue(":UserId", $CookieData["ID"], PDO::PARAM_INT );
               		$UserSt->execute();
               		
               		if ($UserSt->rowcount() > 0)
               		{
	               		$UserData = $UserSt->fetch( PDO::FETCH_ASSOC );
	               		
	               		$CryptDesc = mcrypt_module_open( UserProxy::$CryptName, "", "ecb", "" );
	        			$IV        = mcrypt_create_iv( mcrypt_enc_get_iv_size($CryptDesc), MCRYPT_RAND );
	        			
	        			mcrypt_generic_init( $CryptDesc, $UserData["Hash"], $IV );
	        			
	        			$LoginData = unserialize( mdecrypt_generic($CryptDesc, base64_decode($CookieData["Data"]) ) );
	        			
	        			mcrypt_generic_deinit($CryptDesc);
						mcrypt_module_close($CryptDesc);
						
						$LoginUser = array( "Login"     => $LoginData["Login"], 
	                    	                "Password"  => $LoginData["Password"],
	                        	            "cleartext" => false );
					}
					
					$UserSt->closeCursor();
                }
                
                if ( $LoginUser != null )
                {
	                foreach ( self::$Bindings as $Binding )
	                {
	                	if ( $Binding["Available"] && call_user_func($Binding["Function"], $LoginUser) )
	                	{
	                		// Login worked
	                		
	                		if ( isset($_REQUEST["sticky"]) && ($_REQUEST["sticky"] == "true") )
	                		{
	                			// Sticky login is requested
	                			
	                			$Data = array( 	"Login"    => $LoginUser["Login"], 
	                							"Password" => $_SESSION["User"]["Password"] );
	                			
	                			$CryptDesc = mcrypt_module_open( UserProxy::$CryptName, "", "ecb", "" );
	                			$IV        = mcrypt_create_iv( mcrypt_enc_get_iv_size($CryptDesc), MCRYPT_RAND );
	                			
	                			mcrypt_generic_init( $CryptDesc, self::$Hash, $IV );
	                			
	                			$EncryptedData = mcrypt_generic($CryptDesc, serialize($Data));
	                			
	                			mcrypt_generic_deinit($CryptDesc);
    							mcrypt_module_close($CryptDesc);
	                			
	                			$CookieData["ID"]   = $_SESSION["User"]["UserId"];	                			
	                			$CookieData["Data"] = base64_encode( $EncryptedData );
	                			                			
			            		setcookie( self::$StickyCookieName, base64_encode( serialize($CookieData) ), time() + self::$StickyLifeTime, "", "", false, true );
			            	}
			            	else if ( !isset($_COOKIE[self::$StickyCookieName]) )
			            	{
			            		setcookie( self::$StickyCookieName, null, 0, "", "", false, true );
			            	}
			            	
			            	self::$Hash = null; // do not store hash
			            	
	                		return;
	                	}
	                }
	            }
            }
            
            // All checks failed -> logout
            unset($_SESSION["User"]);
            unset($_SESSION["Calendar"]);
        }
		
		// --------------------------------------------------------------------------------------------
		
		private function CheckSessionCRC()
		{
			if (isset($_SESSION["User"]))
            {
            	$Connector = Connector::GetInstance();
                $UserSt = $Connector->prepare("SELECT * FROM `".RP_TABLE_PREFIX."User` ".
                                              "WHERE UserId = :UserId LIMIT 1");
                
                $UserSt->bindValue(":UserId", $_SESSION["User"]["UserId"], PDO::PARAM_INT);
                $UserSt->execute();
                
                if ( $UserSt->rowCount() > 0 )
                {                
                	$UserDataFromDb = $UserSt->fetch( PDO::FETCH_ASSOC );
                
	                while ( $item = current( $UserDataFromDb ) )
	                {
	                	$key = key( $UserDataFromDb );
	                	
	                	if ( $key != "Hash" )
	                	{
		                	if ( !isset( $_SESSION["User"][ $key ] ) )
		                	{
		                		return false;
		                	}
		                	
		                	if ( crc32($_SESSION["User"][ $key ]) != crc32($item) ) 
		                	{
		                		return false;
		                	}
		                }
	                	
	                	next( $UserDataFromDb );
	                }	                
	                
	                return true;
	        	}
	        	
	        	$UserSt->closeCursor();
            }            
            
            return false;
		}
		
		// --------------------------------------------------------------------------------------------
		
		public static function GetInstance()
		{
			if (self::$Instance == NULL)
				self::$Instance = new UserProxy();
				
			return self::$Instance;
		}
		
		// --------------------------------------------------------------------------------------------
		
		public static function CreateUser( $Group, $ExternalUserId, $BindingName, $Login, $Password )
		{
			$Connector = Connector::GetInstance();
            $UserSt = $Connector->prepare("SELECT UserId FROM `".RP_TABLE_PREFIX."User` ".
                                          "WHERE Login = :Login LIMIT 1");
            
            $UserSt->bindValue(":Login", strtolower($Login), PDO::PARAM_STR);
            
            if ( $UserSt->execute() && ($UserSt->rowCount() == 0) )
            {
            	$Salt = rand(1,100) * time() + rand();
                self::$Hash = md5( strval($Salt) . sha1($Login.$Password) );
                	
            	$UserSt->closeCursor();
	            $UserSt = $Connector->prepare("INSERT INTO `".RP_TABLE_PREFIX."User` (".
	                                          "`Group`, ExternalId, ExternalBinding, Login, Password, Hash) ".
	                                          "VALUES ('".$Group."', :ExternalUserId, '".$BindingName."', :Login, :Password, :Hash)");
	                                          
	            $UserSt->bindValue(":ExternalUserId",   $ExternalUserId,    PDO::PARAM_INT);
	            $UserSt->bindValue(":Login",    		strtolower($Login), PDO::PARAM_STR);
	            $UserSt->bindValue(":Password", 		$Password,  		PDO::PARAM_STR);
	            $UserSt->bindValue(":Hash", 			self::$Hash, 		PDO::PARAM_STR);
	            
	            $UserSt->execute();
	            $UserSt->closeCursor();
	            
	            return $Connector->lastInsertId();
			}
			
			$UserSt->closeCursor();
            return false;
		}
		
		// --------------------------------------------------------------------------------------------
		
		public static function UpdatePasswordIfDifferent( $Password )
		{
			if ( $_SESSION["User"]["Password"] != $Password )
            {
				$Connector = Connector::GetInstance();
	            $UserSt = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."User` SET ".
	                                          "Password = :Pass ".
	                                          "WHERE UserId = :UserId");
	                                      
	            $UserSt->bindValue(":UserId", $_SESSION["User"]["UserId"],	PDO::PARAM_INT);
	            $UserSt->bindValue(":Pass",   $Password,					PDO::PARAM_STR);
	            $UserSt->execute();
	            $UserSt->closeCursor();
	            
	            $_SESSION["User"]["Password"] = $Password;
	        }
		}
		
		// --------------------------------------------------------------------------------------------
		
		private static function SetSessionVariables( $UserQuery )
		{
			$_SESSION["User"] = $UserQuery->fetch( PDO::FETCH_ASSOC );
        	
        	$_SESSION["User"]["Role1"] = array( $_SESSION["User"]["Role1"] );
            $_SESSION["User"]["Role2"] = array( $_SESSION["User"]["Role2"] );
        	$_SESSION["User"]["CharacterId"] = array( $_SESSION["User"]["CharacterId"] );
            $_SESSION["User"]["CharacterName"] = array( $_SESSION["User"]["CharacterName"] );
        	
        	while ( $row = $UserQuery->fetch( PDO::FETCH_ASSOC ) )
        	{
        		array_push( $_SESSION["User"]["Role1"], $row["Role1"] );
            	array_push( $_SESSION["User"]["Role2"], $row["Role2"] );
            	array_push( $_SESSION["User"]["CharacterId"], $row["CharacterId"] );
        		array_push( $_SESSION["User"]["CharacterName"], $row["CharacterName"] );
        	}
        	
        	$Hash = $_SESSION["User"]["Hash"];
        	unset( $_SESSION["User"]["Hash"] );
        	
        	return $Hash;
		}
		
		// --------------------------------------------------------------------------------------------
		
		
		public static function TryLoginUser( $Login, $Password, $BindingName )
		{
			$Connector = Connector::GetInstance();
			
			$UserSt = $Connector->prepare(	"SELECT ".RP_TABLE_PREFIX."User.*, ".RP_TABLE_PREFIX."Character.Name AS CharacterName, ".RP_TABLE_PREFIX."Character.Role1, ".RP_TABLE_PREFIX."Character.Role2, ".RP_TABLE_PREFIX."Character.CharacterId FROM `".RP_TABLE_PREFIX."User` ".
            								"LEFT JOIN `".RP_TABLE_PREFIX."Character` USING (UserId) ".
                   	                      	"WHERE Login = :Login AND Password = :Password AND ExternalBinding = '".$BindingName."' ".
                       	                  	"ORDER BY Mainchar, ".RP_TABLE_PREFIX."Character.Name" );
                                          
            $UserSt->bindValue(":Login",    strtolower($Login), PDO::PARAM_STR);
            $UserSt->bindValue(":Password", $Password, 			PDO::PARAM_STR);
            
            if (!$UserSt->execute() )
            {
            	$ErrorInfo = $UserSt->errorInfo();
				echo "<error>".L("Database error")."</error>";
        		echo "<error>".$ErrorInfo[0]."</error>";
       			echo "<error>".$ErrorInfo[2]."</error>";
            
            	die();
            }
            
            $Success = $UserSt->rowCount() > 0;
            
            if ( $Success )
            {
            	self::$Hash = UserProxy::SetSessionVariables( $UserSt );
            	        		
            	// Fallback for pre-0.9.1 databases
            	// Deprecated block. Remove with 1.0
            	
            	if ( self::$Hash == "" )
            	{
            		$Salt = rand(1,100) * time() + rand();
                	self::$Hash = md5( strval($Salt) . sha1($Login.$Password) );
                
            		$UpdateSt = $Connector->prepare( "UPDATE `".RP_TABLE_PREFIX."User` SET Hash = :Hash WHERE UserId = :UserId" );
            		
            		$UpdateSt->bindValue( ":UserId", $_SESSION["User"]["UserId"],	PDO::PARAM_INT );
            		$UpdateSt->bindValue( ":Hash",   self::$Hash, 					PDO::PARAM_STR );
            		
            		$UpdateSt->execute();
            		$UpdateSt->closeCursor();
            	}
            }
            
            $UserSt->closeCursor();
            
            return $Success;
		}
		
		// --------------------------------------------------------------------------------------------
		
		private function UpdateCharacters()
		{
			if ( isset($_SESSION["User"]) && ($_SESSION["User"]["Group"] != "none") )
        	{
            	$Connector = Connector::GetInstance();
	            $CharacterSt = $Connector->prepare(	"SELECT * FROM `".RP_TABLE_PREFIX."Character` ".
	            							  		"WHERE UserId = :UserId ".
	            							  		"ORDER BY Mainchar, Name" );
	            
	            $CharacterSt->bindValue(":UserId", $_SESSION["User"]["UserId"], PDO::PARAM_INT);
	            
	            if ( $CharacterSt->execute() )
	            {
	            	$_SESSION["User"]["Role1"] = array();
		           	$_SESSION["User"]["Role2"] = array();
		           	$_SESSION["User"]["CharacterId"] = array();
		           	$_SESSION["User"]["CharacterName"] = array();
		           	
		           	while ( $row = $CharacterSt->fetch( PDO::FETCH_ASSOC ) )
	            	{
	            		array_push( $_SESSION["User"]["Role1"], $row["Role1"] );
	                	array_push( $_SESSION["User"]["Role2"], $row["Role2"] );
	                	array_push( $_SESSION["User"]["CharacterId"], $row["CharacterId"] );
	            		array_push( $_SESSION["User"]["CharacterName"], $row["Name"] );
	            	}
	            }
	            
	            $CharacterSt->closeCursor();
        	}
		}
    }
 
	 // --------------------------------------------------------------------------------------------
    
    function RegisteredUser()
    {
    	UserProxy::GetInstance();        
        return isset($_SESSION["User"]);
    }
			
	// --------------------------------------------------------------------------------------------
    
    function ValidUser()
    {
    	UserProxy::GetInstance();
        
        if (isset($_SESSION["User"]))
        {
        	return ($_SESSION["User"]["Group"] != "none");
        }
        
        return false;
    }
    
    // --------------------------------------------------------------------------------------------
    
    function ValidRaidlead()
    {
        UserProxy::GetInstance();
        
        if (isset($_SESSION["User"]))
        {
            return (($_SESSION["User"]["Group"] == "raidlead") || 
                    ($_SESSION["User"]["Group"] == "admin"));
        }
        
        return false;
    }
    
    // --------------------------------------------------------------------------------------------
	
    function ValidAdmin()
    {
        UserProxy::GetInstance();
        
        if (isset($_SESSION["User"]))
        {
            return ($_SESSION["User"]["Group"] == "admin");
        }
        
        return false;
    }
    
    // --------------------------------------------------------------------------------------------
	
    function msgUserCreate( $Request )
    {
    	if ( ALLOW_REGISTRATION )
    	{
	    	if ( !UserProxy::CreateUser("none", 0, "none", $Request["name"], sha1($Request["pass"])) )
	    	{
	    		echo "<error>".L("This username is already in use.")."</error>";
	    	}
		}
		else
		{
			echo "<error>".L("Access denied")."</error>";
		}
    }
?>