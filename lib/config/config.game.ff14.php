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
    
    $gClassMode = "multi";
    
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
    // Identifier => Array( Id, Localization string, Default role, Array(role, role, ...) )
    //
    // - Identifier is used for internal referencing and to map the class to an image in 
    //   themes/iconset/<current iconset>/raids<big/small>/<Identifier>.png
    // - Id is used to store classes in the database.  Make sure this number does not change!
    // - The localization string can be looked up in one of the files in lib/private/locale and will
    //   be shown every time the name of a class is displayed.
    // - The default role has to be an identifier from $gRoles
    // - The last array has to contain at least the default role and all other roles this class can
    //   be played as. Again this have to be identifiers from $gRoles.
    
    $gClasses = Array(
        "empty"       => Array( 0,  "",            "dmg",  Array("dmg") ),
        "archer"      => Array( 1,  "Archer",      "dmg",  Array("dmg") ),
        "bard"        => Array( 2,  "Bard",        "dmg",  Array("dmg") ),
        "blackmage"   => Array( 3,  "Blackmage",   "dmg",  Array("dmg") ),
        "thaumaturge" => Array( 4,  "Thaumaturge", "dmg",  Array("dmg") ),
        "conjurer"    => Array( 5,  "Conjurer",    "heal", Array("heal") ),
        "whitemage"   => Array( 6,  "Whitemage",   "heal", Array("heal") ),
        "dragoon"     => Array( 7,  "Dragoon",     "dmg",  Array("dmg") ),
        "lancer"      => Array( 8,  "Lancer",      "dmg",  Array("dmg") ),
        "paladin"     => Array( 9,  "Paladin",     "tank", Array("tank") ),
        "gladiator"   => Array( 10, "Gladiator",   "tank", Array("tank") ),
        "warrior"     => Array( 11, "Warrior",     "tank", Array("tank") ),
        "marauder"    => Array( 12, "Marauder",    "tank", Array("tank") ),
        "monk"        => Array( 13, "Monk",        "dmg",  Array("dmg") ),
        "pugilist"    => Array( 14, "Pugilist",    "dmg",  Array("dmg") ),
        "arcanist"    => Array( 15, "Arcanist",    "dmg",  Array("dmg") ),
        "scholar"     => Array( 16, "Scholar",     "heal", Array("heal") ),
        "summoner"    => Array( 17, "Summoner",    "dmg",  Array("dmg") )
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
        4  => Array(1,1,2),
        8  => Array(2,2,4),
        24 => Array(6,6,12)
    );
?>