<?php
    header("Content-type: text/xml");
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
    echo "<database>";
    
    define( "LOCALE_SETUP", true );
    require_once("../../lib/private/connector.class.php");
    require_once("../../lib/config/config.php");

    $connector = Connector::GetInstance();
    
    $testSt = $connector->prepare( "SELECT * FROM `".RP_TABLE_PREFIX."User` WHERE UserId=1 LIMIT 1" );
    $testSt->execute();
    
    $salt = md5(mcrypt_create_iv(2048, MCRYPT_RAND));
    $hashedPassword = hash("sha256", sha1($_REQUEST["password"]).$salt);
            
    if ( $testSt->rowCount() == 0 )
    {
        $connector->exec( "INSERT INTO `".RP_TABLE_PREFIX."User` VALUES(1, 'admin', 0, 'none', 'true', 'admin', '".$hashedPassword."', '".$salt."', '', '', FROM_UNIXTIME(".time()."));" );
    }   
    else
    {
        $connector->exec( "UPDATE `".RP_TABLE_PREFIX."User` SET `Password`='".$hashedPassword."', `Salt`='".$salt."' WHERE UserId=1 LIMIT 1;" );        
    }
    
    $testSt->closeCursor();
    
    echo "</database>";
?>