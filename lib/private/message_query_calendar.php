<?php

function prepareCalRequest( $aMonth, $aYear )
{
    $CalRequest["Month"] = $aMonth;
    $CalRequest["Year"]  = $aYear;

    return $CalRequest;
}

// -----------------------------------------------------------------------------

function getCalStartDay()
{
    $Connector = Connector::getInstance();
    $SettingsSt = $Connector->prepare( "Select IntValue FROM ".RP_TABLE_PREFIX."Setting WHERE Name = \"StartOfWeek\" LIMIT 1" );
    
    $FirstDay = 1;
    
    if ($SettingsSt->execute())
    {
        if ($Data = $SettingsSt->fetch(PDO::FETCH_ASSOC) )
            $FirstDay = intval($Data["IntValue"]);
    }
    
    $SettingsSt->closeCursor();
    return $FirstDay;
}

// -----------------------------------------------------------------------------

function msgQueryCalendar( $aRequest )
{
    if (validUser())
    {
        $Connector = Connector::getInstance();

        $ListRaidSt = $Connector->prepare(  "Select ".RP_TABLE_PREFIX."Raid.*, ".RP_TABLE_PREFIX."Location.*, ".
                                            RP_TABLE_PREFIX."Attendance.CharacterId, ".RP_TABLE_PREFIX."Attendance.UserId, ".
                                            RP_TABLE_PREFIX."Attendance.Status, ".RP_TABLE_PREFIX."Attendance.Role, ".RP_TABLE_PREFIX."Attendance.Comment, ".
                                            "UNIX_TIMESTAMP(".RP_TABLE_PREFIX."Raid.Start) AS StartUTC, ".
                                            "UNIX_TIMESTAMP(".RP_TABLE_PREFIX."Raid.End) AS EndUTC ".
                                            "FROM `".RP_TABLE_PREFIX."Raid` ".
                                            "LEFT JOIN `".RP_TABLE_PREFIX."Location` USING(LocationId) ".
                                            "LEFT JOIN `".RP_TABLE_PREFIX."Attendance` USING (RaidId) ".
                                            "LEFT JOIN `".RP_TABLE_PREFIX."Character` USING (CharacterId) ".
                                            "WHERE ".RP_TABLE_PREFIX."Raid.Start >= FROM_UNIXTIME(:Start) AND ".RP_TABLE_PREFIX."Raid.Start <= FROM_UNIXTIME(:End) ".
                                            "ORDER BY ".RP_TABLE_PREFIX."Raid.Start, ".RP_TABLE_PREFIX."Raid.RaidId" );
        
        // Calculate the correct start end end times
        
        $StartDay = getCalStartDay();
        $StartUTC = mktime(0, 0, 0, $aRequest["Month"], 1, $aRequest["Year"]);
        $StartDate = getdate($StartUTC);
        
        if ( $StartDate["wday"] != $StartDay )
        {
            $DayArray  = Array("sunday", "monday", "tuesday", "wednesday", "thursday", "friday", "saturday");
            $StartUTC  = strtotime("previous ".$DayArray[$StartDay], $StartUTC);
            $StartDate = getdate($StartUTC);
        }
        
        $EndUTC = strtotime("+6 weeks", $StartUTC);
        
        // Query and return
        
        $ListRaidSt->bindValue(":Start", $StartUTC, PDO::PARAM_INT);
        $ListRaidSt->bindValue(":End",   $EndUTC,   PDO::PARAM_INT);

        if (!$ListRaidSt->execute())
        {
            postErrorMessage( $ListRaidSt );
        }
        else
        {
            $_SESSION["Calendar"]["month"] = intval($aRequest["Month"]);
            $_SESSION["Calendar"]["year"]  = intval($aRequest["Year"]);

            echo "<startDay>".$StartDate["mday"]."</startDay>";
            echo "<startMonth>".$StartDate["mon"]."</startMonth>";
            echo "<startYear>".$StartDate["year"]."</startYear>";
            echo "<startOfWeek>".$StartDay."</startOfWeek>";
            echo "<displayMonth>".$aRequest["Month"]."</displayMonth>";
            echo "<displayYear>".$aRequest["Year"]."</displayYear>";
            
            parseRaidQuery( $ListRaidSt, 0 );
        }

        $ListRaidSt->closeCursor();
    }
    else
    {
        echo "<error>".L("AccessDenied")."</error>";
    }
}

// -----------------------------------------------------------------------------

function parseRaidQuery( $QueryResult, $Limit )
{
    global $gRoles;
    echo "<raids>";

    $RaidData = Array();
    $RaidInfo = Array();

    while ($Data = $QueryResult->fetch( PDO::FETCH_ASSOC ))
    {
        array_push($RaidData, $Data);

        // Create used slot counts

        if ( !isset($RaidInfo[$Data["RaidId"]]) )
        {
            for ( $i=0; $i < sizeof($gRoles); ++$i )
            {
                $RaidInfo[$Data["RaidId"]]["role".$i] = 0;
            }

            $RaidInfo[$Data["RaidId"]]["bench"] = 0;
        }

        // Count used slots

        if ( ($Data["Status"] == "ok") ||
             ($Data["Status"] == "available") )
        {
            ++$RaidInfo[$Data["RaidId"]]["role".$Data["Role"]];
        }
    }

    $LastRaidId = -1;
    $RaidDataCount = sizeof($RaidData);

    $NumRaids = 0;

    for ( $DataIdx=0; $DataIdx < $RaidDataCount; ++$DataIdx )
    {
        $Data = $RaidData[$DataIdx];

        if ( $LastRaidId != $Data["RaidId"] )
        {
            // If no user assigned for this raid
            // or row belongs to this user
            // or it's the last entry
            // or the next entry is a different raid

            $IsCorrectUser = $Data["UserId"] == UserProxy::getInstance()->UserId;

            if ( ($IsCorrectUser) ||
                 ($Data["UserId"] == NULL) ||
                 ($DataIdx+1 == $RaidDataCount) ||
                 ($RaidData[$DataIdx+1]["RaidId"] != $Data["RaidId"]) )
            {
                $Status = "notset";
                $AttendanceIndex = 0;
                $Role = "";
                $Comment = "";

                if ( $IsCorrectUser )
                {
                    $Status = $Data["Status"];
                    $AttendanceIndex = ($Status == "unavailable") ? -1 : intval($Data["CharacterId"]);
                    $Role = $Data["Role"];
                    $Comment = $Data["Comment"];
                }

                $StartDate = getdate($Data["StartUTC"]);
                $EndDate   = getdate($Data["EndUTC"]);

                echo "<raid>";

                echo "<id>".$Data["RaidId"]."</id>";
                echo "<location>".$Data["Name"]."</location>";
                echo "<stage>".$Data["Stage"]."</stage>";
                echo "<size>".$Data["Size"]."</size>";
                echo "<startDate>".$StartDate["year"]."-".leadingZero10($StartDate["mon"])."-".leadingZero10($StartDate["mday"])."</startDate>";
                echo "<start>".leadingZero10($StartDate["hours"]).":".leadingZero10($StartDate["minutes"])."</start>";
                echo "<end>".leadingZero10($EndDate["hours"]).":".leadingZero10($EndDate["minutes"])."</end>";
                echo "<image>".$Data["Image"]."</image>";
                echo "<description>".$Data["Description"]."</description>";
                echo "<status>".$Status."</status>";
                echo "<attendanceIndex>".$AttendanceIndex."</attendanceIndex>";
                echo "<comment>".$Comment."</comment>";
                echo "<role>".$Role."</role>";

                for ( $i=0; $i < sizeof($gRoles); ++$i )
                {
                    echo "<role".$i."Slots>".$Data["SlotsRole".($i+1)]."</role".$i."Slots>";
                    echo "<role".$i.">".$RaidInfo[$Data["RaidId"]]["role".$i]."</role".$i.">";
                }

                echo "</raid>";

                $LastRaidId = $Data["RaidId"];
                ++$NumRaids;

                if ( ($Limit > 0) && ($NumRaids == $Limit) )
                    break;
            }
        }
    }

    echo "</raids>";
}
?>