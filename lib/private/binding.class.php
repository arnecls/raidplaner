<?php
    require_once dirname(__FILE__).'/connector.class.php';
    require_once dirname(__FILE__).'/tools_site.php';

    define("ENUM_GROUP_NONE", 0);
    define("ENUM_GROUP_MEMBER", 1);
    define("ENUM_GROUP_PRIVILEGED", 2);
    define("ENUM_GROUP_RAIDLEAD", 3);
    define("ENUM_GROUP_ADMIN", 4);

    // Helper class for external bindings, so we don't have to use string
    // based associative arrays.
    class UserInfo
    {
        public $UserId;
        public $UserName;
        public $Password;
        public $Salt;
        public $SessionSalt;
        public $Group;
        public $BindingName;
        public $PassBinding;
    }

    // Helper class for available configuration data
    class BindingConfig
    {
        public $Database;
        public $User;
        public $Password;
        public $Prefix;
        public $CookieData;
        public $Version;
        public $Members;
        public $Privileged;
        public $Raidleads;
        public $Admins;
        public $PostTo;
        public $PostAs;
        public $AutoLoginEnabled;
        public $ForumPostEnabled;
        public $HasCookieConfig;
        public $HasGroupConfig;
        public $HasForumConfig;

        public function __construct()
        {
            $this->Database         = '';
            $this->User             = '';
            $this->Password         = '';
            $this->Prefix           = '';
            $this->CookieData       = '';
            $this->Version          = 0;
            $this->Members          = array();
            $this->Privileged       = array();
            $this->Raidleads        = array();
            $this->Admins           = array();
            $this->PostTo           = 0;
            $this->PostAs           = 0;
            $this->AutoLoginEnabled = false;
            $this->ForumPostEnabled = false;
            $this->HasCookieConfig  = false;
            $this->HasGroupConfig   = false;
            $this->HasForumConfig   = false;
        }

        public function mapGroup($aGroup, $aCurrent)
        {
            if ( in_array($aGroup, $this->Members) )
                $aCurrent = max($aCurrent, ENUM_GROUP_MEMBER);

            if ( in_array($aGroup, $this->Privileged) )
                $aCurrent = max($aCurrent, ENUM_GROUP_PRIVILEGED);

            if ( in_array($aGroup, $this->Raidleads) )
                $aCurrent = max($aCurrent, ENUM_GROUP_RAIDLEAD);

            if ( in_array($aGroup, $this->Admins) )
                $aCurrent = max($aCurrent, ENUM_GROUP_ADMIN);

            return $aCurrent;
        }
    }

    // Common interface for forum/cms bindings
    abstract class Binding
    {
        private $mConnector;

        // -------------------------------------------------------------------------

        abstract public function getName();

        abstract public function getConfig();
        abstract public function writeConfig($aEnable, $aConfig);

        abstract public function getExternalConfig($aRelativePath);
        abstract public function getGroups($aDatabase, $aPrefix, $aUser, $aPass, $aThrow);
        abstract public function getForums($aDatabase, $aPrefix, $aUser, $aPass, $aThrow);
        abstract public function getUsers($aDatabase, $aPrefix, $aUser, $aPass, $aThrow);
        abstract public function post($aSubject, $aMessage);

        abstract public function getExternalLoginData();
        abstract public function getUserInfoByName( $aUserName );
        abstract public function getUserInfoById( $aUserId );
        abstract public function getMethodFromPass( $aPassword );
        abstract public function hash( $aPassword, $aSalt, $aMethod );

        // -------------------------------------------------------------------------

        public function isActive()
        {
            $Name = strtoupper($this->getName());
            return defined($Name.'_BINDING') && constant($Name.'_BINDING');
        }

        // -------------------------------------------------------------------------

        public function postRequested()
        {
            $Name = strtoupper($this->getName());
            return defined($Name.'_POSTTO') && (constant($Name.'_POSTTO') != 0);
        }

        // -------------------------------------------------------------------------

        public function isConfigWriteable()
        {
            $ConfigFolder = dirname(__FILE__).'/../config';
            $ConfigFile   = $ConfigFolder.'/config.'.$this->getName().'.php';

            return (!file_exists($ConfigFile) && is_writable($ConfigFolder)) || is_writable($ConfigFile);
        }

        // -------------------------------------------------------------------------

        public function getConnector()
        {
            if ($this->mConnector == null)
            {
                $Config = $this->getConfig();
                $this->mConnector = new Connector(SQL_HOST, $Config->Database, $Config->User, $Config->Password, false);

            }

            return $this->mConnector;
        }

        // -------------------------------------------------------------------------

        public function getGroupsFromConfig()
        {
            $Config = $this->getConfig();
            return $this->getGroups($Config->Database, $Config->Prefix, $Config->User, $Config->Password, false);
        }

        // -------------------------------------------------------------------------

        public function getForumsFromConfig()
        {
            $Config = $this->getConfig();
            return $this->getForums($Config->Database, $Config->Prefix, $Config->User, $Config->Password, false);
        }

        // -------------------------------------------------------------------------

        public function getUsersFromConfig()
        {
            $Config = $this->getConfig();
            return $this->getUsers($Config->Database, $Config->Prefix, $Config->User, $Config->Password, false);
        }

        // -------------------------------------------------------------------------

        public static function generateMessage($aRaidData, $aLocationData)
        {
            $Template = new SimpleXMLElement( file_get_contents(dirname(__FILE__).'/../config/config.post.xml') );

            $SystemLocale   = setlocale(LC_ALL, 0);
            $SystemTimezone = date_default_timezone_get();
            $Subject = '';
            $Message = '';

            $BrowserLang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
            $DefaultLocale = str_replace('-', '_', substr($BrowserLang, 0, strpos($BrowserLang, ',')));

            try
            {
                $SubjectLocale = (isset($Template->subject['locale']))
                    ? $Template->subject['locale']
                    : $DefaultLocale;

                $SubjectTimezone = (isset($Template->subject['timezone']))
                    ? $Template->subject['timezone']
                    : $SystemTimezone;

                $MessageLocale = (isset($Template->message['locale']))
                    ? $Template->message['locale']
                    : $DefaultLocale;

                $MessageTimezone = (isset($Template->message['timezone']))
                    ? $Template->message['timezone']
                    : $SystemTimezone;

                setlocale(LC_ALL, $SubjectLocale);
                $Subject = self::parseTemplate($Template->subject, $aRaidData, $aLocationData, $SubjectTimezone);

                setlocale(LC_ALL, $MessageLocale);
                $Message = self::parseTemplate($Template->message, $aRaidData, $aLocationData, $MessageTimezone);
            }
            catch (Exception $e)
            {
            }

            setlocale(LC_ALL, $SystemLocale);
            date_default_timezone_set($SystemTimezone);

            return array(
                'subject' => trim($Subject),
                'message' => trim($Message)
            );
        }

        // -------------------------------------------------------------------------

        private static function parseTemplate($aTemplate, $aRaidData, $aLocationData, $aTimezone)
        {
            $Offset = 0;
            $Text = '';

            $TagStart = strpos($aTemplate, '{', $Offset);

            while( $TagStart !== false )
            {
                $TagEnd = strpos($aTemplate, '}', $TagStart);
                $TagData = explode(':', substr($aTemplate, $TagStart+1, $TagEnd-$TagStart-1));

                $Parsed = '';

                switch (strtolower($TagData[0]))
                {
                case 'url':
                    $Parsed = getBaseURL();
                    break;

                case 'location':
                    $Parsed = isset($aLocationData[$TagData[1]]) ? $aLocationData[$TagData[1]] : 'UNKNOWN LOCATION FIELD';
                    break;

                case 'l':
                    $Parsed = L($TagData[1]);
                    break;

                case 'raid':
                    switch (strtolower($TagData[1]))
                    {
                    case 'end':
                    case 'start':
                        date_default_timezone_set('UTC');
                        $Timestamp = strtotime($aRaidData[$TagData[1]]);

                        date_default_timezone_set($aTimezone);
                        $Parsed = strftime($TagData[2], $Timestamp);
                        break;

                    default:
                        $Parsed = isset($aRaidData[$TagData[1]]) ? $aRaidData[$TagData[1]] : 'UNKNOWN RAID FIELD';
                        break;
                    }
                    break;
                }

                $Text .= substr($aTemplate, $Offset, $TagStart-$Offset).$Parsed;
                $Offset = $TagEnd+1;
                $TagStart = strpos($aTemplate, '{', $Offset);
            }

            $Text.= substr($aTemplate, $Offset);
            return $Text;
        }
    }

    function GetGroupName($aEnumGroup)
    {
        switch($aEnumGroup)
        {
        case ENUM_GROUP_MEMBER:
            return 'member';
        case ENUM_GROUP_PRIVILEGED:
            return 'privileged';
        case ENUM_GROUP_RAIDLEAD:
            return 'raidlead';
        case ENUM_GROUP_ADMIN:
            return 'admin';
        }
        return 'none';
    }
?>