<?php

function msgQueryNewRaidData( $aRequest )
{
    $Out = Out::getInstance();
    
    if ( validRaidlead() )
    {
        $Connector = Connector::getInstance();

        // Settings

        $NewRaidSettings = $Connector->prepare("Select Name, IntValue, TextValue FROM `".RP_TABLE_PREFIX."Setting`");

        if ( !$NewRaidSettings->execute() )
        {
            postErrorMessage( $NewRaidSettings );
        }
        else
        {
            $IntOfInterest = Array( "RaidSize", "RaidStartHour", "RaidStartMinute", "RaidEndHour", "RaidEndMinute", "StartOfWeek" );
            $TextOfInterest = Array( "RaidMode" );
            
            $Settings = Array();

            while ( $Data = $NewRaidSettings->fetch( PDO::FETCH_ASSOC ) )
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
            }

            $Out->pushValue("setting", $Settings);
        }

        $NewRaidSettings->closeCursor();

        // Locations

        msgQueryLocations($aRequest);
    }
    else
    {
        $Out->pushError(L("AccessDenied"));
    }
}

?>