<?php
    // This file contains a valid configuration for World of Warcraft and is used
    // as a default if values in confg/config.game.php.

    $gClassMode = "single";
    
    $gRoles = Array(
        "tank" => Array( 0, 1, "Tank",   "role_tank" ),
        "heal" => Array( 1, 1, "Healer", "role_heal" ),
        "dmg"  => Array( 2, 4, "Damage", "role_melee" ),
    );
    
    $gClasses = Array(
        "empty"         => Array( 0,  "",            "dmg",  Array("dmg") ),
        "deathknight"   => Array( 1,  "Deathknight", "tank", Array("dmg","tank") ),
        "druid"         => Array( 2,  "Druid",       "heal", Array("dmg","heal","tank") ),
        "hunter"        => Array( 3,  "Hunter",      "dmg",  Array("dmg") ),
        "mage"          => Array( 4,  "Mage",        "dmg",  Array("dmg") ),
        "monk"          => Array( 5,  "Monk",        "heal", Array("dmg","heal","tank") ),
        "paladin"       => Array( 6,  "Paladin",     "heal", Array("dmg","heal","tank") ),
        "priest"        => Array( 7,  "Priest",      "heal", Array("dmg","heal") ),
        "rogue"         => Array( 8,  "Rogue",       "dmg",  Array("dmg") ),
        "shaman"        => Array( 9,  "Shaman",      "dmg",  Array("dmg","heal") ),
        "warlock"       => Array( 10, "Warlock",     "dmg",  Array("dmg") ),
        "warrior"       => Array( 11, "Warrior",     "tank", Array("dmg","tank") )
    );
    
    $gGroupSizes = Array(
        5  => Array(1,1,3),
        10 => Array(2,3,5),
        25 => Array(2,6,17),
        40 => Array(1,1,38)
    );
?>