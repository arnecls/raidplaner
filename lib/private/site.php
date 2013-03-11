<?php
    
    require_once(dirname(__FILE__)."/../config/config.php");
    require_once("connector.class.php");
    
    $g_Site = Array(
        "BannerLink" => "",
        "Banner"     => "cataclysm.jpg",
        "Background" => "flower.png",
        "BGColor"    => "#898989",
        "BGRepeat"   => "repeat-xy",
        "PortalMode" => false,
        "TimeFormat" => 24
    );

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
?>