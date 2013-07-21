<?php

    function msgRaidList( $aRequest )
    {
        if (validUser())
        {
            $Connector = Connector::getInstance();

            // Get next 6 raids

            $NextRaidSt = $Connector->prepare("Select ".RP_TABLE_PREFIX."Raid.*, ".RP_TABLE_PREFIX."Location.*, ".
                                              RP_TABLE_PREFIX."Attendance.CharacterId, ".RP_TABLE_PREFIX."Attendance.UserId, ".
                                              RP_TABLE_PREFIX."Attendance.Status, ".RP_TABLE_PREFIX."Attendance.Role, ".RP_TABLE_PREFIX."Attendance.Comment, ".
                                              "UNIX_TIMESTAMP(".RP_TABLE_PREFIX."Raid.Start) AS StartUTC, ".
                                              "UNIX_TIMESTAMP(".RP_TABLE_PREFIX."Raid.End) AS EndUTC ".
                                              "FROM `".RP_TABLE_PREFIX."Raid` ".
                                              "LEFT JOIN `".RP_TABLE_PREFIX."Location` USING(LocationId) ".
                                              "LEFT JOIN `".RP_TABLE_PREFIX."Attendance` USING(RaidId) ".
                                              "LEFT JOIN `".RP_TABLE_PREFIX."Character` USING (CharacterId) ".
                                              "WHERE ".RP_TABLE_PREFIX."Raid.Start >= FROM_UNIXTIME(:Start) ".
                                              "ORDER BY ".RP_TABLE_PREFIX."Raid.Start, ".RP_TABLE_PREFIX."Raid.RaidId" );

            $NextRaidSt->bindValue( ":Start", mktime(0,0,0), PDO::PARAM_INT );

            if ( !$NextRaidSt->execute() )
            {
                postErrorMessage( $NextRaidSt );
            }
            else
            {
                parseRaidQuery( $aRequest, $NextRaidSt, 6 );
            }

            $NextRaidSt->closeCursor();

            // -------------------------

            $ListRaidSt = $Connector->prepare("Select ".RP_TABLE_PREFIX."Raid.*, ".RP_TABLE_PREFIX."Location.*, ".
                                              "UNIX_TIMESTAMP(".RP_TABLE_PREFIX."Raid.Start) AS StartUTC, ".
                                              "UNIX_TIMESTAMP(".RP_TABLE_PREFIX."Raid.End) AS EndUTC ".
                                              "FROM `".RP_TABLE_PREFIX."Raid` ".
                                              "LEFT JOIN `".RP_TABLE_PREFIX."Location` USING(LocationId) ".
                                              "WHERE ".RP_TABLE_PREFIX."Raid.Start < FROM_UNIXTIME(:Start)".
                                              "ORDER BY Start DESC LIMIT ".intval($aRequest["offset"]).", ".intval($aRequest["count"]) );

            $ListRaidSt->bindValue( ":Start", mktime(0,0,0), PDO::PARAM_INT );

            if (!$ListRaidSt->execute())
            {
                postErrorMessage( $ListRaidSt );
            }
            else
            {
                echo "<raidList>";

                while ( $Data = $ListRaidSt->fetch( PDO::FETCH_ASSOC ) )
                {
                    $StartDate = getdate($Data["StartUTC"]);
                    $EndDate   = getdate($Data["EndUTC"]);

                    echo "<raid>";
                    echo "<id>".$Data["RaidId"]."</id>";
                    echo "<location>".$Data["Name"]."</location>";
                    echo "<stage>".$Data["Stage"]."</stage>";
                    echo "<image>".$Data["Image"]."</image>";
                    echo "<size>".$Data["Size"]."</size>";
                    echo "<startDate>".$StartDate["year"]."-".leadingZero10($StartDate["mon"])."-".leadingZero10($StartDate["mday"])."</startDate>";
                    echo "<start>".leadingZero10($StartDate["hours"]).":".leadingZero10($StartDate["minutes"])."</start>";
                    echo "<endDate>".$EndDate["year"]."-".leadingZero10($EndDate["mon"])."-".leadingZero10($EndDate["mday"])."</endDate>";
                    echo "<end>".leadingZero10($EndDate["hours"]).":".leadingZero10($EndDate["minutes"])."</end>";
                    echo "</raid>";
                }

                echo "</raidList>";
            }

            $ListRaidSt->closeCursor();
        }
        else
        {
            echo "<error>".L("AccessDenied")."</error>";
        }
    }
?>