<?php
    // Note: You should never ever rely on that data as the only source of
    // user information. These can easily be changed on the clients side and are
    // only meant for caching or display related logic
        
    require_once(dirname(__FILE__)."/../private/users.php");
    if (!defined("UNIFIED_SCRIPT")) header("Content-type: text/javascript");
    
    if ( ValidUser() )
    {
        $CurrentUser = UserProxy::GetInstance();
        
        function echoCharacterIds()
        {
            global $CurrentUser;
            $first = true;
            
            foreach ( $CurrentUser->Characters as $Character )
            {
                if ($first)
                {
                    echo "\"".intval( $Character->CharacterId )."\"";
                    $first = false;
                }
                else
                {
                    echo ", ".intval( $Character->CharacterId );
                }
            }
        }
        
        function echoCharacterNames()
        {
            global $CurrentUser;
            $first = true;
            
            foreach ( $CurrentUser->Characters as $Character )
            {
                if ($first)
                {
                    echo "\"".$Character->Name."\"";
                    $first = false;
                }
                else
                {
                    echo ", \"".$Character->Name."\"";
                }
            }
        }
        
        function echoRole1()
        {
            global $CurrentUser;
            $first = true;
            
            foreach ( $CurrentUser->Characters as $Character )
            {
                if ($first)
                {
                    echo "\"".$Character->Role1."\"";
                    $first = false;
                }
                else
                {
                    echo ", \"".$Character->Role1."\"";
                }
            }
        }
        
        function echoRole2()
        {
            global $CurrentUser;
            $first = true;
            
            foreach ( $CurrentUser->Characters as $Character )
            {
                if ($first)
                {
                    echo "\"".$Character->Role2."\"";
                    $first = false;
                }
                else
                {
                    echo ", \"".$Character->Role2."\"";
                }
            }
        }
?>

var g_User = {
    characterIds    : new Array( <?php echoCharacterIds(); ?> ),
    characterNames  : new Array( <?php echoCharacterNames(); ?> ),
    role1           : new Array( <?php echoRole1(); ?> ),
    role2           : new Array( <?php echoRole2(); ?> ),
    isRaidlead      : <?php echo ValidRaidlead() ? "true" : "false"; ?>,
    isAdmin         : <?php echo ValidAdmin() ? "true" : "false"; ?>,
    id              : <?php echo $CurrentUser->UserId; ?>
};

<?php
    } 
    else 
    {
?>
    
var g_User = null;

<?php } ?>