<?php
    require_once(dirname(__FILE__)."/../../lib/private/connector.class.php");
    require_once(dirname(__FILE__)."/../../lib/private/random.class.php");
    require_once(dirname(__FILE__)."/column.class.php");
    require_once(dirname(__FILE__)."/key.class.php");
    
    // -------------------------------------------------------------------------
    
    $gDatabaseLayout = Array(
        "Attendance" => Array(
            new Column("AttendanceId", "int",          10,                                                 Array("unsigned", "NOT NULL", "AUTO_INCREMENT")),
            new Column("CharacterId",  "int",          10,                                                 Array("unsigned", "NOT NULL")),
            new Column("UserId",       "int",          10,                                                 Array("unsigned", "NOT NULL")),
            new Column("RaidId",       "int",          10,                                                 Array("unsigned", "NOT NULL")),
            new Column("LastUpdate",   "timestamp",    null,                                               Array("DEFAULT CURRENT_TIMESTAMP")),
            new Column("Status",       "enum",         Array('ok','available','unavailable','undecided'),  Array("NOT NULL")),
            new Column("Role",         "char",         3,                                                  Array("NOT NULL")),
            new Column("Class",        "char",         3,                                                  Array("NOT NULL")),
            new Column("Comment",      "text",         null,                                               Array("NOT NULL")),
            new Key(   "primary",      "AttendanceId"),
            new Key(   "",             "UserId"),
            new Key(   "",             "CharacterId"),
            new Key(   "",             "RaidId"),
            new Key(   "",             "Status"),
            new Key(   "",             "UserId,Status"),
            new Key(   "",             "UserId,CharacterId"),

        ),
        
        "Character" => Array(
            new Column("CharacterId",  "int",      10,                     Array("unsigned", "NOT NULL", "AUTO_INCREMENT")),
            new Column("UserId",       "int",      10,                     Array("unsigned", "NOT NULL")),
            new Column("Game",         "char",     4,                      Array("NOT NULL")),
            new Column("Name",         "varchar",  64,                     Array("NOT NULL")),
            new Column("Mainchar",     "enum",     Array('true','false'),  Array("NOT NULL", "DEFAULT 'false'")),
            new Column("Class",        "varchar",  128,                    Array("NOT NULL")),
            new Column("Role1",        "char",     3,                      Array("NOT NULL")),
            new Column("Role2",        "char",     3,                      Array("NOT NULL")),
            new Key(   "primary",      "CharacterId"),
            new Key(   "",             "UserId"),
            new Key(   "",             "Game"),
            new Key(   "",             "Game,UserId")
        ),
        
        "Location" => Array(
            new Column("LocationId",   "int",      10,     Array("unsigned", "NOT NULL", "AUTO_INCREMENT")),
            new Column("Game",         "char",     4,      Array("NOT NULL")),
            new Column("Name",         "varchar",  128,    Array("NOT NULL")),
            new Column("Image",        "varchar",  255,    Array("NOT NULL")),
            new Key(   "primary",      "LocationId"),
            new Key(   "",             "Game")
        ),
        
        "Raid" => Array(
            new Column("RaidId",       "int",       10,                                         Array("unsigned", "NOT NULL", "AUTO_INCREMENT")),
            new Column("LocationId",   "int",       10,                                         Array("unsigned", "NOT NULL")),
            new Column("Stage",        "enum",      Array('open','locked','canceled'),          Array("NOT NULL", "DEFAULT 'open'")),
            new Column("Size",         "tinyint",   2,                                          Array("unsigned", "NOT NULL")),
            new Column("Start",        "datetime",  null,                                       Array("NOT NULL")),
            new Column("End",          "datetime",  null,                                       Array("NOT NULL")),
            new Column("Mode",         "enum",      Array('manual','overbook','attend','all'),  Array("NOT NULL")),
            new Column("Description",  "text",      null,                                       Array("NOT NULL")),
            new Column("SlotRoles",    "varchar",   24,                                         Array("NOT NULL")),
            new Column("SlotCount",    "varchar",   12,                                         Array("NOT NULL")),
            new Key(   "primary",      "RaidId"),
            new Key(   "",             "LocationId"),
            new Key(   "",             "Start")
        ),
        
        "Session" => Array(
            new Column("SessionId",    "int",       10,     Array("unsigned", "NOT NULL", "AUTO_INCREMENT")),
            new Column("UserId",       "int",       10,     Array("NOT NULL")),
            new Column("SessionName",  "char",      40,     Array("NOT NULL")),
            new Column("IpAddress",    "char",      40,     Array("NOT NULL")),
            new Column("Expires",      "timestamp", null,   Array("DEFAULT CURRENT_TIMESTAMP")),
            new Column("Data",         "text",      null,   Array("NOT NULL")),
            new Key(   "primary",      "SessionId"),
            new Key(   "unique",       "SessionName"),
            new Key(   "",             "UserId"),
        ),
        
        "Setting" => Array(
            new Column("SettingId",    "int",      10,     Array("unsigned", "NOT NULL", "AUTO_INCREMENT")),
            new Column("Name",         "varchar",  64,     Array("NOT NULL")),
            new Column("IntValue",     "int",      11,     Array("NOT NULL")),
            new Column("TextValue",    "varchar",  255,    Array("NOT NULL")),
            new Key(   "primary",      "SettingId"),
            new Key(   "",             "Name"),
            new Key(   "unique",       "Name")
        ),
        
        "User" => Array(
            new Column("UserId",           "int",      10,                                         Array("unsigned", "NOT NULL", "AUTO_INCREMENT")),
            new Column("Group",            "enum",     Array('admin','raidlead','member','none'),  Array("NOT NULL", "DEFAULT 'none'")),
            new Column("ExternalId",       "int",      10,                                         Array("unsigned", "NOT NULL")),
            new Column("ExternalBinding",  "char",     10,                                         Array("NOT NULL")),
            new Column("BindingActive",    "enum",     Array('true','false'),                      Array("NOT NULL", "DEFAULT 'true'")),
            new Column("Login",            "varchar",  255,                                        Array("NOT NULL")),
            new Column("Password",         "char",     128,                                        Array("NOT NULL")),
            new Column("Salt",             "char",     64,                                         Array("NOT NULL")),
            new Column("OneTimeKey",       "char",     32,                                         Array("NOT NULL")),
            new Column("Created",          "datetime", null,                                       Array("NOT NULL")),
            new Key(   "primary",          "UserId"),
            new Key(   "",                 "ExternalId")
        ),
        
        "UserSetting" => Array(
            new Column("UserSettingId",    "int",      10,     Array("unsigned", "NOT NULL", "AUTO_INCREMENT")),
            new Column("UserId",           "int",      10,     Array("unsigned", "NOT NULL")),
            new Column("Name",             "varchar",  64,     Array("NOT NULL")),
            new Column("IntValue",         "int",      11,     Array("NOT NULL")),
            new Column("TextValue",        "varchar",  255,    Array("NOT NULL")),
            new Key(   "primary",          "UserSettingId"),
            new Key(   "",                 "UserId"),
            new Key(   "",                 "Name"),
            new Key(   "",                 "UserId,Name")
        )
    );
    
    // ------------------------------------------------------------------------

    function InstallDB($Prefix)
    {
        global $gDatabaseLayout;
        
        $Out = Out::getInstance();
        $Connector = Connector::getInstance();
                
        foreach($gDatabaseLayout as $Name => $Rows)
        {
            $QueryString = "CREATE TABLE IF NOT EXISTS `".$Prefix.$Name."` (";
            $FirstRow = true;
            
            foreach($Rows as $Row)
            {
                $QueryString .= (($FirstRow) ? "" : ",").$Row->CreateText();
                $FirstRow = false;
            }
            
            $QueryString .= ") ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
            $Connector->exec($QueryString);
        }
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

        if ( !in_array("GameConfig", $ExistingSettings) )
            $Connector->exec( "INSERT INTO `".$Prefix."Setting` (`Name`, `IntValue`, `TextValue`) VALUES('GameConfig', 0, 'wow');" );

        if ( !in_array("TimeFormat", $ExistingSettings) )
            $Connector->exec( "INSERT INTO `".$Prefix."Setting` (`Name`, `IntValue`, `TextValue`) VALUES('TimeFormat', 24, '');" );

        if ( !in_array("StartOfWeek", $ExistingSettings) )
            $Connector->exec( "INSERT INTO `".$Prefix."Setting` (`Name`, `IntValue`, `TextValue`) VALUES('StartOfWeek', 1, '');" );
            
        if ( !in_array("PrimaryRole", $ExistingSettings) )
            $Connector->exec( "INSERT INTO `".$Prefix."Setting` (`Name`, `IntValue`, `TextValue`) VALUES('PrimaryRole', 0, 'true');" );
            
        if ( !in_array("ApiPrivate", $ExistingSettings) )
        {
            $PrivateToken = dechex(crc32(Random::getBytes(2048))).dechex(crc32(Random::getBytes(2048)));
            $Connector->exec( "INSERT INTO `".$Prefix."Setting` (`Name`, `IntValue`, `TextValue`) VALUES('ApiPrivate', 0, '".$PrivateToken."');" );
        }
        
        if ( !in_array("Version", $ExistingSettings) )
            $Connector->exec( "INSERT INTO `".$Prefix."Setting` (`Name`, `IntValue`, `TextValue`) VALUES('Version', 110, '');" );
        else
            $Connector->exec( "UPDATE `".$Prefix."Setting` SET IntValue=110 WHERE Name='Version' LIMIT 1" );
    }
    
    // ------------------------------------------------------------------------
    
    function RemoveLast($aCandidates, $aString)
    {
        for ($i = strlen($aString)-1; $i>0; --$i)
        {
            if ( in_array($aString[$i], $aCandidates) )
            {
                return substr($aString, 0, $i).substr($aString, $i+1);
            }
        }
        
        return $aString;
    }
    
    // ------------------------------------------------------------------------
    
    function StripDuplicates($aString)
    {
        $Result = $aString[0];
        $Last = $aString[0];
        $Chars = Array($Last);
        
        for ($i=1; $i<strlen($aString); ++$i)
        {
            if (($aString[$i] != $Last) && !in_array($aString[$i], $Chars))
            {
                $Result .= $aString[$i];
                array_push($Chars, $aString[$i]);
            }
               
            $Last = $aString[$i];
        }
                
        return $Result;
    }
    
    // ------------------------------------------------------------------------
    
    function IsAlternating($aString, $aChars)
    {
        $State = in_array($aString[0], $aChars);
        for ($i=1; $i<strlen($aString); ++$i)
        {
            $NewState = in_array($aString[$i], $aChars);
            if ($NewState == $State)
                return false;
                
            $State = $NewState;
        }
        
        return true;
    }
    
    // ------------------------------------------------------------------------
    
    function BuildXCC($aName, $aCount)
    {
        $Id = StripDuplicates(strtolower($aName));
        
        while (strlen($Id) < $aCount)
        {
            $Id .= "_";
        }
        
        if (strlen($Id) == 3)
            return $Id;
        
        $Replace = Array("a","e","i","o","u"); 
         
        if (IsAlternating(substr($Id,0,$aCount+1), $Replace))
            return substr($Id,0,$aCount);
        
        while (strlen($Id) > $aCount)
        {
            $Reduced = RemoveLast($Replace, $Id);            
            $Id = ($Reduced == $Id) 
                ? substr($Reduced, 0, $aCount)
                : $Reduced;
        }
        
        return $Id;
    }
    
    // ------------------------------------------------------------------------
    
    function MakeUnqiue($aId, $aFullName, $aNames)
    {
        if (!in_array($aId, $aNames))
            return $aId;
            
        $UniqueId = $aId;
        $CharIdx = intval(strlen($UniqueId) / 2);
        $CandidateIdx = strlen($aFullName)-1;
        
        $UniqueId[strlen($UniqueId)-1] = $aFullName[$CandidateIdx];
        
        while ((in_array($UniqueId, $aNames)) && ($CandidateIdx > 0))
        {
            $UniqueId[$CharIdx] = $aFullName[$CandidateIdx];
            --$CandidateIdx;
        }
            
        return $UniqueId;
    }
    
    // ------------------------------------------------------------------------
    
    function UpdateGameConfig110($aGameConfig100, &$aClassNameToId, &$aRoleIdxToId, &$aGame )
    {
        $StyleMappings = Array(
            "images/roles/slot_role1.png" => "role_melee",
            "images/roles/slot_role2.png" => "role_heal",
            "images/roles/slot_role3.png" => "role_support",
            "images/roles/slot_role4.png" => "role_tank",
        );
        
        include_once($aGameConfig100);
        $NewGameConfig = fopen(dirname(__FILE__)."/../../themes/games/legacy.xml", "w");
        
        if ($NewGameConfig === false)
            return false;
        
        $RoleNameToId   = Array();
        $aRoleIdxToId   = Array();
        $aClassNameToId = Array();
        $aGame = "rp10";
        
        // Header
        
        fwrite($NewGameConfig, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n");
        fwrite($NewGameConfig, "<game>\n");
        fwrite($NewGameConfig, "\t<id>rp10</id>\n");
        fwrite($NewGameConfig, "\t<name>Raidplaner 1.0.x</name>\n");
        fwrite($NewGameConfig, "\t<family>wow</family>\n");
        fwrite($NewGameConfig, "\t<classmode>single</classmode>\n");
        
        // Roles
        
        fwrite($NewGameConfig, "\n\t<roles>\n");
        
        $RoleIdx = 0;
        foreach ($gRoles as $Name => $Loca)
        {
            $RoleId = BuildXCC($Loca, 3);
            $RoleId = MakeUnqiue($RoleId, $Loca, $aRoleIdxToId);
               
            $Style = (isset($StyleMappings[$gRoleImages[$RoleIdx]]))
                ? $StyleMappings[$gRoleImages[$RoleIdx]]
                : "role_support";
                
            fwrite($NewGameConfig, "\t\t<role id=\"".$RoleId."\" loca=\"".$Loca."\" style=\"".$Style."\"/>\n");
            
            array_push($aRoleIdxToId, $RoleId);
            $RoleNameToId[$Name] = $RoleId;
            
            ++$RoleIdx;
        }
        
        fwrite($NewGameConfig, "\t</roles>\n");
        
        // Classes
        
        fwrite($NewGameConfig, "\n\t<classes>\n");
        
        $RoleIdx = 0;
        foreach ($gClasses as $Name => $ClassDesc)
        {
            if ($Name == "empty") continue;
                
            $ClassId = BuildXCC($Name, 3);
            $ClassId = MakeUnqiue($ClassId, $Name, array_values($aClassNameToId));
                        
            $aClassNameToId[$Name] = $ClassId;
                
            fwrite($NewGameConfig, "\t\t<class id=\"".$ClassId."\" loca=\"".$ClassDesc[0]."\" style=\"".$Name."\">\n");
            
            foreach($ClassDesc[2] as $RoleName)
            {
                if ($RoleName == $ClassDesc[1])
                    fwrite($NewGameConfig, "\t\t\t<role id=\"".$RoleNameToId[$RoleName]."\" default=\"true\"/>\n");
                else
                    fwrite($NewGameConfig, "\t\t\t<role id=\"".$RoleNameToId[$RoleName]."\"/>\n");
            }
            
            fwrite($NewGameConfig, "\t\t</class>\n");
        }
        
        fwrite($NewGameConfig, "\t</classes>\n");
        
        // Raidview
        
        fwrite($NewGameConfig, "\n\t<raidview>\n");
        
        $RoleIdx = 0;
        foreach($gRoleColumnCount as $Count)
        {
            fwrite($NewGameConfig, "\t\t<slots role=\"".$aRoleIdxToId[$RoleIdx]."\" order=\"".($RoleIdx+1)."\" columns=\"".$Count."\"/>\n");
            ++$RoleIdx;
        }
        
        fwrite($NewGameConfig, "\t</raidview>\n");
        
        // Groups
        
        fwrite($NewGameConfig, "\n\t<groups>\n");
        
        foreach($gGroupSizes as $Size => $RoleCount)
        {
            fwrite($NewGameConfig, "\t\t<group count=\"".$Size."\">\n");
            
            $RoleIdx = 0;
            foreach($RoleCount as $Count)
            {
                fwrite($NewGameConfig, "\t\t\t<role id=\"".$aRoleIdxToId[$RoleIdx]."\" count=\"".$Count."\"/>\n");
                ++$RoleIdx;
            }
            
            fwrite($NewGameConfig, "\t\t</group>\n");
        }
        
        fwrite($NewGameConfig, "\t</groups>\n");
        
        // Create only the default locale
        
        reset($gClasses);
        fwrite($NewGameConfig, "\n\t<locale name=\"en\">\n");
        
        foreach ($gClasses as $Name => $ClassDesc)
        {
            fwrite($NewGameConfig, "\t\t<text key=\"".$ClassDesc[0]."\">".$ClassDesc[0]."</text>\n");
        }
        
        fwrite($NewGameConfig, "\t</locale>\n");
        
        // Clean up
        
        fwrite($NewGameConfig, "</game>\n");
        
        unset($gRoles);
        unset($gRoleImages);
        unset($gRoleColumnCount);
        unset($gClases);
        unset($gGroupSizes);
        
        return true;
    }
?>