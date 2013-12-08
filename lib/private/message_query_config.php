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
    
    $Config = [];    
    $Config["GroupSizes"] = [];
    $Config["RoleNames"] = [];
    $Config["RoleIds"] = [];
    $Config["RoleIdents"] = [];
    $Config["RoleImages"] = $gRoleImages;
    $Config["RoleColumnCount"] = $gRoleColumnCount;
    $Config["ClassIdx"] = [];
    $Config["Classes"] = [];
    
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
        
        $ClassText = (isset($gLocale[$ClassConfig[0]]))
            ? htmlentities($gLocale[$ClassConfig[0]], ENT_COMPAT | ENT_HTML401, 'UTF-8')
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