<?php
    // This file contains a valid configuration for World of Warcraft and is used
    // as a default if values in confg/config.game.php.

    $gClassMode = "single";
    
    $gRoles = Array(
        "tank" => Array(0, 1, "Tank",   "role_tank"),
        "heal" => Array(1, 1, "Healer", "role_heal"),
        "dmg"  => Array(2, 4, "Damage", "role_melee"),
    );
    
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
    
    $gGroupSizes = Array(
        5  => Array(1,1,3),
        10 => Array(2,3,5),
        25 => Array(2,6,17),
        40 => Array(1,1,38)
    );
?>