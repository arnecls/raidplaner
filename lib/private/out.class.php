<?php

    class Out
    {
        private static $Instance = null;
        private $Data;

        // --------------------------------------------------------------------------------------------

        public static function getInstance( $aRethrow = false )
        {
            if (self::$Instance == null)
                self::$Instance = new Out();

            return self::$Instance;
        }

        // --------------------------------------------------------------------------------------------

        public function __construct()
        {
            $this->Data = Array();
        }

        // --------------------------------------------------------------------------------------------

        // Add a key-value pair to the output stream.
        // If the same $aKey is used multiple times, it is converted to an array.
        public function pushValue($aKey, $aValue)
        {
            if (isset($this->Data[$aKey]))
            {
                if ($this->IsIndexedArray($this->Data[$aKey]))
                {

                    // current entry is already indexed
                    // append the new value

                    if ($this->IsIndexedArray($aValue))
                        $this->Data[$aKey] = array_merge($this->Data[$aKey], $aValue);
                    else
                        array_push($this->Data[$aKey], $aValue);
                }
                else
                {
                    // current entry is not indexed
                    // create a new array from old and new value (in that order)

                    if ($this->IsIndexedArray($aValue))
                        $this->Data[$aKey] = array_unshift($aValue, $this->Data[$aKey]);
                    else
                        $this->Data[$aKey] = Array($this->Data[$aKey], $aValue);
                }

            }
            else
            {
                $this->Data[$aKey] = $aValue;
            }
        }

        // --------------------------------------------------------------------------------------------

        // Shortcut for pushValue("error", $aMessage).
        public function pushError($aMessage)
        {
            if (!isset($this->Data["error"]))
                $this->pushValue("error", Array($aMessage));
            else
                $this->pushValue("error", $aMessage);

        }

        // --------------------------------------------------------------------------------------------

        // Clear the output buffer.
        public function clear()
        {
            $this->Data = Array();
        }

        // --------------------------------------------------------------------------------------------
        
        public static function writeHeadersJSON()
        {
            header("Cache-Control: no-cache, max-age=0, s-maxage=0");
            header("Content-type: application/json");
        }

        // --------------------------------------------------------------------------------------------
        
        public static function writeHeadersXML()
        {
            header("Cache-Control: no-cache, max-age=0, s-maxage=0");
            header("Content-type: application/xml");
            echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
        }
        
        // --------------------------------------------------------------------------------------------

        // Echo the output buffer as JSON and clear it 
        public function flushJSON()
        {

            $this->writeJSON();
            $this->Data = Array();
        }

        // --------------------------------------------------------------------------------------------

        // Echo the output buffer as XML and clear it 
        public function flushXML($aTagName)
        {

            $this->writeXML($aTagName);
            $this->Data = Array();
        }

        // --------------------------------------------------------------------------------------------

        // Echo the output buffer as JSON and stop script execution
        public function writeJSONandStop()
        {
            $this->writeJSON();
            die();
        }

        // --------------------------------------------------------------------------------------------

        // Echo the output buffer as XML and stop script execution
        public function writeXMLandStop($aTagName)
        {
            $this->writeXML($aTagName);
            die();
        }

        // --------------------------------------------------------------------------------------------

        // Echo the outputbuffer as JSON.
        // Shortcut for generateJSON(function($aString) { echo $aString; }, null)
        public function writeJSON()
        {
            $this->generateJSON(function($aString) { echo $aString; }, null);
        }
        
        // --------------------------------------------------------------------------------------------

        // Echo the outputbuffer as XML.
        // Shortcut for generateXML(function($aString) { echo $aString; }, null)
        public function writeXML($aTagName)
        {
            $this->generateXML(function($aString) { echo $aString; }, $aTagName, null);
        }
        
        // --------------------------------------------------------------------------------------------

        // Convert the output buffer into a valid JSON string.
        // $aOnOut must be a function accepting a string.
        // If $aData is not null, the passed buffer is used instead of the stored output buffer.
        public function generateJSON($aOnOut, $aData = null)
        {
            $Root = ($aData == null) 
                ? $this->Data 
                : $aData;

            $IsIndexedArray = $this->IsIndexedArray($Root);

            $i = 0;
            $aOnOut(($IsIndexedArray) ? '[' : '{');

            foreach( $Root as  $Name => $Value )
            {
                if ($i > 0) $aOnOut(',');
                if (!$IsIndexedArray) $aOnOut('"'.$Name.'":');

                if ($Value === null)
                {
                    $aOnOut("null");
                }
                else if (is_array($Value))
                {
                    if (empty($Value))
                        $aOnOut("[]");
                    else
                        $this->generateJSON($aOnOut, $Value);
                }
                else if (is_numeric($Value))
                {
                    $aOnOut($Value);
                }
                else if (($Value === true) || ($Value == "true"))
                {
                    $aOnOut('true');
                }
                else if (($Value === false) || ($Value == "false"))
                {
                    $aOnOut('false');
                }
                else
                {
                    $sanitized = str_replace("\n", "</br>", $Value);
                    $sanitized = str_replace("\\", "\\\\", $sanitized);
                    $aOnOut('"'.$sanitized.'"');
                }

                ++$i;
            }

            $aOnOut(($IsIndexedArray) ? ']' : '}');
        }

        // --------------------------------------------------------------------------------------------

        // Convert the output buffer into a valid XML string.
        // $aOnOut must be a function accepting a string.
        // If $aData is not null, the passed buffer is used instead of the stored output buffer.
        // $aAttributes may contain a key/value array of attributes to add to a tag.
        public function generateXML($aOnOut, $aTagName, $aData = null, $aAttributes = null)
        {
            $Root = ($aData === null) 
                ? $this->Data 
                : $aData;
                
            if (($Root === null) || 
                (is_array($Root) && (count($Root) == 0)))
            {
                $aOnOut("<".$aTagName."/>");
                return;
            }
            
            $IsIndexedArray = $this->IsIndexedArray($Root);
            $IsNumericArray = $this->IsNumericArray($Root);
            $Attributes = "";
            
            if ($aAttributes !== null)
            {
                foreach($aAttributes as $Name => $Value)
                {
                    $Attributes .= " ".$Name."=\"".$Value."\"";
                }
            }            
            
            if (!$IsIndexedArray && !$IsNumericArray)
                $aOnOut("<".$aTagName.$Attributes.">");
                
            foreach( $Root as $Name => $Value )
            {
                $InnerTagName = ($IsIndexedArray) 
                    ? $aTagName
                    : $Name;
                    
                if (is_array($Value))
                {
                    if (!$IsIndexedArray && $IsNumericArray)
                        $this->generateXML($aOnOut, $InnerTagName, $Value, Array("key" => $Name));
                    else
                        $this->generateXML($aOnOut, $InnerTagName, $Value);
                }
                else 
                {
                    $aOnOut("<".$InnerTagName.$Attributes.">");

                    if ($Value === null)
                    {
                        // do nothing
                    }
                    else if (is_numeric($Value))
                    {
                        $aOnOut($Value);
                    }
                    else if (($Value === true) || ($Value == "true"))
                    {
                        $aOnOut('true');
                    }
                    else if (($Value === false) || ($Value == "false"))
                    {
                        $aOnOut('false');
                    }
                    else
                    {
                        echo xmlentities($Value, ENT_COMPAT, "UTF-8");
                    }

                    $aOnOut("</".$InnerTagName.">");
                }
            }

            if (!$IsIndexedArray && !$IsNumericArray)
                $aOnOut("</".$aTagName.">");
        }

        // --------------------------------------------------------------------------------------------

        private function IsNumericArray($aArray)
        {
            if (!is_array($aArray))
                return false;
                
            $Keys = array_keys($aArray);
            
            foreach($Keys as $Key)
			{
			    if (is_numeric($Key))
					return true;
			}
			
			return false;
        }

        // --------------------------------------------------------------------------------------------

        private function IsIndexedArray($aArray)
        {
            if (!is_array($aArray))
                return false;

            $Keys = array_keys($aArray);
			$NumKeys = count($Keys);
			
			if ($NumKeys == 0)
				return true;
			
			$PrevKey = -1;
			foreach($Keys as $Key)
			{
				if (!is_numeric($Key))
					return false;
					
				if ($Key - $PrevKey != 1)
					return false;
					
                $PrevKey = $Key;
			}
			
			return true;
        }
    }

?>