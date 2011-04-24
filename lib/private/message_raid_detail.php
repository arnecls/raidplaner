<?php
	function msgRaidDetail( $Request )
	{
		if (ValidUser())
	    {
	    	$Connector = Connector::GetInstance();
	    	
	        $ListRaidSt = $Connector->prepare("Select ".RP_TABLE_PREFIX."Raid.*, ".RP_TABLE_PREFIX."Location.Name AS LocationName, ".RP_TABLE_PREFIX."Location.Image AS LocationImage, ".
	        								  RP_TABLE_PREFIX."Attendance.UserId, ".RP_TABLE_PREFIX."Attendance.CharacterId, ".RP_TABLE_PREFIX."Attendance.Status, ".RP_TABLE_PREFIX."Attendance.Role, ".RP_TABLE_PREFIX."Attendance.Comment, ".
	        								  RP_TABLE_PREFIX."Character.Name, ".RP_TABLE_PREFIX."Character.Class, ".RP_TABLE_PREFIX."Character.Mainchar, ".RP_TABLE_PREFIX."Character.Role1, ".RP_TABLE_PREFIX."Character.Role2 ".
	        								  "FROM `".RP_TABLE_PREFIX."Raid` ".
	        								  "LEFT JOIN `".RP_TABLE_PREFIX."Location` USING(LocationId) ".
	        								  "LEFT JOIN `".RP_TABLE_PREFIX."Attendance` USING(RaidId) ".
	        								  "LEFT JOIN `".RP_TABLE_PREFIX."Character` USING(CharacterId) ".
	        								  "WHERE RaidId = :RaidId");
	        
	        $ListRaidSt->bindValue( ":RaidId", $Request["id"], PDO::PARAM_INT );
	        
	        if (!$ListRaidSt->execute())
	        {
	        	postErrorMessage( $ListRaidSt );
	        }
	        else
	        {
	        	echo "<raid>";
	        	
	        	$Data = $ListRaidSt->fetch( PDO::FETCH_ASSOC );
	        	
	        	echo "<raidId>".$Data["RaidId"]."</raidId>";
                echo "<locationId>".$Data["LocationId"]."</locationId>";
                echo "<location>".$Data["LocationName"]."</location>";
		        echo "<stage>".$Data["Stage"]."</stage>";
                echo "<image>".$Data["LocationImage"]."</image>";
                echo "<size>".$Data["Size"]."</size>";
                echo "<startDate>".substr( $Data["Start"], 0, 10 )."</startDate>";
                echo "<start>".substr( $Data["Start"], 11, 5 )."</start>";
                echo "<end>".substr( $Data["End"], 11, 5 )."</end>";
                echo "<description>".$Data["Description"]."</description>";
                echo "<tankSlots>".$Data["TankSlots"]."</tankSlots>";
                echo "<dmgSlots>".$Data["DmgSlots"]."</dmgSlots>";
                echo "<healSlots>".$Data["HealSlots"]."</healSlots>";
        	
                if ( $Data["UserId"] != NULL )
                {
		        	do
		        	{
		        		if ( ($Data["CharacterId"] == 0) && ($Data["UserId"] != 0) )
		        		{
		        			$CharSt = $Connector->prepare(	"SELECT ".RP_TABLE_PREFIX."Character.*, ".RP_TABLE_PREFIX."User.Login AS UserName ".
		        											"FROM `".RP_TABLE_PREFIX."User` LEFT JOIN `".RP_TABLE_PREFIX."Character` USING(UserId) ".
		        											"WHERE UserId = :UserId ORDER BY Mainchar, CharacterId ASC LIMIT 1" );
		        			
		        			$CharSt->bindValue( ":UserId", $Data["UserId"], PDO::PARAM_INT );
		        			
		        			if (!$CharSt->execute())
		        			{
		        				postErrorMessage( $ErrorInfo );
		        			}
		        			else
		        			{
		        				$DefaultData = $CharSt->fetch( PDO::FETCH_ASSOC );
		        				
		        				echo "<attendee>";
		        				
		        				if ( $DefaultData["CharacterId"] == NULL )
		        				{
		        					echo "<id>0</id>";
			        				echo "<name>[".$DefaultData["UserName"]."]</name>";
			        				echo "<mainchar>false</mainchar>";
		        					echo "<class>empty</class>";
			        				echo "<role>dmg</role>";
			        				echo "<role1>dmg</role1>";
			        				echo "<role2>dmg</role2>";
		        				}
		        				else
		        				{
		        					echo "<id>".$DefaultData["CharacterId"]."</id>";
			        				echo "<name>".$DefaultData["Name"]."</name>";
			        				echo "<mainchar>".$DefaultData["Mainchar"]."</mainchar>";
		        					echo "<class>".$DefaultData["Class"]."</class>";
			        				echo "<role>".$DefaultData["Role1"]."</role>";
			        				echo "<role1>".$DefaultData["Role1"]."</role1>";
			        				echo "<role2>".$DefaultData["Role2"]."</role2>";		        		
			        			}
		        		
			        			echo "<status>".$Data["Status"]."</status>";
			        			echo "<comment>".$Data["Comment"]."</comment>";
			        			echo "</attendee>";
		        			}
		        			
		        			$CharSt->closeCursor();
		        		}
		        		else
		        		{
		        			echo "<attendee>";
		        		
		        			echo "<id>".$Data["CharacterId"]."</id>";
		        			echo "<name>".$Data["Name"]."</name>";
		        			echo "<class>".$Data["Class"]."</class>";
		        			echo "<mainchar>".$Data["Mainchar"]."</mainchar>";
		        			echo "<role>".$Data["Role"]."</role>";
		        			echo "<role1>".$Data["Role1"]."</role1>";
		        			echo "<role2>".$Data["Role2"]."</role2>";		        		
		        		
		        			echo "<status>".$Data["Status"]."</status>";
		        			echo "<comment>".$Data["Comment"]."</comment>";
		        			echo "</attendee>";
		        		}		        		
		        	}
		        	while ( $Data = $ListRaidSt->fetch( PDO::FETCH_ASSOC ) );
	        	}
	        	
	        	echo "</raid>";	        		        
	        }
	        
	        $ListRaidSt->closeCursor();
	        
	        echo "<locations>";
	        
	        msgQueryLocations( $Request );
	        
	        echo "</locations>";
		}
	    else
	    {
	        echo "<error>".L("Access denied")."</error>";
	    }
	}
?>