<?php

function msgQueryLocations( $aRequest )
{
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

        $Images = scandir("../images/raidsmall");

        foreach ( $Images as $Image )
        {
            if ( strripos( $Image, ".png" ) !== false )
            {
                $Out->pushValue("locationimage", $Image);
            }
        }
    }
    else
    {
        $Out->pushError(L("AccessDenied"));
    }
}

?>