<?php
    define( "LOCALE_SETUP", true );
    require_once(dirname(__FILE__)."/../lib/private/locale.php");
?>
<?php include("layout/header.html"); ?>

<script type="text/javascript">
    $(document).ready( function() {
        $(".button_back").click( function() { open("index.php"); });
        $(".button_next").click( function() { open("install_config.php"); });
    });
</script>

<?php
    echo "<h2>".L("FilesystemChecks")."</h2>";
    echo L("PHPRequirements")."<br/>";
    echo L("WritePermissionRequired")."\"lib / config\".<br/>";
    echo L("ChangePermissions")."<br/>";
    echo L("FTPClientHelp")."<br/><br/>";

    // Version check    
    
    echo "<br/><span class=\"check_field\">".L("PHPVersion")."</span>";
    $version = explode('.', phpversion());
    $testsFailed = 0;
    
    if ( ($version[0] > 5) || ($version[0] == 5 && $version[1] >= 2) )
    {
        echo "<span class=\"check_result\" style=\"color: green\">".L("Ok");
    }
    else
    {
        ++$testsFailed;
        echo "<span class=\"check_result\" style=\"color: red\">".L("OutdatedPHP");
    }
        
    echo " (".phpversion().", min 5.2)</span>";

    // PDO check

    echo "<br/><span class=\"check_field\">".L("PDOModule")."</span>";
    $extensions = get_loaded_extensions();
    $PDOInstalled = in_array("PDO", $extensions);
    
    if ( $PDOInstalled )
    {
        echo "<span class=\"check_result\" style=\"color: green\">".L("Ok")."</span>";
    }
    else
    {
        ++$testsFailed;
        echo "<span class=\"check_result\" style=\"color: red\">".L("PDONotFound")."</span>";
    }
    
    // PDO MySQL check

    echo "<br/><span class=\"check_field\">".L("PDOMySQLModule")."</span>";
    $PDODriverInstalled = false;
    
    if ( $PDOInstalled )
    {
        $drivers = PDO::getAvailableDrivers();
        $PDODriverInstalled = in_array("mysql", $drivers);
    }
    
    if ($PDODriverInstalled)
    {
        echo "<span class=\"check_result\" style=\"color: green\">".L("Ok")."</span>";
    }
    else
    {
        ++$testsFailed;
        echo "<span class=\"check_result\" style=\"color: red\">".L("PDOMySQLNotFound")."</span>";
    }
    
    // MCrypt module check         

    echo "<br/><span class=\"check_field\">".L("McryptModule")."</span>";
    if ( in_array("mcrypt", $extensions) )
    {
        echo "<span class=\"check_result\" style=\"color: green\">".L("Ok")."</span>";
    }
    else
    {
        ++$testsFailed;
        echo "<span class=\"check_result\" style=\"color: red\">".L("McryptNotFound")."</span>";
    }
    
    // Config folder check

    echo "<br/><br/><span class=\"check_field\">".L("ConfigFolder")."</span>";
    $configFolderState = is_writable("../lib/config");
    
    if ( $configFolderState )
    {
        echo "<span class=\"check_result\" style=\"color: green\">".L("Ok")."</span>";
    }
    else
    {
        ++$testsFailed;
        echo "<span class=\"check_result\" style=\"color: red\">".L("NotWriteable")."</span>";
    }
    
    // Main config file check
    
    echo "<br/><span class=\"check_field\">".L("MainConfigFile")."</span>";
    $configFileState = (!file_exists("../lib/config/config.php") && $configFolderState) || 
                        is_writable("../lib/config/config.php");
    
    if ( $configFileState )
    {
        echo "<span class=\"check_result\" style=\"color: green\">".L("Ok")."</span>";
    }
    else
    {
        ++$testsFailed;
        echo "<span class=\"check_result\" style=\"color: red\">".L("NotWriteable")."</span>";
    }
    
    // PHPBB3 config file check

    echo "<br/><span class=\"check_field\">".L("PHPBB3ConfigFile")."</span>";
    $phpbbConfigFileState = (!file_exists("../lib/config/config.phpbb3.php") && $configFolderState) || 
                            is_writable("../lib/config/config.phpbb3.php");
                        
    if ( $phpbbConfigFileState )
    {
        echo "<span class=\"check_result\" style=\"color: green\">".L("Ok")."</span>";
    }
    else
    {
        ++$testsFailed;
        echo "<span class=\"check_result\" style=\"color: red\">".L("NotWriteable")."</span>";
    }
    
    // EQDKP config file check

    echo "<br/><span class=\"check_field\">".L("EQDKPConfigFile")."</span>";
    $eqdkpConfigFileState = (!file_exists("../lib/config/config.eqdkp.php") && $configFolderState) || 
                            is_writable("../lib/config/config.eqdkp.php");
                        
    if ( $eqdkpConfigFileState )
    {
        echo "<span class=\"check_result\" style=\"color: green\">".L("Ok")."</span>";
    }
    else
    {
        ++$testsFailed;
        echo "<span class=\"check_result\" style=\"color: red\">".L("NotWriteable")."</span>";
    }
    
    // VBulletin config file check

    echo "<br/><span class=\"check_field\">".L("VBulletinConfigFile")."</span>";
    $vbulletinConfigFileState = (!file_exists("../lib/config/config.vb3.php") && $configFolderState) || 
                            is_writable("../lib/config/config.vb3.php");
                        
    if ( $vbulletinConfigFileState )
    {
        echo "<span class=\"check_result\" style=\"color: green\">".L("Ok")."</span>";
    }
    else
    {
        ++$testsFailed;
        echo "<span class=\"check_result\" style=\"color: red\">".L("NotWriteable")."</span>";
    }
    
    // MYBB config file check
    
    echo "<br/><span class=\"check_field\">".L("MyBBConfigFile")."</span>";
    $mybbConfigFileState = (!file_exists("../lib/config/config.mybb.php") && $configFolderState) || 
                            is_writable("../lib/config/config.mybb.php");
                        
    if ( $mybbConfigFileState )
    {
        echo "<span class=\"check_result\" style=\"color: green\">".L("Ok")."</span>";
    }
    else
    {
        ++$testsFailed;
        echo "<span class=\"check_result\" style=\"color: red\">".L("NotWriteable")."</span>";
    }
    
    // SMF config file check

    echo "<br/><span class=\"check_field\">".L("SMFConfigFile")."</span>";
    $smfConfigFileState = (!file_exists("../lib/config/config.smf.php") && $configFolderState) || 
                            is_writable("../lib/config/config.smf.php");
                        
    if ( $smfConfigFileState )
    {
        echo "<span class=\"check_result\" style=\"color: green\">".L("Ok")."</span>";
    }
    else
    {
        ++$testsFailed;
        echo "<span class=\"check_result\" style=\"color: red\">".L("NotWriteable")."</span>";
    }
?>
</div>
<div class="bottom_navigation">
    <div class="button_back" style="background-image: url(layout/install_white.png)"><?php echo L("Back"); ?></div>
    <?php if ($testsFailed==0) { ?>
    <div class="button_next" style="background-image: url(layout/config_white.png)"><?php echo L("Continue"); ?></div>
    <?php } ?>
<?php include("layout/footer.html"); ?>