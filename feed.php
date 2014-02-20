<?php

    // Raidplaner RSS Feed
    // This is a demo implementation of an RSS feed, but it can be used as is, too.
    //
    // Usage:
    // feed.php?token=<private token>&timezone=<timezone>
    //
    // Timezone is optional and has to be compatible to date_default_timezone_set().

    require_once("lib/private/api.php");
    require_once("lib/private/out.class.php");

    header("Content-type: application/xml");
    echo '<?xml version="1.0" encoding="utf-8"?>';
    echo '<rss version="2.0">';
    
    // Build RSS header
    
    $RFC2822 = "D, d M Y H:i:s O";
    $BaseURL = getBaseURL();
        
    $Out = new Out();
    $Out->pushValue("title",        "Raidplaner RSS feed");
    $Out->pushValue("link",         $BaseURL."index.php");
    $Out->pushValue("description",  "Upcoming raids for the next 2 weeks.");
    $Out->pushValue("language",     "en-en");
    $Out->pushValue("copyright",    "packedpixel");
    $Out->pushValue("pubDate",      date($RFC2822));
    
    // Requires private token to be visible
    
    $Token = (isset($_REQUEST["token"])) 
        ? $_REQUEST["token"] 
        : null;
    
    if (Api::testPrivateToken($Token))
    {
        // Setting the correct timezones
        
        $Timezone  = (isset($_REQUEST["timezone"])) 
            ? $_REQUEST["timezone"] 
            : date_default_timezone_get();
    
        date_default_timezone_set('UTC');
        
        // Query API
        
        $Parameters = Array(
            "start"    => time() - 24 * 60 * 60,
            "end"      => time() + 14 * 24 * 60 * 60,
            "limit"    => 0,
            "closed"   => true,
            "canceled" => true,
        );
        
        $Locations = Api::queryLocation(null);
        $Raids     = Api::queryRaid($Parameters);
        
        // Location lookup table
        
        $LocationName = Array();
        foreach($Locations as $Location)
        {
            $LocationName[$Location["Id"]] = $Location["Name"];
        }
        
        // Generate RSS content
        
        foreach($Raids as $Raid)
        {
            date_default_timezone_set($Timezone);
    
            $Start = date("H:i", intval($Raid["Start"]));
            $End   = date("H:i", intval($Raid["End"]));
            
            date_default_timezone_set('UTC');
    
            $Out->pushValue("item", Array(
                "title"       => $LocationName[$Raid["LocationId"]]. " (".$Raid["Size"].")",
                "description" => "Status: ".$Raid["Status"]."\nFrom ".$Start." to ".$End."\n".$Raid["Description"],
                "link"        => $BaseURL."index.php#raid,".$Raid["RaidId"],
                "author"      => "Raidplaner",
                "guid"        => $Raid["RaidId"],
                "pubDate"     => date($RFC2822, intval($Raid["Start"]))
            ));
        }
    }
    
    $Out->flushXML("channel");
    echo '</rss>';
?>