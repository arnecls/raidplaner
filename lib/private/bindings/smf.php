<?php
    @include_once dirname(__FILE__)."/../../config/config.smf.php";
       
    function smf_hash( $ClearText, $UserName )
    {
        return sha1(strtolower($UserName).$ClearText);
    }
    
    // -------------------------------------------------------------------------
    
    function BindSMFUser($User)
    {
        if ( isset($User["cleartext"]) && 
             ($User["cleartext"] == true) )
        {
            $Connector = new Connector(SQL_HOST, SMF_DATABASE, SMF_USER, SMF_PASS);
            $RaidConnect = Connector::GetInstance();
            
            // Check if user already exists in local database
            // Fetch userid from raidplaner table
            
            $ExternalIdSt = $RaidConnect->prepare( "SELECT ExternalId, Login FROM `".RP_TABLE_PREFIX."User` WHERE Login = :Login AND ExternalBinding = \"smf\" LIMIT 1" );
            
            $ExternalIdSt->bindValue(":Login", strtolower($User["Login"]), PDO::PARAM_STR);
            $ExternalIdSt->execute();
            
            if ( $LocalData = $ExternalIdSt->fetch(PDO::FETCH_ASSOC) )
            {
                // Local user found
                // Try login.
                
                $passwordHash = smf_hash($User["Password"], $LocalData["Login"]);
                
                if ( UserProxy::TryLoginUser($User["Login"], $passwordHash, "smf") )
                {
                    // Check if the binding changed
                    
                    $UserSt = $Connector->prepare("SELECT member_name, passwd ".
                                                  "FROM `".SMF_TABLE_PREFIX."members` ".
                                                  "WHERE id_member = :UserId LIMIT 1");
                                              
                    $UserSt->bindValue(":UserId", $_SESSION["User"]["ExternalId"], PDO::PARAM_INT);
                    $UserSt->execute();
                    
                    if ( $UserData = $UserSt->fetch( PDO::FETCH_ASSOC ) )
                    {
                        // Password or login changed?
                        
                        UserProxy::CheckForBindingUpdate( $_SESSION["User"]["ExternalId"], $UserData["member_name"], $UserData["passwd"], "smf", true );
                    }
                    else
                    {
                        // No user found, so the user does not exist in smf anymore
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
            // Check for the username in smf
            
            $UserSt = $Connector->prepare("SELECT id_member, member_name, passwd, id_group, additional_groups ".
                                          "FROM `".SMF_TABLE_PREFIX."members` ".
                                          "WHERE member_name = :Login LIMIT 1");
            
            $UserSt->bindValue(":Login", $User["Login"], PDO::PARAM_STR);
            $UserSt->execute();
            
            if ( $ExternalUserData = $UserSt->fetch( PDO::FETCH_ASSOC ) )
            {
                // Found user in smf
                
                $UserSt->closeCursor();
                
                if ( smf_hash($User["Password"], $ExternalUserData["member_name"]) == $ExternalUserData["passwd"] )
                {
                    // password check validated
                    // Check if username or password changed for an existing binding
                    
                    if ( UserProxy::CheckForBindingUpdate($ExternalUserData["id_member"], strtolower($User["Login"]), $ExternalUserData["passwd"], "smf", false) )
                    {
                        return UserProxy::TryLoginUser($User["Login"], $ExternalUserData["passwd"], "smf"); // ### return, user modified ###
                    }                    
                    
                    // User not yet registered
                    // Get default group for the current user
                    
                    $DefaultGroup = "none";
                    
                    $MemberGroups   = explode(",", SMF_MEMBER_GROUPS );
                    $RaidleadGroups = explode(",", SMF_RAIDLEAD_GROUPS );
                    
                    $Groups = explode(",", $ExternalUserData["additional_groups"]);
                    array_push($Groups, $ExternalUserData["id_group"] );
                    
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
                    
                    UserProxy::CreateUser( $DefaultGroup, $ExternalUserData["id_member"], "smf", $User["Login"], $ExternalUserData["passwd"] );
                    $Success = UserProxy::TryLoginUser( $User["Login"], $ExternalUserData["passwd"], "smf" );
                
                    return $Success; // ### new user ###
                }
            }
            else
            {
                $UserSt->closeCursor();
            }
            
            // User not found in smf or invalid password            
        }
        else if ( UserProxy::TryLoginUser($User["Login"], $User["Password"], "smf") )
        {
            return true; // ### valid user ###
        }
        
        return false; // ### login failed ###
    }
?>