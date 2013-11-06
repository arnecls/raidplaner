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
    
    if ($_REQUEST["database"] == "")
    {
        echo "<error>".L("WpDatabaseEmpty")."</error>";
    }
    else if ($_REQUEST["user"] == "")
    {
        echo "<error>".L("WpUserEmpty")."</error>";        
    }
    else if ($_REQUEST["password"] == "")
    {
        echo "<error>".L("WpPasswordEmpty")."</error>";        
    }
    else
    {    
        $Connector = new Connector(SQL_HOST, $_REQUEST["database"], $_REQUEST["user"], $_REQUEST["password"]); 
        
        if ($Connector != null)
        {
            $Options = $Connector->prepare( "SELECT option_value FROM `".$_REQUEST["prefix"]."options` WHERE option_name = \"wp_user_roles\" LIMIT 1" );
                
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
    }
    
    $Out->flushXML("");     
    echo "</grouplist>";
?>