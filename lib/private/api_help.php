<?php

    function api_help($aRequest)
    {
        $Out = Out::getInstance();
        
        switch(strtolower($aRequest["help"]))
        {
        default:
        case "help":
            $Out->pushValue("description", "Help page.");
            
            $Out->pushValue("values", Array(
                "help"     => "Help on help.",
                "format"   => "Help on result format.",
                "location" => "Help on querying locations.",
                "raid"     => "Help on querying raid data.",
                "stats"    => "Help on querying statistics.",
            ));
            break;
            
        case "format":
            $Out->pushValue("description", "Result format. Defaults to json.");
            $Out->pushValue("values", Array(
                "json" => "Return result as JSON.",
                "xml"  => "Return result as XML."
            ));
            break;
            
            
        case "location":
            $Out->pushValue("description", "Query value. Get a list of available locations.");
            $Out->pushValue("parameters", Array(
            ));
            break;
            
        case "raid":
            $Out->pushValue("description", "Query value. Get information about raids.");
            $Out->pushValue("parameters", Array(
                "start"     => "Only return raids starting after this UTC timestamp. Default: 0.",
                "end"       => "Only return raids starting before this UTC timestamp. Default: 0x7FFFFFFF.",
                "limit"     => "Maximum number of raids to return. Passing 0 returns all raids. Default: 10.",
                "offset"    => "Number of raids to skip if a limit is set. Default: 0.",
                "location"  => "Comma separated list of location names. Only returns raids on these locations. Default: empty.",
                "full"      => "Include raids that have all slots set. Default: true.",
                "free"      => "Include raids that do not have all slots set. Default: true.",
                "open"      => "Include raids that are open for registration. Default: true.",
                "closed"    => "Include raids that are closed for registration. Default: false.",
                "canceled"  => "Include raids that have been canceled. Default: false.",
                "attends"   => "Return list of attended players, too. Default: false.",    
            ));
            break;
            
        case "statistic":
            $Out->pushValue("description", "Query value. Get user statistics.");
            $Out->pushValue("parameters", Array(
                "start"     => "Only count raids starting after this UTC timestamp. Default: 0.",
                "end"       => "Only count raids starting before this UTC timestamp. Default: PHP_INT_MAX.",
                "raids"     => "Comma separated list of raid ids to count. Empty counts all raids. Default: empty.",
                "users"     => "Comma separated list of user names to include. Empty returns all users. Default: empty.",
            ));
            break;
        }
    }
    
?>