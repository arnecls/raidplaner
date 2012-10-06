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

$s_Roles = Array(
    "tank" => "Tank",
    "heal" => "Healer",
    "dmg"  => "Damage"
);

assert( sizeof($s_Roles) <= 5 );


$s_RoleImages = Array(
    "images/roles/slot_role4.png",
    "images/roles/slot_role2.png",
    "images/roles/slot_role1.png"
);

// Predefined role arrays for convenience

class Roles
{
    public static $damage        = Array("dmg");
    public static $heal          = Array("heal");
    public static $tank          = Array("tank");
    public static $offensiveTank = Array("dmg","tank");
    public static $offensiveHeal = Array("dmg","heal");
    public static $healerTank    = Array("heal","tank");
    public static $hybrid        = Array("dmg","heal","tank");
};

// Class ident => Array( Localization string, Allowed roles array )
// Class ident is also mapped to an image in images/classes[big|small]/<class ident>.png
// The "empty" class must always be present and first in list

$s_Classes = Array(
    "empty"         => Array( "",            Roles::$damage ),
    "deathknight"   => Array( "Deathknight", Roles::$offensiveTank ),
    "druid"         => Array( "Druid",       Roles::$hybrid ),
    "hunter"        => Array( "Hunter",      Roles::$damage ),
    "mage"          => Array( "Mage",        Roles::$damage ),
    "monk"          => Array( "Monk",        Roles::$hybrid ),
    "paladin"       => Array( "Paladin",     Roles::$hybrid ),
    "priest"        => Array( "Priest",      Roles::$offensiveHeal ),
    "rogue"         => Array( "Rogue",       Roles::$damage ),
    "shaman"        => Array( "Shaman",      Roles::$offensiveHeal ),
    "warlock"       => Array( "Warlock",     Roles::$damage ),
    "warrior"       => Array( "Warrior",     Roles::$offensiveTank )
);

// Size => Array(Role1, Role2, ...)
// Sum(Roles) == Size MUST be given

$s_GroupSizes = Array(
    5  => Array(1,1,3),
    10 => Array(2,3,5),
    25 => Array(2,6,17),
    40 => Array(1,1,38)
);

// Check $s_GroupSizes for constraints not matched

while ( list($Count,$RoleSizes) = each($s_GroupSizes) )
{
    $slotSum = 0;
    foreach ( $RoleSizes as $count )
        $slotSum += $count;

    assert( $Count == $slotSum );
}

reset($s_GroupSizes);

?>