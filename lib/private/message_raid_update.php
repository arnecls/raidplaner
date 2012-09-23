<?php
        
function msgRaidUpdate( $Request )
{
    global $s_Roles;
    
    if ( ValidRaidlead() )
    {
        $Connector = Connector::GetInstance();
        
        // The whole update is packed into one transaction.
        // The transaction will be rolled back upon error so no half-updated
        // data is stored in the database. This requires the database to
        // support transactions.
        
        $Connector->beginTransaction();
        $locationId = $Request["locationId"];
        
        
        // Insert new location if necessary
        
        if ( $locationId == 0 )
        {
            $NewLocationSt = $Connector->prepare("INSERT INTO `".RP_TABLE_PREFIX."Location`".
                                                 "(Name, Image) VALUES (:Name, :Image)");
                                                 
            $NewLocationSt->bindValue(":Name", requestToXML( $Request["locationName"], ENT_COMPAT, "UTF-8" ), PDO::PARAM_STR );
            $NewLocationSt->bindValue(":Image", $Request["raidImage"], PDO::PARAM_STR );
            
            if (!$NewLocationSt->execute())
            {
                postErrorMessage( $NewLocationSt );
                
                $NewLocationSt->closeCursor();
                $Connector->rollBack();
                return; // ### return, error ###
            }
            else
            {            
                $locationId = $Connector->lastInsertId();
            }
            
            $NewLocationSt->closeCursor();
        }
        
        // Update raid
        
        $UpdateRaidSt = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."Raid` SET ".
                                            "LocationId = :LocationId, Size = :Size, ".
                                            "Stage = :Stage, ".
                                            "Start = FROM_UNIXTIME(:Start), End = FROM_UNIXTIME(:End), ".
                                            "Description = :Description, ".
                                            "Mode = :Mode, ".
                                            "SlotsRole1 = :SlotsRole1, SlotsRole2 = :SlotsRole2, SlotsRole3 = :SlotsRole3, SlotsRole4 = :SlotsRole4, SlotsRole5 = :SlotsRole5 ".
                                            "WHERE RaidId = :RaidId" );
        
        $StartDateTime = mktime($Request["startHour"], $Request["startMinute"], 0, $Request["month"], $Request["day"], $Request["year"] );
        $EndDateTime   = mktime($Request["endHour"], $Request["endMinute"], 0, $Request["month"], $Request["day"], $Request["year"] );
        
        if ( $EndDateTime < $StartDateTime )
            $EndDateTime += 60*60*24;

        $UpdateRaidSt->bindValue(":RaidId",      $Request["id"], PDO::PARAM_INT);
        $UpdateRaidSt->bindValue(":LocationId",  $locationId, PDO::PARAM_INT);
        $UpdateRaidSt->bindValue(":Stage",       $Request["stage"], PDO::PARAM_STR);
        $UpdateRaidSt->bindValue(":Size",        $Request["locationSize"], PDO::PARAM_INT);
        $UpdateRaidSt->bindValue(":Start",       $StartDateTime, PDO::PARAM_INT);
        $UpdateRaidSt->bindValue(":End",         $EndDateTime, PDO::PARAM_INT);
        $UpdateRaidSt->bindValue(":Mode",        $Request["mode"], PDO::PARAM_STR);
        $UpdateRaidSt->bindValue(":Description", requestToXML( $Request["description"], ENT_COMPAT, "UTF-8" ), PDO::PARAM_STR);
        
        $SlotSizes = Array(
            intval($Request["slotsRole"][0]), intval($Request["slotsRole"][1]), intval($Request["slotsRole"][2]),
            intval($Request["slotsRole"][3]), intval($Request["slotsRole"][4])
        );
        
        // sanity check, at least one slot per role
        
        $numRoles = sizeof($s_Roles);
        $RaidSize = intval($Request["locationSize"]);
        
        for ( $i=0; $i<$numRoles; ++$i )
        {
            $MinSlotsRequired = $numRoles-($i+1);
            
            if ( $RaidSize - $SlotSizes[$i] < $MinSlotsRequired )
            {
                $SlotSizes[$i] = $RaidSize - $MinSlotsRequired;
            }
            
            $RaidSize -= $SlotSizes[$i];
        }
        
        // upload slot sizes
        
        $UpdateRaidSt->bindValue(":SlotsRole1", $SlotSizes[0], PDO::PARAM_INT);
        $UpdateRaidSt->bindValue(":SlotsRole2", $SlotSizes[1], PDO::PARAM_INT);
        $UpdateRaidSt->bindValue(":SlotsRole3", $SlotSizes[2], PDO::PARAM_INT);
        $UpdateRaidSt->bindValue(":SlotsRole4", $SlotSizes[3], PDO::PARAM_INT);
        $UpdateRaidSt->bindValue(":SlotsRole5", $SlotSizes[4], PDO::PARAM_INT);
        
        if (!$UpdateRaidSt->execute())
        {
            postErrorMessage( $UpdateRaidSt );        
            $UpdateRaidSt->closeCursor();
            $Connector->rollBack();
        }
        else
        {        
            $UpdateRaidSt->closeCursor();
            
            // Remove all random slots (no user id assignmend, so always re-inserted)
                
            $DeleteReserved = $Connector->prepare( "DELETE FROM `".RP_TABLE_PREFIX."Attendance` ".
                                                   "WHERE CharacterId = 0 AND UserId = 0 AND RaidId = :RaidId" );
                                                    
            $DeleteReserved->bindValue( ":RaidId", $Request["id"], PDO::PARAM_INT);
                    
            if (!$DeleteReserved->execute())
            {
                postErrorMessage( $DeleteReserved );
                $DeleteReserved->closeCursor();
                $Connector->rollBack();
                return; // ### return, errror ###
            }
            
            $DeleteReserved->closeCursor();
            
            // Now iterate over all role lists and update the players in it
            // Random player will be re-inserted, "real" players will be update if they
            // did not change to "unavailable" while editing the raid.
            
            for ( $RoleIdx=0; $RoleIdx < sizeof($s_Roles); ++$RoleIdx )
            {
                if ( isset($Request["role".($RoleIdx+1)]) )
                {
                    $AttendsForRole = $Request["role".($RoleIdx+1)];
                    
                    // Attendances are passed in the form [id,status,id,status, … ]
                    // So we iterate with a stride of 2
                    
                    for ( $AttendIdx=0; $AttendIdx < sizeof($AttendsForRole); $AttendIdx += 2 )
                    {
                        $UpdateSlot = null;
                        
                        if ( $AttendsForRole[$AttendIdx] < 0 )
                        {
                            // Random player, re-insert
                            // Random players have one additional information set, so we need to
                            // move $AttendIdx here.
                            
                            $UpdateSlot = $Connector->prepare( "INSERT INTO `".RP_TABLE_PREFIX."Attendance` ".
                                                               "( CharacterId, UserId, RaidId, Status, Role, Comment ) ".
                                                               "VALUES ( 0, 0, :RaidId, :Status, :Role, :Name )" );
                        
                            $UpdateSlot->bindValue( ":Name", $AttendsForRole[$AttendIdx+1], PDO::PARAM_STR);
                            $UpdateSlot->bindValue( ":Status", $AttendsForRole[$AttendIdx+2], PDO::PARAM_STR);
                            ++$AttendIdx;
                        }
                        else
                        {
                            // Real player, update if possible
                            
                            $UpdateSlot = $Connector->prepare( "UPDATE `".RP_TABLE_PREFIX."Attendance` SET ".
                                                               "Status = :Status, Role = :Role ".
                                                               "WHERE RaidId = :RaidId AND Status != 'unavialable' AND CharacterId = :CharacterId LIMIT 1" );
                                                                    
                            $UpdateSlot->bindValue( ":CharacterId", $AttendsForRole[$AttendIdx], PDO::PARAM_INT);
                            $UpdateSlot->bindValue( ":Status", $AttendsForRole[$AttendIdx+1], PDO::PARAM_STR);
                        }
                        
                        $UpdateSlot->bindValue( ":RaidId", $Request["id"], PDO::PARAM_INT);
                        $UpdateSlot->bindValue( ":Role", $RoleIdx, PDO::PARAM_INT);
                        
                        if (!$UpdateSlot->execute())
                        {
                            postErrorMessage( $UpdateSlot );
                            $UpdateSlot->closeCursor();
                            $Connector->rollBack();
                            return; // ### return, error ###
                        }
                        
                        $UpdateSlot->closeCursor();
                    }
                }
            }
            
            $Connector->commit();
        }
        
        // reload detailed view
        
        msgRaidDetail( $Request );
    }
    else
    {
        echo "<error>".L("AccessDenied")."</error>";
    }
}

?>