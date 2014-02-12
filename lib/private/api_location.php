<?php
    require_once dirname(__FILE__)."/connector.class.php";
    
    // -------------------------------------------------------------------------
    
    $gApiHelp["location"] = Array(
        "description" => "Query value. Get a list of available locations.",
        "parameters"  => Array(
        )
    );
    
    // -------------------------------------------------------------------------
    
    function api_args_location($aRequest)
    {
        return Array();
    }
    
    // -------------------------------------------------------------------------
    
    function api_query_location()
    {
        $Connector = Connector::getInstance();
        $LocationQuery = $Connector->prepare("SELECT * FROM `".RP_TABLE_PREFIX."Location`");
        
        $Result = Array();
        $LocationQuery->loop(function($LocationRow) use (&$Result) {
            array_push($Result, Array(
                "Id"     => $LocationRow["LocationId"],
                "Name"   => $LocationRow["Name"],
                "GameId" => $LocationRow["Game"],
                "Image"  => $LocationRow["Image"],
            ));
        });
        
        return $Result;
    }
?>