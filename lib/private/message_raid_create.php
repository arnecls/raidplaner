<?php

function msgRaidCreate( $aRequest )
{
    if ( validRaidlead() )
    {
        global $gGroupSizes, $gSite;
        $Connector = Connector::getInstance();

        $LocationId = $aRequest["locationId"];
        
        // Create location

        if ( $LocationId == 0 )
        {
            $NewLocationQuery = $Connector->prepare("INSERT INTO `".RP_TABLE_PREFIX."Location`".
                                                    "(Name, Image) VALUES (:Name, :Image)");

            $NewLocationQuery->bindValue(":Name", requestToXML( $aRequest["locationName"], ENT_COMPAT, "UTF-8" ), PDO::PARAM_STR );
            $NewLocationQuery->bindValue(":Image", $aRequest["raidImage"], PDO::PARAM_STR );

            if (!$NewLocationQuery->execute())
                return; // ### return, location could not be created ###
            
            $LocationId = $Connector->lastInsertId();
        }
        
        // Create raid
        
        if ( $LocationId != 0 )
        {
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
            
            // Get users on vacation
            
            $UserSettingsQuery = $Connector->prepare("SELECT UserId, Name, IntValue, TextValue FROM `".RP_TABLE_PREFIX."UserSetting` ".
               "WHERE Name = 'VacationStart' OR Name = 'VacationEnd' OR Name = 'VacationMessage' ORDER BY UserId");
            
            $VactionUsers = array();
            $UserSettingsQuery->loop( function($Settings) use (&$VactionUsers)
            {
                if (!isset($VactionUsers[$Settings["UserId"]]))
                {
                    $VactionUsers[$Settings["UserId"]] = array("Message" => "");
                }
                
                switch ($Settings["Name"])
                {
                case "VacationStart":
                    $VactionUsers[$Settings["UserId"]]["Start"] = $Settings["IntValue"];
                    break;
                    
                case "VacationEnd":
                    $VactionUsers[$Settings["UserId"]]["End"] = $Settings["IntValue"];
                    break;
                    
                case "VacationMessage":
                    $VactionUsers[$Settings["UserId"]]["Message"] = $Settings["TextValue"];
                    break;
                
                default:
                    break;
                }
            });
            
            // Prepare posting raids to forum
            
            $PostTargets = array();
            PluginRegistry::ForEachPlugin(function($PluginInstance) use (&$PostTargets)
            {
                if ($PluginInstance->isActive() && $PluginInstance->postRequested())
                {
                    array_push($PostTargets, $PluginInstance);
                }
            });
            
            $LocationName = "Raid";
            
            if ( sizeof($PostTargets) > 0 )
            {
                loadSiteSettings();
                
                $LocationQuery = $Connector->prepare("SELECT Name FROM `".RP_TABLE_PREFIX."Location` WHERE LocationId = :LocationId LIMIT 1");
                $LocationQuery->bindValue(":LocationId", $LocationId, PDO::PARAM_INT);
                $LocationData = $LocationQuery->fetchFirst();
                    
                if ($LocationData != null)
                    $LocationName = $LocationData["Name"];
            }
            
            $LocationName .= " (".$aRequest["locationSize"].")";
                
            // Create raids(s)
                    
            $Repeat = max(0, intval($aRequest["repeat"])) + 1; // repeat at least once
            
            for ($rc=0; $rc<$Repeat; ++$rc)
            {
                $NewRaidQuery = $Connector->prepare("INSERT INTO `".RP_TABLE_PREFIX."Raid` ".
                                                    "(LocationId, Size, Start, End, Mode, Description, SlotsRole1, SlotsRole2, SlotsRole3, SlotsRole4, SlotsRole5 ) ".
                                                    "VALUES (:LocationId, :Size, FROM_UNIXTIME(:Start), FROM_UNIXTIME(:End), :Mode, :Description, ".
                                                    ":SlotsRole1, :SlotsRole2, :SlotsRole3, :SlotsRole4, :SlotsRole5)");
    
                $StartDateTime = mktime($StartHour, $StartMinute, 0, $StartMonth, $StartDay, $StartYear);
                $EndDateTime   = mktime($EndHour, $EndMinute, 0, $EndMonth, $EndDay, $EndYear);
                
                // Convert to UTC
    
                $StartDateTime += $aRequest["startOffset"] * 60;
                $EndDateTime   += $aRequest["endOffset"] * 60;
    
                $NewRaidQuery->bindValue(":LocationId",  $LocationId, PDO::PARAM_INT);
                $NewRaidQuery->bindValue(":Size",        $aRequest["locationSize"], PDO::PARAM_INT);
                $NewRaidQuery->bindValue(":Start",       $StartDateTime, PDO::PARAM_INT);
                $NewRaidQuery->bindValue(":End",         $EndDateTime, PDO::PARAM_INT);
                $NewRaidQuery->bindValue(":Mode",        $aRequest["mode"], PDO::PARAM_STR);
                $NewRaidQuery->bindValue(":Description", requestToXML( $aRequest["description"], ENT_COMPAT, "UTF-8" ), PDO::PARAM_STR);
    
                // Set role sizes
    
                $NewRaidQuery->bindValue(":SlotsRole1", $DefaultSizes[0], PDO::PARAM_INT);
                $NewRaidQuery->bindValue(":SlotsRole2", $DefaultSizes[1], PDO::PARAM_INT);
                $NewRaidQuery->bindValue(":SlotsRole3", $DefaultSizes[2], PDO::PARAM_INT);
                $NewRaidQuery->bindValue(":SlotsRole4", $DefaultSizes[3], PDO::PARAM_INT);
                $NewRaidQuery->bindValue(":SlotsRole5", $DefaultSizes[4], PDO::PARAM_INT);
    
                $NewRaidQuery->execute();
                $RaidId = $Connector->lastInsertId();
                
                // Set vacation attendances
                
                while (list($UserId, $Settings) = each($VactionUsers))
                {
                    if ( ($StartDateTime >= $Settings["Start"]) && ($StartDateTime <= $Settings["End"]) )
                    {                    
                        $AbsentQuery = $Connector->prepare("INSERT INTO `".RP_TABLE_PREFIX."Attendance` (UserId, RaidId, Status, Comment) ".
                                                           "VALUES (:UserId, :RaidId, 'unavailable', :Message)");
                            
                        $AbsentQuery->bindValue(":UserId", $UserId, PDO::PARAM_INT);
                        $AbsentQuery->bindValue(":RaidId", $RaidId, PDO::PARAM_INT);
                        $AbsentQuery->bindValue(":Message", $Settings["Message"], PDO::PARAM_STR);
                        
                        $AbsentQuery->execute();
                    }
                }
                
                reset($VactionUsers);
                
                // Post raids to forum
                
                if (sizeof($PostTargets) > 0)
                {
                    $Subject = $LocationName.", ".(($gSite["TimeFormat"] == 24) ? $StartDay.".".$StartMonth."." : $StartMonth."/".$StartDay." ").$StartYear;
                    $RaidUrl = $_SERVER["HTTP_ORIGIN"].substr($_SERVER["REQUEST_URI"], 0, strpos($_SERVER["REQUEST_URI"], "lib/"))."index.php#raid,".$RaidId;
                    $Message = $LocationName."\n<a href=\"".$RaidUrl."\">".L("RaidSetup")."</a>\n\n".$aRequest["description"];
            
                    try
                    {
                        foreach($PostTargets as $PluginInstance)
                        {
                            $PluginInstance->post($Subject, $Message);
                        }
                    }
                    catch (PDOException $Exception)
                    {
                        Out::getInstance()->pushError($Exception->getMessage());
                    }
                }
                
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