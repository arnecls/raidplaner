<?php
    require_once dirname(__FILE__).'/userproxy.class.php';

    define('LOG_TYPE_RAID',     'raid');
    define('LOG_TYPE_ATTEND',   'attn');
    define('LOG_TYPE_USER',     'user');
    define('LOG_TYPE_CONFIG',   'conf');
    define('LOG_TYPE_LOCATION', 'loca');

    define('LOG_SUBTYPE_CREATE', 'new');
    define('LOG_SUBTYPE_UPDATE', 'mod');
    define('LOG_SUBTYPE_DELETE', 'del');

    class Log
    {
        private static $Instance = NULL;
        private $mUser;
        private $mConnector;

        static public function getInstance()
        {
            if (self::$Instance == NULL)
                self::$Instance = new Log();
            return self::$Instance;
        }

        public function __construct()
        {
            $this->mUser = UserProxy::getInstance();
            $this->mConnector = Connector::getInstance();
        }

        public function create($aType, $aReferenceId=0, $aMessage=NULL)
        {
            $this->write($aType, LOG_SUBTYPE_CREATE, $aReferenceId, $aMessage);
        }

        public function update($aType, $aReferenceId=0, $aMessage=NULL)
        {
            $this->write($aType, LOG_SUBTYPE_UPDATE, $aReferenceId, $aMessage);
        }

        public function delete($aType, $aReferenceId=0, $aMessage=NULL)
        {
            $this->write($aType, LOG_SUBTYPE_DELETE, $aReferenceId, $aMessage);
        }

        public function write($aType, $aSubtype, $aReferenceId=0, $aMessage=NULL)
        {
            $logQuery = $this->mConnector->prepare('INSERT INTO `'.RP_TABLE_PREFIX.'Logs` '.
                '(UserId, ReferenceId, Type, Subtype, Message) '.
                'VALUES (:UserId, :ReferenceId, :Type, :Subtype, :Message)');

            switch (true) {
            case is_string($aMessage):
                $encodedMessage = '{message:"'.$aMessage.'"}';
                break;
            case is_numeric($aMessage):
                $encodedMessage = '{message:'.$aMessage.'}';
                break;
            case ($aMessage == NULL || $aMessage == '' || count($aMessage) == 0):
                $encodedMessage = '{}';
                break;
            default:
                $encodedMessage = json_encode($aMessage);
            }

            $logQuery->bindValue(':UserId', $this->mUser->UserId, PDO::PARAM_INT);
            $logQuery->bindValue(':ReferenceId', $aReferenceId, PDO::PARAM_INT);
            $logQuery->bindValue(':Type', $aType, PDO::PARAM_STR);
            $logQuery->bindValue(':Subtype', $aSubtype, PDO::PARAM_STR);
            $logQuery->bindValue(':Message', $encodedMessage, PDO::PARAM_STR);
            $logQuery->execute();
        }
    }
?>
