<?php
    define( "LOCALE_SETUP", true );
    require_once(dirname(__FILE__)."/../lib/private/locale.php");
    @include_once(dirname(__FILE__)."/../lib/config/config.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
    <head>
        <title>Raidplaner config</title>
        <meta http-equiv="X-UA-Compatible" content="IE=Edge">
        
        <link rel="stylesheet" type="text/css" href="layout/default.css">
        <script type="text/javascript" src="../lib/script/jquery-1.9.0.min.js"></script>
        <script type="text/javascript" src="script/main.js"></script>
        
    </head>
    
    <body>
        <div class="appwindow">
            <div style="background-color: black; color: white; padding: 10px">
                Packedpixel<br/>
                <span style="font-size: 24px">Raidplaner setup (1/3)</span>
            </div>
            <div style="padding: 20px">
                <div>
                    <h2><?php echo L("FilesystemChecks"); ?></h2>
                    <?php echo L("PHPRequirements"); ?><br/>
                    <?php echo L("WritePermissionRequired")."\"lib / config\"."; ?><br/>
                    <?php echo L("ChangePermissions"); ?><br/>
                    <?php echo L("FTPClientHelp"); ?><br/><br/>
                    <br/>
                    
                    <?php echo L("PHPVersion"); ?> : <?php
                        $version = explode('.', phpversion());
                        $testsFailed = 0;
                        
                        if ( ($version[0] > 5) || ($version[0] == 5 && $version[1] >= 2) )
                            echo "<span style=\"color: green\">".L("Ok");
                        else
                        {
                            ++$testsFailed;
                            echo "<span style=\"color: red\">".L("OutdatedPHP");
                        }
                            
                        echo " (".phpversion().")</span>";
                    ?><br/>
                    
                    <?php echo L("PDOModule"); ?> : <?php
                        $extensions = get_loaded_extensions();
                        
                        if ( in_array("PDO", $extensions) )
                            echo "<span style=\"color: green\">".L("Ok")."</span>";
                        else
                        {
                            ++$testsFailed;
                            echo "<span style=\"color: red\">".L("PDONotFound")."</span>";
                        }                    
                    ?><br/>
                    
                    <?php echo L("McryptModule"); ?> : <?php
                        if ( in_array("mcrypt", $extensions) )
                            echo "<span style=\"color: green\">".L("Ok")."</span>";
                        else
                        {
                            ++$testsFailed;
                            echo "<span style=\"color: red\">".L("McryptNotFound")."</span>";
                        }
                    ?><br/><br/>
                    
                    <?php echo L("ConfigFolder"); ?> : <?php
                        $configFolderState = is_writable("../lib/config");
                        
                        if ( $configFolderState )
                            echo "<span style=\"color: green\">".L("Ok")."</span>";
                        else
                        {
                            ++$testsFailed;
                            echo "<span style=\"color: red\">".L("NotWriteable")."</span>";
                        }
                    ?><br/>
                    
                    <?php echo L("MainConfigFile"); ?> : <?php
                        $configFileState = (!file_exists("../lib/config/config.php") && $configFolderState) || 
                                            is_writable("../lib/config/config.php");
                        
                        if ( $configFileState )
                            echo "<span style=\"color: green\">".L("Ok")."</span>";
                        else
                        {
                            ++$testsFailed;
                            echo "<span style=\"color: red\">".L("NotWriteable")."</span>";
                        }
                    ?><br/>
                    
                    <?php echo L("PHPBB3ConfigFile"); ?> : <?php
                        $phpbbConfigFileState = (!file_exists("../lib/config/config.phpbb3.php") && $configFolderState) || 
                                                is_writable("../lib/config/config.phpbb3.php");
                                            
                        if ( $phpbbConfigFileState )
                            echo "<span style=\"color: green\">".L("Ok")."</span>";
                        else
                        {
                            ++$testsFailed;
                            echo "<span style=\"color: red\">".L("NotWriteable")."</span>";
                        }
                    ?><br/>
                    
                    <?php echo L("EQDKPConfigFile"); ?> : <?php
                        $eqdkpConfigFileState = (!file_exists("../lib/config/config.eqdkp.php") && $configFolderState) || 
                                                is_writable("../lib/config/config.eqdkp.php");
                                            
                        if ( $eqdkpConfigFileState )
                            echo "<span style=\"color: green\">".L("Ok")."</span>";
                        else
                        {
                            ++$testsFailed;
                            echo "<span style=\"color: red\">".L("NotWriteable")."</span>";
                        }
                    ?><br/>
                    
                    <?php echo L("VBulletinConfigFile"); ?> : <?php
                        $vbulletinConfigFileState = (!file_exists("../lib/config/config.vb3.php") && $configFolderState) || 
                                                is_writable("../lib/config/config.vb3.php");
                                            
                        if ( $vbulletinConfigFileState )
                            echo "<span style=\"color: green\">".L("Ok")."</span>";
                        else
                        {
                            ++$testsFailed;
                            echo "<span style=\"color: red\">".L("NotWriteable")."</span>";
                        }
                    ?><br/>
                    
                    <?php echo L("MyBBConfigFile"); ?> : <?php
                        $mybbConfigFileState = (!file_exists("../lib/config/config.mybb.php") && $configFolderState) || 
                                                is_writable("../lib/config/config.mybb.php");
                                            
                        if ( $mybbConfigFileState )
                            echo "<span style=\"color: green\">".L("Ok")."</span>";
                        else
                        {
                            ++$testsFailed;
                            echo "<span style=\"color: red\">".L("NotWriteable")."</span>";
                        }
                    ?><br/>
                    
                    <?php echo L("SMFConfigFile"); ?> : <?php
                        $smfConfigFileState = (!file_exists("../lib/config/config.smf.php") && $configFolderState) || 
                                                is_writable("../lib/config/config.smf.php");
                                            
                        if ( $smfConfigFileState )
                            echo "<span style=\"color: green\">".L("Ok")."</span>";
                        else
                        {
                            ++$testsFailed;
                            echo "<span style=\"color: red\">".L("NotWriteable")."</span>";
                        }
                    ?><br/>
                </div>
                
                <?php 
                    if ( $testsFailed == 0)
                    {
                ?>
                <div style="position: fixed; right: 50%; top: 50%; margin-right: -380px; margin-top: 260px">
                    <button onclick="loadSetupDb()"><?php echo L("Continue"); ?></button>
                </div>            
                <?php
                    } // if (permissions ok)
                ?>
            </div>
        </div>
    </body>
</html>