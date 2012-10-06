<?php

require_once(dirname(__FILE__)."/connector.class.php");

class Settings
{
    private static $Instance = NULL;
    public $Property = Array();

    // --------------------------------------------------------------------------------------------

    public function __construct()
    {
        $this->Update();
    }

    // --------------------------------------------------------------------------------------------

    public static function GetInstance()
    {
        if (self::$Instance == NULL)
        {
            self::$Instance = new Settings();
        }

        return self::$Instance;
    }

    // --------------------------------------------------------------------------------------------

    public function Update()
    {
        $Connector = Connector::GetInstance();
        $query = $Connector->prepare( "SELECT * FROM `".RP_TABLE_PREFIX."Setting` ORDER BY Name" );

        $this->Property = Array();

        if ( $query->execute() )
        {
            while ( $Data = $query->fetch( PDO::FETCH_ASSOC ) )
            {
                $this->Property[$Data["Name"]] = Array(
                    "IntValue"  => intval($Data["IntValue"]),
                    "TextValue" => $Data["TextValue"]
                );
            }
        }

        $query->closeCursor();
    }
}

?>