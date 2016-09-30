<?php

    function getVacationData($aRequest)
    {
        // Fetch existing vacation values

        $Connector = Connector::getInstance();
        $UserId = UserProxy::getInstance()->UserId;

        $VacationQuery = $Connector->prepare('SELECT * FROM `'.RP_TABLE_PREFIX.'UserSetting` WHERE '.
            'UserId = :UserId AND (Name = "VacationStart" OR Name = "VacationEnd") LIMIT 2');

        $VacationQuery->bindValue(':UserId', $UserId, PDO::PARAM_INT);

        $VacationData = Array();
        $VacationQuery->loop( function($aData) use (&$VacationData) {
            $VacationData[$aData['Name']] = $aData;
        });

        // New calculate the changes

        $Ranges = Array(
            'new' => Array(),
            'update' => Array(),
            'revoke' => Array(),
            'SettingsFound' => count($VacationData) > 0
        );

        // No existing vacation?

        $OneDay   = 60; // Start = 0:00, End = 23:59, Start + 1 = End and vice versa
        $NewStart = $aRequest['vacationStart'];
        $NewEnd   = $aRequest['vacationEnd'];

        if (count($VacationData) == 0)
        {
            if (($NewStart == null) || ($NewEnd == null))
                return $Ranges; // ### return, no vacation set ###

            // [new]
            array_push($Ranges['new'], Array($NewStart, $NewEnd));
            return $Ranges; // ### return, new vacation ###
        }

        $OldStart = $VacationData['VacationStart']['IntValue'];
        $OldEnd   = $VacationData['VacationEnd']['IntValue'];

        // Drop entire vacation?

        if (($NewStart == null) || ($NewEnd == null))
        {
            // [old]
            array_push($Ranges['revoke'], Array($OldStart, $OldEnd));
            return $Ranges; // ### return, drop entire vacation ###
        }

        // Resolve vaction ranges

        if ($OldStart < $NewStart)
        {
            if ($OldEnd < $NewStart)
            {
                // [old][new]
                array_push($Ranges['revoke'], Array($OldStart, $OldEnd));
                array_push($Ranges['new'], Array($NewStart, $NewEnd));
            }
            else if ($OldEnd > $NewEnd)
            {
                // [old][old+new][old]
                array_push($Ranges['revoke'], Array($OldStart, $NewStart - $OneDay));
                array_push($Ranges['update'], Array($NewStart, $NewEnd));
                array_push($Ranges['revoke'], Array($NewEnd + $OneDay, $OldEnd));
            }
            else
            {
                array_push($Ranges['revoke'], Array($OldStart, $NewStart - $OneDay));

                if ($OldEnd < $NewEnd)
                {
                    // [old][old+new][new]
                    array_push($Ranges['update'], Array($NewStart, $OldEnd));
                    array_push($Ranges['new'], Array($OldEnd + $OneDay, $NewEnd));
                }
                else
                {
                    // [old][old+new]
                    array_push($Ranges['update'], Array($NewStart, $NewEnd));
                }
            }
        }
        else if ($OldStart > $NewStart)
        {
            if ($OldStart > $NewEnd)
            {
                // [new][old]
                array_push($Ranges['revoke'], Array($OldStart, $OldEnd));
                array_push($Ranges['new'], Array($NewStart, $NewEnd));
            }
            else if ($OldEnd > $NewEnd)
            {
                // [new][old+new][old]
                array_push($Ranges['new'], Array($NewStart, $OldStart - $OneDay));
                array_push($Ranges['update'], Array($OldStart, $NewEnd));
                array_push($Ranges['revoke'], Array($NewEnd + $OneDay, $OldEnd));
            }
            else
            {
                array_push($Ranges['new'], Array($NewStart, $OldStart - $OneDay));

                if ($OldEnd < $NewEnd)
                {
                    // [new][old+new][new]
                    array_push($Ranges['update'], Array($OldStart, $OldEnd));
                    array_push($Ranges['new'], Array($OldEnd + $OneDay, $NewEnd));
                }
                else
                {
                    // [new][old+new]
                    array_push($Ranges['update'], Array($OldStart, $OldEnd));
                }
            }
        }
        else // $OldStart == $NewStart
        {
            if ( $OldEnd < $NewEnd )
            {
                // [old+new][new]
                array_push($Ranges['update'], Array($NewStart, $OldEnd));
                array_push($Ranges['new'], Array($OldEnd + $OneDay, $NewEnd));
            }
            else
            {
                array_push($Ranges['update'], Array($NewStart, $NewEnd)); // [old+new]

                if ( $OldEnd > $NewEnd )
                {
                    // [old+new][old]
                    array_push($Ranges['revoke'], Array($NewEnd + $OneDay, $OldEnd));
                }
            }
        }

        return $Ranges;
    }

    // -----------------------------------------------------------------------------

    function msgProfileupdate( $aRequest )
    {
        if ( validUser() )
        {
            global $gGame;
            loadGameSettings();

            $UserId = UserProxy::getInstance()->UserId;
            $LogData = array();

            if ( validAdmin() && isset($aRequest['userId']) && ($aRequest['userId']!=0) )
            {
                $UserId = intval( $aRequest['userId'] );
            }

            $Connector = Connector::getInstance();

            do
            {
                $Connector->beginTransaction();

                // Update password

                if (isset($aRequest['newPass']) && ($aRequest['oldPass'] != ''))
                {
                    if ( UserProxy::getInstance()->validateCredentials($aRequest['oldPass']) )
                    {
                        // User authenticated with valid password
                        // change the password of the given id. ChangePassword does a check
                        // for validity (e.g. only admin may change other user's passwords)

                        $Salt = UserProxy::generateKey32();
                        $HashedPassword = NativeBinding::nativeHash( $aRequest['newPass'], $Salt, 'none' );

                        if ( !UserProxy::changePassword($UserId, $HashedPassword, $Salt) )
                        {
                            $Out = Out::getInstance();
                            $Out->pushError(L('PasswordLocked'));
                        }
                    }
                    else
                    {
                        $Out = Out::getInstance();
                        $Out->pushError(L('WrongPassword'));
                    }

                    $LogData['password'] = true;
                }

                // Update always log in

                if ($aRequest['autoAttend'] == 'true')
                {
                    $ExistsRequest = $Connector->prepare('SELECT UserSettingId FROM `'.RP_TABLE_PREFIX.'UserSetting` '.
                        'WHERE UserId=:UserId and Name="AutoAttend" LIMIT 1');

                    $ExistsRequest->bindValue(':UserId', $UserId, PDO::PARAM_INT);

                    if ($ExistsRequest->fetchFirst() == null)
                    {
                        $AttendRequest = $Connector->prepare('INSERT INTO `'.RP_TABLE_PREFIX.'UserSetting` (UserId, Name) VALUES (:UserId, "AutoAttend")');

                        $AttendRequest->bindValue(':UserId', $UserId, PDO::PARAM_INT);
                        $AttendRequest->execute();
                    }
                }
                else
                {
                    $RemoveQuery = $Connector->prepare('DELETE FROM `'.RP_TABLE_PREFIX.'UserSetting` WHERE '.
                            'UserId = :UserId AND (Name = "AutoAttend") LIMIT 1');

                    $RemoveQuery->bindValue(':UserId', $UserId, PDO::PARAM_INT);
                    $RemoveQuery->execute();
                }

                $LogData['autoAttend'] = $aRequest['autoAttend'];

                // Update vacation settings

                $Ranges = getVacationData($aRequest);
                $VacationMessage = ($aRequest['vacationMessage'] == null) ? '' : requestToXML( $aRequest['vacationMessage'], ENT_COMPAT, 'UTF-8' );

                // Revoke ranges that have been removed

                foreach ($Ranges['revoke'] as $RevokeRange)
                {
                    $RevokeQuery = $Connector->prepare('UPDATE `'.RP_TABLE_PREFIX.'Raid` LEFT JOIN `'.RP_TABLE_PREFIX.'Attendance` USING (RaidId) '.
                        'SET `'.RP_TABLE_PREFIX.'Attendance`.Status = "undecided", Comment = "" '.
                        'WHERE Start >= FROM_UNIXTIME(:Start) AND Start <= FROM_UNIXTIME(:End) '.
                        'AND `'.RP_TABLE_PREFIX.'Attendance`.Status = "unavailable" AND `'.RP_TABLE_PREFIX.'Attendance`.UserId = :UserId');

                    $RevokeFrom = max($RevokeRange[0], time());
                    $RevokeTo = max($RevokeRange[1], time());

                    $RevokeQuery->bindValue(':Start', $RevokeFrom, PDO::PARAM_INT);
                    $RevokeQuery->bindValue(':End', $RevokeTo, PDO::PARAM_INT);
                    $RevokeQuery->bindValue(':UserId', $UserId, PDO::PARAM_INT);
                    $RevokeQuery->execute();

                    $LogData['vacation']['delete'][] = ['start'=>$RevokeFrom, 'end'=>$RevokeTo];
                }

                // Update already affected ranges

                foreach ($Ranges['update'] as $UpdateRange)
                {
                    $UpdateQuery = $Connector->prepare('UPDATE `'.RP_TABLE_PREFIX.'Raid` LEFT JOIN `'.RP_TABLE_PREFIX.'Attendance` USING(RaidId) '.
                        'SET Comment = :Message '.
                        'WHERE Start >= FROM_UNIXTIME(:Start) AND Start <= FROM_UNIXTIME(:End) '.
                        'AND UserId = :UserId AND Status = "unavailable"');

                    $UpdateQuery->bindValue(':Start', $UpdateRange[0], PDO::PARAM_INT);
                    $UpdateQuery->bindValue(':End', $UpdateRange[1], PDO::PARAM_INT);
                    $UpdateQuery->bindValue(':UserId', $UserId, PDO::PARAM_INT);
                    $UpdateQuery->bindValue(':Message', $VacationMessage, PDO::PARAM_STR);
                    $UpdateQuery->execute();

                    $LogData['vacation']['update'][] = ['start'=>$UpdateRange[0], 'end'=>$UpdateRange[2], 'message'=>$VacationMessage];
                }

                // Update/Insert new ranges

                foreach ($Ranges['new'] as $NewRange)
                {
                    // Update all raids that already have an attendance record

                    $UpdateQuery = $Connector->prepare('UPDATE `'.RP_TABLE_PREFIX.'Raid` LEFT JOIN `'.RP_TABLE_PREFIX.'Attendance` USING(RaidId) '.
                        'SET Status = "unavailable", Comment = :Message '.
                        'WHERE Start >= FROM_UNIXTIME(:Start) AND Start <= FROM_UNIXTIME(:End) '.
                        'AND UserId = :UserId');

                    $UpdateQuery->bindValue(':Start',   $NewRange[0], PDO::PARAM_INT);
                    $UpdateQuery->bindValue(':End',     $NewRange[1], PDO::PARAM_INT);
                    $UpdateQuery->bindValue(':UserId',  intval($UserId),      PDO::PARAM_INT);
                    $UpdateQuery->bindValue(':Message', $VacationMessage,     PDO::PARAM_STR);
                    $UpdateQuery->execute();

                    // Find all reaids the do not have an attendance record

                    $AffectedQuery = $Connector->prepare('SELECT `'.RP_TABLE_PREFIX.'Raid`.RaidId FROM `'.RP_TABLE_PREFIX.'Raid` '.
                        'LEFT JOIN `'.RP_TABLE_PREFIX.'Attendance` ON (`'.RP_TABLE_PREFIX.'Raid`.RaidId = `'.RP_TABLE_PREFIX.'Attendance`.RaidId '.
                            'AND (`'.RP_TABLE_PREFIX.'Attendance`.UserId = :UserId OR `'.RP_TABLE_PREFIX.'Attendance`.UserId IS NULL)) '.
                        'WHERE Start >= FROM_UNIXTIME(:Start) AND Start <= FROM_UNIXTIME(:End) '.
                        'AND UserId IS NULL '.
                        'GROUP BY RaidId');

                    $AffectedQuery->bindValue(':Start',  $NewRange[0], PDO::PARAM_INT);
                    $AffectedQuery->bindValue(':End',    $NewRange[1], PDO::PARAM_INT);
                    $AffectedQuery->bindValue(':UserId', intval($UserId),      PDO::PARAM_INT);

                    $AffectedQuery->loop(function($aRaid) use (&$Connector, $UserId, $VacationMessage )
                    {
                        // Set user to unavailable

                        $InsertQuery = $Connector->prepare('INSERT INTO `'.RP_TABLE_PREFIX.'Attendance` '.
                            '(UserId, RaidId, Status, Comment) '.
                            'VALUES (:UserId, :RaidId, "unavailable", :Message)');

                        $InsertQuery->bindValue(':UserId',  intval($UserId),          PDO::PARAM_INT);
                        $InsertQuery->bindValue(':RaidId',  $aRaid['RaidId'], PDO::PARAM_INT);
                        $InsertQuery->bindValue(':Message', $VacationMessage,         PDO::PARAM_STR);
                        $InsertQuery->execute();

                        $LogData['vacation']['new'][] = ['start'=>$NewRange[0], 'end'=>$NewRange[1], 'message'=>$VacationMessage];
                    });
                }

                // Update user settings

                if ((count($Ranges['new']) == 0) &&
                    (count($Ranges['update']) == 0))
                {
                    if (count($Ranges['revoke']) > 0)
                    {
                        $RemoveQuery = $Connector->prepare('DELETE FROM `'.RP_TABLE_PREFIX.'UserSetting` WHERE '.
                            'UserId = :UserId AND (Name = "VacationStart" OR Name = "VacationEnd" OR Name = "VacationMessage") LIMIT 3');

                        $RemoveQuery->bindValue(':UserId', $UserId, PDO::PARAM_INT);
                        $RemoveQuery->execute();
                    }
                }
                else
                {
                    if ($Ranges['SettingsFound'])
                    {
                        $UpdateQuery = $Connector->prepare(
                            'UPDATE `'.RP_TABLE_PREFIX.'UserSetting` SET IntValue = :Start WHERE UserId = :UserId AND Name = "VacationStart" LIMIT 1;'.
                            'UPDATE `'.RP_TABLE_PREFIX.'UserSetting` SET IntValue = :End WHERE UserId = :UserId AND Name = "VacationEnd" LIMIT 1;'.
                            'UPDATE `'.RP_TABLE_PREFIX.'UserSetting` SET TextValue = :Message WHERE UserId = :UserId AND Name = "VacationMessage" LIMIT 1;');

                        $UpdateQuery->bindValue(':UserId',  $UserId, PDO::PARAM_INT);
                        $UpdateQuery->bindValue(':Start',   $aRequest['vacationStart'], PDO::PARAM_INT);
                        $UpdateQuery->bindValue(':End',     $aRequest['vacationEnd'], PDO::PARAM_INT);
                        $UpdateQuery->bindValue(':Message', $VacationMessage, PDO::PARAM_STR);
                        $UpdateQuery->execute();
                    }
                    else
                    {
                        $InsertQuery = $Connector->prepare(
                            'INSERT INTO `'.RP_TABLE_PREFIX.'UserSetting` (IntValue, UserId, Name) VALUES (:Start, :UserId, "VacationStart");'.
                            'INSERT INTO `'.RP_TABLE_PREFIX.'UserSetting` (IntValue, UserId, Name) VALUES (:End, :UserId, "VacationEnd");'.
                            'INSERT INTO `'.RP_TABLE_PREFIX.'UserSetting` (TextValue, UserId, Name) VALUES (:Message, :UserId, "VacationMessage");');

                        $InsertQuery->bindValue(':UserId',  $UserId, PDO::PARAM_INT);
                        $InsertQuery->bindValue(':Start',   $aRequest['vacationStart'], PDO::PARAM_INT);
                        $InsertQuery->bindValue(':End',     $aRequest['vacationEnd'], PDO::PARAM_INT);
                        $InsertQuery->bindValue(':Message', $VacationMessage, PDO::PARAM_STR);
                        $InsertQuery->execute();
                    }
                }

                // Update characters

                $CharacterQuery = $Connector->prepare('SELECT * FROM `'.RP_TABLE_PREFIX.'Character` WHERE UserId = :UserId AND Game = :Game ORDER BY Name');
                $CharacterQuery->bindValue(':UserId', $UserId, PDO::PARAM_INT);
                $CharacterQuery->bindValue(':Game', $gGame['GameId'], PDO::PARAM_STR);

                $ValidCharacterIds = array();
                $UpdatedCharacteIds = array();

                $CharacterQuery->loop( function($Data) use (&$ValidCharacterIds)
                {
                    array_push( $ValidCharacterIds, $Data['CharacterId'] );
                });

                $NumCharacters = (isset($aRequest['charId']) && is_array($aRequest['charId'])) ? count($aRequest['charId']) : 0;

                // Sanity check mainchar

                $FoundMainChar = false;

                for ( $CharIndex=0; $CharIndex < $NumCharacters; ++$CharIndex )
                {
                    if ( $aRequest['mainChar'][$CharIndex] == 'true' )
                    {
                        if ( $FoundMainChar )
                        {
                            $aRequest['mainChar'][$CharIndex] = 'false';
                        }
                        else
                        {
                            $FoundMainChar = true;
                        }
                    }
                }

                if ( !$FoundMainChar && $NumCharacters > 0 )
                {
                    $aRequest['mainChar'][0] = 'true';
                }

                // Update/insert chars

                for ( $CharIndex=0; $CharIndex < $NumCharacters; ++$CharIndex )
                {
                    $CharId = $aRequest['charId'][$CharIndex];
                    $ClassArray = $aRequest['charClass'][$CharIndex];
                    $Classes = (count($ClassArray) == 1) ? $ClassArray[0] : implode(':', $ClassArray);

                    if ( $CharId == 0 )
                    {
                        // Insert new character

                        $InsertChar = $Connector->prepare( 'INSERT INTO `'.RP_TABLE_PREFIX.'Character` '.
                            '( UserId, Name, Game, Class, Mainchar, Role1, Role2 ) '.
                            'VALUES ( :UserId, :Name, :Game, :Class, :Mainchar, :Role1, :Role2 )' );

                        $CharName = requestToXML($aRequest['name'][$CharIndex], ENT_COMPAT, 'UTF-8');

                        $InsertChar->bindValue( ':UserId', $UserId, PDO::PARAM_INT );
                        $InsertChar->bindValue( ':Name', $CharName, PDO::PARAM_STR );
                        $InsertChar->bindValue( ':Game', $gGame['GameId'], PDO::PARAM_STR );
                        $InsertChar->bindValue( ':Class', $Classes, PDO::PARAM_STR );
                        $InsertChar->bindValue( ':Mainchar', $aRequest['mainChar'][$CharIndex], PDO::PARAM_STR );
                        $InsertChar->bindValue( ':Role1', $aRequest['role1'][$CharIndex], PDO::PARAM_STR );
                        $InsertChar->bindValue( ':Role2', $aRequest['role2'][$CharIndex], PDO::PARAM_STR );

                        if ( !$InsertChar->execute() )
                        {
                            $Connector->rollBack();
                            return;
                        }

                        $CharId = $Connector->lastInsertId();
                        $LogData['character']['new'][] = [
                            'Game'     => $gGame['GameId'],
                            'Name'     => $CharName,
                            'Id'       => $CharId,
                            'Mainchar' => $aRequest['mainChar'][$CharIndex],
                            'Class'    => $Classes,
                            'Role1'    => $aRequest['role1'][$CharIndex],
                            'Role1'    => $aRequest['role2'][$CharIndex]];
                    }
                    else if ( in_array( $CharId, $ValidCharacterIds ) )
                    {
                        // Update character

                        array_push( $UpdatedCharacteIds, $CharId );

                        $UpdateChar = $Connector->prepare( 'UPDATE `'.RP_TABLE_PREFIX.'Character` '.
                            'SET Class = :Class, Mainchar = :Mainchar, Role1 = :Role1, Role2 = :Role2 '.
                            'WHERE CharacterId = :CharacterId AND UserId = :UserId' );

                        $UpdateChar->bindValue( ':UserId', $UserId, PDO::PARAM_INT );
                        $UpdateChar->bindValue( ':CharacterId', $CharId, PDO::PARAM_INT );
                        $UpdateChar->bindValue( ':Class', $Classes, PDO::PARAM_STR );
                        $UpdateChar->bindValue( ':Mainchar', $aRequest['mainChar'][$CharIndex], PDO::PARAM_STR );
                        $UpdateChar->bindValue( ':Role1', $aRequest['role1'][$CharIndex], PDO::PARAM_STR );
                        $UpdateChar->bindValue( ':Role2', $aRequest['role2'][$CharIndex], PDO::PARAM_STR );

                        if ( !$UpdateChar->execute() )
                        {
                            $Connector->rollBack();
                            return;
                        }

                        $CharName = requestToXML($aRequest['name'][$CharIndex], ENT_COMPAT, 'UTF-8');
                        $LogData['character']['update'][] = [
                            'Game'     => $gGame['GameId'],
                            'Name'     => $CharName,
                            'Id'       => $CharId,
                            'Mainchar' => $aRequest['mainChar'][$CharIndex],
                            'Class'    => $Classes,
                            'Role1'    => $aRequest['role1'][$CharIndex],
                            'Role1'    => $aRequest['role2'][$CharIndex]];
                    }
                }

                $IdsToRemove = array_diff( $ValidCharacterIds, $UpdatedCharacteIds );

                foreach( $IdsToRemove as $CharId )
                {
                   // Remove character

                    $DropChar = $Connector->prepare('DELETE FROM `'.RP_TABLE_PREFIX.'Character` '.
                        'WHERE CharacterId = :CharacterId AND UserId = :UserId' );

                    $DropAttendance = $Connector->prepare('DELETE FROM `'.RP_TABLE_PREFIX.'Attendance` '.
                        'WHERE CharacterId = :CharacterId AND UserId = :UserId' );

                    $DropChar->bindValue( ':UserId', $UserId, PDO::PARAM_INT );
                    $DropChar->bindValue( ':CharacterId', $CharId, PDO::PARAM_INT );

                    $DropAttendance->bindValue( ':UserId', $UserId, PDO::PARAM_INT );
                    $DropAttendance->bindValue( ':CharacterId', $CharId, PDO::PARAM_INT );

                    if ( !$DropChar->execute() )
                    {
                        $Connector->rollBack();
                        return;
                    }

                    $LogData['character']['delete'][] = [
                        'Game' => $gGame['GameId'],
                        'Id'   => $CharId];

                    if ( !$DropAttendance->execute() )
                    {
                        $Connector->rollBack();
                        return;
                    }
                }
            }
            while(!$Connector->commit());

            Log::getInstance()->update(LOG_TYPE_USER, $UserId, $LogData);
            UserProxy::getInstance()->updateCharacters();
            msgQueryProfile( $aRequest );
        }
        else
        {
            $Out = Out::getInstance();
            $Out->pushError(L('AccessDenied'));
        }
    }

?>
