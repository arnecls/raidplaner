<?php
    date_default_timezone_set('UTC');
    
    require_once(dirname(__FILE__)."/private/api.php");
    require_once(dirname(__FILE__)."/private/out.class.php");
    
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
                $Parameter = Array(
                    "start"     => getParam("start", 0),
                    "end"       => getParam("end", 0x7FFFFFFF),
                    "limit"     => getParam("limit", 10),
                    "offset"    => getParam("offset", 0),
                    "location"  => getParam("location", ""),
                    "full"      => getParam("full", true),
                    "free"      => getParam("free", true),
                    "open"      => getParam("open", true),
                    "closed"    => getParam("closed", false),
                    "canceled"  => getParam("canceled", false),
                    "attends"   => getParam("attends", false),
                ); 
                $Out->pushValue("result", api_query_raid($Parameter));
                break;
                
            case "statistic":
                $Parameter = Array(
                    "start" => getParam("start", 0),
                    "end"   => getParam("end", PHP_INT_MAX),
                    "raids" => getParam("raids", ""),
                    "users" => getParam("users", ""),
                );
                
                $Out->pushValue("result", api_query_statistic($Parameter));
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
        $Out->writeXML("Api");
        break;
        
    default:
    case "json":
        header("Content-type: application/json");
        $Out->writeJSON();
        break;
    }
?>