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
        $role = "dmg";
        
        // Check if locked
        
        $LockCheckSt = $Connector->prepare("SELECT Stage FROM `".RP_TABLE_PREFIX."Raid` WHERE RaidId = :RaidId LIMIT 1");
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
                    
                    $status = "available";
                    $characterId = $attendanceIdx;
                    
                    if ( $attendanceIdx == -1 )
                    {
                        $status = "unavailable";
                        $characterId = intval( $Request["fallback"] );
                    }
                    
                    $comment = "";
                    
                    if (isset($Request["comment"]))
                    {
                        $comment = xmlentities( $Request["comment"], ENT_COMPAT, "UTF-8" );
                    }
                    
                    $attendSt->bindValue(":CharacterId", $characterId, PDO::PARAM_INT);
                    $attendSt->bindValue(":RaidId",       $raidId,      PDO::PARAM_INT);
                    $attendSt->bindValue(":UserId",      $userId,      PDO::PARAM_INT);
                    $attendSt->bindValue(":Status",       $status,      PDO::PARAM_STR);
                    $attendSt->bindValue(":Role",           $role,        PDO::PARAM_STR);
                    $attendSt->bindValue(":Comment",       $comment,     PDO::PARAM_STR);
                    
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