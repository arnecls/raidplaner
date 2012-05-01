<?php
	header("Content-type: text/xml");
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
    echo "<database>";
    
	require_once("../../lib/private/connector.class.php");
	
	$configFile = fopen( "../../lib/config/config.php", "w+" );
	
	fwrite( $configFile, "<?php\n");
	
	fwrite( $configFile, "\tdefine(\"SQL_HOST\", \"".$_REQUEST["host"]."\");\n");	
	fwrite( $configFile, "\tdefine(\"RP_DATABASE\", \"".$_REQUEST["database"]."\");\n");
	fwrite( $configFile, "\tdefine(\"RP_USER\", \"".$_REQUEST["user"]."\");\n");
	fwrite( $configFile, "\tdefine(\"RP_PASS\", \"".$_REQUEST["password"]."\");\n");
	fwrite( $configFile, "\tdefine(\"RP_TABLE_PREFIX\", \"".$_REQUEST["prefix"]."\");\n");	
	fwrite( $configFile, "\tdefine(\"ALLOW_REGISTRATION\", ".$_REQUEST["register"].");\n");
	
	fwrite( $configFile, "?>");	
	fclose( $configFile );
	
	require_once("../../lib/config/config.php");

	$connector = Connector::GetInstance();
	
	$connector->exec( "CREATE TABLE IF NOT EXISTS `".$_REQUEST["prefix"]."Attendance` (
		  `CharacterId` int(10) unsigned NOT NULL,
		  `UserId` int(11) unsigned NOT NULL,
		  `RaidId` int(10) unsigned NOT NULL,
		  `Status` enum('available','unavailable','ok') NOT NULL,
		  `Role` enum('dmg','heal','tank') NOT NULL,
		  `Comment` text NOT NULL,
		  KEY `UserId` (`UserId`),
		  KEY `CharacterId` (`CharacterId`),
		  KEY `RaidId` (`RaidId`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;" );
	
	$connector->exec( "CREATE TABLE IF NOT EXISTS `".$_REQUEST["prefix"]."Character` (
		  `CharacterId` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `UserId` int(10) unsigned NOT NULL,
		  `Name` varchar(64) NOT NULL,
		  `Mainchar` enum('true','false') NOT NULL DEFAULT 'false',
		  `Class` enum('deathknight','druid','hunter','mage','monk','paladin','priest','rogue','shaman','warlock','warrior') NOT NULL,
		  `Role1` enum('dmg','heal','tank') NOT NULL,
		  `Role2` enum('dmg','heal','tank') NOT NULL,
		  PRIMARY KEY (`CharacterId`),
		  KEY `UserId` (`UserId`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;" );
		
	$connector->exec( "CREATE TABLE IF NOT EXISTS `".$_REQUEST["prefix"]."Location` (
		  `LocationId` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `Name` varchar(128) NOT NULL,
		  `Image` varchar(255) NOT NULL,
		  PRIMARY KEY (`LocationId`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;" );
		
	$connector->exec( "CREATE TABLE IF NOT EXISTS `".$_REQUEST["prefix"]."Raid` (
		  `RaidId` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `LocationId` int(10) unsigned NOT NULL,
		  `Stage` enum('open','locked','canceled') NOT NULL DEFAULT 'open',
		  `Size` tinyint(2) NOT NULL,
		  `Start` datetime NOT NULL,
		  `End` datetime NOT NULL,
		  `Description` text NOT NULL,
		  `TankSlots` int(11) NOT NULL,
		  `DmgSlots` int(11) NOT NULL,
		  `HealSlots` int(11) NOT NULL,
		  PRIMARY KEY (`RaidId`),
		  KEY `LocationId` (`LocationId`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;" );
		
	$connector->exec( "CREATE TABLE IF NOT EXISTS `".$_REQUEST["prefix"]."Setting` (
		  `SettingId` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `Name` varchar(64) NOT NULL,
		  `IntValue` int(11) NOT NULL,
		  `TextValue` varchar(255) NOT NULL,
		  PRIMARY KEY (`SettingId`),
		  FULLTEXT KEY `Name` (`Name`),
		  UNIQUE KEY `Unique_Name` (`Name`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;" );
		
	$connector->exec( "CREATE TABLE IF NOT EXISTS `".$_REQUEST["prefix"]."User` (
		  `UserId` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `Group` enum('admin','raidlead','member','none') NOT NULL,
		  `ExternalId` int(10) unsigned NOT NULL,
		  `ExternalBinding` enum('none', 'phpbb3', 'eqdkp', 'vb3') NOT NULL,
		  `Login` varchar(255) NOT NULL,
		  `Password` char(64) NOT NULL,
		  `Hash` char(32) NOT NULL,
		  `Created` datetime NOT NULL,
		  PRIMARY KEY (`UserId`),
		  KEY `ExternalId` (`ExternalId`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;" );
		
	$testSt = $connector->prepare( "SELECT * FROM `".$_REQUEST["prefix"]."Setting` LIMIT 1" );
	$testSt->execute();
	
	if ( $testSt->rowCount() == 0 )
	{
		$connector->exec( "INSERT INTO `".$_REQUEST["prefix"]."Setting` (`Name`, `IntValue`, `TextValue`) VALUES('PurgeRaids', 7257600, '');" );
		$connector->exec( "INSERT INTO `".$_REQUEST["prefix"]."Setting` (`Name`, `IntValue`, `TextValue`) VALUES('LockRaids', 3600, '');" );		
		$connector->exec( "INSERT INTO `".$_REQUEST["prefix"]."Setting` (`Name`, `IntValue`, `TextValue`) VALUES('RaidStartHour', 19, '');" );
		$connector->exec( "INSERT INTO `".$_REQUEST["prefix"]."Setting` (`Name`, `IntValue`, `TextValue`) VALUES('RaidStartMinute', 30, '');" );
		$connector->exec( "INSERT INTO `".$_REQUEST["prefix"]."Setting` (`Name`, `IntValue`, `TextValue`) VALUES('RaidEndHour', 23, '');" );
		$connector->exec( "INSERT INTO `".$_REQUEST["prefix"]."Setting` (`Name`, `IntValue`, `TextValue`) VALUES('RaidEndMinute', 0, '');" );
		$connector->exec( "INSERT INTO `".$_REQUEST["prefix"]."Setting` (`Name`, `IntValue`, `TextValue`) VALUES('RaidSize', 10, '');" );
		$connector->exec( "INSERT INTO `".$_REQUEST["prefix"]."Setting` (`Name`, `IntValue`, `TextValue`) VALUES('Site', '', '');" );
		$connector->exec( "INSERT INTO `".$_REQUEST["prefix"]."Setting` (`Name`, `IntValue`, `TextValue`) VALUES('Theme', '', 'default');" );
		$connector->exec( "INSERT INTO `".$_REQUEST["prefix"]."Setting` (`Name`, `IntValue`, `TextValue`) VALUES('TimeFormat', 24, '');" );
		$connector->exec( "INSERT INTO `".$_REQUEST["prefix"]."Setting` (`Name`, `IntValue`, `TextValue`) VALUES('Version', 95, '');" );	
	}
	
	$testSt->closeCursor();
	
	$testSt = $connector->prepare( "SELECT * FROM `".$_REQUEST["prefix"]."User` LIMIT 1" );
	$testSt->execute();
	
	if ( $testSt->rowCount() == 0 )
	{
		$Salt = sha1( strval(microtime() + rand()) . $_SERVER["REMOTE_ADDR"] );
		$Hash = sha1( "admin".$_REQUEST["adminpass"] );			
		$Hash = md5( $Salt.$Hash );
			
		$connector->exec( "INSERT INTO `".$_REQUEST["prefix"]."User` VALUES(1, 'admin', 0, 'none', 'admin', '".sha1($_REQUEST["adminpass"])."', '".$Hash."', FROM_UNIXTIME(".time()."));" );
	}
	
	$testSt->closeCursor();
	
	echo "</database>";
?>