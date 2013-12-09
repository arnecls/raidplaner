<?php
    define( "LOCALE_SETUP", true );
    
    require_once(dirname(__FILE__)."/../lib/private/locale.php");
    require_once(dirname(__FILE__)."/../lib/config/config.php");
    require_once(dirname(__FILE__)."/../lib/private/userproxy.class.php");    
    
    // Load bindings
    
    $gBindings = [];
    
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
    <div class="tab_bg">
    <?php
        $tabClass = "tab_active";        
        foreach ($gBindings as $Binding)
        {
            echo "<div id=\"button_".$Binding->BindingName."\" class=\"".$tabClass."\" onclick=\"showConfig('".$Binding->BindingName."')\">";
            echo "<input type=\"checkbox\" id=\"allow_".$Binding->BindingName."\"".(($Binding->isActive()) ? " checked=\"checked\"/>": "/>").L($Binding->BindingName."_Binding")."</div>";
            if ($tabClass == "tab_active") $tabClass = "tab_inactive";
        }
    ?>
    </div>
</div>

<?php
    $hidden = false;
    foreach ($gBindings as $Binding)
    {
        $LocalePrefix = $Binding->BindingName."_";
        $Config = $Binding->getConfig();
        
        if (!$hidden)
        {
            echo "<div id=\"".$Binding->BindingName."\" class=\"config\">";
            $hidden = true;
        }
        else
        {
            echo "<div id=\"".$Binding->BindingName."\" class=\"config\" style=\"display:none\">";
        }
        
        echo "<div>";
        echo "<h2>".L($LocalePrefix."Binding")."</h2>";
        
        echo "<input type=\"text\" id=\"".$Binding->BindingName."_database\" value=\"".$Config["database"]."\"/> ".L($LocalePrefix."Database")."<br/>";
        echo "<input type=\"text\" id=\"".$Binding->BindingName."_user\" value=\"".$Config["user"]."\"/> ".L("UserWithDBPermissions")."<br/>";
        echo "<input type=\"password\" id=\"".$Binding->BindingName."_password\" value=\"".$Config["password"]."\"/> ".L("UserPassword")."<br/>";        
        echo "<input type=\"password\" id=\"".$Binding->BindingName."_password_check\" value=\"".$Config["password"]."\"/> ".L("RepeatPassword")."<br/>";        
        echo "<input type=\"text\" id=\"".$Binding->BindingName."_prefix\" value=\"".$Config["prefix"]."\"/> ".L("TablePrefix")."<br/>";
        echo "</div>";
        
        if ( $Config["groups"] )
        {
            $Groups = $Binding->getGroupsFromConfig();
            
            echo "<div style=\"margin-top: 1em\">";
            echo "<button onclick=\"ReloadGroups('".$Binding->BindingName."')\">".L("LoadGroups")."</button><br/><br/>";
            
            echo L("AutoMemberLogin")."<br/>";;
            echo "<select id=\"".$Binding->BindingName."_member\" multiple=\"multiple\" style=\"width: 400px; height: 5.5em\">";
            
            if ($Groups != null)
            {
                foreach( $Groups as $Group )
                {
                    echo "<option value=\"".$Group["id"]."\"".((in_array($Group["id"], $Config["members"])) ? " selected=\"selected\"" : "" ).">".$Group["name"]."</option>";
                }
            }
            
            echo "</select><br/><br/>";
            
            echo L("AutoLeadLogin")."<br/>";
            echo "<select id=\"".$Binding->BindingName."_raidlead\" multiple=\"multiple\" style=\"width: 400px; height: 5.5em\">";
            
            if ($Groups != null)
            {
                foreach( $Groups as $Group )
                {
                    echo "<option value=\"".$Group["id"]."\"".((in_array($Group["id"], $Config["leads"])) ? " selected=\"selected\"" : "" ).">".$Group["name"]."</option>";
                }
            }
        
            echo "</select></div>";                    
        }
        else
        {
            echo "<div style=\"margin-top: 1em\">";
            echo "<button onclick=\"CheckGrouplessBinding('".$Binding->BindingName."')\">".L("VerifySettings")."</button><br/><br/>";
            echo "</div>";
        }
        
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