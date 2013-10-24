<?php

function msgRaidAttend( $aRequest )
{
    if (validUser())
    {
        $Connector = Connector::getInstance();

        $AttendanceIdx = intval( $aRequest["attendanceIndex"] );
        $RaidId = intval( $aRequest["raidId"] );
        $UserId = intval( UserProxy::getInstance()->UserId );

        // check user/character match

        $ChangeAllowed = true;
        $RaidInfo = Array();
        $Role = 0;

        // Check if locked

        $LockCheckSt = $Connector->prepare("SELECT Stage,Mode,SlotsRole1,SlotsRole2,SlotsRole3,SlotsRole4,SlotsRole5 FROM `".RP_TABLE_PREFIX."Raid` WHERE RaidId = :RaidId LIMIT 1");
        $LockCheckSt->bindValue(":RaidId", $RaidId, PDO::PARAM_INT);

        if ( !$LockCheckSt->execute() )
        {
            postErrorMessage( $LockCheckSt );
            $LockCheckSt->closeCursor();
            return;
        }

        if ( $LockCheckSt->rowCount() > 0 )
        {
            $RaidInfo = $LockCheckSt->fetch( PDO::FETCH_ASSOC );
            $ChangeAllowed = $RaidInfo["Stage"] == "open";
        }

        $LockCheckSt->closeCursor();

        if ( $ChangeAllowed )
        {
            // Check if character matches user

            if ( $AttendanceIdx > 0)
            {
                $CheckSt = $Connector->prepare("SELECT UserId, Role1 FROM `".RP_TABLE_PREFIX."Character` WHERE CharacterId = :CharacterId LIMIT 1");
                $CheckSt->bindValue(":CharacterId", $AttendanceIdx, PDO::PARAM_INT);

                if ( !$CheckSt->execute() )
                {
                    postErrorMessage( $CheckSt );
                }
                else
                {
                    if ( $CheckSt->rowCount() > 0 )
                    {
                        $CharacterInfo = $CheckSt->fetch( PDO::FETCH_ASSOC );

                        $ChangeAllowed &= ($CharacterInfo["UserId"] == $UserId );
                        $Role = $CharacterInfo["Role1"];
                    }
                    else
                    {
                        $ChangeAllowed = false;
                    }
                }

                $CheckSt->closeCursor();
            }

            // update/insert new attendance data

            if ( $ChangeAllowed )
            {
                $MaxSlotCount = $RaidInfo["SlotsRole".($Role+1)];

                $CheckSt = $Connector->prepare("SELECT UserId FROM `".RP_TABLE_PREFIX."Attendance` WHERE UserId = :UserId AND RaidId = :RaidId LIMIT 1");
                $CheckSt->bindValue(":UserId", $UserId, PDO::PARAM_INT);
                $CheckSt->bindValue(":RaidId", $RaidId, PDO::PARAM_INT);

                if ( !$CheckSt->execute() )
                {
                    postErrorMessage( $CheckSt );
                }
                else
                {
                    $AttendSt = null;
                    $ChangeComment = isset($aRequest["comment"]) && ($aRequest["comment"] != "");

                    if ( $CheckSt->rowCount() > 0 )
                    {
                        if ( $ChangeComment  )
                        {
                            $AttendSt = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."Attendance` SET ".
                                                            "CharacterId = :CharacterId, Status = :Status, Role = :Role, Comment = :Comment, LastUpdate = FROM_UNIXTIME(:Timestamp) ".
                                                            "WHERE RaidId = :RaidId AND UserId = :UserId LIMIT 1" );
                        }
                        else
                        {
                            $AttendSt = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."Attendance` SET ".
                                                            "CharacterId = :CharacterId, Status = :Status, Role = :Role, LastUpdate = FROM_UNIXTIME(:Timestamp) ".
                                                            "WHERE RaidId = :RaidId AND UserId = :UserId LIMIT 1" );                            
                        }
                    }
                    else
                    {
                        if ( $ChangeComment )
                        {
                            $AttendSt = $Connector->prepare("INSERT INTO `".RP_TABLE_PREFIX."Attendance` ( CharacterId, UserId, RaidId, Status, Role, Comment, LastUpdate ) ".
                                                            "VALUES ( :CharacterId, :UserId, :RaidId, :Status, :Role, :Comment, FROM_UNIXTIME(:Timestamp) )" );
                        }
                        else
                        {
                            $AttendSt = $Connector->prepare("INSERT INTO `".RP_TABLE_PREFIX."Attendance` ( CharacterId, UserId, RaidId, Status, Role, Comment, LastUpdate) ".
                                                            "VALUES ( :CharacterId, :UserId, :RaidId, :Status, :Role, '', FROM_UNIXTIME(:Timestamp) )" );
                        }
                    }

                    // Define the status and id to set

                    if ( $AttendanceIdx == -1 )
                    {
                        $Status = "unavailable";
                        $CharacterId = intval( $aRequest["fallback"] );
                    }
                    else
                    
                    {
                        $CharacterId = $AttendanceIdx;

                        switch ( $RaidInfo["Mode"] )
                        {
                        case "all":
                            $Status = "ok";
                            break;

                        case "attend":
                            $Status = "ok";
                            // Gather slot usage

                            /*$AttendanceSt = $Connector->prepare("SELECT COUNT(Role) AS Count FROM `".RP_TABLE_PREFIX."Attendance` ".
                                                                "WHERE RaidId = :RaidId AND Role = :RoleId ".
                                                                "GROUP BY Role");

                            $AttendanceSt->bindValue(":RaidId", $RaidId, PDO::PARAM_INT);
                            $AttendanceSt->bindValue(":RoleId", $Role, PDO::PARAM_INT);

                            if ( !$AttendanceSt->execute() )
                            {
                                postErrorMessage( $AttendanceSt );
                                $AttendanceSt->closeCursor();
                                $Status = "available";
                            }
                            else
                            {
                                if ( $Data = $AttendanceSt->fetch(PDO::FETCH_ASSOC) )
                                {
                                    if ( ($Data["Count"] == null) || $Data["Count"] < $MaxSlotCount )
                                        $Status = "ok";
                                    else
                                        $Status = "available";
                                }
                                else
                                {
                                    $Status = "ok";
                                }

                                $AttendanceSt->closeCursor();
                            }*/
                            break;

                        default:
                        case "manual":
                        case "overbook":
                            $Status = "available";
                            break;
                        }
                    }

                    // Add comment when setting absent status

                    if ( $ChangeComment )
                    {
                        $Comment = requestToXML( $aRequest["comment"], ENT_COMPAT, "UTF-8" );
                        $AttendSt->bindValue(":Comment", $Comment, PDO::PARAM_INT);
                    }
                    
                    $AttendSt->bindValue(":CharacterId", $CharacterId, PDO::PARAM_INT);
                    $AttendSt->bindValue(":RaidId",      $RaidId,      PDO::PARAM_INT);
                    $AttendSt->bindValue(":UserId",      $UserId,      PDO::PARAM_INT);
                    $AttendSt->bindValue(":Status",      $Status,      PDO::PARAM_STR);
                    $AttendSt->bindValue(":Role",        $Role,        PDO::PARAM_INT);
                    $AttendSt->bindValue(":Timestamp",   time(),       PDO::PARAM_INT);

                    if (!$AttendSt->execute())
                    {
                        postErrorMessage( $AttendSt );
                    }
                    else if ( ($RaidInfo["Mode"] == "attend") && ($Status == "ok") )
                    {
                        // Check constraints for auto-attend
                        // This fixes a rare race condition where two (or more) players attend
                        // the last available slot at the same time.

                        $AttendenceSt = $Connector->prepare("SELECT AttendanceId ".
                                                            "FROM `".RP_TABLE_PREFIX."Attendance` ".
                                                            "WHERE RaidId = :RaidId AND Status = \"ok\" AND Role = :RoleId ".
                                                            "ORDER BY AttendanceId" );

                        $AttendenceSt->bindValue(":RaidId", $RaidId, PDO::PARAM_INT);
                        $AttendenceSt->bindValue(":RoleId", $Role, PDO::PARAM_INT);

                        if (!$AttendenceSt->execute())
                        {
                            postErrorMessage( $AttendenceSt );
                        }
                        else if ( $AttendenceSt->rowCount() > $MaxSlotCount )
                        {
                            // Get the last AttendanceId that is still valid

                            for ( $i=0; $i < $MaxSlotCount; ++$i )
                            {
                                $Data = $AttendenceSt->fetch(PDO::FETCH_ASSOC);
                            }

                            // Fix the overhead

                            $FixSt = $Connector->prepare( "UPDATE `".RP_TABLE_PREFIX."Attendance` SET Status = \"available\" ".
                                                          "WHERE RaidId = :RaidId AND Status = \"ok\" AND Role = :RoleId ".
                                                          "AND AttendanceId > :FirstId" );

                            $FixSt->bindValue(":RaidId", $RaidId, PDO::PARAM_INT);
                            $FixSt->bindValue(":RoleId", $Role, PDO::PARAM_INT);
                            $FixSt->bindValue(":FirstId", $Data["AttendanceId"], PDO::PARAM_INT);

                            if (!$FixSt->execute())
                            {
                                postErrorMessage( $FixSt );
                                $FixSt->closeCursor();
                                $Connector->rollBack();
                                return; // ### return, error ###
                            }

                            $FixSt->closeCursor();
                        }

                        $AttendenceSt->closeCursor();
                    }

                    $AttendSt->closeCursor();
                }

                $CheckSt->closeCursor();
            }
            else
            {
                $Out = Out::getInstance();
                $Out->pushError(L("AccessDenied"));
            }
        }
        else
        {
            $Out = Out::getInstance();
            $Out->pushError(L("RaidLocked"));
        }

        // reload calendar

        $RaidSt = $Connector->prepare("SELECT Start FROM `".RP_TABLE_PREFIX."Raid` WHERE RaidId = :RaidId LIMIT 1");
        $RaidSt->bindValue(":RaidId",  $RaidId, PDO::PARAM_INT);
        $RaidSt->execute();

        $RaidData = $RaidSt->fetch( PDO::FETCH_ASSOC );

        $RaidSt->closeCursor();

        $ShowMonth = ( isset($_SESSION["Calendar"]) && isset($_SESSION["Calendar"]["month"]) ) ? $_SESSION["Calendar"]["month"] : intval( substr( $RaidData["Start"], 5, 2 ) );
        $ShowYear  = ( isset($_SESSION["Calendar"]) && isset($_SESSION["Calendar"]["year"]) )  ? $_SESSION["Calendar"]["year"]  : intval( substr( $RaidData["Start"], 0, 4 ) );

        msgQueryCalendar( prepareCalRequest( $ShowMonth, $ShowYear ) );
    }
    else
    {
        $Out = Out::getInstance();
        $Out->pushError(L("AccessDenied"));
    }
}

?>