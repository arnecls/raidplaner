<?php

function tryGetUserLink( $UserId )
{
    $Connector = Connector::GetInstance();
    $UserProxy = UserProxy::GetInstance();
    
    $UserSt = $Connector->prepare("Select * FROM `".RP_TABLE_PREFIX."User` WHERE UserId=:UserId LIMIT 1");
    $UserSt->bindValue( ":UserId", $UserId, PDO::PARAM_INT );
    
    if ( !$UserSt->execute() )
    {
        postErrorMessage( $UserSt );
        $UserSt->closeCursor();
        
        return null; // ### return, failed ###
    }
    
    // Try to find a fitting binding
    
    $UserData = $UserSt->fetch( PDO::FETCH_ASSOC );
    $UserSt->closeCursor();
    
    // External binding is still set.
    // Finding the user is trivial
    
    if ($UserData["ExternalBinding"] != "none")
    {
        return $UserProxy->GetUserInfoById($UserData["ExternalBinding"], $UserData["ExternalId"]); // ### return, success ###
    }
    
    // External id is still set.
    // Finding the user is trivial if there is only one binding
    
    if ( $UserData["ExternalId"] != 0 )
    {
        $candidates = $UserProxy->GetAllUserInfosById($UserData["ExternalId"]);
        
        if ( sizeof($candidates) > 1 )
        {
            // More than one binding, check the username and
            // reduce the array to username matches
            
            $filtered = array();
            
            while( list($bindingName, $userInfo) = each($candidate) )
            {
                if ( $userInfo->UserName == $UserData["Login"] )
                {
                    $filtered[$bindingName] = $userInfo;
                }
            }
            
            // If filtering was successfull, switch arrays
            
            if ( sizeof($filtered) > 0 )
                $candidates = $filtered;
            else
                reset($candidates);
        }  
        
        // Use the first match. Having multiple matches is very unlikely as two (or more)
        // forums need to have a user with the same username AND id.
                        
        if ( sizeof($candidates) > 0 )
        {
            list($bindingName, $userInfo) = each($candidates); // fetch the first entry
            return $userInfo; // ### return, success ###
        }
    }
    
    // All checks failed
    // Search for user by name
    
    $candidates = $UserProxy->GetAllUserInfosByName($UserData["Login"]);
        
    // Use the first match.
    // This may lead to the wrong user, but searching by name is basically wild guessing anyway.
    // Note that there is always at least one candidate with the binding "none".
    
    if ( sizeof($candidates) > 1 )
    {
        list($bindingName, $userInfo) = each($candidates); // first entry is "none"
        list($bindingName, $userInfo) = each($candidates); // this is the first external binding
        
        return $userInfo; // ### return, success ###
    }
    
    return null;
}

// -----------------------------------------------------------------------------

function msgUserLink( $Request )
{
    if ( ValidAdmin() )
    {
        $userInfo = tryGetUserLink($Request["userId"]);
        
        if ( $userInfo != null )
        {   
            echo "<userid>".$Request["userId"]."</userid>";
            echo "<binding>".$userInfo->BindingName."</binding>";
            echo "<group>".$userInfo->Group."</group>";
        }
    }
    else
    {
        echo "<error>".L("AccessDenied")."</error>";
    }
}

?>