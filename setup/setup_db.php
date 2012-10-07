<?php
    define( "LOCALE_SETUP", true );
    require_once(dirname(__FILE__)."/../lib/private/locale.php");
    @include_once(dirname(__FILE__)."/../lib/config/config.php")
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
    <head>
        <title>Raidplaner config</title>
        <meta http-equiv="X-UA-Compatible" content="IE=Edge">
        
        <link rel="stylesheet" type="text/css" href="layout/default.css">
        <script type="text/javascript" src="../lib/script/jquery-1.8.2.min.js"></script>
        <script type="text/javascript" src="script/main.js"></script>
        <script type="text/javascript" src="script/setup_db.js.php"></script>
        
    </head>
    
    <body>
        <div class="appwindow">
            <div style="background-color: black; color: white; padding: 10px">
                Packedpixel<br/>
                <span style="font-size: 24px">Raidplaner setup (2/3)</span>
            </div>
            <div style="padding: 20px">
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
                </div>
                
                <div style="margin-top: 1.5em">
                    <h2 style="margin-top: 1.5em"><?php echo L("AdvancedOptions"); ?></h2>
                    <input type="checkbox" id="allow_registration"<?php echo (!defined("ALLOW_REGISTRATION") || ALLOW_REGISTRATION) ? " checked=\"checked\"" : "" ?>/> <?php echo L("AllowManualRegistration"); ?><br/>
                    <input type="password" id="admin_password"/> <?php echo L("AdminPassword"); ?><br/>
                    <input type="password" id="admin_password_check"/> <?php echo L("RepeatPassword"); ?><br/>
                </div>    
                
                <div style="position: fixed; right: 50%; top: 50%; margin-right: -380px; margin-top: 260px">
                    <button onclick="checkForm()"><?php echo L("SaveAndContinue"); ?></button>
                </div>
            </div>
        </div>
    </body>
</html>