<?php

	require_once(dirname(__FILE__)."/../config/config.php");
	require_once(dirname(__FILE__)."/tools_string.php");
	require_once(dirname(__FILE__)."/locale.php");
	
	class Connector extends PDO
	{
		private static $Instance = NULL;
		
		private $Host;
		private $Database;
		
		// --------------------------------------------------------------------------------------------
	
		public function __construct($_Host, $_Database, $_User, $_Pass)
		{
			try
			{
                $this->Host  = $_Host;
                $this->Database = $_Database;
				parent::__construct("mysql:dbname=".$_Database.";host=".$_Host, $_User, $_Pass);
			}
			catch (PDOException $Exception)
			{
				echo "<error>Database connection error</error>";
				echo "<error>".xmlentities( $Exception->getMessage(), ENT_COMPAT, "UTF-8" )."</error>";
			}
		}
		
		// --------------------------------------------------------------------------------------------
		
		public static function GetInstance()
		{
		    return self::GetExternInstance(SQL_HOST, RP_DATABASE, RP_USER, RP_PASS);
		}
		
		// --------------------------------------------------------------------------------------------
		
		public static function GetExternInstance($_Host, $_Database, $_User, $_Pass)
		{
			if (self::$Instance == NULL)
			{
				self::$Instance = new Connector($_Host, $_Database, $_User, $_Pass);
            }
            else
            {
                if ((self::$Instance->Host != $_Host) || 
                    (self::$Instance->Database != $_Database))
                {
                    self::$Instance = NULL;
                    self::$Instance = new Connector($_Host, $_Database, $_User, $_Pass);
                }       
            }
				
			return self::$Instance;
		}
		
		// --------------------------------------------------------------------------------------------
		
		public function prepare($Statement, $driver_options=array())
		{
            $StatementObj = parent::prepare($Statement, $driver_options);
            
            if ($StatementObj === false)
            {
                foreach (parent::errorInfo() as $ErrorLine)
                {
                    echo $ErrorLine."<br/>\n";
                }
                
                die($Statement);
            }
            
            return $StatementObj;
		}
	}
	
	function postErrorMessage( $Statement )
	{
		$ErrorInfo = $Statement->errorInfo();
		echo "<error>".L("Database error")."</error>";
        echo "<error>".$ErrorInfo[0]."</error>";
        echo "<error>".$ErrorInfo[2]."</error>";
	}
?>