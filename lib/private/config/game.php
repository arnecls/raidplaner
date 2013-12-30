<?php

// This file contains information about the game represented by this raidplaner instance.
// By default this is World of Warcraft.

// Roles:
//
// Role ident => Localization string (must be present in all files in lib/private/locale)
// Role ident is also mapped to an image in images/classes[big|small]/<role ident>[_off].png
//
// You can define up to 5 roles.
// The order of the roles defined here will be the order presentation in the Raid detail sheet.
// If you change the order of these items on an already active raidplaner instance (with
// registered users and/or attends) you will have to change all role fields the database, too.

$gRoles = Array(
    "tank" => "Tank",
    "heal" => "Healer",
    "dmg"  => "Damage"
);

assert( sizeof($gRoles) <= 5 );

$gRoleImages = Array(
    "slot_role4.png",
    "slot_role2.png",
    "slot_role1.png"
);

// Predefined role arrays for convenience

class Roles
{
    public static $Damage        = Array("dmg");
    public static $Heal          = Array("heal");
    public static $Tank          = Array("tank");
    public static $OffensiveTank = Array("dmg","tank");
    public static $OffensiveHeal = Array("dmg","heal");
    public static $HealerTank    = Array("heal","tank");
    public static $Hybrid        = Array("dmg","heal","tank");
};

// Class ident => Array( Localization string, Default role, Allowed roles array )
// Class ident is also mapped to an image in images/classes[big|small]/<class ident>.png
// The "empty" class must always be present and first in list

$gClasses = Array(
    "empty"         => Array( "",            "dmg",  Roles::$Damage ),
    "deathknight"   => Array( "Deathknight", "tank", Roles::$OffensiveTank ),
    "druid"         => Array( "Druid",       "heal", Roles::$Hybrid ),
    "hunter"        => Array( "Hunter",      "dmg",  Roles::$Damage ),
    "mage"          => Array( "Mage",        "dmg",  Roles::$Damage ),
    "monk"          => Array( "Monk",        "heal", Roles::$Hybrid ),
    "paladin"       => Array( "Paladin",     "heal", Roles::$Hybrid ),
    "priest"        => Array( "Priest",      "heal", Roles::$OffensiveHeal ),
    "rogue"         => Array( "Rogue",       "dmg",  Roles::$Damage ),
    "shaman"        => Array( "Shaman",      "dmg",  Roles::$OffensiveHeal ),
    "warlock"       => Array( "Warlock",     "dmg",  Roles::$Damage ),
    "warrior"       => Array( "Warrior",     "tank", Roles::$OffensiveTank )
);

// ColumnSize = Array(Role1,Role2,...)

$gRoleColumnCount = Array(1,1,4);

// Size => Array(Role1, Role2, ...)
// Sum(Roles) == Size MUST be given

$gGroupSizes = Array(
    5  => Array(1,1,3),
    10 => Array(2,3,5),
    25 => Array(2,6,17),
    40 => Array(1,1,38)
);

// Check for constraints not matched

$ColSum = 0;
foreach ( $gRoleColumnCount as $Cols )
{
    $ColSum += $Cols;
}

assert( $ColSum == 6 ); // 6 columns, no more, no less.

while ( list($Count,$RoleSizes) = each($gGroupSizes) )
{
    $SlotSum = 0;
    foreach ( $RoleSizes as $SlotCount )
        $SlotSum += $SlotCount;

    assert( $Count == $SlotSum ); // Slots do not match raid size
}

reset($gGroupSizes);

?>