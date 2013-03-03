<?php
    define( "LOCALE_SETUP", true );
    require_once(dirname(__FILE__)."/../lib/private/locale.php");
?>
<?php include("layout/header.html"); ?>

<script type="text/javascript">
    $(document).ready( function() {
        $(".button_back").click( function() { open("install_bindings.php"); });
        $(".button_next").click( function() { open("index.php"); });
    });
</script>

<h2><?php echo L("SetupComplete"); ?></h2>
<?php 
    echo L("RaidplanerSetupDone")."<br/>";
    echo L("DeleteSetupFolder")."<br/>";
    echo "<br/>";
    echo "lib / private<br/>";
    echo "lib / config<br/>";
    echo "<br/>";
    echo L("ThankYou")."<br/>";
    echo L("VisitBugtracker");
    echo "<a href=\"http://code.google.com/p/ppx-raidplaner/issues/list\">Google Code</a>."
?>

</div>
<div class="bottom_navigation">
    <div class="button_back" style="background-image: url(layout/bindings_white.png)"><?php echo L("Back"); ?></div>
    <div class="button_next" style="background-image: url(layout/install_white.png)"><?php echo L("Continue"); ?></div>


<?php include("layout/footer.html"); ?>