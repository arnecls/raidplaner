<?php

function msgQueryNewRaidData( $Request )
{
    if ( ValidRaidlead() )
    {
        $Connector = Connector::GetInstance();
        
        // Settings
        
        $NewRaidSettings = $Connector->prepare("Select Name, IntValue, TextValue FROM `".RP_TABLE_PREFIX."Setting`");
        
        if ( !$NewRaidSettings->execute() )
        {
            postErrorMessage( $NewRaidSettings );
        }
        else
        {
            echo "<settings>";
                
            $IntOfInterest = array( "RaidSize", "RaidStartHour", "RaidStartMinute", "RaidEndHour", "RaidEndMinute" );
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
        
        msgQueryLocations($Request);
    }
    else
    {
        echo "<error>".L("AccessDenied")."</error>";
    }
}
   
?>