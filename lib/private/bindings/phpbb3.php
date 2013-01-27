<?php
    require_once dirname(__FILE__)."/hash_phpbb3.php";
    @include_once dirname(__FILE__)."/../../config/config.phpbb3.php";
    
    function BindPHPBB3User($User)
    {
        if ( isset($User["cleartext"]) && 
             ($User["cleartext"] == true) )
        {
            // Check if user already exists in local database
            // The salt is stored along with the password so we need to find that first
            
            $RaidConnect = Connector::GetInstance();
            $Connector = new Connector(SQL_HOST, PHPBB3_DATABASE, PHPBB3_USER, PHPBB3_PASS);        
            
            $LocalDataSt = $RaidConnect->prepare( "SELECT Password FROM `".RP_TABLE_PREFIX."User` WHERE Login = :Login AND ExternalBinding = \"phpbb3\" LIMIT 1" );
            $LocalDataSt->bindValue(":Login", strtolower($User["Login"]), PDO::PARAM_STR);
            
            if ( $LocalDataSt->execute() )
            {
                if ( $LocalData = $LocalDataSt->fetch(PDO::FETCH_ASSOC) )
                {
                    if ( phpbb_check_hash($User["Password"], $LocalData["Password"]) )
                    {
                        // Password is correct, user exists
                        // Login with the already verified password to ensure session
                        // variables, etc. are set
                        
                        if ( UserProxy::TryLoginUser($User["Login"], $LocalData["Password"], "phpbb3") )
                        {
                            // Check if the binding changed
                
                            $UserSt = $Connector->prepare("SELECT username_clean, user_password ".
                                                          "FROM `".PHPBB3_TABLE_PREFIX."users` ".
                                                          "WHERE user_id = :UserId LIMIT 1");
                                                      
                            $UserSt->bindValue(":UserId", $_SESSION["User"]["ExternalId"], PDO::PARAM_INT);
                            $UserSt->execute();
                            
                            if ( $UserData = $UserSt->fetch(PDO::FETCH_ASSOC) )
                            {
                                // Password or login changed
                                UserProxy::CheckForBindingUpdate( $_SESSION["User"]["ExternalId"], $UserData["username_clean"], $UserData["user_password"], "phpbb3", true );
                            }
                            else
                            {
                                // No user found, so the user does not exist in phpbb anymore
                                // In this case convert to local user
                                UserProxy::ConvertCurrentUserToLocalBinding( $User["Password"] );
                            }
                            
                            $LocalDataSt->closeCursor();
                            $UserSt->closeCursor();
                    
                            return true; // ### return, valid user ###
                        }
                    }
                }
            }
            
            $LocalDataSt->closeCursor();
            
            // The user does not exist in the local database, so check phpbb for this user
            // Load config values from phpbb
            
            global $phpbb_config;
            
            $ConfigSt = $Connector->prepare( "SELECT config_value FROM `".PHPBB3_TABLE_PREFIX."config` WHERE config_name = \"rand_seed\"");
            $ConfigSt->execute();
            
            $ConfigData = Array();
                        
            if ( !$ConfigData = $ConfigSt->fetch( PDO::FETCH_ASSOC ) )
            {
                // config data could not be loaded so, login failes because there is no seed
                
                $ConfigSt->closeCursor();
                return false; // ### return, no seed ###
            }
            
            $ConfigSt->closeCursor();            
            $phpbb_config["rand_seed"] = $ConfigData["config_value"];
            
            // Query for user in phpbb db            
                        
            $UserSt = $Connector->prepare("SELECT user_id, user_password ".
                                          "FROM `".PHPBB3_TABLE_PREFIX."users` ".
                                          "WHERE username_clean = :Login LIMIT 1");
            
            $UserSt->bindValue(":Login", strtolower($User["Login"]), PDO::PARAM_STR);
            $UserSt->execute();
            
            if ( $ExternalUserData = $UserSt->fetch( PDO::FETCH_ASSOC ) )
            {
                // Found potential user in phpbb, validate password
                
                if ( phpbb_check_hash($User["Password"], $ExternalUserData["user_password"]) )
                {
                    // password check validated
                    // Check if username or password changed for an existing binding
                    
                    if ( UserProxy::CheckForBindingUpdate( $ExternalUserData["user_id"], strtolower($User["Login"]), $ExternalUserData["user_password"], "phpbb3", false ) )
                    {
                        $UserSt->closeCursor();
                        return UserProxy::TryLoginUser($User["Login"], $ExternalUserData["user_password"], "phpbb3"); // ### return, modified user ###
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
                            break; // ### break, highest possible group ###
                        }
                    }
        
                    // Insert user into native table and login
                
                    $UserSt->closeCursor();
                    $NewUserSt->closeCursor();
                    
                    UserProxy::CreateUser( $DefaultGroup, $ExternalUserData["user_id"], "phpbb3", $User["Login"], $ExternalUserData["user_password"] );
                    $Success = UserProxy::TryLoginUser( $User["Login"], $ExternalUserData["user_password"], "phpbb3" );
                
                    return $Success; // ### new user ###
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