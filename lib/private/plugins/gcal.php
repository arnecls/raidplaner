<?php

    set_include_path(dirname(__FILE__).PATH_SEPARATOR.get_include_path());

    include_once_exists(dirname(__FILE__).'/../../config/config.google.php');
    require_once('Google/Client.php');
    require_once('Google/Service/Calendar.php');
    
    // -------------------------------------------------------------------------
    
    if (defined("GOOGLE_CALENDAR") && GOOGLE_CALENDAR)
        array_push(PluginRegistry::$Classes, 'GoogleCalendar');
    
    // -------------------------------------------------------------------------
    
    class GoogleCalendar extends Plugin
    {
        private $mClient = null;
        private $mCalService = null;
        private $mToken = null;
        private $mLocations = null;
        private $mDateFormat = DATE_ATOM;
        
        private function authenticate()
        {
            $Out = Out::getInstance();
            
            try
            {
                // Google authentication process
                
                if ($this->mClient == null)
                {
                    $KeyFile = dirname(__FILE__).'/../../config/key.google.p12';
                    
                    if (!file_exists($KeyFile))
                    {
                        throw new Exception('Missing key file "lib/config/key.google.p12"');
                    }
                       
                    $Certificate = file_get_contents($KeyFile);
                    
                    $this->mClient = new Google_Client();
                    $this->mClient->setApplicationName("ppxraidplaner");
                    $this->mCalService = new Google_Service_Calendar($this->mClient);
                    
                    $AssertCredentials = new Google_Auth_AssertionCredentials(
                        GOOGLE_SERVICE_MAIL,
                        array(Google_Service_Calendar::CALENDAR),
                        $Certificate
                    );
                    
                    $this->mClient->setAssertionCredentials($AssertCredentials);
                }
                
                if ($this->mClient->getAuth()->isAccessTokenExpired()) 
                {
                    $this->mClient->getAuth()->refreshTokenWithAssertion($AssertCredentials);
                }
                
                $this->mToken = $this->mClient->getAccessToken();
                
                // Get locations
                
                $Locations = Api::queryLocation(null);
                
                $this->mLocations = Array();
                foreach($Locations as $Location)
                {
                    $this->mLocations[$Location["Id"]] = $Location["Name"];
                }
                
                return true;
            }
            catch (Exception $Ex)
            {
                $this->mClient = null;
                $Out->pushError($Ex->getMessage());
            }
            
            return false;
        }
        
        // ---------------------------------------------------------------------
        
        public function onRaidCreate($aRaidId)
        {
            if ($this->authenticate())
            {
                $Parameters = Array('raid' => $aRaidId);
                $RaidResult = Api::queryRaid($Parameters);
                
                if (count($RaidResult) > 0)
                {
                    $Raid = $RaidResult[0];
                    $LocationName = $this->mLocations[$Raid['LocationId']];
                    
                    try
                    {
                        $Start = new Google_Service_Calendar_EventDateTime();
                        $Start->setDateTime(date($this->mDateFormat, intval($Raid['Start'])));
                        $Start->setTimeZone('UTC');
                        
                        $End = new Google_Service_Calendar_EventDateTime();
                        $End->setDateTime(date($this->mDateFormat, intval($Raid['End'])));
                        $End->setTimeZone('UTC');
                        
                        $Properties = new Google_Service_Calendar_EventExtendedProperties();
                        $Properties->setShared(Array('RaidId' => $aRaidId));
                        
                        $Event = new Google_Service_Calendar_Event();
                        
                        $Event->setSummary($LocationName.' ('.$Raid['Size'].')');
                        $Event->setLocation($LocationName);
                        $Event->setDescription($Raid['Description']);
                        $Event->setStart($Start);
                        $Event->setEnd($End);
                        $Event->setExtendedProperties($Properties);
                        
                        $this->mCalService->events->insert(GOOGLE_CAL_ID, $Event);
                    }
                    catch(Exception $Ex)
                    { 
                        $Out = Out::getInstance();  
                        $Out->pushError($Ex->getMessage());
                    }
                }
            }
        }
        
        // ---------------------------------------------------------------------
        
        public function onRaidModify($aRaidId)
        {
            if ($this->authenticate())
            {
                $Parameters = Array('raid' => $aRaidId);
                $RaidResult = Api::queryRaid($Parameters);
                
                if (count($RaidResult) > 0)
                {
                    $Raid = $RaidResult[0];
                    $LocationName = $this->mLocations[$Raid['LocationId']];
                    
                    try
                    {
                        $Start = new Google_Service_Calendar_EventDateTime();
                        $Start->setDateTime(date($this->mDateFormat, intval($Raid['Start'])));
                        $Start->setTimeZone('UTC');
                        
                        $End = new Google_Service_Calendar_EventDateTime();
                        $End->setDateTime(date($this->mDateFormat, intval($Raid['End'])));
                        $End->setTimeZone('UTC');
                        
                        $Events = $this->mCalService->events->listEvents(GOOGLE_CAL_ID, Array(
                            'sharedExtendedProperty' => 'RaidId='.$aRaidId
                        ));
                        
                        foreach ($Events->getItems() as $Event) 
                        {
                            $Event->setSummary($LocationName.' ('.$Raid['Size'].')');
                            $Event->setLocation($LocationName);
                            $Event->setDescription($Raid['Description']);
                        
                            $Event->setStart($Start);
                            $Event->setEnd($End);
                            
                            $this->mCalService->events->update(GOOGLE_CAL_ID, $Event->getid(), $Event);
                        }
                    }
                    catch(Exception $Ex)
                    { 
                        $Out = Out::getInstance();  
                        $Out->pushError($Ex->getMessage());
                    }
                }
            }
        }
        
        // ---------------------------------------------------------------------
        
        public function onRaidRemove($aRaidId)
        {
            if ($this->authenticate())
            {
                try
                {
                    $Events = $this->mCalService->events->listEvents(GOOGLE_CAL_ID, Array(
                        'sharedExtendedProperty' => 'RaidId='.$aRaidId
                    ));
                    
                    foreach ($Events->getItems() as $Event) 
                    {
                        $this->mCalService->events->delete(GOOGLE_CAL_ID, $Event->getid());
                    }
                }
                catch(Exception $Ex)
                { 
                    $Out = Out::getInstance();  
                    $Out->pushError($Ex->getMessage());
                }
            }
        }
    }
?>