<?php
    define( "LOCALE_SETUP", true );
    
    require_once(dirname(__FILE__)."/../lib/private/locale.php");
    require_once(dirname(__FILE__)."/../lib/config/config.php");
    require_once(dirname(__FILE__)."/../lib/private/userproxy.class.php");    
    
    // Load bindings
    
    $gBindings = array();
    
    foreach(PluginRegistry::$Classes as $PluginName)
    {
        $Plugin = new ReflectionClass($PluginName);
        $PluginInstance = $Plugin->newInstance();
        array_push($gBindings, $PluginInstance);
    }
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
                echo "<option value=\"".$Binding->BindingName."\"".(($Binding == $FirstBinding) ? " selected" : "").">".L($Binding->BindingName."_Binding")."</option>";
            }
        ?>
        </select>
        <input type="checkbox" id="binding_allow" onchange="toggleCurrentBinding(this)" style="margin-left: 8px; width: 14px;" <?php if ($FirstBinding->IsActive()) echo "checked=\"checked\""; ?>/>
        <?php
            foreach ($gBindings as $Binding)
            {
                echo "<input type=\"hidden\" id=\"allow_".$Binding->BindingName."\" value=\"".($Binding->IsActive() ? "true" : "false")."\">";
            }
        ?>
    </div>
    <h2 id="binding_name"><?php echo L($FirstBinding->BindingName."_Binding"); ?></h2>
</div>

<?php
    $hidden = false;
    foreach ($gBindings as $Binding)
    {
        $LocalePrefix = $Binding->BindingName."_";
        $Config = $Binding->getConfig();
        $Disabled = ($Binding->IsActive()) ? "" : " disabled=\"disabled\"";
        
        if (!$hidden)
        {
            echo "<div id=\"".$Binding->BindingName."\" class=\"config\">";
            $hidden = true;
        }
        else
        {
            echo "<div id=\"".$Binding->BindingName."\" class=\"config\" style=\"display:none\">";
        }
        
        echo "<div class=\"left\">";
        
        echo "<button id=\"".$Binding->BindingName."_loadconfig\" onclick=\"LoadSettings('".$Binding->BindingName."')\"".$Disabled.">".L("LoadSettings")."</button><br/><br/>";
        
        echo "<p>".L($LocalePrefix."Database")."<br/>";
        echo "<input type=\"text\" id=\"".$Binding->BindingName."_database\" value=\"".$Config["database"]."\"".$Disabled."/></p>";
        
        echo "<p>".L("UserWithDBPermissions")."<br/>";
        echo "<input type=\"text\" id=\"".$Binding->BindingName."_user\" value=\"".$Config["user"]."\"".$Disabled."/></p>";
        
        echo "<p>".L("UserPassword")."<br/>";
        echo "<input type=\"password\" id=\"".$Binding->BindingName."_password\" value=\"".$Config["password"]."\"".$Disabled."/></p>";        
        
        echo "<p>".L("RepeatPassword")."<br/>";
        echo "<input type=\"password\" id=\"".$Binding->BindingName."_password_check\" value=\"".$Config["password"]."\"".$Disabled."/></p>";        
        
        echo "<p>".L("TablePrefix")."<br/>";
        echo "<input type=\"text\" id=\"".$Binding->BindingName."_prefix\" value=\"".$Config["prefix"]."\"".$Disabled."/></p>";
        
        if ( $Config["cookie_ex"] )
        {
            echo "<p>".L($Binding->BindingName."_CookieEx")."<br/>";
            echo "<input type=\"text\" id=\"".$Binding->BindingName."_cookie_ex\" value=\"".$Config["cookie"]."\"".$Disabled."/></p>";
        }
        
        echo "</div>";
        
        echo "<div class=\"right\">";
        
        if ( $Config["groups"] )
        {
            $Groups = $Binding->getGroupsFromConfig();
            
            echo "<button id=\"".$Binding->BindingName."_loadgroups\" onclick=\"ReloadGroups('".$Binding->BindingName."')\"".$Disabled.">".L("LoadGroups")."</button><br/><br/>";
            
            echo L("AutoMemberLogin")."<br/>";;
            echo "<select id=\"".$Binding->BindingName."_member\" multiple=\"multiple\" style=\"height: 5.5em\"".$Disabled.">";
            
            if ($Groups != null)
            {
                foreach( $Groups as $Group )
                {
                    echo "<option value=\"".$Group["id"]."\"".((in_array($Group["id"], $Config["members"])) ? " selected=\"selected\"" : "" ).">".$Group["name"]."</option>";
                }
            }
            
            echo "</select><br/><br/>";
            
            echo L("AutoLeadLogin")."<br/>";
            echo "<select id=\"".$Binding->BindingName."_raidlead\" multiple=\"multiple\" style=\"height: 5.5em\"".$Disabled.">";
            
            if ($Groups != null)
            {
                foreach( $Groups as $Group )
                {
                    echo "<option value=\"".$Group["id"]."\"".((in_array($Group["id"], $Config["leads"])) ? " selected=\"selected\"" : "" ).">".$Group["name"]."</option>";
                }
            }
        
            echo "</select><br/><br/>";                    
        }
        else
        {
            echo "<button onclick=\"CheckGrouplessBinding('".$Binding->BindingName."')\"".$Disabled.">".L("VerifySettings")."</button><br/><br/>";
        }
        
        echo "<p><input type=\"checkbox\" id=\"".$Binding->BindingName."_autologin\"".(($Config["autologin"]) ? "checked=\"checked\"" : "")."".$Disabled."/> ".L("AllowAutoLogin")."<br/><br/>";
        echo L("CookieNote")."</p>";
        
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