<?php
	define( "LOCALE_SETUP", true );
	require_once(dirname(__FILE__)."/../lib/private/locale.php");
	include_once(dirname(__FILE__)."/../lib/config/config.phpbb3.php");
	
	if ( defined("PHPBB3_DATABASE") )
	{
		require_once(dirname(__FILE__)."/../lib/private/connector.class.php");
	
		$Connector = new Connector(SQL_HOST, PHPBB3_DATABASE, PHPBB3_USER, PHPBB3_PASS); 
		$Groups = $Connector->prepare( "SELECT group_id, group_name FROM `".PHPBB3_TABLE_PREFIX."groups` ORDER BY group_name" );
		
		$Groups->execute();		
		$phpBBGroups = Array();
		
		while ( $Group = $Groups->fetch( PDO::FETCH_ASSOC ) )
		{
			array_push( $phpBBGroups, $Group );
		}		 
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
	<head>
		<title>Raidplaner config</title>
        <meta http-equiv="X-UA-Compatible" content="IE=Edge">
        
        <script type="text/javascript" src="../lib/script/jquery-1.5.1.min.js"></script>
        <script type="text/javascript" src="step2.js.php"></script>
    </head>
	
	<body style="font-family: helvetica, arial, sans-serif; font-size: 11px; line-height: 1.5em; background-color: #cccccc; color: black">
		<div style="width: 800px; height: 600px; position: fixed; left: 50%; top: 50%; margin-left: -400px; margin-top: -300px; background-color: white">
			<div style="background-color: black; color: white; padding: 10px">
				Packedpixel<br/>
				<span style="font-size: 24px">Raidplaner setup (2/3)</span>
			</div>
			<div style="padding: 20px">
				<div>
					<div>
						<h2><?php echo L("PHPBB3 binding"); ?></h2>
						<input type="checkbox" id="allow_phpbb3"<?php echo (defined("PHPBB3_BINDING") && PHPBB3_BINDING) ? " checked=\"checked\"": "" ?>/> <?php echo L("Allow users to login using their PHPBB3 account"); ?><br/>
						<br/>
						<input type="text" id="database" value="<?php echo (defined("PHPBB3_DATABASE")) ? PHPBB3_DATABASE : "packedpixel" ?>"/> <?php echo L("PHPBB3 database"); ?><br/>
						<input type="text" id="user" value="<?php echo (defined("PHPBB3_TABLE")) ? PHPBB3_USER : "root" ?>"/> <?php echo L("User with permissions for that database"); ?><br/>
						<input type="password" id="password"/> <?php echo L("Password for that user"); ?><br/>
						<input type="password" id="password_check"/> <?php echo L("Please repeat the password"); ?><br/>
						<br/>
						<input type="text" id="prefix" value="<?php echo (defined("PHPBB3_TABLE_PREFIX")) ? PHPBB3_TABLE_PREFIX : "phpbb_" ?>"/> <?php echo L("Prefix for tables in the database"); ?><br/>
					</div>
					
					<div style="margin-top: 3em">
						<h2><?php echo L("PHPBB3 group conversion"); ?> <button onclick="reloadGroups()" style="margin-left: 20px; position: relative; top: -2px"><?php echo L("Load groups using these settings"); ?></button></h2>
						
						<?php echo L("Users of the following groups gain \"member\" rights upon first login"); ?><br/>
						<select id="member" multiple="multiple" style="width: 400px; height: 6.5em">
						<?php
							if ( defined("PHPBB3_DATABASE") )
							{
								$GroupIds = array();
								
								if ( defined("PHPBB3_MEMBER_GROUPS") )
									$GroupIds = explode( ",", PHPBB3_MEMBER_GROUPS );
								
								foreach( $phpBBGroups as $Group )
								{
									echo "<option value=\"".$Group["group_id"]."\"".((in_array($Group["group_id"], $GroupIds)) ? " selected=\"selected\"" : "" ).">".$Group["group_name"]."</option>";
								}
							}
						?>
						</select>
						<br/><br/>
						<?php echo L("Users of the following groups gain \"raidlead\" rights upon first login"); ?><br/>
						<select id="raidlead" multiple="multiple" style="width: 400px; height: 6.5em">
						<?php
							if ( defined("PHPBB3_DATABASE") )
							{
								$GroupIds = array();
								
								if ( defined("PHPBB3_RAIDLEAD_GROUPS") )
									$GroupIds = explode( ",", PHPBB3_RAIDLEAD_GROUPS );
								
								foreach( $phpBBGroups as $Group )
								{
									echo "<option value=\"".$Group["group_id"]."\"".((in_array($Group["group_id"], $GroupIds)) ? " selected=\"selected\"" : "" ).">".$Group["group_name"]."</option>";
								}
							}
						?>
						</select>
					</div>
					
					<div style="position: fixed; right: 50%; top: 50%; margin-right: -380px; margin-top: 260px">
						<button onclick="checkForm()"><?php echo L("Save and continue"); ?></button>
					</div>					
				</div>
			</div>
		</div>
	</body>
</html>