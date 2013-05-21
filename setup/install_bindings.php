<?php
    define( "LOCALE_SETUP", true );
    require_once(dirname(__FILE__)."/../lib/private/locale.php");
    require_once(dirname(__FILE__)."/../lib/config/config.php");
    @include_once(dirname(__FILE__)."/../lib/config/config.phpbb3.php");
    @include_once(dirname(__FILE__)."/../lib/config/config.vb3.php");
    @include_once(dirname(__FILE__)."/../lib/config/config.eqdkp.php");
    @include_once(dirname(__FILE__)."/../lib/config/config.mybb.php");
    @include_once(dirname(__FILE__)."/../lib/config/config.smf.php");
    @include_once(dirname(__FILE__)."/../lib/config/config.vanilla.php");
    @include_once(dirname(__FILE__)."/../lib/config/config.joomla3.php");
    @include_once(dirname(__FILE__)."/../lib/config/config.drupal.php");
    
    if ( defined("PHPBB3_BINDING") && PHPBB3_BINDING )
    {
        require_once(dirname(__FILE__)."/../lib/private/connector.class.php");
        $PHPBB3Groups = Array();
        
        try
        {
            $Connector = new Connector(SQL_HOST, PHPBB3_DATABASE, PHPBB3_USER, PHPBB3_PASS, true); 
            $Groups = $Connector->prepare( "SELECT group_id, group_name FROM `".PHPBB3_TABLE_PREFIX."groups` ORDER BY group_name" );
            
            $Groups->execute();        
            
            while ( $Group = $Groups->fetch( PDO::FETCH_ASSOC ) )
            {
                array_push( $PHPBB3Groups, $Group );
            }
            
            $Groups->closeCursor(); 
        }
        catch(PDOException $Exception)
        {
        }
    }
    
    if ( defined("VB3_BINDING") && VB3_BINDING )
    {
        require_once(dirname(__FILE__)."/../lib/private/connector.class.php");
        $VB3Groups = Array();
        
        try
        {
            $Connector = new Connector(SQL_HOST, VB3_DATABASE, VB3_USER, VB3_PASS, true); 
            $Groups = $Connector->prepare( "SELECT usergroupid, title FROM `".VB3_TABLE_PREFIX."usergroup` ORDER BY title" );
            
            $Groups->execute();        
            
            while ( $Group = $Groups->fetch( PDO::FETCH_ASSOC ) )
            {
                array_push( $VB3Groups, $Group );
            }
            
            $Groups->closeCursor();
        }
        catch(PDOException $Exception)
        {
        }
    }
    
    if ( defined("MYBB_BINDING") && MYBB_BINDING )
    {
        require_once(dirname(__FILE__)."/../lib/private/connector.class.php");
        $MyBBGroups = Array();
        
        try
        {
            $Connector = new Connector(SQL_HOST, MYBB_DATABASE, MYBB_USER, MYBB_PASS, true); 
            $Groups = $Connector->prepare( "SELECT gid, title FROM `".MYBB_TABLE_PREFIX."usergroups` ORDER BY title" );
            
            $Groups->execute();        
            
            while ( $Group = $Groups->fetch( PDO::FETCH_ASSOC ) )
            {
                array_push( $MyBBGroups, $Group );
            }
            
            $Groups->closeCursor(); 
        }
        catch(PDOException $Exception)
        {
        }
    }
    
    if ( defined("SMF_BINDING") && SMF_BINDING )
    {
        require_once(dirname(__FILE__)."/../lib/private/connector.class.php");
        $SMFGroups = Array();
        
        try
        {
            $Connector = new Connector(SQL_HOST, SMF_DATABASE, SMF_USER, SMF_PASS, true); 
            $Groups = $Connector->prepare( "SELECT id_group, group_name FROM `".SMF_TABLE_PREFIX."membergroups` ORDER BY group_name" );
            
            $Groups->execute();        
            
            array_push( $SMFGroups, Array("id_group" => 0, "group_name" => "Board default") );
            
            while ( $Group = $Groups->fetch( PDO::FETCH_ASSOC ) )
            {
                array_push( $SMFGroups, $Group );
            }
            
            $Groups->closeCursor(); 
        }
        catch(PDOException $Exception)
        {
        }
    }
    
    if ( defined("VANILLA_BINDING") && VANILLA_BINDING )
    {
        require_once(dirname(__FILE__)."/../lib/private/connector.class.php");
        $VanillaGroups = Array();
        
        try
        {
            $Connector = new Connector(SQL_HOST, VANILLA_DATABASE, VANILLA_USER, VANILLA_PASS, true); 
            $Groups = $Connector->prepare( "SELECT RoleID, Name FROM `".VANILLA_TABLE_PREFIX."Role` ORDER BY Name" );
            
            $Groups->execute();        
            
            while ( $Group = $Groups->fetch( PDO::FETCH_ASSOC ) )
            {
                array_push( $VanillaGroups, $Group );
            }
            
            $Groups->closeCursor(); 
        }
        catch(PDOException $Exception)
        {
        }
    }
    
    if ( defined("JML3_BINDING") && JML3_BINDING )
    {
        require_once(dirname(__FILE__)."/../lib/private/connector.class.php");
        $JoomlaGroups = Array();
        
        try
        {
            $Connector = new Connector(SQL_HOST, JML3_DATABASE, JML3_USER, JML3_PASS, true); 
            $Groups = $Connector->prepare( "SELECT id, title FROM `".JML3_TABLE_PREFIX."usergroups` ORDER BY title" );
            
            $Groups->execute();        
            
            while ( $Group = $Groups->fetch( PDO::FETCH_ASSOC ) )
            {
                array_push( $JoomlaGroups, $Group );
            }
            
            $Groups->closeCursor();
         
        }
        catch(PDOException $Exception)
        {
        }
    }
    
    if ( defined("DRUPAL_BINDING") && DRUPAL_BINDING )
    {
        require_once(dirname(__FILE__)."/../lib/private/connector.class.php");
        $DrupalGroups = Array();
        
        try
        {
            $Connector = new Connector(SQL_HOST, DRUPAL_DATABASE, DRUPAL_USER, DRUPAL_PASS, true); 
            $Groups = $Connector->prepare( "SELECT rid, name FROM `".DRUPAL_TABLE_PREFIX."role` ORDER BY name" );
            
            $Groups->execute();        
            
            while ( $Group = $Groups->fetch( PDO::FETCH_ASSOC ) )
            {
                array_push( $DrupalGroups, $Group );
            }
            
            $Groups->closeCursor(); 
        }
        catch(PDOException $Exception)
        {
        }
    }
?>
<?php include("layout/header.html"); ?>

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
    <div id="button_phpbb3" class="tab_active" onclick="showConfig('phpbb3')"><input type="checkbox" id="allow_phpbb3"<?php echo (defined("PHPBB3_BINDING") && PHPBB3_BINDING) ? " checked=\"checked\"": "" ?>/> <?php echo L("PHPBB3Binding"); ?></div>
    <div id="button_eqdkp" class="tab_inactive" onclick="showConfig('eqdkp')"><input type="checkbox" id="allow_eqdkp"<?php echo (defined("EQDKP_BINDING") && EQDKP_BINDING) ? " checked=\"checked\"": "" ?>/> <?php echo L("EQDKPBinding"); ?></div>
    <div id="button_vbulletin" class="tab_inactive" onclick="showConfig('vbulletin')"><input type="checkbox" id="allow_vb3"<?php echo (defined("VB3_BINDING") && VB3_BINDING) ? " checked=\"checked\"": "" ?>/> <?php echo L("VBulletinBinding"); ?></div>
    <div id="button_mybb" class="tab_inactive" onclick="showConfig('mybb')"><input type="checkbox" id="allow_mybb"<?php echo (defined("MYBB_BINDING") && MYBB_BINDING) ? " checked=\"checked\"": "" ?>/> <?php echo L("MyBBBinding"); ?></div>
    <div id="button_smf" class="tab_inactive" onclick="showConfig('smf')"><input type="checkbox" id="allow_smf"<?php echo (defined("SMF_BINDING") && SMF_BINDING) ? " checked=\"checked\"": "" ?>/> <?php echo L("SMFBinding"); ?></div>
    <div id="button_vanilla" class="tab_inactive" onclick="showConfig('vanilla')"><input type="checkbox" id="allow_vanilla"<?php echo (defined("VANILLA_BINDING") && VANILLA_BINDING) ? " checked=\"checked\"": "" ?>/> <?php echo L("VanillaBinding"); ?></div>
    <div id="button_joomla" class="tab_inactive" onclick="showConfig('joomla')"><input type="checkbox" id="allow_joomla"<?php echo (defined("JML3_BINDING") && JML3_BINDING) ? " checked=\"checked\"": "" ?>/> <?php echo L("JoomlaBinding"); ?></div>
    <div id="button_drupal" class="tab_inactive" onclick="showConfig('drupal')"><input type="checkbox" id="allow_drupal"<?php echo (defined("DRUPAL_BINDING") && DRUPAL_BINDING) ? " checked=\"checked\"": "" ?>/> <?php echo L("DrupalBinding"); ?></div>
    </div>
</div>

<div id="phpbb3" class="config">
    <div>
        <h2><?php echo L("PHPBB3Binding"); ?></h2>
        <input type="text" id="phpbb3_database" value="<?php echo (defined("PHPBB3_DATABASE")) ? PHPBB3_DATABASE : "phpbb" ?>"/> <?php echo L("PHPBB3Database"); ?><br/>
        <input type="text" id="phpbb3_user" value="<?php echo (defined("PHPBB3_TABLE")) ? PHPBB3_USER : "root" ?>"/> <?php echo L("UserWithDBPermissions"); ?><br/>
        <input type="password" id="phpbb3_password" value="<?php echo (defined("PHPBB3_PASS")) ? PHPBB3_PASS : "" ?>"/> <?php echo L("UserPassword"); ?><br/>
        <input type="password" id="phpbb3_password_check" value="<?php echo (defined("PHPBB3_PASS")) ? PHPBB3_PASS : "" ?>"/> <?php echo L("RepeatPassword"); ?><br/>
        <input type="text" id="phpbb3_prefix" value="<?php echo (defined("PHPBB3_TABLE_PREFIX")) ? PHPBB3_TABLE_PREFIX : "phpbb_" ?>"/> <?php echo L("TablePrefix"); ?><br/>
    </div>
    
    <div style="margin-top: 1em">
        <button onclick="ReloadPHPBB3Groups()"><?php echo L("LoadGroups"); ?></button><br/><br/>
        
        <?php echo L("AutoMemberLogin"); ?><br/>
        <select id="phpbb3_member" multiple="multiple" style="width: 400px; height: 5.5em">
        <?php
            if ( defined("PHPBB3_BINDING") && PHPBB3_BINDING )
            {
                $GroupIds = array();
                
                if ( defined("PHPBB3_MEMBER_GROUPS") )
                    $GroupIds = explode( ",", PHPBB3_MEMBER_GROUPS );
                
                foreach( $PHPBB3Groups as $Group )
                {
                    echo "<option value=\"".$Group["group_id"]."\"".((in_array($Group["group_id"], $GroupIds)) ? " selected=\"selected\"" : "" ).">".$Group["group_name"]."</option>";
                }
            }
        ?>
        </select>
        <br/><br/>
        <?php echo L("AutoLeadLogin"); ?><br/>
        <select id="phpbb3_raidlead" multiple="multiple" style="width: 400px; height: 5.5em">
        <?php
            if ( defined("PHPBB3_BINDING") && PHPBB3_BINDING )
            {
                $GroupIds = array();
                
                if ( defined("PHPBB3_RAIDLEAD_GROUPS") )
                    $GroupIds = explode( ",", PHPBB3_RAIDLEAD_GROUPS );
                
                foreach( $PHPBB3Groups as $Group )
                {
                    echo "<option value=\"".$Group["group_id"]."\"".((in_array($Group["group_id"], $GroupIds)) ? " selected=\"selected\"" : "" ).">".$Group["group_name"]."</option>";
                }
            }
        ?>
        </select>
    </div>                    
</div>

<div id="eqdkp" style="display: none" class="config">
    <div>
        <h2><?php echo L("EQDKPBinding"); ?></h2>
        <input type="text" id="eqdkp_database" value="<?php echo (defined("EQDKP_DATABASE")) ? EQDKP_DATABASE : "eqdkp" ?>"/> <?php echo L("EQDKPDatabase"); ?><br/>
        <input type="text" id="eqdkp_user" value="<?php echo (defined("EQDKP_TABLE")) ? EQDKP_USER : "root" ?>"/> <?php echo L("UserWithDBPermissions"); ?><br/>
        <input type="password" id="eqdkp_password" value="<?php echo (defined("EQDKP_PASS")) ? EQDKP_PASS : "" ?>"/> <?php echo L("UserPassword"); ?><br/>
        <input type="password" id="eqdkp_password_check" value="<?php echo (defined("EQDKP_PASS")) ? EQDKP_PASS : "" ?>"/> <?php echo L("RepeatPassword"); ?><br/>
        <input type="text" id="eqdkp_prefix" value="<?php echo (defined("EQDKP_TABLE_PREFIX")) ? EQDKP_TABLE_PREFIX : "eqdkp_" ?>"/> <?php echo L("TablePrefix"); ?><br/>
    </div>
    
    <br/><br/><button onclick="CheckEQDKP()"><?php echo L("VerifySettings"); ?></button>                
</div>

<div id="vbulletin" style="display: none" class="config">
    <div>
        <h2><?php echo L("VBulletinBinding"); ?></h2>
        <input type="text" id="vb3_database" value="<?php echo (defined("VB3_DATABASE")) ? VB3_DATABASE : "vbulletin" ?>"/> <?php echo L("VBulletinDatabase"); ?><br/>
        <input type="text" id="vb3_user" value="<?php echo (defined("VB3_TABLE")) ? VB3_USER : "root" ?>"/> <?php echo L("UserWithDBPermissions"); ?><br/>
        <input type="password" id="vb3_password" value="<?php echo (defined("VB3_PASS")) ? VB3_PASS : "" ?>"/> <?php echo L("UserPassword"); ?><br/>
        <input type="password" id="vb3_password_check" value="<?php echo (defined("VB3_PASS")) ? VB3_PASS : "" ?>"/> <?php echo L("RepeatPassword"); ?><br/>
        <input type="text" id="vb3_prefix" value="<?php echo (defined("VB3_TABLE_PREFIX")) ? VB3_TABLE_PREFIX : "vb_" ?>"/> <?php echo L("TablePrefix"); ?><br/>
    </div>
    
    <div style="margin-top: 1em">
        <button onclick="ReloadVB3Groups()"><?php echo L("LoadGroups"); ?></button><br/><br/>
        
        <?php echo L("AutoMemberLogin"); ?><br/>
        <select id="vb3_member" multiple="multiple" style="width: 400px; height: 5.5em">
        <?php
            if ( defined("VB3_BINDING") && VB3_BINDING )
            {
                $GroupIds = array();
                
                if ( defined("VB3_MEMBER_GROUPS") )
                    $GroupIds = explode( ",", VB3_MEMBER_GROUPS );
                
                foreach( $VB3Groups as $Group )
                {
                    echo "<option value=\"".$Group["usergroupid"]."\"".((in_array($Group["usergroupid"], $GroupIds)) ? " selected=\"selected\"" : "" ).">".$Group["title"]."</option>";
                }
            }
        ?>
        </select>
        <br/><br/>
        <?php echo L("AutoLeadLogin"); ?><br/>
        <select id="vb3_raidlead" multiple="multiple" style="width: 400px; height: 5.5em">
        <?php
            if ( defined("VB3_BINDING") && VB3_BINDING )
            {
                $GroupIds = array();
                
                if ( defined("VB3_RAIDLEAD_GROUPS") )
                    $GroupIds = explode( ",", VB3_RAIDLEAD_GROUPS );
                
                foreach( $VB3Groups as $Group )
                {
                    echo "<option value=\"".$Group["usergroupid"]."\"".((in_array($Group["usergroupid"], $GroupIds)) ? " selected=\"selected\"" : "" ).">".$Group["title"]."</option>";
                }
            }
        ?>
        </select>
    </div>                    
</div>

<div id="mybb" style="display: none" class="config">
    <div>
        <h2><?php echo L("MyBBBinding"); ?></h2>
        <input type="text" id="mybb_database" value="<?php echo (defined("MYBB_DATABASE")) ? MYBB_DATABASE : "mybb" ?>"/> <?php echo L("MyBBDatabase"); ?><br/>
        <input type="text" id="mybb_user" value="<?php echo (defined("MYBB_TABLE")) ? MYBB_USER : "root" ?>"/> <?php echo L("UserWithDBPermissions"); ?><br/>
        <input type="password" id="mybb_password" value="<?php echo (defined("MYBB_PASS")) ? MYBB_PASS : "" ?>"/> <?php echo L("UserPassword"); ?><br/>
        <input type="password" id="mybb_password_check" value="<?php echo (defined("MYBB_PASS")) ? MYBB_PASS : "" ?>"/> <?php echo L("RepeatPassword"); ?><br/>
        <input type="text" id="mybb_prefix" value="<?php echo (defined("MYBB_TABLE_PREFIX")) ? MYBB_TABLE_PREFIX : "mybb_" ?>"/> <?php echo L("TablePrefix"); ?><br/>
    </div>
    
    <div style="margin-top: 1em">
        <button onclick="ReloadMyBBGroups()"><?php echo L("LoadGroups"); ?></button><br/><br/>
        
        <?php echo L("AutoMemberLogin"); ?><br/>
        <select id="mybb_member" multiple="multiple" style="width: 400px; height: 5.5em">
        <?php
            if ( defined("MYBB_BINDING") && MYBB_BINDING )
            {
                $GroupIds = array();
                
                if ( defined("MYBB_MEMBER_GROUPS") )
                    $GroupIds = explode( ",", MYBB_MEMBER_GROUPS );
                
                foreach( $MyBBGroups as $Group )
                {
                    echo "<option value=\"".$Group["gid"]."\"".((in_array($Group["gid"], $GroupIds)) ? " selected=\"selected\"" : "" ).">".$Group["title"]."</option>";
                }
            }
        ?>
        </select>
        <br/><br/>
        <?php echo L("AutoLeadLogin"); ?><br/>
        <select id="mybb_raidlead" multiple="multiple" style="width: 400px; height: 5.5em">
        <?php
            if ( defined("MYBB_BINDING") && MYBB_BINDING )
            {
                $GroupIds = array();
                
                if ( defined("MYBB_RAIDLEAD_GROUPS") )
                    $GroupIds = explode( ",", MYBB_RAIDLEAD_GROUPS );
                
                foreach( $MyBBGroups as $Group )
                {
                    echo "<option value=\"".$Group["gid"]."\"".((in_array($Group["gid"], $GroupIds)) ? " selected=\"selected\"" : "" ).">".$Group["title"]."</option>";
                }
            }
        ?>
        </select>
    </div>                    
</div>

<div id="smf" style="display: none" class="config">
    <div>
        <h2><?php echo L("SMFBinding"); ?></h2>
        <input type="text" id="smf_database" value="<?php echo (defined("SMF_DATABASE")) ? SMF_DATABASE : "smf" ?>"/> <?php echo L("SMFDatabase"); ?><br/>
        <input type="text" id="smf_user" value="<?php echo (defined("SMF_TABLE")) ? SMF_USER : "root" ?>"/> <?php echo L("UserWithDBPermissions"); ?><br/>
        <input type="password" id="smf_password" value="<?php echo (defined("SMF_PASS")) ? SMF_PASS : "" ?>"/> <?php echo L("UserPassword"); ?><br/>
        <input type="password" id="smf_password_check" value="<?php echo (defined("SMF_PASS")) ? SMF_PASS : "" ?>"/> <?php echo L("RepeatPassword"); ?><br/>
        <input type="text" id="smf_prefix" value="<?php echo (defined("SMF_TABLE_PREFIX")) ? SMF_TABLE_PREFIX : "smf_" ?>"/> <?php echo L("TablePrefix"); ?><br/>
    </div>
    
    <div style="margin-top: 1em">
        <button onclick="ReloadSMFGroups()"><?php echo L("LoadGroups"); ?></button><br/><br/>
        
        <?php echo L("AutoMemberLogin"); ?><br/>
        <select id="smf_member" multiple="multiple" style="width: 400px; height: 5.5em">
        <?php
            if ( defined("SMF_BINDING") && SMF_BINDING )
            {
                $GroupIds = array();
                
                if ( defined("SMF_MEMBER_GROUPS") )
                    $GroupIds = explode( ",", SMF_MEMBER_GROUPS );
                
                foreach( $SMFGroups as $Group )
                {
                    echo "<option value=\"".$Group["id_group"]."\"".((in_array($Group["id_group"], $GroupIds)) ? " selected=\"selected\"" : "" ).">".$Group["group_name"]."</option>";
                }
            }
        ?>
        </select>
        <br/><br/>
        <?php echo L("AutoLeadLogin"); ?><br/>
        <select id="smf_raidlead" multiple="multiple" style="width: 400px; height: 5.5em">
        <?php
            if ( defined("SMF_BINDING") && SMF_BINDING )
            {
                $GroupIds = array();
                
                if ( defined("SMF_RAIDLEAD_GROUPS") )
                    $GroupIds = explode( ",", SMF_RAIDLEAD_GROUPS );
                
                foreach( $SMFGroups as $Group )
                {
                    echo "<option value=\"".$Group["id_group"]."\"".((in_array($Group["id_group"], $GroupIds)) ? " selected=\"selected\"" : "" ).">".$Group["group_name"]."</option>";
                }
            }
        ?>
        </select>
    </div>                    
</div>

<div id="vanilla" style="display: none" class="config">
    <div>
        <h2><?php echo L("VanillaBinding"); ?></h2>
        <input type="text" id="vanilla_database" value="<?php echo (defined("VANILLA_DATABASE")) ? VANILLA_DATABASE : "vanilla" ?>"/> <?php echo L("VanillaDatabase"); ?><br/>
        <input type="text" id="vanilla_user" value="<?php echo (defined("VANILLA_TABLE")) ? VANILLA_USER : "root" ?>"/> <?php echo L("UserWithDBPermissions"); ?><br/>
        <input type="password" id="vanilla_password" value="<?php echo (defined("VANILLA_PASS")) ? VANILLA_PASS : "" ?>"/> <?php echo L("UserPassword"); ?><br/>
        <input type="password" id="vanilla_password_check" value="<?php echo (defined("VANILLA_PASS")) ? VANILLA_PASS : "" ?>"/> <?php echo L("RepeatPassword"); ?><br/>
        <input type="text" id="vanilla_prefix" value="<?php echo (defined("VANILLA_TABLE_PREFIX")) ? VANILLA_TABLE_PREFIX : "GDN_" ?>"/> <?php echo L("TablePrefix"); ?><br/>
    </div>
    
    <div style="margin-top: 1em">
        <button onclick="ReloadVanillaGroups()"><?php echo L("LoadGroups"); ?></button><br/><br/>
        
        <?php echo L("AutoMemberLogin"); ?><br/>
        <select id="vanilla_member" multiple="multiple" style="width: 400px; height: 5.5em">
        <?php
            if ( defined("VANILLA_BINDING") && VANILLA_BINDING )
            {
                $GroupIds = array();
                
                if ( defined("VANILLA_MEMBER_GROUPS") )
                    $GroupIds = explode( ",", VANILLA_MEMBER_GROUPS );
                
                foreach( $VanillaGroups as $Group )
                {
                    echo "<option value=\"".$Group["RoleID"]."\"".((in_array($Group["RoleID"], $GroupIds)) ? " selected=\"selected\"" : "" ).">".$Group["Name"]."</option>";
                }
            }
        ?>
        </select>
        <br/><br/>
        <?php echo L("AutoLeadLogin"); ?><br/>
        <select id="vanilla_raidlead" multiple="multiple" style="width: 400px; height: 5.5em">
        <?php
            if ( defined("VANILLA_BINDING") && VANILLA_BINDING )
            {
                $GroupIds = array();
                
                if ( defined("VANILLA_RAIDLEAD_GROUPS") )
                    $GroupIds = explode( ",", VANILLA_RAIDLEAD_GROUPS );
                
                foreach( $VanillaGroups as $Group )
                {
                    echo "<option value=\"".$Group["RoleID"]."\"".((in_array($Group["RoleID"], $GroupIds)) ? " selected=\"selected\"" : "" ).">".$Group["Name"]."</option>";
                }
            }
        ?>
        </select>
    </div>                    
</div>

<div id="joomla" style="display: none" class="config">
    <div>
        <h2><?php echo L("JoomlaBinding"); ?></h2>
        <input type="text" id="joomla_database" value="<?php echo (defined("JML3_DATABASE")) ? JML3_DATABASE : "joomla" ?>"/> <?php echo L("JoomlaDatabase"); ?><br/>
        <input type="text" id="joomla_user" value="<?php echo (defined("JML3_TABLE")) ? JML3_USER : "root" ?>"/> <?php echo L("UserWithDBPermissions"); ?><br/>
        <input type="password" id="joomla_password" value="<?php echo (defined("JML3_PASS")) ? JML3_PASS : "" ?>"/> <?php echo L("UserPassword"); ?><br/>
        <input type="password" id="joomla_password_check" value="<?php echo (defined("JML3_PASS")) ? JML3_PASS : "" ?>"/> <?php echo L("RepeatPassword"); ?><br/>
        <input type="text" id="joomla_prefix" value="<?php echo (defined("JML3_TABLE_PREFIX")) ? JML3_TABLE_PREFIX : "JML_" ?>"/> <?php echo L("TablePrefix"); ?><br/>
    </div>
    
    <div style="margin-top: 1em">
        <button onclick="ReloadJoomlaGroups()"><?php echo L("LoadGroups"); ?></button><br/><br/>
        
        <?php echo L("AutoMemberLogin"); ?><br/>
        <select id="joomla_member" multiple="multiple" style="width: 400px; height: 5.5em">
        <?php
            if ( defined("JML3_BINDING") && JML3_BINDING )
            {
                $GroupIds = array();
                
                if ( defined("JML3_MEMBER_GROUPS") )
                    $GroupIds = explode( ",", JML3_MEMBER_GROUPS );
                
                foreach( $JoomlaGroups as $Group )
                {
                    echo "<option value=\"".$Group["id"]."\"".((in_array($Group["id"], $GroupIds)) ? " selected=\"selected\"" : "" ).">".$Group["title"]."</option>";
                }
            }
        ?>
        </select>
        <br/><br/>
        <?php echo L("AutoLeadLogin"); ?><br/>
        <select id="joomla_raidlead" multiple="multiple" style="width: 400px; height: 5.5em">
        <?php
            if ( defined("JML3_BINDING") && JML3_BINDING )
            {
                $GroupIds = array();
                
                if ( defined("JML3_RAIDLEAD_GROUPS") )
                    $GroupIds = explode( ",", JML3_RAIDLEAD_GROUPS );
                
                foreach( $JoomlaGroups as $Group )
                {
                    echo "<option value=\"".$Group["id"]."\"".((in_array($Group["id"], $GroupIds)) ? " selected=\"selected\"" : "" ).">".$Group["title"]."</option>";
                }
            }
        ?>
        </select>
    </div>                    
</div>

<div id="drupal" style="display: none" class="config">
    <div>
        <h2><?php echo L("DrupalBinding"); ?></h2>
        <input type="text" id="drupal_database" value="<?php echo (defined("DRUPAL_DATABASE")) ? DRUPAL_DATABASE : "drupal" ?>"/> <?php echo L("DrupalDatabase"); ?><br/>
        <input type="text" id="drupal_user" value="<?php echo (defined("DRUPAL_TABLE")) ? DRUPAL_USER : "root" ?>"/> <?php echo L("UserWithDBPermissions"); ?><br/>
        <input type="password" id="drupal_password" value="<?php echo (defined("DRUPAL_PASS")) ? DRUPAL_PASS : "" ?>"/> <?php echo L("UserPassword"); ?><br/>
        <input type="password" id="drupal_password_check" value="<?php echo (defined("DRUPAL_PASS")) ? DRUPAL_PASS : "" ?>"/> <?php echo L("RepeatPassword"); ?><br/>
        <input type="text" id="drupal_prefix" value="<?php echo (defined("DRUPAL_TABLE_PREFIX")) ? DRUPAL_TABLE_PREFIX : "" ?>"/> <?php echo L("TablePrefix"); ?><br/>
    </div>
    
    <div style="margin-top: 1em">
        <button onclick="ReloadDrupalGroups()"><?php echo L("LoadGroups"); ?></button><br/><br/>
        
        <?php echo L("AutoMemberLogin"); ?><br/>
        <select id="drupal_member" multiple="multiple" style="width: 400px; height: 5.5em">
        <?php
            if ( defined("DRUPAL_BINDING") && DRUPAL_BINDING )
            {
                $GroupIds = array();
                
                if ( defined("DRUPAL_MEMBER_GROUPS") )
                    $GroupIds = explode( ",", DRUPAL_MEMBER_GROUPS );
                
                foreach( $DrupalGroups as $Group )
                {
                    echo "<option value=\"".$Group["rid"]."\"".((in_array($Group["rid"], $GroupIds)) ? " selected=\"selected\"" : "" ).">".$Group["name"]."</option>";
                }
            }
        ?>
        </select>
        <br/><br/>
        <?php echo L("AutoLeadLogin"); ?><br/>
        <select id="drupal_raidlead" multiple="multiple" style="width: 400px; height: 5.5em">
        <?php
            if ( defined("DRUPAL_BINDING") && DRUPAL_BINDING )
            {
                $GroupIds = array();
                
                if ( defined("DRUPAL_RAIDLEAD_GROUPS") )
                    $GroupIds = explode( ",", DRUPAL_RAIDLEAD_GROUPS );
                
                foreach( $DrupalGroups as $Group )
                {
                    echo "<option value=\"".$Group["rid"]."\"".((in_array($Group["rid"], $GroupIds)) ? " selected=\"selected\"" : "" ).">".$Group["name"]."</option>";
                }
            }
        ?>
        </select>
    </div>                    
</div>

</div>
<div class="bottom_navigation">
<?php if (isset($_REQUEST["single"])) { ?>
    <div class="button_back" style="background-image: url(layout/install_white.png)"><?php echo L("Back"); ?></div>
    <div class="button_next" style="background-image: url(layout/update_white.png)"><?php echo L("Continue"); ?></div>
<?php } else { ?>
    <div class="button_back" style="background-image: url(layout/password_white.png)"><?php echo L("Back"); ?></div>
    <div class="button_next" style="background-image: url(layout/install_white.png)"><?php echo L("Continue"); ?></div>
<?php } ?>

<?php include("layout/footer.html"); ?>