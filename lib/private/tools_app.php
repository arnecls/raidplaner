<?php
    require_once("lib/private/connector.class.php");
    
    function CheckVersion($siteVersion)
    {
        try
        {
            $Connector = Connector::GetInstance(true);
            $versionSt = $Connector->prepare("SELECT IntValue FROM `".RP_TABLE_PREFIX."Setting` WHERE Name = 'Version' LIMIT 1" );
            
            if ($versionSt->execute())
            {
                $result = $versionSt->fetch( PDO::FETCH_ASSOC ); 
                $versionSt->closeCursor();
                
                return intval($siteVersion) == intval($result["IntValue"]);
            }
            
            $versionSt->closeCursor();
        }
        catch(PDOException $Exception)
        {
        }
        
        return false;        
    }
?>