<?php
    define( "LOCALE_SETUP", true );

    require_once(dirname(__FILE__)."/../lib/private/locale.php");
    require_once(dirname(__FILE__)."/../lib/config/config.php");
    require_once(dirname(__FILE__)."/../lib/private/userproxy.class.php");

    // Load bindings

    $gBindings = array();

    PluginRegistry::ForEachPlugin( function($PluginInstance) use (&$gBindings)
    {
        array_push($gBindings, $PluginInstance);
    });
?>
<?php readfile("layout/header.html"); ?>

<?php if (isset($_REQUEST["single"])) { ?>
<script type="text/javascript">
    $(document).ready( function() {
        $(".button_back").click( function() { open("index.php"); });
        $(".button_next").click( function() { CheckBindingForm("index.php"); });
    });
</script>
<?php } else { ?>
<script type="text/javascript">
    $(document).ready( function() {
        $(".button_back").click( function() { open("install_password.php"); });
        $(".button_next").click( function() { CheckBindingForm("install_done.php"); });
    });
</script>
<?php } ?>

<div id="bindings">
    <?php

        $FirstBinding = $gBindings[0];
    ?>
    <div class="binding_select">
        <select id="binding_current" onchange="showConfig($(this).children('option:selected').val())">
        <?php
            foreach ($gBindings as $Binding)
            {
                echo "<option value=\"".$Binding->getName()."\"".(($Binding == $FirstBinding) ? " selected" : "").">".L($Binding->getName()."_Binding")."</option>";
            }
        ?>
        </select>
        <input type="checkbox" id="binding_allow" onchange="toggleCurrentBinding(this)" style="margin-left: 8px; width: 14px;" <?php if ($FirstBinding->IsActive()) echo "checked=\"checked\""; ?>/>
        <?php
            foreach ($gBindings as $Binding)
            {
                echo "<input type=\"hidden\" id=\"allow_".$Binding->getName()."\" value=\"".($Binding->IsActive() ? "true" : "false")."\">";
            }
        ?>
    </div>
    <h2 id="binding_name"><?php echo L($FirstBinding->getName()."_Binding"); ?></h2>
</div>

<?php
    $hidden = false;
    foreach ($gBindings as $Binding)
    {
        $LocalePrefix = $Binding->getName()."_";
        $Config = $Binding->getConfig();
        $Disabled = ($Binding->IsActive()) ? "" : " disabled=\"disabled\"";

        if (!$hidden)
        {
            echo "<div id=\"".$Binding->getName()."\" class=\"config\">";
            $hidden = true;
        }
        else
        {
            echo "<div id=\"".$Binding->getName()."\" class=\"config\" style=\"display:none\">";
        }

        echo "<div class=\"left\">";

        echo "<button id=\"".$Binding->getName()."_loadconfig\" onclick=\"LoadSettings('".$Binding->getName()."')\"".$Disabled.">".L("LoadSettings")."</button><br/><br/>";

        echo "<p>".L($LocalePrefix."Database")."<br/>";
        echo "<input type=\"text\" id=\"".$Binding->getName()."_database\" value=\"".$Config->Database."\"".$Disabled."/></p>";

        echo "<p>".L("UserWithDBPermissions")."<br/>";
        echo "<input type=\"text\" id=\"".$Binding->getName()."_user\" value=\"".$Config->User."\"".$Disabled."/></p>";

        echo "<p>".L("UserPassword")."<br/>";
        echo "<input type=\"password\" id=\"".$Binding->getName()."_password\" value=\"".$Config->Password."\"".$Disabled."/></p>";

        echo "<p>".L("RepeatPassword")."<br/>";
        echo "<input type=\"password\" id=\"".$Binding->getName()."_password_check\" value=\"".$Config->Password."\"".$Disabled."/></p>";

        echo "<p>".L("TablePrefix")."<br/>";
        echo "<input type=\"text\" id=\"".$Binding->getName()."_prefix\" value=\"".$Config->Prefix."\"".$Disabled."/></p>";

        if ( $Config->HasCookieConfig )
        {
            echo "<p>".L($Binding->getName()."_CookieEx")."<br/>";
            echo "<input type=\"text\" id=\"".$Binding->getName()."_cookie_ex\" value=\"".$Config->CookieData."\"".$Disabled."/></p>";
        }

        echo "</div>";

        echo "<div class=\"right\">";

        if ( $Config->HasGroupConfig )
        {
            $Groups = $Binding->getGroupsFromConfig();

            echo "<button id=\"".$Binding->getName()."_loaddata\" onclick=\"LoadBindingData('".$Binding->getName()."')\"".$Disabled.">".L("LoadGroups")."</button><br/><br/>";

            echo "<div class=\"groups\" style=\"margin-right: 10px\">";
            echo L("AutoMemberLogin")."<br/>";;
            echo "<select id=\"".$Binding->getName()."_member\" multiple=\"multiple\" style=\"height: 5.5em\"".$Disabled.">";

            if ($Groups != null)
            {
                foreach( $Groups as $Group )
                {
                    echo "<option value=\"".$Group["id"]."\"".((in_array($Group["id"], $Config->Members)) ? " selected=\"selected\"" : "" ).">".$Group["name"]."</option>";
                }
            }

            echo "</select></div>";

            echo "<div class=\"groups\">";
            echo L("AutoLeadLogin")."<br/>";
            echo "<select id=\"".$Binding->getName()."_raidlead\" multiple=\"multiple\" style=\"height: 5.5em\"".$Disabled.">";

            if ($Groups != null)
            {
                foreach( $Groups as $Group )
                {
                    echo "<option value=\"".$Group["id"]."\"".((in_array($Group["id"], $Config->Raidleads)) ? " selected=\"selected\"" : "" ).">".$Group["name"]."</option>";
                }
            }

            echo "</select></div>";

        }
        else
        {
            echo "<button onclick=\"CheckGrouplessBinding('".$Binding->getName()."')\"".$Disabled.">".L("VerifySettings")."</button>";
        }

        echo "<br/><br/>";
        echo "<p><input type=\"checkbox\" id=\"".$Binding->getName()."_autologin\"".(($Config->AutoLoginEnabled) ? "checked=\"checked\"" : "")."".$Disabled."/> ".L("AllowAutoLogin")."<br/><br/>";
        echo L("CookieNote")."</p>";

        if ( $Config->HasForumConfig )
        {
            $Forums = $Binding->getForumsFromConfig();

            echo L("PostToForum")."<br/>";;
            echo "<select id=\"".$Binding->getName()."_postto\"".$Disabled.">";
            echo "<option value=\"0\"".(($Config->PostTo == "") ? " selected=\"selected\"" : "" ).">".L("DisablePosting")."</option>";

            if ($Forums != null)
            {
                foreach( $Forums as $Forum )
                {
                    echo "<option value=\"".$Forum["id"]."\"".(($Config->PostTo == $Forum["id"]) ? " selected=\"selected\"" : "" ).">".$Forum["name"]."</option>";
                }
            }

            echo "</select><br/><br/>";

            $Users = $Binding->getUsersFromConfig();
            $FoundUsers = ($Users != null) && (sizeof($Users) > 0);

            echo L("PostAsUser")."<br/>";
            echo "<select id=\"".$Binding->getName()."_postas\"".$Disabled.">";

            if ($FoundUsers)
            {
                foreach( $Users as $User )
                {
                    echo "<option value=\"".$User["id"]."\"".(($Config->PostAs == $User["id"]) ? " selected=\"selected\"" : "" ).">".$User["name"]."</option>";
                }
            }
            else
            {
                echo "<option value=\"0\" selected=\"selected\">".L("NoUsersFound")."</option>";
            }

            echo "</select>";
        }

        echo "</div>";
        echo "</div>";
    }
?>

</div>
<div class="bottom_navigation">
<?php if (isset($_REQUEST["single"])) { ?>
    <div class="button_back" style="background-image: url(layout/install_white.png)"><?php echo L("Back"); ?></div>
    <div class="button_next" style="background-image: url(layout/update_white.png)"><?php echo L("Continue"); ?></div>
<?php } else { ?>
    <div class="button_back" style="background-image: url(layout/password_white.png)"><?php echo L("Back"); ?></div>
    <div class="button_next" style="background-image: url(layout/install_white.png)"><?php echo L("Continue"); ?></div>
<?php } ?>

<?php readfile("layout/footer.html"); ?>