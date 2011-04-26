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
        
function msgSettingsUpdate( $Request )
{
	if ( ValidAdmin() )
    {
    	$Connector = Connector::GetInstance();
    	
    	// Update settings
    	
    	$updatePurge = $Connector->prepare( "UPDATE `".RP_TABLE_PREFIX."Setting` SET IntValue = :purgeTime WHERE Name=\"PurgeRaids\"" );
    	$updatePurge->bindValue( ":purgeTime", $Request["purgeTime"], PDO::PARAM_INT );

		
		if ( !$updatePurge->execute() )
        {
        	postErrorMessage( $updatePurge );
        }
	    	
        $updatePurge->closeCursor();    	
    	
    	$updateLock = $Connector->prepare( "UPDATE `".RP_TABLE_PREFIX."Setting` SET IntValue = :lockTime WHERE Name=\"LockRaids\"" );
	    $updateLock->bindValue( ":lockTime", $Request["lockTime"], PDO::PARAM_INT );
	    
	    if ( !$updateLock->execute() )
        {
        	postErrorMessage( $updateLock );
        }
	    	
        $updateLock->closeCursor();

		
		// Update users and groups
		
		$Connector->beginTransaction();
		
		$BannedIds   = $_REQUEST["banned"];
		$MemberIds   = $_REQUEST["member"];
		$RaidleadIds = $_REQUEST["raidlead"];
		$AdminIds    = $_REQUEST["admin"];
		$RemovedIds  = $_REQUEST["removed"];
		
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