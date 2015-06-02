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
        $Config = new BindingConfig();
        $Enabled = $_REQUEST[$Binding."_allow"] == "true";

        if ($Enabled)
        {
            $Version = intval($_REQUEST[$Binding."_ver_major"]) * 10000 +
                       intval($_REQUEST[$Binding."_ver_minor"]) * 100 +
                       intval($_REQUEST[$Binding."_ver_patch"]);

            $Config->Database   = $_REQUEST[$Binding."_database"];
            $Config->User       = $_REQUEST[$Binding."_user"];
            $Config->Password   = $_REQUEST[$Binding."_password"];
            $Config->Prefix     = $_REQUEST[$Binding."_prefix"];
            $Config->CookieData = $_REQUEST[$Binding."_cookie"];
            $Config->PostTo     = $_REQUEST[$Binding."_postto"];
            $Config->PostAs     = $_REQUEST[$Binding."_postas"];
            $Config->Members    = $_REQUEST[$Binding."_member"];
            $Config->Privileged = $_REQUEST[$Binding."_privileged"];
            $Config->Raidleads  = $_REQUEST[$Binding."_raidlead"];
            $Config->Admins     = $_REQUEST[$Binding."_admin"];
            $Config->Version    = $Version;

            $Config->ForumPostEnabled = $_REQUEST[$Binding."_postto"] != 0;
            $Config->AutoLoginEnabled = $_REQUEST[$Binding."_autologin"] == "true";
        }

        $PluginInstance->writeConfig($Enabled, $Config);
    });

    echo "</bindings>";

?>