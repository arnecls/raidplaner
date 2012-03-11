<?php

function msgQuerySettings( $Request )
{
	if ( ValidAdmin() )
    {    
		$Connector = Connector::GetInstance();
		
		// Load users
    	
    	$Users = $Connector->prepare("Select * FROM `".RP_TABLE_PREFIX."User` ORDER BY Login, `Group`");
    	
        if ( !$Users->execute() )
        {
        	postErrorMessage( $Users );
        }
        else
        {	        
	        while ( $Data = $Users->fetch( PDO::FETCH_ASSOC ) )
	        {
	        	echo "<user>";
	        	echo "<id>".$Data["UserId"]."</id>";
	        	echo "<login>".xmlentities( $Data["Login"], ENT_COMPAT, "UTF-8" )."</login>";
	        	echo "<binding>".$Data["ExternalBinding"]."</binding>";
	        	echo "<group>".$Data["Group"]."</group>";
	        	echo "</user>";
	        }
	    }
	    	
        $Users->closeCursor();
        
        // Load settings
        
        $Settings = $Connector->prepare("Select * FROM `".RP_TABLE_PREFIX."Setting` ORDER BY Name");
    	
        if ( !$Settings->execute() )
        {
        	postErrorMessage( $Settings );
        }
        else
        {	        
	        while ( $Data = $Settings->fetch( PDO::FETCH_ASSOC ) )
	        {
	        	echo "<setting>";
	        	echo "<name>".$Data["Name"]."</name>";
	        	echo "<intValue>".$Data["IntValue"]."</intValue>";
	        	echo "<textValue>".$Data["TextValue"]."</textValue>";
	        	echo "</setting>";
	        }
	    }
	    	
        $Settings->closeCursor();
        
        // Query number of raids
        
        $NumRaids = 0;
        $Raids = $Connector->prepare( "SELECT COUNT(*) AS `NumberOfRaids` FROM `".RP_TABLE_PREFIX."Raid` WHERE Start < FROM_UNIXTIME(:Now)" );
        $Raids->bindValue( ":Now", time(), PDO::PARAM_INT );
        
        if ( !$Raids->execute() )
        {
        	postErrorMessage( $User );
        }
        else
        {
        	$Data = $Raids->fetch( PDO::FETCH_ASSOC );
        	$NumRaids = $Data["NumberOfRaids"];
        }
        
        $Raids->closeCursor();
        
        echo "<numRaids>".$NumRaids."</numRaids>";
        
        // Query attendance
        
        $Attendance = $Connector->prepare("SELECT `".RP_TABLE_PREFIX."Character`.Name, `".RP_TABLE_PREFIX."Attendance`.Status, `".RP_TABLE_PREFIX."User`.UserId, COUNT(*) AS Count ".
									 	  "FROM `".RP_TABLE_PREFIX."User` LEFT JOIN `".RP_TABLE_PREFIX."Attendance` USING(UserId) ".
									 	  "LEFT JOIN `".RP_TABLE_PREFIX."Raid` USING(RaidId) LEFT JOIN `".RP_TABLE_PREFIX."Character` USING(UserId) ".
									 	  "WHERE `".RP_TABLE_PREFIX."Character`.Mainchar = 'true' AND `".RP_TABLE_PREFIX."Raid`.Start > `".RP_TABLE_PREFIX."User`.Created AND `".RP_TABLE_PREFIX."Raid`.Start < FROM_UNIXTIME(:Now) ".
									 	  "GROUP BY UserId, Status ORDER BY Name" );
									 
		$Attendance->bindValue( ":Now", time(), PDO::PARAM_INT );
		
		if ( !$Attendance->execute() )
        {
        	postErrorMessage( $Attendance );
        }
        else
        {	        
        	$UserId = 0;
        	$MainCharName = "";
        	$StateCounts = array( "available" => 0, "unavailable" => 0, "ok" => 0 );
        	
	        while ( $Data = $Attendance->fetch( PDO::FETCH_ASSOC ) )
	        {
	        	if ( $UserId == 0 )
	        	{
	        		$UserId = $Data["UserId"];
	        		$MainCharName = $Data["Name"];
	        	}
	        	else if ( $UserId != $Data["UserId"] )
	        	{
	        		echo "<attendance>";
	        		echo "<id>".$UserId."</id>";
	        		echo "<name>".$MainCharName."</name>";
	        		echo "<ok>".$StateCounts["ok"]."</ok>";
	        		echo "<available>".$StateCounts["available"]."</available>";
	        		echo "<unavailable>".$StateCounts["unavailable"]."</unavailable>";
	        		echo "</attendance>";
	        		
	        		$StateCounts["ok"] = 0;
	        		$StateCounts["available"] = 0;
	        		$StateCounts["unavailable"] = 0;
	        		$UserId = $Data["UserId"];
	        		$MainCharName = $Data["Name"];
	        	}
	        	
	        	$StateCounts[$Data["Status"]] += $Data["Count"];
	        }
	        
	        if ($UserId != 0)
	        {	        
		        echo "<attendance>";
	    		echo "<id>".$UserId."</id>";
	    		echo "<name>".$MainCharName."</name>";
	    		echo "<ok>".$StateCounts["ok"]."</ok>";
	    		echo "<available>".$StateCounts["available"]."</available>";
	    		echo "<unavailable>".$StateCounts["unavailable"]."</unavailable>";
	    		echo "</attendance>";
	    	}
	    }
	    	
        $Attendance->closeCursor();
        
        // Locations
        
        msgQueryLocations( $Request );
    }
    else
    {
        echo "<error>".L("Access denied")."</error>";
    }
}
   
?>