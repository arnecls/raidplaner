<?php
	define( "LOCALE_SETUP", true );
	require_once(dirname(__FILE__)."/../lib/private/locale.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
	<head>
		<title>Raidplaner config</title>
        <meta http-equiv="X-UA-Compatible" content="IE=Edge">
        
        <script type="text/javascript" src="../lib/script/jquery-1.7.2.min.js"></script>
        <script type="text/javascript" src="script/main.js"></script>
        <script type="text/javascript" src="script/index.js"></script>
        
        <style type="text/css" media="screen">
            div.button {
                width: 240px;
                display: block;
                text-color: white;
                font-weight: bold;
                vertical-align: middle;
                position: relative;
                left: 50%;
                margin-left: -120px;
                
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
	
	<body style="font-family: helvetica, arial, sans-serif; font-size: 11px; line-height: 1.5em; background-color: #cccccc; color: black">
		<div style="width: 800px; height: 600px; position: fixed; left: 50%; top: 50%; margin-left: -400px; margin-top: -300px; background-color: white">
			<div style="background-color: black; color: white; padding: 10px">
				Packedpixel<br/>
				<span style="font-size: 24px">Raidplaner</span>
			</div>
			<div style="padding: 20px; text-align: center">
                <div id="install" class="button up" style="height: 80px; margin-top: 190px; font-size: 3em"><span><?php echo L("Install"); ?></span></div>
                <div id="upgrade" class="button up" style="height: 24px; margin-top: 10px; font-size: 1em"><span style="margin-top: -0.6em"><?php echo L("Update"); ?></span></div>
    		</div>
		</div>
	</body>
</html>