<?php

function msgQueryLocations( $aRequest )
{
    loadSiteSettings();
    
    global $gSite;
    $Out = Out::getInstance();

    if ( validRaidlead() )
    {
        $Connector = Connector::getInstance();

        // Locations

        $ListLocations = $Connector->prepare("Select * FROM `".RP_TABLE_PREFIX."Location` ORDER BY Name");

        $Locations = Array();
        $ListLocations->loop( function($Data) use (&$Locations)
        {
            $LocationData = Array(
                "id"    => $Data["LocationId"],
                "name"  => $Data["Name"],
                "image" => $Data["Image"],
            );

            array_push($Locations, $LocationData);
        });

        $Out->pushValue("location", $Locations);

        // Images

        $Images = scandir("../images/icons/".$gSite["Iconset"]."/raidsmall");
        $ImageList = Array();

        foreach ( $Images as $Image )
        {
            if ( strripos( $Image, ".png" ) !== false )
            {
                array_push($ImageList, $Image);
            }
        }
        
        $Out->pushValue("locationimage", $ImageList);
    }
    else
    {
        $Out->pushError(L("AccessDenied"));
    }
}

?>