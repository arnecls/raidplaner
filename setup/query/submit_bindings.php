<?php
    header("Content-type: text/xml");
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
    echo "<bindings>";

    define( "LOCALE_SETUP", true );
    require_once("../../lib/private/connector.class.php");
    require_once("../../lib/private/userproxy.class.php");

    PluginRegistry::ForEachBinding( function($PluginInstance)
    {
        $Binding = $PluginInstance->getName();

        $Version = intval($_REQUEST[$Binding."_ver_major"]) * 10000 +
                   intval($_REQUEST[$Binding."_ver_minor"]) * 100 +
                   intval($_REQUEST[$Binding."_ver_patch"]);

        $Config = new BindingConfig();
        $Config->$Database   = $_REQUEST[$Binding."_database"];
        $Config->$User       = $_REQUEST[$Binding."_user"];
        $Config->$Password   = $_REQUEST[$Binding."_password"];
        $Config->$Prefix     = $_REQUEST[$Binding."_prefix"];
        $Config->$CookieData = $_REQUEST[$Binding."_cookie"];
        $Config->$Version    = $Version;
        $Config->$Members    = $_REQUEST[$Binding."_member"];
        $Config->$Privileged = $_REQUEST[$Binding."_privileged"];
        $Config->$Raidleads  = $_REQUEST[$Binding."_raidlead"];
        $Config->$Admins     = $_REQUEST[$Binding."_admin"];
        $Config->$PostTo     = $_REQUEST[$Binding."_postto"];
        $Config->$PostAs     = $_REQUEST[$Binding."_postas"];
        $Config->$AutoLoginEnabled = $_REQUEST[$Binding."_autologin"] == "true";
        $Config->$ForumPostEnabled = $_REQUEST[$Binding."_postto"] != 0;

        $PluginInstance->writeConfig($_REQUEST[$Binding."_allow"] == "true", $Config);
    });

    echo "</bindings>";

?>