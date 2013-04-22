<?php
    require_once(dirname(__FILE__)."/../../lib/private/connector.class.php");
    require_once(dirname(__FILE__)."/../../lib/config/config.php");
    @include_once(dirname(__FILE__)."/../../lib/config/config.vb3.php");
    
    function doUpgrade( $a_Statement)
    {
        $Connector = Connector::GetInstance();
        $Connector->beginTransaction();
        
        while ( list($name, $query) = each($a_Statement) )
        {
            echo "<div class=\"update_step\">".$name;
            
            $Action = $Connector->prepare( $query );
            if ( !$Action->execute() )
            {
                postHTMLErrorMessage( $Action );
            }
            else
            {
                echo "<div class=\"update_step_ok\">OK</div>";
            }
            
            echo "</div>";
        }
        
        $Connector->commit();
    }
    
    // ----------------------------------------------------------------------------
    
    function upgrade_092()
    {
       echo "<div class=\"update_version\">".L("UpdateFrom")." 0.9.2 ".L("UpdateTo")." 0.9.3";
       
       $queries = Array( "External binding" => "ALTER TABLE `".RP_TABLE_PREFIX."User` CHANGE `ExternalBinding` `ExternalBinding` ENUM('none',  'phpbb3',  'eqdkp',  'vb3') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;" );
       
       doUpgrade( $queries );
       echo "</div>";
    }
    
    // ----------------------------------------------------------------------------
    
    function upgrade_093()
    {
        echo "<div class=\"update_version\">".L("UpdateFrom")." 0.9.3 ".L("UpdateTo")." 0.9.4";
       
        $Connector = Connector::GetInstance();
        
        // Check for exisiting unique index
        
        $queries1 = Array();
        
        $IndexStatement = $Connector->prepare( "SHOW INDEXES FROM `".RP_TABLE_PREFIX."Setting` WHERE Key_Name='Unique_Name'" );
        if ( $IndexStatement->execute() )
        {
            if ( !$IndexStatement->fetch(PDO::FETCH_ASSOC) )
            {
                $queries1["Unique setting names"] = "ALTER TABLE  `".RP_TABLE_PREFIX."Setting` ADD CONSTRAINT `Unique_Name` UNIQUE (`Name`);";
            }
        }
        
        $IndexStatement->closeCursor();
        
        // Static updates
        
        $queries2 = Array( "Creation date field"  => "ALTER TABLE `".RP_TABLE_PREFIX."User` ADD  `Created` DATETIME NOT NULL;",
                           "User creation date"   => "UPDATE `".RP_TABLE_PREFIX."User` SET Created = NOW();",
                           "Raid start hour"      => "INSERT INTO `".RP_TABLE_PREFIX."Setting` (`SettingId`, `Name`, `IntValue`, `TextValue`) VALUES (NULL, 'RaidStartHour', '19', '');",
                           "Raid start minute"    => "INSERT INTO `".RP_TABLE_PREFIX."Setting` (`SettingId`, `Name`, `IntValue`, `TextValue`) VALUES (NULL, 'RaidStartMinute', '30', '');",
                           "Raid end hour"        => "INSERT INTO `".RP_TABLE_PREFIX."Setting` (`SettingId`, `Name`, `IntValue`, `TextValue`) VALUES (NULL, 'RaidEndHour', '23', '');",
                           "Raid end minute"      => "INSERT INTO `".RP_TABLE_PREFIX."Setting` (`SettingId`, `Name`, `IntValue`, `TextValue`) VALUES (NULL, 'RaidEndMinute', '0', '');",
                           "Raid size"            => "INSERT INTO `".RP_TABLE_PREFIX."Setting` (`SettingId`, `Name`, `IntValue`, `TextValue`) VALUES (NULL, 'RaidSize', '10', '');",
                           "Site"                 => "INSERT INTO `".RP_TABLE_PREFIX."Setting` (`SettingId`, `Name`, `IntValue`, `TextValue`) VALUES (NULL, 'Site', '0', '');",
                           "Banner"               => "INSERT INTO `".RP_TABLE_PREFIX."Setting` (`SettingId`, `Name`, `IntValue`, `TextValue`) VALUES (NULL, 'Banner', '0', 'cata');",
                           "Current version"      => "INSERT INTO `".RP_TABLE_PREFIX."Setting` (`SettingId`, `Name`, `IntValue`, `TextValue`) VALUES (NULL, 'Version', '94', '');" );
        
        doUpgrade( array_merge($queries1, $queries2) );
        
        // Update user creation dates
        
        echo "<div class=\"update_step\">User creation date detection";
            
        $DataStatement = $Connector->prepare( "SELECT `".RP_TABLE_PREFIX."Character`.UserId, `".RP_TABLE_PREFIX."Raid`.Start FROM `".RP_TABLE_PREFIX."Character` ".
                                              "LEFT JOIN `".RP_TABLE_PREFIX."Attendance` USING (CharacterId) ".
                                              "LEFT JOIN `".RP_TABLE_PREFIX."Raid` USING (RaidId) ".
                                              "GROUP BY `".RP_TABLE_PREFIX."Character`.UserId ".
                                              "ORDER BY `".RP_TABLE_PREFIX."Raid`.Start, `".RP_TABLE_PREFIX."Character`.UserId" );
                                               
        if ( $DataStatement->execute() )
        {                          
            $UpdateString = "";
            
            while ( $Data = $DataStatement->fetch( PDO::FETCH_ASSOC ) )
            {
                $UpdateString .= "UPDATE `".RP_TABLE_PREFIX."User` SET Created='".$Data["Start"]."' WHERE UserId=".intval($Data["UserId"])." LIMIT 1;";
            }
            
            $Connector->beginTransaction();
            $Action = $Connector->prepare( $UpdateString );
            
            if ( !$Action->execute() )
            {
                postHTMLErrorMessage( $Action );
                $Connector->rollback();
            }
            else
            {
                echo "<div class=\"update_step_ok\">OK</div>";
                $Connector->commit();
            }
        }
        
        $DataStatement->closeCursor();
                          
        echo "</div>";
        echo "</div>";
    }
    
    // ----------------------------------------------------------------------------
    
    function upgrade_094()
    {
        echo "<div class=\"update_version\">".L("UpdateFrom")." 0.9.4 ".L("UpdateTo")." 0.9.5";
        
        $updates = Array( "Timestamp setting"      => "INSERT INTO `".RP_TABLE_PREFIX."Setting` (`Name`, `IntValue`, `TextValue`) VALUES('TimeFormat', 24, '');",
                          "Remove banner setting"  => "DELETE FROM `".RP_TABLE_PREFIX."Setting` WHERE Name = 'Banner' LIMIT 1;",
                          "Theme setting"          => "INSERT INTO `".RP_TABLE_PREFIX."Setting` (`Name`, `IntValue`, `TextValue`) VALUES('Theme', '', 'cataclysm');",
                          "Configurable classes"   => "ALTER TABLE  `".RP_TABLE_PREFIX."Character` CHANGE `Class` `Class` CHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;");
        
        doUpgrade( $updates );
        
        echo "</div>";
    }
    
    // ----------------------------------------------------------------------------
    
    function upgrade_095()
    {
        echo "<div class=\"update_version\">".L("UpdateFrom")." 0.9.5 ".L("UpdateTo")." 0.9.6";
        
        $updates = Array( "Primary key attendance"   => "ALTER TABLE  `".RP_TABLE_PREFIX."Attendance` ADD  `AttendanceId` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;",
                          "Rename role fields"       => "ALTER TABLE `".RP_TABLE_PREFIX."Raid` CHANGE  `TankSlots` `SlotsRole1` TINYINT(2) UNSIGNED NOT NULL,".
                                                        "CHANGE `HealSlots` `SlotsRole2` TINYINT(2) UNSIGNED NOT NULL,".
                                                        "CHANGE `DmgSlots` `SlotsRole3` TINYINT(2) UNSIGNED NOT NULL;",
                          "New role fields"          => "ALTER TABLE `".RP_TABLE_PREFIX."Raid` ADD `SlotsRole4` TINYINT(2) UNSIGNED NOT NULL,".
                                                        "ADD `SlotsRole5` TINYINT(2) UNSIGNED NOT NULL;",
                          "Char roles are numbers"   => "ALTER TABLE `".RP_TABLE_PREFIX."Character` CHANGE `Role1` `Role1` TINYINT(1) UNSIGNED NOT NULL,".
                                                        "CHANGE `Role2` `Role2` TINYINT(1) UNSIGNED NOT NULL",
                          "Attend roles are numbers" => "ALTER TABLE  `".RP_TABLE_PREFIX."Attendance` CHANGE `Role` `Role` TINYINT(1) UNSIGNED NOT NULL",
                          "Char role fields"         => "UPDATE `".RP_TABLE_PREFIX."Character` SET Role1=0 WHERE Role1=3; UPDATE `".RP_TABLE_PREFIX."Character` SET Role2=0 WHERE Role2=3;". // Tank 3 -> 0
                                                        "UPDATE `".RP_TABLE_PREFIX."Character` SET Role1=3 WHERE Role1=1; UPDATE `".RP_TABLE_PREFIX."Character` SET Role2=3 WHERE Role2=1;". // Dmg  1 -> 3
                                                        "UPDATE `".RP_TABLE_PREFIX."Character` SET Role1=1 WHERE Role1=2; UPDATE `".RP_TABLE_PREFIX."Character` SET Role2=1 WHERE Role2=2;". // Heal 2 -> 1
                                                        "UPDATE `".RP_TABLE_PREFIX."Character` SET Role1=2 WHERE Role1=3; UPDATE `".RP_TABLE_PREFIX."Character` SET Role2=2 WHERE Role2=3;", // Dmg  3 -> 2
                          "Attendance role fields"   => "UPDATE `".RP_TABLE_PREFIX."Attendance` SET Role=0 WHERE Role=3;". // Tank 3 -> 0
                                                        "UPDATE `".RP_TABLE_PREFIX."Attendance` SET Role=3 WHERE Role=1;". // Dmg  1 -> 3
                                                        "UPDATE `".RP_TABLE_PREFIX."Attendance` SET Role=1 WHERE Role=2;". // Heal 2 -> 1
                                                        "UPDATE `".RP_TABLE_PREFIX."Attendance` SET Role=2 WHERE Role=3;", // Dmg  3 -> 2
                          "unsigned raid size"       => "ALTER TABLE `".RP_TABLE_PREFIX."Raid` CHANGE `Size` `Size` TINYINT(2) UNSIGNED NOT NULL;",
                          "raid modes"               => "ALTER TABLE `".RP_TABLE_PREFIX."Raid` ADD `Mode` ENUM('manual', 'attend', 'all') NOT NULL AFTER `End`;",                          
                          "raid mode setting"        => "INSERT INTO `".RP_TABLE_PREFIX."Setting` (`Name`, `IntValue`, `TextValue`) VALUES('RaidMode', '0', 'manual');" );                          
                          
        doUpgrade( $updates );
        
        echo "</div>";
    }
    
    // ----------------------------------------------------------------------------
    
    function upgrade_096()
    {
        echo "<div class=\"update_version\">".L("UpdateFrom")." 0.9.6 ".L("UpdateTo")." 0.9.7";
        
        $updates = Array( "Undecided comments"               => "ALTER TABLE `".RP_TABLE_PREFIX."Attendance` CHANGE `Status` `Status` ENUM('ok', 'available', 'unavailable', 'undecided') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;",
                          "New bindings"                     => "ALTER TABLE `".RP_TABLE_PREFIX."User` CHANGE `ExternalBinding` `ExternalBinding` ENUM('none', 'phpbb3', 'eqdkp', 'vb3', 'mybb', 'smf') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;",
                          "Support for long EQDKP passwords" => "ALTER TABLE `".RP_TABLE_PREFIX."User` CHANGE `Password` `Password` CHAR(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;",
                          "HMAC support fields"              => "ALTER TABLE `".RP_TABLE_PREFIX."User` CHANGE `Hash` `Salt` CHAR(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;".
                                                                "ALTER TABLE `".RP_TABLE_PREFIX."User` ADD `OneTimeKey` CHAR(32) NOT NULL AFTER `Salt`, ADD `SessionKey` CHAR(32) NOT NULL AFTER `OneTimeKey`;".
                                                                "ALTER TABLE `".RP_TABLE_PREFIX."User` ADD `BindingActive` ENUM('true', 'false') NOT NULL DEFAULT 'true' AFTER `ExternalBinding`;");
        
        doUpgrade( $updates );
		$Connector = Connector::GetInstance();
        
        // vBulletin user hash change
        
        if ( defined("VB3_BINDING") && VB3_BINDING )
        {
            echo "<div class=\"update_step\">Convert VB Users";
            
            $UserQuery = $Connector->prepare("SELECT UserId, ExternalId FROM `".RP_TABLE_PREFIX."User` WHERE ExternalBinding = 'vb3'");
            
            if ( !$UserQuery->execute() )
            {
                postHTMLErrorMessage( $UserQuery );
            }
            else
            {                
                // Update vbulletin users
                
                $VbConnector = new Connector(SQL_HOST, VB3_DATABASE, VB3_USER, VB3_PASS);
                $VbUserQuery = $VbConnector->prepare("SELECT userid,salt FROM `".VB3_TABLE_PREFIX."user`");
                
                if ( !$VbUserQuery->execute() )
                {
                    postHTMLErrorMessage( $VbUserQuery );
                }
                else
                {
                    $error = false;
                    
                    // Gather all vbulletin users
                    
                    $vbUserSalt = array();
                    while ( $UserData = $VbUserQuery->fetch(PDO::FETCH_ASSOC) )
                    {
                        $vbUserSalt[$UserData["userid"]] = $UserData["salt"];
                    }
                    
                    // Update salt per user
                
                    while ( $UserData = $UserQuery->fetch(PDO::FETCH_ASSOC) )
                    {
                        if ( isset($vbUserSalt[$UserData["ExternalId"]]) )
                        {
                            $UpdateUser = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."User` SET Salt = :Salt WHERE UserId = :UserId LIMIT 1");
                            
                            $UpdateUser->bindValue(":UserId", $UserData["UserId"], PDO::PARAM_INT);
                            $UpdateUser->bindValue(":Salt", $vbUserSalt[$UserData["ExternalId"]], PDO::PARAM_STR);
                            
                            if ( !$UpdateUser->execute() )
                            {
                                $error = true;
                                postHTMLErrorMessage( $UpdateUser );
                            }
                            
                            $UpdateUser->closeCursor();
                        }
                    }
                }
                
                $VbUserQuery->closeCursor();
                
                if (!$error)
                    echo "<div class=\"update_step_ok\">OK</div>";
            }
            
            $UserQuery->closeCursor();
            
            echo "</div>";
        }
        
        echo "<div class=\"update_step\">Rainbowtable fix";
        
        // Update native password hashes
        
        $NativeUserQuery = $Connector->prepare("SELECT UserId, Salt, Password FROM `".RP_TABLE_PREFIX."User` WHERE ExternalBinding=\"none\"");
        
        if ( !$NativeUserQuery->execute() )
        {
            postHTMLErrorMessage( $NativeUserQuery );
        }
        else
        {
            $error = false;
            
            while ( $UserData = $NativeUserQuery->fetch(PDO::FETCH_ASSOC) )
            {
                // Old style passwords are stored as sha1 hash -> 160 bits (20 bytes -> 40 char hex)
                // New style passwords are stored as sha256 hash -> 256 bits (32 bytes -> 64 char hex)
                
                if ( strlen($UserData["Password"]) < 64 )
                {
                    $UpdateUser = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."User` SET Password=:Password, BindingActive='false' ".
                                                      "WHERE UserId= :UserId LIMIT 1");
                                
                    $UpdateUser->bindValue(":UserId", $UserData["UserId"], PDO::PARAM_INT);
                    $UpdateUser->bindValue(":Password", hash("sha256", $UserData["Password"].$UserData["Salt"]), PDO::PARAM_STR);
                    
                    if ( !$UpdateUser->execute() )
                    {
                        $error = true;
                        postHTMLErrorMessage( $UpdateUser );
                    }
                    
                    $UpdateUser->closeCursor();
                }
            }
            
            if (!$error)
                echo "<div class=\"update_step_ok\">OK</div>";                   
        }
        
        $NativeUserQuery->closeCursor();
        
        echo "</div>";        
        echo "</div>";
    }
    
    // ----------------------------------------------------------------------------
    
    function upgrade_097()
    {
        echo "<div class=\"update_version\">".L("UpdateFrom")." 0.9.7 ".L("UpdateTo")." 0.9.8";
        
        $updates = Array( "New bindings" => "ALTER TABLE `".RP_TABLE_PREFIX."User` CHANGE `ExternalBinding` `ExternalBinding` CHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;" );                          
                          
        doUpgrade( $updates );
        
        echo "</div>";
    }
    
    // ----------------------------------------------------------------------------
    
    function setVersion( $a_Version )
    {
        $Connector = Connector::GetInstance();
        $Connector->exec( "UPDATE `".RP_TABLE_PREFIX."Setting` SET IntValue=".intval($a_Version)." WHERE Name='Version';" );
    }
    
    // ----------------------------------------------------------------------------
    
    if ( isset($_REQUEST["version"]) )
    {
        switch ( $_REQUEST["version"] )
        {
        case 92:
            upgrade_092();
        case 93:
            upgrade_093();
        case 94:
            upgrade_094();
        case 95:
            upgrade_095();
        case 96:
            upgrade_096();
        case 97:
            upgrade_097();
        default:
            setVersion(98);
            break;
        }
    }
?>