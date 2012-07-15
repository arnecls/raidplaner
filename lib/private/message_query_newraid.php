<?php

function msgQueryNewRaidData( $Request )
{
    if ( ValidRaidlead() )
    {
        $Connector = Connector::GetInstance();
        
        // Settings
        
        $NewRaidSettings = $Connector->prepare("Select Name, IntValue FROM `".RP_TABLE_PREFIX."Setting`");
        
        if ( !$NewRaidSettings->execute() )
        {
            postErrorMessage( $NewRaidSettings );
        }
        else
        {
            echo "<settings>";
                
            $OfInterest = array( "RaidSize", "RaidStartHour", "RaidStartMinute", "RaidEndHour", "RaidEndMinute" );
            while ( $Data = $NewRaidSettings->fetch( PDO::FETCH_ASSOC ) )
            {
                if ( in_array($Data["Name"], $OfInterest) )
                {
                    echo "<".$Data["Name"].">".$Data["IntValue"]."</".$Data["Name"].">";
                }
            }
            
            echo "</settings>";
        }
        
        $NewRaidSettings->closeCursor();
        
        // Locations
        
        msgQueryLocations($Request);
    }
    else
    {
        echo "<error>".L("AccessDenied")."</error>";
    }
}
   
?>