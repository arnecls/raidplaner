<?php

	require_once(dirname(__FILE__)."/../config/config.php");
	require_once(dirname(__FILE__)."/locale.php");
	
	class Connector extends PDO
	{
		private static $Instance = NULL;
		
		private $Host;
		private $Table;
		
		// --------------------------------------------------------------------------------------------
	
		public function __construct($_Host, $_Table, $_User, $_Pass)
		{
			assert(self::$Instance == NULL);
			
			try
			{
                $this->Host  = $_Host;
                $this->Table = $_Table;
				parent::__construct("mysql:dbname=".$_Table.";host=".$_Host, $_User, $_Pass);
			}
			catch (PDOException $Exception)
			{
				die("Database connection error: ".$Exception->getMessage());
			}
		}
		
		// --------------------------------------------------------------------------------------------
		
		public static function GetInstance()
		{
		    return self::GetExternInstance(SQL_HOST, RP_TABLE, RP_USER, RP_PASS);
		}
		
		// --------------------------------------------------------------------------------------------
		
		public static function GetExternInstance($_Host, $_Table, $_User, $_Pass)
		{
			if (self::$Instance == NULL)
			{
				self::$Instance = new Connector($_Host, $_Table, $_User, $_Pass);
            }
            else
            {
                if ((self::$Instance->Host != $_Host) || 
                    (self::$Instance->Table != $_Table))
                {
                    self::$Instance = NULL;
                    self::$Instance = new Connector($_Host, $_Table, $_User, $_Pass);
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