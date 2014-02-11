<?php
    require_once(dirname(__FILE__)."/../../lib/private/connector.class.php");
    require_once(dirname(__FILE__)."/../../lib/config/config.php");
    require_once(dirname(__FILE__)."/tools_install.php");
    
    // -------------------------------------------------------------------------
       
    function ValidateTableLayout()
    {
        global $gDatabaseLayout;
        
        // Get all tables that should exist
        
        $TablesRemain = Array();
        
        foreach($gDatabaseLayout as $Name => $Rows)
        {
            array_push($TablesRemain, RP_TABLE_PREFIX.$Name);
        }
        
        // Loop all tables
        
        $Connector = Connector::getInstance();        
        $Tables = $Connector->prepare("SHOW TABLES");
        $Tables->setErrorsAsHTML(true);
        
        $Tables->loop(function($aTable) use ($Connector, &$TablesRemain, &$gDatabaseLayout)
        {
            $FullName = $aTable["Tables_in_".RP_DATABASE];
            $Index = array_search($FullName, $TablesRemain);
            
            if ($Index !== false)
            {
                array_splice($TablesRemain, $Index, 1);
                
                $BaseName = substr($FullName, strlen(RP_TABLE_PREFIX));
                $TableLayout = $gDatabaseLayout[$BaseName];
                
                // Get all columns that should exist
                
                $ColumnsRemain = Array();
                foreach($TableLayout as $ColumnLayout)
                {
                    if (is_a($ColumnLayout, "Column"))
                        $ColumnsRemain[$ColumnLayout->Name] = $ColumnLayout; 
                }
                
                // Loop all columns
                
                $Columns = $Connector->prepare("SHOW COLUMNS FROM `".$FullName."`");
                
                $Columns->setErrorsAsHTML(true);
                $Columns->loop( function($aColumn) use ($Connector, $FullName, &$ColumnsRemain, &$TableLayout)
                {
                    $ColumnName = $aColumn["Field"];
                    
                    // Try to find current column
                    
                    $ColumnLayout = null;
                    foreach($TableLayout as $Layout)
                    {
                        if ($Layout->Name == $ColumnName)
                        {
                            $ColumnLayout = $Layout;
                            break;
                        }
                    }                    
                    
                    if ($ColumnLayout != null)
                    {
                        unset( $ColumnsRemain[$ColumnName] );
                        
                        if (!$ColumnLayout->HasType($aColumn["Type"]) ||
                            !$ColumnLayout->IsNull($aColumn["Null"] != "NO") ||
                            !$ColumnLayout->HasDefault($aColumn["Default"]) ||
                            !$ColumnLayout->HasExtra($aColumn["Extra"]))
                        {
                            // Modify column
                            
                            echo "<div class=\"update_step_warning\">".L("Fixing")." ".$FullName.".".$ColumnName."</div>";
                            
                            $Alter = $Connector->prepare($ColumnLayout->AlterText($FullName));
                            
                            $Alter->setErrorsAsHTML(true);                        
                            $Alter->execute();
                        }
                    }
                    else
                    {
                        // Drop column
                        
                        /*echo "<div class=\"update_step_warning\">".L("Removing")." ".$FullName.".".$ColumnName."</div>";
                        
                        $Drop = $Connector->prepare("ALTER TABLE `".$FullName."` DROP `".$aColumn["Field"]."`");
                        
                        $Drop->setErrorsAsHTML(true);                       
                        $Drop->execute();*/
                    }
                });
                
                // Add missing columns
                
                foreach($ColumnsRemain as $ColumnLayout)
                {
                    echo "<div class=\"update_step_warning\">".L("Fixing")." ".$FullName.".".$ColumnLayout->Name."</div>";
                    
                    $Add = $Connector->prepare($ColumnLayout->AddText($FullName));
                    
                    $Add->setErrorsAsHTML(true);
                    $Add->execute();
                }
            }
        });
                
        // Add missing tables
        
        foreach($TablesRemain as $TableName)
        {
            echo "<div class=\"update_step_warning\">".L("Fixing")." ".$TableName."</div>";
            
            $QueryString = "CREATE TABLE IF NOT EXISTS `".$TableName."` (";
            $FirstRow = true;
            
            $BaseName = substr($TableName, strlen(RP_TABLE_PREFIX));
            
            foreach($gDatabaseLayout[$BaseName] as $Row)
            {
                $QueryString .= (($FirstRow) ? "" : ",").$Row->CreateText();
                $FirstRow = false;
            }
            
            $QueryString .= ") ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
            $Create = $Connector->prepare($QueryString);
            
            $Create->setErrorsAsHTML(true);
            $Create->execute();
        }
    }
    
    // -------------------------------------------------------------------------
    
    function FindFittingGame($Games, &$aGameId, &$aClassId, &$aRole1Id, &$aRole2Id)
    {
        $BestDiff  = PHP_INT_MAX;
        $BestMatch = null;
        
        foreach ($Games as $Id => $Game)
        {
            $Diff = levenshtein($Id, $aGameId);
            
            if (!ContainsClass($Game, $aClassId))
            {
                $Diff += ceil(strlen($aGameId) / 2);
            }
            else
            {
                if ( !ContainsRole($Game[$aClassId], $aRole1Id) )
                    ++$Diff;
                    
                if ( !ContainsRole($Game[$aClassId], $aRole2Id) )
                    ++$Diff;
            }
            
            if ($Diff < $BestDiff)
            {
                $BestDiff  = $Diff;
                $BestMatch = $Id;
            }
            
            if ($BestDiff == 0)
                break; // ### break, full match ###
        }
        
        if ($BestMatch == null)
            return false; // ### return, no classes ###
       
        $aGameId = $BestMatch;
        FindFittingClass($Games[$aGameId], $aClassId, $aRole1Id, $aRole2Id, null, null);
        
        return true; 
    }
    
    // -------------------------------------------------------------------------
    
    function FindFittingClass($aClasses, &$aClassId, &$aRole1Id, &$aRole2Id, $aClassName, $aClassStyle)
    {
        $BestDiff  = PHP_INT_MAX;
        $BestMatch = null;
        
        foreach($aClasses as $Class)
        {
            $Diff = 0;
            
            if ($aClassName != null)
            {
                $Diff += levenshtein($Class["name"], $aClassName);
                if ($Diff == 0)
                {
                    $BestMatch = $Class;
                    break; // ### break, match by loca ###
                }
            }
            
            if ($aClassStyle != null)
            {
                if ($Class["style"] != $aClassStyle)
                    $Diff += ceil(strlen($aClassId) / 2);
            }
            
            $Diff += levenshtein($Class["id"], $aClassId);
            
            if (($aRole1Id != "") && !ContainsRole($Class, $aRole1Id))
                ++$Diff;
                
            if (($aRole2Id != "") && !ContainsRole($Class, $aRole2Id))
                ++$Diff;
                
            if ($Diff < $BestDiff)
            {
                $BestDiff  = $Diff;
                $BestMatch = $Class;
            }
            
            if ($BestDiff == 0)
                break; // ### break, full match ###
        }
        
        if ($BestMatch == null)
            return false; // ### return, no classes ###
       
        $aClassId = $BestMatch["id"];
        
        if ($aRole1Id != "")
            FindFittingRole($BestMatch["roles"], $aRole1Id, null, null);
        
        if ($aRole2Id != "")
            FindFittingRole($BestMatch["roles"], $aRole2Id, null, null);
        
        return true; 
    }
    
    // -------------------------------------------------------------------------
    
    function FindFittingRole($aRoles, &$aRoleId, $aRoleName, $aRoleStyle)
    {
        $BestDiff  = PHP_INT_MAX;
        $BestMatch = null;
        
        foreach($aRoles as $Role)
        {
            $Diff = 0;
            
            if ($aRoleName != null)
            {
                $Diff += levenshtein($Role["name"], $aRoleName);
                if ($Diff == 0)
                {
                    $BestMatch = $Role;
                    break; // ### break, match by loca ###
                }
            }
            
            if ($aRoleStyle != null)
            {
                if ($Role["style"] != $aRoleStyle)
                    $Diff += ceil(strlen($aRoleId) / 2);
            }
            
            $Diff += levenshtein($Role["id"], $aRoleId);
                
            if ($Diff < $BestDiff)
            {
                $BestDiff  = $Diff;
                $BestMatch = $Role;
            }
            
            if ($BestDiff == 0)
                break; // ### break, full match ###
        }
        
        if ($BestMatch == null)
            return false; // ### return, no roles ###
        
        $aRoleId = $Role["id"];
        
        return true;
    }
    
    // -------------------------------------------------------------------------
    
    function GenerateClassList($aGameFile, &$aGameId, &$aGameMode, &$aRoles)
    {
        $Game = @new SimpleXMLElement( file_get_contents($aGameFile) );
        
        $aGameId = strval($Game->id);
        $aGameMode = strval($Game->classmode);
        
        $Classes = Array();
        $aRoles  = Array();
        
        foreach($Game->roles->role as $Role)
        {
            $aRoles[strval($Role["id"])] = Array(
                "id"    => strval($Role["id"]),
                "name"  => strval($Role["loca"]),
                "style" => strval($Role["style"])
            );
        }
                            
        foreach($Game->classes->class as $Class)
        {
            $ClassRoles = Array();
            foreach($Class->role as $Role)
            {
                $ClassRoles[strval($Role["id"])] = $aRoles[strval($Role["id"])];
            }
            
            $Classes[strval($Class["id"])] = Array(
                "id"    => strval($Class["id"]),
                "name"  => strval($Class["loca"]), 
                "style" => strval($Class["style"]),
                "roles" => $ClassRoles
            );
        }
        
        return $Classes;
    }
    
    // -------------------------------------------------------------------------
    
    function ContainsRole($Class, $RoleId)
    {
        return isset($Class["roles"][$RoleId]);
    }
    
    // -------------------------------------------------------------------------
    
    function ContainsClass($Game, $ClassId)
    {
        return isset($Game[$ClassId]);
    }
    
    // -------------------------------------------------------------------------
    
    function ValidateCharacters()
    {
        $Connector = Connector::getInstance();
        
        // Characters without a user
        
        $StrayChars = $Connector->prepare("SELECT CharacterId FROM `".RP_TABLE_PREFIX."Character` ".
            "LEFT JOIN `".RP_TABLE_PREFIX."User` USING(UserId) ".
            "WHERE `".RP_TABLE_PREFIX."User`.UserId IS NULL");
        
        $CharacterIds = Array();
        $StrayChars->setErrorsAsHTML(true);
        $StrayChars->loop( function($aRow) use (&$CharacterIds)
        {
            array_push($CharacterIds, intval($aRow["CharacterId"])); 
        });
                
        if (count($CharacterIds) > 0)
        {
            echo "<div class=\"update_step_warning\">".L("Fixing")." ".count($CharacterIds)." ".L("StrayChars")."</div>";
            
            foreach($CharacterIds as $CharId)
            {
                $Drop = $Connector->prepare("DELETE FROM `".RP_TABLE_PREFIX."Character` WHERE CharacterId = :CharId LIMIT 1");
                
                $Drop->setErrorsAsHTML(true);
                $Drop->bindValue(":CharId", intval($CharId), PDO::PARAM_INT);                
                $Drop->execute();
            }            
        }
        
        // Attends without a character or raid
        
        $StrayAttends = $Connector->prepare("SELECT AttendanceId FROM `".RP_TABLE_PREFIX."Attendance` ".
            "LEFT JOIN `".RP_TABLE_PREFIX."Character` USING(CharacterId) ".
            "LEFT JOIN `".RP_TABLE_PREFIX."Raid` USING(RaidId) ".
            "WHERE `".RP_TABLE_PREFIX."Attendance`.UserId != 0 AND `".RP_TABLE_PREFIX."Character`.CharacterId != 0 ".
            "AND (`".RP_TABLE_PREFIX."Character`.CharacterId IS NULL OR `".RP_TABLE_PREFIX."Raid`.RaidId IS NULL)");
        
        $AttendanceIds = Array();
        $StrayAttends->setErrorsAsHTML(true);
        $StrayAttends->loop( function($aRow) use (&$AttendanceIds) {
            array_push($AttendanceIds, intval($aRow["AttendanceId"])); 
        });
                
        if (count($AttendanceIds) > 0)
        {
            echo "<div class=\"update_step_warning\">".L("Fixing")." ".count($AttendanceIds)." ".L("StrayAttends")."</div>";
            
            foreach($AttendanceIds as $AttId)
            {
                $Drop = $Connector->prepare("DELETE FROM `".RP_TABLE_PREFIX."Attendance` WHERE AttendanceId = :AttId LIMIT 1");
                
                $Drop->setErrorsAsHTML(true);
                $Drop->bindValue(":AttId", intval($AttId), PDO::PARAM_INT);                
                $Drop->execute();
            }
        }
        
        // Get Class information per game
        
        $GameDir = dirname(__FILE__)."/../../themes/games";
        $GameFiles = scandir( $GameDir );
        $Games = Array();
        $GameModes = Array();
        
        foreach ( $GameFiles as $GameFileName )
        {
            try
            {
                if (strpos($GameFileName,".xml") > 0)
                {
                    $GameId = "";
                    $GameMode = "";
                    $Roles = Array();
                    $Classes = GenerateClassList($GameDir."/".$GameFileName, $GameId, $GameMode, $Roles);
                    
                    $Games[$GameId] = $Classes;
                    $GameModes[$GameId] = $GameMode;
                }
            }
            catch (Exception $e)
            {
                echo "<div class=\"update_step_error\">Error parsing ".$GameFileName.": ".$e->getMessage()."</div>";
            }
        }
        
        // Fix any invalid roles
        
        $CharactersFixed = 0;
        $CharacterQuery = $Connector->prepare("SELECT CharacterId, Game, Class, Role1, Role2 FROM `".RP_TABLE_PREFIX."Character`");
     
        $CharacterQuery->setErrorsAsHTML(true);
        $CharacterQuery->loop( function($aRow) use ($Connector, &$CharactersFixed, &$Games, &$GameModes) 
        {
            $GameId  = $aRow["Game"];            
            $ClassId = $aRow["Class"];
            $Role1Id = $aRow["Role1"];
            $Role2Id = $aRow["Role2"];
            $RequiresFix = false;
                
            if (!isset($Games[$GameId]))
            {
                FindFittingGame($Games, $GameId, $ClassId, $Role1Id, $Role2Id);
                $RequiresFix = true;
            }
            else
            {
                $Game = $Games[$GameId];
                $MultiClass = $GameModes[$GameId] == "multi";
                
                if ($MultiClass)
                {
                    // In multiclass scenarios, we only need to check the class, but all of them
                    
                    $ClassIds = explode(":", $ClassId);
                    
                    foreach($ClassIds as &$Id)
                    {
                        if (!ContainsClass($Game, $Id))
                        {
                            $NoRole = "";
                            FindFittingClass($Game, $Id, $NoRole, $NoRole, null, null);
                            $RequiresFix = true;
                        }
                    }
                    
                    $ClassId = implode(":",$ClassIds);
                }
                else
                {
                    // In singleclass scenarios we need to check class and roles
                    
                    $ClassIds = explode(":", $ClassId);
                    $ClassId = $ClassIds[0]; // force only one class
                    
                    if (!ContainsClass($Game, $ClassId))
                    {
                        FindFittingClass($Game, $ClassId, $Role1Id, $Role2Id, null, null);
                        $RequiresFix = true;
                    }
                    else
                    {                
                        $Class = $Game[$ClassId];
                        
                        if (!ContainsRole($Class, $Role1Id))
                        {
                            FindFittingRole($Class["roles"], $Role1Id, null, null);
                            $RequiresFix = true;
                        }
                           
                        if (!ContainsRole($Class, $Role2Id))
                        {
                            FindFittingRole($Class["roles"], $Role2Id, null, null);
                            $RequiresFix = true;
                        }
                    }
                }
            }
            
            // Update if necessary
                
            if ($RequiresFix)
            {
                $Fix = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."Character` SET Game=:Game, Class=:Class, Role1=:Role1, Role2=:Role2 WHERE CharacterId=:CharId LIMIT 1");
            
                $Fix->setErrorsAsHTML(true);
                $Fix->bindValue(":CharId", intval($aRow["CharacterId"]), PDO::PARAM_INT);
                $Fix->bindValue(":Game",  $GameId,  PDO::PARAM_STR);
                $Fix->bindValue(":Class", $ClassId, PDO::PARAM_STR);
                $Fix->bindValue(":Role1", $Role1Id, PDO::PARAM_STR);
                $Fix->bindValue(":Role2", $Role2Id, PDO::PARAM_STR);
                
                $Fix->execute();
                ++$CharactersFixed;
            }
        });
        
        if ($CharactersFixed > 0)
        {
            echo "<div class=\"update_step_warning\">".L("Fixing")." ".$CharactersFixed." ".L("InvalidCharacters")."</div>";
        }
    }
    
    // -------------------------------------------------------------------------
    
    function MergeGames($aSourceFile, $aTargetFile)
    {
        if ($aSourceFile == $aTargetFile)
        {
            echo "<div class=\"update_step_warning\">".L("SameGame")."</div>";
            return false; // ### return, same game ###
        }
        
        // Try to load both files
        
        $SourceGameId = "";
        $TargetGameId = "";
        $SourceGameMode = "";
        $TargetGameMode = "";
        $SourceClasses = Array();
        $TargetClasses = Array();
        $SourceRoles = Array();
        $TargetRoles = Array();
            
        try
        {
            $GameDir = dirname(__FILE__)."/../../themes/games";
            
            $SourceClasses = GenerateClassList($GameDir."/".$aSourceFile.".xml", $SourceGameId, $SourceGameMode, $SourceRoles);
            $TargetClasses = GenerateClassList($GameDir."/".$aTargetFile.".xml", $TargetGameId, $TargetGameMode, $TargetRoles);            
        }
        catch (Exception $e)
        {
            echo "<div class=\"update_step_error\">Error parsing files: ".$e->getMessage()."</div>";
            return false; // ### return, invalid gameconfig ###
        }
        
        // Convert all characters
        
        $Connector = Connector::getInstance();
        $Characters = $Connector->prepare("SELECT * FROM `".RP_TABLE_PREFIX."Character` WHERE Game=:SourceGame");
        
        $Characters->setErrorsAsHTML(true);
        $Characters->bindValue(":SourceGame", $SourceGameId, PDO::PARAM_STR);
        
        $NumCharactersFixed = 0;
        
        $Characters->loop(function($aRow) use ($Connector, &$NumCharactersFixed, $SourceGameMode, $SourceGameId, &$SourceClasses, &$SourceRoles, $TargetGameMode, $TargetGameId, &$TargetClasses, &$TargetRoles)
        {
            $ClassIds = explode(":", $aRow["Class"]);
            $ClassId = $ClassIds[0];
            $Role1Id = $aRow["Role1"];
            $Role2Id = $aRow["Role2"];
            
            if ($SourceGameMode == "multi")
            {
                foreach($ClassIds as &$Id)
                {
                    $ClassInfo = $SourceClasses[$Id];
                    $Roles = array_keys($ClassInfo["roles"]);
                    $RoleId = $Roles[0];
                    
                    FindFittingRole($TargetRoles, $RoleId, $SourceRoles[$RoleId]["name"], $SourceRoles[$RoleId]["style"]);
                    FindFittingClass($TargetClasses, $Id, $RoleId, $RoleId, $ClassInfo["name"], $ClassInfo["style"]);              
                }
                
                $ClassId = ($TargetGameMode == "multi") ? implode(":", $ClassIds) : $ClassIds[0];
                $Roles = array_keys($TargetClasses[$ClassId]["roles"]);
                $Role1Id = $Roles[0];
                $Role2Id = $Roles[0];
            }
            else
            {
                $ClassInfo = $SourceClasses[$ClassId];
                
                FindFittingRole($TargetRoles, $Role1Id, $SourceRoles[$Role1Id]["name"], $SourceRoles[$Role1Id]["style"]);
                FindFittingRole($TargetRoles, $Role2Id, $SourceRoles[$Role2Id]["name"], $SourceRoles[$Role2Id]["style"]);
                    
                FindFittingClass($TargetClasses, $ClassId, $Role1Id, $Role2Id, $ClassInfo["name"], $ClassInfo["style"]);
            }
            
            // Set the new values
            
            $CharUpdate = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."Character` SET Class=:Class, Role1=:Role1, Role2=:Role2, Game=:TargetGame WHERE CharacterId=:CharId LIMIT 1");
            
            $CharUpdate->setErrorsAsHTML(true);
            $CharUpdate->bindValue(":CharId", intval($aRow["CharacterId"]), PDO::PARAM_INT);
            $CharUpdate->bindValue(":Class", $ClassId, PDO::PARAM_STR);
            $CharUpdate->bindValue(":Role1", $Role1Id, PDO::PARAM_STR);
            $CharUpdate->bindValue(":Role2", $Role2Id, PDO::PARAM_STR);
            $CharUpdate->bindValue(":TargetGame", $TargetGameId, PDO::PARAM_STR);
            
            if ($CharUpdate->execute())
                ++$NumCharactersFixed;
        });
        
        echo "<div class=\"update_step_warning\">".L("Merged")." ".$NumCharactersFixed." ".L("Characters")."</div>";
        
        // Convert all locations
        
        $Locations = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."Location` SET Game=:TargetGame WHERE Game=:SourceGame");
        
        $Locations->setErrorsAsHTML(true);
        $Locations->bindValue(":SourceGame", $SourceGameId, PDO::PARAM_STR);
        $Locations->bindValue(":TargetGame", $TargetGameId, PDO::PARAM_STR);
        
        if ($Locations->execute())
        {
            echo "<div class=\"update_step_warning\">".L("Merged")." ".$Locations->getAffectedRows()." ".L("Locations")."</div>";
        }
        
        return true;
    }
?>