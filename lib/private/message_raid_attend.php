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

        $LockCheckQuery = $Connector->prepare("SELECT Stage,Mode,SlotsRole1,SlotsRole2,SlotsRole3,SlotsRole4,SlotsRole5 FROM `".RP_TABLE_PREFIX."Raid` WHERE RaidId = :RaidId LIMIT 1");
        $LockCheckQuery->bindValue(":RaidId", $RaidId, PDO::PARAM_INT);

        $RaidInfo = $LockCheckQuery->fetchFirst();
            
        if ( $RaidInfo == null )
            return; // ### return, locked ###
        
        $ChangeAllowed = $RaidInfo["Stage"] == "open";
        
        if ( $ChangeAllowed )
        {
            // Check if character matches user

            if ( $AttendanceIdx > 0)
            {
                $CheckQuery = $Connector->prepare("SELECT UserId, Role1 FROM `".RP_TABLE_PREFIX."Character` WHERE CharacterId = :CharacterId LIMIT 1");
                $CheckQuery->bindValue(":CharacterId", $AttendanceIdx, PDO::PARAM_INT);

                $CharacterInfo = $CheckQuery->fetchFirst();
                
                if ($CharacterInfo != null)
                {
                    $ChangeAllowed &= ($CharacterInfo["UserId"] == $UserId );
                    $Role = $CharacterInfo["Role1"];
                }
                else
                {
                    $ChangeAllowed = false;
                }
            }

            // update/insert new attendance data

            if ( $ChangeAllowed )
            {
                $MaxSlotCount = $RaidInfo["SlotsRole".($Role+1)];

                $CheckQuery = $Connector->prepare("SELECT UserId FROM `".RP_TABLE_PREFIX."Attendance` WHERE UserId = :UserId AND RaidId = :RaidId LIMIT 1");
                $CheckQuery->bindValue(":UserId", $UserId, PDO::PARAM_INT);
                $CheckQuery->bindValue(":RaidId", $RaidId, PDO::PARAM_INT);
                $CheckQuery->execute();

                $AttendQuery = null;
                $ChangeComment = isset($aRequest["comment"]) && ($aRequest["comment"] != "");

                if ( $CheckQuery->getAffectedRows() > 0 )
                {
                    if ( $ChangeComment  )
                    {
                        $AttendQuery = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."Attendance` SET ".
                                                        "CharacterId = :CharacterId, Status = :Status, Role = :Role, Comment = :Comment, LastUpdate = FROM_UNIXTIME(:Timestamp) ".
                                                        "WHERE RaidId = :RaidId AND UserId = :UserId LIMIT 1" );
                    }
                    else
                    {
                        $AttendQuery = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."Attendance` SET ".
                                                        "CharacterId = :CharacterId, Status = :Status, Role = :Role, LastUpdate = FROM_UNIXTIME(:Timestamp) ".
                                                        "WHERE RaidId = :RaidId AND UserId = :UserId LIMIT 1" );                            
                    }
                }
                else
                {
                    if ( $ChangeComment )
                    {
                        $AttendQuery = $Connector->prepare("INSERT INTO `".RP_TABLE_PREFIX."Attendance` ( CharacterId, UserId, RaidId, Status, Role, Comment, LastUpdate ) ".
                                                        "VALUES ( :CharacterId, :UserId, :RaidId, :Status, :Role, :Comment, FROM_UNIXTIME(:Timestamp) )" );
                    }
                    else
                    {
                        $AttendQuery = $Connector->prepare("INSERT INTO `".RP_TABLE_PREFIX."Attendance` ( CharacterId, UserId, RaidId, Status, Role, Comment, LastUpdate) ".
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
                    case "attend":
                        $Status = "ok";
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
                    $AttendQuery->bindValue(":Comment", $Comment, PDO::PARAM_INT);
                }
                
                $AttendQuery->bindValue(":CharacterId", $CharacterId, PDO::PARAM_INT);
                $AttendQuery->bindValue(":RaidId",      $RaidId,      PDO::PARAM_INT);
                $AttendQuery->bindValue(":UserId",      $UserId,      PDO::PARAM_INT);
                $AttendQuery->bindValue(":Status",      $Status,      PDO::PARAM_STR);
                $AttendQuery->bindValue(":Role",        $Role,        PDO::PARAM_INT);
                $AttendQuery->bindValue(":Timestamp",   time(),       PDO::PARAM_INT);

                if ( $AttendQuery->execute() && 
                     ($RaidInfo["Mode"] == "attend") && 
                     ($Status == "ok") )
                {
                    // Check constraints for auto-attend
                    // This fixes a rare race condition where two (or more) players attend
                    // the last available slot at the same time.

                    $AttendenceQuery = $Connector->prepare("SELECT AttendanceId ".
                                                           "FROM `".RP_TABLE_PREFIX."Attendance` ".
                                                           "WHERE RaidId = :RaidId AND Status = \"ok\" AND Role = :RoleId ".
                                                           "ORDER BY AttendanceId DESC LIMIT :MaxCount" );

                    $AttendenceQuery->bindValue(":RaidId", $RaidId, PDO::PARAM_INT);
                    $AttendenceQuery->bindValue(":RoleId", $Role, PDO::PARAM_INT);
                    $AttendenceQuery->bindValue(":MaxCount", $MaxSlotCount, PDO::PARAM_INT);
                    
                    $LastAttend = $AttendenceQuery->fetchFirst();

                    if ( $AttendenceQuery->getAffectedRows() == $MaxSlotCount )
                    {
                        // Fix the overhead

                        $FixQuery = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."Attendance` SET Status = \"available\" ".
                                                        "WHERE RaidId = :RaidId AND Status = \"ok\" AND Role = :RoleId ".
                                                        "AND AttendanceId > :FirstId" );

                        $FixQuery->bindValue(":RaidId", $RaidId, PDO::PARAM_INT);
                        $FixQuery->bindValue(":RoleId", $Role, PDO::PARAM_INT);
                        $FixQuery->bindValue(":FirstId", $LastAttend["AttendanceId"], PDO::PARAM_INT);

                        $FixQuery->execute();
                    }
                }
                
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

        $RaidQuery = $Connector->prepare("SELECT Start FROM `".RP_TABLE_PREFIX."Raid` WHERE RaidId = :RaidId LIMIT 1");
        $RaidQuery->bindValue(":RaidId",  $RaidId, PDO::PARAM_INT);
        
        $RaidData = $RaidQuery->fetchFirst();

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