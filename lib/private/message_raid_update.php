<?php

define("PlayerFlagModified", 1);
define("PlayerFlagNew",      1 << 1);
define("PlayerFlagCharId",   1 << 2);
define("PlayerFlagUserId",   1 << 3);
define("PlayerFlagName",     1 << 4);
define("PlayerFlagComment",  1 << 5);


define("PlayerFlagJustName", PlayerFlagName | PlayerFlagModified);

function msgRaidupdate( $aRequest )
{
    global $gRoles;

    if ( validRaidlead() )
    {
        $Connector = Connector::getInstance();

        // The whole update is packed into one transaction.
        // The transaction will be rolled back upon error so no half-updated
        // data is stored in the database. This requires the database to
        // support transactions.

        $Connector->beginTransaction();
        $LocationId = $aRequest["locationId"];

        // Insert new location if necessary

        if ( $LocationId == 0 )
        {
            $NewLocationQuery = $Connector->prepare("INSERT INTO `".RP_TABLE_PREFIX."Location`".
                                                    "(Name, Image) VALUES (:Name, :Image)");

            $NewLocationQuery->bindValue(":Name", requestToXML( $aRequest["locationName"], ENT_COMPAT, "UTF-8" ), PDO::PARAM_STR );
            $NewLocationQuery->bindValue(":Image", $aRequest["raidImage"], PDO::PARAM_STR );

            if (!$NewLocationQuery->execute())
            {
                $Connector->rollBack();
                return; // ### return, error ###
            }
            
            $LocationId = $Connector->lastInsertId();
        }

        // Update raid

        $UpdateRaidQuery = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."Raid` SET ".
                                               "LocationId = :LocationId, Size = :Size, ".
                                               "Stage = :Stage, ".
                                               "Start = FROM_UNIXTIME(:Start), End = FROM_UNIXTIME(:End), ".
                                               "Description = :Description, ".
                                               "Mode = :Mode, ".
                                               "SlotsRole1 = :SlotsRole1, SlotsRole2 = :SlotsRole2, SlotsRole3 = :SlotsRole3, SlotsRole4 = :SlotsRole4, SlotsRole5 = :SlotsRole5 ".
                                               "WHERE RaidId = :RaidId" );

        $StartDateTime = mktime(intval($aRequest["startHour"]), intval($aRequest["startMinute"]), 0, intval($aRequest["startMonth"]), intval($aRequest["startDay"]), intval($aRequest["startYear"]) );
        $EndDateTime   = mktime(intval($aRequest["endHour"]), intval($aRequest["endMinute"]), 0, intval($aRequest["endMonth"]), intval($aRequest["endDay"]), intval($aRequest["endYear"]) );

        // Convert to UTC

        $StartDateTime += $aRequest["startOffset"] * 60;
        $EndDateTime   += $aRequest["endOffset"] * 60; 
        
        $UpdateRaidQuery->bindValue(":RaidId",      $aRequest["id"], PDO::PARAM_INT);
        $UpdateRaidQuery->bindValue(":LocationId",  $LocationId, PDO::PARAM_INT);
        $UpdateRaidQuery->bindValue(":Stage",       $aRequest["stage"], PDO::PARAM_STR);
        $UpdateRaidQuery->bindValue(":Size",        $aRequest["locationSize"], PDO::PARAM_INT);
        $UpdateRaidQuery->bindValue(":Start",       $StartDateTime, PDO::PARAM_INT);
        $UpdateRaidQuery->bindValue(":End",         $EndDateTime, PDO::PARAM_INT);
        $UpdateRaidQuery->bindValue(":Mode",        $aRequest["mode"], PDO::PARAM_STR);
        $UpdateRaidQuery->bindValue(":Description", requestToXML( $aRequest["description"], ENT_COMPAT, "UTF-8" ), PDO::PARAM_STR);

        $SlotSizes = Array(
            intval($aRequest["slotsRole"][0]), intval($aRequest["slotsRole"][1]), intval($aRequest["slotsRole"][2]),
            intval($aRequest["slotsRole"][3]), intval($aRequest["slotsRole"][4])
        );

        // upload slot sizes

        $UpdateRaidQuery->bindValue(":SlotsRole1", $SlotSizes[0], PDO::PARAM_INT);
        $UpdateRaidQuery->bindValue(":SlotsRole2", $SlotSizes[1], PDO::PARAM_INT);
        $UpdateRaidQuery->bindValue(":SlotsRole3", $SlotSizes[2], PDO::PARAM_INT);
        $UpdateRaidQuery->bindValue(":SlotsRole4", $SlotSizes[3], PDO::PARAM_INT);
        $UpdateRaidQuery->bindValue(":SlotsRole5", $SlotSizes[4], PDO::PARAM_INT);

        if (!$UpdateRaidQuery->execute())
        {
            $Connector->rollBack();
            return; // ### return, error ###
        }
        
        // Remove the attends marked for delete.
        // Only random player attends can be removed.
        
        $NumRemoved = (isset($aRequest["removed"])) ? sizeof($aRequest["removed"]) : 0;

        for ( $i=0; $i<$NumRemoved; ++$i )
        {
            $RemoveSlot = $Connector->prepare( "DELETE FROM `".RP_TABLE_PREFIX."Attendance` ".
                                               "WHERE AttendanceId = :AttendanceId AND CharacterId = 0 AND UserId = 0" );

            $RemoveSlot->bindValue( ":AttendanceId", $aRequest["removed"][$i], PDO::PARAM_INT );
            
            if (!$RemoveSlot->execute())
            {
                $Connector->rollBack();
                return; // ### return, error ###
            }
        }

        // Now iterate over all role lists and update the players in it
        // Random player will be converted to "real" player, i.e. they loose their
        // negative pseudo-id.
        
        for ( $RoleIdx=0; $RoleIdx < sizeof($gRoles); ++$RoleIdx )
        {
            if ( isset($aRequest["role".($RoleIdx+1)]) )
            {
                $NumAttends = 0;
                $AttendsForRole = $aRequest["role".($RoleIdx+1)];

                // Attendances are passed in the form [id,status,id,status, … ]
                // So we iterate with a stride of 2

                for ( $AttendIdx=0; $AttendIdx < sizeof($AttendsForRole); )
                {
                    $UpdateSlot = null;
                    
                    // $Id = UserId when not having an attendance record
                    // $Id = AttendanceId for all others
                    $Id           = intVal($AttendsForRole[$AttendIdx++]);
                    $Status       = $AttendsForRole[$AttendIdx++];
                    $OldTimestamp = $AttendsForRole[$AttendIdx++];
                    $Flags        = intVal($AttendsForRole[$AttendIdx++]);
                    
                    if ( $Status == "undecided" )
                        continue; // ### continue, skip undecided ###
                    
                    // Get extra parameters
                    
                    if ( ($Flags & PlayerFlagCharId) != 0 )
                        $CharId = intVal($AttendsForRole[$AttendIdx++]);
                    
                    if ( ($Flags & PlayerFlagUserId) != 0 )
                        $UserId = intVal($AttendsForRole[$AttendIdx++]);
                    
                    if ( ($Flags & PlayerFlagName) != 0 )
                        $Name = $AttendsForRole[$AttendIdx++];
                                            
                    if ( ($Flags & PlayerFlagComment) != 0 )
                        $Comment = $AttendsForRole[$AttendIdx++];
                    
                    if ( ($Flags & PlayerFlagNew) != 0 )
                    {
                        // New entries
                        
                        if ( (($Flags & PlayerFlagComment) != 0) &&
                             (($Flags & PlayerFlagUserId) != 0) &&
                             (($Flags & PlayerFlagCharId) != 0) )
                        {
                            // Undecided set-up
                            
                            $UpdateSlot = $Connector->prepare( "INSERT INTO `".RP_TABLE_PREFIX."Attendance` ".
                                                               "( CharacterId, UserId, RaidId, Status, Role, Comment ) ".
                                                               "VALUES ( :CharId, :UserId, :RaidId, :Status, :Role, :Comment )" );

                            $UpdateSlot->bindValue( ":CharId", $CharId, PDO::PARAM_INT);
                            $UpdateSlot->bindValue( ":UserId", $UserId, PDO::PARAM_INT);
                            $UpdateSlot->bindValue( ":Comment", $Comment, PDO::PARAM_STR);
                            
                        }
                        else if ( ($Flags & PlayerFlagName) != 0 )
                        {
                            // Random player. Set name.
                                                        
                            $UpdateSlot = $Connector->prepare( "INSERT INTO `".RP_TABLE_PREFIX."Attendance` ".
                                                               "( CharacterId, UserId, RaidId, Status, Role, Comment ) ".
                                                               "VALUES ( 0, 0, :RaidId, :Status, :Role, :Name )" );

                            $UpdateSlot->bindValue( ":Name", $Name, PDO::PARAM_STR);
                        }                            
                        else
                        {
                            $Out = Out::getInstance();
                            $Out->pushError("Invalid user flags");
                        }                           
                    }
                    else
                    {
                        // Update existing entries
                        
                        if ( (($Flags & PlayerFlagComment) != 0) &&
                             (($Flags & PlayerFlagCharId) != 0) )
                        {
                            // Used when setting up an absent player
                            
                            $UpdateSlot = $Connector->prepare( "UPDATE `".RP_TABLE_PREFIX."Attendance` SET ".
                                                               "Status = :Status, CharacterId = :CharId, Comment = :Comment, Role = :Role, LastUpdate = FROM_UNIXTIME(:TimestampNow) ".
                                                               "WHERE RaidId = :RaidId AND LastUpdate = FROM_UNIXTIME(:LastUpdate) AND AttendanceId = :AttendanceId LIMIT 1" );

                            $UpdateSlot->bindValue( ":Comment", $Comment, PDO::PARAM_STR);
                            $UpdateSlot->bindValue( ":CharId", $CharId, PDO::PARAM_INT);
                        }
                        else if ( ($Flags & PlayerFlagCharId) != 0 )
                        {
                            // Used when changing a character
                            
                            $UpdateSlot = $Connector->prepare( "UPDATE `".RP_TABLE_PREFIX."Attendance` SET ".
                                                               "Status = :Status, CharacterId = :CharId, Role = :Role, LastUpdate = FROM_UNIXTIME(:TimestampNow) ".
                                                               "WHERE RaidId = :RaidId AND LastUpdate = FROM_UNIXTIME(:LastUpdate) AND AttendanceId = :AttendanceId LIMIT 1" );

                            $UpdateSlot->bindValue( ":CharId", $CharId, PDO::PARAM_INT);
                        }
                        else if ( (($Flags & PlayerFlagComment) != 0) )
                        {
                            // Used when setting a player to absent
                            
                            $UpdateSlot = $Connector->prepare( "UPDATE `".RP_TABLE_PREFIX."Attendance` SET ".
                                                               "Status = :Status, Comment = :Comment, Role = :Role, LastUpdate = FROM_UNIXTIME(:TimestampNow) ".
                                                               "WHERE RaidId = :RaidId AND LastUpdate = FROM_UNIXTIME(:LastUpdate) AND AttendanceId = :AttendanceId LIMIT 1" );

                            $UpdateSlot->bindValue( ":Comment", $Comment, PDO::PARAM_STR);
                        }
                        else if ( ($Flags & PlayerFlagName) != 0 )
                        {
                            // Used when changing the name of a random player
                        
                            $UpdateSlot = $Connector->prepare( "UPDATE `".RP_TABLE_PREFIX."Attendance` SET ".
                                                               "Status = :Status, Role = :Role, Comment = :Name, LastUpdate = FROM_UNIXTIME(:TimestampNow) ".
                                                               "WHERE RaidId = :RaidId AND LastUpdate = FROM_UNIXTIME(:LastUpdate) AND AttendanceId = :AttendanceId LIMIT 1" );

                            $UpdateSlot->bindValue( ":Name",         $Name, PDO::PARAM_STR);
                        }
                        else
                        {
                            // Existing player, update
                            
                            $UpdateSlot = $Connector->prepare( "UPDATE `".RP_TABLE_PREFIX."Attendance` SET ".
                                                               "Status = :Status, Role = :Role, LastUpdate = FROM_UNIXTIME(:TimestampNow) ".
                                                               "WHERE RaidId = :RaidId AND LastUpdate = FROM_UNIXTIME(:LastUpdate) AND AttendanceId = :AttendanceId LIMIT 1" );
                        }
                        
                        $UpdateSlot->bindValue( ":AttendanceId", $Id, PDO::PARAM_INT);
                        $UpdateSlot->bindValue( ":LastUpdate", $OldTimestamp, PDO::PARAM_INT);
                        $UpdateSlot->bindValue( ":TimestampNow", time(), PDO::PARAM_INT);
                    }
                    

                    $UpdateSlot->bindValue( ":Status", $Status, PDO::PARAM_STR);
                    $UpdateSlot->bindValue( ":RaidId", $aRequest["id"], PDO::PARAM_INT);
                    $UpdateSlot->bindValue( ":Role",   $RoleIdx, PDO::PARAM_INT);

                    if (!$UpdateSlot->execute())
                    {
                        $Connector->rollBack();
                        return; // ### return, error ###
                    }
                }
            }
        }
        
        // Assure mode constraints

        if ( $aRequest["mode"] == "all" )
        {
            // Mode "all" means all players are either "ok" or "unavailable"
            
            $AttendenceQuery = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."Attendance` SET Status = \"ok\" ".
                                                "WHERE RaidId = :RaidId AND Status = \"available\"" );

            $AttendenceQuery->bindValue(":RaidId", $aRequest["id"], PDO::PARAM_INT);

            if (!$AttendenceQuery->execute())
            {
                $Connector->rollBack();
                return; // ### return, error ###
            }
        }
        else if ( $aRequest["mode"] != "overbook" )
        {
            // Assure there not more "ok" players than allowed by slot size

            for ( $RoleId=0; $RoleId<sizeof($SlotSizes); ++$RoleId )
            {
                if ( $SlotSizes[$RoleId] > 0 )
                {
                    $AttendenceQuery = $Connector->prepare("SELECT AttendanceId ".
                                                           "FROM `".RP_TABLE_PREFIX."Attendance` ".
                                                           "WHERE RaidId = :RaidId AND Status = \"ok\" AND Role = :RoleId ".
                                                           "ORDER BY AttendanceId DESC LIMIT :MaxCount" );

                    $AttendenceQuery->bindValue(":RaidId", $aRequest["id"], PDO::PARAM_INT);
                    $AttendenceQuery->bindValue(":RoleId", $RoleId, PDO::PARAM_INT);
                    $AttendenceQuery->bindValue(":MaxCount", $SlotSizes[$RoleId], PDO::PARAM_INT);
                    
                    $LastAttend = $AttendenceQuery->fetchFirst();

                    if ( $AttendenceQuery->getAffectedRows() == $SlotSizes[$RoleId] )
                    {
                        // Fix the overhead
                        
                        $FixQuery = $Connector->prepare( "UPDATE `".RP_TABLE_PREFIX."Attendance` SET Status = \"available\" ".
                                                         "WHERE RaidId = :RaidId AND Status = \"ok\" AND Role = :RoleId ".
                                                         "AND AttendanceId > :FirstId" );

                        $FixQuery->bindValue(":RaidId", $aRequest["id"], PDO::PARAM_INT);
                        $FixQuery->bindValue(":RoleId", $RoleId, PDO::PARAM_INT);
                        $FixQuery->bindValue(":FirstId", $LastAttend["AttendanceId"], PDO::PARAM_INT);

                        if (!$FixQuery->execute())
                        {
                            $Connector->rollBack();
                            return; // ### return, error ###
                        }
                    }
                }
            }
        }

        $Connector->commit();
        
        // reload detailed view

        msgRaidDetail( $aRequest );
    }
    else
    {
        $Out = Out::getInstance();
        $Out->pushError(L("AccessDenied"));
    }
}

?>