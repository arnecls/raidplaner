<?php

    function msgQuerySettings( $aRequest )
    {
        if ( validAdmin() )
        {
            global $gGame;
            loadGameSettings();
            
            $Out = Out::getInstance();
            $Connector = Connector::getInstance();
    
            // Pass through parameter
    
            $Out->pushValue("show", $aRequest["showPanel"]);
            $Out->pushValue("syncActive", !defined("ALLOW_GROUP_SYNC") || ALLOW_GROUP_SYNC);
    
            // Load users
    
            $UserQuery = $Connector->prepare("SELECT * FROM `".RP_TABLE_PREFIX."User` ORDER BY Login, `Group`");
    
            $Users = Array();
            $UserQuery->loop( function($Data) use (&$Users)
            {
                $UserData = Array(
                    "id"            => $Data["UserId"],
                    "login"         => xmlentities( $Data["Login"], ENT_COMPAT, "UTF-8" ),
                    "bindingActive" => $Data["BindingActive"],
                    "binding"       => $Data["ExternalBinding"],
                    "group"         => $Data["Group"]
                );
    
                array_push($Users, $UserData);
            });
    
            $Out->pushValue("user", $Users);
    
            // Load settings
    
            $SettingQuery = $Connector->prepare("Select * FROM `".RP_TABLE_PREFIX."Setting` ORDER BY Name");
    
            $Settings = Array();
            $SettingQuery->loop(function($Data) use (&$Settings)
            {
                $SettingData = Array(
                    "name"      => $Data["Name"],
                    "intValue"  => $Data["IntValue"],
                    "textValue" => $Data["TextValue"]
                );
    
                array_push($Settings, $SettingData);
            });
    
            $Out->pushValue("setting", $Settings);
            
            // Load games
            
            $GameFiles = scandir( "../themes/games" );
            $Games = Array();
            
            foreach ( $GameFiles as $GameFileName )
            {
                try
                {
                    if (strpos($GameFileName,".xml") > 0)
                    {
                        $Game = @new SimpleXMLElement( file_get_contents("../themes/games/".$GameFileName) );
                        $SimpleGameFileName = substr($GameFileName, 0, strrpos($GameFileName, "."));
                        
                        if ($Game->name != "")
                            $GameName = strval($Game->name);
                        else
                            $GameName = str_replace("_", " ", $SimpleGameFileName);
                        
                        $Groups = Array();
                        foreach($Game->groups->group as $Group)
                        {
                            array_push($Groups, intval($Group["count"]));
                        }
    
                        array_push($Games, Array(
                            "name"   => $GameName,
                            "family" => strval($Game->family),
                            "file"   => $SimpleGameFileName,
                            "groups" => $Groups
                        ));
                    }
                }
                catch (Exception $e)
                {
                    $Out->pushError("Error parsing gameconfig ".$GameFileName.": ".$e->getMessage());
                }
            }
    
            $Out->pushValue("game", $Games);
    
    
            // Load themes
    
            $ThemeFiles = scandir( "../themes/themes" );
            $Themes = Array();
    
            foreach ( $ThemeFiles as $ThemeFileName )
            {
                try
                {
                    if (strpos($ThemeFileName,".xml") > 0)
                    {
                        $Theme = @new SimpleXMLElement( file_get_contents("../themes/themes/".$ThemeFileName) );
                        $SimpleThemeFileName = substr($ThemeFileName, 0, strrpos($ThemeFileName, "."));
                        
                        $Family = (isset($Theme->family)) 
                            ? explode(",",strtolower($Theme->family))
                            : "wow";
                        
                        if ($Theme->name != "")
                            $ThemeName = strval($Theme->name);
                        else
                            $ThemeName = str_replace("_", " ", $SimpleThemeFileName);
    
                        array_push($Themes, Array(
                            "name"   => $ThemeName,
                            "family" => $Family,
                            "file"   => $SimpleThemeFileName
                        ));
                    }
                }
                catch (Exception $e)
                {
                    $Out->pushError("Error parsing themefile ".$ThemeFileName.": ".$e->getMessage());
                }
            }
    
            $Out->pushValue("theme", $Themes);
    
            // Query attendance
    
            $Attendance = $Connector->prepare( "SELECT `".RP_TABLE_PREFIX."Character`.Name, `".RP_TABLE_PREFIX."Attendance`.Status, ".
                "`".RP_TABLE_PREFIX."User`.UserId, UNIX_TIMESTAMP(`".RP_TABLE_PREFIX."User`.Created) AS CreatedUTC, ".
                "COUNT(*) AS Count ".
                "FROM `".RP_TABLE_PREFIX."User` LEFT JOIN `".RP_TABLE_PREFIX."Attendance` USING(UserId) ".
                "LEFT JOIN `".RP_TABLE_PREFIX."Raid` USING(RaidId) LEFT JOIN `".RP_TABLE_PREFIX."Character` USING(UserId) ".
                "WHERE `".RP_TABLE_PREFIX."Character`.Mainchar = 'true' ".
                "AND `".RP_TABLE_PREFIX."Raid`.Start > `".RP_TABLE_PREFIX."User`.Created ".
                "AND `".RP_TABLE_PREFIX."Raid`.Start < FROM_UNIXTIME(:Now) AND Game = :Game ".
                "GROUP BY UserId, `Status` ORDER BY Name" );
    
            $Attendance->bindValue( ":Now", time(), PDO::PARAM_INT );
            $Attendance->bindValue( ":Game", $gGame["GameId"], PDO::PARAM_STR );
    
            $UserId = 0;
            $NumRaidsRemain = 0;
            $MainCharName = "";
            $StateCounts = array( "undecided" => 0, "available" => 0, "unavailable" => 0, "ok" => 0 );
            $Attendances = Array();
    
            $Attendance->loop( function($Data) use (&$gGame, &$Connector, &$UserId, &$NumRaidsRemain, &$MainCharName, &$StateCounts, &$Attendances)
            {
                if ( $UserId != $Data["UserId"] )
                {
                    if ( $UserId > 0 )
                    {
                        $AttendanceData = Array(
                            "id"          => $UserId,
                            "name"        => $MainCharName,
                            "ok"          => $StateCounts["ok"],
                            "available"   => $StateCounts["available"],
                            "unavailable" => $StateCounts["unavailable"],
                            "undecided"   => $StateCounts["undecided"] + $NumRaidsRemain
                        );
    
                        array_push($Attendances, $AttendanceData);
                    }
    
                    // Clear cache
    
                    $StateCounts["ok"] = 0;
                    $StateCounts["available"] = 0;
                    $StateCounts["unavailable"] = 0;
                    $StateCounts["undecided"] = 0;
                    $NumRaidsRemain = 0;
    
                    $UserId = $Data["UserId"];
                    $MainCharName = $Data["Name"];
    
                    // Fetch number of attendable raids
    
                    $Raids = $Connector->prepare( "SELECT COUNT(*) AS `NumberOfRaids` FROM `".RP_TABLE_PREFIX."Raid` ".
                        "LEFT JOIN `".RP_TABLE_PREFIX."Location` USING(LocationId) ".
                        "WHERE Start > FROM_UNIXTIME(:Created) AND Start < FROM_UNIXTIME(:Now) AND Game = :Game" );
    
                    $Raids->bindValue( ":Now", time(), PDO::PARAM_INT );
                    $Raids->bindValue( ":Created", $Data["CreatedUTC"], PDO::PARAM_INT );
                    $Raids->bindValue( ":Game", $gGame["GameId"], PDO::PARAM_STR );
    
                    $RaidCountData = $Raids->fetchFirst();
                    $NumRaidsRemain = ($RaidCountData == null) ? 0 : $RaidCountData["NumberOfRaids"];
                }
    
                $StateCounts[$Data["Status"]] += $Data["Count"];
                $NumRaidsRemain -= $Data["Count"];
            });
    
            // Push last user
    
            if ($UserId != 0)
            {
                $AttendanceData = Array(
                    "id"          => $UserId,
                    "name"        => $MainCharName,
                    "ok"          => $StateCounts["ok"],
                    "available"   => $StateCounts["available"],
                    "unavailable" => $StateCounts["unavailable"],
                    "undecided"   => $StateCounts["undecided"] + $NumRaidsRemain,
                );
    
                array_push($Attendances, $AttendanceData);
            }
    
            $Out->pushValue("attendance", $Attendances);
    
            // Locations
    
            msgQueryLocations( $aRequest );
        }
        else
        {
            $Out = Out::getInstance();
            $Out->pushError(L("AccessDenied"));
        }
    }

?>