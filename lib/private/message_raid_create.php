<?php

    function msgRaidCreate( $aRequest )
    {
        if ( validRaidlead() )
        {
            global $gGame;
            
            loadGameSettings();
            $Connector = Connector::getInstance();
            $LocationId = $aRequest['locationId'];
    
            // Create location
    
            if ( $LocationId == 0 )
            {
                $NewLocationQuery = $Connector->prepare('INSERT INTO `'.RP_TABLE_PREFIX.'Location`'.
                                                        '(Game, Name, Image) VALUES (:Game, :Name, :Image)');
    
                $NewLocationQuery->bindValue(':Name', requestToXML( $aRequest['locationName'], ENT_COMPAT, 'UTF-8' ), PDO::PARAM_STR );
                $NewLocationQuery->bindValue(':Image', $aRequest['raidImage'], PDO::PARAM_STR );
                $NewLocationQuery->bindValue(':Game', $gGame['GameId'], PDO::PARAM_STR );
    
                if (!$NewLocationQuery->execute())
                    return; // ### return, location could not be created ###
    
                $LocationId = $Connector->lastInsertId();
            }
    
            // Create raid
    
            if ( $LocationId != 0 )
            {
                // First raid time calculation
    
                $StartHour   = intval($aRequest['startHour']);
                $StartMinute = intval($aRequest['startMinute']);
                $StartDay    = intval($aRequest['startDay']);
                $StartMonth  = intval($aRequest['startMonth']);
                $StartYear   = intval($aRequest['startYear']);
    
                $EndHour   = intval($aRequest['endHour']);
                $EndMinute = intval($aRequest['endMinute']);
                $EndDay    = intval($aRequest['endDay']);
                $EndMonth  = intval($aRequest['endMonth']);
                $EndYear   = intval($aRequest['endYear']);
    
                // Get users on vacation
    
                $UserSettingsQuery = $Connector->prepare('SELECT UserId, Name, IntValue, TextValue FROM `'.RP_TABLE_PREFIX.'UserSetting` '.
                   'WHERE Name = "VacationStart" OR Name = "VacationEnd" OR Name = "VacationMessage" ORDER BY UserId');
    
                $VactionUsers = array();
                $UserSettingsQuery->loop( function($Settings) use (&$VactionUsers)
                {
                    if (!isset($VactionUsers[$Settings['UserId']]))
                    {
                        $VactionUsers[$Settings['UserId']] = array('Message' => '');
                    }
    
                    switch ($Settings['Name'])
                    {
                    case 'VacationStart':
                        $VactionUsers[$Settings['UserId']]['Start'] = $Settings['IntValue'];
                        break;
    
                    case 'VacationEnd':
                        $VactionUsers[$Settings['UserId']]['End'] = $Settings['IntValue'];
                        break;
    
                    case 'VacationMessage':
                        $VactionUsers[$Settings['UserId']]['Message'] = $Settings['TextValue'];
                        break;
    
                    default:
                        break;
                    }
                });
    
                // Prepare posting raids to forum
    
                $PostTargets = array();
                PluginRegistry::ForEachBinding(function($PluginInstance) use (&$PostTargets)
                {
                    if ($PluginInstance->isActive() && $PluginInstance->postRequested())
                    {
                        array_push($PostTargets, $PluginInstance);
                    }
                });
    
                $LocationData = null;
    
                if ( count($PostTargets) > 0 )
                {
                    loadSiteSettings();
    
                    $LocationQuery = $Connector->prepare('SELECT * FROM `'.RP_TABLE_PREFIX.'Location` WHERE LocationId = :LocationId LIMIT 1');
                    $LocationQuery->bindValue(':LocationId', $LocationId, PDO::PARAM_INT);
                    $LocationData = $LocationQuery->fetchFirst();
                }
                
                // Get opt-out list or auto attend users
                
                $AutoAttendUsers = Array();
                
                if (strtolower($aRequest['mode'] == 'optout'))
                {
                    $UserQuery = $Connector->prepare('SELECT UserId, CharacterId, Class, Role1 FROM `'.RP_TABLE_PREFIX.'User` '.
                        'LEFT JOIN `'.RP_TABLE_PREFIX.'Character` USING(UserId) '.
                        'WHERE Mainchar="true" AND Game=:Game');
                        
                    $UserQuery->bindValue(':Game', $gGame['GameId'], PDO::PARAM_STR);
                    $UserQuery->loop(function($aUser) use (&$AutoAttendUsers)
                    {
                        array_push($AutoAttendUsers, $aUser);
                    });
                }
                else
                {
                    $UserQuery = $Connector->prepare('SELECT UserId, CharacterId, Class, Role1 FROM `'.RP_TABLE_PREFIX.'UserSetting` '.
                        'LEFT JOIN `'.RP_TABLE_PREFIX.'Character` USING(UserId) '.
                        'WHERE `'.RP_TABLE_PREFIX.'UserSetting`.Name="AutoAttend" AND Mainchar="true AND Game=:Game');
                        
                    $UserQuery->bindValue(':Game', $gGame['GameId'], PDO::PARAM_STR);
                    $UserQuery->loop(function($aUser) use (&$AutoAttendUsers)
                    {
                        array_push($AutoAttendUsers, $aUser);
                    });
                }
    
                // Create raids(s)
    
                $Repeat = max(0, intval($aRequest['repeat'])) + 1; // repeat at least once
    
                $GroupInfo = $gGame['Groups'][$aRequest['locationSize']];            
                $SlotRoles = implode(':', array_keys($GroupInfo));
                $SlotCount = implode(':', $GroupInfo);
                $RaidMode  = ($aRequest['mode'] == 'optout') ? 'manual' : $aRequest['mode'];
    
                
                for ($rc=0; $rc<$Repeat; ++$rc)
                {
                    $NewRaidQuery = $Connector->prepare('INSERT INTO `'.RP_TABLE_PREFIX.'Raid` '.
                                                        '(LocationId, Size, Start, End, Mode, Description, SlotRoles, SlotCount ) '.
                                                        'VALUES (:LocationId, :Size, FROM_UNIXTIME(:Start), FROM_UNIXTIME(:End), :Mode, :Description, '.
                                                        ':SlotRoles, :SlotCount)');
    
                    $StartDateTime = mktime($StartHour, $StartMinute, 0, $StartMonth, $StartDay, $StartYear);
                    $EndDateTime   = mktime($EndHour, $EndMinute, 0, $EndMonth, $EndDay, $EndYear);
    
                    // Convert to UTC
    
                    $StartDateTime += $aRequest['startOffset'] * 60;
                    $EndDateTime   += $aRequest['endOffset'] * 60;
                    
                    
                    $NewRaidQuery->bindValue(':LocationId',   $LocationId, PDO::PARAM_INT);
                    $NewRaidQuery->bindValue(':Size',        $aRequest['locationSize'], PDO::PARAM_INT);
                    $NewRaidQuery->bindValue(':Start',       $StartDateTime, PDO::PARAM_INT);
                    $NewRaidQuery->bindValue(':End',         $EndDateTime, PDO::PARAM_INT);
                    $NewRaidQuery->bindValue(':Mode',        $RaidMode, PDO::PARAM_STR);
                    $NewRaidQuery->bindValue(':Description', requestToXML( $aRequest['description'], ENT_COMPAT, 'UTF-8' ), PDO::PARAM_STR);
                    $NewRaidQuery->bindValue(':SlotRoles',   $SlotRoles, PDO::PARAM_STR);
                    $NewRaidQuery->bindValue(':SlotCount',   $SlotCount, PDO::PARAM_STR);
    
                    $NewRaidQuery->execute();
                    $RaidId = $Connector->lastInsertId();
                    
                    // Attend players when mode is optout
                    
                    if (count($AutoAttendUsers > 0))
                    {
                        $Status = (($RaidMode == 'all') || ($RaidMode == 'attend')) 
                            ? 'ok' 
                            : 'available'; 
                            
                        foreach($AutoAttendUsers as $User)
                        {
                            $UserId = intval($User['UserId']);
                            if (isset($VactionUsers[$UserId]) &&
                                (($StartDateTime >= $VactionUsers[$UserId]['Start']) && ($StartDateTime <= $VactionUsers[$UserId]['End'])))
                            {
                                continue; // ### continue, user is on vacation ###
                            }
                            
                            $Classes = explode(':', $User['Class']);
                            $ClassId = $Classes[0];
                            
                            $RoleId = ($gGame['ClassMode'] == 'multi')
                                ? $gGame['Classes'][$ClassId]['roles'][0]
                                : $User['Role1'];
                                
                            $AttendQuery = $Connector->prepare('INSERT INTO `'.RP_TABLE_PREFIX.'Attendance` (UserId, RaidId, CharacterId, Class, Role, Status) '.
                                                               'VALUES (:UserId, :RaidId, :CharId, :Class, :Role, :Status)');
    
                            $AttendQuery->bindValue(':UserId', $UserId, PDO::PARAM_INT);
                            $AttendQuery->bindValue(':RaidId', $RaidId, PDO::PARAM_INT);
                            $AttendQuery->bindValue(':CharId', $User['CharacterId'], PDO::PARAM_INT);
                            $AttendQuery->bindValue(':Class', $ClassId, PDO::PARAM_STR);
                            $AttendQuery->bindValue(':Role', $RoleId, PDO::PARAM_STR);
                            $AttendQuery->bindValue(':Status', $Status, PDO::PARAM_STR);
    
                            $AttendQuery->execute();
                        }
                        
                        if ($RaidMode == 'attend')
                        {
                            removeOverbooked($RaidId, $SlotRoles, $SlotCount);
                        }
                    }
                    
                    // Set vacation attendances
    
                    foreach ($VactionUsers as $UserId => $Settings)
                    {
                        if ( ($StartDateTime >= $Settings['Start']) && ($StartDateTime <= $Settings['End']) )
                        {
                            $AbsentQuery = $Connector->prepare('INSERT INTO `'.RP_TABLE_PREFIX.'Attendance` (UserId, RaidId, Status, Comment) '.
                                                               'VALUES (:UserId, :RaidId, "unavailable", :Message)');
    
                            $AbsentQuery->bindValue(':UserId', $UserId, PDO::PARAM_INT);
                            $AbsentQuery->bindValue(':RaidId', $RaidId, PDO::PARAM_INT);
                            $AbsentQuery->bindValue(':Message', $Settings['Message'], PDO::PARAM_STR);
    
                            $AbsentQuery->execute();
                        }
                    }
    
                    // Post raids to forum
    
                    if (count($PostTargets) > 0)
                    {
                        $RaidQuery = $Connector->prepare('SELECT * FROM `'.RP_TABLE_PREFIX.'Raid` WHERE RaidId=:RaidId LIMIT 1');
                        $RaidQuery->bindValue(':RaidId',  $RaidId, PDO::PARAM_INT);
                        $RaidData = $RaidQuery->fetchFirst();
                        
                        $MessageData = Binding::generateMessage($RaidData, $LocationData);
                        
                        try
                        {
                            foreach($PostTargets as $PluginInstance)
                            {
                                $PluginInstance->post($MessageData['subject'], $MessageData['message']);
                            }
                        }
                        catch (PDOException $Exception)
                        {
                            Out::getInstance()->pushError($Exception->getMessage());
                        }
                    }
                    
                    // Call plugins
                    
                    PluginRegistry::ForEachPlugin(function($PluginInstance) use ($RaidId)
                    {
                        $PluginInstance->onRaidCreate($RaidId); 
                    });
    
                    // Increment start/end
    
                    switch ($aRequest['stride'])
                    {
                    case 'day':
                        ++$StartDay;
                        ++$EndDay;
                        break;
    
                    case 'week':
                        $StartDay += 7;
                        $EndDay += 7;
                        break;
    
                    case 'month':
                        ++$StartMonth;
                        ++$EndMonth;
                        break;
    
                    default;
                    case 'once':
                        $rc = $Repeat; // Force done
                        break;
                    }
                }
    
                // reload calendar
                
                $Session = Session::get();
    
                $ShowMonth = ( isset($Session['Calendar']) && isset($Session['Calendar']['month']) ) ? $Session['Calendar']['month'] : $aRequest['month'];
                $ShowYear  = ( isset($Session['Calendar']) && isset($Session['Calendar']['year']) )  ? $Session['Calendar']['year']  : $aRequest['year'];
    
                msgQueryCalendar( prepareCalRequest( $ShowMonth, $ShowYear ) );
            }
        }
        else
        {
            $Out = Out::getInstance();
            $Out->pushError(L('AccessDenied'));
        }
    }

?>