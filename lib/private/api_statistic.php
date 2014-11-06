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
            'end'   => getParamFrom($aRequest, 'end', PHP_INT_MAX),
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
            '(`'.RP_TABLE_PREFIX.'Character`.Mainchar IS NULL OR '.
                '`'.RP_TABLE_PREFIX.'Character`.Mainchar = "true")',
            '(`'.RP_TABLE_PREFIX.'Raid`.RaidId IS NULL OR '.
                '(`'.RP_TABLE_PREFIX.'Raid`.Start > `'.RP_TABLE_PREFIX.'User`.Created AND '.
                '`'.RP_TABLE_PREFIX.'Raid`.Start > FROM_UNIXTIME(?) AND '.
                '`'.RP_TABLE_PREFIX.'Raid`.Start < FROM_UNIXTIME(?)))',
        );
        
        $Parameters = Array(
            $aStart,
            $aEnd,
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
                array_push($Conditions, '`'.RP_TABLE_PREFIX.'User`.UserId=?');
                array_push($Parameters, $Users[0]);
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
                array_push($Conditions, 'RaidId=?');
                array_push($Parameters, $Raids[0]);
            }
            else
            {
                array_push($Conditions, 'RaidId IN ('.implode(',',$Raids).')');
            }
        }
        
        // Filter games
        
        if ($aGames != '')
        {
            $Games = explode(',', $aGames);
            $GameByLoc = Array();
            $GameByChar = Array();
            
            foreach($Games as $Game)
            {
                array_push($GameByLoc, '`'.RP_TABLE_PREFIX.'Location`.Game=?');                
                array_push($GameByChar, '`'.RP_TABLE_PREFIX.'Character`.Game=?');                
                array_push($Parameters, $Game);
                array_push($Parameters, $Game);
                array_push($GamesParameter, $Game);
            }
            
            $GamesCondition = implode(' OR ', $GameByLoc);
            
            array_push($GameByChar, '(`'.RP_TABLE_PREFIX.'Character`.CharacterId IS NULL AND (`raids_Location`.Game IS NULL OR '.$GamesCondition.'))');
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
            '`'.RP_TABLE_PREFIX.'Attendance`.Status, '.
            '`'.RP_TABLE_PREFIX.'Attendance`.Role, '.
            'UNIX_TIMESTAMP(`'.RP_TABLE_PREFIX.'User`.Created) AS CreatedUTC, '.
            'COUNT(RaidId) AS Count '.
            'FROM `'.RP_TABLE_PREFIX.'User` '.
            'LEFT JOIN `'.RP_TABLE_PREFIX.'Attendance` USING(UserId) '.
            'LEFT JOIN `'.RP_TABLE_PREFIX.'Character` USING(CharacterId) '.
            'LEFT JOIN `'.RP_TABLE_PREFIX.'Raid` USING(RaidId) '.
            'LEFT JOIN `'.RP_TABLE_PREFIX.'Location` USING(LocationId) '.
            $WhereString.
            'GROUP BY `'.RP_TABLE_PREFIX.'User`.UserId, `'.RP_TABLE_PREFIX.'Attendance`.Status ';
            
        //Out::getInstance()->pushValue('debug', $QueryString);
    
        $Connector = Connector::getInstance();
        $AttendanceQuery = $Connector->prepare( $QueryString );

        foreach($Parameters as $Index => $Value)
        {
            if (is_numeric($Value))
                $AttendanceQuery->bindValue($Index+1, $Value, PDO::PARAM_INT);
            else
                $AttendanceQuery->bindValue($Index+1, $Value, PDO::PARAM_STR);
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
                $aUTF8, $GamesCondition, $GamesParameter, $aEnd)
        {
            if ( $UserId != $Data['UserId'] )
            {
                if ( $UserId > 0 )
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
                
                $RaidQueryString = 'SELECT COUNT(RaidId) AS `NumberOfRaids` FROM `'.RP_TABLE_PREFIX.'Raid` '.
                    'LEFT JOIN `'.RP_TABLE_PREFIX.'Location` USING(LocationId) '.
                    'WHERE Start > FROM_UNIXTIME(?) AND Start < FROM_UNIXTIME(?) '.
                    (($GamesCondition == '') ? '' : 'AND ('.$GamesCondition.')');
                    
                $Raids = $Connector->prepare( $RaidQueryString );
                    
                $Raids->bindValue( 1, $Data['CreatedUTC'], PDO::PARAM_INT );
                $Raids->bindValue( 2, $aEnd,                       PDO::PARAM_INT );
                
                foreach($GamesParameter as $Index => $Value)
                    $Raids->bindValue($Index+3, $Value, PDO::PARAM_STR);
                                
                $RaidCountData = $Raids->fetchFirst();
                $NumRaidsRemain = ($RaidCountData == null) ? 0 : $RaidCountData['NumberOfRaids'];
            }
            
            //Out::getInstance()->pushValue('debug', $Data['Status']);
            
            if ($Data['Status'] != null)
            {
                $StateCounts[$Data['Status']] += $Data['Count'];
                
                if ($Data['Role'] != null)
                {
                    if (!isset($Roles[$Data['Role']]))
                        $Roles[$Data['Role']] = 1;
                    else
                        ++$Roles[$Data['Role']];
                }
                
                $NumRaidsRemain -= $Data['Count'];
            }
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