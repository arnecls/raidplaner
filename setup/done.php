<?php
	define( "LOCALE_SETUP", true );
	require_once(dirname(__FILE__)."/../lib/private/locale.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
	<head>
		<title>Raidplaner config</title>
        <meta http-equiv="X-UA-Compatible" content="IE=Edge">
        
        <script type="text/javascript" src="../lib/script/jquery-1.5.2.min.js"></script>
    </head>
	
	<body style="font-family: helvetica, arial, sans-serif; font-size: 11px; line-height: 1.5em; background-color: #cccccc; color: black">
		<div style="width: 800px; height: 600px; position: fixed; left: 50%; top: 50%; margin-left: -400px; margin-top: -300px; background-color: white">
			<div style="background-color: black; color: white; padding: 10px">
				Packedpixel<br/>
				<span style="font-size: 24px">Raidplaner setup</span>
			</div>
			<div style="padding: 20px">
				<div>
					<h2><?php echo L("Setup complete"); ?></h2>
					<?php 
						echo L("Raidplaner has been successfully set up.")."<br/>";
						echo L("You should now delete the setup folder and secure the following folders:")."<br/>";
						echo "<br/>";
						echo "lib / private<br/>";
						echo "lib / config<br/>";
						echo "<br/>";
						echo L("Thank you for using packedpixel Raidplaner.")."<br/>";
						echo L("If you encounter any bugs or if you have feature requests, please visit our bugtracker at ");
						echo "<a href=\"http://code.google.com/p/ppx-raidplaner/issues/list\">Google Code</a>."
					?>
				</div>
			</div>
		</div>
	</body>
</html>