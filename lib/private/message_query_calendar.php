<?php

    function prepareCalRequest( $aMonth, $aYear )
    {
        $CalRequest['Month'] = $aMonth;
        $CalRequest['Year']  = $aYear;
    
        return $CalRequest;
    }
    
    // -----------------------------------------------------------------------------
    
    function getCalStartDay()
    {
        $Connector = Connector::getInstance();
        $SettingsQuery = $Connector->prepare( 'Select IntValue FROM '.RP_TABLE_PREFIX.'Setting WHERE Name = "StartOfWeek" LIMIT 1' );
    
        $Data = $SettingsQuery->fetchFirst();
        $FirstDay = ($Data == null) ? 1 : intval($Data['IntValue']);
    
        return $FirstDay;
    }
    
    // -----------------------------------------------------------------------------
    
    function msgQueryCalendar( $aRequest )
    {
        if (validUser())
        {
            global $gGame, $gSite;
            loadGameSettings();
            
            $Out = Out::getInstance();
            $Connector = Connector::getInstance();
    
            $ListRaidQuery = $Connector->prepare(  'Select '.RP_TABLE_PREFIX.'Raid.*, '.RP_TABLE_PREFIX.'Location.*, '.
                                                RP_TABLE_PREFIX.'Attendance.CharacterId, '.RP_TABLE_PREFIX.'Attendance.UserId, '.
                                                RP_TABLE_PREFIX.'Attendance.Status, '.RP_TABLE_PREFIX.'Attendance.Class, '.RP_TABLE_PREFIX.'Attendance.Role, '.RP_TABLE_PREFIX.'Attendance.Comment, '.
                                                'UNIX_TIMESTAMP('.RP_TABLE_PREFIX.'Raid.Start) AS StartUTC, '.
                                                'UNIX_TIMESTAMP('.RP_TABLE_PREFIX.'Raid.End) AS EndUTC '.
                                                'FROM `'.RP_TABLE_PREFIX.'Raid` '.
                                                'LEFT JOIN `'.RP_TABLE_PREFIX.'Location` USING(LocationId) '.
                                                'LEFT JOIN `'.RP_TABLE_PREFIX.'Attendance` USING (RaidId) '.
                                                'LEFT JOIN `'.RP_TABLE_PREFIX.'Character` USING (CharacterId) '.
                                                'WHERE '.RP_TABLE_PREFIX.'Raid.Start >= FROM_UNIXTIME(:Start) AND '.RP_TABLE_PREFIX.'Raid.Start <= FROM_UNIXTIME(:End) '.
                                                'AND '.RP_TABLE_PREFIX.'Location.Game = :Game '.
                                                'ORDER BY '.RP_TABLE_PREFIX.'Raid.Start, '.RP_TABLE_PREFIX.'Raid.RaidId' );
    
            // Calculate the correct start end end times
    
            $StartDay = getCalStartDay();
            $StartUTC = mktime(0, 0, 0, $aRequest['Month'], 1, $aRequest['Year']);
            $StartDate = getdate($StartUTC);
    
            if ( $StartDate['wday'] != $StartDay )
            {
                // Calculate the first day displayed in the calendar
    
                $Offset = ($StartDate['wday'] < $StartDay)
    
                    ? 7 - ($StartDay - $StartDate['wday'])
    
                    : ($StartDate['wday'] - $StartDay);
    
                $StartUTC -= 60 * 60 * 24 * $Offset;
                $StartDate = getdate($StartUTC);
            }
    
            // Calculate the last day displayed in the calendar
    
            $EndUTC = $StartUTC + 60 * 60 * 24 * 7 * 6; // + 6 weeks
    
            // Query and return
    
            $ListRaidQuery->bindValue(':Start', $StartUTC, PDO::PARAM_INT);
            $ListRaidQuery->bindValue(':End',   intval($EndUTC),   PDO::PARAM_INT);
            $ListRaidQuery->bindValue(':Game',  $gGame['GameId'],  PDO::PARAM_STR);
    
            $Session = Session::get();
            
            $Session['Calendar'] = Array( 
                'month' => intval($aRequest['Month']),
                'year'  => intval($aRequest['Year'])
            );
    
            $Out->pushValue('startDay', $StartDate['mday']);
            $Out->pushValue('startMonth', $StartDate['mon']);
            $Out->pushValue('startYear', $StartDate['year']);
            $Out->pushValue('startOfWeek', $StartDay);
            $Out->pushValue('displayMonth', $aRequest['Month']);
            $Out->pushValue('displayYear', $aRequest['Year']);
            $Out->pushValue('showBigIcons', $gSite['CalendarBigIcons']);

            parseRaidQuery( $aRequest, $ListRaidQuery, 0 );
        }
        else
        {
            $Out = Out::getInstance();
            $Out->pushError(L('AccessDenied'));
        }
    }
    
    // -----------------------------------------------------------------------------
    
    function parseRaidQuery( $aRequest, $aQueryResult, $aLimit )
    {
        $Out = Out::getInstance();
    
        $RaidData = Array();
        $RoleInfo = Array();
        $NumAttends = Array();
    
        $aQueryResult->loop( function($Data) use (&$RaidData, &$RoleInfo, &$NumAttends)
        {
            array_push($RaidData, $Data);
            $RaidId = $Data['RaidId'];
            
            // Create used slot counts
    
            if ( !isset($RoleInfo[$RaidId]) )
                $RoleInfo[$RaidId] = Array();
            
            if ( !isset($NumAttends[$RaidId]) )
                $NumAttends[$RaidId] = 0;
                
            // Count used slots
    
            if ( ($Data['Status'] == 'ok') ||
                 ($Data['Status'] == 'available') )
            {
                $Role = $Data['Role'];
                                    
                if ( !isset($RoleInfo[$RaidId][$Role]) )
                    $RoleInfo[$RaidId][$Role] = 0;
                                
                ++$NumAttends[$RaidId];    
                ++$RoleInfo[$RaidId][$Role];                    
            }
        });
    
        $LastRaidId = -1;
        $RaidDataCount = count($RaidData);
    
        $NumRaids = 0;
        $Raids = Array();
    
        for ( $DataIdx=0; $DataIdx < $RaidDataCount; ++$DataIdx )
        {
            $Data = $RaidData[$DataIdx];
            $RaidId = $Data['RaidId'];
    
            if ( $LastRaidId != $RaidId )
            {
                // If no user assigned for this raid
                // or row belongs to this user
                // or it's the last entry
                // or the next entry is a different raid
    
                $IsCorrectUser = $Data['UserId'] == UserProxy::getInstance()->UserId;
    
                if ( ($IsCorrectUser) ||
                     ($Data['UserId'] == NULL) ||
                     ($DataIdx+1 == $RaidDataCount) ||
                     ($RaidData[$DataIdx+1]['RaidId'] != $RaidId) )
                {
                    $Status = 'notset';
                    $AttendanceIndex = 0;
                    $Role = '';
                    $Class = '';
                    $Comment = '';
    
                    if ( $IsCorrectUser )
                    {
                        $Status = $Data['Status'];
                        $AttendanceIndex = ($Status == 'unavailable') ? -1 : intval($Data['CharacterId']);
                        $Role = $Data['Role'];
                        $Class = $Data['Class'];
                        $Comment = $Data['Comment'];
                    }
    
                    $StartDate = getdate($Data['StartUTC']);
                    $EndDate   = getdate($Data['EndUTC']);
    
                    $Raid = Array(
                        'id'              => $RaidId,
                        'location'        => $Data['Name'],
                        'game'            => $Data['Game'],
                        'stage'           => $Data['Stage'],
                        'size'            => $Data['Size'],
                        'startDate'       => $StartDate['year'].'-'.leadingZero10($StartDate['mon']).'-'.leadingZero10($StartDate['mday']),
                        'start'           => leadingZero10($StartDate['hours']).':'.leadingZero10($StartDate['minutes']),
                        'endDate'         => $EndDate['year'].'-'.leadingZero10($EndDate['mon']).'-'.leadingZero10($EndDate['mday']),
                        'end'             => leadingZero10($EndDate['hours']).':'.leadingZero10($EndDate['minutes']),
                        'image'           => $Data['Image'],
                        'description'     => $Data['Description'],
                        'status'          => $Status,
                        'attendanceIndex' => $AttendanceIndex,
                        'comment'         => $Comment,
                        'role'            => $Role,
                        'classId'         => $Class,
                        'slotMax'         => Array(),
                        'slotCount'       => Array(),
                        'attended'        => $NumAttends[$RaidId],
                        'mode'            => $Data['Mode']
                    );
                    
                    $Roles = explode(':',$Data['SlotRoles']);
                    $Count = explode(':',$Data['SlotCount']);
                    
                    for ( $i=0; $i < count($Roles); ++$i )
                    {
                        $RoleId = $Roles[$i];
                        
                        $Raid['slotMax'][$RoleId] = $Count[$i];
                        $Raid['slotCount'][$RoleId] = (isset($RoleInfo[$RaidId][$RoleId])) ? $RoleInfo[$RaidId][$RoleId] : 0;
                    }
    
                    array_push($Raids, $Raid);
    
                    $LastRaidId = $RaidId;
                    ++$NumRaids;
    
                    if ( ($aLimit > 0) && ($NumRaids == $aLimit) )
                        break;
                }
            }
        }
    
        $Out->pushValue('raid', $Raids);
    }
?>