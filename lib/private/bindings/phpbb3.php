<?php
    require_once dirname(__FILE__)."/hash_phpbb3.php";
    @include_once dirname(__FILE__)."/../../config/config.phpbb3.php";
    
    function BindPHPBB3User($User)
	{
        if ( isset($User["cleartext"]) && ($User["cleartext"] == true) )
        {
        	// Check if user already exists in local database
        	// Load config values from phpbb
        	
        	global $phpbb_config;
        	$Connector = new Connector(SQL_HOST, PHPBB3_DATABASE, PHPBB3_USER, PHPBB3_PASS);        
            
            $ConfigSt = $Connector->prepare( "SELECT config_value FROM `".PHPBB3_TABLE_PREFIX."config` WHERE config_name = \"rand_seed\"");
            $ConfigSt->execute();
            
            $ConfigData = Array();
                        
            if ( !$ConfigData = $ConfigSt->fetch( PDO::FETCH_ASSOC ) )
            {
            	// config data could not be loaded so, login failes because there is no seed
            	
            	$ConfigSt->closeCursor();
            	return false; // ### no seed ###
            }
            
            $ConfigSt->closeCursor();
            
            $phpbb_config["rand_seed"] = $ConfigData["config_value"];
            
            // Try login
            
            $passwordHash = phpbb_hash($User["Password"]);
        	
        	if ( UserProxy::TryLoginUser($User["Login"], $passwordHash, "phpbb3") )
        	{
        		// Check if the binding changed
        		
        		$UserSt = $Connector->prepare(	"SELECT username_clean, user_password ".
                                  				"FROM `".PHPBB3_TABLE_PREFIX."users` ".
                                  				"WHERE user_id = :UserId LIMIT 1");
                                  		
		        $UserSt->bindValue(":UserId", $_SESSION["User"]["ExternalId"], PDO::PARAM_INT);
		        $UserSt->execute();
		        
		        if ( $UserData = $UserSt->fetch( PDO::FETCH_ASSOC ) )
		        {
		        	// Password or login changed
		        	UserProxy::CheckForBindingUpdate( $_SESSION["User"]["ExternalId"], $UserData["username_clean"], $UserData["user_password"], "phpbb3", true );
		        }
		        else
		        {
		        	// No user found, so the user does not exist in phpbb anymore
		        	// In this case convert to local user
		        	UserProxy::ConvertCurrentUserToLocalBinding();
		        }
		        
		        $UserSt->closeCursor();
        
        		return true; // ### valid, registered user ###
        	}        	
        	
        	// Login failed, or user not registered
        	// Check for the username in phpbb
        	
        	$UserSt = $Connector->prepare("SELECT user_id, user_password ".
                                          "FROM `".PHPBB3_TABLE_PREFIX."users` ".
                                          "WHERE username_clean = :Login LIMIT 1");
            
            $UserSt->bindValue(":Login", strtolower($User["Login"]), PDO::PARAM_STR);
            $UserSt->execute();
            
            if ( $ExternalUserData = $UserSt->fetch( PDO::FETCH_ASSOC ) )
            {
            	// Found user in phpbb
            	
                $UserSt->closeCursor();
            	        
                if ( phpbb_check_hash($User["Password"], $ExternalUserData["user_password"]) )
                {
                    // password check validated
                    // Check if username or password changed for an existing binding
                    
                    if ( UserProxy::CheckForBindingUpdate( $ExternalUserData["user_id"], strtolower($User["Login"]), $ExternalUserData["user_password"], "phpbb3", false ) )
                    {
                    	UserProxy::TryLoginUser($User["Login"], $ExternalUserData["user_password"], "phpbb3");
                    	return true; // ### user changed password or was renamed ###
                    }                    
                    
                	// User not yet registered
                    // Get default group for the current user
                    
                    $DefaultGroup = "none";
                    $NewUserSt = $Connector->prepare("SELECT group_id ".
                                                  	 "FROM `".PHPBB3_TABLE_PREFIX."user_group` ".
                                                  	 "WHERE user_id = :UserId");
                                                  
                    $NewUserSt->bindValue(":UserId", $ExternalUserData["user_id"], PDO::PARAM_INT);
                    $NewUserSt->execute();
                    
                    $MemberGroups   = explode(",", PHPBB3_MEMBER_GROUPS );
                    $RaidleadGroups = explode(",", PHPBB3_RAIDLEAD_GROUPS );
                    
                    while ($Group = $NewUserSt->fetch( PDO::FETCH_ASSOC ))
                    {
                        if ( in_array($Group["group_id"], $MemberGroups) )
                        {
                            $DefaultGroup = "member";
                        }
                           
                        if ( in_array($Group["group_id"], $RaidleadGroups) )
                        {
                            $DefaultGroup = "raidlead";
                            break;
                        }
                    }
        
                    // Insert user into native table and login
                
                    $NewUserSt->closeCursor();
                    
                    UserProxy::CreateUser( $DefaultGroup, $ExternalUserData["user_id"], "phpbb3", $User["Login"], $ExternalUserData["user_password"] );
                    UserProxy::TryLoginUser( $User["Login"], $ExternalUserData["user_password"], "phpbb3" );
            	
            		return true; // ### new user ###
            	}
            }
            
            $UserSt->closeCursor();
        }
        else if ( UserProxy::TryLoginUser($User["Login"], $User["Password"], "phpbb3") )
        {
        	return true; // ### valid user ###
        }
        
        return false; // ### login failed ###
	}
?>