<?php

function updateGroup( $Connector, $GroupName, $IdArray )
{
	$userGroup = $Connector->prepare( "SELECT UserId FROM `".RP_TABLE_PREFIX."User` WHERE `Group` = :GroupName" );	
	$userGroup->bindValue(":GroupName", $GroupName, PDO::PARAM_STR );
		
	if ( !$userGroup->execute() )
    {
    	postErrorMessage( $userGroup );
    	
    	$userGroup->closeCursor();
        $Connector->rollBack();
        return false;
    }
    
    $CurrentGroupIds = Array();        
    
    while ( $User = $userGroup->fetch( PDO::FETCH_ASSOC ) )
    {
    	array_push( $CurrentGroupIds, intval($User["UserId"]) );	
    }
    
    $userGroup->closeCursor();    
    $ChangedIds = array_diff( $IdArray, $CurrentGroupIds ); // new ids
    
    foreach ( $ChangedIds as $UserId )
    {
    	$changeUser = $Connector->prepare( "UPDATE `".RP_TABLE_PREFIX."User` SET `Group` = :GroupName WHERE UserId = :UserId " );
    	$changeUser->bindValue(":UserId", $UserId, PDO::PARAM_INT);
    	$changeUser->bindValue(":GroupName", $GroupName, PDO::PARAM_STR );
    	
    	if ( !$changeUser->execute() )
        {
        	postErrorMessage( $changeUser );
        	
        	$changeUser->closeCursor();
	        $Connector->rollBack();
	        return false;
        }
        
        $changeUser->closeCursor();
    }
    
    return true;
}

// -----------------------------------------------------------------------------

function generateQueryStringInt( $CurrentValues, &$BindValues, $ValueName, $NewValue )
{
	if ( isset($CurrentValues[$ValueName]) )
    {
    	if ( $CurrentValues[$ValueName]["number"] != $NewValue )
    	{
    		array_push( $BindValues, array(":".$ValueName, $NewValue, PDO::PARAM_INT) );
    		return "UPDATE `".RP_TABLE_PREFIX."Setting` SET IntValue = :".$ValueName." WHERE Name=\"".$ValueName."\"; ";
    	}
    }
    else
    {
    	array_push( $BindValues, array(":".$ValueName, $NewValue, PDO::PARAM_INT) );
    	return "INSERT INTO `".RP_TABLE_PREFIX."Setting` (`Name`, `IntValue`) VALUES ('".$ValueName."', :".$ValueName."); ";
    }
    
    return "";
}

// -----------------------------------------------------------------------------

function generateQueryStringText( $CurrentValues, &$BindValues, $ValueName, $NewValue )
{
	if ( isset($CurrentValues[$ValueName]) )
    {
    	if ( $CurrentValues[$ValueName]["text"] != $NewValue )
    	{
    		array_push( $BindValues, array(":".$ValueName, $NewValue, PDO::PARAM_STR) );
    		return "UPDATE `".RP_TABLE_PREFIX."Setting` SET TextValue = :".$ValueName." WHERE Name=\"".$ValueName."\"; ";
    	}
    }
    else
    {
    	array_push( $BindValues, array(":".$ValueName, $NewValue, PDO::PARAM_STR) );
    	return "INSERT INTO `".RP_TABLE_PREFIX."Setting` (`Name`, `TextValue`) VALUES ('".$ValueName."', :".$ValueName."); ";    	
    }
    
    return "";
}

// -----------------------------------------------------------------------------
        
function msgSettingsUpdate( $Request )
{
	if ( ValidAdmin() )
    {
    	$Connector = Connector::GetInstance();
    	
    	// Update settings
    	
    	$existingSettings = $Connector->prepare( "SELECT * FROM `".RP_TABLE_PREFIX."Setting`" );
    	$currentValues = array();
    	
    	if ( !$existingSettings->execute() )
        {
        	postErrorMessage( $existingSettings );
        }
        else
        {
        	while ( $Data = $existingSettings->fetch( PDO::FETCH_ASSOC ) )
	        {
	        	$currentValues[$Data["Name"]] = array( "number" => $Data["IntValue"], "text" => $Data["TextValue"] );
	        }
        }
        
        $existingSettings->closeCursor();
        $queryString = "";
        $bindValues = array();
        
        // Generate settings update query
        
        $queryString .= generateQueryStringInt( $currentValues, $bindValues, "PurgeRaids", $Request["purgeTime"] );
        $queryString .= generateQueryStringInt( $currentValues, $bindValues, "LockRaids", $Request["lockTime"] );
        $queryString .= generateQueryStringInt( $currentValues, $bindValues, "TimeFormat", $Request["timeFormat"] );
        $queryString .= generateQueryStringInt( $currentValues, $bindValues, "RaidStartHour", $Request["raidStartHour"] );
        $queryString .= generateQueryStringInt( $currentValues, $bindValues, "RaidStartMinute", $Request["raidStartMinute"] );
        $queryString .= generateQueryStringInt( $currentValues, $bindValues, "RaidEndHour", $Request["raidEndHour"] );
        $queryString .= generateQueryStringInt( $currentValues, $bindValues, "RaidEndMinute", $Request["raidEndMinute"] );
        $queryString .= generateQueryStringInt( $currentValues, $bindValues, "RaidSize", $Request["raidSize"] );
        $queryString .= generateQueryStringText( $currentValues, $bindValues, "Site", $Request["site"] );
        $queryString .= generateQueryStringText( $currentValues, $bindValues, "Banner", $Request["banner"] );
        
        if ( $queryString != "" )
       	{
       		$settingsUpdate = $Connector->prepare( $queryString );
        	
        	foreach( $bindValues as $bindData )
        	{
        		$settingsUpdate->bindValue( $bindData[0], $bindData[1], $bindData[2] );
        	}
        	
        	$Connector->beginTransaction();
        	if ( !$settingsUpdate->execute() )
        	{
        		postErrorMessage( $settingsUpdate );
        		$Connector->rollBack();
        	}
        	else
        	{
        		$Connector->commit();
        	}
	    	
        	$settingsUpdate->closeCursor();        	
        }
        
        // Update locations
        
        $existingLocations = $Connector->prepare( "SELECT * FROM `".RP_TABLE_PREFIX."Location`" );
    	$currentValues = array();
    	
    	if ( !$existingLocations->execute() )
        {
        	postErrorMessage( $existingLocations );
        }
        else
        {
        	while ( $Data = $existingLocations->fetch( PDO::FETCH_ASSOC ) )
	        {
	        	$currentValues[$Data["LocationId"]] = array( "Name" => $Data["Name"], "Image" => $Data["Image"] );
	        }
        }
        
        $existingLocations->closeCursor();
        $queryString = "";
        $bindValues = array();
        
        // Build location query
        
        for ( $i=0; $i < sizeof($Request["locationIds"]); ++$i )
        {
        	$locationId      = intval($Request["locationIds"][$i]);
        	$currentLocation = $currentValues[$locationId];        	
        	$locationName    = requestToXML( $Request["locationNames"][$i], ENT_COMPAT, "UTF-8" );
        	$locationImage   = ( isset($Request["locationImages"]) && isset($Request["locationImages"][$i]) && ($Request["locationImages"][$i] != "undefined") ) 
        		? $Request["locationImages"][$i]
        		: $currentLocation["Image"];
        	
        	if ( ($locationName != $currentLocation["Name"]) || ($locationImage != $currentLocation["Image"]) )
        	{
        		array_push( $bindValues, array(":Name".$locationId, $locationName, PDO::PARAM_STR) );
        		array_push( $bindValues, array(":Image".$locationId, $locationImage, PDO::PARAM_STR) );
    			$queryString .= "UPDATE `".RP_TABLE_PREFIX."Location` SET Name = :Name".$locationId.", Image = :Image".$locationId." WHERE LocationId=".$locationId."; ";
        	}
        }
        
        if ( isset($Request["locationRemoved"]) )
        {
        	foreach( $Request["locationRemoved"] as $locationId )
       		{
        		$queryString .= "DELETE `".RP_TABLE_PREFIX."Location`, `".RP_TABLE_PREFIX."Raid`, `".RP_TABLE_PREFIX."Attendance` FROM `".RP_TABLE_PREFIX."Location` ".
        						"LEFT JOIN `".RP_TABLE_PREFIX."Raid` USING(LocationId) ".
        						"LEFT JOIN `".RP_TABLE_PREFIX."Attendance` USING(RaidId) ".
        						" WHERE LocationId=".intval($locationId)."; ";
        	}
        }
        
        if ( $queryString != "" )
       	{
       		$locationUpdate = $Connector->prepare( $queryString );
        	
        	foreach( $bindValues as $bindData )
        	{
        		$locationUpdate->bindValue( $bindData[0], $bindData[1], $bindData[2] );
        	}
        	
        	$Connector->beginTransaction();
        	
        	if ( !$locationUpdate->execute() )
        	{
        		postErrorMessage( $locationUpdate );
		        $Connector->rollBack();
        	}
        	else
        	{
        		$Connector->commit();
        	}
	    	
        	$locationUpdate->closeCursor();        	
        }
		
		// Update users and groups
		
		$Connector->beginTransaction();
		
		$BannedIds   = (isset($Request["banned"]))   ? $Request["banned"]   : array();
		$MemberIds   = (isset($Request["member"]))   ? $Request["member"]   : array();
		$RaidleadIds = (isset($Request["raidlead"])) ? $Request["raidlead"] : array();
		$AdminIds    = (isset($Request["admin"]))    ? $Request["admin"]    : array();
		$RemovedIds  = (isset($Request["removed"]))  ? $Request["removed"]  : array();
		
		if ( !updateGroup( $Connector, "none", $BannedIds ) ) 
			return;
			
		if ( !updateGroup( $Connector, "member", $MemberIds ) ) 
			return;
		
		if ( !updateGroup( $Connector, "raidlead", $RaidleadIds ) ) 
			return;
		
		if ( !updateGroup( $Connector, "admin", $AdminIds ) ) 
			return;
			
		// Update removed users
			
		foreach ( $RemovedIds as $UserId )
	    {
	    	// Get characters of user
	    	
	    	$characters = $Connector->prepare( "SELECT CharacterId FROM `".RP_TABLE_PREFIX."Character` WHERE UserId = :UserId" );
	    	$characters->bindValue(":UserId", $UserId, PDO::PARAM_INT);
	    	
	    	if ( !$characters->execute() )
	        {
	        	postErrorMessage( $characters );
	        	
	        	$characters->closeCursor();
		        $Connector->rollBack();
		        return;
	        }
	        
	        // remove characters and attendances 
	        
	        while ( $Data = $characters->fetch( PDO::FETCH_ASSOC ) )
	        {
	        	$dropCharacter  = $Connector->prepare( "DELETE FROM `".RP_TABLE_PREFIX."Character` WHERE CharacterId = :CharacterId LIMIT 1" );
	    		$dropAttendance = $Connector->prepare( "DELETE FROM `".RP_TABLE_PREFIX."Attendance` WHERE CharacterId = :CharacterId" );
	    		
	    		$dropCharacter->bindValue(":CharacterId", $Data["CharacterId"], PDO::PARAM_INT);
	    		$dropAttendance->bindValue(":CharacterId", $Data["CharacterId"], PDO::PARAM_INT);
	    		
	    		if ( !$dropCharacter->execute() )
		        {
		        	postErrorMessage( $dropCharacter );
		        	
		        	$dropCharacter->closeCursor();
			        $Connector->rollBack();
			        return;
		        }
		        
		        if ( !$dropAttendance->execute() )
		        {
		        	postErrorMessage( $dropAttendance );
		        	
		        	$dropAttendance->closeCursor();
			        $Connector->rollBack();
			        return;
		        }
		        
		        $dropCharacter->closeCursor();
		        $dropAttendance->closeCursor();
	        }
	        
	        $characters->closeCursor();
	    
	    	// remove user
	    
	    	$dropUser = $Connector->prepare( "DELETE FROM `".RP_TABLE_PREFIX."User` WHERE UserId = :UserId LIMIT 1" );
	    	$dropUser->bindValue(":UserId", $UserId, PDO::PARAM_INT);
	    	
	    	if ( !$dropUser->execute() )
	        {
	        	postErrorMessage( $dropUser );
	        	
	        	$dropUser->closeCursor();
		        $Connector->rollBack();
		        return false;
	        }
	        
	        $dropUser->closeCursor();
	    }
			
		$Connector->commit();
		
    	msgQuerySettings( $Request );
    }
    else
    {
        echo "<error>".L("Access denied")."</error>";
    }
}

?>