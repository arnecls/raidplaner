<?php
    define( "LOCALE_SETUP", true );
    require_once(dirname(__FILE__)."/../../lib/private/connector.class.php");
    require_once(dirname(__FILE__)."/../../lib/config/config.php");

    header("Content-type: text/xml");
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
    echo "<grouplist>";
    
    $Out = Out::getInstance();
    
    if ($_REQUEST["database"] == "")
    {
        echo "<error>".L("PHPBB3DatabaseEmpty")."</error>";
    }
    else if ($_REQUEST["user"] == "")
    {
        echo "<error>".L("PHPBB3UserEmpty")."</error>";        
    }
    else if ($_REQUEST["password"] == "")
    {
        echo "<error>".L("PHPBB3PasswordEmpty")."</error>";        
    }
    else
    {    
        $Connector = new Connector(SQL_HOST, $_REQUEST["database"], $_REQUEST["user"], $_REQUEST["password"]); 
        
        if ($Connector != null)
        {
            $Groups = $Connector->prepare( "SELECT group_id, group_name FROM `".$_REQUEST["prefix"]."groups` ORDER BY group_name" );
            
            if ( $Groups->execute() )
            {
                while ( $Group = $Groups->fetch( PDO::FETCH_ASSOC ) )
                {
                    echo "<group>";
                    echo "<id>".$Group["group_id"]."</id>";
                    echo "<name>".$Group["group_name"]."</name>";
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