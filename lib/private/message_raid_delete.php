<?php

    function msgRaidDelete( $aRequest )
    {
        if ( validPrivileged() )
        {
            if ( !validRaidlead() && !userOwnsRaid($aRequest['id']) )
            {
                $Out = Out::getInstance();
                $Out->pushError(L('AccessDenied'));
                return; // ### return, no rights ###
            }

            $Connector = Connector::getInstance();

            // Call plugins

            $RaidId = intval($aRequest['id']);
            PluginRegistry::ForEachPlugin(function($PluginInstance) use ($RaidId)
            {
                $PluginInstance->onRaidRemove($RaidId);
            });

            do
            {
                // Delete raid

                $Connector->beginTransaction();

                $DeleteRaidQuery = $Connector->prepare('DELETE FROM `'.RP_TABLE_PREFIX.'Raid` WHERE RaidId = :RaidId LIMIT 1' );
                $DeleteRaidQuery->bindValue(':RaidId', $aRequest['id'], PDO::PARAM_INT);

                if (!$DeleteRaidQuery->execute())
                {
                    $Connector->rollBack();
                    return; // ### return, error ###
                }

                // Delete attendance

                $DeleteAttendanceQuery = $Connector->prepare('DELETE FROM `'.RP_TABLE_PREFIX.'Attendance` WHERE RaidId = :RaidId' );
                $DeleteAttendanceQuery->bindValue(':RaidId', $aRequest['id'], PDO::PARAM_INT);

                if (!$DeleteAttendanceQuery->execute())
                {
                    $Connector->rollBack();
                    return; // ### return, error ###
                }
            }
            while(!$Connector->commit());

            Log::getInstance()->delete(LOG_TYPE_RAID, $aRequest['id']);

            $Session = Session::get();

            $ShowMonth = ( isset($Session['Calendar']) && isset($Session['Calendar']['month']) ) ? $Session['Calendar']['month'] : $aRequest['month'];
            $ShowYear  = ( isset($Session['Calendar']) && isset($Session['Calendar']['year']) )  ? $Session['Calendar']['year']  : $aRequest['year'];

            msgQueryCalendar( prepareCalRequest( $ShowMonth, $ShowYear ) );
        }
        else
        {
            $Out = Out::getInstance();
            $Out->pushError(L('AccessDenied'));
        }
    }

?>
