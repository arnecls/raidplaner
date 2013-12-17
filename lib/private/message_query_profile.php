<?php

function msgQueryProfile( $aRequest )
{
    global $gRoles;
    $Out = Out::getInstance();

    if ( validUser() )
    {
        $UserId = UserProxy::getInstance()->UserId;

        if ( validAdmin() && isset( $aRequest["id"] ) )
        {
            $UserId = intval( $aRequest["id"] );
        }

        $Connector = Connector::getInstance();
        
        $Out->pushValue("show", $aRequest["showPanel"]);

        // Admintool relevant data

        $Users = $Connector->prepare( "SELECT Login, UNIX_TIMESTAMP(Created) AS CreatedUTC, ExternalBinding, BindingActive FROM `".RP_TABLE_PREFIX."User` WHERE UserId = :UserId LIMIT 1" );
        $Users->bindValue( ":UserId", $UserId, PDO::PARAM_INT );

        if ( !$Users->execute() )
        {
            postErrorMessage( $User );
        }
        else
        {
            $Data = $Users->fetch( PDO::FETCH_ASSOC );
            
            $Out->pushValue("userid", $UserId);
            $Out->pushValue("name", $Data["Login"]);
            $Out->pushValue("bindingActive", $Data["BindingActive"] == "true");
            $Out->pushValue("binding", $Data["ExternalBinding"]);
            
            $CreatedUTC = $Data["CreatedUTC"];
        }

        $Users->closeCursor();

        // Load characters
        
        $Characters = Array();
        
        if ( $UserId == UserProxy::getInstance()->UserId )
        {
            foreach ( UserProxy::getInstance()->Characters as $Data )
            {
                $Character = Array(
                    "id"        => $Data->CharacterId,
                    "name"      => $Data->Name,
                    "classname" => $Data->ClassName,
                    "mainchar"  => $Data->IsMainChar,
                    "role1"     => $Data->Role1,
                    "role2"     => $Data->Role2
                );
                
                array_push($Characters, $Character);
            }
        }
        else
        {
            $CharacterSt = $Connector->prepare( "SELECT * FROM `".RP_TABLE_PREFIX."Character` ".
                                                "WHERE UserId = :UserId ".
                                                "ORDER BY Mainchar, Name" );

            $CharacterSt->bindValue(":UserId", $UserId, PDO::PARAM_INT);
            $CharacterSt->execute();
            
            while ( $Row = $CharacterSt->fetch( PDO::FETCH_ASSOC ) )
            {
                $Character = Array(
                    "id"        => $Row["CharacterId"],
                    "name"      => $Row["Name"],
                    "classname" => $Row["Class"],
                    "mainchar"  => $Row["Mainchar"] == "true",
                    "role1"     => $Row["Role1"],
                    "role2"     => $Row["Role2"]
                );
                
                array_push($Characters, $Character);
            }
            
            $CharacterSt->closeCursor();
        }
                
        $Out->pushValue("character", $Characters);
        
        // Total raid count

        $NumRaids = 0;
        $Raids = $Connector->prepare( "SELECT COUNT(*) AS `NumberOfRaids` FROM `".RP_TABLE_PREFIX."Raid` WHERE Start > FROM_UNIXTIME(:Created) AND Start < FROM_UNIXTIME(:Now)" );
        $Raids->bindValue( ":Now", time(), PDO::PARAM_INT );
        $Raids->bindValue( ":Created", $CreatedUTC, PDO::PARAM_STR );

        if ( !$Raids->execute() )
        {
            postErrorMessage( $User );
        }
        else
        {
            $Data = $Raids->fetch( PDO::FETCH_ASSOC );
            $NumRaids = $Data["NumberOfRaids"];
        }

        $Raids->closeCursor();

        // Load attendance

        $Attendance = $Connector->prepare(  "Select `Status`, `Role`, COUNT(*) AS `Count` ".
                                            "FROM `".RP_TABLE_PREFIX."Attendance` ".
                                            "LEFT JOIN `".RP_TABLE_PREFIX."Raid` USING(RaidId) ".
                                            "WHERE UserId = :UserId AND Start > FROM_UNIXTIME(:Created) AND Start < FROM_UNIXTIME(:Now) ".
                                            "GROUP BY `Status`, `Role` ORDER BY Status" );

        $Attendance->bindValue( ":UserId", $UserId, PDO::PARAM_INT );
        $Attendance->bindValue( ":Created", $CreatedUTC, PDO::PARAM_INT );
        $Attendance->bindValue( ":Now", time(), PDO::PARAM_INT );

        if ( !$Attendance->execute() )
        {
            postErrorMessage( $Attendance );
        }
        else
        {
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

            while ( $Data = $Attendance->fetch( PDO::FETCH_ASSOC ) )
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
            }
            
            $Out->pushValue("attendance", $AttendanceData);
        }

        $Attendance->closeCursor();
    }
    else
    {
        $Out->pushError(L("AccessDenied"));
    }
}

?>