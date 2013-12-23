<?php

function msgRaidDelete( $aRequest )
{
    if ( validRaidlead() )
    {
        $Connector = Connector::getInstance();

        // Delete raid

        $Connector->beginTransaction();

        $DeleteRaidQuery = $Connector->prepare("DELETE FROM `".RP_TABLE_PREFIX."Raid` WHERE RaidId = :RaidId LIMIT 1" );
        $DeleteRaidQuery->bindValue(":RaidId", $aRequest["id"], PDO::PARAM_INT);

        if (!$DeleteRaidQuery->execute())
        {
            $Connector->rollBack();
            return; // ### return, error ###
        }

        // Delete attendance

        $DeleteAttendanceQuery = $Connector->prepare("DELETE FROM `".RP_TABLE_PREFIX."Attendance` WHERE RaidId = :RaidId" );
        $DeleteAttendanceQuery->bindValue(":RaidId", $aRequest["id"], PDO::PARAM_INT);

        if (!$DeleteAttendanceQuery->execute())
        {
            $Connector->rollBack();
            return; // ### return, error ###
        }

        $Connector->commit();

        $ShowMonth = ( isset($_SESSION["Calendar"]) && isset($_SESSION["Calendar"]["month"]) ) ? $_SESSION["Calendar"]["month"] : $aRequest["month"];
        $ShowYear  = ( isset($_SESSION["Calendar"]) && isset($_SESSION["Calendar"]["year"]) )  ? $_SESSION["Calendar"]["year"]  : $aRequest["year"];

        msgQueryCalendar( prepareCalRequest( $ShowMonth, $ShowYear ) );
    }
    else
    {
        $Out = Out::getInstance();
        $Out->pushError(L("AccessDenied"));
    }
}

?>