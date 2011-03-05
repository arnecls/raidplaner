<?php
	function msgRaidList( $Request )
	{
		if (ValidUser())
	    {
	    	$Connector = Connector::GetInstance();
	    	
	    	// Get next 6 raids
	    	
	    	$NextRaidSt = $Connector->prepare(	"Select ".RP_TABLE_PREFIX."Raid.*, ".RP_TABLE_PREFIX."Location.*, ".
        										RP_TABLE_PREFIX."Attendance.CharacterId, ".RP_TABLE_PREFIX."Attendance.UserId, ".
        								 		RP_TABLE_PREFIX."Attendance.Status, ".RP_TABLE_PREFIX."Attendance.Role, ".RP_TABLE_PREFIX."Attendance.Comment ".
	    										"FROM `".RP_TABLE_PREFIX."Raid` ".
	                                          	"LEFT JOIN `".RP_TABLE_PREFIX."Location` USING(LocationId) ".
	                                          	"LEFT JOIN `".RP_TABLE_PREFIX."Attendance` USING(RaidId) ".
                                          		"LEFT JOIN `".RP_TABLE_PREFIX."Character` USING (CharacterId) ".
	                                          	"WHERE ".RP_TABLE_PREFIX."Raid.Start >= FROM_UNIXTIME(:Start) ".
	                                          	"ORDER BY ".RP_TABLE_PREFIX."Raid.Start, ".RP_TABLE_PREFIX."Raid.RaidId" );
	                                          
	        $NextRaidSt->bindValue( ":Start", mktime(0,0,0), PDO::PARAM_INT );
	        
	        if ( !$NextRaidSt->execute() )
	        {
	        	postErrorMessage( $NextRaidSt );
	        }
	        else
	        {
		        parseRaidQuery( $NextRaidSt, 6 );        
	        }
	        
	        $NextRaidSt->closeCursor();
	        
	        // -------------------------
	        
	        $ListRaidSt = $Connector->prepare("Select ".RP_TABLE_PREFIX."Raid.*, ".RP_TABLE_PREFIX."Location.* ".
	        								  "FROM `".RP_TABLE_PREFIX."Raid` ".
	                                          "LEFT JOIN `".RP_TABLE_PREFIX."Location` USING(LocationId) ".
	                                          "WHERE ".RP_TABLE_PREFIX."Raid.Start < FROM_UNIXTIME(:Start)".
	                                          "ORDER BY Start DESC LIMIT ".intval($Request["offset"]).", ".intval($Request["count"]) );
	                                          
	        $ListRaidSt->bindValue( ":Start", mktime(0,0,0), PDO::PARAM_INT );
	        
	        if (!$ListRaidSt->execute())
	        {
	        	postErrorMessage( $ListRaidSt );
	        }
	        else
	        {
	        	echo "<raidList>";
	        	
		        while ( $Data = $ListRaidSt->fetch() )
		        {
		        	echo "<raid>";
		        	echo "<id>".$Data["RaidId"]."</id>";
		        	echo "<location>".$Data["Name"]."</location>";
		        	echo "<stage>".$Data["Stage"]."</stage>";
		        	echo "<image>".$Data["Image"]."</image>";
		        	echo "<size>".$Data["Size"]."</size>";
		        	echo "<startDate>".substr( $Data["Start"], 0, 10 )."</startDate>";
                	echo "<start>".substr( $Data["Start"], 11, 5 )."</start>";
                	echo "<end>".substr( $Data["End"], 11, 5 )."</end>";
                	echo "</raid>";
		        }
		        
		        echo "</raidList>";     
	        }
	        
	        $ListRaidSt->closeCursor();
		}
	    else
	    {
	        echo "<error>".L("Access denied")."</error>";
	    }
	}
?>