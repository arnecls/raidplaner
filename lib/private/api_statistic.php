<?php
    require_once dirname(__FILE__).'/connector.class.php';
    
    // -------------------------------------------------------------------------
    
    $gApiHelp['statistic'] = Array(
        'description' => 'Query value. Get user statistics.',
        'parameters' => Array(
            'start'     => 'Only count raids starting after this UTC timestamp. Default: 0.',
            'end'       => 'Only count raids starting before this UTC timestamp. Default: now.',
            'raids'     => 'Comma separated list of raid ids to count. Empty counts all raids. Default: empty.',
            'users'     => 'Comma separated list of user names to include. Empty returns all users. Default: empty.',
            'games'     => 'Comma separated list of game ids. Only returns statistics for these games. Default: empty',
            'utf8'      => 'Convert strings back to UTF8. Default: false.'
        )
    );
    
    // -------------------------------------------------------------------------
    
    function api_args_statistic($aRequest)
    {
        return Array(
            'start' => getParamFrom($aRequest, 'start', 0),
            'end'   => getParamFrom($aRequest, 'end', time()),
            'raids' => getParamFrom($aRequest, 'raids', ''),
            'users' => getParamFrom($aRequest, 'users', ''),
            'games' => getParamFrom($aRequest, 'games', ''),
            'utf8'  => getParamFrom($aRequest, 'utf8', false)
        );
    }
    
    // -------------------------------------------------------------------------
    
    function api_query_statistic($aParameter)
    {
        $aStart = getParamFrom($aParameter, 'start', 0);
        $aEnd   = getParamFrom($aParameter, 'end',   time());
        $aRaids = getParamFrom($aParameter, 'raids', '');
        $aUsers = getParamFrom($aParameter, 'users', '');
        $aGames = getParamFrom($aParameter, 'games', '');
        $aUTF8  = getParamFrom($aParameter, 'utf8',  false);
        
        // Build query
        
        $Conditions = Array(
            '`'.RP_TABLE_PREFIX.'Character`.Mainchar = true',
            '`'.RP_TABLE_PREFIX.'Raid`.Start > `'.RP_TABLE_PREFIX.'User`.Created',
            '`'.RP_TABLE_PREFIX.'Raid`.Start > FROM_UNIXTIME(:Start)',
            '`'.RP_TABLE_PREFIX.'Raid`.Start < FROM_UNIXTIME(:End)'
        );
        
        $Parameters = Array(
            "Start" => $aStart,
            "End"   => $aEnd,
        );
        
        $GamesCondition = '';
        $GamesParameter = Array();
        
        // Filter users
        
        if ($aUsers != '')
        {
            $Users = explode(',', $aUsers);
            foreach($Users as &$UserId)
                $UserId = intval($UserId);
                
            if (count($Users) == 1)
            {         
                array_push($Conditions, '`'.RP_TABLE_PREFIX.'User`.UserId=:UserId');                
                $Parameters["UserId"] = $Users[0];
            }
            else
            {
                array_push($Conditions, '`'.RP_TABLE_PREFIX.'User`.UserId IN ('.implode(',',$Users).')');
            }
        }
        
        // Filter raids
        
        if ($aRaids != '')
        {
            $Raids = explode(',', $aRaids);
            foreach($Raids as &$RaidId)
                $RaidId = intval($RaidId);
                
            if (count($Raids) == 1)
            {         
                array_push($Conditions, '`'.RP_TABLE_PREFIX.'raid`.RaidId=:RaidId');                
                $Parameters["RaidId"] = $Raids[0];
            }
            else
            {
                array_push($Conditions, '`'.RP_TABLE_PREFIX.'raid`.RaidId IN ('.implode(',',$Raids).')');
            }
        }
        
        // Filter games
        
        if ($aGames != '')
        {
            $Games = explode(',', $aGames);
            $GameByLoc = Array();
            $GameByChar = Array();
            
            $GameIdx = 0;
            
            foreach($Games as $Game)
            {
                array_push($GameByLoc, '`'.RP_TABLE_PREFIX.'Location`.Game=:Game'.$GameIdx);                
                array_push($GameByChar, '`'.RP_TABLE_PREFIX.'Character`.Game=:Game'.$GameIdx);
                
                $Parameters["Game".$GameIdx]     = $Game;
                $GamesParameter["Game".$GameIdx] = $Game;
                ++$GameIdx;
            }
            
            $GamesCondition = implode(' OR ', $GameByLoc);
            
            array_push($GameByChar, '(`'.RP_TABLE_PREFIX.'Character`.CharacterId IS NULL AND (`'.RP_TABLE_PREFIX.'Location`.Game IS NULL OR '.$GamesCondition.'))');
            array_push($Conditions, $GameByChar);
        }
        
        // Build where clause
        
        $WhereString = '';        
        if (count($Conditions) > 0)
        {
            foreach($Conditions as &$Part)
            {
                if (is_array($Part))
                    $Part = '('.implode(' OR ', $Part).')';
            }
            
            $WhereString = ' WHERE '.implode(' AND ',$Conditions).' ';
        }
        
        // Query attendances
        
        $QueryString = 'SELECT '.
            '`'.RP_TABLE_PREFIX.'User`.UserId, '.
            '`'.RP_TABLE_PREFIX.'Character`.Name, '.
            '`'.RP_TABLE_PREFIX.'Attendance`.`Status`, '.
            '`'.RP_TABLE_PREFIX.'Attendance`.Role, '.
            'UNIX_TIMESTAMP(`'.RP_TABLE_PREFIX.'User`.Created) AS CreatedUTC, '.
            'COUNT(`'.RP_TABLE_PREFIX.'Raid`.RaidId) AS Count '.
            'FROM `'.RP_TABLE_PREFIX.'User` '.
            'LEFT JOIN `'.RP_TABLE_PREFIX.'Attendance` USING(UserId) '.
            'LEFT JOIN `'.RP_TABLE_PREFIX.'Raid` USING(RaidId) '.
            'LEFT JOIN `'.RP_TABLE_PREFIX.'Location` USING(LocationId) '.
            'LEFT JOIN `'.RP_TABLE_PREFIX.'Character` ON `'.RP_TABLE_PREFIX.'User`.UserId = `'.RP_TABLE_PREFIX.'Character`.UserId '.
            $WhereString.
            'GROUP BY `'.RP_TABLE_PREFIX.'User`.UserId, `'.RP_TABLE_PREFIX.'Attendance`.`Status`, `'.RP_TABLE_PREFIX.'Attendance`.Role ';
            
        $Connector = Connector::getInstance();
        
        $AttendanceQuery = $Connector->prepare( $QueryString );
        
        foreach($Parameters as $IndexName => $Value)
        {        
            if (is_numeric($Value))
                $AttendanceQuery->bindValue(':'.$IndexName, $Value, PDO::PARAM_INT);
            else
                $AttendanceQuery->bindValue(':'.$IndexName, $Value, PDO::PARAM_STR);
        }

        $UserId = 0;
        $NumRaidsRemain = 0;
        $MainCharName = '';
        $StateCounts = Array( 'undecided' => 0, 'available' => 0, 'unavailable' => 0, 'ok' => 0 );
        $Attendances = Array();
        $Roles = Array();
        
        $AttendanceQuery->loop( 
            function($Data) use (
                $Connector, &$UserId, &$NumRaidsRemain, 
                &$MainCharName, &$StateCounts, &$Attendances, &$Roles, 
                $aUTF8, &$GamesCondition, &$GamesParameter, $aStart, $aEnd)
        {
            if ( $UserId != $Data['UserId'] )
            {
                // User changed, store cache
                
                if ( $UserId != 0 )
                {
                    $AttendanceData = Array(
                        'Id'        => $UserId,
                        'MainChar'  => ($aUTF8) ? xmlToUTF8($MainCharName) : $MainCharName,
                        'SetToRaid' => $StateCounts['ok'],
                        'Available' => $StateCounts['available'],
                        'Absent'    => $StateCounts['unavailable'],
                        'Undecided' => $StateCounts['undecided'] + $NumRaidsRemain,
                        'Roles'     => $Roles,
                    );
                    array_push($Attendances, $AttendanceData);
                }

                // Clear cache

                $StateCounts['ok'] = 0;
                $StateCounts['available'] = 0;
                $StateCounts['unavailable'] = 0;
                $StateCounts['undecided'] = 0;
                $NumRaidsRemain = 0;
                $Roles = Array();

                $UserId = $Data['UserId'];
                $MainCharName = $Data['Name'];

                // Fetch number of attendable raids
                
                $RaidQueryString = 'SELECT '.
                    'COUNT(RaidId) AS `NumberOfRaids` '.
                    'FROM `'.RP_TABLE_PREFIX.'Raid` '.
                    'LEFT JOIN `'.RP_TABLE_PREFIX.'Location` USING(LocationId) '.
                    'WHERE `'.RP_TABLE_PREFIX.'Raid`.Start > FROM_UNIXTIME(:Created) '.
                    'AND `'.RP_TABLE_PREFIX.'Raid`.Start > FROM_UNIXTIME(:Start) '.
                    'AND `'.RP_TABLE_PREFIX.'Raid`.Start < FROM_UNIXTIME(:End) '.
                    (($GamesCondition == '') ? '' : 'AND ('.$GamesCondition.')');
                    
                $Raids = $Connector->prepare( $RaidQueryString );
                    
                $Raids->bindValue( ':Start',   $aStart,             PDO::PARAM_INT );
                $Raids->bindValue( ':End',     $aEnd,               PDO::PARAM_INT );
                $Raids->bindValue( ':Created', $Data['CreatedUTC'], PDO::PARAM_INT );
                
                foreach($GamesParameter as $IndexName => $Value)
                {
                     if (is_numeric($Value))
                        $Raids->bindValue(':'.$IndexName, $Value, PDO::PARAM_INT);
                    else
                        $Raids->bindValue(':'.$IndexName, $Value, PDO::PARAM_STR);
                }
                               
                $RaidCountData = $Raids->fetchFirst();
                $NumRaidsRemain = ($RaidCountData == null) ? 0 : $RaidCountData['NumberOfRaids'];
            }
                        
            // Same user / first entry, add data to cache
            
            if ($Data['Status'] == null)
                return true; // ### continue, invalid data ###
            
            $StateCounts[$Data['Status']] += $Data['Count'];                                
            $NumRaidsRemain -= $Data['Count'];
            
            if (($Data['Role'] == null) || ($Data['Status'] == 'unavailable'))
                return true; // ### continue, no role set or absent ###
            
            if (!isset($Roles[$Data['Role']]))
                $Roles[$Data['Role']] = 1;
            else
                ++$Roles[$Data['Role']];
        });
        
        // Push last user

        if ($UserId != 0)
        {
            $AttendanceData = Array(
                'Id'        => $UserId,
                'MainChar'  => ($aUTF8) ? xmlToUTF8($MainCharName) : $MainCharName,
                'SetToRaid' => $StateCounts['ok'],
                'Available' => $StateCounts['available'],
                'Absent'    => $StateCounts['unavailable'],
                'Undecided' => $StateCounts['undecided'] + $NumRaidsRemain,
                'Roles'     => $Roles,
            );

            array_push($Attendances, $AttendanceData);
        }
        
        return $Attendances;
    }
?>
