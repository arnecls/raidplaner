<?php

function msgQueryConfig( $aRequest )
{
    global $gSite;
    global $gRoles;
    global $gClasses;
    global $gGroupSizes;
    global $gRoleImages;
    global $gRoleColumnCount;
    global $gLocale;
    
    $Out = Out::getInstance();
    loadSiteSettings();
    
    $Config = array();    
    $Config["GroupSizes"] = array();
    $Config["RoleNames"] = array();
    $Config["RoleIds"] = array();
    $Config["RoleIdents"] = array();
    $Config["RoleImages"] = $gRoleImages;
    $Config["RoleColumnCount"] = $gRoleColumnCount;
    $Config["ClassIdx"] = array();
    $Config["Classes"] = array();
    
    // Groups
    
    for ($i=0; list($Count,$RoleSizes) = each($gGroupSizes); ++$i)
    {
        array_push($Config["GroupSizes"], $Count);
    }
    
    reset($gGroupSizes);
    
    // Roles
    
    for ( $i=0; list($RoleIdent,$RoleName) = each($gRoles); ++$i )
    {
        $Config["RoleNames"][$RoleIdent] = $RoleName;
        $Config["RoleIds"][$RoleIdent] = $i;
        $Config["RoleIdents"][$i] = $RoleIdent;
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