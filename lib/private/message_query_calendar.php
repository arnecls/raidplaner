<?php

function prepareCalRequest( $Month, $Year )
{
    $calRequest["Month"] = $Month;
    $calRequest["Year"]  = $Year;

    return $calRequest;
}

// -----------------------------------------------------------------------------

function getCalStartDay()
{
    $Connector = Connector::GetInstance();
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

function msgQueryCalendar( $Request )
{
    if (ValidUser())
    {
        $Connector = Connector::GetInstance();

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
        
        $startDay = getCalStartDay();
        $startUTC = mktime(0, 0, 0, $Request["Month"], 1, $Request["Year"]);
        $startDate = getdate($startUTC);
        
        if ( $startDate["wday"] != $startDay )
        {
            $dayArray  = Array("sunday", "monday", "tuesday", "wednesday", "thursday", "friday", "saturday");
            $startUTC  = strtotime("previous ".$dayArray[$startDay], $startUTC);
            $startDate = getdate($startUTC);
        }
        
        $endUTC = strtotime("+6 weeks", $startUTC);
        
        // Query and return
        
        $ListRaidSt->bindValue(":Start", $startUTC, PDO::PARAM_INT);
        $ListRaidSt->bindValue(":End",   $endUTC,   PDO::PARAM_INT);

        if (!$ListRaidSt->execute())
        {
            postErrorMessage( $ListRaidSt );
        }
        else
        {
            $_SESSION["Calendar"]["month"] = intval($Request["Month"]);
            $_SESSION["Calendar"]["year"]  = intval($Request["Year"]);

            echo "<startDay>".$startDate["mday"]."</startDay>";
            echo "<startMonth>".$startDate["mon"]."</startMonth>";
            echo "<startYear>".$startDate["year"]."</startYear>";
            echo "<startOfWeek>".$startDay."</startOfWeek>";
            echo "<displayMonth>".$Request["Month"]."</displayMonth>";
            echo "<displayYear>".$Request["Year"]."</displayYear>";
            
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
    global $s_Roles;
    echo "<raids>";

    $RaidData = Array();
    $RaidInfo = Array();

    while ($Data = $QueryResult->fetch( PDO::FETCH_ASSOC ))
    {
        array_push($RaidData, $Data);

        // Create used slot counts

        if ( !isset($RaidInfo[$Data["RaidId"]]) )
        {
            for ( $i=0; $i < sizeof($s_Roles); ++$i )
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

            $IsCorrectUser = $Data["UserId"] == UserProxy::GetInstance()->UserId;

            if ( ($IsCorrectUser) ||
                 ($Data["UserId"] == NULL) ||
                 ($DataIdx+1 == $RaidDataCount) ||
                 ($RaidData[$DataIdx+1]["RaidId"] != $Data["RaidId"]) )
            {
                $status = "notset";
                $attendanceIndex = 0;
                $role = "";
                $comment = "";

                if ( $IsCorrectUser )
                {
                    $status = $Data["Status"];
                    $attendanceIndex = ($status == "unavailable") ? -1 : intval($Data["CharacterId"]);
                    $role = $Data["Role"];
                    $comment = $Data["Comment"];
                }

                $StartDate = getdate($Data["StartUTC"]);
                $EndDate   = getdate($Data["EndUTC"]);

                echo "<raid>";

                echo "<id>".$Data["RaidId"]."</id>";
                echo "<location>".$Data["Name"]."</location>";
                echo "<stage>".$Data["Stage"]."</stage>";
                echo "<size>".$Data["Size"]."</size>";
                echo "<startDate>".$StartDate["year"]."-".LeadingZero10($StartDate["mon"])."-".LeadingZero10($StartDate["mday"])."</startDate>";
                echo "<start>".LeadingZero10($StartDate["hours"]).":".LeadingZero10($StartDate["minutes"])."</start>";
                echo "<end>".LeadingZero10($EndDate["hours"]).":".LeadingZero10($EndDate["minutes"])."</end>";
                echo "<image>".$Data["Image"]."</image>";
                echo "<description>".$Data["Description"]."</description>";
                echo "<status>".$status."</status>";
                echo "<attendanceIndex>".$attendanceIndex."</attendanceIndex>";
                echo "<comment>".$comment."</comment>";
                echo "<role>".$role."</role>";

                for ( $i=0; $i < sizeof($s_Roles); ++$i )
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