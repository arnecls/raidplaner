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
    
    echo "<br/><span class=\"check_field\">".L("PHPVersion")." (>= 5.2)</span>";
    $Version = explode('.', phpversion());
    $TestsFailed = 0;
    
    if ( ($Version[0] > 5) || ($Version[0] == 5 && $Version[1] >= 2) )
    {
        echo "<span class=\"check_result\" style=\"color: green\">".L("Ok");
    }
    else
    {
        ++$TestsFailed;
        echo "<span class=\"check_result\" style=\"color: red\">".L("OutdatedPHP");
    }
        
    echo " (".phpversion().")</span>";

    // PDO check

    echo "<br/><span class=\"check_field\">".L("PDOModule")."</span>";
    $Extensions = get_loaded_extensions();
    $PDOInstalled = in_array("PDO", $Extensions);
    
    if ( $PDOInstalled )
    {
        echo "<span class=\"check_result\" style=\"color: green\">".L("Ok")."</span>";
    }
    else
    {
        ++$TestsFailed;
        echo "<span class=\"check_result\" style=\"color: red\">".L("PDONotFound")."</span>";
    }
    
    // PDO MySQL check

    echo "<br/><span class=\"check_field\">".L("PDOMySQLModule")."</span>";
    $PDODriverInstalled = false;
    
    if ( $PDOInstalled )
    {
        $Drivers = PDO::getAvailableDrivers();
        $PDODriverInstalled = in_array("mysql", $Drivers);
    }
    
    if ($PDODriverInstalled)
    {
        echo "<span class=\"check_result\" style=\"color: green\">".L("Ok")."</span>";
    }
    else
    {
        ++$TestsFailed;
        echo "<span class=\"check_result\" style=\"color: red\">".L("PDOMySQLNotFound")."</span>";
    }
    
    // MCrypt module check         

    echo "<br/><span class=\"check_field\">".L("McryptModule")."</span>";
    if ( in_array("mcrypt", $Extensions) )
    {
        echo "<span class=\"check_result\" style=\"color: green\">".L("Ok")."</span>";
    }
    else
    {
        ++$TestsFailed;
        echo "<span class=\"check_result\" style=\"color: red\">".L("McryptNotFound")."</span>";
    }
    
    // Config folder check

    echo "<br/><br/><span class=\"check_field\">".L("ConfigFolder")."</span>";
    $ConfigFolderState = is_writable("../lib/config");
    
    if ( $ConfigFolderState )
    {
        echo "<span class=\"check_result\" style=\"color: green\">".L("Ok")."</span>";
    }
    else
    {
        ++$TestsFailed;
        echo "<span class=\"check_result\" style=\"color: red\">".L("NotWriteable")."</span>";
    }
    
    // Main config file check
    
    echo "<br/><span class=\"check_field\">".L("MainConfigFile")."</span>";
    $ConfigFileState = (!file_exists("../lib/config/config.php") && $ConfigFolderState) || 
                        is_writable("../lib/config/config.php");
    
    if ( $ConfigFileState )
    {
        echo "<span class=\"check_result\" style=\"color: green\">".L("Ok")."</span>";
    }
    else
    {
        ++$TestsFailed;
        echo "<span class=\"check_result\" style=\"color: red\">".L("NotWriteable")."</span>";
    }
    
    // PHPBB3 config file check

    echo "<br/><span class=\"check_field\">".L("PHPBB3ConfigFile")."</span>";
    $PhpbbConfigFileState = (!file_exists("../lib/config/config.phpbb3.php") && $ConfigFolderState) || 
                            is_writable("../lib/config/config.phpbb3.php");
                        
    if ( $PhpbbConfigFileState )
    {
        echo "<span class=\"check_result\" style=\"color: green\">".L("Ok")."</span>";
    }
    else
    {
        ++$TestsFailed;
        echo "<span class=\"check_result\" style=\"color: red\">".L("NotWriteable")."</span>";
    }
    
    // EQDKP config file check

    echo "<br/><span class=\"check_field\">".L("EQDKPConfigFile")."</span>";
    $EqdkpConfigFileState = (!file_exists("../lib/config/config.eqdkp.php") && $ConfigFolderState) || 
                            is_writable("../lib/config/config.eqdkp.php");
                        
    if ( $EqdkpConfigFileState )
    {
        echo "<span class=\"check_result\" style=\"color: green\">".L("Ok")."</span>";
    }
    else
    {
        ++$TestsFailed;
        echo "<span class=\"check_result\" style=\"color: red\">".L("NotWriteable")."</span>";
    }
    
    // VBulletin config file check

    echo "<br/><span class=\"check_field\">".L("VBulletinConfigFile")."</span>";
    $VbulletinConfigFileState = (!file_exists("../lib/config/config.vb3.php") && $ConfigFolderState) || 
                            is_writable("../lib/config/config.vb3.php");
                        
    if ( $VbulletinConfigFileState )
    {
        echo "<span class=\"check_result\" style=\"color: green\">".L("Ok")."</span>";
    }
    else
    {
        ++$TestsFailed;
        echo "<span class=\"check_result\" style=\"color: red\">".L("NotWriteable")."</span>";
    }
    
    // MYBB config file check
    
    echo "<br/><span class=\"check_field\">".L("MyBBConfigFile")."</span>";
    $MybbConfigFileState = (!file_exists("../lib/config/config.mybb.php") && $ConfigFolderState) || 
                            is_writable("../lib/config/config.mybb.php");
                        
    if ( $MybbConfigFileState )
    {
        echo "<span class=\"check_result\" style=\"color: green\">".L("Ok")."</span>";
    }
    else
    {
        ++$TestsFailed;
        echo "<span class=\"check_result\" style=\"color: red\">".L("NotWriteable")."</span>";
    }
    
    // SMF config file check

    echo "<br/><span class=\"check_field\">".L("SMFConfigFile")."</span>";
    $SmfConfigFileState = (!file_exists("../lib/config/config.smf.php") && $ConfigFolderState) || 
                            is_writable("../lib/config/config.smf.php");
                        
    if ( $SmfConfigFileState )
    {
        echo "<span class=\"check_result\" style=\"color: green\">".L("Ok")."</span>";
    }
    else
    {
        ++$TestsFailed;
        echo "<span class=\"check_result\" style=\"color: red\">".L("NotWriteable")."</span>";
    }
    
    // Joomla config file check

    echo "<br/><span class=\"check_field\">".L("JoomlaConfigFile")."</span>";
    $JmlConfigFileState = (!file_exists("../lib/config/config.joomla3.php") && $ConfigFolderState) || 
                            is_writable("../lib/config/config.joomla3.php");
                        
    if ( $JmlConfigFileState )
    {
        echo "<span class=\"check_result\" style=\"color: green\">".L("Ok")."</span>";
    }
    else
    {
        ++$TestsFailed;
        echo "<span class=\"check_result\" style=\"color: red\">".L("NotWriteable")."</span>";
    }
    
    // Drupal config file check

    echo "<br/><span class=\"check_field\">".L("DrupalConfigFile")."</span>";
    $DrupalConfigFileState = (!file_exists("../lib/config/config.drupal.php") && $ConfigFolderState) || 
                               is_writable("../lib/config/config.drupal.php");
                        
    if ( $DrupalConfigFileState )
    {
        echo "<span class=\"check_result\" style=\"color: green\">".L("Ok")."</span>";
    }
    else
    {
        ++$TestsFailed;
        echo "<span class=\"check_result\" style=\"color: red\">".L("NotWriteable")."</span>";
    }
    
    // Wordpress config file check

    echo "<br/><span class=\"check_field\">".L("WpConfigFile")."</span>";
    $WpConfigFileState = (!file_exists("../lib/config/config.wp.php") && $ConfigFolderState) || 
                           is_writable("../lib/config/config.wp.php");
                        
    if ( $WpConfigFileState )
    {
        echo "<span class=\"check_result\" style=\"color: green\">".L("Ok")."</span>";
    }
    else
    {
        ++$TestsFailed;
        echo "<span class=\"check_result\" style=\"color: red\">".L("NotWriteable")."</span>";
    }
?>
</div>
<div class="bottom_navigation">
    <div class="button_back" style="background-image: url(layout/install_white.png)"><?php echo L("Back"); ?></div>
    <?php if ($TestsFailed==0) { ?>
    <div class="button_next" style="background-image: url(layout/config_white.png)"><?php echo L("Continue"); ?></div>
    <?php } ?>
<?php include("layout/footer.html"); ?>