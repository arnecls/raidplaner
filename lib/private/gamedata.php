<?php

    require_once(dirname(__FILE__)."/tools_site.php");

    // Raidplaner defaults
    // See config/config.game.php for details of the different fields
    
    require_once(dirname(__FILE__)."/game.default.php");
    
    // Load the custom config
            
    include_once_exists(dirname(__FILE__)."/../config/config.game.php");
        
    // Check if constraints are matched
    // Data ranges
    
    assert( sizeof($gRoles) <= 5 );
    assert( ($gClassMode == "wow") || ($gClassMode == "ff14") );
    
    // Max num columns
    
    $ColSum = 0;
    foreach ( $gRoleColumnCount as $Cols )
    {
        $ColSum += $Cols;
    }
    
    assert( $ColSum == 6 ); // 6 columns, no more, no less.
    
    // Groupsize = sum of slots
    
    while ( list($Count,$RoleSizes) = each($gGroupSizes) )
    {
        $SlotSum = 0;
        foreach ( $RoleSizes as $SlotCount )
            $SlotSum += $SlotCount;
    
        assert( $Count == $SlotSum ); // Slots do not match raid size
    }
    
    reset($gGroupSizes);

?>