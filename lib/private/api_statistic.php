<?php
    require_once dirname(__FILE__)."/connector.class.php";
    
    $gApiHelp["statistic"] = Array(
        "description" => "Query value. Get user statistics.",
        "parameters" => Array(
            "start"     => "Only count raids starting after this UTC timestamp. Default: 0.",
            "end"       => "Only count raids starting before this UTC timestamp. Default: PHP_INT_MAX.",
            "raids"     => "Comma separated list of raid ids to count. Empty counts all raids. Default: empty.",
            "users"     => "Comma separated list of user names to include. Empty returns all users. Default: empty.",
        )
    );
    
    function api_query_statistic($aParameter)
    {
        return null;
    }
?>