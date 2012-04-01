<?php
	define( "LOCALE_MAIN", true );
	require_once("lib/private/locale.php");
	
    if ( !isset($_REQUEST["nocheck"]) )
	{
		include_once("oldbrowser.php");
    }
    
    if ( !file_exists("lib/config/config.php") )
    {
    	die( L("Raidplaner is not yet configured.")."<br>".L("Please run setup or follow the manual installation instructions.") );
    }
    
    require_once("lib/private/users.php");
    
    UserProxy::GetInstance(); // Init user
    $siteVersion = "0.9.4";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
	<head>
		<title>Raidplaner</title>
        <meta http-equiv="X-UA-Compatible" content="IE=Edge">
        
		<link rel="stylesheet" type="text/css" href="lib/layout/default.css?version=<?php echo $siteVersion; ?>"/>
		<link rel="stylesheet" type="text/css" href="lib/layout/combobox.css?version=<?php echo $siteVersion; ?>"/>
		<link rel="stylesheet" type="text/css" href="lib/layout/calendar.css?version=<?php echo $siteVersion; ?>"/>
		<link rel="stylesheet" type="text/css" href="lib/layout/raid.css?version=<?php echo $siteVersion; ?>"/>
		<link rel="stylesheet" type="text/css" href="lib/layout/raidlist.css?version=<?php echo $siteVersion; ?>"/>
		<link rel="stylesheet" type="text/css" href="lib/layout/profile.css?version=<?php echo $siteVersion; ?>"/>
		<link rel="stylesheet" type="text/css" href="lib/layout/jquery-ui-1.8.18.custom.css"/>
		<link rel="stylesheet" type="text/css" href="lib/layout/tooltip.css?version=<?php echo $siteVersion; ?>"/>
		<link rel="stylesheet" type="text/css" href="lib/layout/shadow.css?version=<?php echo $siteVersion; ?>"/>
		<link rel="stylesheet" type="text/css" href="lib/layout/sheet.css?version=<?php echo $siteVersion; ?>"/>
		
		
        <!--[if IE 9]>
		<link rel="stylesheet" type="text/css" href="lib/layout/shadowIE.css?version=<?php echo $siteVersion; ?>"/>
		<![endif]-->
        
		<!--[if IE 8]>
		<link rel="stylesheet" type="text/css" href="lib/layout/tooltipIE.css?version=<?php echo $siteVersion; ?>"/>
		<link rel="stylesheet" type="text/css" href="lib/layout/shadowIE.css?version=<?php echo $siteVersion; ?>"/>
		<link rel="stylesheet" type="text/css" href="lib/layout/sheetIE.css?version=<?php echo $siteVersion; ?>"/>
		<![endif]-->
        
        <!--[if IE 7]>
		<link rel="stylesheet" type="text/css" href="lib/layout/tooltipIE.css?version=<?php echo $siteVersion; ?>"/>
		<link rel="stylesheet" type="text/css" href="lib/layout/shadowIE.css?version=<?php echo $siteVersion; ?>"/>
		<link rel="stylesheet" type="text/css" href="lib/layout/sheetIE.css?version=<?php echo $siteVersion; ?>"/>
		<![endif]-->
		
		<?php if ( ValidAdmin() ) { ?>
		<link rel="stylesheet" type="text/css" href="lib/layout/settings.css?version=<?php echo $siteVersion; ?>"/>
		<?php } ?>
		
		<script type="text/javascript" src="lib/script/jquery-1.7.2.min.js"></script>
		<script type="text/javascript" src="lib/script/jquery-ui-1.8.18.custom.min.js"></script>
		<script type="text/javascript" src="lib/script/jquery.ba-hashchange.min.js"></script>
		
		<script type="text/javascript" src="lib/script/locale.js.php?version=<?php echo $siteVersion; ?>"></script>		
		
		<script type="text/javascript" src="lib/script/user.js.php?version=<?php echo $siteVersion; ?>"></script>
		<script type="text/javascript" src="lib/script/calendarsession.js.php?version=<?php echo $siteVersion; ?>"></script>
		
		<script type="text/javascript" src="lib/script/calendar.js?version=<?php echo $siteVersion; ?>"></script>
		<script type="text/javascript" src="lib/script/combobox.js?version=<?php echo $siteVersion; ?>"></script>
		<script type="text/javascript" src="lib/script/messagehub.js?version=<?php echo $siteVersion; ?>"></script>
		<script type="text/javascript" src="lib/script/sheet.js?version=<?php echo $siteVersion; ?>"></script>
		<script type="text/javascript" src="lib/script/tooltip.js?version=<?php echo $siteVersion; ?>"></script>
		<script type="text/javascript" src="lib/script/raid.js?version=<?php echo $siteVersion; ?>"></script>
		<script type="text/javascript" src="lib/script/raidlist.js?version=<?php echo $siteVersion; ?>"></script>
		<script type="text/javascript" src="lib/script/profile.js?version=<?php echo $siteVersion; ?>"></script>
		<script type="text/javascript" src="lib/script/main.js?version=<?php echo $siteVersion; ?>"></script>
		<?php if ( ValidAdmin() ) { ?>
		<script type="text/javascript" src="lib/script/settings.js?version=<?php echo $siteVersion; ?>"></script>
		<?php } ?>
		
		<?php if ( RegisteredUser() ) { ?>    	
    	<script type="text/javascript" src="lib/script/initmenu.js?version=<?php echo $siteVersion; ?>"></script>
    	<?php } else { ?>
    	
    	<script type="text/javascript" src="lib/script/login.js?version=<?php echo $siteVersion; ?>"></script>
    		<?php if ( ALLOW_REGISTRATION ) { ?>	
		<script type="text/javascript" src="lib/script/register.js?version=<?php echo $siteVersion; ?>"></script>
			<?php } ?>			
		<script type="text/javascript" src="lib/script/initlogin.js?version=<?php echo $siteVersion; ?>"></script>
    	
    	<?php } ?>
 	</head>
	
	<body>
		<div style="width: 1024px; height: 1px">&nbsp;</div>
		<div id="appwindow">
			<?php 
			
				$Connector = Connector::GetInstance();
				$Settings = $Connector->prepare("Select `Name`, `TextValue` FROM `".RP_TABLE_PREFIX."Setting` WHERE Name=\"Site\" OR Name=\"Banner\"");
    	
		        if ( $Settings->execute() )
		        {
		        	$Values = array( "Site" => "", "Banner" => "cata" );	        
			        while ( $Data = $Settings->fetch( PDO::FETCH_ASSOC ) )
			        {
			        	$Values[$Data["Name"]] = $Data["TextValue"];
			        }
			        
			        if ( $Values["Site"] == "" )
			       	{
			        	echo "<div id=\"logo\" class=\"logo_".$Values["Banner"]."\"></div>";
			        }
			        else
			        {			        
			        	echo "<a href=\"".$Values["Site"]."\" id=\"landingPage\"><div id=\"logo\" class=\"logo_".$Values["Banner"]."\"></div></a>";
			       	}
			    }
			    else
			   	{
			   		echo "<div id=\"logo\" class=\"logo_cata\"></div>";
			   	}
			    	
		        $Settings->closeCursor();
				
			?>
    		<div id="menu">
    			<?php if ( RegisteredUser() ) { ?>
    			
    			<span class="logout">
    				<form method="post" action="index.php">
    					<input type="hidden" name="nocheck"/>
    					<input type="hidden" name="logout"/>
    					<button onclick="submit()" class="button_logout"><?php echo L("Logout"); ?></button>
    				</form>
    			</span>
                <span id="button_calendar" class="menu_button"><?php echo L("Calendar"); ?></span>
    			<span id="button_raid" class="menu_button"><?php echo L("Raid"); ?></span>
    			<span id="button_profile" class="menu_button"><?php echo L("Profile"); ?></span>
    			
    				<?php if ( ValidAdmin() ) { ?>
    			<span id="button_settings" class="menu_button"><?php echo L("Settings"); ?></span>
    				<?php } ?>
    			
    			<?php } else { ?>
    			
    			<span id="button_login" class="menu_button"><?php echo L("Login"); ?></span>
    				<?php if ( ALLOW_REGISTRATION ) { ?>
    			<span id="button_register" class="menu_button"><?php echo L("Register"); ?></span>
    				<?php } ?>
    			
    			<?php } ?>
    		</div>
    		<div id="body">
    			<?php 
    				if ( !ValidUser() && RegisteredUser() )
    				{
    					echo "<div id=\"lockMessage\">";
    					echo L("Your account is currently locked.")."<br/>";
    					echo L("Please contact your admin to get your account unlocked.");
    					echo "</div>";
    				}
    			?>
    		</div>
    		<span id="version"><?php echo "version ".$siteVersion; ?></span>
		</div>
		
		<div id="eventblocker"></div>
		<div id="dialog"></div>
		<div id="ajaxblocker">
			<div class="background ui-corner-all">
				<img src="lib/layout/images/busy.gif"/><br/><br/>
				<?php echo L("Busy. Please wait."); ?>
			</div>
		</div>
		
		<?php if ( RegisteredUser() ) { ?>
		
		<table id="tooltip" cellspacing="0" border="0">
			<tr class="top">
				<td class="left"></td>
				<td class="center" id="info_arrow_tl"></td>
                <td class="center" id="info_arrow_tr"></td>
				<td class="right"></td>
			</tr>
			<tr class="middle">
				<td class="left" id="info_arrow_ml"></td>
				<td class="center" colspan="2" rowspan="2" id="info_text"></td>
				<td class="right"></td>
			</tr>
			<tr class="middle2">
				<td class="left" id="info_arrow_ml2"></td>
				<td class="right"></td>
			</tr>
			<tr class="bottom">
				<td class="left"></td>
				<td class="center" id="info_arrow_bl"></td>	
               <td class="center" id="info_arrow_br"></td>
				<td class="right"></td>
			</tr>
		</table>
		
		<table id="sheetoverlay" cellspacing="0" border="0">
			<tr class="top">
				<td class="left" id="closesheet"></td>
				<td class="center"></td>				
				<td class="right"></td>
			</tr>
			<tr class="middle">
				<td class="left"></td>
				<td class="center" id="sheet_body"></td>
				<td class="right"></td>
			</tr>
			<tr class="bottom">
				<td class="left"></td>
				<td class="center"></td>
				<td class="right"></td>
			</tr>
		</table>
		
		<?php } ?>		
		<?php if ( ValidRaidlead() ) { ?>
		
		<div id="sheetNewRaid">
			<div id="newRaid">
				<span style="display: inline-block; vertical-align: top; margin-right: 20px" id="raiddatepicker"></span>	
				<span style="display: inline-block; vertical-align: top">
					<span style="display: inline-block; margin-right: 5px" class="imagepicker" id="locationimagepicker"><div class="imagelist" id="locationimagelist"></div></span>
					<span style="display: inline-block; vertical-align: top">
						<div style="margin-bottom: 10px">
							<select id="selectlocation" onchange="onLocationChange(this)">
								<option value="0"><?php echo L("New dungeon"); ?></option>
							</select>
							<select id="selectsize" style="width: 48px">
								<option value="10">10</option>
								<option value="25">25</option>
							</select>					
						</div>
						<div style="margin-bottom: 10px">
							<select id="starthour" style="width: 48px">
								<option value="4">4</option>
								<option value="3">3</option>
								<option value="2">2</option>
								<option value="1">1</option>
								<option value="0">24</option>
								<option value="23">23</option>
								<option value="22">22</option>
								<option value="21">21</option>
								<option value="20">20</option>
								<option value="19">19</option>
								<option value="18">18</option>
								<option value="17">17</option>
								<option value="16">16</option>
								<option value="15">15</option>
								<option value="14">14</option>
								<option value="13">13</option>
								<option value="12">12</option>
								<option value="11">11</option>
								<option value="10">10</option>
								<option value="9">9</option>
								<option value="8">8</option>
								<option value="7">7</option>
								<option value="6">6</option>
								<option value="5">5</option>
							</select>
							<span>:</span>
							<select id="startminute" style="width: 48px">
								<option value="0">00</option>
								<option value="15">15</option>
								<option value="30">30</option>
								<option value="45">45</option>
							</select>
							<span style="display: inline-block; width: 29px; text-align:center"><?php echo L("to"); ?></span>
							<select id="endhour" style="width: 48px">
								<option value="4">4</option>
								<option value="3">3</option>
								<option value="2">2</option>
								<option value="1">1</option>
								<option value="0">24</option>
								<option value="23">23</option>
								<option value="22">22</option>
								<option value="21">21</option>
								<option value="20">20</option>
								<option value="19">19</option>
								<option value="18">18</option>
								<option value="17">17</option>
								<option value="16">16</option>
								<option value="15">15</option>
								<option value="14">14</option>
								<option value="13">13</option>
								<option value="12">12</option>
								<option value="11">11</option>
								<option value="10">10</option>
								<option value="9">9</option>
								<option value="8">8</option>
								<option value="7">7</option>
								<option value="6">6</option>
								<option value="5">5</option>
							</select>
							<span>:</span>
							<select id="endminute" style="width: 48px">
								<option value="0">00</option>
								<option value="15">15</option>
								<option value="30">30</option>
								<option value="45">45</option>
							</select>
						</div>
					</span>
					<div style="margin-bottom: 10px">
						<textarea id="descriptiondummy" class="textdummy description"><?php echo L("Description"); ?></textarea>
						<textarea id="description" class="textinput description"></textarea>
					</div>
					<button id="newRaidSubmit"><?php echo L("Create raid"); ?></button>
				</span>
			</div>			
		</div>	
		<?php } ?>
		<?php if ( !RegisteredUser() ) { ?>
		<div class="preload"><?php include("lib/private/resources.php"); ?></div>
		<?php } ?>
		
	</body>
</html>
