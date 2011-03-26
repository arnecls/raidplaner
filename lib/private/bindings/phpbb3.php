<?php
    require_once dirname(__FILE__)."/hash_phpbb3.php";
    require_once dirname(__FILE__)."/../../config/config.phpbb3.php";
    
    function BindPHPBB3User($_User)
	{
        $Success = false;
        
        if ( isset($_User["cleartext"]) )
        {
            $Connector = new Connector(SQL_HOST, PHPBB3_DATABASE, PHPBB3_USER, PHPBB3_PASS);
            $UserSt = $Connector->prepare("SELECT user_id, user_password ".
                                          "FROM `".PHPBB3_TABLE_PREFIX."users` ".
                                          "WHERE username_clean = :Login LIMIT 1");
            
            $UserSt->bindValue(":Login", strtolower($_User["Login"]), PDO::PARAM_STR);
            
            $UserSt->execute();
            $Success = ($UserSt->rowCount() == 1);
            
            if ( $Success )
            {
                // Found user in phpbb
                
                $UserData = $UserSt->fetch();
                $UserSt->closeCursor();
                        
                if ( phpbb_check_hash($_User["Password"], $UserData["user_password"]) )
                {
                    // password check ok
                	
                	$Success = UserProxy::TryLoginUser( $_User["Login"], $UserData["user_password"], "phpbb3" );
                	
                    if ( $Success) 
                    {
                    	// Check if passwords still match
                    	
                    	UserProxy::UpdatePasswordIfDifferent( $UserData["user_password"] );
                    }
                    else
                    {
                    	// User not yet registered
                        // Get default group for the current user
                        
                        $DefaultGroup = "none";
                        $UserSt->closeCursor();
                        
                        $Connector = Connector::GetExternInstance(SQL_HOST, PHPBB3_DATABASE, PHPBB3_USER, PHPBB3_PASS);
                        $UserSt = $Connector->prepare("SELECT group_id ".
                                                      "FROM `".PHPBB3_TABLE_PREFIX."user_group` ".
                                                      "WHERE user_id = :UserId");
                                                      
                        $UserSt->bindValue(":UserId", $UserData["user_id"], PDO::PARAM_INT);
                        $UserSt->execute();
                        
                        $MemberGroups   = explode(",", PHPBB3_MEMBER_GROUPS );
                        $RaidleadGroups = explode(",", PHPBB3_RAIDLEAD_GROUPS );
                        
                        while ($Group = $UserSt->fetch())
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
            
                        // Insert user into native table
                    
                        $UserSt->closeCursor();
                        
                        UserProxy::CreateUser( $DefaultGroup, $UserData["user_id"], "phpbb3", $_User["Login"], $UserData["user_password"] );
                        $Success = UserProxy::TryLoginUser( $_User["Login"], $UserData["user_password"], "phpbb3" );
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
        	$Success = UserProxy::TryLoginUser( $_User["Login"], $_User["Password"], "phpbb3" );
        }
        
        return $Success;
	}
?>