<?php
	require_once dirname(__FILE__)."/../../config/config.eqdkp.php";
        
	function BindEQDKPUser( $User )
	{
		$Success = false;
        
        if ( isset($User["cleartext"]) && ($User["cleartext"] == true) )
        {
            $Connector = new Connector(SQL_HOST, EQDKP_DATABASE, EQDKP_USER, EQDKP_PASS);
            $UserSt = $Connector->prepare("SELECT user_id, user_password ".
                                          "FROM `".EQDKP_TABLE_PREFIX."users` ".
                                          "WHERE username = :Login LIMIT 1");
            
            $UserSt->bindValue(":Login", strtolower($User["Login"]), PDO::PARAM_STR);
            
            $UserSt->execute();
            $Success = ($UserSt->rowCount() == 1);
            
            if ( $Success )
            {
                // Found user in phpbb
                
                $UserData = $UserSt->fetch( PDO::FETCH_ASSOC );
                $UserSt->closeCursor();
                        
                if ( md5($User["Password"]) == $UserData["user_password"] )
                {
                    // password check ok
                	
                	$Success = UserProxy::TryLoginUser( $User["Login"], $UserData["user_password"], "eqdkp" );
                	
                    if ( $Success) 
                    {
                    	// Check if passwords still match
                    	
                    	UserProxy::UpdatePasswordIfDifferent( $UserData["user_password"] );
                    }
                    else
                    {
                    	// User not yet registered
                        // Get default group for the current user
                        
                        $DefaultGroup = "member";
                        $UserSt->closeCursor();
                        
                        $UserSt = $Connector->prepare("SELECT ".EQDKP_TABLE_PREFIX."users.user_active, ".EQDKP_TABLE_PREFIX."auth_users.auth_setting,  ".EQDKP_TABLE_PREFIX."auth_options.auth_value ".
                                                      "FROM `".EQDKP_TABLE_PREFIX."users` ".
                                                      "LEFT JOIN `".EQDKP_TABLE_PREFIX."auth_users` USING(user_id) ".
                                                      "LEFT JOIN `".EQDKP_TABLE_PREFIX."auth_options` USING(auth_id) ".
                                                      "WHERE user_id = :UserId");
                                                      
                        $UserSt->bindValue(":UserId", $UserData["user_id"], PDO::PARAM_INT);
                        $UserSt->execute();
                        
                        while ( $Right = $UserSt->fetch( PDO::FETCH_ASSOC ) )
                        {
                        	if ( $Right["user_active"] == 0 )
                        	{
                        		$DefaultGroup = "none";
                        		break; // ### not active, defaults to "none" ###
	                    	}
	                    	
	                    	if ( $Right["auth_setting"] == "Y" )
	                    	{
	                    		switch ($Right["auth_value"])
	                    		{
	                    		case "a_raid_add":
	                    		case "a_raid_upd":
	                    			if ($DefaultGroup != "admin")
	                    				$DefaultGroup = "raidlead";
	                    			break;
	                    			
	                    		case "a_config_man":
	                    			$DefaultGroup = "admin";
	                    			break;
	                    			
	                    		default:
	                    			break;
	                    		}	                    	
	                    	}
	                    }
            
                        // Insert user into native table
                    
                        $UserSt->closeCursor();
                        
                        UserProxy::CreateUser( $DefaultGroup, $UserData["user_id"], "eqdkp", $User["Login"], $UserData["user_password"] );
                        $Success = UserProxy::TryLoginUser( $User["Login"], $UserData["user_password"], "eqdkp" );
                    }
                    
                    $UserSt->closeCursor();
                }
                else // Password check failed
                {
                    $Success = false;
                }
            }
            else // User fetch failed
            {
                $UserSt->closeCursor();
            }
        }
        else // password not cleartext
        {
        	$Success = UserProxy::TryLoginUser( $User["Login"], $User["Password"], "eqdkp" );
        }
        
        return $Success;
	}
?>