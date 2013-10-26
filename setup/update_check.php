<?php
    define( "LOCALE_SETUP", true );
    require_once(dirname(__FILE__)."/../lib/private/locale.php");
    @include_once(dirname(__FILE__)."/../lib/config/config.php");
    require_once(dirname(__FILE__)."/../lib/private/connector.class.php");
    
    $CurrentVersion = 100;
    $CurrentPatch = $CurrentVersion % 10;
    $CurrentMinor = ($CurrentVersion / 10) % 10;
    $CurrentMajor = ($CurrentVersion / 100) % 10;
    
?>
<?php include("layout/header.html"); ?>

<script type="text/javascript">
    $(document).ready( function() {
        var Now = new Date();
        var UTCOffset = Now.getTimezoneOffset()
    
        $("#button_repair").click( function() { open("repair_done.php"); });
        $(".button_back").click( function() { open("index.php"); });
        $(".button_next").click( function() { open("update_done.php?version="+$("#version").val()+"&utcoffset="+UTCOffset); });
    });
</script>

<h2><?php echo L("VersionDetection"); ?></h2>
<?php echo L("VersionDetectProgress"); ?><br/>
<?php echo L("ChooseManually"); ?><br/>
<?php echo L("OnlyDBAffected"); ?><br/>
<?php echo L("NoChangeNoAction"); ?><br/>
<br/><br/>

<?php
    echo "<span class=\"check_field\">".L("DatabaseConnection")."</span>";
    try
    {
        $Connector  = Connector::getInstance(true);
        $DatabaseOk = true;
    }
    catch (PDOException $Exception)
    {
        $DatabaseOk = false;
    }

    if ( $DatabaseOk )
    {
        echo "<span class=\"check_result\" style=\"color: green\">".L("Ok")."</span><br/>";
        echo "<span class=\"check_field\">".L("DetectedVersion")."</span>";
        
        $GetVersion = $Connector->prepare("SELECT IntValue FROM `".RP_TABLE_PREFIX."Setting` WHERE Name='Version' LIMIT 1");

        if ( !$GetVersion->execute() )
        {
            $Version = 0;
        }
        else
        {
            if ( $Data = $GetVersion->fetch(PDO::FETCH_ASSOC) )
               $Version = intval($Data["IntValue"]);
            else
               $Version = 92;
        }
        
        $GetVersion->closeCursor();
        
        $Patch = $Version % 10;
        $Minor = ($Version / 10) % 10;
        $Major = ($Version / 100) % 10;
        
        if ( $Version == $CurrentVersion )
        {
            echo "<span class=\"check_result\" style=\"color: green\">".$Major.".".$Minor.".".$Patch."</span>";
            echo "<br/><div style=\"margin-top:30px; font-size: 20px; color: green\">".L("NoUpdateNecessary")."</div>";
        }
        else if ($Version == 0)
        {
            echo "<span class=\"check_result\" style=\"color: red\">".L("BrokenDatabase")."</span><br/>";
        }
        else
        {
            echo "<span class=\"check_result\" style=\"color: orange\">".$Major.".".$Minor.".".$Patch."</span><br/>";
        }
        
        if ($Version == 0)
        {
    ?>
        <div style="margin-top:20px">
            <button id="button_repair"><?php echo L("RepairDatabase"); ?></button>
        </div>
    <?php
        }
        else if ($Version != $CurrentVersion)
        {
    ?>
    
    <div style="margin-top:20px">
        <span><?php echo L("UpdateFrom") ?>: </span>
        <select id="version">
            <option value="92"<?php if ($Version==92) echo " selected"; ?>>0.9.2</option>
            <option value="93"<?php if ($Version==93) echo " selected"; ?>>0.9.3</option>
            <option value="94"<?php if ($Version==94) echo " selected"; ?>>0.9.4</option>
            <option value="95"<?php if ($Version==95) echo " selected"; ?>>0.9.5</option>
            <option value="96"<?php if ($Version==96) echo " selected"; ?>>0.9.6</option>
            <option value="97"<?php if ($Version==97) echo " selected"; ?>>0.9.7</option>
            <option value="98"<?php if ($Version==98) echo " selected"; ?>>0.9.8</option>
        </select>
        <span> <?php echo L("UpdateTo")." ".$CurrentMajor.".".$CurrentMinor.".".$CurrentPatch; ?></span>
    </div>
    
    <?php
        }
    }
    else
    {
        ++$TestsFailed;
        echo "<span style=\"color: red\">".L("Database settings are not correct")."</span>";
    }
?>
</div>
<div class="bottom_navigation">
    <div class="button_back" style="background-image: url(layout/install_white.png)"><?php echo L("Back"); ?></div>
    <?php if ($Version != $CurrentVersion) { ?>
    <div class="button_next" style="background-image: url(layout/update_white.png)"><?php echo L("Continue"); ?></div>
    <?php } ?>

<?php include("layout/footer.html"); ?>