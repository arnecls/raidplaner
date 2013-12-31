<?php

    // -----------------------------------------------------------------------------------------------
    // This file contains information about the game represented by this raidplaner instance.
    // By default this is World of Warcraft.
    //
    // *** HANDLE WITH CARE IF YOUR RAIDPLANER IS ALREADY IN USE ***
    //
    // If your profiles are displaying messed up data after a change, you should run the repair tool
    // from the setup utility. This will happen when changing the order of $gRoles.
    // -----------------------------------------------------------------------------------------------
    
    // You can define different roles for your raids.
    // This can be tank/heal/dmg or range, melee, support, etc.
    // You can define up to 5 roles.
    //
    // Role ident => Localization string (must be present in all files in lib/private/locale)
    //
    // The order of the roles defined here will be the order presentation in the Raid detail sheet.
    // If you change the order of these items on an already active raidplaner instance (with
    // registered users and/or attends) you will have to change all role fields the database, too.
    
    $gRoles = Array(
        "tank" => "Tank",
        "heal" => "Healer",
        "dmg"  => "Damage"
    );
    
    // -----------------------------------------------------------------------------------------------
    
    // Empty roles slots will be displayed in with a certain image.
    // These images are searched in the current iconset, e.g. "images/icons/wow/roles".
    // The order of the images here have to match the order of $gRoles.
    
    $gRoleImages = Array(
        "slot_role4.png",
        "slot_role2.png",
        "slot_role1.png"
    );
    
    // -----------------------------------------------------------------------------------------------
    
    // The raid view always shows 6 columns.
    // This variable defines how many columns are to be shown for which role.
    // The numbers in this array have to sum up to 6.
    // The order of the numbers here matches the order of $gRoles.
    //
    // ColumnSize = Array(columns for Role1, columns for Role2,...)
    
    $gRoleColumnCount = Array(1,1,4);
    
    // -----------------------------------------------------------------------------------------------
    
    // Your game will either support different group sizes or you will want to set a maximum
    // amount of players for a raid. You can set these values here.
    // You can choose the size when creating a raid.
    //
    // Size => Array(Players for role1, players for role2, ...)
    //
    // The numbers in the array have to sum up to match the size given.
    // The order of the numbers here matches the order of $gRoles.
    
    $gGroupSizes = Array(
        5  => Array(1,1,3),
        10 => Array(2,3,5),
        25 => Array(2,6,17),
        40 => Array(1,1,38)
    );
    
    // -----------------------------------------------------------------------------------------------
    
    // You can choose between different class modes which will change the way classes and roles
    // are being handled by the profile page and the raid attend tooltips.
    // The following values are supported:
    //
    // "wow"  : World of Warcraft style classes and roles.
    //          Each character has one class and two different roles based on that class.
    //
    // "ff14" : Final Fantasy 14 style job system
    //          Each character may have one or more class(es) with one predefined role each.
    
    $gClassMode = "wow";
    
    // -----------------------------------------------------------------------------------------------
    
    // You can define the classes available in oyur game here.
    //
    // Class ident => Array( Localization string, Default role, Allowed roles array )
    //
    // Class ident is also mapped to a png image in "images/icons/<iconset>/classes[big|small]"
    // The "empty" class must always be present and first in list.
    // If you are using the ff14 classmode only the first role in the array will be used as classes
    // have exactly one role with that mode.
    
    $gClasses = Array(
        "empty"         => Array( "",            "dmg",  Array("dmg") ),
        "deathknight"   => Array( "Deathknight", "tank", Array("dmg","tank") ),
        "druid"         => Array( "Druid",       "heal", Array("dmg","heal","tank") ),
        "hunter"        => Array( "Hunter",      "dmg",  Array("dmg") ),
        "mage"          => Array( "Mage",        "dmg",  Array("dmg") ),
        "monk"          => Array( "Monk",        "heal", Array("dmg","heal","tank") ),
        "paladin"       => Array( "Paladin",     "heal", Array("dmg","heal","tank") ),
        "priest"        => Array( "Priest",      "heal", Array("dmg","heal") ),
        "rogue"         => Array( "Rogue",       "dmg",  Array("dmg") ),
        "shaman"        => Array( "Shaman",      "dmg",  Array("dmg","heal") ),
        "warlock"       => Array( "Warlock",     "dmg",  Array("dmg") ),
        "warrior"       => Array( "Warrior",     "tank", Array("dmg","tank") )
    );
?>