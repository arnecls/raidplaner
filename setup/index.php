<?php
	define( "LOCALE_SETUP", true );
	require_once(dirname(__FILE__)."/../lib/private/locale.php");
	@include_once(dirname(__FILE__)."/../lib/config/config.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
	<head>
		<title>Raidplaner config</title>
        <meta http-equiv="X-UA-Compatible" content="IE=Edge">
        
        <script type="text/javascript" src="../lib/script/jquery-1.7.1.min.js"></script>
        <script type="text/javascript" src="script/main.js"></script>
        
    </head>
	
	<body style="font-family: helvetica, arial, sans-serif; font-size: 11px; line-height: 1.5em; background-color: #cccccc; color: black">
		<div style="width: 800px; height: 600px; position: fixed; left: 50%; top: 50%; margin-left: -400px; margin-top: -300px; background-color: white">
			<div style="background-color: black; color: white; padding: 10px">
				Packedpixel<br/>
				<span style="font-size: 24px">Raidplaner setup (1/3)</span>
			</div>
			<div style="padding: 20px">
				<div>
					<h2><?php echo L("Filesystem permission checks"); ?></h2>
					<?php echo L("The raidplaner needs a PHP 5.2 installation configured with the mcrypt and PDO extensions."); ?><br/>
					<?php echo L("Setup needs write permission on all files in the config folder located at ")."\"lib / config\"."; ?><br/>
					<?php echo L("If any of these checks fails you have to change permissions to \"writeable\" for your http server's user."); ?><br/>
					<?php echo L("On how to change permissions, please consult your FTP client's helpfiles."); ?><br/><br/>
					<br/>
					
					<?php echo L("PHP version"); ?> : <?php
						$version = explode('.', phpversion());
						$testsFailed = 0;
						
						if ( ($version[0] > 5) || ($version[0] == 5 && $version[1] >= 2) )
							echo "<span style=\"color: green\">".L("Ok");
						else
						{
							++$testsFailed;
							echo "<span style=\"color: red\">".L("Outdated PHP version");
						}
							
						echo " (".phpversion().")</span>";
					?><br/>
					
					<?php echo L("PDO module"); ?> : <?php
						$extensions = get_loaded_extensions();
						
						if ( in_array("PDO", $extensions) )
							echo "<span style=\"color: green\">".L("Ok")."</span>";
						else
						{
							++$testsFailed;
							echo "<span style=\"color: red\">".L("PDO not configured with PHP")."</span>";
						}					
					?><br/>
					
					<?php echo L("mcrypt module"); ?> : <?php
						if ( in_array("mcrypt", $extensions) )
							echo "<span style=\"color: green\">".L("Ok")."</span>";
						else
						{
							++$testsFailed;
							echo "<span style=\"color: red\">".L("Mcrypt not configured with PHP")."</span>";
						}
					?><br/><br/>
					
					<?php echo L("Config folder"); ?> : <?php
						$configFolderState = is_writable("../lib/config");
						
						if ( $configFolderState )
							echo "<span style=\"color: green\">".L("Ok")."</span>";
						else
						{
							++$testsFailed;
							echo "<span style=\"color: red\">".L("Not writeable")."</span>";
						}
					?><br/>
					
					<?php echo L("Main config file"); ?> : <?php
						$configFileState = (!file_exists("../lib/config/config.php") && $configFolderState) || 
											is_writable("../lib/config/config.php");
						
						if ( $configFileState )
							echo "<span style=\"color: green\">".L("Ok")."</span>";
						else
						{
							++$testsFailed;
							echo "<span style=\"color: red\">".L("Not writeable")."</span>";
						}
					?><br/>
					
					<?php echo L("PHPBB3 config file"); ?> : <?php
						$phpbbConfigFileState = (!file_exists("../lib/config/config.phpbb3.php") && $configFolderState) || 
												is_writable("../lib/config/config.phpbb3.php");
											
						if ( $phpbbConfigFileState )
							echo "<span style=\"color: green\">".L("Ok")."</span>";
						else
						{
							++$testsFailed;
							echo "<span style=\"color: red\">".L("Not writeable")."</span>";
						}
					?><br/>
					
					<?php echo L("EQDKP config file"); ?> : <?php
						$phpbbConfigFileState = (!file_exists("../lib/config/config.eqdkp.php") && $configFolderState) || 
												is_writable("../lib/config/config.eqdkp.php");
											
						if ( $phpbbConfigFileState )
							echo "<span style=\"color: green\">".L("Ok")."</span>";
						else
						{
							++$testsFailed;
							echo "<span style=\"color: red\">".L("Not writeable")."</span>";
						}
					?><br/>
					
					<?php echo L("vBulletin config file"); ?> : <?php
						$phpbbConfigFileState = (!file_exists("../lib/config/config.vb3.php") && $configFolderState) || 
												is_writable("../lib/config/config.vb3.php");
											
						if ( $phpbbConfigFileState )
							echo "<span style=\"color: green\">".L("Ok")."</span>";
						else
						{
							++$testsFailed;
							echo "<span style=\"color: red\">".L("Not writeable")."</span>";
						}
					?><br/>
				</div>
				
				<?php 
					if ( $testsFailed == 0)
					{
				?>
				<div style="position: fixed; right: 50%; top: 50%; margin-right: -380px; margin-top: 260px">
					<button onclick="loadSetupDb()"><?php echo L("Continue"); ?></button>
				</div>			
				<?php
					} // if (permissions ok)
				?>
			</div>
		</div>
	</body>
</html>