<?php

    // This class tries to provide a random bytes generator that is
    // "as good as possible". If we only have weak rngs, we need
    // to stick with those.
    class Random 
    {
        public static function getBytes($aBytes)
        {
            $result = false;
            
            // Try OpenSSL random (preferred)
            
            if (extension_loaded('openssl'))
            {
                $result = openssl_random_pseudo_bytes($aBytes);
            }
            
            // Try mcrypt random
            
            if (($result === false) && extension_loaded('mcrypt'))
            {
                $result = mcrypt_create_iv($aBytes, MCRYPT_DEV_RANDOM);
                if ($result === false)
                {
                    $result = mcrypt_create_iv($aBytes, MCRYPT_RAND); 
                }
            }
            
            // If everything fails, use system random
            
            if ($result === false)
            {
                mt_srand();
                $ByteArray = Array();
                
                for ($i=0; $i<$aBytes; ++$i)
                {
                    array_push($ByteArray, mt_rand(0,255));
                }
                
                $result = call_user_func_array("pack", array_merge(array("C*"), $ByteArray));
            }
            
            return $result;
        }    
    }

?>