<?php

function msgQueryProfile( $Request )
{
	if ( ValidUser() )
    {
		$userId = intval( $_SESSION["User"]["UserId"] );
	
		if ( ValidAdmin() && isset( $_REQUEST["id"] ) )
		{
			$userId = intval( $_REQUEST["id"] );
		}
		
    	$Connector = Connector::GetInstance();
    	
    	// Load characters
    	
    	$Characters = $Connector->prepare(	"Select ".RP_TABLE_PREFIX."Character.* ".
    										"FROM `".RP_TABLE_PREFIX."Character` ".
    										"WHERE UserId = :UserId ORDER BY Mainchar, Name");
    	
    	$Characters->bindValue( ":UserId", $userId, PDO::PARAM_INT );
        
        if ( !$Characters->execute() )
        {
        	postErrorMessage( $Characters );
        }
        else
        {
        	$userName = "unknown";
        	
        	while ( $Data = $Characters->fetch( PDO::FETCH_ASSOC ) )
	        {
	        	$userName = $Data["Login"];
	        	
	        	echo "<character>";
	        	echo "<id>".$Data["CharacterId"]."</id>";
	        	echo "<name>".$Data["Name"]."</name>";
	        	echo "<class>".$Data["Class"]."</class>";
	        	echo "<mainchar>".$Data["Mainchar"]."</mainchar>";
	        	echo "<role1>".$Data["Role1"]."</role1>";
	        	echo "<role2>".$Data["Role2"]."</role2>";
	        	echo "</character>";
	        }
	    }
	    	
        $Characters->closeCursor();
        
        // Total raid count
        
        $NumRaids = 0;
        $Raids = $Connector->prepare( "SELECT COUNT(*) AS `NumberOfRaids` FROM `".RP_TABLE_PREFIX."Raid` WHERE Start <= FROM_UNIXTIME(:Start)" );
        $Raids->bindValue( ":Start", time(), PDO::PARAM_INT );
        
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
        
        // Load attendance
        
        $Attendance = $Connector->prepare(	"Select `Status`, `Role`, COUNT(*) AS `Count` ".
        									"FROM `".RP_TABLE_PREFIX."Attendance` ".
    										"LEFT JOIN `".RP_TABLE_PREFIX."Raid` USING(RaidId) ".
    										"WHERE UserId = :UserId AND Start <= FROM_UNIXTIME(:Start) ".
    										"GROUP BY `Status`, `Role` ORDER BY Status" );
    	
    	$Attendance->bindValue( ":UserId", $userId, PDO::PARAM_INT );
    	$Attendance->bindValue( ":Start", time(), PDO::PARAM_INT );
        
        if ( !$Attendance->execute() )
        {
        	postErrorMessage( $Attendance );
        }
        else
        {
        	$AttendanceData = array( 
        		"available"   => 0, 
        		"unavailable" => 0, 
        		"ok"          => 0,
        		"dmg" 		  => 0,
        		"heal"        => 0,
        		"tank"        => 0 );
        	
        	while ( $Data = $Attendance->fetch( PDO::FETCH_ASSOC ) )
	        {
	        	$AttendanceData[ $Data["Status"] ] += $Data["Count"];
	        
	        	if ( $Data["Status"] == "ok" )
	        		$AttendanceData[ $Data["Role"] ] += $Data["Count"];
	        }
	        
	        echo "<attendance>";
	        echo "<raids>".$NumRaids."</raids>";
	        
	        while( list($Name, $Count) = each($AttendanceData) )
	        {
	        	echo "<".$Name.">".$Count."</".$Name.">";
		    }
	        
	        echo "</attendance>";
	    }
	    	
        $Attendance->closeCursor();
        
        // Admintool relevant data
        
        if ( ValidAdmin() && isset( $_REQUEST["id"] ) )
        {
        	$Users = $Connector->prepare( "SELECT Login FROM `".RP_TABLE_PREFIX."User` WHERE UserId = :UserId LIMIT 1" );
        	$Users->bindValue( ":UserId", $userId, PDO::PARAM_INT );
        	
        	if ( !$Users->execute() )
	        {
	        	postErrorMessage( $User );
	        }
	        else
	        {
	        	$Data = $Users->fetch( PDO::FETCH_ASSOC );
	        	
	        	echo "<userid>".$userId."</userid>";
        		echo "<name>".$Data["Login"]."</name>";
	        }
	        
	        $Users->closeCursor();
        }
    }
    else
    {
        echo "<error>".L("Access denied")."</error>";
    }
}
   
?>