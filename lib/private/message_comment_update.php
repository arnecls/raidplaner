<?php

function msgCommentupdate( $aRequest )
{
    if ( validUser() )
    {
        $Connector = Connector::getInstance();

        $RaidId = intval( $aRequest["raidId"] );
        $UserId = UserProxy::getInstance()->UserId;

        $CheckQuery = $Connector->prepare("SELECT UserId FROM `".RP_TABLE_PREFIX."Attendance` WHERE UserId = :UserId AND RaidId = :RaidId LIMIT 1");
        $CheckQuery->bindValue(":UserId", $UserId, PDO::PARAM_INT);
        $CheckQuery->bindValue(":RaidId", $RaidId, PDO::PARAM_INT);

        if ( $CheckQuery->execute() )
        {
            $UpdateQuery = null;

            if ( $CheckQuery->rowCount() > 0 )
            {
                $UpdateQuery = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."Attendance` ".
                                                "SET comment = :Comment, LastUpdate = FROM_UNIXTIME(:Timestamp) ".
                                                "WHERE RaidId = :RaidId AND UserId = :UserId LIMIT 1" );
                
                $UpdateQuery->bindValue(":Timestamp", time(), PDO::PARAM_INT);
            }
            else
            {
                $UpdateQuery = $Connector->prepare("INSERT INTO `".RP_TABLE_PREFIX."Attendance` ( CharacterId, UserId, RaidId, Status, Role, Comment ) ".
                                                "VALUES ( :CharacterId, :UserId, :RaidId, :Status, :Role, :Comment )" );
                                                
                $UpdateQuery->bindValue(":CharacterId", 0, PDO::PARAM_INT);
                $UpdateQuery->bindValue(":Role",        0, PDO::PARAM_INT);
                $UpdateQuery->bindValue(":Status",      "undecided", PDO::PARAM_STR);
            }

            $UpdateQuery->bindValue(":RaidId",  $RaidId, PDO::PARAM_INT);
            $UpdateQuery->bindValue(":UserId",  $UserId, PDO::PARAM_INT);
            $UpdateQuery->bindValue(":Comment", requestToXML( $aRequest["comment"], ENT_COMPAT, "UTF-8" ), PDO::PARAM_STR);
    
            $UpdateQuery->execute();
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