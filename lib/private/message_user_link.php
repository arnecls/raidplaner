<?php

function msgUserLink( $Request )
{
    if ( ValidAdmin() )
    {
        $Connector = Connector::GetInstance();
    
        $UserSt = $Connector->prepare("Select * FROM `".RP_TABLE_PREFIX."User` WHERE UserId=:UserId LIMIT 1");
        $UserSt->bindValue( ":UserId", $Request["userId"], PDO::PARAM_INT );
        
        if ( !$UserSt->execute() )
        {
            postErrorMessage( $UserSt );
        }
        else
        {
            $UserData = $UserSt->fetch( PDO::FETCH_ASSOC );
            $UserSt->closeCursor();
            
            // External binding is still set.
            // Finding the user is trivial
            
            if ($UserData["ExternalBinding"] != "none")
            {
                // Use binding from database
                
                $userInfo = UserProxy::GetInstance()->GetUserInfoById($UserData["ExternalBinding"], $UserData["ExternalId"]);
                if ($userInfo != null)
                {
                    $UpdateSt = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."User` SET BindingActive='true', `Group`=:Group WHERE UserId=:UserId LIMIT 1");
                    
                    $UpdateSt->bindValue( ":Group",  $userInfo->Group, PDO::PARAM_STR );                        
                    $UpdateSt->bindValue( ":UserId", $Request["userId"], PDO::PARAM_INT );
                    
                    if ( !$UpdateSt->execute() )
                    {
                        postErrorMessage( $UpdateSt );
                    }
                    else
                    {
                        echo "<userid>".intval($Request["userId"])."</userid>";
                        echo "<binding>".$UserData["ExternalBinding"]."</binding>";
                        echo "<group>".$userInfo->Group."</group>";
                    }
                    
                    $UpdateSt->closeCursor();
                    return; // ### return, success ###
                }
            }
            
            // External id is still set.
            // Finding the user is trivial if there is only one binding
            
            if ( $UserData["ExternalId"] != 0 )
            {
                $candidates = UserProxy::GetInstance()->GetAllUserInfosById($UserData["ExternalId"]);
                
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
                    
                    $UpdateSt = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."User` SET BindingActive='true', ExternalBinding=:Binding, `Group`=:Group WHERE UserId=:UserId LIMIT 1");
                    
                    $UpdateSt->bindValue( ":Binding", $bindingName, PDO::PARAM_STR );                        
                    $UpdateSt->bindValue( ":Group",  $userInfo->Group, PDO::PARAM_STR );                        
                    $UpdateSt->bindValue( ":UserId", $Request["userId"], PDO::PARAM_INT );
                    
                    if ( !$UpdateSt->execute() )
                    {
                        postErrorMessage( $UpdateSt );
                    }
                    else
                    {
                        echo "<userid>".intval($Request["userId"])."</userid>";
                        echo "<binding>".$bindingName."</binding>";
                        echo "<group>".$userInfo->Group."</group>";
                    }
                    
                    $UpdateSt->closeCursor();
                    return; // ### return, success ###
                }
            }
            
            // TODO:
            // Search for user
        }
    }
    else
    {
        echo "<error>".L("AccessDenied")."</error>";
    }
}

?>