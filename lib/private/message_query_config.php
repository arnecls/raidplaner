<?php

function msgQueryConfig( $aRequest )
{
    global $gSite;
    global $gClassMode;
    global $gRoles;
    global $gClasses;
    global $gGroupSizes;
    global $gLocale;

    $Out = Out::getInstance();
    loadSiteSettings();

    $Config = array();

    $Config["RoleIdx"] = array();
    $Config["Roles"] = array();
    $Config["ClassIdx"] = array();
    $Config["Classes"] = array();
    $Config["GroupSizes"] = array();
    $Config["ClassMode"] = $gClassMode;    

    // Groups

    for ($i=0; list($Count,$RoleSizes) = each($gGroupSizes); ++$i)
    {
        array_push($Config["GroupSizes"], $Count);
    }

    reset($gGroupSizes);

    // Roles
    
    reset($gRoles);

    while ( list($RoleIdent,$RoleConfig) = each($gRoles) )
    {
        $Config["RoleIdx"][$RoleIdent] = $RoleConfig[0];
        
        $Flags = (PHP_VERSION_ID >= 50400) ? ENT_COMPAT | ENT_XHTML : ENT_COMPAT;
        $RoleText = (isset($gLocale[$RoleConfig[2]]))
            ? htmlentities($gLocale[$RoleConfig[2]], $Flags, 'UTF-8')
            : "LOCA_MISSING_".$RoleConfig[2];
            
        array_push( $Config["Roles"], array(
            "ident"   => $RoleIdent,
            "columns" => $RoleConfig[1],
            "text"    => $RoleText,
            "style"   => $RoleConfig[3]
        ));
    }

    reset($gRoles);

    // Classes

    for ( $i=0; list($ClassIdent,$ClassConfig) = each($gClasses); ++$i )
    {
        $Config["ClassIdx"][$ClassIdent] = $i;
        
        $Flags = (PHP_VERSION_ID >= 50400) ? ENT_COMPAT | ENT_XHTML : ENT_COMPAT;
        $ClassText = (isset($gLocale[$ClassConfig[0]]))
            ? htmlentities($gLocale[$ClassConfig[0]], $Flags, 'UTF-8')
            : "";

        array_push( $Config["Classes"], array(
            "ident"        => $ClassIdent,
            "text"         => $ClassText,
            "defaultRole"  => $ClassConfig[1],
            "roles"        => $ClassConfig[2]
        ));
    }

    reset($gClasses);

    // Push

    $Out->pushValue("site", $gSite);
    $Out->pushValue("config", $Config);
}

?>