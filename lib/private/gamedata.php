<?php

    require_once(dirname(__FILE__)."/tools_site.php");

    // Raidplaner defaults
    // See config/config.game.php for details of the different fields
    
    require_once(dirname(__FILE__)."/game.default.php");
    
    // Load the custom config
            
    include_once(dirname(__FILE__)."/../config/config.game.php");
        
    // Check if constraints are matched
    // Data ranges
    
    assert( ($gClassMode == "single") || ($gClassMode == "multi") );
    
    // Max num columns
    
    $ColSum = 0;
    $MaxRoleId = 0;
    foreach ( $gRoles as $Role )
    {
        $ColSum += $Role[1];
        $MaxRoleId = max($MaxRoleId, $Role[0]);
    }
    
    assert( $MaxRoleId = sizeof($gRoles)-1 ); // Number of items in gRoles must equal max id
    assert( $MaxRoleId < 5);                  // Only 5 roles supported
    assert( $ColSum == 6 );                   // 6 columns, no more, no less.
    
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