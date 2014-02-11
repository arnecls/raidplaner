<?php

    $gApiHelp = Array();

    require_once(dirname(__FILE__)."/tools_site.php");
    require_once(dirname(__FILE__)."/api_help.php");
    require_once(dirname(__FILE__)."/api_raid.php");
    require_once(dirname(__FILE__)."/api_location.php");
    require_once(dirname(__FILE__)."/api_statistic.php");

    class Api
    {
        private static $PublicUpdateInterval = 2; // Seconds until new public key
    
        // ---------------------------------------------------------------------
                
        public static function getPrivateToken()
        {
            $Settings = Settings::getInstance();
            
            if (!isset($Settings["ApiPrivate"])) 
            {
                $PrivateToken = dechex(crc32(openssl_random_pseudo_bytes(2048))).
                    dechex(crc32(openssl_random_pseudo_bytes(2048)));
                
                $Settings["ApiPrivate"] = Array(
                    "IntValue"  => 0,
                    "TextValue" => $PrivateToken
                );
            }
            
            return $Settings["ApiPrivate"]["TextValue"];
        }
        
        // ---------------------------------------------------------------------
        
        public static function getPublicToken()
        {
            $Settings = Settings::getInstance();
            
            if (!isset($Settings["ApiPublic"])) 
                self::tryGeneratePublicToken();
            
            return $Settings["ApiPublic"]["TextValue"];
        }
        
        // ---------------------------------------------------------------------
        
        public static function tryGeneratePublicToken()
        {
            $Settings = Settings::getInstance();
            
            if ($Settings["ApiPublic"]["IntValue"] < time()) 
            {
                $PublicToken = md5(openssl_random_pseudo_bytes(2048));
                
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
        
        public static function testPublicToken($aToken)
        {
            $Settings = Settings::getInstance();
            
            // Make sure token is regenerated on a regular basis
            
            self::tryGeneratePublicToken();
            
            if ($Settings["ApiPublic"]["TextValue"] == $aToken)
                return true;
                
            // Make sure to test the timeout of old values (long-time update gap)
                
            return (isset($Settings["ApiPublicOld"]) &&
                ($Settings["ApiPublicOld"]["IntValue"] + self::$PublicUpdateInterval > time()) &&
                ($Settings["ApiPublicOld"]["TextValue"] == $aToken));
        }
        
        // ---------------------------------------------------------------------
        
        public static function queryLocations()
        {
            return api_query_location();
        }
        
        // ---------------------------------------------------------------------
        
        public static function queryRaid($aParameter)
        {
            return api_query_raid($aParameter);
        }
        
        // ---------------------------------------------------------------------
        
        public static function queryStatistic($aParameter)
        {
            return api_query_statistic($aParameter);
        }
    }

?>