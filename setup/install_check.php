<?php
    define( "LOCALE_SETUP", true );
    require_once(dirname(__FILE__)."/../lib/private/locale.php");
    require_once(dirname(__FILE__)."/../lib/private/userproxy.class.php");
?>
<?php readfile("layout/header.html"); ?>

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
    
    echo "<br/><span class=\"check_field\">".L("PHPVersion")." (>= 5.3.0)</span>";
    $TestsFailed = 0;
    
    if ( PHP_VERSION_ID >= 50300 )
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
    
    // Plugin config files check
    
    foreach(PluginRegistry::$Classes as $PluginName)
    {
        $Plugin = new ReflectionClass($PluginName);
        $PluginInstance = $Plugin->newInstance();
        $Binding = $PluginInstance->BindingName;
        
        echo "<br/><span class=\"check_field\">".L($Binding."_ConfigFile")."</span>";
                                    
        if ( $PluginInstance->isConfigWriteable() )
        {
            echo "<span class=\"check_result\" style=\"color: green\">".L("Ok")."</span>";
        }
        else
        {
            ++$TestsFailed;
            echo "<span class=\"check_result\" style=\"color: red\">".L("NotWriteable")."</span>";
        }
    }
?>
</div>
<div class="bottom_navigation">
    <div class="button_back" style="background-image: url(layout/install_white.png)"><?php echo L("Back"); ?></div>
    <?php if ($TestsFailed==0) { ?>
    <div class="button_next" style="background-image: url(layout/config_white.png)"><?php echo L("Continue"); ?></div>
    <?php } ?>
<?php readfile("layout/footer.html"); ?>