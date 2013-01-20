<?php
    define( "LOCALE_SETUP", true );
    require_once(dirname(__FILE__)."/../lib/private/locale.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
    <head>
        <title>Raidplaner config</title>
        <meta http-equiv="X-UA-Compatible" content="IE=Edge">
        
        <link rel="stylesheet" type="text/css" href="layout/default.css">
        <script type="text/javascript" src="../lib/script/jquery-1.9.0.min.js"></script>
    </head>
    
    <body>
        <div class="appwindow">
            <div style="background-color: black; color: white; padding: 10px">
                Packedpixel<br/>
                <span style="font-size: 24px">Raidplaner</span>
            </div>
            <div style="padding: 20px">
                <div>
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
            </div>
        </div>
    </body>
</html>