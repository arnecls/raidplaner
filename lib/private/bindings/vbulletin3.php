<?php
    require_once dirname(__FILE__)."/../../config/config.vb3.php";
    
    function vb3_check_hash( $ClearText, $Password, $Salt )
    {
    	if ( md5($ClearText.$Salt) == $Password )
    		return true;
    		
    	if ( md5(md5($ClearText).$Salt) == $Password )
    		return true;
    		
    	return false;
    }
    
    function BindVB3User($User)
	{
        $Success = false;
        
        if ( isset($User["cleartext"]) && ($User["cleartext"] == true) )
        {
            $Connector = new Connector(SQL_HOST, VB3_DATABASE, VB3_USER, VB3_PASS);
            $UserSt = $Connector->prepare("SELECT userid, password, salt ".
                                          "FROM `".VB3_TABLE_PREFIX."user` ".
                                          "WHERE username = :Login LIMIT 1");
            
            $UserSt->bindValue(":Login", strtolower($User["Login"]), PDO::PARAM_STR);
            
            $UserSt->execute();
            $Success = ($UserSt->rowCount() == 1);
            
            if ( $Success )
            {
                // Found user in vbulletin3
                
                $UserData = $UserSt->fetch( PDO::FETCH_ASSOC );
                $UserSt->closeCursor();
                        
                if ( vb3_check_hash($User["Password"], $UserData["password"], $UserData["salt"]) )
                {
                    // password check ok
                	
                	$Success = UserProxy::TryLoginUser( $User["Login"], $UserData["password"], "vb3" );
                	
                    if ( $Success) 
                    {
                    	// Check if passwords still match
                    	
                    	UserProxy::UpdatePasswordIfDifferent( $UserData["password"] );
                    }
                    else
                    {
                    	// User not yet registered
                        // Get default group for the current user
                        
                        $DefaultGroup = "none";
                        $UserSt->closeCursor();
                        
                        $UserSt = $Connector->prepare("SELECT usergroupid ".
                                                      "FROM `".VB3_TABLE_PREFIX."user` ".
                                                      "WHERE userid = :UserId LIMIT 1");
                                                      
                        $UserSt->bindValue(":UserId", $UserData["userid"], PDO::PARAM_INT);
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
                        
                        UserProxy::CreateUser( $DefaultGroup, $UserData["userid"], "vb3", $User["Login"], $UserData["password"] );
                        $Success = UserProxy::TryLoginUser( $User["Login"], $UserData["password"], "vb3" );
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
        	$Success = UserProxy::TryLoginUser( $User["Login"], $User["Password"], "vb3" );
        }
        
        return $Success;
	}
?>