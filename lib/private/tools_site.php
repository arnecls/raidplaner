<?php
    
    @include_once(dirname(__FILE__)."/../config/config.php");
    include_once(dirname(__FILE__)."/connector.class.php");
    
    $g_Site = Array(
        "BannerLink" => "",
        "Banner"     => "cataclysm.jpg",
        "Background" => "flower.png",
        "BGColor"    => "#898989",
        "BGRepeat"   => "repeat-xy",
        "PortalMode" => false,
        "TimeFormat" => 24
    );
    
    // ---------------------------------------------------------------

    function loadSiteSettings()
    {
        global $g_Site;
        
        $Connector = Connector::GetInstance();
        $Settings = $Connector->prepare("Select `Name`, `TextValue`, `IntValue` FROM `".RP_TABLE_PREFIX."Setting`");
    
        if ( $Settings->execute() )
        {
            $g_Site = Array(
                "BannerLink" => "",
                "Banner"     => "cataclysm.jpg",
                "Background" => "flower.png",
                "BGColor"    => "#898989",
                "BGRepeat"   => "repeat-xy",
                "PortalMode" => false,
                "TimeFormat" => 24
            );
    
            while ( $Data = $Settings->fetch( PDO::FETCH_ASSOC ) )
            {
                switch( $Data["Name"] )
                {
                case "Site":
                    $g_Site["BannerLink"] = $Data["TextValue"];
                    break;
    
                case "Theme":
                    $ThemeFile = dirname(__FILE__)."/../../images/themes/".$Data["TextValue"].".xml";
    
                    if ( file_exists($ThemeFile) )
                    {
                        $Theme = new SimpleXMLElement( file_get_contents($ThemeFile) );
                        $g_Site["Banner"]     = (string)$Theme->banner;
                        $g_Site["Background"] = (string)$Theme->bgimage;
                        $g_Site["BGColor"]    = (string)$Theme->bgcolor;
                        $g_Site["BGRepeat"]   = (string)$Theme->bgrepeat;
                        $g_Site["PortalMode"] = ((string)$Theme->portalmode) == "true";
                    }
                    break;
    
                case "TimeFormat":
                    $g_Site["TimeFormat"] = $Data["IntValue"];
                    break;
    
                default:
                    break;
                };
            }
        }
    
        $Settings->closeCursor();
    } 
    
    // ---------------------------------------------------------------
    
    function CheckVersion($siteVersion)
    {
        try
        {
            $Connector = Connector::GetInstance(true);
            $versionSt = $Connector->prepare("SELECT IntValue FROM `".RP_TABLE_PREFIX."Setting` WHERE Name = 'Version' LIMIT 1" );
            
            if ($versionSt->execute())
            {
                $result = $versionSt->fetch( PDO::FETCH_ASSOC ); 
                $versionSt->closeCursor();
                
                return intval($siteVersion) == intval($result["IntValue"]);
            }
            
            $versionSt->closeCursor();
        }
        catch(PDOException $Exception)
        {
        }
        
        return false;        
    }
    
    // ---------------------------------------------------------------
    
    function lockOldRaids( $Seconds )
    {
        if ( ValidUser() )
        {
            $Connector = Connector::GetInstance();


            $UpdateRaidSt = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."Raid` SET ".
                                                "Stage = 'locked'".
                                                "WHERE Start < FROM_UNIXTIME(:Time) AND Stage = 'open'" );

            $UpdateRaidSt->bindValue(":Time", time() + $Seconds, PDO::PARAM_INT);

            if ( !$UpdateRaidSt->execute() )
            {
                postErrorMessage( $UpdateRaidSt );
            }

            $UpdateRaidSt->closeCursor();
        }
    }
    
    // ---------------------------------------------------------------

    function purgeOldRaids( $Seconds )
    {
        $Connector = Connector::GetInstance();


        $DropRaidSt = $Connector->prepare( "DELETE `".RP_TABLE_PREFIX."Raid`, `".RP_TABLE_PREFIX."Attendance` ".
                                           "FROM `".RP_TABLE_PREFIX."Raid` LEFT JOIN `".RP_TABLE_PREFIX."Attendance` USING ( RaidId ) ".
                                           "WHERE ".RP_TABLE_PREFIX."Raid.End < FROM_UNIXTIME(:Time)" );


        $Timestamp = time() - $Seconds;
        $DropRaidSt->bindValue( ":Time", $Timestamp, PDO::PARAM_INT );

        if ( !$DropRaidSt->execute() )
        {
               postErrorMessage( $DropRaidSt );
        }

        $DropRaidSt->closeCursor();
    }
?>