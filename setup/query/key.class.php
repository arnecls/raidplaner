<?php
    class Key
    {
        public $Name;
        public $Type;
        
        public function __construct($aType, $aName)
        {
            $this->Name = $aName;
            $this->Type = $aType;
        }
        
        public function CreateText()
        {
            switch($this->Type)
            {
            case "primary":
                return "PRIMARY KEY (`".$this->Name."`)";
                
            case "fulltext":
                return "FULLTEXT KEY `".$this->Name."` (`".$this->Name."`)";
                
            case "unique";
                return "UNIQUE KEY `Unique_".$this->Name."` (`".$this->Name."`)";
            
            default:
                return "KEY `".$this->Name."` (`".$this->Name."`)";
            }
        }
    }
?>