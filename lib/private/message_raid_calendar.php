<?php

function prepareRaidListRequest( $Month, $Year )
{
	$StartDateTime = mktime(0, 0, 0, $Month, 1, $Year);	    
    $StartDate = getdate( $StartDateTime );
	
    if ( $StartDate["wday"] != 1 )
    {
    	$StartDateTime = strtotime("previous monday", $StartDateTime);
        $StartDate = getdate( $StartDateTime );
    }
    
    $EndDateTime = strtotime("+6 weeks", $StartDateTime);
    $EndDate = getdate( $EndDateTime );
                	
	$listRequest["StartDay"]	 = $StartDate["mday"];
	$listRequest["StartMonth"]	 = $StartDate["mon"];
	$listRequest["StartYear"]	 = $StartDate["year"];
	
	$listRequest["EndDay"]		 = $EndDate["mday"];
	$listRequest["EndMonth"]	 = $EndDate["mon"];
	$listRequest["EndYear"]	  	 = $EndDate["year"];
	
	$listRequest["DisplayMonth"] = $Month-1;
	$listRequest["DisplayYear"]  = $Year;
	
	return $listRequest;
}

// -----------------------------------------------------------------------------

function parseRaidQuery( $QueryResult, $Limit )
{
	echo "<raids>";
	
    $RaidData = Array();
    $RaidInfo = Array();
    
    while ($Data = $QueryResult->fetch())
    {
    	array_push($RaidData, $Data);
    	
    	if ( !isset($RaidInfo[$Data["RaidId"]]) )
    	{
    		$RaidInfo[$Data["RaidId"]]["tanks"] = 0;
    		$RaidInfo[$Data["RaidId"]]["heal"] = 0;
    		$RaidInfo[$Data["RaidId"]]["dmg"] = 0;
    		$RaidInfo[$Data["RaidId"]]["bench"] = 0;
    	}
    	
    	if ( $Data["Status"] == "ok" )
    	{    	
	    	if ( $Data["Role"] == "tank" )      ++$RaidInfo[$Data["RaidId"]]["tanks"];
	    	else if ( $Data["Role"] == "heal" ) ++$RaidInfo[$Data["RaidId"]]["heal"];
	    	else if ( $Data["Role"] == "dmg" )  ++$RaidInfo[$Data["RaidId"]]["dmg"];
 	   	}
 	   	else if ( $Data["Status"] == "available" )
 	   	{
 	   		++$RaidInfo[$Data["RaidId"]]["bench"];
 	   	}    	
    }
    
    $LastRaidId = -1;
    $RaidDataCount = sizeof($RaidData);
    
    $NumRaids = 0;
    
    for ( $DataIdx=0; $DataIdx < $RaidDataCount; ++$DataIdx )
    {
    	$Data = $RaidData[$DataIdx];
    	
    	if ( $LastRaidId != $Data["RaidId"] )
    	{
    		// If no user assigned for this raid
    		// or row belongs to this user
    		// or it's the last entry
    		// or the next entry is a different raid
    		
    		$IsCorrectUser = $Data["UserId"] == intval($_SESSION["User"]["UserId"]);
    		
    		if ( ($IsCorrectUser) ||
    		     ($Data["UserId"] == NULL) || 
    		     ($DataIdx+1 == $RaidDataCount) ||
    		     ($RaidData[$DataIdx+1]["RaidId"] != $Data["RaidId"]) )
    		{
    			$status = "notset";
    			$attendanceIndex = 0;
            	$role = "";
            	$comment = "";
            	
    			if ( $IsCorrectUser )
            	{
            		$status = $Data["Status"];
            		$attendanceIndex = ($status == "unavailable") ? -1 : intval($Data["CharacterId"]);
            		$role = $Data["Role"];
            		$comment = $Data["Comment"];
            	}
            	           	
                echo "<raid>";
                
                echo "<id>".$Data["RaidId"]."</id>";
                echo "<location>".$Data["Name"]."</location>";
		        echo "<stage>".$Data["Stage"]."</stage>";
                echo "<size>".$Data["Size"]."</size>";
                echo "<startDate>".substr( $Data["Start"], 0, 10 )."</startDate>";
                echo "<start>".substr( $Data["Start"], 11, 5 )."</start>";
                echo "<end>".substr( $Data["End"], 11, 5 )."</end>";
                echo "<image>".$Data["Image"]."</image>";
                echo "<description>".$Data["Description"]."</description>";
                echo "<status>".$status."</status>";
                echo "<attendanceIndex>".$attendanceIndex."</attendanceIndex>";
                echo "<comment>".$comment."</comment>";
                echo "<role>".$role."</role>";
                
                echo "<tankCount>" .$RaidInfo[$Data["RaidId"]]["tanks"]."</tankCount>";
                echo "<healCount>".$RaidInfo[$Data["RaidId"]]["heal"]."</healCount>";
                echo "<dmgCount>".$RaidInfo[$Data["RaidId"]]["dmg"]."</dmgCount>";
                echo "<benchCount>".$RaidInfo[$Data["RaidId"]]["bench"]."</benchCount>";
                
                echo "<tankSlots>".$Data["TankSlots"]."</tankSlots>";
                echo "<healSlots>".$Data["HealSlots"]."</healSlots>";
                echo "<dmgSlots>".$Data["DmgSlots"]."</dmgSlots>";
                
                echo "</raid>";
                
                $LastRaidId = $Data["RaidId"];
                ++$NumRaids;
                
                if ( ($Limit > 0) && ($NumRaids == $Limit) )
                	break;
        	}
    	}
    }
    
    echo "</raids>";
}

// -----------------------------------------------------------------------------

function msgRaidCalendar( $Request )
{
	if (ValidUser())
    {
    	$Connector = Connector::GetInstance();
    		
        $ListRaidSt = $Connector->prepare(	"Select ".RP_TABLE_PREFIX."Raid.*, ".RP_TABLE_PREFIX."Location.*, ".
        									RP_TABLE_PREFIX."Attendance.CharacterId, ".RP_TABLE_PREFIX."Attendance.UserId, ".
        								 	RP_TABLE_PREFIX."Attendance.Status, ".RP_TABLE_PREFIX."Attendance.Role, ".RP_TABLE_PREFIX."Attendance.Comment ".
        								  	"FROM `".RP_TABLE_PREFIX."Raid` ".
                                          	"LEFT JOIN `".RP_TABLE_PREFIX."Location` USING(LocationId) ".
                                          	"LEFT JOIN `".RP_TABLE_PREFIX."Attendance` USING (RaidId) ".
                                          	"LEFT JOIN `".RP_TABLE_PREFIX."Character` USING (CharacterId) ".
                                          	"WHERE ".RP_TABLE_PREFIX."Raid.Start >= FROM_UNIXTIME(:Start) AND ".RP_TABLE_PREFIX."Raid.Start <= FROM_UNIXTIME(:End) ".
                                          	"ORDER BY ".RP_TABLE_PREFIX."Raid.Start, ".RP_TABLE_PREFIX."Raid.RaidId" );
                                         
        $StartDateTime = mktime(0, 0, 0, $Request["StartMonth"], $Request["StartDay"], $Request["StartYear"]);
        $EndDateTime   = mktime(0, 0, 0, $Request["EndMonth"], $Request["EndDay"], $Request["EndYear"]);
        
        $ListRaidSt->bindValue(":Start",  $StartDateTime, PDO::PARAM_INT);
        $ListRaidSt->bindValue(":End",    $EndDateTime,   PDO::PARAM_INT);
        
        if (!$ListRaidSt->execute())
        {
        	postErrorMessage( $ListRaidSt );
        }
        else
        {
        	$_SESSION["Calendar"]["month"] = intval( $Request["DisplayMonth"] );
        	$_SESSION["Calendar"]["year"]  = intval( $Request["DisplayYear"] );
        	
            echo "<startDay>".$Request["StartDay"]."</startDay>";
            echo "<startMonth>".$Request["StartMonth"]."</startMonth>";
            echo "<startYear>".$Request["StartYear"]."</startYear>";
            echo "<displayMonth>".$Request["DisplayMonth"]."</displayMonth>";
            echo "<displayYear>".$Request["DisplayYear"]."</displayYear>";
            
            parseRaidQuery( $ListRaidSt, 0 );
        }
        
        $ListRaidSt->closeCursor();
    }
    else
    {
        echo "<error>".L("Access denied")."</error>";
    }
}
?>