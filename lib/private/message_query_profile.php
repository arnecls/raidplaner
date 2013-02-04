<?php

function msgQueryProfile( $Request )
{
    global $s_Roles;

    if ( ValidUser() )
    {
        $userId = UserProxy::GetInstance()->UserId;

        if ( ValidAdmin() && isset( $Request["id"] ) )
        {
            $userId = intval( $Request["id"] );
        }

        $Connector = Connector::GetInstance();

        // Admintool relevant data

        $Users = $Connector->prepare( "SELECT Login, Created, ExternalBinding FROM `".RP_TABLE_PREFIX."User` WHERE UserId = :UserId LIMIT 1" );
        $Users->bindValue( ":UserId", $userId, PDO::PARAM_INT );

        if ( !$Users->execute() )
        {
            postErrorMessage( $User );
        }
        else
        {
            $Data = $Users->fetch( PDO::FETCH_ASSOC );

            echo "<userid>".$userId."</userid>";
            echo "<name>".$Data["Login"]."</name>";
            echo "<binding>".$Data["ExternalBinding"]."</binding>";
            
            $Created = $Data["Created"];
        }

        $Users->closeCursor();

        // Load characters
        
        if ( $userId == UserProxy::GetInstance()->UserId )
        {
            foreach ( UserProxy::GetInstance()->Characters as $Data )
            {
                echo "<character>";
                echo "<id>".$Data->CharacterId."</id>";
                echo "<name>".$Data->Name."</name>";
                echo "<class>".$Data->ClassName."</class>";
                echo "<mainchar>".(($Data->IsMainChar) ? "true" : "false")."</mainchar>";
                echo "<role1>".$Data->Role1."</role1>";
                echo "<role2>".$Data->Role2."</role2>";
                echo "</character>";
            }
        }
        else
        {
            $CharacterSt = $Connector->prepare( "SELECT * FROM `".RP_TABLE_PREFIX."Character` ".
                                                "WHERE UserId = :UserId ".
                                                "ORDER BY Mainchar, Name" );

            $CharacterSt->bindValue(":UserId", $userId, PDO::PARAM_INT);
            $CharacterSt->execute();
            
            while ( $row = $CharacterSt->fetch( PDO::FETCH_ASSOC ) )
            {
                echo "<character>";
                echo "<id>".$row["CharacterId"]."</id>";
                echo "<name>".$row["Name"]."</name>";
                echo "<class>".$row["Class"]."</class>";
                echo "<mainchar>".(($row["IsMainchar"]) ? "true" : "false")."</mainchar>";
                echo "<role1>".$row["Role1"]."</role1>";
                echo "<role2>".$row["Role2"]."</role2>";
                echo "</character>";
            }
            
            $CharacterSt->closeCursor();
        }
        
        // Total raid count

        $NumRaids = 0;
        $Raids = $Connector->prepare( "SELECT COUNT(*) AS `NumberOfRaids` FROM `".RP_TABLE_PREFIX."Raid` WHERE Start > :Registered AND Start < FROM_UNIXTIME(:Now)" );
        $Raids->bindValue( ":Now", time(), PDO::PARAM_INT );
        $Raids->bindValue( ":Registered", $Created, PDO::PARAM_STR );

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
                                            "WHERE UserId = :UserId AND Start > :Registered AND Start < FROM_UNIXTIME(:Now) ".
                                            "GROUP BY `Status`, `Role` ORDER BY Status" );

        $Attendance->bindValue( ":UserId", $userId, PDO::PARAM_INT );
        $Attendance->bindValue( ":Registered", $Created, PDO::PARAM_STR );
        $Attendance->bindValue( ":Now", time(), PDO::PARAM_INT );

        if ( !$Attendance->execute() )
        {
            postErrorMessage( $Attendance );
        }
        else
        {
            $AttendanceData = array(
                "available"   => 0,
                "unavailable" => 0,
                "ok"          => 0 );

            // Initialize roles

            $RoleKeys = array_keys($s_Roles);

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
                    $roleIdx = intval($Data["Role"]);
                    if ( $roleIdx < sizeof($RoleKeys) )
                    {
                        $resolvedRole = $RoleKeys[ $roleIdx ];
                        $AttendanceData[ $resolvedRole ] += $Data["Count"];
                    }
                }
            }

            echo "<attendance>";
            echo "<raids>".$NumRaids."</raids>";

            while( list($Name, $Count) = each($AttendanceData) )
            {
                echo "<".$Name.">".$Count."</".$Name.">";
            }

            echo "</attendance>";
        }

        $Attendance->closeCursor();
    }
    else
    {
        echo "<error>".L("AccessDenied")."</error>";
    }
}

?>