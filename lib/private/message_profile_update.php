<?php

function msgProfileupdate( $aRequest )
{
    if ( validUser() )
    {
        $UserId = UserProxy::getInstance()->UserId;

        if ( validAdmin() && isset($aRequest["userId"]) && ($aRequest["userId"]!=0) )
        {
            $UserId = intval( $aRequest["userId"] );
        }

        $Connector = Connector::getInstance();

        // Update password

        if (isset($aRequest["newPass"]) && ($aRequest["oldPass"] != ""))
        {
            if ( UserProxy::getInstance()->validateCredentials($aRequest["oldPass"]) )
            {
                // User authenticated with valid password
                // change the password of the given id. ChangePassword does a check
                // for validity (e.g. only admin may change other user's passwords)

                $Salt = UserProxy::generateKey128();
                $HashedPassword = NativeBinding::nativeHash( $aRequest["newPass"], $Salt, "none" );

                if ( !UserProxy::changePassword($UserId, $HashedPassword, $Salt) )
                {
                    $Out = Out::getInstance();
                    $Out->pushError(L("PasswordLocked"));
                }
            }
            else
            {
                $Out = Out::getInstance();
                $Out->pushError(L("WrongPassword"));
            }
        }

        // Update vacation settings
        
        $VacationQuery = $Connector->prepare("SELECT * FROM `".RP_TABLE_PREFIX."UserSetting` WHERE ".
            "UserId = :UserId AND (Name = 'VacationStart' OR Name = 'VacationEnd' OR Name = 'VacationMessage')");
            
        $VacationQuery->bindValue(":UserId", $UserId, PDO::PARAM_INT);
        
        $VacationData = Array();
        $VacationQuery->loop( function($aData) use (&$VacationData) {
            $VacationData[$aData["Name"]] = $aData;
        });
        
        $StartChanged   = isset($VacationData["VacationStart"]) && ($VacationData["VacationStart"]["IntValue"] != $aRequest["vacationStart"]);        
        $EndChanged     = isset($VacationData["VacationEnd"]) && ($VacationData["VacationEnd"]["IntValue"] != $aRequest["vacationEnd"]);                          
        $MessageChanged = isset($VacationData["VacationMessage"]) && ($VacationData["VacationMessage"]["TextValue"] != $aRequest["vacationMessage"]);
        
        // Remove vacation from all affected raids if times have changed
        
        if ($StartChanged || $EndChanged)
        {
            $RevokeQuery = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."Raid` LEFT JOIN `".RP_TABLE_PREFIX."Attendance` USING (RaidId) ".
                "SET `".RP_TABLE_PREFIX."Attendance`.Status = 'undecided', Comment = '' ".
                "WHERE Start >= FROM_UNIXTIME(:Start) AND Start <= FROM_UNIXTIME(:End) ".
                "AND `".RP_TABLE_PREFIX."Attendance`.Status = 'unavailable' AND `".RP_TABLE_PREFIX."Attendance`.UserId = :UserId");
                
            $RevokeQuery->bindValue(":Start",  max($VacationData["VacationStart"]["IntValue"], time()), PDO::PARAM_INT);
            $RevokeQuery->bindValue(":End",    $VacationData["VacationEnd"]["IntValue"], PDO::PARAM_INT);
            $RevokeQuery->bindValue(":UserId", $UserId, PDO::PARAM_INT);
            
            $RevokeQuery->execute();
        }
        
        $StartChanged   = $StartChanged   || (!isset($VacationData["VacationStart"]) && ($aRequest["vacationStart"] != null));
        $EndChanged     = $EndChanged     || (!isset($VacationData["VacationEnd"]) && ($aRequest["vacationEnd"] != null));
        $MessageChanged = $MessageChanged || (!isset($VacationData["VacationMessage"]) && ($aRequest["vacationMessage"] != null)); 
        
        // Remove vacation settings if no data has been passed
        
        if ($aRequest["vacationStart"] == null)
        {      
            $DropOldQuery = $Connector->prepare("DELETE FROM `".RP_TABLE_PREFIX."UserSetting` WHERE UserId = :UserId AND (Name = 'VacationStart' OR Name = 'VacationEnd' OR Name = 'VacationMessage')");
            
            $DropOldQuery->bindValue(":UserId", $UserId, PDO::PARAM_INT);
            $DropOldQuery->execute();
        }
        else if ($StartChanged || $EndChanged || $MessageChanged)
        {
            // Create or update start date
            
            if ( isset($VacationData["VacationStart"]) )
            {
                $StartQuery = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."UserSetting` SET IntValue = :Time WHERE UserSettingId = :SettingId LIMIT 1");
                $StartQuery->bindValue(":SettingId", $VacationData["VacationStart"]["UserSettingId"], PDO::PARAM_INT);
            }
            else
            {
                $StartQuery = $Connector->prepare("INSERT INTO `".RP_TABLE_PREFIX."UserSetting` (UserId, Name, IntValue) VALUES (:UserId, 'VacationStart', :Time)");
                $StartQuery->bindValue(":UserId", $UserId, PDO::PARAM_INT);
            }
            
            $StartQuery->bindValue(":Time", $aRequest["vacationStart"], PDO::PARAM_INT);
            $StartQuery->execute();
            
            // Create or update end date
            
            if ( isset($VacationData["VacationEnd"]) )
            {
                $EndQuery = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."UserSetting` SET IntValue = :Time WHERE UserSettingId = :SettingId LIMIT 1");
                $EndQuery->bindValue(":SettingId", $VacationData["VacationEnd"]["UserSettingId"], PDO::PARAM_INT);
            }
            else
            {
                $EndQuery = $Connector->prepare("INSERT INTO `".RP_TABLE_PREFIX."UserSetting` (UserId, Name, IntValue) VALUES (:UserId, 'VacationEnd', :Time)");
                $EndQuery->bindValue(":UserId", $UserId, PDO::PARAM_INT);
            }

            $EndQuery->bindValue(":Time", $aRequest["vacationEnd"], PDO::PARAM_INT);
            $EndQuery->execute();

            // Update or create message

            if ( isset($VacationData["VacationMessage"]) )
            {
                $MessageQuery = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."UserSetting` SET TextValue = :Message WHERE UserSettingId = :SettingId LIMIT 1");
                $MessageQuery->bindValue(":SettingId", $VacationData["VacationMessage"]["UserSettingId"], PDO::PARAM_INT);
            }
            else
            {
                $MessageQuery = $Connector->prepare("INSERT INTO `".RP_TABLE_PREFIX."UserSetting` (UserId, Name, TextValue) VALUES (:UserId, 'VacationMessage', :Message)");
                $MessageQuery->bindValue(":UserId", $UserId, PDO::PARAM_INT);                
            }

            $VacationMessage = ($aRequest["vacationMessage"] == null) ? "" : $aRequest["vacationMessage"];
            
            $MessageQuery->bindValue(":Message", $VacationMessage, PDO::PARAM_STR);
            $MessageQuery->execute();
            
            // Update all raids in that time to "undecided"

            $RaidsQuery = $Connector->prepare("SELECT RaidId, AttendanceId FROM `".RP_TABLE_PREFIX."Raid` ".
                "LEFT JOIN `".RP_TABLE_PREFIX."Attendance` USING(RaidId) ".
                "WHERE Start >= FROM_UNIXTIME(:Start) AND Start <= FROM_UNIXTIME(:End) ".
                "AND (UserId = :UserId OR UserId IS NULL)");

            $RaidsQuery->bindValue(":Start", $aRequest["vacationStart"], PDO::PARAM_INT);
            $RaidsQuery->bindValue(":End", $aRequest["vacationEnd"], PDO::PARAM_INT);
            $RaidsQuery->bindValue(":UserId", $UserId, PDO::PARAM_INT);
            
            $RaidsQuery->loop(function($RaidData) use (&$Connector, $UserId, $VacationMessage)
            {
                if ($RaidData["AttendanceId"] != null)
                {
                    $AbsentQuery = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."Attendance` ".
                        "SET Comment = :Message".(($StartChanged || $EndChanged) ? ", Status='unavailable' " : " ").
                        "WHERE AttendanceId = :AttendanceId LIMIT 1");
                    
                    $AbsentQuery->bindValue(":AttendanceId", $RaidData["AttendanceId"], PDO::PARAM_INT);
                }
                else
                {
                    $AbsentQuery = $Connector->prepare("INSERT INTO `".RP_TABLE_PREFIX."Attendance` (UserId, RaidId, Status, Comment) ".
                        "VALUES (:UserId, :RaidId, 'unavailable', :Message)");
                    
                    $AbsentQuery->bindValue(":UserId", $UserId, PDO::PARAM_INT);
                    $AbsentQuery->bindValue(":RaidId", $RaidData["RaidId"], PDO::PARAM_INT);
                }
                
                $AbsentQuery->bindValue(":Message", $VacationMessage, PDO::PARAM_STR);
                $AbsentQuery->execute();
            });
        }

        // Update characters

        $CharacterQuery = $Connector->prepare("SELECT * FROM `".RP_TABLE_PREFIX."Character` WHERE UserId = :UserId ORDER BY Name");
        $CharacterQuery->bindValue(":UserId", $UserId, PDO::PARAM_INT);

        $ValidCharacterIds = array();
        $UpdatedCharacteIds = array();

        $CharacterQuery->loop( function($Data) use (&$ValidCharacterIds)
        {
            array_push( $ValidCharacterIds, $Data["CharacterId"] );
        });

        $NumCharacters = (isset($aRequest["charId"]) && is_array($aRequest["charId"])) ? sizeof($aRequest["charId"]) : 0;

        // Sanity check mainchar

        $FoundMainChar = false;

        for ( $CharIndex=0; $CharIndex < $NumCharacters; ++$CharIndex )
        {
            if ( $aRequest["mainChar"][$CharIndex] == "true" )
            {
                if ( $FoundMainChar )
                {
                    $aRequest["mainChar"][$CharIndex] = "false";
                }
                else
                {
                    $FoundMainChar = true;
                }
            }
        }

        if ( !$FoundMainChar && $NumCharacters > 0 )
        {
            $aRequest["mainChar"][0] = "true";
        }

        // Update/insert chars

        $Connector->beginTransaction();

        for ( $CharIndex=0; $CharIndex < $NumCharacters; ++$CharIndex )
        {
            $CharId = $aRequest["charId"][$CharIndex];
            
            $ClassArray = $aRequest["charClass"][$CharIndex];
            $Classes = (sizeof($ClassArray) == 1) ? $ClassArray[0] : implode(":", $ClassArray);

            if ( $CharId == 0 )
            {
                // Insert new character

                $InsertChar = $Connector->prepare(  "INSERT INTO `".RP_TABLE_PREFIX."Character` ".
                                                    "( UserId, Name, Class, Mainchar, Role1, Role2 ) ".
                                                    "VALUES ( :UserId, :Name, :Class, :Mainchar, :Role1, :Role2 )" );
                                                    
                $InsertChar->bindValue( ":UserId", $UserId, PDO::PARAM_INT );
                $InsertChar->bindValue( ":Name", requestToXML( $aRequest["name"][$CharIndex], ENT_COMPAT, "UTF-8" ), PDO::PARAM_STR );
                $InsertChar->bindValue( ":Class", $Classes, PDO::PARAM_STR );
                $InsertChar->bindValue( ":Mainchar", $aRequest["mainChar"][$CharIndex], PDO::PARAM_STR );
                $InsertChar->bindValue( ":Role1", $aRequest["role1"][$CharIndex], PDO::PARAM_STR );
                $InsertChar->bindValue( ":Role2", $aRequest["role2"][$CharIndex], PDO::PARAM_STR );

                if ( !$InsertChar->execute() )
                {
                    $Connector->rollBack();
                    return;
                }
            }
            else if ( in_array( $CharId, $ValidCharacterIds ) )
            {
                // Update character

                array_push( $UpdatedCharacteIds, $CharId );

                $UpdateChar = $Connector->prepare(  "UPDATE `".RP_TABLE_PREFIX."Character` ".
                                                    "SET Class = :Class, Mainchar = :Mainchar, Role1 = :Role1, Role2 = :Role2 ".
                                                    "WHERE CharacterId = :CharacterId AND UserId = :UserId" );

                $UpdateChar->bindValue( ":UserId", $UserId, PDO::PARAM_INT );
                $UpdateChar->bindValue( ":CharacterId", $CharId, PDO::PARAM_INT );
                $UpdateChar->bindValue( ":Class", $Classes, PDO::PARAM_STR );
                $UpdateChar->bindValue( ":Mainchar", $aRequest["mainChar"][$CharIndex], PDO::PARAM_STR );
                $UpdateChar->bindValue( ":Role1", $aRequest["role1"][$CharIndex], PDO::PARAM_STR );
                $UpdateChar->bindValue( ":Role2", $aRequest["role2"][$CharIndex], PDO::PARAM_STR );

                if ( !$UpdateChar->execute() )
                {
                    $Connector->rollBack();
                    return;
                }
            }
        }

        $IdsToRemove = array_diff( $ValidCharacterIds, $UpdatedCharacteIds );

        foreach( $IdsToRemove as $CharId )
        {
           // Remove character

            $DropChar = $Connector->prepare("DELETE FROM `".RP_TABLE_PREFIX."Character` ".
                                            "WHERE CharacterId = :CharacterId AND UserId = :UserId" );

            $DropAttendance = $Connector->prepare("DELETE FROM `".RP_TABLE_PREFIX."Attendance` ".
                                                  "WHERE CharacterId = :CharacterId AND UserId = :UserId" );

            $DropChar->bindValue( ":UserId", $UserId, PDO::PARAM_INT );
            $DropChar->bindValue( ":CharacterId", $CharId, PDO::PARAM_INT );

            $DropAttendance->bindValue( ":UserId", $UserId, PDO::PARAM_INT );
            $DropAttendance->bindValue( ":CharacterId", $CharId, PDO::PARAM_INT );

            if ( !$DropChar->execute() )
            {
                $Connector->rollBack();
                return;
            }

            if ( !$DropAttendance->execute() )
            {
                $Connector->rollBack();
                return;
            }
        }

        $Connector->commit();

        UserProxy::getInstance()->updateCharacters();
        msgQueryProfile( $aRequest );
    }
    else
    {
        $Out = Out::getInstance();
        $Out->pushError(L("AccessDenied"));
    }
}

?>