<?php

function msgProfileupdate( $aRequest )
{
    if ( validUser() )
    {
        $UserId = UserProxy::getInstance()->UserId;

        if ( validAdmin() && isset( $aRequest["id"] ) )
        {
            $UserId = intval( $aRequest["id"] );
        }

        $Connector = Connector::getInstance();

        $Characters = $Connector->prepare("Select * FROM `".RP_TABLE_PREFIX."Character` WHERE UserId = :UserId ORDER BY Name");

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

            $NumCharacters = is_array( $aRequest["charId"] ) ? sizeof($aRequest["charId"]) : 0;

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
        echo "<error>".L("AccessDenied")."</error>";
    }
}

?>