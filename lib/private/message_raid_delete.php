<?php
        
function msgRaidDelete( $Request )
{
	if ( ValidRaidlead() )
    {
    	$Connector = Connector::GetInstance();
    	
    	// Delete raid
    	
    	$Connector->beginTransaction();
    	
    	$DeleteRaidSt = $Connector->prepare("DELETE FROM `".RP_TABLE_PREFIX."Raid` WHERE RaidId = :RaidId LIMIT 1" );
    	
        $DeleteRaidSt->bindValue(":RaidId", $Request["id"], PDO::PARAM_INT);
        
        if (!$DeleteRaidSt->execute())
        {
        	postErrorMessage( $DeleteRaidSt );
        	$Connector->rollBack();
        	return;
        }
       
        $DeleteRaidSt->closeCursor();
        
        // Delete attendance
        
        $DeleteAttendanceSt = $Connector->prepare("DELETE FROM `".RP_TABLE_PREFIX."Attendance` WHERE RaidId = :RaidId" );
        
        $DeleteAttendanceSt->bindValue(":RaidId", $Request["id"], PDO::PARAM_INT);
        
        if (!$DeleteAttendanceSt->execute())
        {
        	postErrorMessage( $DeleteAttendanceSt );
        	$Connector->rollBack();
        	return;
        }
       
        $DeleteAttendanceSt->closeCursor();
        $Connector->commit();
		
		$showMonth = ( isset($_SESSION["Calendar"]) && isset($_SESSION["Calendar"]["month"]) ) ? $_SESSION["Calendar"]["month"]+1 : $Request["month"];
	    $showYear  = ( isset($_SESSION["Calendar"]) && isset($_SESSION["Calendar"]["year"]) )  ? $_SESSION["Calendar"]["year"]    : $Request["year"];
		
		msgRaidCalendar( prepareRaidListRequest( $showMonth, $showYear ) );
    }
    else
    {
        echo "<error>".L("AccessDenied")."</error>";
    }
}

?>