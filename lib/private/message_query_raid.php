<?php
    function msgRaidDetail( $aRequest )
    {
        $Out = Out::getInstance();
            
        if (validUser())
        {
            $Out->pushValue("show", $aRequest["showPanel"]);

            $Connector = Connector::getInstance();

            $ListRaidQuery = $Connector->prepare("SELECT ".RP_TABLE_PREFIX."Raid.*, ".RP_TABLE_PREFIX."Location.Name AS LocationName, ".RP_TABLE_PREFIX."Location.Image AS LocationImage, ".
                                              RP_TABLE_PREFIX."Attendance.AttendanceId, ".RP_TABLE_PREFIX."Attendance.UserId, ".RP_TABLE_PREFIX."Attendance.CharacterId, ".
                                              RP_TABLE_PREFIX."Attendance.Status, ".RP_TABLE_PREFIX."Attendance.Role, ".RP_TABLE_PREFIX."Attendance.Comment, UNIX_TIMESTAMP(".RP_TABLE_PREFIX."Attendance.LastUpdate) AS LastUpdate, ".
                                              RP_TABLE_PREFIX."Character.Name, ".RP_TABLE_PREFIX."Character.Class, ".RP_TABLE_PREFIX."Character.Mainchar, ".RP_TABLE_PREFIX."Character.Role1, ".RP_TABLE_PREFIX."Character.Role2, ".
                                              "UNIX_TIMESTAMP(".RP_TABLE_PREFIX."Raid.Start) AS StartUTC, ".
                                              "UNIX_TIMESTAMP(".RP_TABLE_PREFIX."Raid.End) AS EndUTC ".
                                              "FROM `".RP_TABLE_PREFIX."Raid` ".
                                              "LEFT JOIN `".RP_TABLE_PREFIX."Location` USING(LocationId) ".
                                              "LEFT JOIN `".RP_TABLE_PREFIX."Attendance` USING(RaidId) ".
                                              "LEFT JOIN `".RP_TABLE_PREFIX."Character` USING(CharacterId) ".
                                              "WHERE RaidId = :RaidId ORDER BY `".RP_TABLE_PREFIX."Attendance`.AttendanceId");

            $ListRaidQuery->bindValue( ":RaidId", $aRequest["id"], PDO::PARAM_INT );
            $Data = $ListRaidQuery->fetchFirstOfLoop();

            if ($Data != null)
            {
                $Participants = Array();

                $StartDate    = getdate($Data["StartUTC"]);
                $EndDate      = getdate($Data["EndUTC"]);
                $EndTimestamp = $Data["EndUTC"];
                
                $Out->pushValue("raidId", $Data["RaidId"]);
                $Out->pushValue("locationid", $Data["LocationId"]);
                $Out->pushValue("locationname", $Data["LocationName"]);
                $Out->pushValue("stage", $Data["Stage"]);
                $Out->pushValue("mode", $Data["Mode"]);
                $Out->pushValue("image", $Data["LocationImage"]);
                $Out->pushValue("size", $Data["Size"]);
                $Out->pushValue("startDate", intval($StartDate["year"])."-".leadingZero10($StartDate["mon"])."-".leadingZero10($StartDate["mday"]));
                $Out->pushValue("start", leadingZero10($StartDate["hours"]).":".leadingZero10($StartDate["minutes"]));
                $Out->pushValue("endDate", intval($EndDate["year"])."-".leadingZero10($EndDate["mon"])."-".leadingZero10($EndDate["mday"]));
                $Out->pushValue("end", leadingZero10($EndDate["hours"]).":".leadingZero10($EndDate["minutes"]));
                $Out->pushValue("description", $Data["Description"]);
                $Out->pushValue("slots", Array($Data["SlotsRole1"], $Data["SlotsRole2"], $Data["SlotsRole3"], $Data["SlotsRole4"], $Data["SlotsRole5"]));
                $Attendees = Array();
                
                $MaxAttendanceId = 1;

                if ( $Data["UserId"] != NULL )
                {
                    $ListRaidQuery->loop(function($Data) use (&$Connector, &$MaxAttendanceId, &$Participants, &$Attendees)
                    {
                        // Track max attendance id to give undecided players (without a comment) a distinct one.
                        $MaxAttendanceId = Max($MaxAttendanceId,$Data["AttendanceId"]);

                        if ( $Data["UserId"] != 0 )
                        {
                            array_push( $Participants, intval($Data["UserId"]) );
                        }
                        
                        if ( $Data["CharacterId"] == 0 )
                        {
                            // CharacterId is 0 on random players or players that are absent

                            if ( $Data["UserId"] != 0 )
                            {
                                // Fetch the mainchar of the registered player and display this
                                // character as "absent"

                                $CharQuery = $Connector->prepare("SELECT ".RP_TABLE_PREFIX."Character.*, ".RP_TABLE_PREFIX."User.Login AS UserName ".
                                                                 "FROM `".RP_TABLE_PREFIX."Character` LEFT JOIN `".RP_TABLE_PREFIX."User` USING(UserId) ".
                                                                 "WHERE UserId = :UserId ".
                                                                 "ORDER BY Mainchar, CharacterId ASC" );

                                $CharQuery->bindValue( ":UserId", $Data["UserId"], PDO::PARAM_INT );
                                $CharData = $CharQuery->fetchFirstOfLoop();
                                
                                if ( ($CharData != null) && ($CharData["CharacterId"] != null) )
                                {
                                    $AttendeeData = Array(
                                        "id"        => $Data["AttendanceId"], // AttendanceId to support random players (userId 0)
                                        "hasId"     => true,
                                        "userId"    => $Data["UserId"],
                                        "timestamp" => $Data["LastUpdate"],
                                        "charid"    => $CharData["CharacterId"],
                                        "name"      => $CharData["Name"],
                                        "mainchar"  => $CharData["Mainchar"],
                                        "classname" => $CharData["Class"],
                                        "role"      => $CharData["Role1"],
                                        "role1"     => $CharData["Role1"],
                                        "role2"     => $CharData["Role2"],
                                        "status"    => $Data["Status"],
                                        "comment"   => $Data["Comment"],
                                        "character" => Array()
                                    );
                                    
                                    $CharQuery->loop(function($CharData) use (&$AttendeeData) 
                                    {
                                        $Character = Array(
                                            "id"        => $CharData["CharacterId"],
                                            "name"      => $CharData["Name"],
                                            "mainchar"  => $CharData["Mainchar"],
                                            "classname" => $CharData["Class"],
                                            "role1"     => $CharData["Role1"],
                                            "role2"     => $CharData["Role2"]
                                        );
                                        
                                        array_push($AttendeeData["character"], $Character);
                                    });

                                    array_push($Attendees, $AttendeeData);
                                }
                            }
                            else
                            {
                                // CharacterId and UserId set to 0 means "random player"
                                
                                $AttendeeData = Array(
                                    "id"        => $Data["AttendanceId"], // AttendanceId to support random players (userId 0)
                                    "hasId"     => true,
                                    "userId"    => 0,
                                    "timestamp" => $Data["LastUpdate"],
                                    "charid"    => 0,
                                    "name"      => $Data["Comment"],
                                    "mainchar"  => false,
                                    "classname" => "random",
                                    "role"      => $Data["Role"],
                                    "role1"     => $Data["Role"],
                                    "role2"     => $Data["Role"],
                                    "status"    => $Data["Status"],
                                    "comment"   => "",
                                    "character" => Array()
                                );

                                array_push($Attendees, $AttendeeData);
                            }
                        }
                        else
                        {
                            // CharacterId is set

                            $AttendeeData = Array(
                                "id"        => $Data["AttendanceId"], // AttendanceId to support random players (userId 0)
                                "hasId"     => true,
                                "userId"    => $Data["UserId"],
                                "timestamp" => $Data["LastUpdate"],
                                "charid"    => $Data["CharacterId"],
                                "name"      => $Data["Name"],
                                "mainchar"  => $Data["Mainchar"],
                                "classname" => $Data["Class"],
                                "role"      => $Data["Role"],
                                "role1"     => $Data["Role1"],
                                "role2"     => $Data["Role2"],
                                "status"    => $Data["Status"],
                                "comment"   => $Data["Comment"],
                                "character" => Array()
                            );

                            $CharQuery = $Connector->prepare(  "SELECT ".RP_TABLE_PREFIX."Character.*, ".RP_TABLE_PREFIX."User.Login AS UserName ".
                                                            "FROM `".RP_TABLE_PREFIX."User` LEFT JOIN `".RP_TABLE_PREFIX."Character` USING(UserId) ".
                                                            "WHERE UserId = :UserId ".
                                                            "ORDER BY Mainchar, CharacterId ASC" );

                            $CharQuery->bindValue( ":UserId", $Data["UserId"], PDO::PARAM_INT );
                            $CharQuery->loop( function($CharData) use (&$AttendeeData)
                            {
                                $Character = Array(
                                    "id"        => $CharData["CharacterId"],
                                    "name"      => $CharData["Name"],
                                    "mainchar"  => $CharData["Mainchar"],
                                    "classname" => $CharData["Class"],
                                    "role1"     => $CharData["Role1"],
                                    "role2"     => $CharData["Role2"]
                                );
                                
                                array_push($AttendeeData["character"], $Character);
                            });
                            
                            array_push($Attendees, $AttendeeData);
                        }
                    });
                }

                // Fetch all registered and unblocked users

                $AllUsersQuery = $Connector->prepare(  "SELECT ".RP_TABLE_PREFIX."User.UserId ".
                                                    "FROM `".RP_TABLE_PREFIX."User` ".
                                                    "WHERE `Group` != \"none\"" );

                $AllUsersQuery->loop(function($User) use (&$Connector, &$MaxAttendanceId, &$EndTimestamp, &$Participants, &$Attendees)
                {
                    if ( !in_array( intval($User["UserId"]), $Participants ) )
                    {
                        // Users that are not registered for this raid are undecided
                        // Fetch their character data, maincharacter first

                        $CharQuery = $Connector->prepare(  "SELECT ".RP_TABLE_PREFIX."Character.*, ".RP_TABLE_PREFIX."User.Login AS UserName ".
                                                        "FROM `".RP_TABLE_PREFIX."Character` LEFT JOIN `".RP_TABLE_PREFIX."User` USING(UserId) ".
                                                        "WHERE UserId = :UserId AND Created < FROM_UNIXTIME(:RaidEnd) ".
                                                        "ORDER BY Mainchar, CharacterId ASC" );
                                                        
                        $CharQuery->bindValue( ":UserId", $User["UserId"], PDO::PARAM_INT );
                        $CharQuery->bindValue( ":RaidEnd", $EndTimestamp, PDO::PARAM_INT );
                        $UserData = $CharQuery->fetchFirstOfLoop();
                        
                        if ( $UserData != null )
                        {
                            // Absent user have no attendance Id, so we need to generate one
                            // that is not in use (for this raid).
                            
                            ++$MaxAttendanceId;
                                                        
                            $AttendeeData = Array(
                                "id"        => $MaxAttendanceId,
                                "hasId"     => false,
                                "userId"    => $UserData["UserId"],
                                "timestamp" => time(),
                                "charid"    => $UserData["CharacterId"],
                                "name"      => $UserData["Name"],
                                "mainchar"  => $UserData["Mainchar"],
                                "classname" => $UserData["Class"],
                                "role"      => $UserData["Role1"],
                                "role1"     => $UserData["Role1"],
                                "role2"     => $UserData["Role2"],
                                "status"    => "undecided",
                                "comment"   => "",
                                "character" => Array()
                            );

                            $CharQuery->loop(function($UserData) use (&$AttendeeData)
                            {
                                $Character = Array(
                                    "id"        => $UserData["CharacterId"],
                                    "name"      => $UserData["Name"],
                                    "mainchar"  => $UserData["Mainchar"],
                                    "classname" => $UserData["Class"],
                                    "role1"     => $UserData["Role1"],
                                    "role2"     => $UserData["Role2"]
                                );
                                
                                array_push($AttendeeData["character"], $Character);
                            });

                            array_push($Attendees, $AttendeeData);
                        }
                    }
                });
                
                $Out->pushValue("attendee", $Attendees);
            }

            if ( validRaidlead() )
            {
                msgQueryLocations( $aRequest );
            }
        }
        else
        {
            $Out->pushError(L("AccessDenied"));
        }
    }
?>