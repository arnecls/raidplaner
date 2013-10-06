<?php

    function msgRaidList( $aRequest )
    {
        $Out = Out::getInstance();
            
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
                $RaidList = Array();

                while ( $Data = $ListRaidSt->fetch( PDO::FETCH_ASSOC ) )
                {
                    $StartDate = getdate($Data["StartUTC"]);
                    $EndDate   = getdate($Data["EndUTC"]);

                    $Raid = Array(
                        "id"        => $Data["RaidId"],
                        "location"  => $Data["Name"],
                        "stage"     => $Data["Stage"],
                        "image"     => $Data["Image"],
                        "size"      => $Data["Size"],
                        "startDate" => $StartDate["year"]."-".leadingZero10($StartDate["mon"])."-".leadingZero10($StartDate["mday"]),
                        "start"     => leadingZero10($StartDate["hours"]).":".leadingZero10($StartDate["minutes"]),
                        "endDate"   => $EndDate["year"]."-".leadingZero10($EndDate["mon"])."-".leadingZero10($EndDate["mday"]),
                        "end"       => leadingZero10($EndDate["hours"]).":".leadingZero10($EndDate["minutes"])
                    );
                    
                    array_push($RaidList, $Raid);                    
                }
                
                $Out->pushValue("history", $RaidList);
            }

            $ListRaidSt->closeCursor();
        }
        else
        {
            $Out->pushError(L("AccessDenied"));
        }
    }
?>