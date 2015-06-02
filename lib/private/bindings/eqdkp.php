<?php
    include_once_exists(dirname(__FILE__).'/../../config/config.eqdkp.php');

    array_push(PluginRegistry::$Classes, 'EQDKPBinding');

    class EQDKPBinding extends Binding
    {
        private static $BindingName = 'eqdkp';

        public static $HashMethod_sha512s = 'eqdkp_sha512s';
        public static $HashMethod_sha512b = 'eqdkp_sha512sb';
        public static $HashMethod_sha512d = 'eqdkp_sha512sd';
        public static $HashMethod_sha512r = 'eqdkp_sha512r';
        public static $HashMethod_md5     = 'eqdkp_md5';

        // -------------------------------------------------------------------------

        public function getName()
        {
            return self::$BindingName;
        }

        // -------------------------------------------------------------------------

        public function getConfig()
        {
            $Config = new BindingConfig();

            $Config->Database         = defined('EQDKP_DATABASE') ? EQDKP_DATABASE : RP_DATABASE;
            $Config->User             = defined('EQDKP_USER') ? EQDKP_USER : RP_USER;
            $Config->Password         = defined('EQDKP_PASS') ? EQDKP_PASS : RP_PASS;
            $Config->Prefix           = defined('EQDKP_TABLE_PREFIX') ? EQDKP_TABLE_PREFIX : 'eqdkp_';
            $Config->AutoLoginEnabled = defined('EQDKP_AUTOLOGIN') ? EQDKP_AUTOLOGIN : false;
            $Config->Version          = defined('EQDKP_VERSION') ? EQDKP_VERSION : 10000;

            return $Config;
        }

        // -------------------------------------------------------------------------

        public function getExternalConfig($aRelativePath)
        {
            $Out = Out::getInstance();

            $ConfigPath = $_SERVER['DOCUMENT_ROOT'].'/'.$aRelativePath.'/config.php';
            $DataPath = $_SERVER['DOCUMENT_ROOT'].'/'.$aRelativePath.'/data';

            if (!file_exists($ConfigPath))
            {
                $Out->pushError($ConfigPath.' '.L('NotExisting').'.');
                return null;
            }

            @include_once($ConfigPath);

            if (!defined('EQDKP_INSTALLED'))
            {
                $Out->pushError(L('NoValidConfig'));
                return null;
            }

            $Version = 10000;
            $DataFolder = scandir($DataPath);

            foreach($DataFolder as $Candidate)
            {
                $LocalConfPath = $DataPath.'/'.$Candidate.'/eqdkp/config/localconf.php';
                if (file_exists($LocalConfPath))
                {
                    define('EQDKP_INC',1);
                    @include_once($LocalConfPath);

                    $VersionParts = explode('.', $localconf['plus_version']);
                    $Version = intval($VersionParts[0]) * 10000 + intval($VersionParts[1]) * 100 + intval($VersionParts[2]);
                    break;
                }
            }

            return array(
                'database'  => $dbname,
                'user'      => $dbuser,
                'password'  => $dbpass,
                'prefix'    => $table_prefix,
                'cookie'    => null,
                'version'   => $Version
            );
        }

        // -------------------------------------------------------------------------

        public function writeConfig($aEnable, $aConfig)
        {
            $Config = fopen( dirname(__FILE__).'/../../config/config.eqdkp.php', 'w+' );

            fwrite( $Config, "<?php\n");
            fwrite( $Config, "\tdefine('EQDKP_BINDING', ".(($aEnable) ? "true" : "false").");\n");

            if ( $aEnable )
            {
                fwrite( $Config, "\tdefine('EQDKP_DATABASE', '".$aConfig->Database."');\n");
                fwrite( $Config, "\tdefine('EQDKP_USER', '".$aConfig->User."');\n");
                fwrite( $Config, "\tdefine('EQDKP_PASS', '".$aConfig->Password."');\n");
                fwrite( $Config, "\tdefine('EQDKP_TABLE_PREFIX', '".$aConfig->Prefix."');\n");
                fwrite( $Config, "\tdefine('EQDKP_AUTOLOGIN', ".(($aConfig->AutoLoginEnabled) ? "true" : "false").");\n");
            }

            fwrite( $Config, "?>");

            fclose( $Config );
        }

        // -------------------------------------------------------------------------

        public function getGroups($aDatabase, $aPrefix, $aUser, $aPass, $aThrow)
        {
            $Connector = new Connector(SQL_HOST, $aDatabase, $aUser, $aPass, $aThrow);

            if ($Connector != null)
            {
                // only test if we can read the user table
                $TestQuery = $Connector->prepare( 'SELECT user_id FROM `'.$aPrefix.'users` LIMIT 1' );
                $TestQuery->execute($aThrow);
           }

            return null;
        }

        // -------------------------------------------------------------------------

        public function getForums($aDatabase, $aPrefix, $aUser, $aPass, $aThrow)
        {
            return null;

        }

        // -------------------------------------------------------------------------

        public function getUsers($aDatabase, $aPrefix, $aUser, $aPass, $aThrow)
        {
            return null;

        }

        // -------------------------------------------------------------------------

        private function getGroupForUser( $aUserId )
        {
            $Connector = $this->getConnector();
            $Config = $this->getConfig();
            $UserRightsQuery = $Connector->prepare('SELECT '.$Config->Prefix.'users.user_active, '.$Config->Prefix.'auth_users.auth_setting,  '.$Config->Prefix.'auth_options.auth_value '.
                                                'FROM `'.$Config->Prefix.'users` '.
                                                'LEFT JOIN `'.$Config->Prefix.'auth_users` USING(user_id) '.
                                                'LEFT JOIN `'.$Config->Prefix.'auth_options` USING(auth_id) '.
                                                'WHERE user_id = :UserId');

            $UserRightsQuery->bindValue(':UserId', $aUserId, PDO::PARAM_INT);
            $AssignedGroup = ENUM_GROUP_MEMBER;

            $UserRightsQuery->loop(function($Right) use (&$AssignedGroup)
            {
                if ( $Right['user_active'] == 0 )
                {
                    $AssignedGroup = ENUM_GROUP_NONE;
                    return false; // ### return, not active ###
                }

                if ( (($Right['auth_value'] == 'a_raid_add') || ($Right['auth_value'] == 'a_raid_upd'))
                     && ($Right['auth_setting'] == 'Y') )
                {
                    $AssignedGroup = ENUM_GROUP_RAIDLEAD;
                    return false; // ### return, highest possible group ###
                }
            });

            return GetGroupName($AssignedGroup);
        }

        // -------------------------------------------------------------------------

        private function generateUserInfo( $aUserData )
        {
            $Info = new UserInfo();
            $Info->UserId      = $aUserData['user_id'];
            $Info->UserName    = $aUserData['username'];
            $Info->Password    = $aUserData['user_password'];
            $Info->Salt        = self::extractSaltPart($aUserData['user_password']);
            $Info->SessionSalt = null;
            $Info->Group       = $this->getGroupForUser($aUserData['user_id']);
            $Info->BindingName = $this->getName();
            $Info->PassBinding = $this->getName();

            return $Info;
        }

        // -------------------------------------------------------------------------

        public function getExternalLoginData()
        {
            if (!defined('EQDKP_AUTOLOGIN') || !EQDKP_AUTOLOGIN)
                return null;

            $Connector = $this->getConnector();
            $UserInfo = null;

            // Fetch cookie name

            $CookieQuery = $Connector->prepare('SELECT config_value '.
                'FROM `'.EQDKP_TABLE_PREFIX.'backup_cnf` '.
                'WHERE config_name = "cookie_name" LIMIT 1');

            $ConfigData = $CookieQuery->fetchFirst();

            if ( $ConfigData != null )
            {
                $CookieName = $ConfigData['config_value'].'_sid';

                // Fetch user info if seesion cookie is set

                if (isset($_COOKIE[$CookieName]))
                {
                    $UserQuery = $Connector->prepare('SELECT session_user_id '.
                        'FROM `'.EQDKP_TABLE_PREFIX.'sessions` '.
                        'WHERE session_id = :sid LIMIT 1');

                    $UserQuery->BindValue( ':sid', $_COOKIE[$CookieName], PDO::PARAM_STR );
                    $UserData = $UserQuery->fetchFirst();

                    if ( $UserData != null )
                    {
                        // Get user info by external id

                        $UserData = $UserQuery->fetchFirst();
                        $UserId = $UserData['session_user_id'];

                        $UserInfo = $this->getUserInfoById($UserId);
                    }
                }
            }

            return $UserInfo;
        }

        // -------------------------------------------------------------------------

        public function getUserInfoByName( $aUserName )
        {
            $Connector = $this->getConnector();
            $UserQuery = $Connector->prepare('SELECT user_id, username, user_password '.
                                          'FROM `'.EQDKP_TABLE_PREFIX.'users` '.
                                          'WHERE LOWER(username) = :Login LIMIT 1');

            $UserQuery->bindValue(':Login', strtolower($aUserName), PDO::PARAM_STR);
            $UserData = $UserQuery->fetchFirst();

            return ($UserData != null)
                ? $this->generateUserInfo($UserData)
                : null;
        }

        // -------------------------------------------------------------------------

        public function getUserInfoById( $aUserId )
        {
            $Connector = $this->getConnector();
            $UserQuery = $Connector->prepare('SELECT user_id, username, user_password '.
                                          'FROM `'.EQDKP_TABLE_PREFIX.'users` '.
                                          'WHERE user_id = :UserId LIMIT 1');

            $UserQuery->bindValue(':UserId', $aUserId, PDO::PARAM_INT);
            $UserData = $UserQuery->fetchFirst();

            return ($UserData != null)
                ? $this->generateUserInfo($UserData)
                : null;
        }

        // -------------------------------------------------------------------------

        private static function extractSaltPart( $aPassword )
        {
            global $gItoa64;
            $Length = strlen(substr($aPassword, 0, strpos($aPassword, ':')));

            if ((substr($aPassword, 0, 4) == '$2a$') && ($Length == 60))
            {
                $Parts = explode(':', $aPassword);

                return substr($Parts[0],0,29).':'.$Parts[1];
            }

            if (($aPassword[0] == '_') && ($Length == 20))
            {
                $Parts = explode(':', $aPassword);
                return substr($Parts[0],0,9).':'.$Parts[1];
            }

            if ((substr($aPassword, 0, 3) == '$S$') && ($Length == 98))
            {
                $Count = strpos($gItoa64, $aPassword[3]);
                $Salt2 = substr($aPassword, 4, 8);
                $Salt  = substr($aPassword, strpos($aPassword,':')+1);

                return $Count.':'.$Salt.':'.$Salt2;
            }

            if ($Length == 128)
            {
                $Parts = explode(':', $aPassword);
                return $Parts[1];
            }

            return '';
        }

        // -------------------------------------------------------------------------

        public function getMethodFromPass( $aPassword )
        {
            $Length = strlen(substr($aPassword, 0, strpos($aPassword, ':')));

            if ((substr($aPassword, 0, 4) == '$2a$') && ($Length == 60))
                return self::$HashMethod_sha512b;

            if (($Length > 0) && ($aPassword[0] == '_') && ($Length == 20))
                return self::$HashMethod_sha512d;

            if ((substr($aPassword, 0, 3) == '$S$') && ($Length == 98))

                return self::$HashMethod_sha512r;

            if ($Length == 128)

                return self::$HashMethod_sha512s;

            return self::$HashMethod_md5;
        }

        // -------------------------------------------------------------------------

        public function hash( $aPassword, $aSalt, $aMethod )
        {
            global $gItoa64;

            if ( ($aMethod == self::$HashMethod_sha512b) ||
                 ($aMethod == self::$HashMethod_sha512d) )
            {
                $Parts  = explode(':',$aSalt);
                $Config = $Parts[0];
                $Salt   = $Parts[1];

                $PreHash = hash('sha512', $Salt.$aPassword);

                return crypt($PreHash, $Config).':'.$Salt;
            }

            if ( $aMethod == self::$HashMethod_sha512r )
            {
                $Parts   = explode(':',$aSalt);
                $CountB2 = intval($Parts[0], 10);
                $Count   = 1 << $CountB2;
                $Salt    = $Parts[1];
                $Salt2   = $Parts[2];

                $PreHash = hash('sha512', $Salt.$aPassword);
                $Hash    = hash('sha512', $Salt2.$PreHash, true);

                do {
                    $Hash = hash('sha512', $Hash.$PreHash, true);
                } while(--$Count);

                return '$S$'.$gItoa64[$CountB2].$Salt2.encode64($Hash,strlen($Hash)).':'.$Salt;
            }

            if ( $aMethod == self::$HashMethod_sha512s )
            {
                return hash('sha512', $aSalt.$aPassword);
            }

            return md5($aPassword);
        }

        // -------------------------------------------------------------------------

        public function post($aSubject, $aMessage)
        {

        }
    }
?>