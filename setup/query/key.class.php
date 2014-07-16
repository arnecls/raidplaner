<?php
    class Key
    {
        public $Columns;
        public $Type;
                
        // ---------------------------------------------------------------------
        
        public function __construct($aType, $aColumns)
        {
            $this->Columns = $aColumns;
            $this->Type = $aType;
        }
        
        // ---------------------------------------------------------------------
        
        public function CreateText()
        {
            $NameParts = explode(",", $this->Columns);
            $KeyName = "";
            
            foreach($NameParts as &$Name)
            {
                $Name = trim($Name);
                $KeyName .= $Name;
            }
            
            $Columns = implode("`,`",$NameParts);
                
            switch($this->Type)
            {
            case "primary":
                return "PRIMARY KEY (`".$Columns."`)";
                
            case "fulltext":
                return "FULLTEXT KEY `".$KeyName."` (`".$Columns."`)";
                
            case "unique";
                return "UNIQUE KEY `Unique_".$KeyName."` (`".$Columns."`)";
            
            default:
                return "KEY `".$KeyName."` (`".$Columns."`)";
            }
        }
    }
?>