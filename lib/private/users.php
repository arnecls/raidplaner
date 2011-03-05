<?php
    require_once dirname(__FILE__)."/connector.class.php";
    require_once dirname(__FILE__)."/../config/config.php";
    require_once dirname(__FILE__)."/bindings/native.php";
    require_once dirname(__FILE__)."/bindings/phpbb3.php";
    
    class UserProxy
	{
		private static $Instance = NULL;
		
		// --------------------------------------------------------------------------------------------
	
		public function __construct()
		{
			assert(self::$Instance == NULL);
			
		    session_name("mugraid");
            session_start();
                            
            $Connector = Connector::GetInstance();
            
            if (isset($_REQUEST["logout"]))
            {
                // explicit unlog
                unset($_SESSION["User"]);
                unset($_SESSION["Calendar"]);
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
            else if (isset($_REQUEST["user"]) && isset($_REQUEST["pass"]))
            {
                $LoginUser = array( "Login"     => $_REQUEST["user"],
                                    "Password"  => $_REQUEST["pass"],
                                    "cleartext" => true);
                
                // Pure internal login
                if ( BindNativeUser($LoginUser) ) 
                    return;  // ### valid
                
                // Login via PHPBB3
                if ( PHPBB3_BINDING && BindPHPBB3User($LoginUser) ) 
                    return;  // ### valid
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
                	$UserDataFromDb = $UserSt->fetch();
                
	                while ( $item = current( $UserDataFromDb ) )
	                {
	                	$key = key( $UserDataFromDb );
	                	
	                	if ( !isset( $_SESSION["User"][ $key ] ) )
	                	{
	                		return false;
	                	}
	                	
	                	if ( crc32($_SESSION["User"][ $key ]) != crc32($item) ) 
	                	{
	                		return false;
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
            	$UserSt->closeCursor();
	            $UserSt = $Connector->prepare("INSERT INTO `".RP_TABLE_PREFIX."User` (".
	                                          "`Group`, ExternalId, ExternalBinding, Login, Password) ".
	                                          "VALUES ('".$Group."', :ExternalUserId, '".$BindingName."', :Login, :Password)");
	                                          
	            $UserSt->bindValue(":ExternalUserId",   $ExternalUserId,    PDO::PARAM_INT);
	            $UserSt->bindValue(":Login",    		strtolower($Login), PDO::PARAM_STR);
	            $UserSt->bindValue(":Password", 		$Password,  		PDO::PARAM_STR);
	            
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
		
		public static function SetSessionVariables( $UserQuery )
		{
			$_SESSION["User"] = $UserQuery->fetch();
        	
        	$_SESSION["User"]["Role1"] = array( $_SESSION["User"]["Role1"] );
            $_SESSION["User"]["Role2"] = array( $_SESSION["User"]["Role2"] );
        	$_SESSION["User"]["CharacterId"] = array( $_SESSION["User"]["CharacterId"] );
        	
        	while ( $row = $UserQuery->fetch() )
        	{
        		array_push( $_SESSION["User"]["Role1"], $row["Role1"] );
            	array_push( $_SESSION["User"]["Role2"], $row["Role2"] );
            	array_push( $_SESSION["User"]["CharacterId"], $row["CharacterId"] );
        		array_push( $_SESSION["User"]["CharacterName"], $row["CharacterName"] );
        	}
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
            
            $UserSt->execute();
            $Success = $UserSt->rowCount() > 0;
            
            if ( $Success )
            {
            	UserProxy::SetSessionVariables( $UserSt );
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
		           	
		           	while ( $row = $CharacterSt->fetch() )
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