<?php
    require_once dirname(__FILE__)."/connector.class.php";
    
    // -------------------------------------------------------------------------
    
    $gApiHelp["raid"] = Array(
        "description" => "Query value. Get information about raids.",
        "parameters"  => Array(
            "start"     => "Only return raids starting after this UTC timestamp. Default: 0.",
            "end"       => "Only return raids starting before this UTC timestamp. Default: 0x7FFFFFFF.",
            "limit"     => "Maximum number of raids to return. Passing 0 returns all raids. Default: 10.",
            "offset"    => "Number of raids to skip if a limit is set. Default: 0.",
            "location"  => "Comma separated list of location ids. Only returns raids on these locations. Default: empty.",
            "games"     => "Comma separated list of game ids. Only returns raids for these games. Default: empty",
            "full"      => "Include raids that have all slots set. Default: true.",
            "free"      => "Include raids that do not have all slots set. Default: true.",
            "open"      => "Include raids that are open for registration. Default: true.",
            "closed"    => "Include raids that are closed for registration. Default: false.",
            "canceled"  => "Include raids that have been canceled. Default: false.",
            "attends"   => "Return list of attended players, too. Default: false.",    
        )
    );
    
    // -------------------------------------------------------------------------
    
    function api_args_raid($aRequest)
    {
        return Array(
            "start"     => getParamFrom($aRequest, "start", 0),
            "end"       => getParamFrom($aRequest, "end", 0x7FFFFFFF),
            "limit"     => getParamFrom($aRequest, "limit", 10),
            "offset"    => getParamFrom($aRequest, "offset", 0),
            "location"  => getParamFrom($aRequest, "location", ""),
            "games"     => getParamFrom($aRequest, "games", ""),
            "full"      => getParamFrom($aRequest, "full", true),
            "free"      => getParamFrom($aRequest, "free", true),
            "open"      => getParamFrom($aRequest, "open", true),
            "closed"    => getParamFrom($aRequest, "closed", false),
            "canceled"  => getParamFrom($aRequest, "canceled", false),
            "attends"   => getParamFrom($aRequest, "attends", false),
        );
    }
    
    // -------------------------------------------------------------------------
    
    function api_query_raid($aParameter)
    {
        // Assemble paramters
        
        $aStart         = getParamFrom($aParameter, "start",    0);
        $aEnd           = getParamFrom($aParameter, "end",      0x7FFFFFFF);
        $aLimit         = getParamFrom($aParameter, "limit",    10);
        $aOffset        = getParamFrom($aParameter, "offset",   0);
        $aLocation      = getParamFrom($aParameter, "location", "");
        $aGames         = getParamFrom($aParameter, "games",    "");
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
        
        $TableQuery  = " FROM `".RP_TABLE_PREFIX."Raid` ";
        $TableQuery .= "LEFT JOIN `".RP_TABLE_PREFIX."Location` USING (LocationId) ";
            
        // Merge locations if required
        
        if ($aLocation != "")
        {            
            $Locations = explode(",",$aLocation);
            $LocationById = Array();
            $LocationByName = Array();
            $LocationConditions = Array();
            
            // Sort into ids and names
            
            foreach($Locations as $Location)
            {
                if (is_numeric($Location))
                    array_push($LocationById, intval($Location));
                else
                    array_push($LocationByName, $Location);
            }
            
            // Build id based condition
            
            if (count($LocationById) == 1)
            {
                array_push($LocationConditions, "`".RP_TABLE_PREFIX."Location`.LocationId=?");
                array_push($Parameters, $LocationById[0]);
            }
            else if (count($LocationById) > 1)
            {
                array_push($LocationConditions, "`".RP_TABLE_PREFIX."Location`.LocationId IN (".implode(",",$LocationById).")");
            }
            
            // Build name based condition
            
            if (count($LocationByName) == 1)
            {
                array_push($LocationConditions, "`".RP_TABLE_PREFIX."Location`.Name=?");
                array_push($Parameters, $LocationByName[0]);
            }
            else if (count($LocationByName) > 1)
            {
                array_push($LocationConditions, "`".RP_TABLE_PREFIX."Location`.Name IN ('".implode("','",$LocationByName)."')");
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
        
        // Filter games
        
        if ($aGames != "")
        {
            $Games = explode(",", $aGames);
            $GameOptions = Array();
            
            foreach($Games as $Game)
            {
                array_push($GameOptions, "`".RP_TABLE_PREFIX."Location`.Game=?");
                array_push($Parameters, $Game);
            }
            
            array_push($Conditions, $GameOptions);
        }
        
        // Build where part
        
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
            if (is_numeric($Value))
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
                if (api_filter_raid($Raid, $aFetchFull, $aFetchFree))
                   array_push($Result, $Raid);
                
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
                    "Absent"      => 0,
                );
                
                if ($aAddAttends)
                {
                    $Raid["Attends"]  = Array();
                }                
                   
                foreach($Raid["Slots"] as $Role => $Max)
                {
                    $Raid["SetToRaid"][$Role] = 0;
                    $Raid["Available"][$Role] = 0;
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
                    // TODO: Need to query all available users to return undecided
                    break;
                    
                case "unavailable":
                default:
                    ++$Raid["Absent"];
                    break;
                }
            }
            
            // Add attends if requested
            
            if ($aAddAttends && ($aRaidRow["Status"] !== null))
            {
                $Attend = Array(
                    "UserId"           => $aRaidRow["UserId"],
                    "BindingId"        => $aRaidRow["BindingId"],
                    "BoundUserId"      => $aRaidRow["BoundUserId"],
                    "Status"           => $aRaidRow["Status"],
                    "Role"             => $aRaidRow["Role"],
                    "Class"            => $aRaidRow["Class"],
                    "Comment"          => $aRaidRow["Comment"],
                    "CharacterName"    => $aRaidRow["CharacterName"],
                    "CharacterIsMain"  => $aRaidRow["CharacterIsMain"],
                    "CharacterClasses" => explode(":",$aRaidRow["CharacterClasses"]),
                    "CharacterRoles"   => Array($aRaidRow["CharacterRole1"], $aRaidRow["CharacterRole2"]),
                );
                
                array_push($Raid["Attends"], $Attend);
            }          
        }); 
        
        // Add remaining raid
        
        if (api_filter_raid($Raid, $aFetchFull, $aFetchFree))
            array_push($Result, $Raid);
    
        return $Result;
    }
    
    // -------------------------------------------------------------------------
    
    function api_filter_raid($aRaid, $aFetchFull, $aFetchFree)
    {
        if (count($aRaid) > 0)
        {
            $RaidFull = true;
            $RaidFree = false;
            
            foreach($aRaid["Slots"] as $Role => $Max)
            {
                $RoleLimitReached = $aRaid["SetToRaid"][$Role] >= $Max;                        
                $RaidFull = $RaidFull && $RoleLimitReached;
                $RaidFree = $RaidFree || !$RoleLimitReached;
            }
        
            if (($aFetchFull && $RaidFull) ||
                ($aFetchFree && $RaidFree))
            {                
                return true;
            }
        }
        
        return false;
    }
?>