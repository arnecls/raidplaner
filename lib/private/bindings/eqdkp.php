<?php
    @include_once dirname(__FILE__)."/../../config/config.eqdkp.php";
       
    function BindEQDKPUser($User)
    {
        if ( isset($User["cleartext"]) && ($User["cleartext"] == true) )
        {
            $Connector = new Connector(SQL_HOST, EQDKP_DATABASE, EQDKP_USER, EQDKP_PASS);
            
            // Check if user already exists in local database
            // Try login
            
            $passwordHash = md5($User["Password"]);
            
            if ( UserProxy::TryLoginUser($User["Login"], $passwordHash, "eqdkp") )
            {
                // Check if the binding changed
                
                $UserSt = $Connector->prepare(    "SELECT username, user_password ".
                                                  "FROM `".EQDKP_TABLE_PREFIX."users` ".
                                                  "WHERE user_id = :UserId LIMIT 1");
                                          
                $UserSt->bindValue(":UserId", $_SESSION["User"]["ExternalId"], PDO::PARAM_INT);
                $UserSt->execute();
                
                if ( $UserData = $UserSt->fetch( PDO::FETCH_ASSOC ) )
                {
                    // Password or login changed
                    UserProxy::CheckForBindingUpdate( $_SESSION["User"]["ExternalId"], $UserData["username"], $UserData["user_password"], "eqdkp", true );
                }
                else
                {
                    // No user found, so the user does not exist in eqdkp anymore
                    // In this case convert to local user
                    UserProxy::ConvertCurrentUserToLocalBinding();
                }
                
                $UserSt->closeCursor();
        
                return true; // ### valid, registered user ###
            }            
            
            // Login failed, or user not registered
            // Check for the username in eqdkp
            
            $UserSt = $Connector->prepare("SELECT user_id, user_password ".
                                          "FROM `".EQDKP_TABLE_PREFIX."users` ".
                                          "WHERE username = :Login LIMIT 1");
            
            $UserSt->bindValue(":Login", $User["Login"], PDO::PARAM_STR);
            $UserSt->execute();
            
            if ( $ExternalUserData = $UserSt->fetch( PDO::FETCH_ASSOC ) )
            {
                // Found user in eqdkp
                
                $UserSt->closeCursor();
                        
                if ( md5($User["Password"]) == $ExternalUserData["user_password"] )
                {
                    // password check validated
                    // Check if username or password changed for an existing binding
                    
                    if ( UserProxy::CheckForBindingUpdate( $ExternalUserData["user_id"], strtolower($User["Login"]), $ExternalUserData["user_password"], "eqdkp", false ) )
                    {
                        UserProxy::TryLoginUser($User["Login"], $ExternalUserData["user_password"], "eqdkp");
                        return true; // ### user changed password or was renamed ###
                    }                    
                    
                    // User not yet registered
                    // Get default group for the current user
                    
                    $DefaultGroup = "member";
                    $UserSt->closeCursor();
                    
                    $UserSt = $Connector->prepare("SELECT ".EQDKP_TABLE_PREFIX."users.user_active, ".EQDKP_TABLE_PREFIX."auth_users.auth_setting,  ".EQDKP_TABLE_PREFIX."auth_options.auth_value ".
                                                  "FROM `".EQDKP_TABLE_PREFIX."users` ".
                                                  "LEFT JOIN `".EQDKP_TABLE_PREFIX."auth_users` USING(user_id) ".
                                                  "LEFT JOIN `".EQDKP_TABLE_PREFIX."auth_options` USING(auth_id) ".
                                                  "WHERE user_id = :UserId");
                                                  
                    $UserSt->bindValue(":UserId", $ExternalUserData["user_id"], PDO::PARAM_INT);
                    $UserSt->execute();
                    
                    while ( $Right = $UserSt->fetch( PDO::FETCH_ASSOC ) )
                    {
                        if ( $Right["user_active"] == 0 )
                        {
                            $DefaultGroup = "none";
                            break; // ### not active, defaults to "none" ###
                        }
                        
                        if ( (($Right["auth_value"] == "a_raid_add") || ($Right["auth_value"] == "a_raid_upd"))
                             && ($Right["auth_setting"] == "Y") )
                        {
                            $DefaultGroup = "raidlead";
                            break;
                        }
                    }
        
                    // Insert user into native table
                
                    $UserSt->closeCursor();
                    
                    UserProxy::CreateUser( $DefaultGroup, $ExternalUserData["user_id"], "eqdkp", $User["Login"], $ExternalUserData["user_password"] );
                    $Success = UserProxy::TryLoginUser( $User["Login"], $ExternalUserData["user_password"], "eqdkp" );
                
                    return true; // ### new user ###
                }
            }
            
            $UserSt->closeCursor();
        }
        else if ( UserProxy::TryLoginUser($User["Login"], $User["Password"], "eqdkp") )
        {
            return true; // ### valid user ###
        }
        
        return false; // ### login failed ###
    }
?>