<?php
    require_once dirname(__FILE__)."/connector.class.php";
    
    function api_query_raid($aParameter)
    {
        // Assemble paramters
        
        $aStart         = getParamFrom($aParameter, "start",    0);
        $aEnd           = getParamFrom($aParameter, "end",      0x7FFFFFFF);
        $aLimit         = getParamFrom($aParameter, "limit",    10);
        $aOffset        = getParamFrom($aParameter, "offset",   0);
        $aLocation      = getParamFrom($aParameter, "location", "");
        $aFetchFull     = getParamFrom($aParameter, "full",     true);
        $aFetchFree     = getParamFrom($aParameter, "free",     true);
        $aFetchOpen     = getParamFrom($aParameter, "open",     true);
        $aFetchClosed   = getParamFrom($aParameter, "closed",   false);
        $aFetchCanceled = getParamFrom($aParameter, "canceled", false);
        $aAddAttends    = getParamFrom($aParameter, "attends",  false);
        
        // Build query
        
        $Fields = Array(
            "`".RP_TABLE_PREFIX."Raid`.*",
            "UNIX_TIMESTAMP(`".RP_TABLE_PREFIX."Raid`.Start) AS StartUTC",
            "UNIX_TIMESTAMP(`".RP_TABLE_PREFIX."Raid`.End)   AS EndUTC",
        );
        
        $Conditions = Array(
            "`".RP_TABLE_PREFIX."Raid`.Start > FROM_UNIXTIME(?)",
            "`".RP_TABLE_PREFIX."Raid`.Start < FROM_UNIXTIME(?)",
        );
        
        $Parameters = Array(
            $aStart, $aEnd
        );
        
        $TableQuery = " FROM `".RP_TABLE_PREFIX."Raid` ";
        
        // Merge locations if required
        
        if ($aLocation != "")
        {
            $TableQuery .= "LEFT JOIN `".RP_TABLE_PREFIX."Location` USING (LocationId) ";
            
            $Locations = explode(",",$aLocation);
            $LocationConditions = Array();
            
            foreach($Locations as $Location)
            {
                array_push($LocationConditions, "`".RP_TABLE_PREFIX."Location`.Name=?");
                array_push($Parameters, $Location);
            }
            
            array_push($Conditions, $LocationConditions);
        }
                
        // Merge attends if required
        
        if ($aAddAttends === true)
        {
            $TableQuery .= "LEFT JOIN `".RP_TABLE_PREFIX."Attendance` USING (RaidId) ";
            $TableQuery .= "LEFT JOIN `".RP_TABLE_PREFIX."User` USING (UserId) ";
            $TableQuery .= "LEFT JOIN `".RP_TABLE_PREFIX."Character` USING (CharacterId) ";
            
            $Fields = array_merge($Fields, Array(
                "`".RP_TABLE_PREFIX."Attendance`.Status",
                "`".RP_TABLE_PREFIX."Attendance`.Role",
                "`".RP_TABLE_PREFIX."Attendance`.Class",
                "`".RP_TABLE_PREFIX."Attendance`.Comment",
                
                "`".RP_TABLE_PREFIX."User`.UserId",
                "`".RP_TABLE_PREFIX."User`.Login AS UserName",
                "`".RP_TABLE_PREFIX."User`.ExternalBinding AS BindingId",
                "`".RP_TABLE_PREFIX."User`.ExternalId AS BoundUserId",
                
                "`".RP_TABLE_PREFIX."Character`.Name AS CharacterName",
                "`".RP_TABLE_PREFIX."Character`.Class AS CharacterClasses",
                "`".RP_TABLE_PREFIX."Character`.Mainchar AS CharacterIsMain",
                "`".RP_TABLE_PREFIX."Character`.Role1 AS CharacterRole1",
                "`".RP_TABLE_PREFIX."Character`.Role2 AS CharacterRole2",
            ));
        }
        else
        {
            $TableQuery .= "LEFT JOIN `".RP_TABLE_PREFIX."Attendance` USING (RaidId) ";
            
            $Fields = array_merge($Fields, Array(
                "`".RP_TABLE_PREFIX."Attendance`.Status",
                "`".RP_TABLE_PREFIX."Attendance`.Role"
            ));
        }
        
        // Raid status
        
        if (!$aFetchOpen || !$aFetchClosed || !$aFetchCanceled)
        {
            $StatusConditions = Array();
                
            if ($aFetchOpen)
                array_push($StatusConditions, "`".RP_TABLE_PREFIX."Raid`.Stage = 'open'");
            
            if ($aFetchClosed)
                array_push($StatusConditions, "`".RP_TABLE_PREFIX."Raid`.Stage = 'locked'");
            
            if ($aFetchCanceled)
                array_push($StatusConditions, "`".RP_TABLE_PREFIX."Raid`.Stage = 'canceled'");
                
            array_push($Conditions, $StatusConditions);
        }
        
        // Build where part
        
        $WhereString = "";        
        if (sizeof($Conditions) > 0)
        {
            foreach($Conditions as &$Part)
            {
                if (is_array($Part))
                    $Part = "(".implode(" OR ", $Part).")";
            }
            
            $WhereString = " WHERE ".implode(" AND ",$Conditions);
        }
        
        // Build limit part
        
        $LimitString = "";
        if ($aLimit > 0)
        {
            $LimitString = " LIMIT ".intval($aOffset).",".intval($aLimit);
        }
        
        // Build order part
        
        $OrderString = " ORDER BY `".RP_TABLE_PREFIX."Raid`.RaidId";
        
        // Execute query
        
        $QueryString = "SELECT ".implode(",", $Fields).$TableQuery.$WhereString.$OrderString.$LimitString;
        //Out::getInstance()->pushValue("query", $QueryString);
        
        $Connector = Connector::getInstance();
        $RaidQuery = $Connector->prepare($QueryString);
        
        foreach($Parameters as $Index => $Value)
        {
            //Out::getInstance()->pushValue("query", $Value);
            if (is_null($Value))
                $RaidQuery->bindValue($Index+1, intval($Value), PDO::PARAM_INT);
            else
                $RaidQuery->bindValue($Index+1, strval($Value), PDO::PARAM_STR);
        }
        
        // Prepare results
        
        $LastRaidId = 0;
        $Result = Array();
        $Raid = Array();
        
        $RaidQuery->loop(function($aRaidRow) use (&$LastRaidId, &$Raid, &$Result, $aAddAttends, $aFetchFull, $aFetchFree)
        {
            if ($aRaidRow["RaidId"] != $LastRaidId)
            {
                if (sizeof($Raid) > 0)
                {
                    $RaidFull = true;
                    $RaidFree = false;
                    
                    foreach($Raid["Slots"] as $Role => $Max)
                    {
                        $RoleLimitReached = $Raid["SetToRaid"][$Role] >= $Max;                        
                        $RaidFull = $RaidFull && $RoleLimitReached;
                        $RaidFree = $RaidFree || !$RoleLimitReached;
                    }
                
                    if (($aFetchFull && $RaidFull) ||
                        ($aFetchFree && $RaidFree))
                    {                
                        array_push($Result, $Raid);
                    }
                }
                
                // Generate Raid
                                
                $LastRaidId = $aRaidRow["RaidId"];
                $Raid = Array(
                    "RaidId"      => $aRaidRow["RaidId"],
                    "LocationId"  => $aRaidRow["LocationId"],
                    "Status"      => $aRaidRow["Stage"],
                    "Size"        => $aRaidRow["Size"],
                    "Start"       => $aRaidRow["StartUTC"],
                    "End"         => $aRaidRow["EndUTC"],
                    "Description" => $aRaidRow["Description"],
                    "Slots"       => array_combine(explode(":", $aRaidRow["SlotRoles"]), explode(":", $aRaidRow["SlotCount"])),
                    "SetToRaid"   => Array(),
                    "Available"   => Array(),
                    "Absent"      => Array(),
                );
                
                if ($aAddAttends)
                    $Raid["Attends"]  = Array();
                    
                foreach($Raid["Slots"] as $Role => $Max)
                {
                    $Raid["SetToRaid"][$Role] = 0;
                    $Raid["Available"][$Role] = 0;
                    $Raid["Absent"][$Role]    = 0;
                }
            }
            
            // Count available / absent
            
            if ($aRaidRow["Role"] !== null)
            {
                switch($aRaidRow["Status"])
                {
                case "ok":
                    ++$Raid["SetToRaid"][$aRaidRow["Role"]];
                    // ok counts as available, too
                case "available":
                    ++$Raid["Available"][$aRaidRow["Role"]];
                    break;
                
                case "undecided":
                    // TODO: Undecided are only those with a comment
                    break;
                    
                case "unavailable":
                default:
                    ++$Raid["Absent"][$aRaidRow["Role"]];
                    break;
                }
            }
            
            // Add attends if requested
            
            if ($aAddAttends && ($aRaidRow["Status"] !== null))
            {
                $Attend = Array(
                    "UserId"           => $aRaidRow["UserId"],
                    "UserName"         => $aRaidRow["UserName"],
                    "BindingId"        => $aRaidRow["BindingId"],
                    "BoundUserId"      => $aRaidRow["BoundUserId"],
                    "Status"           => $aRaidRow["Status"],
                    "Role"             => $aRaidRow["Role"],
                    "Class"            => $aRaidRow["Class"],
                    "Comment"          => $aRaidRow["Comment"],
                    "CharacterName"    => $aRaidRow["CharacterName"],
                    "CharacterClasses" => explode(":",$aRaidRow["CharacterClasses"]),
                    "CharacterIsMain"  => $aRaidRow["CharacterIsMain"],
                    "CharacterRole1"   => $aRaidRow["CharacterRole1"],
                    "CharacterRole2"   => $aRaidRow["CharacterRole2"],
                );
                
                array_push($Raid["Attends"], $Attend);
            }          
        }); 
        
        // Add remaining raid
        
        if (sizeof($Raid) > 0)
        {
            $RaidFull = true;
            $RaidFree = false;
            
            foreach($Raid["Slots"] as $Role => $Max)
            {
                $RoleLimitReached = $Raid["SetToRaid"][$Role] >= $Max;                        
                $RaidFull = $RaidFull && $RoleLimitReached;
                $RaidFree = $RaidFree || !$RoleLimitReached;
            }
        
            if (($aFetchFull && $RaidFull) ||
                ($aFetchFree && $RaidFree))
            {                
                array_push($Result, $Raid);
            }
        }
        
        return $Result;
    }
?>