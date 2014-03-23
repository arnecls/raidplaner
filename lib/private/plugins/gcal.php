<?php

    // Raidplaner plugin for Google Caledar support.
    // This plugin requires two additional files as well as the Google PHP API:
    //
    // lib/config/config.google.php     Plugin configuration
    // lib/config/key.google.p12        Private key of a Google Service
    //
    // You can find a guide to configure this plugin on GitHub

    set_include_path(dirname(__FILE__).PATH_SEPARATOR.get_include_path());

    include_once_exists(dirname(__FILE__).'/../../config/config.google.php');
    require_once('Google/Client.php');
    require_once('Google/Service/Calendar.php');
    
    // -------------------------------------------------------------------------
    
    // This will register the plugin to the global plugin registry if it is
    // explicitly enabled in the config file
    
    if (defined('GOOGLE_CALENDAR') && GOOGLE_CALENDAR)
        array_push(PluginRegistry::$Classes, 'GoogleCalendar');
    
    // -------------------------------------------------------------------------
    
    class GoogleCalendar extends Plugin
    {
        private $mClient = null;
        private $mCalService = null;
        private $mToken = null;
        private $mLocations = null;
        private $mDateFormat = DATE_ATOM;
        
        // ---------------------------------------------------------------------
        
        // This function authenticate against the Google API and does some one-
        // time setups.
        
        private function authenticate()
        {
            $Out = Out::getInstance();
            
            try
            {
                // Google authentication process
                // For details see the Google PHP API documentation
                
                if ($this->mClient == null)
                {
                    $KeyFile = dirname(__FILE__).'/../../config/key.google.p12';
                    
                    if (!file_exists($KeyFile))
                    {
                        throw new Exception('Missing key file "lib/config/key.google.p12"');
                    }
                       
                    $Certificate = file_get_contents($KeyFile);
                    
                    $this->mClient = new Google_Client();
                    $this->mClient->setApplicationName('ppxraidplaner');
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
                // This is an initial setup step upon first call of this function.
                
                $Locations = Api::queryLocation(null);
                
                $this->mLocations = Array();
                foreach($Locations as $Location)
                {
                    $this->mLocations[$Location['Id']] = $Location['Name'];
                }
                
                return true;
            }
            catch (Exception $Ex)
            {
                // Make sure any exceptions are properly passed to the UI
                
                $this->mClient = null;
                $Out->pushError($Ex->getMessage());
            }
            
            return false;
        }
        
        // ---------------------------------------------------------------------
        
        // This function handles the creation of new raids.
        // It will try to authenticate, fetch the created raid and create a
        // Google Calendar event from the given information.
        // Note that the RaidId is stored in the Google Calendar event to
        // simplify searches for a specific event later on.
        
        public function onRaidCreate($aRaidId)
        {
            if ($this->authenticate())
            {
                // Query the given raid, make sure we include canceled and closed 
                // raids
                
                $Parameters = Array('raid' => $aRaidId, 'canceled' => true, 'closed' => true);
                $RaidResult = Api::queryRaid($Parameters);
                
                if (count($RaidResult) > 0)
                {
                    // As we specified a specific raid id, we are only 
                    // interested in the first (and only) raid.
                    // Cache and set UTC timezone just to be sure.
                    
                    $Raid = $RaidResult[0];
                    $LocationName = $this->mLocations[$Raid['LocationId']];
                    $Url = getBaseURL().'index.php#raid,'.$aRaidId;
                    $Timezone = date_default_timezone_get();
                        
                    try
                    {
                        date_default_timezone_set('UTC');
        
                        $Start = new Google_Service_Calendar_EventDateTime();
                        $Start->setDateTime(date($this->mDateFormat, intval($Raid['Start'])));
                        $Start->setTimeZone('UTC');
                        
                        $End = new Google_Service_Calendar_EventDateTime();
                        $End->setDateTime(date($this->mDateFormat, intval($Raid['End'])));
                        $End->setTimeZone('UTC');
                        
                        $Properties = new Google_Service_Calendar_EventExtendedProperties();
                        $Properties->setShared(Array('RaidId' => $aRaidId));
                        
                        $Source = new Google_Service_Calendar_EventSource();
                        $Source->setTitle('Raidplaner link');
                        $Source->setUrl($Url);
                        
                        $Event = new Google_Service_Calendar_Event();
                        
                        $Event->setSummary($LocationName.' ('.$Raid['Size'].')');
                        $Event->setLocation($LocationName);
                        $Event->setDescription($Raid['Description']);
                        $Event->setOriginalStartTime($Start);
                        $Event->setStart($Start);
                        $Event->setEnd($End);
                        $Event->setExtendedProperties($Properties);
                        $Event->setSource($Source);
                        
                        $this->mCalService->events->insert(GOOGLE_CAL_ID, $Event);
                    }
                    catch(Exception $Ex)
                    { 
                        $Out = Out::getInstance();  
                        $Out->pushError($Ex->getMessage());
                    }
                    
                    date_default_timezone_set($Timezone);
                }
            }
        }
        
        // ---------------------------------------------------------------------
        
        // This function handles the update of existing raids.
        // It is pretty much the same as the create method if you strip out
        // the Google Calendar update part.
        
        public function onRaidModify($aRaidId)
        {
            if ($this->authenticate())
            {
                $Parameters = Array('raid' => $aRaidId, 'canceled' => true, 'closed' => true);
                $RaidResult = Api::queryRaid($Parameters);
                
                if (count($RaidResult) > 0)
                {
                    $Raid = $RaidResult[0];
                    $LocationName = $this->mLocations[$Raid['LocationId']];
                    $Timezone = date_default_timezone_get();
                    
                    try
                    {
                        date_default_timezone_set('UTC');
        
                        $Start = new Google_Service_Calendar_EventDateTime();
                        $Start->setDateTime(date($this->mDateFormat, intval($Raid['Start'])));
                        $Start->setTimeZone('UTC');
                        
                        $End = new Google_Service_Calendar_EventDateTime();
                        $End->setDateTime(date($this->mDateFormat, intval($Raid['End'])));
                        $End->setTimeZone('UTC');
                        
                        $Events = $this->mCalService->events->listEvents(GOOGLE_CAL_ID, Array(
                            'sharedExtendedProperty' => 'RaidId='.$aRaidId
                        ));
                        
                        // There should be only one event, but we're a bit 
                        // paranoid here
                        
                        foreach ($Events->getItems() as $Event) 
                        {
                            $Event->setLocation($LocationName);
                            $Event->setDescription($Raid['Description']);
                            
                            if ($Raid['Status'] == 'canceled')
                            {
                                $Event->setSummary('[canceled] '.$LocationName.' ('.$Raid['Size'].')');
                            }
                            else
                            {
                                $Event->setSummary($LocationName.' ('.$Raid['Size'].')');
                            }
                            
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
                    
                    date_default_timezone_set($Timezone);
                }
            }
        }
        
        // ---------------------------------------------------------------------
        
        // This function handles the deletion of raids.
        // The content is pretty straight forward and no Raidplaner API call
        // is required here.
        
        public function onRaidRemove($aRaidId)
        {
            if ($this->authenticate())
            {
                try
                {
                    $Events = $this->mCalService->events->listEvents(GOOGLE_CAL_ID, Array(
                        'sharedExtendedProperty' => 'RaidId='.$aRaidId
                    ));
                    
                    // Again, there should be only one event, but we're a bit 
                    // paranoid here
                        
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