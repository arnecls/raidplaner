<?php
    require_once(dirname(__FILE__)."/connector.class.php");
    
    $gSite = Array(
        "Version"     => 109.0,
        "BannerLink"  => "",
        "HelpLink"    => "",
        "Banner"      => "cataclysm.jpg",
        "Background"  => "flower.png",
        "BGColor"     => "#898989",
        "BGRepeat"    => "repeat-xy",
        "PortalMode"  => false,
        "TimeFormat"  => 24,
        "StartOfWeek" => 1
    );
    
    // ---------------------------------------------------------------
    
    function include_once_exists($aFile)
    {
        if (file_exists($aFile))
            include_once($aFile);
    }
    
    // ---------------------------------------------------------------

    function loadSiteSettings()
    {
        global $gSite;
        
        $Out = Out::getInstance();
        $Connector = Connector::getInstance();
        $Settings = $Connector->prepare("Select `Name`, `TextValue`, `IntValue` FROM `".RP_TABLE_PREFIX."Setting`");
    
        $gSite["BannerLink"]  = "";
        $gSite["HelpLink"]    = "";
        $gSite["Banner"]      = "cataclysm.jpg";
        $gSite["Background"]  = "flower.png";
        $gSite["BGColor"]     = "#898989";
        $gSite["BGRepeat"]    = "repeat-xy";
        $gSite["PortalMode"]  = false;
        $gSite["TimeFormat"]  = 24;
        $gSite["StartOfWeek"] = 1;
        
        $Settings->loop( function($Data) use (&$gSite)
        {
            switch( $Data["Name"] )
            {
            case "Site":
                $gSite["BannerLink"] = $Data["TextValue"];
                break;
                
            case "HelpPage":
                $gSite["HelpLink"] = $Data["TextValue"];
                break;

            case "Theme":
                $ThemeFile = dirname(__FILE__)."/../../images/themes/".$Data["TextValue"].".xml";

                if ( file_exists($ThemeFile) )
                {
                    try
                    {
                        $Theme = @new SimpleXMLElement( file_get_contents($ThemeFile) );
                        $gSite["Banner"]     = (string)$Theme->banner;
                        $gSite["Background"] = (string)$Theme->bgimage;
                        $gSite["BGColor"]    = (string)$Theme->bgcolor;
                        $gSite["BGRepeat"]   = (string)$Theme->bgrepeat;
                        $gSite["PortalMode"] = ((string)$Theme->portalmode) == "true";
                    }
                    catch(Exception $e)
                    {
                        $Out->pushError("Error parsing themefile ".$Data["TextValue"].": ".$e->getMessage());
                    }
                }
                break;

            case "TimeFormat":
                $gSite["TimeFormat"] = $Data["IntValue"];
                break;

            case "StartOfWeek":
                $gSite["StartOfWeek"] = $Data["IntValue"];
                break;

            default:
                break;
            };
        });
    } 
    
    // ---------------------------------------------------------------
    
    function beginSession()
    {
        ini_set("session.use_trans_sid",    0);
        ini_set("session.use_cookies",      1);
        ini_set("session.use_only_cookies", 1);
        ini_set("session.cookie_httponly",  1);
        ini_set("session.hash_function",    1);
        ini_set("session.bug_compat_42",    0);
        
        $SiteId = dechex(crc32(dirname(__FILE__)));

        session_name("ppx_raidplaner_".$SiteId);
        session_start();
    }
    
    // ---------------------------------------------------------------
    
    function checkVersion($aSiteVersion)
    {
        try
        {
            $Connector = Connector::getInstance(true);
            $VersionQuery = $Connector->prepare("SELECT IntValue FROM `".RP_TABLE_PREFIX."Setting` WHERE Name = 'Version' LIMIT 1" );
            $Result = $VersionQuery->fetchFirst(); 
                
            if ($Result != null)
                return intval(intval($aSiteVersion) / 10) == intval(intval($Result["IntValue"]) / 10);
        }
        catch(PDOException $Exception)
        {
        }
        
        return false;        
    }
    
    // ---------------------------------------------------------------
    
    function lockOldRaids( $aSeconds )
    {
        if ( validUser() )
        {
            $Connector = Connector::getInstance();
            $UpdateRaidQuery = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."Raid` SET ".
                                                   "Stage = 'locked'".
                                                   "WHERE Start < FROM_UNIXTIME(:Time) AND Stage = 'open'" );

            $UpdateRaidQuery->bindValue(":Time", time() + $aSeconds, PDO::PARAM_INT);
            $UpdateRaidQuery->execute();
        }
    }
    
    // ---------------------------------------------------------------

    function purgeOldRaids( $aSeconds )
    {
        $Connector = Connector::getInstance();
        $DropRaidQuery = $Connector->prepare( "DELETE `".RP_TABLE_PREFIX."Raid`, `".RP_TABLE_PREFIX."Attendance` ".
                                           "FROM `".RP_TABLE_PREFIX."Raid` LEFT JOIN `".RP_TABLE_PREFIX."Attendance` USING ( RaidId ) ".
                                           "WHERE ".RP_TABLE_PREFIX."Raid.End < FROM_UNIXTIME(:Time)" );


        $Timestamp = time() - $aSeconds;
        $DropRaidQuery->bindValue( ":Time", $Timestamp, PDO::PARAM_INT );
        $DropRaidQuery->execute();
    }
?>