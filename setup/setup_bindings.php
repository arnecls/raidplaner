<?php
    define( "LOCALE_SETUP", true );
    require_once(dirname(__FILE__)."/../lib/private/locale.php");
    require_once(dirname(__FILE__)."/../lib/config/config.php");
    @include_once(dirname(__FILE__)."/../lib/config/config.phpbb3.php");
    @include_once(dirname(__FILE__)."/../lib/config/config.vb3.php");
    @include_once(dirname(__FILE__)."/../lib/config/config.eqdkp.php");
    
    if ( defined("PHPBB3_DATABASE") )
    {
        require_once(dirname(__FILE__)."/../lib/private/connector.class.php");
    
        $Connector = new Connector(SQL_HOST, PHPBB3_DATABASE, PHPBB3_USER, PHPBB3_PASS); 
        $Groups = $Connector->prepare( "SELECT group_id, group_name FROM `".PHPBB3_TABLE_PREFIX."groups` ORDER BY group_name" );
        
        $Groups->execute();        
        $PHPBB3Groups = Array();
        
        while ( $Group = $Groups->fetch( PDO::FETCH_ASSOC ) )
        {
            array_push( $PHPBB3Groups, $Group );
        }
        
        $Groups->closeCursor();     
    }
    
    if ( defined("VB3_DATABASE") )
    {
        require_once(dirname(__FILE__)."/../lib/private/connector.class.php");
    
        $Connector = new Connector(SQL_HOST, VB3_DATABASE, VB3_USER, VB3_PASS); 
        $Groups = $Connector->prepare( "SELECT usergroupid, title FROM `".VB3_TABLE_PREFIX."usergroup` ORDER BY title" );
        
        $Groups->execute();        
        $VB3Groups = Array();
        
        while ( $Group = $Groups->fetch( PDO::FETCH_ASSOC ) )
        {
            array_push( $VB3Groups, $Group );
        }
        
        $Groups->closeCursor(); 
    }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
    <head>
        <title>Raidplaner config</title>
        <meta http-equiv="X-UA-Compatible" content="IE=Edge">
        
        <link rel="stylesheet" type="text/css" href="layout/default.css">
        <script type="text/javascript" src="../lib/script/jquery-1.8.2.min.js"></script>
        <script type="text/javascript" src="script/main.js"></script>
        <script type="text/javascript" src="script/setup_bindings.js.php"></script>
        
        <style>
            .tab_active {
                margin: 0px;
                border-left: 1px solid black;
                border-right: 1px solid black;
                border-top: 1px solid black;
                border-bottom: 1px solid white;
                background-color: white;
                position: relative;
                top: 1px;
            }
            
            .tab_inactive {
                margin: 0px;
                border: 1px solid black;
                background-color: #dddddd;
                position: relative;
                top: 1px;
            }
        </style>
    </head>
    
    <body>
        <div class="appwindow">
            <div style="background-color: black; color: white; padding: 10px">
                Packedpixel<br/>
                <span style="font-size: 24px">Raidplaner setup (3/3)</span>
            </div>
            <div style="padding: 20px">
            
                <div id="bindings">
                    <input type="checkbox" id="allow_phpbb3"<?php echo (defined("PHPBB3_BINDING") && PHPBB3_BINDING) ? " checked=\"checked\"": "" ?>/> <?php echo L("AllowPHPBB3Login"); ?><br/>
                    <input type="checkbox" id="allow_eqdkp"<?php echo (defined("EQDKP_BINDING") && EQDKP_BINDING) ? " checked=\"checked\"": "" ?>/> <?php echo L("AllowEQDKPLogin"); ?><br/>
                    <input type="checkbox" id="allow_vb3"<?php echo (defined("VB3_BINDING") && VB3_BINDING) ? " checked=\"checked\"": "" ?>/> <?php echo L("AllowVBulletinLogin"); ?><br/>
                    <br/>
                    <div style="border-bottom: 1px solid black">
                    <button id="button_phpbb3" class="tab_active" onclick="showConfig('phpbb3')"><?php echo L("PHPBB3Binding"); ?></button>
                    <button id="button_eqdkp" class="tab_inactive" onclick="showConfig('eqdkp')"><?php echo L("EQDKPBinding"); ?></button>
                    <button id="button_vbulletin" class="tab_inactive" onclick="showConfig('vbulletin')"><?php echo L("VBulletinBinding"); ?></button>
                    </div>
                </div>
                
                <div id="phpbb3">
                    <div>
                        <h2><?php echo L("PHPBB3Binding"); ?></h2>
                        <input type="text" id="phpbb3_database" value="<?php echo (defined("PHPBB3_DATABASE")) ? PHPBB3_DATABASE : "phpbb" ?>"/> <?php echo L("PHPBB3Database"); ?><br/>
                        <input type="text" id="phpbb3_user" value="<?php echo (defined("PHPBB3_TABLE")) ? PHPBB3_USER : "root" ?>"/> <?php echo L("UserWithDBPermissions"); ?><br/>
                        <input type="password" id="phpbb3_password"/> <?php echo L("UserPassword"); ?><br/>
                        <input type="password" id="phpbb3_password_check"/> <?php echo L("RepeatPassword"); ?><br/>
                        <br/>
                        <input type="text" id="phpbb3_prefix" value="<?php echo (defined("PHPBB3_TABLE_PREFIX")) ? PHPBB3_TABLE_PREFIX : "phpbb_" ?>"/> <?php echo L("TablePrefix"); ?><br/>
                    </div>
                    
                    <div style="margin-top: 1em">
                        <button onclick="reloadPHPBB3Groups()"><?php echo L("LoadGroups"); ?></button><br/><br/>
                        
                        <?php echo L("AutoMemberLogin"); ?><br/>
                        <select id="phpbb3_member" multiple="multiple" style="width: 400px; height: 5.5em">
                        <?php
                            if ( defined("PHPBB3_DATABASE") )
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
                            if ( defined("PHPBB3_DATABASE") )
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
                
                <div id="eqdkp">
                    <div>
                        <h2><?php echo L("EQDKPBinding"); ?></h2>
                        <input type="text" id="eqdkp_database" value="<?php echo (defined("EQDKP_DATABASE")) ? EQDKP_DATABASE : "eqdkp" ?>"/> <?php echo L("EQDKPDatabase"); ?><br/>
                        <input type="text" id="eqdkp_user" value="<?php echo (defined("EQDKP_TABLE")) ? EQDKP_USER : "root" ?>"/> <?php echo L("UserWithDBPermissions"); ?><br/>
                        <input type="password" id="eqdkp_password"/> <?php echo L("UserPassword"); ?><br/>
                        <input type="password" id="eqdkp_password_check"/> <?php echo L("RepeatPassword"); ?><br/>
                        <br/>
                        <input type="text" id="eqdkp_prefix" value="<?php echo (defined("EQDKP_TABLE_PREFIX")) ? EQDKP_TABLE_PREFIX : "eqdkp_" ?>"/> <?php echo L("TablePrefix"); ?><br/>
                    </div>
                    
                    <br/><br/><button onclick="checkEQDKP()"><?php echo L("VerifySettings"); ?></button>                
                </div>
                
                <div id="vbulletin">
                    <div>
                        <h2><?php echo L("VBulletinBinding"); ?></h2>
                        <input type="text" id="vb3_database" value="<?php echo (defined("VB3_DATABASE")) ? VB3_DATABASE : "vbulletin" ?>"/> <?php echo L("VBulletinDatabase"); ?><br/>
                        <input type="text" id="vb3_user" value="<?php echo (defined("VB3_TABLE")) ? VB3_USER : "root" ?>"/> <?php echo L("UserWithDBPermissions"); ?><br/>
                        <input type="password" id="vb3_password"/> <?php echo L("UserPassword"); ?><br/>
                        <input type="password" id="vb3_password_check"/> <?php echo L("RepeatPassword"); ?><br/>
                        <br/>
                        <input type="text" id="vb3_prefix" value="<?php echo (defined("VB3_TABLE_PREFIX")) ? VB3_TABLE_PREFIX : "vb_" ?>"/> <?php echo L("TablePrefix"); ?><br/>
                    </div>
                    
                    <div style="margin-top: 1em">
                        <button onclick="reloadVB3Groups()"><?php echo L("LoadGroups"); ?></button><br/><br/>
                        
                        <?php echo L("AutoMemberLogin"); ?><br/>
                        <select id="vb3_member" multiple="multiple" style="width: 400px; height: 5.5em">
                        <?php
                            if ( defined("VB3_DATABASE") )
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
                            if ( defined("VB3_DATABASE") )
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
                                        
                <div style="position: fixed; right: 50%; top: 50%; margin-right: -380px; margin-top: 260px">
                    <button onclick="checkForm()"><?php echo L("SaveAndContinue"); ?></button>
                </div>
            </div>
        </div>
    </body>
</html>