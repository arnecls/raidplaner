<?php
    include_once_exists(dirname(__FILE__).'/../../config/config.drupal.php');

    array_push(PluginRegistry::$Classes, 'DrupalBinding');

    class DrupalBinding extends Binding
    {
        private static $BindingName = 'drupal';
        private static $AuthenticatedGroupId = 2;

        public static $HashMethod_sha512  = 'drupal_sha512';
        public static $HashMethod_usha512 = 'drupal_usha512';
        public static $HashMethod_pmd5    = 'drupal_pmd5';
        public static $HashMethod_hmd5    = 'drupal_hmd5';
        public static $HashMethod_upmd5   = 'drupal_upmd5';
        public static $HashMethod_uhmd5   = 'drupal_uhmd5';

        // -------------------------------------------------------------------------

        public function getName()
        {
            return self::$BindingName;
        }

        // -------------------------------------------------------------------------

        public function getConfig()
        {
            $Config = new BindingConfig();

            $Config->Database         = defined('DRUPAL_DATABASE') ? DRUPAL_DATABASE : RP_DATABASE;
            $Config->User             = defined('DRUPAL_USER') ? DRUPAL_USER : RP_USER;
            $Config->Password         = defined('DRUPAL_PASS') ? DRUPAL_PASS : RP_PASS;
            $Config->Prefix           = defined('DRUPAL_TABLE_PREFIX') ? DRUPAL_TABLE_PREFIX : '';
            $Config->CookieData       = defined('DRUPAL_ROOT') ? DRUPAL_ROOT : 'http://'.$_SERVER['HTTP_HOST'];
            $Config->Version          = defined('DRUPAL_VERSION') ? DRUPAL_VERSION : 70600;
            $Config->AutoLoginEnabled = defined('DRUPAL_AUTOLOGIN') ? DRUPAL_AUTOLOGIN : false;
            $Config->Members          = defined('DRUPAL_MEMBER_GROUPS') ? explode(',', DRUPAL_MEMBER_GROUPS ) : array();
            $Config->Privileged       = defined('DRUPAL_PRIVILEGED_GROUPS') ? explode(',', DRUPAL_PRIVILEGED_GROUPS ) : array();
            $Config->Raidleads        = defined('DRUPAL_RAIDLEAD_GROUPS') ? explode(',', DRUPAL_RAIDLEAD_GROUPS ) : array();
            $Config->Admins           = defined('DRUPAL_ADMIN_GROUPS') ? explode(',', DRUPAL_ADMIN_GROUPS ) : array();
            $Config->HasCookieConfig  = true;
            $Config->HasGroupConfig   = true;

            return $Config;
        }

        // -------------------------------------------------------------------------

        public function getExternalConfig($aRelativePath)
        {
            $Out = Out::getInstance();
            $ConfigPath = $_SERVER['DOCUMENT_ROOT'].'/'.$aRelativePath.'/sites';
            $BootstrapPath = $_SERVER['DOCUMENT_ROOT'].'/'.$aRelativePath.'/includes/bootstrap.inc';

            if (!file_exists($ConfigPath))
            {
                $Out->pushError($ConfigPath.' '.L('NotExisting').'.');
                return null;
            }

            @include_once($BootstrapPath);

            $Version = 70000;
            if (defined('VERSION'))
            {
                $VersionParts = explode('.', VERSION);
                $Version = intval($VersionParts[0]) * 10000 + intval($VersionParts[1]) * 100;
            }


            $Sites = scandir($ConfigPath);

            foreach($Sites as $SiteDir)
            {
                if (is_dir($ConfigPath.'/'.$SiteDir) && file_exists($ConfigPath.'/'.$SiteDir.'/settings.php'))
                {
                    @include_once($ConfigPath.'/'.$SiteDir.'/settings.php');

                    if (isset($databases) && isset($databases['default']['default']))
                    {
                        $DbConfig = $databases['default']['default'];

                        return array(
                            'database'  => $DbConfig['database'],
                            'user'      => $DbConfig['username'],
                            'password'  => $DbConfig['password'],
                            'prefix'    => $DbConfig['prefix'],
                            'cookie'    => (isset($base_url)) ? $base_url : 'http://'.$_SERVER['HTTP_HOST'].'/'.$aRelativePath,
                            'version'   => $Version
                        );

                    }

                }
            }

            $Out->pushError(L('NoValidConfig'));
            return null;
        }

        // -------------------------------------------------------------------------

        public function writeConfig($aEnable, $aConfig)
        {
            $Config = fopen( dirname(__FILE__).'/../../config/config.drupal.php', 'w+' );

            fwrite( $Config, "<?php\n");
            fwrite( $Config, "\tdefine('DRUPAL_BINDING', ".(($aEnable) ? 'true' : 'false').");\n");

            if ( $aEnable )
            {
                fwrite( $Config, "\tdefine('DRUPAL_DATABASE', '".$aConfig->Database."');\n");
                fwrite( $Config, "\tdefine('DRUPAL_USER', '".$aConfig->User."');\n");
                fwrite( $Config, "\tdefine('DRUPAL_PASS', '".$aConfig->Password."');\n");
                fwrite( $Config, "\tdefine('DRUPAL_TABLE_PREFIX', '".$aConfig->Prefix."');\n");
                fwrite( $Config, "\tdefine('DRUPAL_ROOT', '".$aConfig->CookieData."');\n");
                fwrite( $Config, "\tdefine('DRUPAL_AUTOLOGIN', ".(($aConfig->AutoLoginEnabled) ? 'true' : 'false').");\n");

                fwrite( $Config, "\tdefine('DRUPAL_MEMBER_GROUPS', '".implode( ",", $aConfig->Members )."');\n");
                fwrite( $Config, "\tdefine('DRUPAL_PRIVILEGED_GROUPS', '".implode( ",", $aConfig->Privileged )."');\n");
                fwrite( $Config, "\tdefine('DRUPAL_RAIDLEAD_GROUPS', '".implode( ",", $aConfig->Raidleads )."');\n");
                fwrite( $Config, "\tdefine('DRUPAL_ADMIN_GROUPS', '".implode( ",", $aConfig->Admins )."');\n");
            }

            fwrite( $Config, '?>');

            fclose( $Config );
        }

        // -------------------------------------------------------------------------

        public function getGroups($aDatabase, $aPrefix, $aUser, $aPass, $aThrow)
        {
            $Connector = new Connector(SQL_HOST, $aDatabase, $aUser, $aPass, $aThrow);

            if ($Connector != null)
            {
                $GroupQuery = $Connector->prepare( 'SELECT rid, name FROM `'.$aPrefix.'role` ORDER BY name' );
                $Groups = array();

                $GroupQuery->loop(function($Group) use (&$Groups) {
                    array_push( $Groups, array(
                        'id'   => $Group['rid'],
                        'name' => $Group['name'])
                    );
                }, $aThrow);

                return $Groups;
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
            $AssignedGroup = ENUM_GROUP_NONE;

            // Authenticated users don't gain the corresponding role, so we need to
            // fake the assigment check. 'If the user is not blocked, he/she is
            // authenticated'.

            if ( in_array(self::$AuthenticatedGroupId, $MemberGroups) )
                $AssignedGroup = 'member';

            $GroupQuery = $Connector->prepare('SELECT status, rid '.
                                              'FROM `'.DRUPAL_TABLE_PREFIX.'users` '.
                                              'LEFT OUTER JOIN `'.DRUPAL_TABLE_PREFIX.'users_roles` USING(uid) '.
                                              'WHERE uid = :UserId');

            $GroupQuery->bindValue(':UserId', $aUserId, PDO::PARAM_INT);

            $GroupQuery->loop(function($Group) use (&$AssignedGroup, $Config)
            {
                if ( $Group['status'] == 0 )
                {
                    $AssignedGroup = ENUM_GROUP_NONE;
                    return false; // ### return, blocked ###
                }

                if ( $Group['rid'] != NULL )
                {
                    $AssignedGroup = $Config->mapGroup($Group['rid'], $AssignedGroup);
                }
            });

            return GetGroupName($AssignedGroup);
        }

        // -------------------------------------------------------------------------

        private function generateUserInfo( $aUserData )
        {
            $Info = new UserInfo();
            $Info->UserId      = $aUserData['uid'];
            $Info->UserName    = $aUserData['name'];
            $Info->Password    = $aUserData['pass'];
            $Info->Salt        = self::extractSaltPart($aUserData['pass']);
            $Info->SessionSalt = null;
            $Info->Group       = $this->getGroupForUser($aUserData['uid']);
            $Info->BindingName = $this->getName();
            $Info->PassBinding = $this->getName();

            return $Info;
        }

        // -------------------------------------------------------------------------

        public function getExternalLoginData()
        {
            if (!defined('DRUPAL_AUTOLOGIN') || !DRUPAL_AUTOLOGIN)
                return null;

            $UserInfo = null;

            if (defined('DRUPAL_ROOT'))
            {
                // Derive the drupal cookie name from its root path

                $DrupalBaseDir = substr(DRUPAL_ROOT, strpos(DRUPAL_ROOT, '://')+3);

                $Prefix = ini_get('session.cookie_secure') ? 'SSESS' : 'SESS';
                $CookieName = $Prefix.substr(hash('sha256', $DrupalBaseDir), 0, 32);

                if (isset($_COOKIE[$CookieName]))
                {
                    // Query the user id and info

                    $Connector = $this->getConnector();

                    $UserQuery = $Connector->prepare('SELECT uid '.
                         'FROM `'.DRUPAL_TABLE_PREFIX.'sessions` '.
                         'WHERE sid = :sid LIMIT 1');

                    $UserQuery->BindValue( ':sid', $_COOKIE[$CookieName], PDO::PARAM_STR );
                    $UserData = $UserQuery->fetchFirst();

                    if ( $UserData != null )
                    {
                        $UserId = $UserData['uid'];
                        $UserInfo = $this->getUserInfoById($UserId); // ### return, userinfo ###
                    }
                }
            }

            return $UserInfo;

        }

        // -------------------------------------------------------------------------

        public function getUserInfoByName( $aUserName )
        {
            $Connector = $this->getConnector();
            $UserQuery = $Connector->prepare('SELECT uid, name, pass '.
                                          'FROM `'.DRUPAL_TABLE_PREFIX.'users` '.
                                          'WHERE LOWER(name) = :Login LIMIT 1');

            $UserQuery->bindValue(':Login', strtolower($aUserName), PDO::PARAM_STR);
            $UserData = $UserQuery->fetchFirst();

            return ($UserData != null)

                ? $this->generateUserInfo( $UserData )
                : null;
        }

        // -------------------------------------------------------------------------

        public function getUserInfoById( $aUserId )
        {
            $Connector = $this->getConnector();
            $UserQuery = $Connector->prepare('SELECT uid, name, pass '.
                                                 'FROM `'.DRUPAL_TABLE_PREFIX.'users` '.
                                                 'WHERE uid = :UserId LIMIT 1');

            $UserQuery->bindValue(':UserId', $aUserId, PDO::PARAM_INT);
            $UserData = $UserQuery->fetchFirst();

            return ($UserData != null)

                ? $this->generateUserInfo( $UserData )
                : null;
        }

        // -------------------------------------------------------------------------

        private static function extractSaltPart( $aPassword )
        {
            global $gItoa64;
            $MD5Prefix = (substr($aPassword, 0, 2) == 'U$');

            $Salt = ($MD5Prefix)
                ? substr($aPassword, 5, 8)
                : substr($aPassword, 4, 8);

            $Count = strpos($gItoa64, ($MD5Prefix) ? $aPassword[4] : $aPassword[3]);

            return $Count.':'.$Salt;
        }

        // -------------------------------------------------------------------------

        public function getMethodFromPass( $aPassword )
        {
            $MD5Prefix = (substr($aPassword, 0, 2) == 'U$');
            $Type = ($MD5Prefix)
                ? substr($aPassword, 1, 3)
                : substr($aPassword, 0, 3);

            switch($Type)
            {
            case '$S$':
                return ($MD5Prefix) ? self::$HashMethod_usha512 : self::$HashMethod_sha512;

            case '$H$':
                return ($MD5Prefix) ? self::$HashMethod_uhmd5 : self::$HashMethod_hmd5;

            case '$P$':
                return ($MD5Prefix) ? self::$HashMethod_upmd5 : self::$HashMethod_pmd5;

            default:
                break;
            }

            return self::$HashMethod_md5s;
        }

        // -------------------------------------------------------------------------

        public function hash( $aPassword, $aSalt, $aMethod )
        {
            global $gItoa64;

            $Password = $aPassword;
            $Prefix = '';

            switch($aMethod)
            {
            case self::$HashMethod_sha512:
                $Prefix = '$S$';
                break;

            case self::$HashMethod_usha512:
                $Password = md5($Password);
                $Prefix = '$S$';
                break;

            case self::$HashMethod_uhmd5:
                $Password = md5($Password);
                $Prefix = 'U$H$';
                break;

            case self::$HashMethod_hmd5:
                $Prefix = '$H$';
                break;

            case self::$HashMethod_upmd5:
                $Password = md5($Password);
                $Prefix = 'U$P$';
                break;

            case self::$HashMethod_pmd5:
                $Prefix = '$P$';
                break;

            default:
                break;
            }

            $Parts   = explode(':',$aSalt);
            $CountB2 = intval($Parts[0],10);
            $Count   = 1 << $CountB2;
            $Salt    = $Parts[1];
            $Hash    = null;

            if (($aMethod == self::$HashMethod_sha512) ||
                ($aMethod == self::$HashMethod_usha512))
            {

                $Hash = hash('sha512', $Salt.$Password, TRUE);

                do {
                    $Hash = hash('sha512', $Hash.$Password, TRUE);
                } while (--$Count);

                $Hash = encode64($Hash,64);
            }
            else
            {
                $Hash = md5($Salt.$Password, TRUE);

                do {
                    $Hash = md5($Hash.$Password, TRUE);
                } while (--$count);

                $Hash = encode64($Hash,16);
            }

            return substr($Prefix.$gItoa64[$CountB2].$Salt.$Hash, 0, 55);
        }

        // -------------------------------------------------------------------------

        public function post($aSubject, $aMessage)
        {

        }
    }
?>
