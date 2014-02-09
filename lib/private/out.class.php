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

        public function flushJSON()
        {

            $this->WriteJSON();
            $this->Data = Array();
        }

        // --------------------------------------------------------------------------------------------

        public function writeJSONandStop()
        {
            $this->writeJSON();
            die();

        }

        // --------------------------------------------------------------------------------------------

        private function IsIndexed($aArray)
        {
            if (!is_array($aArray))
                return false;

            $Keys = array_keys($aArray);
            return (sizeof($Keys) == 0) || (is_numeric($Keys[0]));
        }

        // --------------------------------------------------------------------------------------------

        public function writeJSON($aArray = null)
        {
            $Root = ($aArray == null) 
                ? $this->Data 
                : $aArray;

            $IsIndexedArray = $this->IsIndexed($Root);

            $i = 0;
            echo ($IsIndexedArray) ? '[' : '{';

            foreach( $Root as  $Name => $Value )
            {
                if ($i > 0) echo ',';

                if (!$IsIndexedArray) echo '"'.$Name.'":';

                if ($Value === null)
                {
                    echo "null";
                }
                else if (is_array($Value))
                {
                    if (empty($Value))
                        echo "[]";
                    else
                        $this->writeJSON($Value);
                }
                else if (is_numeric($Value))
                {
                    echo $Value;
                }
                else if (($Value === true) || ($Value == "true"))
                {
                    echo 'true';
                }
                else if (($Value === false) || ($Value == "false"))
                {
                    echo 'false';
                }
                else
                {
                    $sanitized = str_replace("\n", "</br>", $Value);
                    $sanitized = str_replace("\\", "\\\\", $sanitized);
                    echo '"'.$sanitized.'"';
                }

                ++$i;
            }

            echo ($IsIndexedArray) ? ']' : '}';
        }

        // --------------------------------------------------------------------------------------------

        public function flushXML($aTagName)
        {

            $this->WriteXML($aTagName);
            $this->Data = Array();
        }

        // --------------------------------------------------------------------------------------------

        public function writeXMLandStop($aTagName)
        {
            $this->WriteXML($aTagName);
            die();

        }

        // --------------------------------------------------------------------------------------------

        public function writeXML($aTagName, $aArray = null)
        {
            $Root = ($aArray == null) 
                ? $this->Data 
                : $aArray;
                
            $IsIndexedArray = $this->IsIndexed($Root);
            
            if (!$IsIndexedArray)
                echo "<".$aTagName.">";
                
            foreach( $Root as $Name => $Value )
            {
                $InnerTagName = ($IsIndexedArray) 
                    ? $aTagName
                    : $Name;
                    
                if (is_array($Value))
                {
                    $this->writeXML($InnerTagName, $Value);
                }
                else 
                {
                    echo "<".$InnerTagName.">";

                    if ($Value === null)
                    {
                        // do nothing
                    }
                    else if (is_numeric($Value))
                    {
                        echo $Value;
                    }
                    else if (($Value === true) || ($Value == "true"))
                    {
                        echo 'true';
                    }
                    else if (($Value === false) || ($Value == "false"))
                    {
                        echo 'false';
                    }
                    else
                    {
                        echo xmlentities($Value, ENT_COMPAT, "UTF-8");
                    }

                    echo "</".$InnerTagName.">";
                }
            }

            if (!$IsIndexedArray)
                echo "</".$aTagName.">";
        }

        // --------------------------------------------------------------------------------------------

        public function clear()
        {

            $this->Data = Array();
        }

        // --------------------------------------------------------------------------------------------

        public function pushValue($aKey, $aValue)
        {
            if (isset($this->Data[$aKey]))
            {
                if ($this->IsIndexed($this->Data[$aKey]))
                {

                    // current entry is already indexed
                    // append the new value

                    if ($this->IsIndexed($aValue))
                        $this->Data[$aKey] = array_merge($this->Data[$aKey], $aValue);
                    else
                        array_push($this->Data[$aKey], $aValue);
                }
                else
                {
                    // current entry is not indexed
                    // create a new array from old and new value (in that order)

                    if ($this->IsIndexed($aValue))
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

        public function pushError($aMessage)
        {
            if (!isset($this->Data["error"]))
                $this->pushValue("error", Array($aMessage));
            else
                $this->pushValue("error", $aMessage);

        }
    }

?>