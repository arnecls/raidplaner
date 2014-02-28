<?php

    $gApiHelp = Array();

    require_once(dirname(__FILE__)."/tools_site.php");
    require_once(dirname(__FILE__)."/api_raid.php");
    require_once(dirname(__FILE__)."/api_location.php");
    require_once(dirname(__FILE__)."/api_user.php");
    require_once(dirname(__FILE__)."/api_statistic.php");
    require_once(dirname(__FILE__)."/api_help.php");

    // The functions used in this file can safely be used by plugins or other
    // raidplaner extensions. API modifications will be avoided if possible and
    // documented if necessary.
    class Api
    {
        private static $PublicUpdateInterval = 1800; // Seconds until new public key
        private static $Plugins = Array();
        
        // ---------------------------------------------------------------------
        
        // Returns the private token. If no token has been created, yet a new
        // token will be generated.     
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
        
        // Returns a public token for the passed parameter set. This token is
        // guaranteed to be valid for Api::$PublicUpdateInterval seconds.     
        public static function getPublicToken($aParameter)
        {
            $Settings = Settings::getInstance();
            
            if (!isset($Settings["ApiPublic"])) 
                self::tryGeneratePublicToken();
            
            return sha1(serialize($aParameter).$Settings["ApiPublic"]["TextValue"]);
        }
        
        // ---------------------------------------------------------------------
        
        // Try to generate a new public token. This function is called by
        // Api::testPublicToken, but there might be situations where you want
        // to genreate a new token manually.
        // This function takes Api::$PublicUpdateInterval into account.
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
        
        // Test if the given token equals the private token.
        public static function testPrivateToken($aToken)
        {
            return self::getPrivateToken() == $aToken;
        }
        
        // ---------------------------------------------------------------------
        
        // Test if the given public tokan is valid for a given set of
        // parameters. This function will call Api::tryGeneratePublicToken to
        // assure the public token lifetime.
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
        
        // Filter the given set of parameters so that only known location
        // parameters are included. Missing location parameters will be added
        // with their default value.
        public static function normalizeArgsLocation($aParameter)
        {
            return api_args_location($aParameter);
        }
        
        // ---------------------------------------------------------------------
        
        // Returns a list of locations. This function is the PHP equivalent to
        // apihub.php?query=location. A token does not need to be passed.
        public static function queryLocation($aParameter)
        {
            return api_query_location($aParameter);
        }
        
        // ---------------------------------------------------------------------
        
        // Filter the given set of parameters so that only known user
        // parameters are included. Missing user parameters will be added
        // with their default value.
        public static function normalizeArgsUser($aParameter)
        {
            return api_args_user($aParameter);
        }
        
        // ---------------------------------------------------------------------
                
        // Returns a list of users. This function is the PHP equivalent to
        // apihub.php?query=user. A token does not need to be passed.
        public static function queryUser($aParameter)
        {
            return api_query_user($aParameter);
        }
        
        // ---------------------------------------------------------------------
        
        // Filter the given set of parameters so that only known raid
        // parameters are included. Missing raid parameters will be added
        // with their default value.
        public static function normalizeArgsRaid($aParameter)
        {
            return api_args_raid($aParameter);
        }
        
        // ---------------------------------------------------------------------
        
        // Returns a list of raids. This function is the PHP equivalent to
        // apihub.php?query=raid. A token does not need to be passed.
        public static function queryRaid($aParameter)
        {
            return api_query_raid($aParameter);
        }
        
        // ---------------------------------------------------------------------
        
        // Filter the given set of parameters so that only known statistic
        // parameters are included. Missing statistic parameters will be added
        // with their default value.
        public static function normalizeArgsStatistic($aParameter)
        {
            return api_args_statistic($aParameter);
        }
        
        // ---------------------------------------------------------------------
        
        // Returns a list of user statistics. This function is the PHP equivalent
        // to apihub.php?query=statistic. A token does not need to be passed.
        public static function queryStatistic($aParameter)
        {
            return api_query_statistic($aParameter);
        }
    }

?>