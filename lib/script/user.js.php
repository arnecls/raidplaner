<?php
    // Note: You should never ever rely on that data as the only source of
    // user information. These can easily be changed on the clients side and are
    // only meant for caching or display related logic
        
    require_once(dirname(__FILE__)."/../private/users.php");
    UserProxy::GetInstance(); // Init user
    
    if (!defined("UNIFIED_SCRIPT")) header("Content-type: text/javascript");
    
    if ( ValidUser() )
    {
        function echoCharacterIds()
        {
            $first = true;
            foreach ( $_SESSION["User"]["CharacterId"] as $CharacterId )
            {
                if ($first)
                {
                    echo "\"".intval( $CharacterId )."\"";
                    $first = false;
                }
                else
                {
                    echo ", ".intval( $CharacterId );
                }
            }
        }
        
        function echoCharacterNames()
        {
            $first = true;
            foreach ( $_SESSION["User"]["CharacterName"] as $CharacterName )
            {
                if ($first)
                {
                    echo "\"".$CharacterName."\"";
                    $first = false;
                }
                else
                {
                    echo ", \"".$CharacterName."\"";
                }
            }
        }
        
        function echoRole1()
        {
            $first = true;
            foreach ( $_SESSION["User"]["Role1"] as $Role1 )
            {
                if ($first)
                {
                    echo "\"".$Role1."\"";
                    $first = false;
                }
                else
                {
                    echo ", \"".$Role1."\"";
                }
            }
        }
        
        function echoRole2()
        {
            $first = true;
            foreach ( $_SESSION["User"]["Role2"] as $Role2 )
            {
                if ($first)
                {
                    echo "\"".$Role2."\"";
                    $first = false;
                }
                else
                {
                    echo ", \"".$Role2."\"";
                }
            }
        }
?>

var g_User = {
    characterIds    : new Array( <?php echoCharacterIds(); ?> ),
    characterNames    : new Array( <?php echoCharacterNames(); ?> ),
    role1            : new Array( <?php echoRole1(); ?> ),
    role2            : new Array( <?php echoRole2(); ?> ),
    isRaidlead        : <?php echo ValidRaidlead() ? "true" : "false"; ?>,
    isAdmin            : <?php echo ValidAdmin() ? "true" : "false"; ?>,
    id                : <?php echo $_SESSION["User"]["UserId"]; ?>
};

<?php
    } 
    else 
    {
?>
    
var g_User = null;

<?php } ?>