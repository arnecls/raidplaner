<?php

    require_once(dirname(__FILE__)."/settings.class.php");

    class Api
    {
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
            
            return $Settings["ApiPrivate"];
        }
        
        // ---------------------------------------------------------------------
        
        public static function getPublicToken()
        {
            $Settings = Settings::getInstance();
            
            if (!isset($Settings["ApiPublic"]) || 
                ($Settings["ApiPublic"]["IntValue"] < time())) 
            {
                $PublicToken = md5(openssl_random_pseudo_bytes(2048));
                
                $Settings["ApiPublic"] = Array(
                    "IntValue"  => time() + 60,
                    "TextValue" => $PublicToken
                );
            }
            
            return $Settings["ApiPublic"]["TextValue"];
        }
    }

?>