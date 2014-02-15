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
        $Out->pushError("You must at least provide the parameter `query` and a `token`.");
        $Out->pushError("You can also pass `help` with a topic to see a list of available parameters.");
    }
    else
    {
        $PublicQuery  = false;
        $PrivateQuery = false;
        $Parameter = call_user_func("api_args_".strtolower($_REQUEST["query"]), $_REQUEST);
        
        // Validate against public or private token
        
        if (isset($_REQUEST["token"]))
        {
            $PublicQuery  = Api::testPublicToken($Parameter, $_REQUEST["token"]);
            $PrivateQuery = Api::testPrivateToken($_REQUEST["token"]);
        }
        
        // Only execute requests if validated
        
        if (!$PublicQuery && !$PrivateQuery)
        {
            $Out->pushError("Validation failed.");
        }
        else
        {
            switch( strtolower($_REQUEST["query"]))
            {
            case "location":
                $Out->pushValue("result", api_query_location($Parameter));
                break;
                
            case "user":
                $Out->pushValue("result", api_query_user($Parameter));
                break;
            
            case "raid":
                $Out->pushValue("result", api_query_raid($Parameter));
                break;
                
            case "statistic":
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
