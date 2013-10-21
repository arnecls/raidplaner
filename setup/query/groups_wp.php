<?php
    define( "LOCALE_SETUP", true );
    require_once(dirname(__FILE__)."/../../lib/private/connector.class.php");
    require_once(dirname(__FILE__)."/../../lib/private/userproxy.class.php");
    require_once(dirname(__FILE__)."/../../lib/config/config.php");
    require_once(dirname(__FILE__)."/../../lib/private/bindings/wp.php");

    header("Content-type: text/xml");
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
    echo "<grouplist>";
    
    $Out = Out::getInstance();
    $Connector = new Connector(SQL_HOST, $_REQUEST["database"], $_REQUEST["user"], $_REQUEST["password"]); 
    
    if ($Connector != null)
    {
        $Options = $Connector->prepare( "SELECT option_value FROM `".WP_TABLE_PREFIX."options` WHERE option_name = \"wp_user_roles\" LIMIT 1" );
            
        if ( $Options->execute() )
        {
            $Option = $Options->fetch(PDO::FETCH_ASSOC);
            $Roles = null;
            
            WPBinding::readWpObj($Option["option_value"], $Roles, 0);
            
            for ($i=0; $i<sizeof($Roles); $i+=2)
            {
                echo "<group>";
                echo "<id>".strtolower($Roles[$i])."</id>";
                echo "<name>".$Roles[$i]."</name>";
                echo "</group>";
            }
        }
        else
        {
            postErrorMessage( $Groups );
        }
    }
    
    $Out->flushXML("");     
    echo "</grouplist>";
?>