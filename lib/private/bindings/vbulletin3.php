<?php
    @include_once dirname(__FILE__)."/../../config/config.vb3.php";
    
    function vb3_check_hash( $ClearText, $Password, $Salt )
    {
        if ( md5($ClearText.$Salt) == $Password )
            return true;
            
        if ( md5(md5($ClearText).$Salt) == $Password )
            return true;
            
        return false;
    }
    
    // -------------------------------------------------------------------------
    
    function BindVB3User($User)
    {
        if ( isset($User["cleartext"]) && 
             ($User["cleartext"] == true) )
        {
            $Connector = new Connector(SQL_HOST, VB3_DATABASE, VB3_USER, VB3_PASS);
            $RaidConnect = Connector::GetInstance();
            
            // Check if user already exists in local database
            // Fetch userid from raidplaner table
            
            $ExternalIdSt = $RaidConnect->prepare( "SELECT ExternalId, Hash FROM `".RP_TABLE_PREFIX."User` WHERE Login = :Login AND ExternalBinding = \"vb3\" LIMIT 1" );
            
            $ExternalIdSt->bindValue(":Login", strtolower($User["Login"]), PDO::PARAM_STR);
            $ExternalIdSt->execute();
            
            if ( $LocalData = $ExternalIdSt->fetch(PDO::FETCH_ASSOC) )
            {
                // Local user found
                // Try login. vbulletin allows two hash variants, so check both
                // The correct one will be stored in session data
                
                $passwordHash1 = md5(md5($User["Password"]).$LocalData["Hash"]);
                $passwordHash2 = md5($User["Password"].$LocalData["Hash"]);
                
                if ( UserProxy::TryLoginUser($User["Login"], $passwordHash1, "vb3") || 
                     UserProxy::TryLoginUser($User["Login"], $passwordHash2, "vb3") )
                {
                    // Check if the binding changed
                    
                    $UserSt = $Connector->prepare("SELECT username, password, salt ".
                                                  "FROM `".VB3_TABLE_PREFIX."user` ".
                                                  "WHERE userid = :UserId LIMIT 1");
                                              
                    $UserSt->bindValue(":UserId", $_SESSION["User"]["ExternalId"], PDO::PARAM_INT);
                    $UserSt->execute();
                    
                    if ( $UserData = $UserSt->fetch( PDO::FETCH_ASSOC ) )
                    {
                        // Password or login changed?
                        
                        UserProxy::CheckForBindingUpdate( $_SESSION["User"]["ExternalId"], $UserData["username"], $UserData["password"], "vb3", true, $UserData["salt"] );
                    }
                    else
                    {
                        // No user found, so the user does not exist in vbulletin anymore
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
            // Check for the username in vbulletin
            
            $UserSt = $Connector->prepare("SELECT userid, password, salt ".
                                          "FROM `".VB3_TABLE_PREFIX."user` ".
                                          "WHERE username = :Login LIMIT 1");
            
            $UserSt->bindValue(":Login", $User["Login"], PDO::PARAM_STR);
            $UserSt->execute();
            
            if ( $ExternalUserData = $UserSt->fetch( PDO::FETCH_ASSOC ) )
            {
                // Found user in vbulletin
                
                $UserSt->closeCursor();
                        
                if ( vb3_check_hash($User["Password"], $ExternalUserData["password"], $ExternalUserData["salt"]) )
                {
                    // password check validated
                    // Check if username or password changed for an existing binding
                    
                    if ( UserProxy::CheckForBindingUpdate($ExternalUserData["userid"], strtolower($User["Login"]), $ExternalUserData["password"], "vb3", false, $ExternalUserData["salt"]) )
                    {
                        return UserProxy::TryLoginUser($User["Login"], $ExternalUserData["password"], "vb3"); // ### return, user modified ###
                    }                    
                    
                    // User not yet registered
                    // Get default group for the current user
                    
                    $DefaultGroup = "none";
                    
                    $UserSt = $Connector->prepare("SELECT usergroupid ".
                                                  "FROM `".VB3_TABLE_PREFIX."user` ".
                                                  "WHERE userid = :UserId LIMIT 1");
                                                  
                    $UserSt->bindValue(":UserId", $ExternalUserData["userid"], PDO::PARAM_INT);
                    $UserSt->execute();
                    
                    $MemberGroups   = explode(",", VB3_MEMBER_GROUPS );
                    $RaidleadGroups = explode(",", VB3_RAIDLEAD_GROUPS );
                    
                    if ($Group = $UserSt->fetch( PDO::FETCH_ASSOC ))
                    {
                        if ( in_array($Group["usergroupid"], $MemberGroups) )
                        {
                            $DefaultGroup = "member";
                        }
                           
                        if ( in_array($Group["usergroupid"], $RaidleadGroups) )
                        {
                            $DefaultGroup = "raidlead";
                        }
                    }
        
                    // Insert user into native table
                
                    $UserSt->closeCursor();
                    
                    UserProxy::CreateUser( $DefaultGroup, $ExternalUserData["userid"], "vb3", $User["Login"], $ExternalUserData["password"], $ExternalUserData["salt"] );
                    $Success = UserProxy::TryLoginUser( $User["Login"], $ExternalUserData["password"], "vb3" );
                
                    return $Success; // ### new user ###
                }
            }
            else
            {
                $UserSt->closeCursor();
            }
            
            // User not found in vbulletin or invalid password            
        }
        else if ( UserProxy::TryLoginUser($User["Login"], $User["Password"], "vb3") )
        {
            return true; // ### valid user ###
        }
        
        return false; // ### login failed ###
    }
?>