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
                $Roles = unserialize($Option["option_value"]);
                
                while (list($Role,$Options) = each($Roles))
                {
                    echo "<group>";
                    echo "<id>".strtolower($Role)."</id>";
                    echo "<name>".$Role."</name>";
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