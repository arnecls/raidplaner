<?php

function msgQueryProfile( $aRequest )
{
    global $gRoles;
    global $gClassMode;
    $Out = Out::getInstance();

    if ( validUser() )
    {
        $UserId = UserProxy::getInstance()->UserId;

        if ( validAdmin() && isset($aRequest["userId"]) && ($aRequest["userId"]!=0) )
        {
            $UserId = intval( $aRequest["userId"] );
        }

        $Connector = Connector::getInstance();

        $Out->pushValue("show", $aRequest["showPanel"]);

        // Admintool relevant data

        $Users = $Connector->prepare( "SELECT Login, UNIX_TIMESTAMP(Created) AS CreatedUTC, ExternalBinding, BindingActive FROM `".RP_TABLE_PREFIX."User` WHERE UserId = :UserId LIMIT 1" );
        $Users->bindValue( ":UserId", $UserId, PDO::PARAM_INT );

        $Data = $Users->fetchFirst();

        if ($Data != null)
        {

            $Out->pushValue("userid", $UserId);
            $Out->pushValue("name", $Data["Login"]);
            $Out->pushValue("bindingActive", $Data["BindingActive"] == "true");
            $Out->pushValue("binding", $Data["ExternalBinding"]);

            $CreatedUTC = $Data["CreatedUTC"];
        }

        // Load settings

        $SettingsQuery = $Connector->prepare( "SELECT * FROM `".RP_TABLE_PREFIX."UserSetting` WHERE UserId = :UserId" );
        $SettingsQuery->bindValue(":UserId", $UserId, PDO::PARAM_INT);
        $UserSettings = array();

        $SettingsQuery->loop(function($Data) use (&$UserSettings)
        {
            $UserSettings[$Data["Name"]] = array("number" => $Data["IntValue"], "text" => $Data["TextValue"]);
        });

        $Out->pushValue("settings", $UserSettings);

        // Load characters

        $Characters = Array();

        if ( $UserId == UserProxy::getInstance()->UserId )
        {
            foreach ( UserProxy::getInstance()->Characters as $Data )
            {
                $Classes = explode(":", $Data->ClassName);
                
                $Character = Array(
                    "id"        => $Data->CharacterId,
                    "name"      => $Data->Name,
                    "classname" => $Classes,
                    "mainchar"  => $Data->IsMainChar,
                    "role1"     => $Data->Role1,
                    "role2"     => $Data->Role2
                );

                array_push($Characters, $Character);
            }
        }
        else
        {
            $CharacterQuery = $Connector->prepare( "SELECT * FROM `".RP_TABLE_PREFIX."Character` ".
                                                "WHERE UserId = :UserId ".
                                                "ORDER BY Mainchar, Name" );

            $CharacterQuery->bindValue(":UserId", $UserId, PDO::PARAM_INT);

            $CharacterQuery->loop( function($Row) use (&$Characters)
            {
                $Classes = explode(":", $Row["Class"]);
                
                $Character = Array(
                    "id"        => $Row["CharacterId"],
                    "name"      => $Row["Name"],
                    "classname" => $Classes,
                    "mainchar"  => $Row["Mainchar"] == "true",
                    "role1"     => $Row["Role1"],
                    "role2"     => $Row["Role2"]
                );

                array_push($Characters, $Character);
            });
        }

        $Out->pushValue("character", $Characters);

        // Total raid count

        $NumRaids = 0;
        $RaidsQuery = $Connector->prepare( "SELECT COUNT(*) AS `NumberOfRaids` FROM `".RP_TABLE_PREFIX."Raid` WHERE Start > FROM_UNIXTIME(:Created) AND Start < FROM_UNIXTIME(:Now)" );
        $RaidsQuery->bindValue( ":Now", time(), PDO::PARAM_INT );
        $RaidsQuery->bindValue( ":Created", $CreatedUTC, PDO::PARAM_STR );

        $Data = $RaidsQuery->fetchFirst();
        if ($Data != null)
            $NumRaids = $Data["NumberOfRaids"];

        // Load attendance

        $AttendanceQuery = $Connector->prepare(  "Select `Status`, `Role`, COUNT(*) AS `Count` ".
                                            "FROM `".RP_TABLE_PREFIX."Attendance` ".
                                            "LEFT JOIN `".RP_TABLE_PREFIX."Raid` USING(RaidId) ".
                                            "WHERE UserId = :UserId AND Start > FROM_UNIXTIME(:Created) AND Start < FROM_UNIXTIME(:Now) ".
                                            "GROUP BY `Status`, `Role` ORDER BY Status" );

        $AttendanceQuery->bindValue( ":UserId", $UserId, PDO::PARAM_INT );
        $AttendanceQuery->bindValue( ":Created", $CreatedUTC, PDO::PARAM_INT );
        $AttendanceQuery->bindValue( ":Now", time(), PDO::PARAM_INT );

        $AttendanceData = array(
            "raids"       => $NumRaids,
            "available"   => 0,
            "unavailable" => 0,
            "ok"          => 0 );

        // Initialize roles

        $RoleKeys = array_keys($gRoles);

        foreach ( $RoleKeys as $RoleKey )
        {
            $AttendanceData[$RoleKey] = 0;
        }

        // Pull data

        $AttendanceQuery->loop( function($Data) use (&$AttendanceData, $RoleKeys)
        {
            if ( $Data["Status"] != "undecided" )
                $AttendanceData[ $Data["Status"] ] += $Data["Count"];

            if ( $Data["Status"] == "ok" )
            {
                $RoleIdx = intval($Data["Role"]);
                if ( $RoleIdx < sizeof($RoleKeys) )
                {
                    $ResolvedRole = $RoleKeys[ $RoleIdx ];
                    $AttendanceData[ $ResolvedRole ] += $Data["Count"];
                }
            }
        });

        $Out->pushValue("attendance", $AttendanceData);
    }
    else
    {
        $Out->pushError(L("AccessDenied"));
    }
}

?>