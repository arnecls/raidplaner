<?php
    date_default_timezone_set('UTC');
    
    require_once(dirname(__FILE__)."/private/api.php");
    require_once(dirname(__FILE__)."/private/out.class.php");
    
    // Helper to handle request paramters   
    
    function getParam($aName, $aDefault)
    {
        $Value = (isset($_REQUEST[$aName])) ? $_REQUEST[$aName] : $aDefault;
        switch(strtolower($Value))
        {
        case "true":
            return true;
            
        case "false":
            return false;
            
        default:
            return (is_numeric($Value))
                ? intval($Value)
                : $Value;
        }
    }
    
    // Generate response
    
    $Out = Out::getInstance();
    
    if (isset($_REQUEST["help"]))
    {
        api_help($_REQUEST);
    }
    else if (!isset($_REQUEST["query"]))
    {
        $Out->pushError("You must at least provide the parameter `query` and either a `public` or the `private` token.");
        $Out->pushError("You can also pass `help` with a topic to see a list of available parameters.");
    }
    else
    {
        $Validated = false;
        
        // Validate against public or private token
        
        if (isset($_REQUEST["public"]))
        {
            $Validated = Api::testPublicToken($_REQUEST["public"]);
        }        
        else if (isset($_REQUEST["private"]))
        {
            $Validated = Api::testPrivateToken($_REQUEST["private"]);
        }
        
        // Only execute requests if validated
        
        if (!$Validated)
        {
            $Out->pushError("Validation failed.");
        }
        else
        {
            switch( strtolower($_REQUEST["query"]))
            {
            case "location":
                $Out->pushValue("result", api_query_location());
                break;
            
            case "raid":
                $aParameter = Array(
                    "start"     => getParam("start", 0),
                    "end"       => getParam("end", PHP_INT_MAX),
                    "after"     => getParam("after", 0),
                    "before"    => getParam("before", 0),
                    "limit"     => getParam("limit", 10),
                    "location"  => getParam("location", ""),
                    "full"      => getParam("full", true),
                    "free"      => getParam("free", true),
                    "open"      => getParam("open", true),
                    "closed"    => getParam("closed", false),
                    "canceled"  => getParam("canceled", false),
                    "attends"   => getParam("attends", false),
                ); 
                $Out->pushValue("result", api_query_raid($aParameter));
                break;
                
            case "statistic":
                $aParameter = Array(
                    "start" => getParam("start", 0),
                    "end"   => getParam("end", PHP_INT_MAX),
                    "raids" => getParam("raids", ""),
                    "users" => getParam("users", ""),
                );
                
                $Out->pushValue("result", api_query_statistic($aParameter));
                break;
                
            default:
                $Out->pushError("Unknown query type ".$_REQUEST["query"]);
                break;
            }
        }
    }
    
    // Output response
    
    header("Cache-Control: no-cache, max-age=0, s-maxage=0");
    
    $ResultFormat = (isset($_REQUEST["format"]))
        ? strtolower($_REQUEST["format"])
        : "json";
        
    switch ($ResultFormat)
    {
    case "xml":
        header("Content-type: application/xml");
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
        $Out->writeXML("api");
        break;
        
    default:
    case "json":
        header("Content-type: application/json");
        $Out->writeJSON();
        break;
    }
?>