<?php

    require_once(dirname(__FILE__)."/tools_string.php");
    require_once(dirname(__FILE__)."/locale.php");

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
                    echo "<error>Database connection error</error>";
                    echo "<error>".xmlentities( $Exception->getMessage(), ENT_COMPAT, "UTF-8" )."</error>";
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
                foreach (parent::errorInfo() as $ErrorLine)
                {
                    echo $ErrorLine."<br/>\n";
                }

                die($aStatement);
            }

            return $StatementObj;
        }
    }
    
    // --------------------------------------------------------------------------------------------

    function postErrorMessage( $aStatement )
    {
        $ErrorInfo = $aStatement->errorInfo();
        echo "<error>".L("DatabaseError")."</error>";
        echo "<error>".$ErrorInfo[0]."</error>";
        echo "<error>".$ErrorInfo[2]."</error>";
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