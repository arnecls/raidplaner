<?php
    define( "LOCALE_SETUP", true );
    require_once(dirname(__FILE__)."/../lib/private/locale.php");
?>
<?php readfile("layout/header.html"); ?>

<script type="text/javascript">
    $(document).ready( function() {
        $(".button_back").click( function() { open("index.php"); });
        $(".button_next").click( function() { open("repair_done.php"); });
    });
</script>

<h2><?php echo L("Repair"); ?></h2>

<?php echo L("GameconfigProblems"); ?><br/>
<?php echo L("RepairTheseProblems"); ?><br/>

</div>
<div class="bottom_navigation">
    <div class="button_back" style="background-image: url(layout/install_white.png)"><?php echo L("Back"); ?></div>
    <div class="button_next" style="background-image: url(layout/repair_white.png)"><?php echo L("Continue"); ?></div>

<?php readfile("layout/footer.html"); ?>
