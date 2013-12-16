<?php

function msgRaidCreate( $aRequest )
{
    if ( validRaidlead() )
    {
        global $gGroupSizes;
        $Connector = Connector::getInstance();

        $LocationId = $aRequest["locationId"];

        if ( $LocationId == 0 )
        {
            $NewLocationSt = $Connector->prepare("INSERT INTO `".RP_TABLE_PREFIX."Location`".
                                                 "(Name, Image) VALUES (:Name, :Image)");

            $NewLocationSt->bindValue(":Name", requestToXML( $aRequest["locationName"], ENT_COMPAT, "UTF-8" ), PDO::PARAM_STR );
            $NewLocationSt->bindValue(":Image", $aRequest["raidImage"], PDO::PARAM_STR );

            if (!$NewLocationSt->execute())
            {
                postErrorMessage( $NewLocationSt );
                $NewLocationSt->closeCursor();
                return;
            }
            else
            {
                $LocationId = $Connector->lastInsertId();
            }

            $NewLocationSt->closeCursor();
        }

        if ( $LocationId != 0 )
        {
            // Sanity checks
            
            //while ( list($GroupSize,$Slots) = each($gGroupSizes) )
            //{
            //    echo "<option value=\"".$GroupSize."\">".$GroupSize."</option>";
            //}

            // Get the default sizes

            if ( isset($gGroupSizes[$aRequest["locationSize"]]) )
            {
                // Sizes are defined in gameconfig
                $DefaultSizes = $gGroupSizes[$aRequest["locationSize"]];
            }
            else
            {
                // Sizes are not defined in gameconfig
                // Equally distribute, last role gets remaining slots

                $NumRoles = sizeof($gRoles);
                $DefaultSizes = Array();
                $SlotsUsed = 0;
                $RaidSize = intval($aRequest["locationSize"]);

                for ($i=0; $i<$NumRoles-1; ++$i)
                {
                    $DefaultSizes[$i] = intval($RaidSize / $NumRoles);
                    $SlotsUsed += $DefaultSizes[$i];
                }

                $DefaultSizes[$NumRoles-1] = $RaidSize - $SlotsUsed;
            }

            // Assure array contains entries for all 5 roles

            while (sizeof($DefaultSizes) < 5)
                array_push($DefaultSizes, 0);
                
            // First raid time calculation
            
            $StartHour   = intval($aRequest["startHour"]);
            $StartMinute = intval($aRequest["startMinute"]);
            $StartDay    = intval($aRequest["startDay"]);
            $StartMonth  = intval($aRequest["startMonth"]);
            $StartYear   = intval($aRequest["startYear"]);
            
            $EndHour   = intval($aRequest["endHour"]);
            $EndMinute = intval($aRequest["endMinute"]);
            $EndDay    = intval($aRequest["endDay"]);
            $EndMonth  = intval($aRequest["endMonth"]);
            $EndYear   = intval($aRequest["endYear"]);
                
            // Create raids(s)
                    
            $Repeat = max(0, intval($aRequest["repeat"])) + 1; // repeat at least once
            
            for ($rc=0; $rc<$Repeat; ++$rc)
            {        
                $NewRaidSt = $Connector->prepare("INSERT INTO `".RP_TABLE_PREFIX."Raid` ".
                                                 "(LocationId, Size, Start, End, Mode, Description, SlotsRole1, SlotsRole2, SlotsRole3, SlotsRole4, SlotsRole5 ) ".
                                                 "VALUES (:LocationId, :Size, FROM_UNIXTIME(:Start), FROM_UNIXTIME(:End), :Mode, :Description, ".
                                                 ":SlotsRole1, :SlotsRole2, :SlotsRole3, :SlotsRole4, :SlotsRole5)");
    
                $StartDateTime = mktime($StartHour, $StartMinute, 0, $StartMonth, $StartDay, $StartYear);
                $EndDateTime   = mktime($EndHour, $EndMinute, 0, $EndMonth, $EndDay, $EndYear);
                
                // Convert to UTC
    
                $StartDateTime += $aRequest["startOffset"] * 60;
                $EndDateTime   += $aRequest["endOffset"] * 60;
    
                $NewRaidSt->bindValue(":LocationId",  $LocationId, PDO::PARAM_INT);
                $NewRaidSt->bindValue(":Size",        $aRequest["locationSize"], PDO::PARAM_INT);
                $NewRaidSt->bindValue(":Start",       $StartDateTime, PDO::PARAM_INT);
                $NewRaidSt->bindValue(":End",         $EndDateTime, PDO::PARAM_INT);
                $NewRaidSt->bindValue(":Mode",        $aRequest["mode"], PDO::PARAM_STR);
                $NewRaidSt->bindValue(":Description", requestToXML( $aRequest["description"], ENT_COMPAT, "UTF-8" ), PDO::PARAM_STR);
    
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
                
                // Increment start/end
                
                switch ($aRequest["stride"])
                {
                case "day":
                    ++$StartDay;
                    ++$EndDay;
                    break;
                    
                case "week":
                    $StartDay += 7;
                    $EndDay += 7;
                    break;
                    
                case "month":
                    ++$StartMonth;
                    ++$EndMonth;
                    break;
                
                default;
                case "once":
                    $rc = $Repeat; // Force done
                    break;
                }
            }

            // reload calendar

            $ShowMonth = ( isset($_SESSION["Calendar"]) && isset($_SESSION["Calendar"]["month"]) ) ? $_SESSION["Calendar"]["month"] : $aRequest["month"];
            $ShowYear  = ( isset($_SESSION["Calendar"]) && isset($_SESSION["Calendar"]["year"]) )  ? $_SESSION["Calendar"]["year"]  : $aRequest["year"];

            msgQueryCalendar( prepareCalRequest( $ShowMonth, $ShowYear ) );
        }
    }
    else
    {
        $Out = Out::getInstance();
        $Out->pushError(L("AccessDenied"));
    }
}

?>