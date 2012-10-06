<?php
	require_once("lib/private/locale.php");
	require_once("lib/private/browser.php");

	function isSupportedVersion()
	{
		$browserInfo = new Browser();
	
		switch ( $browserInfo->getBrowser() )
		{
		case Browser::BROWSER_SAFARI:
			return $browserInfo->getVersion() >= 4;
			break;
			
		case Browser::BROWSER_FIREFOX:
			return $browserInfo->getVersion() >= 3;
			break;
			
		case Browser::BROWSER_IE:
			return $browserInfo->getVersion() >= 7;
			break;
			
		case Browser::BROWSER_CHROME:
			return $browserInfo->getVersion() >= 4;
			break;
			
		case Browser::BROWSER_OPERA:
		    return intval($browserInfo->getVersion()) >= 11;
			break;
		}
		
		return true;
	}

	if ( !isSupportedVersion() )
	{
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
	<head>
		<title><?php echo L("UpdateBrowser"); ?></title>
        <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    </head>
    <body style="text-align: center; font-family: Helvetica, Arial, sans-serif; font-size: 14px; line-height: 1.8em;">
        <div style="width: 600px; height: 460px; position: fixed; left: 50%; top: 50%; margin-left: -300px; margin-top: -230px">
    		<img src="lib/layout/images/alert.png" style="margin-bottom: 20px"/><br/>
    		<div style="font-size: 20px; font-weight: bold"><?php echo L("UpdateBrowser"); ?></div><br/>
    		<?php echo L("UsingOldBrowser"); ?><br/>
    		<?php echo L("OlderBrowserFeatures"); ?><br/>
    		<?php echo L("DownloadNewBrowser"); ?><br/>
    		<div style="position: relative; left: 50%; margin-left: -220px; width: 440px; margin-top: 20px; font-size: 11px; line-height: 1.5em;">
    			<span style="float: left;"><a href="http://www.google.com/chrome"><img src="lib/layout/images/chrome64.png" border="0"/><br/>Google Chrome</a></span>
    			<span style="float: left; margin-left: 20px"><a href="http://www.mozilla.com/de/firefox/"><img src="lib/layout/images/firefox64.png" border="0"/><br/>Firefox</a></span>
    			<span style="float: left; margin-left: 20px"><a href="http://www.microsoft.com/windows/internet-explorer/default.aspx"><img src="lib/layout/images/explorer64.png" border="0"/><br/>Internet Explorer</a></span>			
    			<span style="float: left; margin-left: 20px"><a href="http://www.opera.com/"><img src="lib/layout/images/opera64.png" border="0"/><br/>Opera</a></span>			
    			<span style="float: left; margin-left: 20px"><a href="http://www.apple.com/de/safari/"><img src="lib/layout/images/safari64.png" border="0"/><br/>Safari</a></span>
    		</div>
    		<div style="clear: left">
    			<br/>
    			<a href="index.php?nocheck"><?php echo L("ContinueNoUpdate"); ?></a>
    		</div>
    	</div>
    </body>
</html>
<?php die(); } ?>