<?php
    define( "LOCALE_SETUP", true );
    require_once(dirname(__FILE__)."/../lib/private/locale.php");
?>
<?php readfile("layout/header.html"); ?>

<?php if (isset($_REQUEST["single"])) { ?>
<script type="text/javascript">
    $(document).ready( function() {
        $(".button_back").click( function() { open("index.php"); });
        $(".button_next").click( function() { checkPasswordForm("index.php"); });
    });
</script>
<?php } else { ?>
<script type="text/javascript">
    $(document).ready( function() {
        $(".button_back").click( function() { open("install_config.php"); });
        $(".button_next").click( function() { checkPasswordForm("install_bindings.php"); });
    });
</script>
<?php } ?>

<h2><?php echo L("AdminPassword"); ?></h2>

<?php echo L("AdminPasswordSetup"); ?><br/>
<?php echo L("AdminNotMoveable"); ?><br/>

<br/>

<input type="password" id="password"/> <?php echo L("AdminPassword"); ?><br/>
<input type="password" id="password_check"/> <?php echo L("RepeatPassword"); ?><br/>

</div>
<div class="bottom_navigation">
<?php if (isset($_REQUEST["single"])) { ?>
    <div class="button_back" style="background-image: url(layout/install_white.png)"><?php echo L("Back"); ?></div>
    <div class="button_next" style="background-image: url(layout/update_white.png)"><?php echo L("Continue"); ?></div>
<?php } else { ?>
    <div class="button_back" style="background-image: url(layout/config_white.png)"><?php echo L("Back"); ?></div>
    <div class="button_next" style="background-image: url(layout/bindings_white.png)"><?php echo L("Continue"); ?></div>
<?php } ?>

<?php readfile("layout/footer.html"); ?>
