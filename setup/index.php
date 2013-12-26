<?php
    define( "LOCALE_SETUP", true );
    require_once(dirname(__FILE__)."/../lib/private/locale.php");
    
    $ConfigFolderState = is_writable("../lib/config");
    $ConfigFileState = (file_exists("../lib/config/config.php") && $ConfigFolderState) || 
                       is_writable("../lib/config/config.php");
                       
    $UpdateMode = $ConfigFolderState && $ConfigFileState;
    
    if ($UpdateMode)
    {
        require_once(dirname(__FILE__)."/../lib/private/connector.class.php");
        @require_once(dirname(__FILE__)."/../lib/config/config.php");
        
        $Connector = Connector::getInstance();
        
        $TableQuery = $Connector->prepare("SHOW TABLES");
        $Tables = array("Attendance", "Character", "Location", "Raid", "Setting", "User", "UserSetting");
        $TablesOk = false;
                
        $TableQuery->loop( function($TableData) use ($Tables, &$TablesOk) {
            list($key,$TableName) = each($TableData);
            
            if (strpos($TableName, RP_TABLE_PREFIX) === 0)
            {
                $Name = substr($TableName, strlen(RP_TABLE_PREFIX));
                if (in_array($Name, $Tables))
                {
                    $TablesOk = true;
                    return false;
                }
            }
        });
        
        $UpdateMode = $TablesOk;
    }
?>
<?php readfile("layout/header.html"); ?>
                
<script type="text/javascript">
    $(document).ready( function() {
        $(".button_small, .button_large").mouseout( function() {
            $(".button_text").empty();
        });
        
        <?php if ($UpdateMode) { ?>
        $(".icon_update")  .mouseover( function() { $(".button_text").append("<?php echo L("Update");?>"); });
        $(".icon_bindings").mouseover( function() { $(".button_text").append("<?php echo L("EditBindings");?>"); });
        $(".icon_repair")  .mouseover( function() { $(".button_text").append("<?php echo L("RepairDatabase");?>"); });
        $(".icon_password").mouseover( function() { $(".button_text").append("<?php echo L("ResetPassword");?>"); });
        $(".icon_config")  .mouseover( function() { $(".button_text").append("<?php echo L("EditConfig");?>"); });
        
        $(".icon_update")  .click( function() { open("update_check.php"); });
        $(".icon_config")  .click( function() { open("install_config.php?single"); });
        $(".icon_password").click( function() { open("install_password.php?single"); });
        $(".icon_bindings").click( function() { open("install_bindings.php?single"); });
        $(".icon_repair").click( function() { open("repair.php"); });
        <?php } else { ?>
        $(".icon_install") .mouseover( function() { $(".button_text").append("<?php echo L("Install");?>"); });
        $(".icon_install") .click( function() { open("install_check.php"); });
        <?php } ?>
    });
</script>

<?php if ($UpdateMode) { ?>
<div class="button_text"></div>
<div class="buttongrid">
    <div class="button_large icon_update" style="float:left"></div>
    <div style="float:left">
        <div class="button_small icon_config" style="float: left"></div>
        <div class="button_small icon_password" style="float: left"></div>
        <div class="button_small icon_bindings" style="clear: left; float: left"></div>
        <div class="button_small icon_repair" style="float: left"></div>
    </div>
    </div>
</div>
<?php } else { ?>
<div class="button_text"></div>
<div class="buttongrid">
    <div class="button_large icon_install" style="float:left"></div>
    <div style="float:left">
        <div class="button_small_disabled icon_config" style="float: left"></div>
        <div class="button_small_disabled icon_password" style="float: left"></div>
        <div class="button_small_disabled icon_bindings" style="clear: left; float: left"></div>
        <div class="button_small_disabled icon_repair" style="float: left"></div>
    </div>
    </div>
</div>
<?php } ?>

<?php readfile("layout/footer.html"); ?>