<?php

function msgRaidCreate( $Request )
{
    if ( ValidRaidlead() )
    {
        global $s_GroupSizes;
        $Connector = Connector::GetInstance();

        $locationId = $Request["locationId"];

        if ( $locationId == 0 )
        {
            $NewLocationSt = $Connector->prepare("INSERT INTO `".RP_TABLE_PREFIX."Location`".
                                                 "(Name, Image) VALUES (:Name, :Image)");

            $NewLocationSt->bindValue(":Name", requestToXML( $Request["locationName"], ENT_COMPAT, "UTF-8" ), PDO::PARAM_STR );
            $NewLocationSt->bindValue(":Image", $Request["raidImage"], PDO::PARAM_STR );

            if (!$NewLocationSt->execute())
            {
                postErrorMessage( $NewLocationSt );
                $NewLocationSt->closeCursor();
                return;
            }
            else
            {
                $locationId = $Connector->lastInsertId();
            }

            $NewLocationSt->closeCursor();
        }

        if ( $locationId != 0 )
        {

            $NewRaidSt = $Connector->prepare("INSERT INTO `".RP_TABLE_PREFIX."Raid` ".
                                             "(LocationId, Size, Start, End, Mode, Description, SlotsRole1, SlotsRole2, SlotsRole3, SlotsRole4, SlotsRole5 ) ".
                                             "VALUES (:LocationId, :Size, FROM_UNIXTIME(:Start), FROM_UNIXTIME(:End), :Mode, :Description, ".
                                             ":SlotsRole1, :SlotsRole2, :SlotsRole3, :SlotsRole4, :SlotsRole5)");

            $StartDateTime = mktime($Request["startHour"], $Request["startMinute"], 0, $Request["month"], $Request["day"], $Request["year"]);
            $EndDateTime   = mktime($Request["endHour"], $Request["endMinute"], 0, $Request["month"], $Request["day"], $Request["year"]);

            $Mode = "manual"; // TODO: Read from parameter

            if ( $EndDateTime < $StartDateTime )
               $EndDateTime += 60*60*24;

            $NewRaidSt->bindValue(":LocationId",  $locationId, PDO::PARAM_INT);
            $NewRaidSt->bindValue(":Size",        $Request["locationSize"], PDO::PARAM_INT);
            $NewRaidSt->bindValue(":Start",       $StartDateTime, PDO::PARAM_INT);
            $NewRaidSt->bindValue(":End",         $EndDateTime, PDO::PARAM_INT);
            $NewRaidSt->bindValue(":Mode",        $Request["mode"], PDO::PARAM_STR);
            $NewRaidSt->bindValue(":Description", requestToXML( $Request["description"], ENT_COMPAT, "UTF-8" ), PDO::PARAM_STR);

            while ( list($groupSize,$slots) = each($s_GroupSizes) )
            {
                echo "<option value=\"".$groupSize."\">".$groupSize."</option>";
            }

            // Get the default sizes

            if ( isset($s_GroupSizes[$Request["locationSize"]]) )
            {
                // Sizes are defined in gameconfig
                $DefaultSizes = $s_GroupSizes[$Request["locationSize"]];
            }
            else
            {
                // Sizes are not defined in gameconfig
                // Equally distribute, last role gets remaining slots

                $numRoles = sizeof($s_Roles);
                $DefaultSizes = Array();
                $slotsUsed = 0;
                $RaidSize = intval($Request["locationSize"]);

                for ($i=0; $i<$numRoles-1; ++$i)
                {
                    $DefaultSizes[$i] = intval($RaidSize / $numRoles);
                    $slotsUsed += $DefaultSizes[$i];
                }

                $DefaultSizes[$numRoles-1] = $RaidSize - $slotsUsed;
            }

            // Assure array contains entries for all 5 roles

            while (sizeof($DefaultSizes) < 5)
                array_push($DefaultSizes, 0);

            // Set role sizes

            $NewRaidSt->bindValue(":SlotsRole1", $DefaultSizes[0], PDO::PARAM_INT);
            $NewRaidSt->bindValue(":SlotsRole2", $DefaultSizes[1], PDO::PARAM_INT);
            $NewRaidSt->bindValue(":SlotsRole3", $DefaultSizes[2], PDO::PARAM_INT);
            $NewRaidSt->bindValue(":SlotsRole4", $DefaultSizes[3], PDO::PARAM_INT);
            $NewRaidSt->bindValue(":SlotsRole5", $DefaultSizes[4], PDO::PARAM_INT);

            if (!$NewRaidSt->execute())
            {
                postErrorMessage( $NewRaidSt );
            }

            $NewRaidSt->closeCursor();

            // reload calendar

            $showMonth = ( isset($_SESSION["Calendar"]) && isset($_SESSION["Calendar"]["month"]) ) ? $_SESSION["Calendar"]["month"]+1 : $Request["month"];
            $showYear  = ( isset($_SESSION["Calendar"]) && isset($_SESSION["Calendar"]["year"]) )  ? $_SESSION["Calendar"]["year"]    : $Request["year"];

            msgRaidCalendar( prepareRaidListRequest( $showMonth, $showYear ) );
        }
    }
    else
    {
        echo "<error>".L("AccessDenied")."</error>";
    }
}

?>