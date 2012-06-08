<?php

// This file contains information about the game represented by this raidplaner instance.
// By default this is World of Warcraft.
// You will be able to represent other games as long as the classic "damage,healer,tank"
// scheme works with this game.

// Role ident => Localization string
// Role ident is also mapped to an image in images/classes[big|small]/<role ident>[_off].png
// The role count and ident strings are fixed.

$s_Roles = Array(
	"dmg"  => "Damage",
	"heal" => "Healer",
	"tank" => "Tank"
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

$s_Classes = Array( 
	"empty"			=> Array( "", 			 Roles::$damage ),
	"deathknight"	=> Array( "Deathknight", Roles::$offensiveTank ),
	"druid"		 	=> Array( "Druid", 		 Roles::$hybrid ),
	"hunter"		=> Array( "Hunter", 	 Roles::$damage ),
	"mage"			=> Array( "Mage", 		 Roles::$damage ),
	"monk"			=> Array( "Monk", 		 Roles::$hybrid ),
	"paladin"       => Array( "Paladin", 	 Roles::$hybrid ),
	"priest"  	    => Array( "Priest", 	 Roles::$offensiveHeal ),
	"rogue"         => Array( "Rogue", 		 Roles::$damage ),
	"shaman"        => Array( "Schaman", 	 Roles::$offensiveHeal ),
	"warlock"       => Array( "Warlock", 	 Roles::$damage ),
	"warrior"       => Array( "Warrior", 	 Roles::$offensiveTank )
);

// Size => Array(Tanks, Healer, Damage)
// Tanks + Healer + Damage == Size MUST be given
// Sizes > 25 are NOT supported 

$s_GroupSizes = Array(
	5  => Array(1,1,3),
	10 => Array(2,3,5),
	25 => Array(2,6,17)
);

// Check $s_GroupSizes for constraints not matched

while ( list($Count,$RoleSizes) = each($s_GroupSizes) )
{
	assert( $Count == $RoleSizes[0] + $RoleSizes[1] + $RoleSizes[2] );
	assert( $Count <= 25 );
}

reset($s_GroupSizes);
	
?>