<?php
    require_once dirname(__FILE__)."/connector.class.php";
    
    // -------------------------------------------------------------------------
    
    $gApiHelp["location"] = Array(
        "description" => "Query value. Get a list of available characters per user.",
        "parameters"  => Array(
            "users"   => "Comma separated list of user ids to fetch. Default: empty",
            "games"   => "Comma separated list of game ids. Only returns characters for these games. Default: empty",
            "current" => "Set to true to only return the currently logged in user. Default: false",
        )
    );
    
    // -------------------------------------------------------------------------
    
    function api_args_user($aRequest)
    {
        return Array(
            "users"   => getParamFrom($aRequest, "users", ""),
            "games"   => getParamFrom($aRequest, "games", ""),
            "current" => getParamFrom($aRequest, "current", false),
        );
    }
    
    // -------------------------------------------------------------------------
    
    function api_query_user($aParameter)
    {
        $aUsers   = getParamFrom($aParameter, "users", "");
        $aGames   = getParamFrom($aParameter, "games", "");
        $aCurrent = getParamFrom($aParameter, "current", false);
        
        $Parameters = Array();
        $Conditions = Array();
        
        // Filter users
        
        if (!$aCurrent && ($aUsers != ""))
        {
            $Users = explode(",", $aUsers);
            foreach($Users as &$UserId)
                $UserId = intval($UserId);
                
            if (count($Users) == 1)
            {         
                array_push($Conditions, "UserId=?");
                array_push($Parameters, $Users[0]);
            }
            else
            {
                array_push($Conditions, "UserId IN (".implode(",",$Users).")");
            }
        }
        
        if ($aCurrent)
        {
            $Session = Session::get();
            if ($Session === null)
                return Array(); // no user logged in
                
            array_push($Conditions, "UserId=?");
            array_push($Parameters, $Session->getUserId());
        }
        
        // Filter games
        
        if ($aGames != "")
        {
            $Games = explode(",", $aGames);
            $GameOptions = Array();
            
            foreach($Games as $Game)
            {
                array_push($GameOptions, "Game=?");
                array_push($Parameters, $Game);
            }
            
            array_push($Conditions, $GameOptions);
        }
        
        // Build where clause
        
        $WhereString = "";        
        if (count($Conditions) > 0)
        {
            foreach($Conditions as &$Part)
            {
                if (is_array($Part))
                    $Part = "(".implode(" OR ", $Part).")";
            }
            
            $WhereString = "WHERE ".implode(" AND ",$Conditions)." ";
        }
        
        // Run query
        
        $Connector = Connector::getInstance();
        $UserQuery = $Connector->prepare("SELECT `".RP_TABLE_PREFIX."User`.UserId AS _UserId, `".RP_TABLE_PREFIX."Character`.* FROM `".RP_TABLE_PREFIX."User` ".
            "LEFT JOIN `".RP_TABLE_PREFIX."Character` USING(UserId) ".
            $WhereString.
            "ORDER BY UserId,Name,Game");
            
        foreach($Parameters as $Index => $Value)
        {
            //Out::getInstance()->pushValue("query", $Value);
            if (is_numeric($Value))
                $UserQuery->bindValue($Index+1, intval($Value), PDO::PARAM_INT);
            else
                $UserQuery->bindValue($Index+1, strval($Value), PDO::PARAM_STR);
        }
        
        // Resolve result
        
        $Result = Array();
        $LastUserId = 0;
        $User = Array();
        
        $UserQuery->loop(function($UserRow) use (&$LastUserId, &$Result, &$User) 
        {            
            if ($LastUserId != $UserRow["_UserId"])
            {
                if (count($User) > 0)
                {
                    array_push($Result, $User);
                }
                
                $LastUserId = $UserRow["_UserId"];
                $User = Array(
                    "Id"         => $LastUserId,
                    "Characters" => Array()
                );
            }
            
            if ($UserRow["CharacterId"] != null)
            {
                array_push($User["Characters"], Array(
                    "Name"       => $UserRow["Name"],
                    "Game"       => $UserRow["Game"],
                    "IsMainChar" => $UserRow["Mainchar"] == "true",
                    "Classes"    => explode(":",$UserRow["Class"]),
                    "Roles"      => Array($UserRow["Role1"], $UserRow["Role2"])
                ));
            }
        });
        
        if (count($User) > 0)
        {
            array_push($Result, $User);
        }
        
        return $Result;
    }
?>