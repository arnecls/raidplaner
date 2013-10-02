<?php

    require_once(dirname(__FILE__)."/tools_string.php");
    require_once(dirname(__FILE__)."/locale.php");
    require_once(dirname(__FILE__)."/out.class.php");

    class Connector extends PDO
    {
        private static $Instance = NULL;

        private $mHost;
        private $mDatabase;

        // --------------------------------------------------------------------------------------------

        public function __construct($aHost, $aDatabase, $aUser, $aPass, $aRethrow = false)
        {
            try
            {
                $this->mHost  = $aHost;
                $this->mDatabase = $aDatabase;
                parent::__construct("mysql:dbname=".$aDatabase.";host=".$aHost, $aUser, $aPass,
                    array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
            }
            catch (PDOException $Exception)
            {
                if ( $aRethrow )
                {
                    throw $Exception;
                }
                else
                {
                    $Out = Out::getInstance();
                    $Out->pushError("Database connection error");
                    $Out->pushError($Exception->getMessage());                    
                }
            }
        }

        // --------------------------------------------------------------------------------------------

        public static function getInstance( $aRethrow = false )
        {
            require_once(dirname(__FILE__)."/../config/config.php");
            return self::getExternInstance(SQL_HOST, RP_DATABASE, RP_USER, RP_PASS, $aRethrow);
        }

        // --------------------------------------------------------------------------------------------

        public static function getExternInstance($aHost, $aDatabase, $aUser, $aPass, $aRethrow = false)
        {
            if (self::$Instance == NULL)
            {
                self::$Instance = new Connector($aHost, $aDatabase, $aUser, $aPass, $aRethrow);
            }
            else
            {
                if ((self::$Instance->mHost != $aHost) ||
                    (self::$Instance->mDatabase != $aDatabase))
                {
                    self::$Instance = NULL;
                    self::$Instance = new Connector($aHost, $aDatabase, $aUser, $aPass, $aRethrow);
                }
            }

            return self::$Instance;
        }

        // --------------------------------------------------------------------------------------------

        public function prepare($aStatement, $aDriverOptions=array())
        {
            $StatementObj = parent::prepare($aStatement, $aDriverOptions);

            if ($StatementObj === false)
            {
                $Out = Out::getInstance();
                
                foreach (parent::errorInfo() as $ErrorLine)
                {
                    $Out->pushError($ErrorLine);
                }

                $Out->writeJSON();
                die($aStatement);
            }

            return $StatementObj;
        }
    }
    
    // --------------------------------------------------------------------------------------------

    function postErrorMessage( $aStatement )
    {
        $Out = Out::getInstance();
        $Out->pushError(L("DatabaseError"));
        
        $ErrorInfo = $aStatement->errorInfo();
        
        foreach($ErrorInfo as $Info)
        {
            $Out->pushError($Info);
        }
    }
    
    // --------------------------------------------------------------------------------------------

    function postHTMLErrorMessage( $aStatement )
    {
        $ErrorInfo = $aStatement->errorInfo();
        echo "<div class=\"database_error\">";
        echo "<div class=\"error_head\">".L("DatabaseError")."</div>";
        echo "<div class=\"error_line error_line1\">".$ErrorInfo[0]."</div>";
        echo "<div class=\"error_line error_line2\">".$ErrorInfo[2]."</div>";
        echo "</div>";
    }
?>