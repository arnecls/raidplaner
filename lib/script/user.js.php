<?php
    // Note: You should never ever rely on that data as the only source of
    // user information. These can easily be changed on the clients side and are
    // only meant for caching or display related logic
        
    require_once(dirname(__FILE__)."/../private/userproxy.class.php");
    if (!defined("UNIFIED_SCRIPT")) header("Content-type: text/javascript");
    
    if ( validUser() )
    {
        $CurrentUser = UserProxy::getInstance();
        
        function echoCharacterIds()
        {
            global $CurrentUser;
            $First = true;
            
            foreach ( $CurrentUser->Characters as $Character )
            {
                if ($First)
                {
                    echo intval( $Character->CharacterId );
                    $First = false;
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
            $First = true;
            
            foreach ( $CurrentUser->Characters as $Character )
            {
                if ($First)
                {
                    echo "\"".$Character->Name."\"";
                    $First = false;
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
            $First = true;
            
            foreach ( $CurrentUser->Characters as $Character )
            {
                if ($First)
                {
                    echo $Character->Role1;
                    $First = false;
                }
                else
                {
                    echo ", ".$Character->Role1;
                }
            }
        }
        
        function echoRole2()
        {
            global $CurrentUser;
            $First = true;
            
            foreach ( $CurrentUser->Characters as $Character )
            {
                if ($First)
                {
                    echo $Character->Role2;
                    $First = false;
                }
                else
                {
                    echo ", ".$Character->Role2;
                }
            }
        }
?>

var gUser = {
    characterIds    : new Array( <?php echoCharacterIds(); ?> ),
    characterNames  : new Array( <?php echoCharacterNames(); ?> ),
    role1           : new Array( <?php echoRole1(); ?> ),
    role2           : new Array( <?php echoRole2(); ?> ),
    isRaidlead      : <?php echo validRaidlead() ? "true" : "false"; ?>,
    isAdmin         : <?php echo validAdmin() ? "true" : "false"; ?>,
    id              : <?php echo $CurrentUser->UserId; ?>,
    name            : "<?php echo $CurrentUser->UserName; ?>"
};

<?php
    } 
    else 
    {
?>
    
var gUser = null;

<?php } ?>