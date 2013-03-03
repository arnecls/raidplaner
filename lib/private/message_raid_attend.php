<?php

function msgRaidAttend( $Request )
{
    if (ValidUser())
    {
        $Connector = Connector::GetInstance();

        $attendanceIdx = intval( $Request["attendanceIndex"] );
        $raidId = intval( $Request["raidId"] );
        $userId = intval( UserProxy::GetInstance()->UserId );

        // check user/character match

        $changeAllowed = true;
        $raidInfo = Array();
        $role = 0;

        // Check if locked

        $LockCheckSt = $Connector->prepare("SELECT Stage,Mode,SlotsRole1,SlotsRole2,SlotsRole3,SlotsRole4,SlotsRole5 FROM `".RP_TABLE_PREFIX."Raid` WHERE RaidId = :RaidId LIMIT 1");
        $LockCheckSt->bindValue(":RaidId", $raidId, PDO::PARAM_INT);

        if ( !$LockCheckSt->execute() )
        {
            postErrorMessage( $LockCheckSt );
            $LockCheckSt->closeCursor();
            return;
        }

        if ( $LockCheckSt->rowCount() > 0 )
        {
            $raidInfo = $LockCheckSt->fetch( PDO::FETCH_ASSOC );
            $changeAllowed = $raidInfo["Stage"] == "open";
        }

        $LockCheckSt->closeCursor();

        if ( $changeAllowed )
        {
            // Check if character matches user

            if ( $attendanceIdx > 0)
            {
                $CheckSt = $Connector->prepare("SELECT UserId, Role1 FROM `".RP_TABLE_PREFIX."Character` WHERE CharacterId = :CharacterId LIMIT 1");
                $CheckSt->bindValue(":CharacterId", $attendanceIdx, PDO::PARAM_INT);

                if ( !$CheckSt->execute() )
                {
                    postErrorMessage( $CheckSt );
                }
                else
                {
                    if ( $CheckSt->rowCount() > 0 )
                    {
                        $characterInfo = $CheckSt->fetch( PDO::FETCH_ASSOC );

                        $changeAllowed &= ($characterInfo["UserId"] == $userId );
                        $role = $characterInfo["Role1"];
                    }
                    else
                    {
                        $changeAllowed = false;
                    }
                }

                $CheckSt->closeCursor();
            }

            // update/insert new attendance data

            if ( $changeAllowed )
            {
                $MaxSlotCount = $raidInfo["SlotsRole".($role+1)];

                $CheckSt = $Connector->prepare("SELECT UserId FROM `".RP_TABLE_PREFIX."Attendance` WHERE UserId = :UserId AND RaidId = :RaidId LIMIT 1");
                $CheckSt->bindValue(":UserId", $userId, PDO::PARAM_INT);
                $CheckSt->bindValue(":RaidId", $raidId, PDO::PARAM_INT);

                if ( !$CheckSt->execute() )
                {
                    postErrorMessage( $CheckSt );
                }
                else
                {
                    $attendSt = null;
                    $changeComment = isset($Request["comment"]) && ($Request["comment"] != "");

                    if ( $CheckSt->rowCount() > 0 )
                    {
                        if ( $changeComment  )
                        {
                            $attendSt = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."Attendance` SET ".
                                                            "CharacterId = :CharacterId, Status = :Status, Role = :Role, Comment = :Comment ".
                                                            "WHERE RaidId = :RaidId AND UserId = :UserId LIMIT 1" );
                        }
                        else
                        {
                            $attendSt = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."Attendance` SET ".
                                                            "CharacterId = :CharacterId, Status = :Status, Role = :Role ".
                                                            "WHERE RaidId = :RaidId AND UserId = :UserId LIMIT 1" );                            
                        }
                    }
                    else
                    {
                        if ( $changeComment )
                        {
                            $attendSt = $Connector->prepare("INSERT INTO `".RP_TABLE_PREFIX."Attendance` ( CharacterId, UserId, RaidId, Status, Role, Comment ) ".
                                                            "VALUES ( :CharacterId, :UserId, :RaidId, :Status, :Role, :Comment )" );
                        }
                        else
                        {
                            $attendSt = $Connector->prepare("INSERT INTO `".RP_TABLE_PREFIX."Attendance` ( CharacterId, UserId, RaidId, Status, Role ) ".
                                                            "VALUES ( :CharacterId, :UserId, :RaidId, :Status, :Role )" );
                        }
                    }

                    // Define the status and id to set

                    if ( $attendanceIdx == -1 )
                    {
                        $status = "unavailable";
                        $characterId = intval( $Request["fallback"] );
                    }
                    else
                    
                    {
                        $characterId = $attendanceIdx;

                        switch ( $raidInfo["Mode"] )
                        {
                        case "all":
                            $status = "ok";
                            break;

                        case "attend":
                            $status = "ok";
                            // Gather slot usage

                            /*$AttendanceSt = $Connector->prepare("SELECT COUNT(Role) AS Count FROM `".RP_TABLE_PREFIX."Attendance` ".
                                                                "WHERE RaidId = :RaidId AND Role = :RoleId ".
                                                                "GROUP BY Role");

                            $AttendanceSt->bindValue(":RaidId", $raidId, PDO::PARAM_INT);
                            $AttendanceSt->bindValue(":RoleId", $role, PDO::PARAM_INT);

                            if ( !$AttendanceSt->execute() )
                            {
                                postErrorMessage( $AttendanceSt );
                                $AttendanceSt->closeCursor();
                                $status = "available";
                            }
                            else
                            {
                                if ( $Data = $AttendanceSt->fetch(PDO::FETCH_ASSOC) )
                                {
                                    if ( ($Data["Count"] == null) || $Data["Count"] < $MaxSlotCount )
                                        $status = "ok";
                                    else
                                        $status = "available";
                                }
                                else
                                {
                                    $status = "ok";
                                }

                                $AttendanceSt->closeCursor();
                            }*/
                            break;

                        default:
                        case "manual":
                            $status = "available";
                            break;
                        }
                    }

                    // Add comment when setting absent status

                    if ( $changeComment )
                    {
                        $comment = xmlentities( $Request["comment"], ENT_COMPAT, "UTF-8" );
                        $attendSt->bindValue(":Comment", $comment, PDO::PARAM_INT);
                    }
                    
                    $attendSt->bindValue(":CharacterId", $characterId, PDO::PARAM_INT);
                    $attendSt->bindValue(":RaidId",      $raidId,      PDO::PARAM_INT);
                    $attendSt->bindValue(":UserId",      $userId,      PDO::PARAM_INT);
                    $attendSt->bindValue(":Status",      $status,      PDO::PARAM_STR);
                    $attendSt->bindValue(":Role",        $role,        PDO::PARAM_INT);

                    if (!$attendSt->execute())
                    {
                        postErrorMessage( $attendSt );
                    }
                    else if ( ($raidInfo["Mode"] == "attend") && ($status == "ok") )
                    {
                        // Check constraints for auto-attend
                        // This fixes a rare race condition where two (or more) players attend
                        // the last available slot at the same time.

                        $AttendenceSt = $Connector->prepare("SELECT AttendanceId ".
                                                            "FROM `".RP_TABLE_PREFIX."Attendance` ".
                                                            "WHERE RaidId = :RaidId AND Status = \"ok\" AND Role = :RoleId ".
                                                            "ORDER BY AttendanceId" );

                        $AttendenceSt->bindValue(":RaidId", $raidId, PDO::PARAM_INT);
                        $AttendenceSt->bindValue(":RoleId", $role, PDO::PARAM_INT);

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

                            $FixSt->bindValue(":RaidId", $raidId, PDO::PARAM_INT);
                            $FixSt->bindValue(":RoleId", $role, PDO::PARAM_INT);
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

                    $attendSt->closeCursor();
                }

                $CheckSt->closeCursor();
            }
            else
            {
                echo "<error>".L("ForeignCharacter").". ".L("AccessDenied")."</error>";
            }
        }
        else
        {
            echo "<error>".L("RaidLocked")."</error>";
        }

        // reload calendar

        $RaidSt = $Connector->prepare("SELECT Start FROM `".RP_TABLE_PREFIX."Raid` WHERE RaidId = :RaidId LIMIT 1");
        $RaidSt->bindValue(":RaidId",  $raidId, PDO::PARAM_INT);
        $RaidSt->execute();

        $RaidData = $RaidSt->fetch( PDO::FETCH_ASSOC );

        $RaidSt->closeCursor();

        $showMonth = ( isset($_SESSION["Calendar"]) && isset($_SESSION["Calendar"]["month"]) ) ? $_SESSION["Calendar"]["month"]+1 : intval( substr( $RaidData["Start"], 5, 2 ) );
        $showYear  = ( isset($_SESSION["Calendar"]) && isset($_SESSION["Calendar"]["year"]) )  ? $_SESSION["Calendar"]["year"]    : intval( substr( $RaidData["Start"], 0, 4 ) );

        msgRaidCalendar( prepareRaidListRequest( $showMonth, $showYear ) );
    }
    else
    {
        echo "<error>".L("AccessDenied")."</error>";
    }
}

?>