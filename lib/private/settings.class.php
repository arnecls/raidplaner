<?php

require_once(dirname(__FILE__)."/connector.class.php");

class Settings
{
    private static $mInstance = NULL;
    public $Property = Array();

    // --------------------------------------------------------------------------------------------

    public function __construct()
    {
        $this->update();
    }

    // --------------------------------------------------------------------------------------------

    public static function getInstance()
    {
        if (self::$mInstance == NULL)
        {
            self::$mInstance = new Settings();
        }

        return self::$mInstance;
    }

    // --------------------------------------------------------------------------------------------

    public function update()
    {
        $Connector = Connector::getInstance();
        $Query = $Connector->prepare( "SELECT * FROM `".RP_TABLE_PREFIX."Setting` ORDER BY Name" );

        $this->Property = Array();

        $Query->loop( function($Data) use (&$this)
        {
            $this->Property[$Data["Name"]] = Array(
                "IntValue"  => intval($Data["IntValue"]),
                "TextValue" => $Data["TextValue"]
            );
        });
    }
}

?>