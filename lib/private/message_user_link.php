<?php

function tryGetUserLink( $UserId )
{
    $Connector = Connector::getInstance();
    $UserProxy = UserProxy::getInstance();

    $UserQuery = $Connector->prepare("Select * FROM `".RP_TABLE_PREFIX."User` WHERE UserId=:UserId LIMIT 1");
    $UserQuery->bindValue( ":UserId", $UserId, PDO::PARAM_INT );
    $UserData = $UserQuery->fetchFirst();

    if ( $UserData == null )
        return null; // ### return, failed ###

    // Try to find a fitting binding
    // External binding is still set.
    // Finding the user is trivial

    if ($UserData["ExternalBinding"] != "none")
    {
        return $UserProxy->getUserInfoById($UserData["ExternalBinding"], $UserData["ExternalId"]); // ### return, success ###
    }

    // External id is still set.
    // Finding the user is trivial if there is only one binding

    if ( $UserData["ExternalId"] != 0 )
    {
        $Candidates = UserProxy::getAllUserInfosById($UserData["ExternalId"]);

        if ( sizeof($Candidates) > 1 )
        {
            // More than one binding, check the username and
            // reduce the array to username matches

            $Filtered = array();

            while( list($BindingName, $UserInfo) = each($Candidates) )
            {
                if ( $UserInfo->UserName == $UserData["Login"] )
                {
                    $Filtered[$BindingName] = $UserInfo;
                }
            }

            // If filtering was successfull, switch arrays

            if ( sizeof($Filtered) > 0 )
                $Candidates = $Filtered;
            else
                reset($Candidates);
        }

        // Use the first match. Having multiple matches is very unlikely as two (or more)
        // forums need to have a user with the same username AND id.

        if ( sizeof($Candidates) > 0 )
        {
            list($BindingName, $UserInfo) = each($Candidates); // fetch the first entry
            return $UserInfo; // ### return, success ###
        }
    }

    // All checks failed
    // Search for user by name

    $Candidates = $UserProxy->getAllUserInfosByName($UserData["Login"]);

    // Use the first match.
    // This may lead to the wrong user, but searching by name is basically wild guessing anyway.
    // Note that there is always at least one candidate with the binding "none".

    if ( sizeof($Candidates) > 1 )
    {
        list($BindingName, $UserInfo) = each($Candidates); // first entry is "none"
        list($BindingName, $UserInfo) = each($Candidates); // this is the first external binding

        return $UserInfo; // ### return, success ###
    }

    return null;
}

// -----------------------------------------------------------------------------

function msgUserLink( $aRequest )
{
    $Out = Out::getInstance();

    if ( validAdmin() )
    {
        $UserInfo = tryGetUserLink($aRequest["userId"]);

        if ( $UserInfo != null )
        {
            $Out->pushValue("syncActive", !defined("ALLOW_GROUP_SYNC") || ALLOW_GROUP_SYNC);

            $Out->pushValue("userid",     $aRequest["userId"]);
            $Out->pushValue("binding",    $UserInfo->BindingName);
            $Out->pushValue("group",      $UserInfo->Group);
        }
    }
    else
    {
        $Out->pushError(L("AccessDenied"));
    }
}

?>