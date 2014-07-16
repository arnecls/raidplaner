<?php

    // -------------------------------------------------------------------------
    
    $gApiHelp['help'] = Array(
        'description' => 'Help page.',
        'values'      => Array(
            'help'     => 'Help on help.',
            'format'   => 'Help on result format.',
            'as'       => 'Help on downloading results.',
            'query'    => 'Help on queries.',
            'location' => 'Help on querying locations.',
            'raid'     => 'Help on querying raid data.',
            'stats'    => 'Help on querying statistics.',
        )
    );

    // -------------------------------------------------------------------------
    
    $gApiHelp['query'] = Array(
        'description' => 'Main query selector.',
        'values'      => Array(
            'location' => 'Querying locations.',
            'raid'     => 'Querying raid data.',
            'stats'    => 'Querying statistics.',
        )
    );
    
    // -------------------------------------------------------------------------

    $gApiHelp['format'] = Array(
        'description' => 'Result format. Defaults to json.',
        'values'      => Array(
            'json' => 'Return result as JSON.',
            'xml'  => 'Return result as XML.',
        )
    );
    
    // -------------------------------------------------------------------------

    $gApiHelp['as'] = Array(
        'description' => 'Trigger a download of the result instead of returning the contents.',
        'values'      => 'Pass the filename to generate.'
    );
    
    // -------------------------------------------------------------------------

    function api_help($aRequest)
    {
        global $gApiHelp;        
        $Out = Out::getInstance();
        $Topic = strtolower($aRequest['help']);
        
        if (isset($gApiHelp[$Topic]))
        {
            foreach($gApiHelp[$Topic] as $Name => $Value)
            {
                $Out->pushValue($Name, $Value);
            }
        }
        else
        {
            $Out->pushError('Unknown help topic.');
        }
    }
    
?>