<?php
	define( "LOCALE_SETUP", true );
	require_once(dirname(__FILE__)."/../lib/private/locale.php");
	include_once(dirname(__FILE__)."/../lib/config/config.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
	<head>
		<title>Raidplaner config</title>
        <meta http-equiv="X-UA-Compatible" content="IE=Edge">
        
        <script type="text/javascript" src="../lib/script/jquery-1.5.2.min.js"></script>
        <script type="text/javascript" src="step1.js.php"></script>
        
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
					<?php echo L("Setup needs write permission on all files in the config folder located at ")."\"lib / config\"."; ?><br/>
					<?php echo L("If any of these checks fails you have to change permissions to \"writeable\" for your http server's user."); ?><br/>
					<?php echo L("On how to change permissions, please consult your FTP client's helpfiles."); ?><br/>
					<br/>
					<?php echo L("Config folder"); ?> : <?php
						$configFolderState = is_writable("../lib/config");
						
						if ( $configFolderState )
							echo "<span style=\"color: green\">".L("Ok")."</span>";
						else
							echo "<span style=\"color: red\">".L("Not writeable")."</span>";
					?><br/>
					
					<?php echo L("Main config file"); ?> : <?php
						$configFileState = (!file_exists("../lib/config/config.php") && $configFolderState) || 
											is_writable("../lib/config/config.php");
						
						if ( $configFileState )
							echo "<span style=\"color: green\">".L("Ok")."</span>";
						else
							echo "<span style=\"color: red\">".L("Not writeable")."</span>";
					?><br/>
					
					<?php echo L("PHPBB3 config file"); ?> : <?php
						$phpbbConfigFileState = (!file_exists("../lib/config/config.phpbb3.php") && $configFolderState) || 
												is_writable("../lib/config/config.phpbb3.php");
											
						if ( $phpbbConfigFileState )
							echo "<span style=\"color: green\">".L("Ok")."</span>";
						else
							echo "<span style=\"color: red\">".L("Not writeable")."</span>";
					?><br/>
				</div>
				
				<?php 
					if ( $configFolderState && $configFileState && $phpbbConfigFileState )
					{
				?>
				<div style="margin-top: 1.5em">
					<h2><?php echo L("Database connection"); ?></h2>
					<input type="text" id="host" value="<?php echo (defined("SQL_HOST")) ? SQL_HOST : "localhost" ?>"/> <?php echo L("Database host"); ?><br/>
					<input type="text" id="database" value="<?php echo (defined("RP_USER")) ? RP_DATABASE : "raidplaner" ?>"/> <?php echo L("Raidplaner database"); ?><br/>
					<input type="text" id="user" value="<?php echo (defined("RP_USER")) ? RP_USER : "root" ?>"/> <?php echo L("User with permissions for that database"); ?><br/>
					<input type="password" id="password"/> <?php echo L("Password for that user"); ?><br/>
					<input type="password" id="password_check"/> <?php echo L("Please repeat the password"); ?><br/>
					<br/>
					<input type="text" id="prefix" value="<?php echo (defined("RP_TABLE_PREFIX")) ? RP_TABLE_PREFIX : "table_" ?>"/> <?php echo L("Prefix for tables in the database"); ?><br/>
				</div>
				
				<div style="margin-top: 1.5em">
					<h2 style="margin-top: 1.5em"><?php echo L("Advanced options"); ?></h2>
					<input type="checkbox" id="allow_registration"<?php echo (!defined("ALLOW_REGISTRATION") || ALLOW_REGISTRATION) ? " checked=\"checked\"" : "" ?>/> <?php echo L("Allow users to register manually"); ?><br/>
					<input type="password" id="admin_password"/> <?php echo L("Password for the admin user"); ?><br/>
					<input type="password" id="admin_password_check"/> <?php echo L("Please repeat the password"); ?><br/>
				</div>	
				
				<div style="position: fixed; right: 50%; top: 50%; margin-right: -380px; margin-top: 260px">
					<button onclick="checkForm()"><?php echo L("Save and continue"); ?></button>
				</div>			
				<?php
					} // if (permissions ok)
				?>
			</div>
		</div>
	</body>
</html>