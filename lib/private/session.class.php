<?php
    
    require_once dirname(__FILE__)."/connector.class.php";
    require_once dirname(__FILE__)."/random.class.php";
    
    class Session implements ArrayAccess
    {
        private static $SessionCookieName = "ppx_raidplaner_";
        private static $SessionTimeOut = 3600; // 1 Hour
        private static $Instance = null;
    
        private $SessionId;
        private $UserId;
        private $Expires;
        private $Data;
        private $IsDirty;
        
        // ---------------------------------------------------------------------
        
        private function __construct($aSessionName)
        {
            self::dropExpired();
            
            $Connector = Connector::getInstance();
            $SessionQuery = $Connector->prepare("SELECT SessionId, UserId, UNIX_TIMESTAMP(Expires) AS Expires, Data FROM `".RP_TABLE_PREFIX."Session` WHERE SessionName = :Name LIMIT 1");
            
            $SessionQuery->bindValue(":Name", $aSessionName, PDO::PARAM_STR);            
            $SessionData = $SessionQuery->fetchFirst();
            
            if ($SessionData == null)
            {
                throw new Exception();
            }
            
            $this->SessionId = $SessionData["SessionId"];
            $this->UserId = $SessionData["UserId"];
            $this->Expires = $SessionData["Expires"];
            $this->Data = unserialize($SessionData["Data"]);
            $this->IsDirty = false;            
        }
        
        // ---------------------------------------------------------------------
        
        public function __destruct()
        {
            $this->serialize();
        }
        
        // ---------------------------------------------------------------------
        
        public function refresh()
        {
            if (($this->SessionId != 0) &&
                ($this->Expires - time() < self::$SessionTimeOut/2) )
            {   
                $Connector = Connector::getInstance();
                
                $this->Expires = time() + self::$SessionTimeOut;
                $SessionName = self::generateKey40();
            
                $UpdateData = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."Session` SET SessionName=:Name, Expires=FROM_UNIXTIME(:Expires) WHERE SessionId=:SessionId LIMIT 1");
                
                $UpdateData->bindValue(":Expires",   intval($this->Expires), PDO::PARAM_INT);
                $UpdateData->bindValue(":SessionId", intval($this->SessionId), PDO::PARAM_INT);
                $UpdateData->bindValue(":Name",      $SessionName, PDO::PARAM_STR);
            
                if ($UpdateData->execute())
                {
                    $CookieName = self::getCookieName();            
                    if (isset($_COOKIE[$CookieName]))
                    {
                        setcookie($CookieName, $SessionName, $this->Expires);
                    }
                }
            }
        }
        
        // ---------------------------------------------------------------------
        
        public function serialize()
        {
            if ($this->IsDirty && ($this->SessionId != 0))
            {
                $Connector = Connector::getInstance();
                $UpdateData = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."Session` SET Data=:Data WHERE SessionId=:SessionId LIMIT 1");
                
                $UpdateData->bindValue(":SessionId", intval($this->SessionId), PDO::PARAM_INT);
                $UpdateData->bindValue(":Data", serialize($this->Data), PDO::PARAM_STR);
                
                if ($UpdateData->execute())
                    $this->IsDirty = false;
            }
        }
        
        // ---------------------------------------------------------------------
        //  Array access
        // ---------------------------------------------------------------------
        
        public function getUserId()
        {
            return $this->UserId;
        }
        
        // ---------------------------------------------------------------------
        
        public function offsetExists( $aOffset )
        {
            return isset($this->Data[$aOffset]);            
        }
        
        // ---------------------------------------------------------------------
        
        public function &offsetGet( $aOffset )
        {
            return $this->Data[$aOffset];
        }
        
        // ---------------------------------------------------------------------
        
        public function offsetSet( $aOffset, $aValue )
        {
            $this->Data[$aOffset] = $aValue;
            $this->IsDirty = true;     
        }
        
        // ---------------------------------------------------------------------
        
        public function offsetUnset( $aOffset )
        {
            unset($this->Data[$aOffset]);         
        }
        
        // ---------------------------------------------------------------------
        //  Global session functions
        // ---------------------------------------------------------------------
        
        private static function getCookieName()
        {
            $SiteId = dechex(crc32(dirname(__FILE__)));
            return self::$SessionCookieName.$SiteId;
        }
        
        // ---------------------------------------------------------------------
        
        private static function generateKey40()
        {
            return sha1(Random::getBytes(2048));
        }
        
        // ---------------------------------------------------------------------
        
        public static function isActive()
        {
            return self::$Instance != null;
        }
        
        // ---------------------------------------------------------------------
        
        public static function get()
        {
            if (self::IsActive())
                return self::$Instance; // ### return, active session ###
                
            try
            {
                $CookieName = self::GetCookieName();                
                self::$Instance = (isset($_COOKIE[$CookieName])) 
                    ? new Session($_COOKIE[$CookieName])
                    : null;
                
                return self::$Instance; // ### return, revoked session ###
            }
            catch(Exception $e)
            {
                return null;
            }
        }
        
        // ---------------------------------------------------------------------
        
        public static function create($aUserId, $aExpiresInSec=3600)
        {
            if (self::IsActive())
                return null; // ### return, session already active ###
                
            try
            {
                $Connector = Connector::getInstance();
                $CreateSession = $Connector->prepare("INSERT INTO `".RP_TABLE_PREFIX."Session` (UserId, SessionName, IpAddress, Expires, Data) ".
                    "VALUES (:UserId, :Name, :Ip, FROM_UNIXTIME(:Expires), :Data)");
                
                $SessionName = self::generateKey40();
                $Expires = time() + $aExpiresInSec;
                            
                $CreateSession->bindValue(":UserId",  intval($aUserId),        PDO::PARAM_INT);
                $CreateSession->bindValue(":Name",    $SessionName,            PDO::PARAM_STR);
                $CreateSession->bindValue(":Ip",      $_SERVER["REMOTE_ADDR"], PDO::PARAM_STR);
                $CreateSession->bindValue(":Expires", intval($Expires),        PDO::PARAM_INT);
                $CreateSession->bindValue(":Data",    serialize(Array()),      PDO::PARAM_STR);  
                
                if ($CreateSession->execute())
                {
                    self::$Instance = new Session($SessionName);
                    
                    $ServerName      = "";
                    $ServerPath      = "";
                    $ServerUsesHttps = isset($_SERVER["HTTPS"]) && ($_SERVER["HTTPS"] != "") && ($_SERVER["HTTPS"] != null) && ($_SERVER["HTTPS"] != "off");
                    setcookie( self::GetCookieName(), $SessionName, $Expires, $ServerPath, $ServerName, $ServerUsesHttps, true );
                    
                    return self::$Instance; // ### return, new session ###
                }
            }
            catch(Exception $e)
            {
                return null;
            }
        }
        
        // ---------------------------------------------------------------------
        
        public static function release()
        {
            if (!self::IsActive())
                return; // ### return, no active session ###
                        
            $Connector = Connector::getInstance();
            $DropSessions = $Connector->prepare("DELETE FROM `".RP_TABLE_PREFIX."Session` WHERE SessionId = :SessionId LIMIT 1");
            
            $DropSessions->bindValue(":SessionId", intval(self::$Instance->SessionId), PDO::PARAM_INT);
            $DropSessions->execute();
            
            self::$Instance->SessionId = 0;
            self::$Instance->Data = null;
            self::$Instance->IsDirty = false;
            
            $CookieName = self::GetCookieName();
            
            if (isset($_COOKIE[$CookieName]))
                unset($_COOKIE[$CookieName]);
                
            self::$Instance = null;
        }
        
        // ---------------------------------------------------------------------
        
        public static function dropExpired()
        {
            $Connector = Connector::getInstance();
            $DropSessions = $Connector->prepare("DELETE FROM `".RP_TABLE_PREFIX."Session` WHERE Expires <= CURRENT_TIMESTAMP");
            $DropSessions->execute();
        }    
    }
?>