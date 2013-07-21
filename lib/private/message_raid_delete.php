<?php

function msgRaidDelete( $aRequest )
{
    if ( validRaidlead() )
    {
        $Connector = Connector::getInstance();

        // Delete raid

        $Connector->beginTransaction();

        $DeleteRaidSt = $Connector->prepare("DELETE FROM `".RP_TABLE_PREFIX."Raid` WHERE RaidId = :RaidId LIMIT 1" );

        $DeleteRaidSt->bindValue(":RaidId", $aRequest["id"], PDO::PARAM_INT);

        if (!$DeleteRaidSt->execute())
        {
            postErrorMessage( $DeleteRaidSt );
            $Connector->rollBack();
            return;
        }

        $DeleteRaidSt->closeCursor();

        // Delete attendance

        $DeleteAttendanceSt = $Connector->prepare("DELETE FROM `".RP_TABLE_PREFIX."Attendance` WHERE RaidId = :RaidId" );

        $DeleteAttendanceSt->bindValue(":RaidId", $aRequest["id"], PDO::PARAM_INT);

        if (!$DeleteAttendanceSt->execute())
        {
            postErrorMessage( $DeleteAttendanceSt );
            $Connector->rollBack();
            return;
        }

        $DeleteAttendanceSt->closeCursor();
        $Connector->commit();

        $ShowMonth = ( isset($_SESSION["Calendar"]) && isset($_SESSION["Calendar"]["month"]) ) ? $_SESSION["Calendar"]["month"] : $aRequest["month"];
        $ShowYear  = ( isset($_SESSION["Calendar"]) && isset($_SESSION["Calendar"]["year"]) )  ? $_SESSION["Calendar"]["year"]  : $aRequest["year"];

        msgQueryCalendar( prepareCalRequest( $ShowMonth, $ShowYear ) );
    }
    else
    {
        echo "<error>".L("AccessDenied")."</error>";
    }
}

?>