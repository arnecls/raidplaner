<?php
    require_once(dirname(__FILE__)."/../../lib/private/connector.class.php");
    require_once(dirname(__FILE__)."/../../lib/config/config.php");
    require_once(dirname(__FILE__)."/../../lib/private/gameconfig.php");
    
    // globals

    $roleKeys = array_keys($s_Roles);
    $g_RoleToIdx = array();
    $roleIdx = 0;
    
    foreach($roleKeys as $key)
    {
        $g_RoleToIdx[$key] = $roleIdx++;
    }
    
    $Connector = Connector::GetInstance();
    
    // -------------------------------------------------------------------------
    //  Invalid characters
    // -------------------------------------------------------------------------
    
    echo "<div class=\"update_version\">".L("InvalidCharacters");
    
    $charStatement = $Connector->prepare("SELECT CharacterId, Name, Class, Role1, Role2 FROM `".RP_TABLE_PREFIX."Character`");
    $fixCount = 0;
    $resolveCount = 0;
        
    if (!$charStatement->execute())
    {
        postHTMLErrorMessage( $charStatement );
    }
    else
    {
        $invalidCharacters = array();
        
        while ( $Character = $charStatement->fetch(PDO::FETCH_ASSOC) )
        {
            $CharId = intval($Character["CharacterId"]);
            $submitNewRole = false;
            
            if ( !array_key_exists($Character["Class"], $s_Classes) )
            {
                array_push( $invalidCharacters, $Character );
            }
            else
            {            
                $mainRoleOk = false;
                $offRoleOk  = false;
                $roles      = $s_Classes[$Character["Class"]][1];
                
                // Check if roles are allowed for the class
                
                foreach ( $roles as $role )
                {
                    $mainRoleOk = $mainRoleOk || ($g_RoleToIdx[$role] == intval($Character["Role1"]));
                    $offRoleOk  = $offRoleOk  || ($g_RoleToIdx[$role] == intval($Character["Role2"]));
                }
                
                // Fix main role if necessary
                
                if ( !$mainRoleOk )
                {
                    $Character["Role1"] = ($offRoleOk) 
                        ? $Character["Role2"]
                        : $g_RoleToIdx[ $roles[0] ];
                }
                
                // Fix off role if necessary
                
                if ( !$offRoleOk )
                {
                    $Character["Role2"] = ($mainRoleOk) 
                        ? $Character["Role1"]
                        : $g_RoleToIdx[ $roles[0] ];
                }
                
                if ( !$mainRoleOk || !$offRoleOk )
                {
                    // Update roles
                
                    $UpdateChar = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."Character` SET Role1=:Role1, Role2=:Role2 WHERE CharacterId=:CharId");
                    $UpdateChar->bindValue(":Role1", $Character["Role1"], PDO::PARAM_INT);
                    $UpdateChar->bindValue(":Role2", $Character["Role2"], PDO::PARAM_INT);
                    $UpdateChar->bindValue(":CharId", $Character["CharacterId"], PDO::PARAM_INT);
                    
                    if (!$UpdateChar->execute())
                    {
                        postHTMLErrorMessage( $UpdateChar );
                    }
                    else
                    {
                        ++$fixCount;
                    }
                    
                    // Delete attendances with invalid roles
                    
                    $UpdateChar->closeCursor();
                    
                    $DeleteAttendance = $Connector->prepare("DELETE FROM `".RP_TABLE_PREFIX."Attendance` WHERE CharacterId=:CharId AND Role!=:Role1 AND Role!=:Role2");
                    $DeleteAttendance->bindValue(":Role1", $Character["Role1"], PDO::PARAM_INT);
                    $DeleteAttendance->bindValue(":Role2", $Character["Role2"], PDO::PARAM_INT);
                    $DeleteAttendance->bindValue(":CharId", $Character["CharacterId"], PDO::PARAM_INT);
                    
                    if (!$DeleteAttendance->execute())
                    {
                        postHTMLErrorMessage( $DeleteAttendance );
                    }
                    
                    $DeleteAttendance->closeCursor();
                }
            }
        }
        
        $resolveCount = sizeof($invalidCharacters);
        
        foreach( $invalidCharacters as $character )
        {
            echo "<div class=\"update_step_warning classResolve\">".$character["Name"];
            echo "<div class=\"resolve_options\">";
            echo "<span class=\"resolve_type\">".$character["Class"]."</span>";
            echo "<select id=\"char".$character["CharacterId"]."\" class=\"change_class\">";
            echo "<option value=\"_delete\">".L("Delete")."</option>";
            
            while(list($class,$info) = each($s_Classes))
            {
                if ( $class != "empty" )
                    echo "<option value=\"".$class."\">".$info[0]."</option>";
            }
            
            reset($s_Classes);
            
            echo "</select>";
            echo "</div>";
            echo "</div>";
        }
    }
    
    if ($resolveCount > 0)
    {
        echo "<div class=\"update_step_warning classResolve\">".$resolveCount." ".L("ItemsToResolve");
        echo "<div class=\"resolve_options\">";
        echo "<button id=\"resolveClasses\" onclick=\"resolveClasses()\">".L("Resolve")."</button>";
        echo "</div>";
        echo "</div>";
    }
    else
    {
        echo "<div class=\"update_step_ok\">".$resolveCount." ".L("ItemsToResolve")."</div>";
    }
    
    if ($fixCount > 0)
        echo "<div class=\"update_step_warning\">".$fixCount." ".L("ItemsRepaired")."</div>";
    else
        echo "<div class=\"update_step_ok\">".$fixCount." ".L("ItemsRepaired")."</div>";
        
    $charStatement->closeCursor();
    echo "</div>";
    
    // -------------------------------------------------------------------------
    //  Invalid characters
    // -------------------------------------------------------------------------
    
    echo "<div class=\"update_version\">".L("InvalidAttendances");
    
    // Delete attended roles that are out of range
    
    $DeleteAttendance = $Connector->prepare("DELETE FROM `".RP_TABLE_PREFIX."Attendance` WHERE Role>:MaxRole");
    $DeleteAttendance->bindValue(":MaxRole", sizeof($s_Roles), PDO::PARAM_INT);
    
    if (!$DeleteAttendance->execute())
    {
        postHTMLErrorMessage( $DeleteAttendance );
    }
    else
    {
        echo "<div class=\"update_step_ok\">".$DeleteAttendance->rowCount()." ".L("ItemsRepaired")." (".L("StrayRoles").")</div>";
    }
    
    $DeleteAttendance->closeCursor();
    
    // Delete stray attends from deleted characters
    
    $DeleteAttendance = $Connector->prepare("DELETE `".RP_TABLE_PREFIX."Attendance`.* FROM `".RP_TABLE_PREFIX."Attendance` ".
                                            "LEFT JOIN `".RP_TABLE_PREFIX."Character` USING(CharacterId) ".
                                            "WHERE `".RP_TABLE_PREFIX."Character`.UserId IS NULL");
    
    if (!$DeleteAttendance->execute())
    {
        postHTMLErrorMessage( $DeleteAttendance );
    }
    else
    {
        echo "<div class=\"update_step_ok\">".$DeleteAttendance->rowCount()." ".L("ItemsRepaired")." (".L("StrayCharacters").")</div>";
    }
    
    $DeleteAttendance->closeCursor();
    
    // Delete stray attends from deleted users
    
    $DeleteAttendance = $Connector->prepare("DELETE `".RP_TABLE_PREFIX."Attendance`.* FROM `".RP_TABLE_PREFIX."Attendance` ".
                                            "LEFT JOIN `".RP_TABLE_PREFIX."User` USING(UserId) ".
                                            "WHERE `".RP_TABLE_PREFIX."User`.UserId IS NULL");
    
    if (!$DeleteAttendance->execute())
    {
        postHTMLErrorMessage( $DeleteAttendance );
    }
    else
    {
        echo "<div class=\"update_step_ok\">".$DeleteAttendance->rowCount()." ".L("ItemsRepaired")." (".L("StrayUsers").")</div>";
    }
    
    $DeleteAttendance->closeCursor();
                    
    echo "</div>";

?>