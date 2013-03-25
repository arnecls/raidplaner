<?php
    define( "LOCALE_SETUP", true );
    require_once(dirname(__FILE__)."/../lib/private/locale.php");
    @include_once(dirname(__FILE__)."/../lib/config/config.php");
?>
<?php include("layout/header.html"); ?>

<?php if (isset($_REQUEST["single"])) { ?>
<script type="text/javascript">
    $(document).ready( function() {
        $(".button_back").click( function() { open("index.php"); });
        $(".button_next").click( function() { checkConfigForm(OnConfigSubmit, "index.php"); });
    });
</script>
<?php } else { ?>
<script type="text/javascript">
    $(document).ready( function() {
        $(".button_back").click( function() { open("install_check.php"); });
        $(".button_next").click( function() { checkConfigForm(OnConfigSubmit, "install_password.php"); });
    });
</script>
<?php } ?>

<div style="margin-top: 1.5em">
    <h2><?php echo L("DatabaseConnection"); ?></h2>
    
    <?php echo L("ConfigureDatabase"); ?><br/>
    <?php echo L("EnterPrefix"); ?><br/>
    <?php echo L("SameAsForumDatabase"); ?><br/>
    <br/>
    
    <input type="text" id="host" value="<?php echo (defined("SQL_HOST")) ? SQL_HOST : "localhost" ?>"/> <?php echo L("DatabaseHost"); ?><br/>
    <input type="text" id="database" value="<?php echo (defined("RP_USER")) ? RP_DATABASE : "raidplaner" ?>"/> <?php echo L("RaidplanerDatabase"); ?><br/>
    <input type="text" id="user" value="<?php echo (defined("RP_USER")) ? RP_USER : "root" ?>"/> <?php echo L("UserWithDBPermissions"); ?><br/>
    <input type="password" id="password"/> <?php echo L("UserPassword"); ?><br/>
    <input type="password" id="password_check"/> <?php echo L("RepeatPassword"); ?><br/>
    <br/>
    <input type="text" id="prefix" value="<?php echo (defined("RP_TABLE_PREFIX")) ? RP_TABLE_PREFIX : "raids_" ?>"/> <?php echo L("TablePrefix"); ?><br/>
    <br/>
    <input type="checkbox" id="allow_registration"<?php echo (!defined("ALLOW_REGISTRATION") || ALLOW_REGISTRATION) ? " checked=\"checked\"" : "" ?>/> <?php echo L("AllowManualRegistration"); ?><br/>
	<input type="checkbox" id="allow_cleartext"<?php echo (defined("USE_CLEARTEXT_PASSWORDS") && USE_CLEARTEXT_PASSWORDS) ? " checked=\"checked\"" : "" ?>/> <?php echo L("UseClearText"); ?><br/>
    <br/>
    <button onclick="checkConfigForm(OnCheckConfigConnection)"><?php echo L("VerifySettings"); ?></button>
</div>

</div>
<div class="bottom_navigation">
<?php if (isset($_REQUEST["single"])) { ?>
    <div class="button_back" style="background-image: url(layout/install_white.png)"><?php echo L("Back"); ?></div>
    <div class="button_next" style="background-image: url(layout/update_white.png)"><?php echo L("Continue"); ?></div>
<?php } else { ?>
    <div class="button_back" style="background-image: url(layout/install_white.png)"><?php echo L("Back"); ?></div>
    <div class="button_next" style="background-image: url(layout/password_white.png)"><?php echo L("Continue"); ?></div>
<?php } ?>


<?php include("layout/footer.html"); ?>

