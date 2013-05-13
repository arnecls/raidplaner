<?php

function msgCommentUpdate( $Request )
{
    if ( ValidUser() )
    {
        $Connector = Connector::GetInstance();

        $raidId = intval( $Request["raidId"] );
        $userId = UserProxy::GetInstance()->UserId;

        $CheckSt = $Connector->prepare("SELECT UserId FROM `".RP_TABLE_PREFIX."Attendance` WHERE UserId = :UserId AND RaidId = :RaidId LIMIT 1");
        $CheckSt->bindValue(":UserId", $userId, PDO::PARAM_INT);
        $CheckSt->bindValue(":RaidId", $raidId, PDO::PARAM_INT);

        if ( !$CheckSt->execute() )
        {
            postErrorMessage( $CheckSt );
        }
        else
        {
            $UpdateSt = null;

            if ( $CheckSt->rowCount() > 0 )
            {
                $UpdateSt = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."Attendance` ".
                                                "SET comment = :Comment, LastUpdate = FROM_UNIXTIME(:Timestamp) ".
                                                "WHERE RaidId = :RaidId AND UserId = :UserId LIMIT 1" );
                
                $UpdateSt->bindValue(":Timestamp", time(), PDO::PARAM_INT);
            }
            else
            {
                $UpdateSt = $Connector->prepare("INSERT INTO `".RP_TABLE_PREFIX."Attendance` ( CharacterId, UserId, RaidId, Status, Role, Comment ) ".
                                                "VALUES ( :CharacterId, :UserId, :RaidId, :Status, :Role, :Comment )" );
                                                
                $UpdateSt->bindValue(":CharacterId", 0, PDO::PARAM_INT);
                $UpdateSt->bindValue(":Role",        0, PDO::PARAM_INT);
                $UpdateSt->bindValue(":Status",      "undecided", PDO::PARAM_STR);
            }

            $UpdateSt->bindValue(":RaidId",  $raidId, PDO::PARAM_INT);
            $UpdateSt->bindValue(":UserId",  $userId, PDO::PARAM_INT);
            $UpdateSt->bindValue(":Comment", requestToXML( $Request["comment"], ENT_COMPAT, "UTF-8" ), PDO::PARAM_STR);
    
            if ( !$UpdateSt->execute() )
            {
                 postErrorMessage( $UpdateSt );
            }
    
            $UpdateSt->closeCursor();
        }
        
        $CheckSt->closeCursor();

        // reload calendar

        $RaidSt = $Connector->prepare("SELECT Start FROM `".RP_TABLE_PREFIX."Raid` WHERE RaidId = :RaidId LIMIT 1");
        $RaidSt->bindValue(":RaidId",  $raidId, PDO::PARAM_INT);
        $RaidSt->execute();

        $RaidData = $RaidSt->fetch( PDO::FETCH_ASSOC );

        $RaidSt->closeCursor();

        $showMonth = ( isset($_SESSION["Calendar"]) && isset($_SESSION["Calendar"]["month"]) ) ? $_SESSION["Calendar"]["month"] : intval( substr( $RaidData["Start"], 5, 2 ) );
        $showYear  = ( isset($_SESSION["Calendar"]) && isset($_SESSION["Calendar"]["year"]) )  ? $_SESSION["Calendar"]["year"]  : intval( substr( $RaidData["Start"], 0, 4 ) );

        msgQueryCalendar( prepareCalRequest( $showMonth, $showYear ) );
    }
    else
    {
        echo "<error>".L("AccessDenied")."</error>";
    }
}

?>