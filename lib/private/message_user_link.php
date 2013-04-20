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
                        echo "<group>".$userInfo->Group."</group>";
                    }
                    
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