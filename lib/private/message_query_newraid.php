<?php

function msgQueryNewRaidData( $aRequest )
{
    $Out = Out::getInstance();

    if ( validRaidlead() )
    {
        $Connector = Connector::getInstance();

        // Settings

        $NewRaidSettings = $Connector->prepare("SELECT Name, IntValue, TextValue FROM `".RP_TABLE_PREFIX."Setting`");

        $IntOfInterest = Array( "RaidSize", "RaidStartHour", "RaidStartMinute", "RaidEndHour", "RaidEndMinute", "StartOfWeek" );
        $TextOfInterest = Array( "RaidMode" );
        $Settings = Array();

        $NewRaidSettings->loop( function($Data) use (&$Settings, $IntOfInterest, $TextOfInterest)
        {
            $KeyValue = Array(
                "name"  => $Data["Name"],
                "value" => null
            );

            if ( in_array($Data["Name"], $IntOfInterest) )
            {
                $KeyValue["value"] = $Data["IntValue"];
            }
            elseif ( in_array($Data["Name"], $TextOfInterest) )
            {
                $KeyValue["value"] = $Data["TextValue"];
            }

            array_push($Settings, $KeyValue);
        });

        $Out->pushValue("setting", $Settings);

        // Locations

        msgQueryLocations($aRequest);
    }
    else
    {
        $Out->pushError(L("AccessDenied"));
    }
}

?>