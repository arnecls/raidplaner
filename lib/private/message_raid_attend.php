<?php

function msgRaidAttend( $Request )
{
    if (ValidUser())
    {
        $Connector = Connector::GetInstance();
        
        $attendanceIdx = intval( $Request["attendanceIndex"] );
        $raidId = intval( $Request["raidId"] );
        $userId = intval( $_SESSION["User"]["UserId"] );
        
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
                    
                    if ( $CheckSt->rowCount() > 0 )
                    {
                        $attendSt = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."Attendance` SET ".
                            "CharacterId = :CharacterId, Status = :Status, Role = :Role, Comment = :Comment ".
                            "WHERE RaidId = :RaidId AND UserId = :UserId LIMIT 1" );
                    }
                    else
                    {
                        $attendSt = $Connector->prepare("INSERT INTO `".RP_TABLE_PREFIX."Attendance` ( CharacterId, UserId, RaidId, Status, Role, Comment ) ".
                            "VALUES ( :CharacterId, :UserId, :RaidId, :Status, :Role, :Comment )" );
                    }
                    
                    // Define the status and id to set
                    
                    if ( $attendanceIdx == -1 )
                    {
                        $status = "unavailable";
                        $characterId = intval( $Request["fallback"] );
                    }
                    else
                    {
                        switch ( $raidInfo["Mode"] )
                        {                        
                        case "all":
                            $status = "ok";
                            break;
                            
                        case "attend":
                            // Gather slot usage
                            
                            $AttendanceSt = $Connector->prepare("SELECT Role, COUNT(Role) AS Count FROM `".RP_TABLE_PREFIX."Attendance` WHERE RaidId = :RaidId GROUP BY Role");
                            $AttendanceSt->bindValue(":RaidId", $raidId, PDO::PARAM_INT);
        
                            if ( !$AttendanceSt->execute() )
                            {
                                postErrorMessage( $AttendanceSt );
                                $AttendanceSt->closeCursor();
                                return;
                            }
                            
                            $SlotUsage = Array();
        
                            while ( $Data = $AttendanceSt->fetch( PDO::FETCH_ASSOC ) )
                            {
                                $SlotCount[ $Data["Role"] ] = $Data["Count"];
                            }
                            
                            $AttendanceSt->closeCursor();
        
                            if ( $SlotCount[$role] < $raidInfo["SlotsRole".($role+1)] )
                            {
                                $status = "ok";
                            }
                            else
                            {
                                $status = "available";
                            }
                            break;
                            
                        default:
                        case "manual":
                            $status = "available";
                            break;
                        }
                        
                        $characterId = $attendanceIdx;
                    }
                    
                    // Define comment
                    
                    $comment = "";
                    
                    if (isset($Request["comment"]))
                    {
                        $comment = xmlentities( $Request["comment"], ENT_COMPAT, "UTF-8" );
                    }
                    
                    $attendSt->bindValue(":CharacterId", $characterId, PDO::PARAM_INT);
                    $attendSt->bindValue(":RaidId",      $raidId,      PDO::PARAM_INT);
                    $attendSt->bindValue(":UserId",      $userId,      PDO::PARAM_INT);
                    $attendSt->bindValue(":Status",      $status,      PDO::PARAM_STR);
                    $attendSt->bindValue(":Role",        $role,        PDO::PARAM_INT);
                    $attendSt->bindValue(":Comment",     $comment,     PDO::PARAM_STR);
                    
                    if (!$attendSt->execute())
                    {
                        postErrorMessage( $attendSt );
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