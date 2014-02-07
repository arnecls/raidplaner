<?php
    header("Content-type: text/xml");
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
    echo "<database>";

    define( "LOCALE_SETUP", true );
    require_once("../../lib/private/connector.class.php");
    require_once("../../lib/config/config.php");

    $Out = Out::getInstance();
    $Connector = Connector::getInstance();

    $TestQuery = $Connector->prepare( "SELECT Login FROM `".RP_TABLE_PREFIX."User` WHERE UserId=1 LIMIT 1" );

    try
    {
        $TestQuery->fetchFirst(true);

        $Salt = md5(openssl_random_pseudo_bytes(2048));
        $HashedPassword = hash("sha256", sha1($_REQUEST["password"]).$Salt);

        if ( $TestQuery->getAffectedRows() == 0 )
        {
            $NewAdmin = $Connector->prepare( "INSERT INTO `".RP_TABLE_PREFIX."User` ".
                "VALUES(1, 'admin', 0, 'none', 'true', :Name, :Password, :Salt, '', FROM_UNIXTIME(:Now));");

            $NewAdmin->BindValue(":Name", $_REQUEST["name"], PDO::PARAM_STR);
            $NewAdmin->BindValue(":Password", $HashedPassword, PDO::PARAM_STR);
            $NewAdmin->BindValue(":Salt", $Salt, PDO::PARAM_STR);
            $NewAdmin->BindValue(":Now", time(), PDO::PARAM_STR);

            $NewAdmin->execute(true);
        }
        else
        {
            $UpdateAdmin = $Connector->prepare( "UPDATE `".RP_TABLE_PREFIX."User` SET `Login`= :Name, `Password`= :Password, `Salt`= :Salt WHERE UserId=1 LIMIT 1;" );
            
            $UpdateAdmin->BindValue(":Name", $_REQUEST["name"], PDO::PARAM_STR);
            $UpdateAdmin->BindValue(":Password", $HashedPassword, PDO::PARAM_STR);
            $UpdateAdmin->BindValue(":Salt", $Salt, PDO::PARAM_STR);

            $UpdateAdmin->execute(true);

        }
    }
    catch (PDOException $Exception)
    {
        $Out->pushError($Exception->getMessage());
    }

    $Out->flushXML("");

    echo "</database>";
?>