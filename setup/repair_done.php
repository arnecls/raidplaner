<?php
    define( "LOCALE_SETUP", true );
    require_once(dirname(__FILE__)."/../lib/private/locale.php");
    require_once(dirname(__FILE__)."/query/tools_repair.php");
?>
<?php readfile("layout/header.html"); ?>

<script type="text/javascript">
    $(document).ready( function() {
        $(".button_back").click( function() { open("repair.php"); });
        $(".button_next").click( function() { open("index.php"); });
    });
</script>

<div class="update_log">
<?php

    // Repair database
    
    if (isset($_REQUEST["db"]))
    {
        echo "<div class=\"update_step\">".L("RepairDatabase");
        
        ValidateTableLayout();
        
        echo "<div class=\"update_step_ok\">OK</div>";
        echo "</div>";
    }
    
    // Convert gameconfig.php
    
    if (isset($_REQUEST["conf"]))
    {
        echo "<div class=\"update_step\">".L("TransferGameconfig");
        
        $ClassNameToId = Array();
        $RoleIdxToId = Array();
        $Game = "";
        
        $GameConfig = dirname(__FILE__)."/../../lib/private/gameconfig.php";
            
        if (file_exists($GameConfig))
        {
            if (UpdateGameConfig110($GameConfig, $ClassNameToId, $RoleIdxToId, $Game))
            {          
                echo "<div class=\"update_step_ok\">OK</div>";
            }
            else
            {
                echo "<div class=\"database_error\">".L("FailedGameconfig")."</div>";
            }
        }
        else
        {
            echo "<div class=\"update_step_warning\">".L("GameconfigNotFound")." (lib/private/gameconfig.php).</div>";
        }
        
        echo "</div>";
    }
    
    // Merge two games
    
    if (isset($_REQUEST["merge"]))
    {
        echo "<div class=\"update_step\">".L("MergeGames");
        
        if (MergeGames($_REQUEST["source"], $_REQUEST["target"]))
            echo "<div class=\"update_step_ok\">OK</div>";
        
        echo "</div>";
    }
    
    // Repair stray characters and invalid classes/roles
    
    if (isset($_REQUEST["char"]))
    {
        echo "<div class=\"update_step\">".L("RepairCharacters");
        
        ValidateCharacters();
        echo "<div class=\"update_step_ok\">OK</div>";
        
        echo "</div>";
    }

?>
<div class="update_step_done"><?php echo L("RepairDone"); ?></div>
</div>

</div>
<div class="bottom_navigation">
    <div class="button_back" style="background-image: url(layout/repair_white.png)"><?php echo L("Back"); ?></div>
    <div class="button_next" style="background-image: url(layout/install_white.png)"><?php echo L("Continue"); ?></div>

<?php readfile("layout/footer.html"); ?>
