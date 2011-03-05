<?php

function msgCommentUpdate( $Request )
{
	if ( ValidUser() )
    {
		$Connector = Connector::GetInstance();
		
		$raidId = intval( $Request["raidId"] );
		$userId = intval( $_SESSION["User"]["UserId"] );
		
		$updateSt = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."Attendance` ".
										"SET comment = :Comment ".
										"WHERE RaidId = :RaidId AND UserId = :UserId LIMIT 1" );
		
		$updateSt->bindValue(":RaidId",  $raidId,   PDO::PARAM_INT);
    	$updateSt->bindValue(":UserId",  $userId,   PDO::PARAM_INT);
    	$updateSt->bindValue(":Comment", requestToXML( $Request["comment"], ENT_COMPAT, "UTF-8" ), PDO::PARAM_STR);
    	
    	if ( !$updateSt->execute() )
        {
         	postErrorMessage( $updateSt );
        }
		
		$updateSt->closeCursor();
		
		// reload calendar
		
		$RaidSt = $Connector->prepare("SELECT Start FROM `".RP_TABLE_PREFIX."Raid` WHERE RaidId = :RaidId LIMIT 1");
	    $RaidSt->bindValue(":RaidId",  $raidId, PDO::PARAM_INT);
		$RaidSt->execute();
		
		$RaidData = $RaidSt->fetch();
		
		$RaidSt->closeCursor();
		
		$showMonth = ( isset($_SESSION["Calendar"]) && isset($_SESSION["Calendar"]["month"]) ) ? $_SESSION["Calendar"]["month"]+1 : intval( substr( $RaidData["Start"], 5, 2 ) );
	    $showYear  = ( isset($_SESSION["Calendar"]) && isset($_SESSION["Calendar"]["year"]) )  ? $_SESSION["Calendar"]["year"]    : intval( substr( $RaidData["Start"], 0, 4 ) );
		
		msgRaidCalendar( prepareRaidListRequest( $showMonth, $showYear ) );
	}
    else
    {
        echo "<error>".L("Access denied")."</error>";
    }
}

?>