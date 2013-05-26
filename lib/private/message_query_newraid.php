<?php

function msgQueryNewRaidData( $aRequest )
{
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
            echo "<settings>";

            $IntOfInterest = array( "RaidSize", "RaidStartHour", "RaidStartMinute", "RaidEndHour", "RaidEndMinute", "StartOfWeek" );
            $TextOfInterest = array( "RaidMode" );

            while ( $Data = $NewRaidSettings->fetch( PDO::FETCH_ASSOC ) )
            {
                if ( in_array($Data["Name"], $IntOfInterest) )
                {
                    echo "<".$Data["Name"].">".$Data["IntValue"]."</".$Data["Name"].">";
                }

                if ( in_array($Data["Name"], $TextOfInterest) )
                {
                    echo "<".$Data["Name"].">".$Data["TextValue"]."</".$Data["Name"].">";
                }
            }

            echo "</settings>";
        }

        $NewRaidSettings->closeCursor();

        // Locations

        msgQueryLocations($aRequest);
    }
    else
    {
        echo "<error>".L("AccessDenied")."</error>";
    }
}

?>