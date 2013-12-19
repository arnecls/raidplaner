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
                $HashedPassword = NativeBinding::hash( $aRequest["newPass"], $Salt, "none" );
            
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
        
        $SettingSt = $Connector->prepare("DELETE FROM `".RP_TABLE_PREFIX."UserSetting` WHERE UserId = :UserId AND (Name = 'VacationStart' OR Name = 'VacationEnd')");
        $SettingSt->bindValue(":UserId", $UserId, PDO::PARAM_INT);
        
        if ( !$SettingSt->execute() )
        {
            postErrorMessage( $SettingSt );
        }
        else
        {
            if ($aRequest["vacationStart"] != null)
            {
                $StartSt = $Connector->prepare("INSERT INTO `".RP_TABLE_PREFIX."UserSetting` (UserId, Name, IntValue) VALUES (:UserId, 'VacationStart', :Time)");
                $StartSt->bindValue(":UserId", $UserId, PDO::PARAM_INT);
                $StartSt->bindValue(":Time", $aRequest["vacationStart"], PDO::PARAM_INT);
                
                if ( !$StartSt->execute() )
                {
                    postErrorMessage( $StartSt );
                }
                
                $StartSt->closeCursor();
                
                $EndSt = $Connector->prepare("INSERT INTO `".RP_TABLE_PREFIX."UserSetting` (UserId, Name, IntValue) VALUES (:UserId, 'VacationEnd', :Time)");
                $EndSt->bindValue(":UserId", $UserId, PDO::PARAM_INT);
                $EndSt->bindValue(":Time", $aRequest["vacationEnd"], PDO::PARAM_INT);
                
                if ( !$EndSt->execute() )
                {
                    postErrorMessage( $EndSt );
                }
                
                $EndSt->closeCursor();
            }
        }
        
        $SettingSt->closeCursor();
        
        // Update characters

        $Characters = $Connector->prepare("SELECT * FROM `".RP_TABLE_PREFIX."Character` WHERE UserId = :UserId ORDER BY Name");
        $Characters->bindValue(":UserId", $UserId, PDO::PARAM_INT);

        $ValidCharacterIds = array();
        $UpdatedCharacteIds = array();

        if ( !$Characters->execute() )
        {
            postErrorMessage( $Characters );
            $Characters->closeCursor();
        }
        else
        {
            while ( $Data = $Characters->fetch( PDO::FETCH_ASSOC ) )
            {
                array_push( $ValidCharacterIds, $Data["CharacterId"] );
            }

            $Characters->closeCursor();

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

                if ( $CharId == 0 )
                {
                    // Insert new character

                    $InsertChar = $Connector->prepare(  "INSERT INTO `".RP_TABLE_PREFIX."Character` ".
                                                        "( UserId, Name, Class, Mainchar, Role1, Role2 ) ".
                                                        "VALUES ( :UserId, :Name, :Class, :Mainchar, :Role1, :Role2 )" );

                    $InsertChar->bindValue( ":UserId", $UserId, PDO::PARAM_INT );
                    $InsertChar->bindValue( ":Name", requestToXML( $aRequest["name"][$CharIndex], ENT_COMPAT, "UTF-8" ), PDO::PARAM_STR );
                    $InsertChar->bindValue( ":Class", $aRequest["charClass"][$CharIndex], PDO::PARAM_STR );
                    $InsertChar->bindValue( ":Mainchar", $aRequest["mainChar"][$CharIndex], PDO::PARAM_STR );
                    $InsertChar->bindValue( ":Role1", $aRequest["role1"][$CharIndex], PDO::PARAM_STR );
                    $InsertChar->bindValue( ":Role2", $aRequest["role2"][$CharIndex], PDO::PARAM_STR );

                    if ( !$InsertChar->execute() )
                    {
                        postErrorMessage( $InsertChar );
                        $InsertChar->closeCursor();
                        $Connector->rollBack();
                        return;
                    }

                    $InsertChar->closeCursor();
                }
                else if ( in_array( $CharId, $ValidCharacterIds ) )
                {
                    // Update character

                    array_push( $UpdatedCharacteIds, $CharId );

                    $UpdateChar = $Connector->prepare(  "UPDATE `".RP_TABLE_PREFIX."Character` ".
                                                        "SET Mainchar = :Mainchar, Role1 = :Role1, Role2 = :Role2 ".
                                                        "WHERE CharacterId = :CharacterId AND UserId = :UserId" );

                    $UpdateChar->bindValue( ":UserId", $UserId, PDO::PARAM_INT );
                    $UpdateChar->bindValue( ":CharacterId", $CharId, PDO::PARAM_INT );
                    $UpdateChar->bindValue( ":Mainchar", $aRequest["mainChar"][$CharIndex], PDO::PARAM_STR );
                    $UpdateChar->bindValue( ":Role1", $aRequest["role1"][$CharIndex], PDO::PARAM_STR );
                    $UpdateChar->bindValue( ":Role2", $aRequest["role2"][$CharIndex], PDO::PARAM_STR );

                    if ( !$UpdateChar->execute() )
                    {
                        postErrorMessage( $UpdateChar );
                        $UpdateChar->closeCursor();
                        $Connector->rollBack();
                        return;
                    }

                    $UpdateChar->closeCursor();
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
                    postErrorMessage( $DropChar );
                    $DropChar->closeCursor();
                    $Connector->rollBack();
                    return;
                }
                
                if ( !$DropAttendance->execute() )
                {
                    postErrorMessage( $DropAttendance );
                    $DropAttendance->closeCursor();
                    $Connector->rollBack();
                    return;
                }
                
                $DropChar->closeCursor();
                $DropAttendance->closeCursor();
            }
            
            $Connector->commit();
            
            UserProxy::getInstance()->updateCharacters();
        }
        
        msgQueryProfile( $aRequest );
    }
    else
    {
        $Out = Out::getInstance();
        $Out->pushError(L("AccessDenied"));
    }
}

?>