<?php

    require_once(dirname(__FILE__)."/connector.class.php");
    
    class Settings implements ArrayAccess
    {
        private static $mInstance = NULL;
        private $Property = Array();
    
        // --------------------------------------------------------------------------------------------
    
        public function __construct()
        {
            $this->refresh();
        }
        
        // --------------------------------------------------------------------------------------------
    
        public function __destruct()
        {
            $this->serialize();
        }
    
        // --------------------------------------------------------------------------------------------
    
        public static function getInstance()
        {
            if (self::$mInstance == NULL)
            {
                self::$mInstance = new Settings();
            }
    
            return self::$mInstance;
        }
    
        // --------------------------------------------------------------------------------------------
    
        public function getProperties()
        {
            return $this->Property;
        }
    
        // --------------------------------------------------------------------------------------------
    
        public function refresh()
        {
            $Connector = Connector::getInstance();
            $Query = $Connector->prepare( "SELECT * FROM `".RP_TABLE_PREFIX."Setting` ORDER BY Name" );
    
            $Property = array();
    
            $Query->loop( function($Data) use (&$Property)
            {
                $Property[$Data["Name"]] = Array(
                    "IntValue"  => intval($Data["IntValue"]),
                    "TextValue" => $Data["TextValue"]
                );
            });
    
            $this->Property = $Property;
        }
    
        // --------------------------------------------------------------------------------------------
    
        public function serialize()
        {
            $Connector = Connector::getInstance();
            
            // Get existing settings
            
            $TestQuery = $Connector->prepare( "SELECT * FROM `".RP_TABLE_PREFIX."Setting`" );
            $ExistingValues = Array();
    
            $TestQuery->loop( function($Row) use (&$ExistingValues)
            {
                $ExistingValues[$Row["Name"]] = $Row;
            });
            
            $ExistingSettings = array_keys($ExistingValues);
                        
            // Update / insert settings
            
            foreach($this->Property as $Name => $Property)
            {
                $Index = array_search($Name, $ExistingSettings);
                
                if ( $Index === false )
                {
                    $InsertQuery = $Connector->prepare("INSERT INTO `".RP_TABLE_PREFIX."Setting` (Name, IntValue, TextValue) VALUES (:Name, :IntValue, :TextValue)");
                    
                    $IntValue  = (isset($Property["IntValue"]))  ? intval($Property["IntValue"]) : 0;
                    $TextValue = (isset($Property["TextValue"])) ? $Property["TextValue"] : "";
                    
                    
                    $InsertQuery->bindValue(":IntValue",  $IntValue,  PDO::PARAM_INT);
                    $InsertQuery->bindValue(":TextValue", $TextValue, PDO::PARAM_STR);
                    $InsertQuery->bindValue(":Name",      $Name,      PDO::PARAM_STR);                        
                    $InsertQuery->execute();
                }
                else
                {
                    $CurrentValue = $ExistingValues[$Name];
                    
                    if ( ($CurrentValue["IntValue"] != $Property["IntValue"]) ||
                         ($CurrentValue["TextValue"] != $Property["TextValue"]) )
                    {                        
                        $UpdateQuery = $Connector->prepare("UPDATE `".RP_TABLE_PREFIX."Setting` SET IntValue=:IntValue, TextValue=:TextValue WHERE Name=:Name LIMIT 1");
                        
                        $UpdateQuery->bindValue(":IntValue",  intval($Property["IntValue"]), PDO::PARAM_INT);
                        $UpdateQuery->bindValue(":TextValue", $Property["TextValue"],        PDO::PARAM_STR);
                        $UpdateQuery->bindValue(":Name",      $Name,                         PDO::PARAM_STR);                        
                        $UpdateQuery->execute();
                    }
                    
                    array_splice($ExistingSettings, $Index, 1);
                }
            }
            
            // Remove settings
            
            foreach($ExistingSettings as $Setting)
            {
                $DropQuery = $Connector->prepare("DELETE FROM `".RP_TABLE_PREFIX."Setting` WHERE Name=:Name LIMIT 1");
                $DropQuery->bindValue(":Name", $Setting, PDO::PARAM_STR);
                $DropQuery->execute();
            }
        }
        
        // ---------------------------------------------------------------------
        //  Array access
        // ---------------------------------------------------------------------
        
        public function offsetExists( $aOffset )
        {
            return isset($this->Property[$aOffset]);            
        }
        
        // ---------------------------------------------------------------------
        
        public function &offsetGet( $aOffset )
        {
            return $this->Property[$aOffset];
        }
        
        // ---------------------------------------------------------------------
        
        public function offsetSet( $aOffset, $aValue )
        {
            $this->Property[$aOffset] = $aValue;
        }
        
        // ---------------------------------------------------------------------
        
        public function offsetUnset( $aOffset )
        {
            unset($this->Property[$aOffset]);         
        }
    }

?>