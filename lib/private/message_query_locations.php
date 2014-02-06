<?php

function msgQueryLocations( $aRequest )
{
    global $gSite;
    global $gGame;
    
    loadGameSettings();
    $Out = Out::getInstance();

    if ( validRaidlead() )
    {
        $Connector = Connector::getInstance();

        // Locations

        $ListLocations = $Connector->prepare("Select * FROM `".RP_TABLE_PREFIX."Location` WHERE Game = :Game ORDER BY Name");
        
        $ListLocations->bindValue( ":Game", $gGame["GameId"], PDO::PARAM_STR );

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

        $Images = @scandir("../themes/icons/".$gSite["Iconset"]."/raidsmall");
        $ImageList = Array();
        
        if ($Images != null)
        {
            foreach ( $Images as $Image )
            {
                if ( strripos( $Image, ".png" ) !== false )
                {
                    array_push($ImageList, $Image);
                }
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