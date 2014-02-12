<?php
    require_once dirname(__FILE__)."/connector.class.php";
    
    // -------------------------------------------------------------------------
    
    $gApiHelp["statistic"] = Array(
        "description" => "Query value. Get user statistics.",
        "parameters" => Array(
            "start"     => "Only count raids starting after this UTC timestamp. Default: 0.",
            "end"       => "Only count raids starting before this UTC timestamp. Default: now.",
            "raids"     => "Comma separated list of raid ids to count. Empty counts all raids. Default: empty.",
            "users"     => "Comma separated list of user names to include. Empty returns all users. Default: empty.",
        )
    );
    
    // -------------------------------------------------------------------------
    
    function api_args_statistic($aRequest)
    {
        return Array(
            "start" => getParamFrom($aRequest, "start", 0),
            "end"   => getParamFrom($aRequest, "end", PHP_INT_MAX),
            "raids" => getParamFrom($aRequest, "raids", ""),
            "users" => getParamFrom($aRequest, "users", ""),
        );
    }
    
    // -------------------------------------------------------------------------
    
    function api_query_statistic($aParameter)
    {
        $aStart = getParamFrom($aParameter, "start", 0);
        $aEnd   = getParamFrom($aParameter, "end",   time());
        $aRaids = getParamFrom($aParameter, "raids", "");
        $aUsers = getParamFrom($aParameter, "users", "");
        
        $Conditions = Array(
            "`".RP_TABLE_PREFIX."Character`.Mainchar = 'true'",
            "`".RP_TABLE_PREFIX."Raid`.Start > `".RP_TABLE_PREFIX."User`.Created",
            "`".RP_TABLE_PREFIX."Raid`.Start > FROM_UNIXTIME(?)",
            "`".RP_TABLE_PREFIX."Raid`.Start < FROM_UNIXTIME(?)",
        );
        
        $Parameters = Array(
            $aStart,
            $aEnd,
        );
        
        // Filter users
        
        if ($aUsers != "")
        {
            $Users = explode(",", $aUsers);
            foreach($Users as &$UserId)
                $UserId = intval($UserId);
                
            if (count($Users) == 1)
            {         
                array_push($Conditions, "UserId=?");
                array_push($Parameters, $Users[0]);
            }
            else
            {
                array_push($Conditions, "UserId IN (".implode(",",$Users).")");
            }
        }
        
        // Filter games
        
        if ($aRaids != "")
        {
            $Raids = explode(",", $aRaids);
            foreach($Raids as &$RaidId)
                $RaidId = intval($RaidId);
                
            if (count($Raids) == 1)
            {         
                array_push($Conditions, "RaidId=?");
                array_push($Parameters, $Raids[0]);
            }
            else
            {
                array_push($Conditions, "RaidId IN (".implode(",",$Raids).")");
            }
        }
        
        // Build where clause
        
        $WhereString = "";        
        if (count($Conditions) > 0)
        {
            foreach($Conditions as &$Part)
            {
                if (is_array($Part))
                    $Part = "(".implode(" OR ", $Part).")";
            }
            
            $WhereString = " WHERE ".implode(" AND ",$Conditions);
        }
        
        // Query attendances
    
        $Connector = Connector::getInstance();
        $AttendanceQuery = $Connector->prepare( "SELECT ".
            "`".RP_TABLE_PREFIX."Character`.Name, ".
            "`".RP_TABLE_PREFIX."Attendance`.Status, ".
            "`".RP_TABLE_PREFIX."Attendance`.Role, ".
            "`".RP_TABLE_PREFIX."User`.UserId, ".
            "UNIX_TIMESTAMP(`".RP_TABLE_PREFIX."User`.Created) AS CreatedUTC, ".
            "COUNT(*) AS Count ".
            "FROM `".RP_TABLE_PREFIX."User` ".
            "LEFT JOIN `".RP_TABLE_PREFIX."Attendance` USING(UserId) ".
            "LEFT JOIN `".RP_TABLE_PREFIX."Raid` USING(RaidId) ".
            "LEFT JOIN `".RP_TABLE_PREFIX."Character` USING(UserId) ".
            $WhereString.
            "GROUP BY UserId, `Status` ORDER BY Name" );

        foreach($Parameters as $Index => $Value)
        {
            //Out::getInstance()->pushValue("query", $Value);
            if (is_null($Value))
                $AttendanceQuery->bindValue($Index+1, intval($Value), PDO::PARAM_INT);
            else
                $AttendanceQuery->bindValue($Index+1, strval($Value), PDO::PARAM_STR);
        }

        $UserId = 0;
        $NumRaidsRemain = 0;
        $MainCharName = "";
        $StateCounts = Array( "undecided" => 0, "available" => 0, "unavailable" => 0, "ok" => 0 );
        $Attendances = Array();
        $Roles = Array();

        $AttendanceQuery->loop( function($Data) use ($Connector, &$UserId, &$NumRaidsRemain, &$MainCharName, &$StateCounts, &$Attendances, &$Roles, $aEnd)
        {
            if ( $UserId != $Data["UserId"] )
            {
                if ( $UserId > 0 )
                {
                    $AttendanceData = Array(
                        "Id"        => $UserId,
                        "MainChar"  => $MainCharName,
                        "SetToRaid" => $StateCounts["ok"],
                        "Available" => $StateCounts["available"],
                        "Absent"    => $StateCounts["unavailable"],
                        "Undecided" => $StateCounts["undecided"] + $NumRaidsRemain,
                        "Roles"     => $Roles,
                    );

                    array_push($Attendances, $AttendanceData);
                }

                // Clear cache

                $StateCounts["ok"] = 0;
                $StateCounts["available"] = 0;
                $StateCounts["unavailable"] = 0;
                $StateCounts["undecided"] = 0;
                $NumRaidsRemain = 0;
                $Roles = Array();

                $UserId = $Data["UserId"];
                $MainCharName = $Data["Name"];

                // Fetch number of attendable raids

                $Raids = $Connector->prepare( "SELECT COUNT(*) AS `NumberOfRaids` FROM `".RP_TABLE_PREFIX."Raid` ".
                    "LEFT JOIN `".RP_TABLE_PREFIX."Location` USING(LocationId) ".
                    "WHERE Start > FROM_UNIXTIME(:Created) AND Start < FROM_UNIXTIME(:End)" );

                $Raids->bindValue( ":End",     $aEnd,                       PDO::PARAM_INT );
                $Raids->bindValue( ":Created", intval($Data["CreatedUTC"]), PDO::PARAM_INT );

                $RaidCountData = $Raids->fetchFirst();
                $NumRaidsRemain = ($RaidCountData == null) ? 0 : $RaidCountData["NumberOfRaids"];
            }

            $StateCounts[$Data["Status"]] += $Data["Count"];
            
            if (!isset($Roles[$Data["Role"]]))
                $Roles[$Data["Role"]] = 1;
            else
                ++$Roles[$Data["Role"]];
            
            $NumRaidsRemain -= $Data["Count"];
        });

        // Push last user

        if ($UserId != 0)
        {
            $AttendanceData = Array(
                "Id"        => $UserId,
                "MainChar"  => $MainCharName,
                "SetToRaid" => $StateCounts["ok"],
                "Available" => $StateCounts["available"],
                "Absent"    => $StateCounts["unavailable"],
                "Undecided" => $StateCounts["undecided"] + $NumRaidsRemain,
                "Roles"     => $Roles,
            );

            array_push($Attendances, $AttendanceData);
        }
        
        return $Attendances;
    }
?>