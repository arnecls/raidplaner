<?php
    define( "LOCALE_SETUP", true );
    require_once(dirname(__FILE__)."/../lib/private/locale.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
    <head>
        <title>Raidplaner config</title>
        <meta http-equiv="X-UA-Compatible" content="IE=Edge">
        
        <script type="text/javascript" src="../lib/script/jquery-1.9.1.min.js"></script>
        <script type="text/javascript" src="script/main.js"></script>
        <script type="text/javascript" src="script/index.js"></script>
        
        <link rel="stylesheet" type="text/css" href="layout/default.css">
        <style type="text/css" media="screen">
            div.button {
                width: 240px;
                display: block;
                text-color: white;
                font-weight: bold;
                vertical-align: middle;
                position: relative;
                margin: auto;
                
                -moz-border-radius: 4px; 
                -webkit-border-radius: 4px; 
                -khtml-border-radius: 4px; 
                border-radius: 4px;
            }
            
            .up:hover {
                border: 1px solid #aaa;
                background-color: #ccc;
                color: #0a0;
                
                background: -webkit-gradient(linear, left top, left bottom, from(#fff), to(#ccc));
                background: -moz-linear-gradient(top,  #fff,  #ccc);
                filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffffff', endColorstr='#cccccc');
            }
            
            .down {
                border: 1px solid #0a0;
                background-color: #ccc;
                color: #0a0;
                
                background: -webkit-gradient(linear, left top, left bottom, from(#ccc), to(#fff));
                background: -moz-linear-gradient(top,  #ccc,  #fff);
                filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#cccccc', endColorstr='#ffffff');
            }
            
            .up {
                border: 1px solid #666;
                background-color: #aaa;
                color: #333;
                
                background: -webkit-gradient(linear, left top, left bottom, from(#fff), to(#aaa));
                background: -moz-linear-gradient(top,  #fff,  #aaa);
                filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffffff', endColorstr='#aaaaaa');
            }
            
            div.button span {
                position: relative;
                top: 50%;
                margin-top: -0.25em;
                display: block;
            }
        </style>
    </head>
    
    <body>
        <div class="appwindow">
            <div style="background-color: black; color: white; padding: 10px">
                Packedpixel<br/>
                <span style="font-size: 24px">Raidplaner</span>
            </div>
            <div style="padding: 20px; text-align: center">
                <div id="install" class="button up" style="height: 80px; top: 170px; font-size: 3em"><span><?php echo L("Install"); ?></span></div>
                <div id="upgrade" class="button up" style="height: 34px; top: 175px; font-size: 1.5em"><span style="margin-top: -0.5em"><?php echo L("Update"); ?></span></div>
                <?php 
                    $configFolderState = is_writable("../lib/config");
                    $configFileState = (file_exists("../lib/config/config.php") && $configFolderState) || 
                                       is_writable("../lib/config/config.php");
                        
                    if ($configFolderState && $configFileState)
                    {
                ?>
                <div id="binding" class="button up" style="width: 200px; height: 24px; top: 195px; font-size: 1em"><span style="margin-top: -0.6em"><?php echo L("EditBindings"); ?></span></div>
                <?php } ?>
            </div>
        </div>
    </body>
</html>