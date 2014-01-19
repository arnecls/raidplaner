<?php

    // -----------------------------------------------------------------------------------------------
    // This file contains information about the game represented by this raidplaner instance.
    // By default this is World of Warcraft.
    //
    // *** HANDLE WITH CARE IF YOUR RAIDPLANER IS ALREADY IN USE ***
    // -----------------------------------------------------------------------------------------------
    
    // You can choose between different class modes which will change the way classes and roles
    // are being handled by the profile page and the raid attend tooltips.
    // The following values are supported:
    //
    // "single" : World of Warcraft style classes and roles.
    //            Each character has one class and two different roles based on that class.
    //
    // "multi"  : Final Fantasy 14 style job system
    //            Each character may have one or more class(es) with one predefined role each.
    
    $gClassMode = "single";
    
    // -----------------------------------------------------------------------------------------------
    
    // You can define different roles for your raids based on your needs.
    // There are 5 different roles available and you can use up to 5 different roles simultaneously.
    // To add a new role, simply add a line of the following form to the array below:
    //
    // Identifier => Array( Id, Number of columns, Localization string, role image )
    //
    // - Identifier is used for internal referencing (e.g. from the $gClasses array)
    // - The order of this array defines the display in raid view (the first role is the left most role).
    //   Keep this in mind when modifying $gGroupSizes
    // - The first number is used to store roles in the database. Make sure this number does not change!
    // - The second number defines how many columns should be displayed for this role in raid view.
    //   The sum of these numbers has to be 6.
    // - The localization string can be looked up in one of the files in lib/private/locale and will
    //   be shown every time the name of a role is displayed.
    // - The last string refers to the style to be used for this role. The following styles are available:
    //
    //   role_tank    : resembles a shield (yellow)
    //   role_heal    : resembles a cross (blue/white)
    //   role_melee   : resembles two crossed swords (orange/red)
    //   role_range   : resembles a bow (green)
    //   role_support : resembles a player with aura (violet/pink)
    
    $gRoles = Array(
        "tank" => Array(0, 1, "Tank",   "role_tank"),
        "heal" => Array(1, 1, "Healer", "role_heal"),
        "dmg"  => Array(2, 4, "Damage", "role_melee"),
    );
    
    
    // -----------------------------------------------------------------------------------------------
    
    // You can define the classes available in yuor game here.
    // To add a new role, simply add a line of the following form to the array below:
    //
    // Identifier => Array( Localization string, Default role, Array(role, role, ...) )
    //
    // - Identifier is used for internal referencing and to map the class to an image in 
    //   themes/iconset/<current iconset>/raids<big/small>/<Identifier>.png
    // - The localization string can be looked up in one of the files in lib/private/locale and will
    //   be shown every time the name of a class is displayed.
    // - The default role has to be an identifier from $gRoles
    // - The last array has to contain at least the default role and all other roles this class can
    //   be played as. Again this have to be identifiers from $gRoles.
    
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
    
    // -----------------------------------------------------------------------------------------------
    
    // Your game will either support different group sizes or you will want to set a maximum
    // group size for your raids. You can configure these group sizes here by defining how many players
    // of which role are required to build a group.
    // To add a new role, simply add a line of the following form to the array below:
    //
    // Size => Array(Players for role1, players for role2, ...)
    //
    // - Size is used when choosing a group size and defines the total amount of players
    // - The numbers in the array have to sum up to match the given group size.
    // - The order of the numbers here matche the order of columns in the raid view. Keep in mind that
    //   the meaning of each number changes when changing to order of $gRoles.
    
    $gGroupSizes = Array(
        5  => Array(1,1,3),
        10 => Array(2,3,5),
        25 => Array(2,6,17),
        40 => Array(1,1,38)
    );
?>