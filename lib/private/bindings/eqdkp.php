<?php
    @include_once dirname(__FILE__)."/../../config/config.eqdkp.php";
    
    function eqdkp_hash_crypt( $Password, $StoredPassword )
    {
        $parts = explode(':', $StoredPassword);
        $key   = $parts[0];
        $salt  = $parts[1];
        
        $preHash = hash('sha512', $salt.$Password);
        
        return crypt($preHash, $key);
    }
    
	// ----------------------------------------------------------------------------
	    
    function eqdkp_hash_md5( $Password, $StoredPassword )
    {
        return md5($Password);
    }
    
	// ----------------------------------------------------------------------------
	
    function eqdkp_hash_sha512( $Password, $StoredPassword )
    {
        $parts = explode(':', $StoredPassword);
        $salt  = $parts[1];
        
        return hash('sha512', $salt.$Password);
    }

    // ----------------------------------------------------------------------------
		
    function eqdkp_hash_sha512_rounds( $Password, $StoredPassword )
    {
        $itoa64 = "./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
        
        $parts = explode(':', $StoredPassword);
        $key  = $parts[0];
        $salt = $parts[1];
        
        $preHash = hash('sha512', $salt.$Password);
        
        $count = 1 << strpos($itoa64, $key[3]);
        $salt = substr($key, 4, 8);
        
        $hash = hash('sha512', $salt.$preHash, TRUE);
        
		do 
		{
			$hash = hash('sha512', $hash.$preHash, TRUE);
		} 
		while (--$count);
		
		$prefix  = substr($key, 0, 12);
		$hashLen = strlen($hash);
		$postfix = "";		
						
		$i = 0;
		do 
		{
            $value = ord($hash[$i++]);
            $postfix .= $itoa64[$value & 0x3f];
            
            if ($i < $hashLen) $value |= ord($hash[$i]) << 8;            	
            $postfix .= $itoa64[($value >> 6) & 0x3f];
            
            if ($i++ < $hashLen)
            {
                if ($i < $hashLen) $value |= ord($hash[$i]) << 16;
                $postfix .= $itoa64[($value >> 12) & 0x3f];
            
                if ($i++ < $hashLen)
            	   $postfix .= $itoa64[($value >> 18) & 0x3f];
            }
        }  
		while ($i < $hashLen);
		
		return $prefix.$postfix;
    }
    
	// ----------------------------------------------------------------------------
	
    function eqdkp_hash( $Password, $StoredPassword )
    {
        $length = strlen(substr($StoredPassword, 0, strpos($StoredPassword, ":")));
        
        if ((substr($StoredPassword, 0, 4) == '$2a$') && ($length == 60)) 
            return eqdkp_hash_crypt($Password, $StoredPassword);
        
        if (($StoredPassword[0] == '_') && ($length == 20)) 
            return eqdkp_hash_crypt($Password, $StoredPassword);
        
        if ((substr($StoredPassword, 0, 3) == '$S$') && ($length == 98)) 
            return eqdkp_hash_sha512_rounds($Password, $StoredPassword);
            
        if ($length == 128) 
            return eqdkp_hash_sha512($Password, $StoredPassword);

		return eqdkp_hash_md5($Password, $StoredPassword);
	}
	
	// ----------------------------------------------------------------------------
       
    function BindEQDKPUser($User)
    {
        if ( isset($User["cleartext"]) && 
            ($User["cleartext"] == true) )
        {
            $Connector = new Connector(SQL_HOST, EQDKP_DATABASE, EQDKP_USER, EQDKP_PASS);
            $RaidConnect = Connector::GetInstance();
            
            // Check if user already exists in local database
            // Fetch userid from raidplaner table
            
            $ExternalIdSt = $RaidConnect->prepare( "SELECT ExternalId, Password, Hash FROM `".RP_TABLE_PREFIX."User` WHERE Login = :Login AND ExternalBinding = \"eqdkp\" LIMIT 1" );
            
            $ExternalIdSt->bindValue(":Login", strtolower($User["Login"]), PDO::PARAM_STR);
            $ExternalIdSt->execute();
            
            if ( $LocalData = $ExternalIdSt->fetch(PDO::FETCH_ASSOC) )
            {
                // Local user found
                // Try login. EQDkp stores hash information along with the password, so
                // it is used to hash a password, too.
                
                $passwordHash = eqdkp_hash($User["Password"], $LocalData["Password"].":".$LocalData["Hash"]);
                
                if ( UserProxy::TryLoginUser($User["Login"], $passwordHash, "eqdkp") )
                {
                    // Check if the binding changed
                    
                    $UserSt = $Connector->prepare("SELECT username, user_password ".
                                                  "FROM `".EQDKP_TABLE_PREFIX."users` ".
                                                  "WHERE user_id = :UserId LIMIT 1");
                                              
                    $UserSt->bindValue(":UserId", $_SESSION["User"]["ExternalId"], PDO::PARAM_INT);
                    $UserSt->execute();
                    
                    if ( $UserData = $UserSt->fetch( PDO::FETCH_ASSOC ) )
                    {
                        // Password or login changed?
                        
                        $parts = explode(":", $UserData["user_password"]);                        
                        UserProxy::CheckForBindingUpdate( $_SESSION["User"]["ExternalId"], $UserData["username"], $parts[0], "eqdkp", true, $parts[1] );
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
            // Check for the username in eqdkp
            
            $UserSt = $Connector->prepare("SELECT user_id, username, user_password ".
                                          "FROM `".EQDKP_TABLE_PREFIX."users` ".
                                          "WHERE username = :Login LIMIT 1");
            
            $UserSt->bindValue(":Login", $User["Login"], PDO::PARAM_STR);
            $UserSt->execute();
            
            if ( $ExternalUserData = $UserSt->fetch( PDO::FETCH_ASSOC ) )
            {
                // Found user in eqdkp
                
                $UserSt->closeCursor();                
                $hashedPassword = eqdkp_hash($User["Password"], $ExternalUserData["user_password"]);
                $parts = explode(":", $ExternalUserData["user_password"]);
                
                if ( $hashedPassword == $parts[0] )
                {
                    // password check validated
                    // Check if username or password changed for an existing binding
                    
                    if ( UserProxy::CheckForBindingUpdate($ExternalUserData["user_id"], strtolower($User["Login"]), $parts[0], "eqdkp", false, $parts[1]) )
                    {
                        return UserProxy::TryLoginUser($User["Login"], $parts[0], "eqdkp"); // ### return, user modified ###
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
                            break; // ### break, not active ###
                        }
                        
                        if ( (($Right["auth_value"] == "a_raid_add") || ($Right["auth_value"] == "a_raid_upd"))
                             && ($Right["auth_setting"] == "Y") )
                        {
                            $DefaultGroup = "raidlead";
                            break; // ### break, admin ###
                        }
                    }
        
                    // Insert user into native table
                    
                    UserProxy::CreateUser( $DefaultGroup, $ExternalUserData["user_id"], "eqdkp", $User["Login"], $parts[0], $parts[1] );
                    $Success = UserProxy::TryLoginUser( $User["Login"], $parts[0], "eqdkp" );
                
                    return $Success; // ### new user ###
                }
            }
            else
            {
                $UserSt->closeCursor();
            }
        }
        else if ( UserProxy::TryLoginUser($User["Login"], $User["Password"], "eqdkp") )
        {
            return true; // ### valid user ###
        }
        
        return false; // ### login failed ###
    }
?>