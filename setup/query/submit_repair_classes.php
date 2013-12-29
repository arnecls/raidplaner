<?php
    require_once(dirname(__FILE__)."/../../lib/private/connector.class.php");
    require_once(dirname(__FILE__)."/../../lib/config/config.php");
    require_once(dirname(__FILE__)."/../../lib/private/config/game.php");

    // globals

    $RoleKeys = array_keys($gRoles);
    $g_RoleToIdx = array();
    $RoleIdx = 0;

    foreach($RoleKeys as $Key)
    {
        $g_RoleToIdx[$Key] = $RoleIdx++;
    }

    $Connector = Connector::getInstance();
    $FixCount = sizeof($_REQUEST["ids"]);

    for ( $i=0; $i<$FixCount; ++$i )
    {
        $CharId = $_REQUEST["ids"][$i];
        $Class  = $_REQUEST["classes"][$i];

        if ($Class == "_delete")
        {
            // Remove character

            $DeleteAttendance = $Connector->prepare("DELETE FROM `".RP_TABLE_PREFIX."Character` WHERE CharacterId=:CharId;".
                                                    "DELETE FROM `".RP_TABLE_PREFIX."Attendance` WHERE CharacterId=:CharId;");

            $DeleteAttendance->bindValue(":CharId", $CharId, PDO::PARAM_INT);
            $DeleteAttendance->setErrorsAsHTML(true);

            $DeleteAttendance->execute();
        }
        else
        {
            $DefaultRole = $g_RoleToIdx[ $gClasses[$Class][2][0] ];

            $UpdateChar = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."Character` SET Class=:Class, Role1=:Role, Role2=:Role WHERE CharacterId=:CharId;".
                                              "UPDATE `".RP_TABLE_PREFIX."Attendance` SET Role=:Role WHERE  CharacterId=:CharId");

            $UpdateChar->bindValue(":Class", $Class, PDO::PARAM_STR);
            $UpdateChar->bindValue(":Role", $DefaultRole, PDO::PARAM_INT);
            $UpdateChar->bindValue(":CharId", $CharId, PDO::PARAM_INT);
            $DeleteAttendance->setErrorsAsHTML(true);

            $UpdateChar->execute();
        }
    }

?>