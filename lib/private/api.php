<?php

    $gApiHelp = Array();

    require_once(dirname(__FILE__)."/tools_site.php");
    require_once(dirname(__FILE__)."/api_raid.php");
    require_once(dirname(__FILE__)."/api_location.php");
    require_once(dirname(__FILE__)."/api_user.php");
    require_once(dirname(__FILE__)."/api_statistic.php");
    require_once(dirname(__FILE__)."/api_help.php");

    class Api
    {
        private static $PublicUpdateInterval = 1800; // Seconds until new public key
    
        // ---------------------------------------------------------------------
                
        public static function getPrivateToken()
        {
            $Settings = Settings::getInstance();
            
            if (!isset($Settings["ApiPrivate"])) 
            {
                $PrivateToken = dechex(crc32(Random::getBytes(2048))).
                    dechex(crc32(Random::getBytes(2048)));
                
                $Settings["ApiPrivate"] = Array(
                    "IntValue"  => 0,
                    "TextValue" => $PrivateToken
                );
            }
            
            return $Settings["ApiPrivate"]["TextValue"];
        }
        
        // ---------------------------------------------------------------------
        
        public static function getPublicToken($aParameter)
        {
            $Settings = Settings::getInstance();
            
            if (!isset($Settings["ApiPublic"])) 
                self::tryGeneratePublicToken();
            
            return sha1(serialize($aParameter).$Settings["ApiPublic"]["TextValue"]);
        }
        
        // ---------------------------------------------------------------------
        
        public static function tryGeneratePublicToken()
        {
            $Settings = Settings::getInstance();
            
            if ($Settings["ApiPublic"]["IntValue"] < time()) 
            {
                $PublicToken = md5(Random::getBytes(2048));
                
                // Store old token so that running requests don't fail
                
                if (isset($Settings["ApiPublic"]))
                {
                    $Settings["ApiPublicOld"] = Array(
                        "IntValue"  => $Settings["ApiPublic"]["IntValue"],
                        "TextValue" => $Settings["ApiPublic"]["TextValue"]
                    );
                }
                
                // New token
                
                $Settings["ApiPublic"] = Array(
                    "IntValue"  => time() + self::$PublicUpdateInterval,
                    "TextValue" => $PublicToken
                );
            }
        }
        
        // ---------------------------------------------------------------------
        
        public static function testPrivateToken($aToken)
        {
            return self::getPrivateToken() == $aToken;
        }
        
        // ---------------------------------------------------------------------
        
        public static function testPublicToken($aParameter, $aToken)
        {
            $Settings = Settings::getInstance();
            
            // Make sure token is regenerated on a regular basis
            
            self::tryGeneratePublicToken();
            
            // Check against current public hash
            
            $TestHash = sha1(serialize($aParameter).$Settings["ApiPublic"]["TextValue"]);            
            if ($TestHash == $aToken)
                return true; // ### return, success ###
            
            // Make sure to test the timeout of old values (long-time update gap)
                
            if (!isset($Settings["ApiPublicOld"]) ||
                ($Settings["ApiPublicOld"]["IntValue"] + self::$PublicUpdateInterval > time()))
            {
                return false; // ### return, invalid old key ###
            }
            
            // Old value is exisiting and valid, try hash this value
               
            $TestHash = sha1(serialize($aParameter).$Settings["ApiPublicOld"]["TextValue"]);                
            return ($TestHash == $aToken);
        }
        
        // ---------------------------------------------------------------------
        
        public static function normalizeArgsLocation($aParameter)
        {
            return api_args_location($aParameter);
        }
        
        // ---------------------------------------------------------------------
        
        public static function queryLocation()
        {
            return api_query_location();
        }
        
        // ---------------------------------------------------------------------
        
        public static function normalizeArgsUser($aParameter)
        {
            return api_args_user($aParameter);
        }
        
        // ---------------------------------------------------------------------
                
        public static function queryUser($aParameter)
        {
            return api_query_user($aParameter);
        }
        
        // ---------------------------------------------------------------------
        
        public static function normalizeArgsRaid($aParameter)
        {
            return api_args_raid($aParameter);
        }
        
        // ---------------------------------------------------------------------
        
        public static function queryRaid($aParameter)
        {
            return api_query_raid($aParameter);
        }
        
        // ---------------------------------------------------------------------
        
        public static function normalizeArgsStatistic($aParameter)
        {
            return api_args_statistic($aParameter);
        }
        
        // ---------------------------------------------------------------------
        
        public static function queryStatistic($aParameter)
        {
            return api_query_statistic($aParameter);
        }
    }

?>