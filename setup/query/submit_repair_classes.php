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
    $fixCount = sizeof($_REQUEST["ids"]);
    
    for ( $i=0; $i<$fixCount; ++$i )
    {
        $charId = $_REQUEST["ids"][$i];
        $class  = $_REQUEST["classes"][$i];
        
        if ($class == "_delete")
        {
            // Remove character
            
            $DeleteAttendance = $Connector->prepare("DELETE FROM `".RP_TABLE_PREFIX."Character` WHERE CharacterId=:CharId;".
                                                    "DELETE FROM `".RP_TABLE_PREFIX."Attendance` WHERE CharacterId=:CharId;");
            
            $DeleteAttendance->bindValue(":CharId", $charId, PDO::PARAM_INT);
            
            if (!$DeleteAttendance->execute())
            {
                postErrorMessage( $DeleteAttendance );
            }
            
            $DeleteAttendance->closeCursor();
        }
        else
        {
            $defaultRole = $g_RoleToIdx[ $s_Classes[$class][1][0] ];
            
            $UpdateChar = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."Character` SET Class=:Class, Role1=:Role, Role2=:Role WHERE CharacterId=:CharId;".
                                              "UPDATE `".RP_TABLE_PREFIX."Attendance` SET Role=:Role WHERE  CharacterId=:CharId");
                                              
            $UpdateChar->bindValue(":Class", $class, PDO::PARAM_STR);
            $UpdateChar->bindValue(":Role", $defaultRole, PDO::PARAM_INT);
            $UpdateChar->bindValue(":CharId", $charId, PDO::PARAM_INT);
            
            if (!$UpdateChar->execute())
            {
                postHTMLErrorMessage( $UpdateChar );
            }
            
            $UpdateChar->closeCursor();
        }
    }    
?>