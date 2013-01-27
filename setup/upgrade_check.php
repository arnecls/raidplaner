<?php
    define( "LOCALE_SETUP", true );
    require_once(dirname(__FILE__)."/../lib/private/locale.php");
    @include_once(dirname(__FILE__)."/../lib/config/config.php");
    require_once(dirname(__FILE__)."/../lib/private/connector.class.php");
    
    $CurrentVersion = 97;
    $CurrentPatch = $CurrentVersion % 10;
    $CurrentMinor = ($CurrentVersion / 10) % 10;
    $CurrentMajor = ($CurrentVersion / 100) % 10;
                            
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
    <head>
        <title>Raidplaner config</title>
        <meta http-equiv="X-UA-Compatible" content="IE=Edge">
        
        <link rel="stylesheet" type="text/css" href="layout/default.css">
        <script type="text/javascript" src="../lib/script/jquery-1.9.0.min.js"></script>
        <script type="text/javascript" src="script/main.js"></script>
        <script type="text/javascript" src="script/upgrade_check.js.php"></script>
        
        <style type="text/css" media="screen">
            #error {
                width: 90%;
                height: 90%;
                position: absolute;
                top: 5%;
                left: 5%;
                background-color: white;
                border: 1px solid black;
                padding: 6px;
                overflow-y: scroll;
                
                -moz-border-radius: 4px; 
                -webkit-border-radius: 4px; 
                -khtml-border-radius: 4px; 
                border-radius: 4px;
            }
            
            .item {
                border-top: 1px dotted black;
                margin-bottom: 10px;
            }
            
            .version {
                font-size: 0.9em;
                color: #999;
            }
            
            .step {
                font-size: 0.9em;
                color: #999;
            }
        </style>
    </head>
    
    <body>
        <div class="appwindow">
            <div style="background-color: black; color: white; padding: 10px">
                Packedpixel<br/>
                <span style="font-size: 24px">Raidplaner update (1/1)</span>
            </div>
            <div style="padding: 20px">
                <div>
                    <h2><?php echo L("VersionDetection"); ?></h2>
                    <?php echo L("VersionDetectProgress"); ?><br/>
                    <?php echo L("ChooseManually"); ?><br/>
                    <?php echo L("OnlyDBAffected"); ?><br/>
                    <?php echo L("NoChangeNoAction"); ?><br/>
                    <br/><br/>
                    
                    <?php echo L("DatabaseConnection"); ?> : <?php
                        try
                        {
                            $Connector  = Connector::GetInstance(true);
                            $databaseOk = true;
                        }
                        catch (PDOException $Exception)
                        {
                            $databaseOk = false;
                        }
                    
                        if ( $databaseOk )
                        {
                            echo "<span style=\"color: green\">".L("Ok")."</span><br/>";
                            echo L("DetectedVersion").": ";
                            
                            $GetVersion = $Connector->prepare("SELECT IntValue FROM `".RP_TABLE_PREFIX."Setting` WHERE Name='Version' LIMIT 1");
        
                            if ( !$GetVersion->execute() )
                            {
                                postErrorMessage($GetVersion);
                                $Version = 93;
                            }
                            else
                            {
                                if ( $Data = $GetVersion->fetch(PDO::FETCH_ASSOC) )
                                   $Version = intval($Data["IntValue"]);
                                else
                                   $Version = 93;
                            }
                            
                            $GetVersion->closeCursor();
                            
                            $Patch = $Version % 10;
                            $Minor = ($Version / 10) % 10;
                            $Major = ($Version / 100) % 10;
                            
                            if ( $Version == $CurrentVersion )
                            {
                                echo "<span style=\"color: green\">".$Major.".".$Minor.".".$Patch."</span><br/>";
                                echo "<br/><span style=\"font-size: 20px; color: green\">".L("NoUpdateNecessary")."</span>";
                            }
                            else
                            {
                                echo "<span style=\"color: orange\">".$Major.".".$Minor.".".$Patch."</span><br/>";
                            }
                        ?>
                        
                        <br/><br/>
                        <span><?php echo L("UpdateFrom") ?>: </span>
                        <select id="version">
                            <option value="92"<?php if ($Version==92) echo " selected"; ?>>0.9.2</option>
                            <option value="93"<?php if ($Version==93) echo " selected"; ?>>0.9.3</option>
                            <option value="94"<?php if ($Version==94) echo " selected"; ?>>0.9.4</option>
                            <option value="95"<?php if ($Version==95) echo " selected"; ?>>0.9.5</option>
                            <option value="96"<?php if ($Version==96) echo " selected"; ?>>0.9.6</option>
                            <option value="97"<?php if ($Version==97) echo " selected"; ?>>0.9.7</option>
                        </select>
                        <span> <?php echo L("UpdateTo")." ".$CurrentMajor.".".$CurrentMinor.".".$CurrentPatch; ?></span>
                        
                        <div style="position: fixed; right: 50%; top: 50%; margin-right: -380px; margin-top: 260px">
                           <button onclick="updateDatabase()"><?php echo L("Continue"); ?></button>
                        </div>
                        
                    <?php
                        }
                        else
                        {
                            ++$testsFailed;
                            echo "<span style=\"color: red\">".L("Database settings are not correct")."</span>";
                        }
                    ?>
                </div>
            </div>
        </div>
        <div id="error">
        </div>
    </body>
</html>