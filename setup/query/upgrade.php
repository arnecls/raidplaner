<?php
    header("Content-type: text/xml");
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
    
	require_once("../../lib/private/connector.class.php");
	require_once(dirname(__FILE__)."/../../lib/config/config.php");
	
	echo "<upgrade>";
	
	// ----------------------------------------------------------------------------
	
	function doUpgrade( $a_Statement)
	{
        $Connector = Connector::GetInstance();
        $Connector->beginTransaction();
        
        while ( list($name, $query) = each($a_Statement) )
        {
            echo "<step name=\"".$name."\">";
            
            $Action = $Connector->prepare( $query );
            if ( !$Action->execute() )
            {
                postErrorMessage( $Action );
            }
            
            echo "</step>";
        }
        
        $Connector->commit();
	}
	
	// ----------------------------------------------------------------------------
	
	function upgrade_092()
	{
	   echo "<update version=\"92\">";
	   
	   $queries = Array( "External binding" => "ALTER TABLE `".RP_TABLE_PREFIX."User` CHANGE `ExternalBinding` `ExternalBinding` ENUM('none',  'phpbb3',  'eqdkp',  'vb3') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;" );
	   
	   doUpgrade( $queries );
	   echo "</update>";
	}
	
	// ----------------------------------------------------------------------------
	
	function upgrade_093()
	{
        echo "<update version=\"93\">";
        
        $Connector = Connector::GetInstance();
        
        // Check for exisiting unique index
        
        $queries1 = Array();
        
        $IndexStatement = $Connector->prepare( "SHOW INDEXES FROM `".RP_TABLE_PREFIX."Setting` WHERE Key_Name='Unique_Name'" );
        if ( $IndexStatement->execute() )
        {
            if ( !$IndexStatement->fetch(PDO::FETCH_ASSOC) )
            {
                $queries1["Unique setting names"] = "ALTER TABLE  `".RP_TABLE_PREFIX."Setting` ADD CONSTRAINT `Unique_Name` UNIQUE (`Name`);";
            }
	    }
	    
	    $IndexStatement->closeCursor();
	    
	    // Static updates
        
        $queries2 = Array( "Creation date field"  => "ALTER TABLE `".RP_TABLE_PREFIX."User` ADD  `Created` DATETIME NOT NULL;",
                           "User creation date"   => "UPDATE `".RP_TABLE_PREFIX."User` SET Created = NOW();",
                           "Raid start hour"      => "INSERT INTO `".RP_TABLE_PREFIX."Setting` (`SettingId`, `Name`, `IntValue`, `TextValue`) VALUES (NULL, 'RaidStartHour', '19', '');",
                           "Raid start minute"    => "INSERT INTO `".RP_TABLE_PREFIX."Setting` (`SettingId`, `Name`, `IntValue`, `TextValue`) VALUES (NULL, 'RaidStartMinute', '30', '');",
                           "Raid end hour"        => "INSERT INTO `".RP_TABLE_PREFIX."Setting` (`SettingId`, `Name`, `IntValue`, `TextValue`) VALUES (NULL, 'RaidEndHour', '23', '');",
                           "Raid end minute"      => "INSERT INTO `".RP_TABLE_PREFIX."Setting` (`SettingId`, `Name`, `IntValue`, `TextValue`) VALUES (NULL, 'RaidEndMinute', '0', '');",
                           "Raid size"            => "INSERT INTO `".RP_TABLE_PREFIX."Setting` (`SettingId`, `Name`, `IntValue`, `TextValue`) VALUES (NULL, 'RaidSize', '10', '');",
                           "Site"                 => "INSERT INTO `".RP_TABLE_PREFIX."Setting` (`SettingId`, `Name`, `IntValue`, `TextValue`) VALUES (NULL, 'Site', '', '');",
                           "Banner"               => "INSERT INTO `".RP_TABLE_PREFIX."Setting` (`SettingId`, `Name`, `IntValue`, `TextValue`) VALUES (NULL, 'Banner', '', 'cata');",
                           "Current version"      => "INSERT INTO `".RP_TABLE_PREFIX."Setting` (`SettingId`, `Name`, `IntValue`, `TextValue`) VALUES (NULL, 'Version', '94', '');" );
        
        doUpgrade( array_merge($queries1, $queries2) );
        
        // Update user creation dates
        
        echo "<step name=\"User creation date detection\">";
            
        $DataStatement = $Connector->prepare( "SELECT `".RP_TABLE_PREFIX."Character`.UserId, `".RP_TABLE_PREFIX."Raid`.Start FROM `".RP_TABLE_PREFIX."Character` ".
                                              "LEFT JOIN `".RP_TABLE_PREFIX."Attendance` USING (CharacterId) ".
                                              "LEFT JOIN `".RP_TABLE_PREFIX."Raid` USING (RaidId) ".
                                              "GROUP BY `".RP_TABLE_PREFIX."Character`.UserId ".
                                              "ORDER BY `".RP_TABLE_PREFIX."Raid`.Start, `".RP_TABLE_PREFIX."Character`.UserId" );
                                               
        if ( $DataStatement->execute() )
        {                          
            $UpdateString = "";
            
            while ( $Data = $DataStatement->fetch( PDO::FETCH_ASSOC ) )
	        {
                $UpdateString .= "UPDATE `".RP_TABLE_PREFIX."User` SET Created='".$Data["Start"]."' WHERE UserId=".intval($Data["UserId"])." LIMIT 1;";
            }
            
            $Connector->beginTransaction();
            $Action = $Connector->prepare( $UpdateString );
            
            if ( !$Action->execute() )
            {
                postErrorMessage( $Action );
                $Connector->rollback();
            }
            else
            {
                $Connector->commit();
            }
        }
        
        $DataStatement->closeCursor();
                          
        echo "</step>";
        echo "</update>";
	}
	
	// ----------------------------------------------------------------------------
	
	function upgrade_094()
	{
		echo "<update version=\"94\">";
		
		$updates = Array( "Monk class support"    => "ALTER TABLE  `".RP_TABLE_PREFIX."Character` CHANGE  `Class`  `Class` ENUM('deathknight','druid','hunter','mage','monk','paladin','priest','rogue','shaman','warlock','warrior');",
						  "Timestamp setting"     => "INSERT INTO `".RP_TABLE_PREFIX."Setting` (`Name`, `IntValue`, `TextValue`) VALUES('TimeFormat', 24, '');",
						  "Remove banner setting" => "DELETE FROM `".RP_TABLE_PREFIX."Setting` WHERE Name = 'Banner' LIMIT 1;",
						  "Theme setting" 	      => "INSERT INTO `".RP_TABLE_PREFIX."Setting` (`Name`, `IntValue`, `TextValue`) VALUES('Theme', '', 'default');" );
		
		doUpgrade( $updates );
		
        echo "</update>";
	}
	
	// ----------------------------------------------------------------------------
	
	function setVersion( $a_Version )
	{
        $Connector = Connector::GetInstance();
        $Connector->exec( "UPDATE `".RP_TABLE_PREFIX."Setting` SET IntValue=".intval($a_Version)." WHERE Name='Version';" );
	}
	
	// ----------------------------------------------------------------------------
	
	if ( isset($_REQUEST["version"]) )
	{
    	switch ( $_REQUEST["version"] )
    	{
    	case 92:
            upgrade_092();
        case 93:
            upgrade_093();
        case 94:
        	upgrade_094();
        default:
            setVersion(95);
            break;
    	}
    }
    	
	echo "</upgrade>";
?>