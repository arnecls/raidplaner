<?php
    date_default_timezone_set('UTC');
    
    // Output headers and set error handler
    
    require_once(dirname(__FILE__)."/private/debug.php");    
    
    if (isset($_REQUEST["as"]))
    {
        header('Content-Disposition: attachment; filename="'.$_REQUEST["as"].'"');
    }
    
    header("Cache-Control: no-cache, max-age=0, s-maxage=0");
    
    $ResultFormat = (isset($_REQUEST["format"]))
        ? strtolower($_REQUEST["format"])
        : "json";
        
    switch ($ResultFormat)
    {
    case "xml":
        header("Content-type: application/xml");
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
        Debug::setHandlersXML();
        break;
        
    default:
    case "json":
        header("Content-type: application/json");
        Debug::setHandlersJSON();
        break;
    }
    
    // Process includes after error handler has been set
    
    require_once(dirname(__FILE__)."/private/api.php");
    
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
        $Authenticated = false;
        $Parameter = call_user_func("api_args_".strtolower($_REQUEST["query"]), $_REQUEST);
        
        // Validate against public or private token
        // If no token is given, try to validate the currently logged in user.
        
        if (isset($_REQUEST["token"]))
        {
            $Authenticated = 
                Api::testPrivateToken($_REQUEST["token"]) || 
                Api::testPublicToken($Parameter, $_REQUEST["token"]);
        }
        
        // Only execute requests if validated
        
        if (!$Authenticated)
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
        
    switch ($ResultFormat)
    {
    case "xml":
        $Out->flushXML("Api");
        break;
        
    default:
    case "json":
        $Out->flushJSON();
        break;
    }
?>
