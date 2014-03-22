<?php
    require_once(dirname(__FILE__)."/out.class.php");
    
    class Debug
    {
        private static function backTrace()
        {
            $Out = Out::getInstance();
            $Trace = array_reverse(debug_backtrace());
            
            foreach($Trace as $Frame)
            {
                $File = isset($Frame["file"]) 
                    ? substr($Frame["file"], strrpos($Frame["file"], DIRECTORY_SEPARATOR)+1)
                    : "unknown file";
                    
                $Line = isset($Frame["line"])
                    ? $Frame["line"]
                    : "?";
            
                if (strpos($Frame["function"], "Debug::") === 0)
                {
                    $Out->pushError($File."(".$Line.") : root\n");
                    break;
                }
                
                $Out->pushError($File."(".$Line.") : ".$Frame["function"]."\n");
            }
        }
        
        // ---------------------------------------------------------------
    
        private static function errorHandler($aCode, $aMessage, $aFile, $aLine, $aContext)
        {
            $RootFile = substr($aFile, strrpos($aFile, DIRECTORY_SEPARATOR)+1);
            
            $Out = Out::getInstance();
            $Out->pushError("PHP Error ".$aCode." in file ".$RootFile."(".$aLine."):\n");
            $Out->pushError($aMessage);
            $Out->pushError("\n\nCallstack:\n");
            
            self::backTrace();
            
            return true;
        }
        
        // ---------------------------------------------------------------
        
        private static function exceptionHandler($aException)
        {
            $RootFile = substr($aException->getFile(), strrpos($aException->getFile(), DIRECTORY_SEPARATOR)+1);
            
            $Out = Out::getInstance();
            $Out->pushError("Unhandled Exception in file ".$RootFile."(".$aException->getLine()."):\n");
            $Out->pushError($aException->getMessage());
            $Out->pushError("\n\nCallstack:\n");
            
            self::backTrace();
        }
        
        // ---------------------------------------------------------------
        
        public static function errorHandlerJSON($aCode, $aMessage, $aFile, $aLine, $aContext)
        {
            self::errorHandler($aCode, $aMessage, $aFile, $aLine, $aContext);
            Out::getInstance()->writeJSONandStop();
            
            return false;
        }
        
        // ---------------------------------------------------------------
        
        public static function exceptionHandlerJSON($aException)
        {
            self::exceptionHandler($aException);
            Out::getInstance()->writeJSONandStop();
            
            return false;
        }
        
        // ---------------------------------------------------------------
        
        public static function errorHandlerXML($aCode, $aMessage, $aFile, $aLine, $aContext)
        {
            self::errorHandler($aCode, $aMessage, $aFile, $aLine, $aContext);
            Out::getInstance()->writeXMLandStop();
            
            return false;
        }
        
        // ---------------------------------------------------------------
        
        public static function exceptionHandlerXML($aException)
        {
            self::exceptionHandler($aException);
            Out::getInstance()->writeXMLandStop();
            
            return false;
        }
        
        // ---------------------------------------------------------------
         
        public static function setHandlersJSON()
        {
            set_error_handler("Debug::errorHandlerJSON", E_ALL);
            set_exception_handler("Debug::exceptionHandlerJSON");
        }
        
        // ---------------------------------------------------------------
         
        public static function setHandlersXML()
        {
            set_error_handler("Debug::errorHandlerJSON", E_ALL);
            set_exception_handler("Debug::exceptionHandlerXML");
        }
    }
?>