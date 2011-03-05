<?php
        
function msgRaidUpdate( $Request )
{
	if ( ValidRaidlead() )
    {
    	$Connector = Connector::GetInstance();
    	
    	$Connector->beginTransaction();
    	
    	$locationId = $Request["locationId"];
    	
    	
    	// Insert new location if necessary
    	
    	if ( $locationId == 0 )
    	{
    		$NewLocationSt = $Connector->prepare("INSERT INTO `".RP_TABLE_PREFIX."Location`".
    											 "(Name, Image) VALUES (:Name, :Image)");
    											 
    		$NewLocationSt->bindValue(":Name", requestToXML( $Request["locationName"], ENT_COMPAT, "UTF-8" ), PDO::PARAM_STR );
    		$NewLocationSt->bindValue(":Image", $Request["raidImage"], PDO::PARAM_STR );
    		
    		if (!$NewLocationSt->execute())
        	{
        		postErrorMessage( $NewLocationSt );
            	
            	$NewLocationSt->closeCursor();
            	$Connector->rollBack();
            	return;
        	}
        	else
        	{        	
    			$locationId = $Connector->lastInsertId();
    		}
    		
    		$NewLocationSt->closeCursor();
    	}
    	
    	// Update raid
    	
    	$UpdateRaidSt = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."Raid` SET ".
    										"LocationId = :LocationId, Size = :Size, ".
    										"Stage = :Stage, ".
    										"Start = FROM_UNIXTIME(:Start), End = FROM_UNIXTIME(:End), ".
    										"Description = :Description, ".
    										"TankSlots = :TankSlots, DmgSlots = :DmgSlots, HealSlots = :HealSlots ".
    										"WHERE RaidId = :RaidId" );
    	
		$StartDateTime = mktime($Request["startHour"], $Request["startMinute"], 0, $Request["month"], $Request["day"], $Request["year"]);
        $EndDateTime   = mktime($Request["endHour"], $Request["endMinute"], 0, $Request["month"], $Request["day"], $Request["year"]);
        
        $UpdateRaidSt->bindValue(":RaidId",  	 $Request["id"], PDO::PARAM_INT);
        $UpdateRaidSt->bindValue(":LocationId",  $locationId, PDO::PARAM_INT);
        $UpdateRaidSt->bindValue(":Stage",  	 $Request["stage"], PDO::PARAM_STR);
        $UpdateRaidSt->bindValue(":Size",        $Request["locationSize"], PDO::PARAM_INT);
        $UpdateRaidSt->bindValue(":Start",       $StartDateTime, PDO::PARAM_INT);
        $UpdateRaidSt->bindValue(":End",         $EndDateTime, PDO::PARAM_INT);
        $UpdateRaidSt->bindValue(":Description", requestToXML( $Request["description"], ENT_COMPAT, "UTF-8" ), PDO::PARAM_STR);
        
        $UpdateRaidSt->bindValue(":TankSlots",	$Request["tankSlots"], PDO::PARAM_INT);
        $UpdateRaidSt->bindValue(":HealSlots",	$Request["healSlots"], PDO::PARAM_INT);
        $UpdateRaidSt->bindValue(":DmgSlots",	$Request["locationSize"] - ( $Request["tankSlots"] + $Request["healSlots"] ), PDO::PARAM_INT);
        
        if (!$UpdateRaidSt->execute())
        {
            postErrorMessage( $UpdateRaidSt );        
        	$UpdateRaidSt->closeCursor();
        	$Connector->rollBack();
        }
        else
        {        
	        $UpdateRaidSt->closeCursor();
	        
	        // Remove all reserved slots (no user id assignmend, so always re-inserted)
				
			$DeleteReserved = $Connector->prepare( "DELETE FROM `".RP_TABLE_PREFIX."Attendance` ".
		    									   "WHERE CharacterId = 0 AND UserId = 0 AND RaidId = :RaidId" );
		    										
	        $DeleteReserved->bindValue( ":RaidId", $Request["id"], PDO::PARAM_INT);
			        
			if (!$DeleteReserved->execute())
	        {
	        	postErrorMessage( $DeleteReserved );
	        	$DeleteReserved->closeCursor();
	        	$Connector->rollBack();
	            return;
	        }
	        
	        $DeleteReserved->closeCursor();
	        
	        // Update tanks in list
	        
	        if ( isset($Request["tanks"]) && is_array( $Request["tanks"] ) )
	        {
		        foreach( $Request["tanks"] as $PlayerId )
		        {
		        	$UpdateAttendance = null;
		        	
		        	if ( intval($PlayerId) == 0 )
		        	{
		        		$UpdateAttendance = $Connector->prepare("INSERT INTO `".RP_TABLE_PREFIX."Attendance` ".
			    												"( CharacterId, UserId, RaidId, Status, Role ) ".
			    												"VALUES ( :CharacterId, 0, :RaidId, 'ok', 'tank' )" );
			    	}
		        	else
		        	{
		        		$UpdateAttendance = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."Attendance` SET ".
			    												"Status = 'ok', Role = 'tank' ".
			    												"WHERE RaidId = :RaidId AND (Status = 'available' OR Status = 'ok') AND CharacterId = :CharacterId LIMIT 1" );
			    	}
			    	
			    	$UpdateAttendance->bindValue( ":RaidId", $Request["id"], PDO::PARAM_INT);
			        $UpdateAttendance->bindValue( ":CharacterId", $PlayerId, PDO::PARAM_INT);
			        
			        if (!$UpdateAttendance->execute())
			        {
			        	postErrorMessage( $UpdateAttendance );
			            $UpdateAttendance->closeCursor();
			            $Connector->rollBack();
			            return;
			        }
			        
			        $UpdateAttendance->closeCursor();
		        }
		    }
	        
	        // Update healers in list
	        
	        if ( isset($Request["healers"]) && is_array( $Request["healers"] ) )
	        {
		        foreach( $Request["healers"] as $PlayerId )
		        {
		        	$UpdateAttendance = null;
		        	
		        	if ( intval($PlayerId) == 0 )
		        	{
		        		$UpdateAttendance = $Connector->prepare("INSERT INTO `".RP_TABLE_PREFIX."Attendance` ".
			    												"( CharacterId, UserId, RaidId, Status, Role ) ".
			    												"VALUES ( :CharacterId, 0, :RaidId, 'ok', 'heal' )" );
			    	}
		        	else
		        	{
		        		$UpdateAttendance = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."Attendance` SET ".
			    												"Status = 'ok', Role = 'heal' ".
			    												"WHERE RaidId = :RaidId AND (Status = 'available' OR Status = 'ok') AND CharacterId = :CharacterId LIMIT 1" );
			    	}
			    							
			    	$UpdateAttendance->bindValue(":RaidId", $Request["id"], PDO::PARAM_INT);
			        $UpdateAttendance->bindValue(":CharacterId", $PlayerId, PDO::PARAM_INT);
			               
			        if (!$UpdateAttendance->execute())
			        {
			        	postErrorMessage( $UpdateAttendance );
			        	$UpdateAttendance->closeCursor();
			        	$Connector->rollBack();
			            return;
			        }
			        
			        $UpdateAttendance->closeCursor();
		        }
		    }
	        
	        // Update dds in list
	        
	        if ( isset($Request["damage"]) && is_array( $Request["damage"] ) )
	        {
		        foreach( $Request["damage"] as $PlayerId )
		        {
		        	$UpdateAttendance = null;
		        	
		        	if ( intval($PlayerId) == 0 )
		        	{
		        		$UpdateAttendance = $Connector->prepare("INSERT INTO `".RP_TABLE_PREFIX."Attendance` ".
			    												"( CharacterId, UserId, RaidId, Status, Role ) ".
			    												"VALUES ( :CharacterId, 0, :RaidId, 'ok', 'dmg' )" );
			    	}
		        	else
		        	{
		        		$UpdateAttendance = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."Attendance` SET ".
			    												"Status = 'ok', Role = 'dmg' ".
			    												"WHERE RaidId = :RaidId AND (Status = 'available' OR Status = 'ok') AND CharacterId = :CharacterId LIMIT 1" );
			    	}
			    							
			    	$UpdateAttendance->bindValue(":RaidId", $Request["id"], PDO::PARAM_INT);
			        $UpdateAttendance->bindValue(":CharacterId", $PlayerId, PDO::PARAM_INT);
			               
			        if (!$UpdateAttendance->execute())
			        {
			        	postErrorMessage( $UpdateAttendance );
			        	$UpdateAttendance->closeCursor();
			        	$Connector->rollBack();
			            return;
			        }
			        
			        $UpdateAttendance->closeCursor();
		        }
		    }
	        
	        // Update players not in list
		    
		    if ( isset($Request["onBench"]) )
	        {    
		        $OnBench = "";
		        $firstItem = true;
		        
		        foreach( $Request["onBench"] as $PlayerId )
		        {
		        	if ( intval($PlayerId) != 0 )
		        	{
			        	if (!$firstItem)
			        		$OnBench .= " OR ";
			        	
			        	$OnBench .= "CharacterId = ".intval($PlayerId);
			        	$firstItem = false;
			        }
		        }
		        
		        if ( strlen( $OnBench ) > 0 )
		        {   
			        $UpdateBench = $Connector->prepare( "UPDATE `".RP_TABLE_PREFIX."Attendance` SET ".
			    										"Status = 'available' ".
			    										"WHERE RaidId = :RaidId AND Status = 'ok' AND (". $OnBench .")" );
			    										
			    	$UpdateBench->bindValue(":RaidId", $Request["id"], PDO::PARAM_INT);
			               
			        if (!$UpdateBench->execute())
			        {
			        	postErrorMessage( $UpdateBench );
			        	$UpdateBench->closeCursor();
			        	$Connector->rollBack();
			            return;
			        }
			        
			        $UpdateBench->closeCursor(); 
				}
			}
			
			$Connector->commit();
		}
        
        // reload detailed view
		
		msgRaidDetail( $Request );
    }
    else
    {
        echo "<error>".L("Access denied")."</error>";
    }
}

?>