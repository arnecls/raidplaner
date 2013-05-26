<?php

function msgQueryLocations( $aRequest )
{
    if ( validRaidlead() )
    {
        $Connector = Connector::getInstance();

        // Locations

        $ListLocations = $Connector->prepare("Select * FROM `".RP_TABLE_PREFIX."Location` ORDER BY Name");


        if ( !$ListLocations->execute() )
        {
            postErrorMessage( $ListLocations );
        }
        else
        {
            while ( $Data = $ListLocations->fetch( PDO::FETCH_ASSOC ) )
            {
                echo "<location>";
                echo "<id>".$Data["LocationId"]."</id>";
                echo "<name>".$Data["Name"]."</name>";
                echo "<image>".$Data["Image"]."</image>";
                echo "</location>";
            }
        }

        $ListLocations->closeCursor();

        // Images

        $Images = scandir("../images/raidsmall");

        foreach ( $Images as $Image )
        {
            if ( strripos( $Image, ".png" ) !== false )
            {
                echo "<locationimage>".$Image."</locationimage>";
            }
        }
    }
    else
    {
        echo "<error>".L("AccessDenied")."</error>";
    }
}

?>