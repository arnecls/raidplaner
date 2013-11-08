<?php
    require_once("lib/private/locale.php");
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
    <head>
        <title>Raidplaner setup</title>
        <meta http-equiv="X-UA-Compatible" content="IE=Edge">
        <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>        
        <style type="text/css" media="screen">
            html {
                height: 100%;
            }
            
            body {
                min-width: 800px;
                min-height: 600px;
                text-align: center; 
                font-family: Helvetica, Arial, sans-serif; 
                font-size: 14px; 
                line-height: 1.8em;
                padding: 0px;
                margin: 0px;
                height: 100%;
            }
            
            div.appwindow {
                width: 600px; 
                height: 460px; 
                position: relative; 
                top: 50%; 
                margin: -230px auto 0 auto; 
                background-color: white;
            }
        </style>
    </head>

    <body>
        <div class="appwindow">
            <img src="lib/layout/images/alert.png" style="margin-bottom: 20px"/><br/>
            <?php echo L("RaidplanerNotConfigured"); ?><br>
            <?php echo L("PleaseRunSetup"); ?>
        </div>
    </body>

</html>