<?php

function msgQueryNewRaidData( $Request )
{
	if ( ValidRaidlead() )
    {
    	$Connector = Connector::GetInstance();
    	
    	// Settings
    	
    	$NewRaidSettings = $Connector->prepare("Select Name, IntValue FROM `".RP_TABLE_PREFIX."Setting`");
    	
    	if ( !$NewRaidSettings->execute() )
        {
        	postErrorMessage( $NewRaidSettings );
        }
        else
        {
        	echo "<settings>";
	        	
        	$OfInterest = array( "RaidSize", "RaidStartHour", "RaidStartMinute", "RaidEndHour", "RaidEndMinute" );
        	while ( $Data = $NewRaidSettings->fetch( PDO::FETCH_ASSOC ) )
	        {
	        	if ( in_array($Data["Name"], $OfInterest) )
	        	{
	        		echo "<".$Data["Name"].">".$Data["IntValue"]."</".$Data["Name"].">";
	        	}
	        }
	        
	        echo "</settings>";
        }
        
        $NewRaidSettings->closeCursor();
        
        // Locations
    	
    	$ListLocations = $Connector->prepare("Select * FROM `".RP_TABLE_PREFIX."Location` ORDER BY Name");
        
        
        if ( !$ListLocations->execute() )
        {
        	postErrorMessage( $ListLocations );
        }
        else
        {
	        while ( $Data = $ListLocations->fetch( PDO::FETCH_ASSOC ) )
	        {
	        	echo "<location>";
	        	echo "<id>".$Data["LocationId"]."</id>";
	        	echo "<name>".$Data["Name"]."</name>";
	        	echo "<image>".$Data["Image"]."</image>";
	        	echo "</location>";
	        }
        }
        
        $ListLocations->closeCursor();
        
        // Images
        
    	$images = scandir("../images/raidsmall");
    	
    	foreach ( $images as $image )
    	{
    		if ( strripos( $image, ".png" ) !== false )
    		{
    			echo "<locationimage>".$image."</locationimage>";
    		}
    	}
    }
    else
    {
        echo "<error>".L("Access denied")."</error>";
    }
}
   
?>