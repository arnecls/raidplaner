<?php
    @include_once dirname(__FILE__)."/../../config/config.mybb.php";
       
    function mybb_hash( $ClearText, $Salt )
    {
        return md5(md5($Salt).md5($ClearText));
    }
    
    // -------------------------------------------------------------------------
    
    function BindMyBBUser($User)
    {
        if ( isset($User["cleartext"]) && 
             ($User["cleartext"] == true) )
        {
            $Connector = new Connector(SQL_HOST, MYBB_DATABASE, MYBB_USER, MYBB_PASS);
            $RaidConnect = Connector::GetInstance();
            
            // Check if user already exists in local database
            // Fetch userid from raidplaner table
            
            $ExternalIdSt = $RaidConnect->prepare( "SELECT ExternalId, Hash FROM `".RP_TABLE_PREFIX."User` WHERE Login = :Login AND ExternalBinding = \"mybb\" LIMIT 1" );
            
            $ExternalIdSt->bindValue(":Login", strtolower($User["Login"]), PDO::PARAM_STR);
            $ExternalIdSt->execute();
            
            if ( $LocalData = $ExternalIdSt->fetch(PDO::FETCH_ASSOC) )
            {
                // Local user found
                // Try login.
                
                $passwordHash = mybb_hash($User["Password"], $LocalData["Hash"]);
                
                if ( UserProxy::TryLoginUser($User["Login"], $passwordHash, "mybb") )
                {
                    // Check if the binding changed
                    
                    $UserSt = $Connector->prepare("SELECT username, password, salt ".
                                                  "FROM `".MYBB_TABLE_PREFIX."users` ".
                                                  "WHERE uid = :UserId LIMIT 1");
                                              
                    $UserSt->bindValue(":UserId", $_SESSION["User"]["ExternalId"], PDO::PARAM_INT);
                    $UserSt->execute();
                    
                    if ( $UserData = $UserSt->fetch( PDO::FETCH_ASSOC ) )
                    {
                        // Password or login changed?
                        
                        UserProxy::CheckForBindingUpdate( $_SESSION["User"]["ExternalId"], $UserData["username"], $UserData["password"], "mybb", true, $UserData["salt"] );
                    }
                    else
                    {
                        // No user found, so the user does not exist in mybb anymore
                        // convert to local user
                        
                        UserProxy::ConvertCurrentUserToLocalBinding($User["Password"]);
                    }
                    
                    $UserSt->closeCursor();
                    $ExternalIdSt->closeCursor();
                    
                    return true; // ### valid, registered user ###
                }
            }
            
            $ExternalIdSt->closeCursor();
            
            // Login failed, or user not registered
            // Check for the username in mybb
            
            $UserSt = $Connector->prepare("SELECT uid, password, salt, usergroup, additionalgroups ".
                                          "FROM `".MYBB_TABLE_PREFIX."users` ".
                                          "WHERE username = :Login LIMIT 1");
            
            $UserSt->bindValue(":Login", $User["Login"], PDO::PARAM_STR);
            $UserSt->execute();
            
            if ( $ExternalUserData = $UserSt->fetch( PDO::FETCH_ASSOC ) )
            {
                // Found user in mybb
                
                $UserSt->closeCursor();
                
                if ( mybb_hash($User["Password"], $ExternalUserData["salt"]) == $ExternalUserData["password"] )
                {
                    // password check validated
                    // Check if username or password changed for an existing binding
                    
                    if ( UserProxy::CheckForBindingUpdate($ExternalUserData["uid"], strtolower($User["Login"]), $ExternalUserData["password"], "mybb", false, $ExternalUserData["salt"]) )
                    {
                        return UserProxy::TryLoginUser($User["Login"], $ExternalUserData["password"], "mybb"); // ### return, user modified ###
                    }                    
                    
                    // User not yet registered
                    // Get default group for the current user
                    
                    $DefaultGroup = "none";
                    
                    $MemberGroups   = explode(",", MYBB_MEMBER_GROUPS );
                    $RaidleadGroups = explode(",", MYBB_RAIDLEAD_GROUPS );
                    
                    $Groups = explode(",", $ExternalUserData["additionalgroups"]);
                    array_push($Groups, $ExternalUserData["usergroup"] );
                    
                    foreach( $Groups as $Group )
                    {
                        if ( in_array($Group, $MemberGroups) )
                        {
                            $DefaultGroup = "member";
                        }
                           
                        if ( in_array($Group, $RaidleadGroups) )
                        {
                            $DefaultGroup = "raidlead";
                            break; // ### break, best possible group ###
                        }
                    }
        
                    // Insert user into native table
                
                    $UserSt->closeCursor();
                    
                    UserProxy::CreateUser( $DefaultGroup, $ExternalUserData["uid"], "mybb", $User["Login"], $ExternalUserData["password"], $ExternalUserData["salt"] );
                    $Success = UserProxy::TryLoginUser( $User["Login"], $ExternalUserData["password"], "mybb" );
                
                    return $Success; // ### new user ###
                }
            }
            else
            {
                $UserSt->closeCursor();
            }
            
            // User not found in mybb or invalid password            
        }
        else if ( UserProxy::TryLoginUser($User["Login"], $User["Password"], "mybb") )
        {
            return true; // ### valid user ###
        }
        
        return false; // ### login failed ###
    }
?>