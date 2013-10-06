<?php
    
    @include_once(dirname(__FILE__)."/../config/config.php");
    include_once(dirname(__FILE__)."/connector.class.php");
    
    $gSite = Array(
        "BannerLink" => "",
        "HelpLink"   => "",
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
        global $gSite;
        
        $Connector = Connector::getInstance();
        $Settings = $Connector->prepare("Select `Name`, `TextValue`, `IntValue` FROM `".RP_TABLE_PREFIX."Setting`");
    
        if ( $Settings->execute() )
        {
            $gSite = Array(
                "BannerLink" => "",
                "HelpLink"   => "",
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
                    $gSite["BannerLink"] = $Data["TextValue"];
                    break;
                    
                case "HelpPage":
                    $gSite["HelpLink"] = $Data["TextValue"];
                    break;
    
                case "Theme":
                    $ThemeFile = dirname(__FILE__)."/../../images/themes/".$Data["TextValue"].".xml";
    
                    if ( file_exists($ThemeFile) )
                    {
                        $Theme = new SimpleXMLElement( file_get_contents($ThemeFile) );
                        $gSite["Banner"]     = (string)$Theme->banner;
                        $gSite["Background"] = (string)$Theme->bgimage;
                        $gSite["BGColor"]    = (string)$Theme->bgcolor;
                        $gSite["BGRepeat"]   = (string)$Theme->bgrepeat;
                        $gSite["PortalMode"] = ((string)$Theme->portalmode) == "true";
                    }
                    break;
    
                case "TimeFormat":
                    $gSite["TimeFormat"] = $Data["IntValue"];
                    break;
    
                default:
                    break;
                };
            }
        }
    
        $Settings->closeCursor();
    } 
    
    // ---------------------------------------------------------------
    
    function checkVersion($aSiteVersion)
    {
        try
        {
            $Connector = Connector::getInstance(true);
            $VersionSt = $Connector->prepare("SELECT IntValue FROM `".RP_TABLE_PREFIX."Setting` WHERE Name = 'Version' LIMIT 1" );
            
            if ($VersionSt->execute())
            {
                $Result = $VersionSt->fetch( PDO::FETCH_ASSOC ); 
                $VersionSt->closeCursor();
                
                return intval($aSiteVersion) == intval($Result["IntValue"]);
            }
            
            $VersionSt->closeCursor();
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


            $UpdateRaidSt = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."Raid` SET ".
                                                "Stage = 'locked'".
                                                "WHERE Start < FROM_UNIXTIME(:Time) AND Stage = 'open'" );

            $UpdateRaidSt->bindValue(":Time", time() + $aSeconds, PDO::PARAM_INT);

            if ( !$UpdateRaidSt->execute() )
            {
                postErrorMessage( $UpdateRaidSt );
            }

            $UpdateRaidSt->closeCursor();
        }
    }
    
    // ---------------------------------------------------------------

    function purgeOldRaids( $aSeconds )
    {
        $Connector = Connector::getInstance();


        $DropRaidSt = $Connector->prepare( "DELETE `".RP_TABLE_PREFIX."Raid`, `".RP_TABLE_PREFIX."Attendance` ".
                                           "FROM `".RP_TABLE_PREFIX."Raid` LEFT JOIN `".RP_TABLE_PREFIX."Attendance` USING ( RaidId ) ".
                                           "WHERE ".RP_TABLE_PREFIX."Raid.End < FROM_UNIXTIME(:Time)" );


        $Timestamp = time() - $aSeconds;
        $DropRaidSt->bindValue( ":Time", $Timestamp, PDO::PARAM_INT );

        if ( !$DropRaidSt->execute() )
        {
               postErrorMessage( $DropRaidSt );
        }

        $DropRaidSt->closeCursor();
    }
?>