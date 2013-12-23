<?php
    header("Content-type: text/xml");
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
    echo "<database>";
    
    define( "LOCALE_SETUP", true );
    require_once("../../lib/private/connector.class.php");
    require_once("../../lib/config/config.php");

    $Out = Out::getInstance();
    $Connector = Connector::getInstance();
    
    $TestQuery = $Connector->prepare( "SELECT * FROM `".RP_TABLE_PREFIX."User` WHERE UserId=1 LIMIT 1" );
    
    if (!$TestQuery->execute())
    {
        postErrorMessage($TestQuery);
    }
    else
    {    
        $Salt = md5(mcrypt_create_iv(2048, MCRYPT_RAND));
        $HashedPassword = hash("sha256", sha1($_REQUEST["password"]).$Salt);
                
        if ( $TestQuery->rowCount() == 0 )
        {
            $Connector->exec( "INSERT INTO `".RP_TABLE_PREFIX."User` VALUES(1, 'admin', 0, 'none', 'true', 'admin', '".$HashedPassword."', '".$Salt."', '', '', FROM_UNIXTIME(".time()."));" );
        }   
        else
        {
            $Connector->exec( "UPDATE `".RP_TABLE_PREFIX."User` SET `Password`='".$HashedPassword."', `Salt`='".$Salt."' WHERE UserId=1 LIMIT 1;" );        
        }
    }
    
    $TestQuery->closeCursor();
    $Out->flushXML("");
    
    echo "</database>";
?>