<?php

    function updateGroup( $Connector, $GroupName, $IdArray )
    {
        $UserGroup = $Connector->prepare( "SELECT UserId FROM `".RP_TABLE_PREFIX."User` WHERE `Group` = :GroupName" );
        $UserGroup->bindValue(":GroupName", $GroupName, PDO::PARAM_STR );
    
        $CurrentGroupIds = Array();
        $UserGroup->loop( function($User) use (&$CurrentGroupIds)
        {
            array_push( $CurrentGroupIds, intval($User["UserId"]) );
        });
    
        $ChangedIds = array_diff( $IdArray, $CurrentGroupIds ); // new ids
    
        foreach ( $ChangedIds as $UserId )
        {
            $ChangeUser = $Connector->prepare( "UPDATE `".RP_TABLE_PREFIX."User` SET `Group` = :GroupName WHERE UserId = :UserId " );
            $ChangeUser->bindValue(":UserId", $UserId, PDO::PARAM_INT);
            $ChangeUser->bindValue(":GroupName", $GroupName, PDO::PARAM_STR );
    
            if ( !$ChangeUser->execute() )
            {
                $Connector->rollBack();
                return false; // ### return, error ###
            }
        }
    
        return true;
    }
    
    // -----------------------------------------------------------------------------
    
    function generateQueryStringInt( $CurrentValues, &$BindValues, $ValueName, $NewValue )
    {
        if ( isset($CurrentValues[$ValueName]) )
        {
            if ( $CurrentValues[$ValueName]["number"] != $NewValue )
            {
                array_push( $BindValues, array(":".$ValueName, $NewValue, PDO::PARAM_INT) );
                return "UPDATE `".RP_TABLE_PREFIX."Setting` SET IntValue = :".$ValueName." WHERE Name=\"".$ValueName."\"; ";
            }
        }
        else
        {
            array_push( $BindValues, array(":".$ValueName, $NewValue, PDO::PARAM_INT) );
            return "INSERT INTO `".RP_TABLE_PREFIX."Setting` (`Name`, `IntValue`, `TextValue`) VALUES ('".$ValueName."', :".$ValueName.", ''); ";
        }
    
        return "";
    }
    
    // -----------------------------------------------------------------------------
    
    function generateQueryStringText( $CurrentValues, &$BindValues, $ValueName, $NewValue )
    {
        if ( isset($CurrentValues[$ValueName]) )
        {
            if ( $CurrentValues[$ValueName]["text"] != $NewValue )
            {
                array_push( $BindValues, array(":".$ValueName, $NewValue, PDO::PARAM_STR) );
                return "UPDATE `".RP_TABLE_PREFIX."Setting` SET TextValue = :".$ValueName." WHERE Name=\"".$ValueName."\"; ";
            }
        }
        else
        {
            array_push( $BindValues, array(":".$ValueName, $NewValue, PDO::PARAM_STR) );
            return "INSERT INTO `".RP_TABLE_PREFIX."Setting` (`Name`, `IntValue`, `TextValue`) VALUES ('".$ValueName."', 0, :".$ValueName."); ";
        }
    
        return "";
    }
    
    // -----------------------------------------------------------------------------
    
    function msgSettingsupdate( $aRequest )
    {
        if ( validAdmin() )
        {
            $Connector = Connector::getInstance();
    
            // Update settings
            
            $Settings = Settings::getInstance();
            
            $Settings["PurgeRaids"]["IntValue"]      = $aRequest["purgeTime"];
            $Settings["LockRaids"]["IntValue"]       = $aRequest["lockTime"];
            $Settings["TimeFormat"]["IntValue"]      = $aRequest["timeFormat"];
            $Settings["StartOfWeek"]["IntValue"]     = $aRequest["startOfWeek"];
            $Settings["RaidStartHour"]["IntValue"]   = $aRequest["raidStartHour"];
            $Settings["RaidStartMinute"]["IntValue"] = $aRequest["raidStartMinute"];
            $Settings["RaidEndHour"]["IntValue"]     = $aRequest["raidEndHour"];
            $Settings["RaidEndMinute"]["IntValue"]   = $aRequest["raidEndMinute"];
            $Settings["RaidSize"]["IntValue"]        = $aRequest["raidSize"];            
            
            $Settings["RaidMode"]["TextValue"]       = $aRequest["raidMode"];
            $Settings["Site"]["TextValue"]           = $aRequest["site"];
            $Settings["Theme"]["TextValue"]          = $aRequest["theme"];
            $Settings["GameConfig"]["TextValue"]     = $aRequest["game"];
            $Settings["HelpPage"]["TextValue"]       = $aRequest["helpPage"];
            
            $Settings->serialize();
                
            do
            {
                // Update locations
            
                $Connector->beginTransaction();
    
                $ExistingLocations = $Connector->prepare( "SELECT * FROM `".RP_TABLE_PREFIX."Location`" );
        
                $CurrentValues = array();
                $ExistingLocations->loop( function($Data) use (&$CurrentValues)
                {
                    $CurrentValues[$Data["LocationId"]] = array( "Name" => $Data["Name"], "Image" => $Data["Image"] );
                });
        
                $QueryString = "";
                $BindValues = array();
        
                // Build location query
        
                if (isset($aRequest["locationIds"]))
                {
                    for ( $i=0; $i < count($aRequest["locationIds"]); ++$i )
                    {
                        $LocationId      = intval($aRequest["locationIds"][$i]);
                        $CurrentLocation = $CurrentValues[$LocationId];
                        $LocationName    = requestToXML( $aRequest["locationNames"][$i], ENT_COMPAT, "UTF-8" );
                        $LocationImage   = ( isset($aRequest["locationImages"]) && isset($aRequest["locationImages"][$i]) && ($aRequest["locationImages"][$i] != "undefined") )
                            ? $aRequest["locationImages"][$i]
                            : $CurrentLocation["Image"];
        
                        if ( ($LocationName != $CurrentLocation["Name"]) || ($LocationImage != $CurrentLocation["Image"]) )
                        {
                            array_push( $BindValues, array(":Name".$LocationId, $LocationName, PDO::PARAM_STR) );
                            array_push( $BindValues, array(":Image".$LocationId, $LocationImage, PDO::PARAM_STR) );
                            $QueryString .= "UPDATE `".RP_TABLE_PREFIX."Location` SET Name = :Name".$LocationId.", Image = :Image".$LocationId." WHERE LocationId=".$LocationId."; ";
                        }
                    }
                }
        
                if ( isset($aRequest["locationRemoved"]) )
                {
                    foreach( $aRequest["locationRemoved"] as $LocationId )
                    {
                        $QueryString .= "DELETE `".RP_TABLE_PREFIX."Location`, `".RP_TABLE_PREFIX."Raid`, `".RP_TABLE_PREFIX."Attendance` FROM `".RP_TABLE_PREFIX."Location` ".
                                        "LEFT JOIN `".RP_TABLE_PREFIX."Raid` USING(LocationId) ".
                                        "LEFT JOIN `".RP_TABLE_PREFIX."Attendance` USING(RaidId) ".
                                        " WHERE LocationId=".intval($LocationId)."; ";
                    }
                }
        
                if ( $QueryString != "" )
                {
                    $LocationUpdate = $Connector->prepare( $QueryString );
        
                    foreach( $BindValues as $BindData )
                    {
                        $LocationUpdate->bindValue( $BindData[0], $BindData[1], $BindData[2] );
                    }
        
                    if ( !$LocationUpdate->execute() )
                    {
                        $Connector->rollBack();
                        return; // ### return, error ###
                    }
                }
        
                // Update users and groups
        
                $BannedIds   = (isset($aRequest["banned"]))   ? $aRequest["banned"]   : array();
                $MemberIds   = (isset($aRequest["member"]))   ? $aRequest["member"]   : array();
                $RaidleadIds = (isset($aRequest["raidlead"])) ? $aRequest["raidlead"] : array();
                $AdminIds    = (isset($aRequest["admin"]))    ? $aRequest["admin"]    : array();
                $RemovedIds  = (isset($aRequest["removed"]))  ? $aRequest["removed"]  : array();
                $UnlinkedIds = (isset($aRequest["unlinked"])) ? $aRequest["unlinked"]  : array();
                $RelinkedIds = (isset($aRequest["relinked"])) ? $aRequest["relinked"]  : array();
        
                if ( !updateGroup( $Connector, "none", $BannedIds ) )
                    return;
        
                if ( !updateGroup( $Connector, "member", $MemberIds ) )
                    return;
        
                if ( !updateGroup( $Connector, "raidlead", $RaidleadIds ) )
                    return;
        
                if ( !updateGroup( $Connector, "admin", $AdminIds ) )
                    return;
        
                // Update unlinked users
        
                foreach ( $UnlinkedIds as $UserId )
                {
                    $UnlinkUser = $Connector->prepare( "UPDATE `".RP_TABLE_PREFIX."User` SET `BindingActive` = 'false' WHERE UserId = :UserId LIMIT 1" );
                    $UnlinkUser->bindValue(":UserId", $UserId, PDO::PARAM_INT);
        
                    if ( !$UnlinkUser->execute() )
                    {
                        $Connector->rollBack();
                        return; // ### return, error ###
                    }
                }
        
                // Update relinked users
        
                foreach ( $RelinkedIds as $UserId )
                {
                    $UserInfo = tryGetUserLink($UserId);
        
                    if ( $UserInfo != null )
                    {
                        $UpdateQuery = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."User` SET ".
                                                           "Password = :Password, Salt = :Salt, `Group` = :Group, ExternalBinding = :Binding, BindingActive = 'true' ".
                                                           "WHERE UserId = :UserId LIMIT 1" );
        
                        $UpdateQuery->bindValue( ":Password", $UserInfo->Password,    PDO::PARAM_STR );
                        $UpdateQuery->bindValue( ":Group",    $UserInfo->Group,       PDO::PARAM_STR );
                        $UpdateQuery->bindValue( ":Salt",     $UserInfo->Salt,        PDO::PARAM_STR );
                        $UpdateQuery->bindValue( ":Binding",  $UserInfo->BindingName, PDO::PARAM_STR );
                        $UpdateQuery->bindValue( ":UserId",   intval($UserId),        PDO::PARAM_INT );
        
                        if ( !$UpdateQuery->execute() )
                        {
                            $Connector->rollBack();
                            return; // ### return, error ###
                        }
                    }
                }
        
                // Update removed users
        
                foreach ( $RemovedIds as $UserId )
                {
                    // remove characters and attendances
        
                    $DropCharacter  = $Connector->prepare( "DELETE FROM `".RP_TABLE_PREFIX."Character` WHERE UserId = :UserId LIMIT 1" );
                    $DropAttendance = $Connector->prepare( "DELETE FROM `".RP_TABLE_PREFIX."Attendance` WHERE UserId = :UserId" );
        
                    $DropCharacter->bindValue(":UserId", $UserId, PDO::PARAM_INT);
                    $DropAttendance->bindValue(":UserId", $UserId, PDO::PARAM_INT);
        
                    if ( !$DropCharacter->execute() )
                    {
                        $Connector->rollBack();
                        return; // ### return, error ###
                    }
        
                    if ( !$DropAttendance->execute() )
                    {
                        $Connector->rollBack();
                        return; // ### return, error ###
                    }
        
                    // remove user
        
                    $DropUser = $Connector->prepare( "DELETE FROM `".RP_TABLE_PREFIX."User` WHERE UserId = :UserId LIMIT 1" );
                    $DropUser->bindValue(":UserId", $UserId, PDO::PARAM_INT);
        
                    if ( !$DropUser->execute() )
                    {
                        $Connector->rollBack();
                        return; // ### return, error ###
                    }
                }
            }
            while (!$Connector->commit());
    
            msgQuerySettings( $aRequest );
        }
        else
        {
            $Out = Out::getInstance();
            $Out->pushError(L("AccessDenied"));
        }
    }

?>