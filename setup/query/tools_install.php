<?php
    require_once(dirname(__FILE__)."/../../lib/private/connector.class.php");

    function InstallDB($Prefix)
    {
        $Out = Out::getInstance();
        $Connector = Connector::getInstance();

        $Connector->exec( "CREATE TABLE IF NOT EXISTS `".$Prefix."Attendance` (
              `AttendanceId` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `CharacterId` int(10) unsigned NOT NULL,
              `UserId` int(11) unsigned NOT NULL,
              `RaidId` int(10) unsigned NOT NULL,
              `LastUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `Status` enum('ok','available','unavailable','undecided') NOT NULL,
              `Role` tinyint(1) unsigned NOT NULL,
              `Class` tinyint(2) NOT NULL DEFAULT '0',
              `Comment` text NOT NULL,
              PRIMARY KEY (`AttendanceId`),
              KEY `UserId` (`UserId`),
              KEY `CharacterId` (`CharacterId`),
              KEY `RaidId` (`RaidId`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;" );

        $Connector->exec( "CREATE TABLE IF NOT EXISTS `".$Prefix."Character` (
              `CharacterId` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `UserId` int(10) unsigned NOT NULL,
              `Name` varchar(64) NOT NULL,
              `Mainchar` enum('true','false') NOT NULL DEFAULT 'false',
              `Class` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
              `Role1` tinyint(1) unsigned NOT NULL,
              `Role2` tinyint(1) unsigned NOT NULL,
              PRIMARY KEY (`CharacterId`),
              KEY `UserId` (`UserId`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;" );

        $Connector->exec( "CREATE TABLE IF NOT EXISTS `".$Prefix."Location` (
              `LocationId` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `Name` varchar(128) NOT NULL,
              `Image` varchar(255) NOT NULL,
              PRIMARY KEY (`LocationId`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;" );

        $Connector->exec( "CREATE TABLE IF NOT EXISTS `".$Prefix."Raid` (
              `RaidId` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `LocationId` int(10) unsigned NOT NULL,
              `Stage` enum('open','locked','canceled') NOT NULL DEFAULT 'open',
              `Size` tinyint(2) unsigned NOT NULL,
              `Start` datetime NOT NULL,
              `End` datetime NOT NULL,
              `Mode` enum('manual','overbook','attend','all') NOT NULL,
              `Description` text NOT NULL,
              `SlotsRole1` tinyint(2) unsigned NOT NULL,
              `SlotsRole2` tinyint(2) unsigned  NOT NULL,
              `SlotsRole3` tinyint(2) unsigned  NOT NULL,
              `SlotsRole4` tinyint(2) unsigned  NOT NULL,
              `SlotsRole5` tinyint(2) unsigned  NOT NULL,
              PRIMARY KEY (`RaidId`),
              KEY `LocationId` (`LocationId`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;" );

        $Connector->exec( "CREATE TABLE IF NOT EXISTS `".$Prefix."Setting` (
              `SettingId` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `Name` varchar(64) NOT NULL,
              `IntValue` int(11) NOT NULL,
              `TextValue` varchar(255) NOT NULL,
              PRIMARY KEY (`SettingId`),
              FULLTEXT KEY `Name` (`Name`),
              UNIQUE KEY `Unique_Name` (`Name`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;" );

        $Connector->exec( "CREATE TABLE IF NOT EXISTS `".$Prefix."User` (
              `UserId` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `Group` enum('admin','raidlead','member','none') NOT NULL,
              `ExternalId` int(10) unsigned NOT NULL,
              `ExternalBinding` CHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
              `BindingActive` enum('true','false') NOT NULL DEFAULT 'true',
              `Login` varchar(255) NOT NULL,
              `Password` char(128) NOT NULL,
              `Salt` char(64) NOT NULL,
              `OneTimeKey` char(32) NOT NULL,
              `SessionKey` char(32) NOT NULL,
              `Created` datetime NOT NULL,
              PRIMARY KEY (`UserId`),
              KEY `ExternalId` (`ExternalId`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;" );

        $Connector->exec( "CREATE TABLE IF NOT EXISTS `".$Prefix."UserSetting` (
              `UserSettingId` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `UserId` int(10) unsigned NOT NULL,
              `Name` varchar(64) NOT NULL,
              `IntValue` int(11) NOT NULL,
              `TextValue` varchar(255) NOT NULL,
              PRIMARY KEY (`UserSettingId`),
              UNIQUE KEY `Unique_Name` (`Name`),
              KEY `UserId` (`UserId`),
              FULLTEXT KEY `Name` (`Name`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;" );
    }

    // ------------------------------------------------------------------------

    function InstallDefaultSettings($Prefix)
    {
        $Connector = Connector::getInstance();

        // Add default values for settings table

        $TestQuery = $Connector->prepare( "SELECT * FROM `".$Prefix."Setting`" );
        $ExistingSettings = array();

        $TestQuery->loop( function($Row) use ($ExistingSettings)
        {
            array_push($ExistingSettings, $Row["Name"]);
        });

        if ( !in_array("PurgeRaids", $ExistingSettings) )
            $Connector->exec( "INSERT INTO `".$Prefix."Setting` (`Name`, `IntValue`, `TextValue`) VALUES('PurgeRaids', 7257600, '');" );

        if ( !in_array("LockRaids", $ExistingSettings) )
            $Connector->exec( "INSERT INTO `".$Prefix."Setting` (`Name`, `IntValue`, `TextValue`) VALUES('LockRaids', 3600, '');" );

        if ( !in_array("RaidStartHour", $ExistingSettings) )
            $Connector->exec( "INSERT INTO `".$Prefix."Setting` (`Name`, `IntValue`, `TextValue`) VALUES('RaidStartHour', 19, '');" );

        if ( !in_array("RaidStartMinute", $ExistingSettings) )
            $Connector->exec( "INSERT INTO `".$Prefix."Setting` (`Name`, `IntValue`, `TextValue`) VALUES('RaidStartMinute', 30, '');" );

        if ( !in_array("RaidEndHour", $ExistingSettings) )
            $Connector->exec( "INSERT INTO `".$Prefix."Setting` (`Name`, `IntValue`, `TextValue`) VALUES('RaidEndHour', 23, '');" );

        if ( !in_array("RaidEndMinute", $ExistingSettings) )
            $Connector->exec( "INSERT INTO `".$Prefix."Setting` (`Name`, `IntValue`, `TextValue`) VALUES('RaidEndMinute', 0, '');" );

        if ( !in_array("RaidSize", $ExistingSettings) )
            $Connector->exec( "INSERT INTO `".$Prefix."Setting` (`Name`, `IntValue`, `TextValue`) VALUES('RaidSize', 10, '');" );

        if ( !in_array("RaidMode", $ExistingSettings) )
            $Connector->exec( "INSERT INTO `".$Prefix."Setting` (`Name`, `IntValue`, `TextValue`) VALUES('RaidMode', 0, 'manual');" );

        if ( !in_array("Site", $ExistingSettings) )
            $Connector->exec( "INSERT INTO `".$Prefix."Setting` (`Name`, `IntValue`, `TextValue`) VALUES('Site', 0, '');" );

        if ( !in_array("HelpPage", $ExistingSettings) )
            $Connector->exec( "INSERT INTO `".$Prefix."Setting` (`Name`, `IntValue`, `TextValue`) VALUES('HelpPage', 0, '');" );

        if ( !in_array("Theme", $ExistingSettings) )
            $Connector->exec( "INSERT INTO `".$Prefix."Setting` (`Name`, `IntValue`, `TextValue`) VALUES('Theme', 0, 'cataclysm');" );

        if ( !in_array("TimeFormat", $ExistingSettings) )
            $Connector->exec( "INSERT INTO `".$Prefix."Setting` (`Name`, `IntValue`, `TextValue`) VALUES('TimeFormat', 24, '');" );

        if ( !in_array("StartOfWeek", $ExistingSettings) )
            $Connector->exec( "INSERT INTO `".$Prefix."Setting` (`Name`, `IntValue`, `TextValue`) VALUES('StartOfWeek', 1, '');" );

        if ( !in_array("Version", $ExistingSettings) )
            $Connector->exec( "INSERT INTO `".$Prefix."Setting` (`Name`, `IntValue`, `TextValue`) VALUES('Version', 110, '');" );
        else
            $Connector->exec( "UPDATE `".$Prefix."Setting` SET IntValue=110 WHERE Name='Version' LIMIT 1" );
    }
    
    // ------------------------------------------------------------------------
    
    function UpdateGameConfig110($aGameConfig100)
    {
        $Icons = Array(
            "slot_role1.png" => "role_melee",
            "slot_role2.png" => "role_heal",
            "slot_role3.png" => "role_support",
            "slot_role4.png" => "role_tank",
        );
    
        include_once($aGameConfig100);
        $NewGameConfig = fopen(dirname(__FILE__)."/../../lib/config/config.game.php", "w");
        
        if ($NewGameConfig === false)
            return false;
        
        fwrite($NewGameConfig, "<?php\n");
        fwrite($NewGameConfig, "\t\$gClassMode = \"single\";\n\n");
        
        // Roles
        
        fwrite($NewGameConfig, "\t\$gRoles = Array(\n");
        
        reset($gRoles);
        for($RoleIdx=0; list($Ident, $Loca) = each($gRoles); ++$RoleIdx)
        {
            $Style = (isset($Icons[strtolower($gRoleImages[$RoleIdx])]))
                ? $Icons[strtolower($gRoleImages[$RoleIdx])]
                : "role_range";
                
            fwrite($NewGameConfig, "\t\t\"".$Ident."\" => Array( ".$RoleIdx.", ".$gRoleColumnCount[$RoleIdx].", \"".$Loca."\", \"".$Style."\" ),\n");
        }
        
        fwrite($NewGameConfig, "\t);\n\n");
        
        // Classes
        
        fwrite($NewGameConfig, "\t\$gClasses = Array(\n");
        
        reset($gClasses);
        for ($ClassIdx=0; list($Ident, $Data) = each($gClasses); ++$ClassIdx)
        {
            $Roles = array();
            foreach($Data[2] as $Role)
            {
                array_push($Roles, "\"".$Role."\"");    
            }
            
            fwrite($NewGameConfig, "\t\t\"".$Ident."\" => Array( ".$ClassIdx.", \"".$Data[0]."\", \"".$Data[1]."\", Array(".implode(",", $Roles).") ),\n");
        }
        
        fwrite($NewGameConfig, "\t);\n\n");
        
        // Group sizes
        
        fwrite($NewGameConfig, "\t\$gGroupSizes = Array(\n");
        
        reset($gGroupSizes);
        while(list($Count,$Slots) = each($gGroupSizes))
        {
            fwrite($NewGameConfig, "\t\t".$Count." => Array(".implode(",", $Slots)."),\n");
        }
        
        fwrite($NewGameConfig, "\t);\n");
        
        // Close
        
        fwrite($NewGameConfig, "?>\n");
        fclose($NewGameConfig);
        
        unset($gRoles);
        unset($gRoleImages);
        unset($gRoleColumnCount);
        unset($gClases);
        unset($gGroupSizes);
        
        return true;
    }
?>