<?php

    function lockOldRaids( $Seconds )
    {
        if ( ValidUser() )
        {
            $Connector = Connector::GetInstance();


            $UpdateRaidSt = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."Raid` SET ".
                                                "Stage = 'locked'".
                                                "WHERE End < FROM_UNIXTIME(:Time) AND Stage = 'open'" );

            $UpdateRaidSt->bindValue(":Time", time() + $Seconds, PDO::PARAM_INT);

            if ( !$UpdateRaidSt->execute() )
            {
                postErrorMessage( $UpdateRaidSt );
            }

            $UpdateRaidSt->closeCursor();
        }
    }

    function purgeOldRaids( $Seconds )
    {
        $Connector = Connector::GetInstance();


        $DropRaidSt = $Connector->prepare( "DELETE `".RP_TABLE_PREFIX."Raid`, `".RP_TABLE_PREFIX."Attendance` ".
                                           "FROM `".RP_TABLE_PREFIX."Raid` LEFT JOIN `".RP_TABLE_PREFIX."Attendance` USING ( RaidId ) ".
                                           "WHERE ".RP_TABLE_PREFIX."Raid.End < FROM_UNIXTIME(:Time)" );


        $Timestamp = time() - $Seconds;
        $DropRaidSt->bindValue( ":Time", $Timestamp, PDO::PARAM_INT );

        if ( !$DropRaidSt->execute() )
        {
               postErrorMessage( $DropRaidSt );
        }

        $DropRaidSt->closeCursor();
    }

?>