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
                    "Name"      => "ApiPrivate",
                    "IntValue"  => 0,
                    "TextValue" => $PrivateToken
                );
            }
            
            return $Settings["ApiPrivate"];
        }
    }

?>