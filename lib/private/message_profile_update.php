<?php

function msgProfileUpdate( $Request )
{
    if ( ValidUser() )
    {
        $userId = UserProxy::GetInstance()->UserId;

        if ( ValidAdmin() && isset( $Request["id"] ) )
        {
            $userId = intval( $Request["id"] );
        }

        $Connector = Connector::GetInstance();

        $Characters = $Connector->prepare("Select * FROM `".RP_TABLE_PREFIX."Character` WHERE UserId = :UserId ORDER BY Name");

        $Characters->bindValue(":UserId", $userId, PDO::PARAM_INT);

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

            $NumCharacters = is_array( $Request["charId"] ) ? sizeof($Request["charId"]) : 0;

            // Sanity check mainchar

            $foundMainChar = false;

            for ( $CharIndex=0; $CharIndex < $NumCharacters; ++$CharIndex )
            {
                if ( $Request["mainChar"][$CharIndex] == "true" )
                {
                    if ( $foundMainChar )
                    {
                        $Request["mainChar"][$CharIndex] = "false";
                    }
                    else
                    {
                        $foundMainChar = true;
                    }
                }
            }

            if ( !$foundMainChar && $NumCharacters > 0 )
            {
                $Request["mainChar"][0] = "true";
            }

            // Update/insert chars

            $Connector->beginTransaction();

            for ( $CharIndex=0; $CharIndex < $NumCharacters; ++$CharIndex )
            {
                $CharId = $Request["charId"][$CharIndex];

                if ( $CharId == 0 )
                {
                    // Insert new character

                    $InsertChar = $Connector->prepare(  "INSERT INTO `".RP_TABLE_PREFIX."Character` ".
                                                        "( UserId, Name, Class, Mainchar, Role1, Role2 ) ".
                                                        "VALUES ( :UserId, :Name, :Class, :Mainchar, :Role1, :Role2 )" );

                    $InsertChar->bindValue( ":UserId", $userId, PDO::PARAM_INT );
                    $InsertChar->bindValue( ":Name", requestToXML( $Request["name"][$CharIndex], ENT_COMPAT, "UTF-8" ), PDO::PARAM_STR );
                    $InsertChar->bindValue( ":Class", $Request["charClass"][$CharIndex], PDO::PARAM_STR );
                    $InsertChar->bindValue( ":Mainchar", $Request["mainChar"][$CharIndex], PDO::PARAM_STR );
                    $InsertChar->bindValue( ":Role1", $Request["role1"][$CharIndex], PDO::PARAM_STR );
                    $InsertChar->bindValue( ":Role2", $Request["role2"][$CharIndex], PDO::PARAM_STR );

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

                    $UpdateChar = $Connector->prepare(    "UPDATE `".RP_TABLE_PREFIX."Character` ".
                                                        "SET Mainchar = :Mainchar, Role1 = :Role1, Role2 = :Role2 ".
                                                        "WHERE CharacterId = :CharacterId AND UserId = :UserId" );

                    $UpdateChar->bindValue( ":UserId", $userId, PDO::PARAM_INT );
                    $UpdateChar->bindValue( ":CharacterId", $CharId, PDO::PARAM_INT );
                    $UpdateChar->bindValue( ":Mainchar", $Request["mainChar"][$CharIndex], PDO::PARAM_STR );
                    $UpdateChar->bindValue( ":Role1", $Request["role1"][$CharIndex], PDO::PARAM_STR );
                    $UpdateChar->bindValue( ":Role2", $Request["role2"][$CharIndex], PDO::PARAM_STR );

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
                
                $DropChar->bindValue( ":UserId", $userId, PDO::PARAM_INT );
                $DropChar->bindValue( ":CharacterId", $CharId, PDO::PARAM_INT );
                
                $DropAttendance->bindValue( ":UserId", $userId, PDO::PARAM_INT );
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
            
            UserProxy::GetInstance()->UpdateCharacters();
        }

        msgQueryProfile( $Request );
    }
    else
    {
        echo "<error>".L("AccessDenied")."</error>";
    }
}

?>