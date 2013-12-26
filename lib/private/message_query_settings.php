<?php

function msgQuerySettings( $aRequest )
{
    $Out = Out::getInstance();

    if ( validAdmin() )
    {
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

        // Load themes

        $ThemeFiles = scandir( "../images/themes" );
        $Themes = Array();

        foreach ( $ThemeFiles as $ThemeFileName )
        {
            try
            {
                if (strpos($ThemeFileName,".") > 0)
                {
                    $Theme = @new SimpleXMLElement( file_get_contents("../images/themes/".$ThemeFileName) );
                    $SimpleThemeFileName = substr($ThemeFileName, 0, strrpos($ThemeFileName, "."));

                    if ($Theme->name != "")
                        $ThemeName = $Theme->name;
                    else
                        $ThemeName = str_replace("_", " ", $SimpleThemeFileName);

                    array_push($Themes, Array(
                        "name" => $ThemeName,
                        "file" => $SimpleThemeFileName
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
                                           "AND `".RP_TABLE_PREFIX."Raid`.Start < FROM_UNIXTIME(:Now) ".
                                           "GROUP BY UserId, `Status` ORDER BY Name" );

        $Attendance->bindValue( ":Now", time(), PDO::PARAM_INT );

        $UserId = 0;
        $NumRaidsRemain = 0;
        $MainCharName = "";
        $StateCounts = array( "undecided" => 0, "available" => 0, "unavailable" => 0, "ok" => 0 );
        $Attendances = Array();

        $Attendance->loop( function($Data) use (&$Connector, &$UserId, &$NumRaidsRemain, &$MainCharName, &$StateCounts, &$Attendances)
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
                                              "WHERE Start > FROM_UNIXTIME(:Created) AND Start < FROM_UNIXTIME(:Now)" );

                $Raids->bindValue( ":Now", time(), PDO::PARAM_INT );
                $Raids->bindValue( ":Created", $Data["CreatedUTC"], PDO::PARAM_INT );

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
        $Out->pushError(L("AccessDenied"));
    }
}

?>