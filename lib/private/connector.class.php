<?php

    require_once(dirname(__FILE__)."/tools_string.php");
    require_once(dirname(__FILE__)."/locale.php");
    require_once(dirname(__FILE__)."/out.class.php");
    require_once(dirname(__FILE__)."/query.class.php");

    class Connector extends PDO
    {
        private static $Instance = NULL;

        private $mHost;
        private $mDatabase;
        private $mRetryCounter;

        // --------------------------------------------------------------------------------------------

        public function __construct($aHost, $aDatabase, $aUser, $aPass, $aRethrow = false, $aSetTimezone = true)
        {
            try
            {
                $this->mHost  = $aHost;
                $this->mDatabase = $aDatabase;
                $this->mRetryCounter = 0;
                
                if ($aSetTimezone)
                {
                    parent::__construct("mysql:dbname=".$aDatabase.";host=".$aHost, $aUser, $aPass,
                        array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8' COLLATE 'utf8_general_ci', time_zone = '+00:00'"));
                }
                else
                {
                    parent::__construct("mysql:dbname=".$aDatabase.";host=".$aHost, $aUser, $aPass,
                        array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8' COLLATE 'utf8_general_ci'"));
                }
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
            @require_once(dirname(__FILE__)."/../config/config.php");
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
                    $Out->pushError(strval($ErrorLine));
                }

                $Out->writeJSONandStop();
            }

            return new Query($StatementObj);
        }
        
        // --------------------------------------------------------------------------------------------

        public function beginTransaction()
        {
            if ($this->mRetryCounter++ > 5)
            {
                $this->mRetryCounter = 0;
                throw new PDOException("Maximum number of transactional retries reached.");
            }
               
            return parent::beginTransaction();
        }
        
        // --------------------------------------------------------------------------------------------

        public function rollback()
        {
            $this->mRetryCounter = 0;
            return parent::rollback();
        }
        
        // --------------------------------------------------------------------------------------------

        public function commit()
        {
            $this->mRetryCounter = 0;
            return parent::commit();
        }
    }
?>