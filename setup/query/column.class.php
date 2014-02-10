<?php
    class Column 
    {
        public $Name;
        public $Type;
        public $Size;
        public $Options;
        
        // ---------------------------------------------------------------------
        
        public function __construct($aName, $aType, $aSize, $aOptions)
        {
            $this->Name = $aName;
            $this->Type = $aType;
            $this->Size = $aSize;
            $this->Options = $aOptions;
            
            if (is_array($aSize))
            {
                $this->Size = "";
                $FirstValue = true;
                
                foreach($aSize as $EnumValue)
                {
                    $this->Size .= (($FirstValue) ? "" : ",")."'".$EnumValue."'";
                    $FirstValue = false;
                }
            }
        }
        
        // ---------------------------------------------------------------------
        
        public function CreateText()
        {
            $Line = "`".$this->Name."` ".$this->Type;
            
            if ($this->Size != null)
                $Line .= "(".$this->Size.")";
               
            if ($this->Options != null)
                $Line .= " ".implode(" ", $this->Options);
            
            return $Line;
        }
        
        // ---------------------------------------------------------------------
        
        public function AlterText($aTable)
        {
            $Line = "ALTER TABLE `".$aTable."` CHANGE `".$this->Name."` `".$this->Name."` ".$this->Type;
            
            if ($this->Size != null)
                $Line .= "(".$this->Size.")";
               
            if ($this->Options != null)
                $Line .= " ".implode(" ", $this->Options);
            
            return $Line;
        }
        
        // ---------------------------------------------------------------------
        
        public function AddText($aTable)
        {
            $Line = "ALTER TABLE `".$aTable."` ADD `".$this->Name."` ".$this->Type;
            
            if ($this->Size != null)
                $Line .= "(".$this->Size.")";
               
            if ($this->Options != null)
                $Line .= " ".implode(" ", $this->Options);
            
            return $Line;
        }
        
        // ---------------------------------------------------------------------
                
        public function HasType($aSQLType)
        {
            $Type = $this->Type;
            
            if ($this->Size != null)
                $Type .= "(".$this->Size.")";
                
            if (in_array("unsigned", $this->Options))
                $Type .= " unsigned";
            
            return $Type == $aSQLType;
        }
        
        // ---------------------------------------------------------------------
        
        public function IsNull($aState)
        {
            return ($aState == !in_array("NOT NULL", $this->Options)) || 
                   ($aState == (!$this->Type == "timestamp"));
        }
        
        // ---------------------------------------------------------------------
        
        public function HasDefault($aDefaultValue)
        {
            return ($aDefaultValue == null) || 
                   in_array("DEFAULT ".$aDefaultValue, $this->Options) || 
                   in_array("DEFAULT '".$aDefaultValue."'", $this->Options);
        }
        
        // ---------------------------------------------------------------------
        
        public function HasExtra($aExtra)
        {
            return ($aExtra == null) || in_array($aExtra, $this->Options) || in_array(strtoupper($aExtra), $this->Options); 
        }
    }
?>