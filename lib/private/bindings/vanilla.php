<?php
    include_once_exists(dirname(__FILE__).'/../../config/config.vanilla.php');

    array_push(PluginRegistry::$Classes, 'VanillaBinding');

    class VanillaBinding extends Binding
    {
        private static $BindingName = 'vanilla';

        public static $HashMethod = 'vanilla_md5r';

        // -------------------------------------------------------------------------

        public function getName()
        {
            return self::$BindingName;
        }

        // -------------------------------------------------------------------------

        public function getConfig()
        {
            $Config = new BindingConfig();

            $Config->Database         = defined('VANILLA_DATABASE') ? VANILLA_DATABASE : RP_DATABASE;
            $Config->User             = defined('VANILLA_USER') ? VANILLA_USER : RP_USER;
            $Config->Password         = defined('VANILLA_PASS') ? VANILLA_PASS : RP_PASS;
            $Config->Prefix           = defined('VANILLA_TABLE_PREFIX') ? VANILLA_TABLE_PREFIX : 'GDN_';
            $Config->Version          = defined('VANILLA_VERSION') ? VANILLA_VERSION : 20000;
            $Config->AutoLoginEnabled = defined('VANILLA_AUTOLOGIN') ? VANILLA_AUTOLOGIN : false;
            $Config->CookieData       = defined('VANILLA_COOKIE') ? VANILLA_COOKIE : 'Vanilla,md5,123456';
            $Config->PostTo           = defined('VANILLA_POSTTO') ? VANILLA_POSTTO : '';
            $Config->PostAs           = defined('VANILLA_POSTAS') ? VANILLA_POSTAS : '';
            $Config->Members          = defined('VANILLA_MEMBER_GROUPS') ? explode(',', VANILLA_MEMBER_GROUPS ) : array();
            $Config->Privileged       = defined('VANILLA_PRIVILEGED_GROUPS') ? explode(',', VANILLA_PRIVILEGED_GROUPS ) : array();
            $Config->Raidleads        = defined('VANILLA_RAIDLEAD_GROUPS') ? explode(',', VANILLA_RAIDLEAD_GROUPS ) : array();
            $Config->Admins           = defined('VANILLA_ADMIN_GROUPS') ? explode(',', VANILLA_ADMIN_GROUPS ) : array();
            $Config->HasCookieConfig  = true;
            $Config->HasGroupConfig   = true;
            $Config->HasForumConfig   = true;

            return $Config;
        }

        // -------------------------------------------------------------------------

        public function getExternalConfig($aRelativePath)
        {
            $Out = Out::getInstance();

            $DefaultsPath = $_SERVER['DOCUMENT_ROOT'].'/'.$aRelativePath.'/conf/config-defaults.php';
            $ConfigPath   = $_SERVER['DOCUMENT_ROOT'].'/'.$aRelativePath.'/conf/config.php';
            $IndexPath    = $_SERVER['DOCUMENT_ROOT'].'/'.$aRelativePath.'/index.php';

            if (!file_exists($DefaultsPath))
            {
                $Out->pushError($DefaultsPath.' '.L('NotExisting').'.');
                return null;
            }

            if (!file_exists($ConfigPath))
            {
                $Out->pushError($ConfigPath.' '.L('NotExisting').'.');
                return null;
            }

            $Version = 20000;
            if (file_exists($IndexPath))
            {
                $Index = file_get_contents($IndexPath);
                $AppIdx = strpos($Index, 'APPLICATION_VERSION');

                if ($AppIdx !== false)
                {
                    $StripIdx = strpos($Index, ';', $AppIdx);
                    $Index = substr($Index, 0, $StripIdx+1);
                    $Index = substr($Index, strpos($Index, '<?php')+5);
                    eval($Index);

                    $VersionParts = explode('.', APPLICATION_VERSION);
                    $Version = intval($VersionParts[0]) * 10000 + intval($VersionParts[1]) * 100 + intval($VersionParts[2]);
                }
            }

            if (!defined('APPLICATION'))
                define('APPLICATION', 'Vanilla');

            define('PATH_CACHE', '');

            @include_once($DefaultsPath);
            @include_once($ConfigPath);

            if (!isset($Configuration))
            {
                $Out->pushError(L('NoValidConfig'));
                return null;
            }

            $CookieConf = $Configuration['Garden']['Cookie'];
            $DbConf = $Configuration['Database'];

            return array(
                'database'  => $DbConf['Name'],
                'user'      => $DbConf['User'],
                'password'  => $DbConf['Password'],
                'prefix'    => $DbConf['DatabasePrefix'],
                'cookie'    => $CookieConf['Name'].','.$CookieConf['HashMethod'].','.$CookieConf['Salt'],
                'version'   => $Version
            );
        }

        // -------------------------------------------------------------------------

        public function writeConfig($aEnable, $aConfig)
        {
            $Config = fopen( dirname(__FILE__).'/../../config/config.vanilla.php', 'w+' );

            fwrite( $Config, "<?php\n");
            fwrite( $Config, "\tdefine('VANILLA_BINDING', ".(($aEnable) ? "true" : "false").");\n");

            if ( $aEnable )
            {
                fwrite( $Config, "\tdefine('VANILLA_DATABASE', '".$aConfig->Database."');\n");
                fwrite( $Config, "\tdefine('VANILLA_USER', '".$aConfig->User."');\n");
                fwrite( $Config, "\tdefine('VANILLA_PASS', '".$aConfig->Password."');\n");
                fwrite( $Config, "\tdefine('VANILLA_TABLE_PREFIX', '".$aConfig->Prefix."');\n");
                fwrite( $Config, "\tdefine('VANILLA_COOKIE', '".$aConfig->CookieData."');\n");
                fwrite( $Config, "\tdefine('VANILLA_AUTOLOGIN', ".(($aConfig->AutoLoginEnabled) ? "true" : "false").");\n");
                fwrite( $Config, "\tdefine('VANILLA_POSTTO', ".$aConfig->PostTo.");\n");
                fwrite( $Config, "\tdefine('VANILLA_POSTAS', ".$aConfig->PostAs.");\n");

                fwrite( $Config, "\tdefine('VANILLA_MEMBER_GROUPS', '".implode( ",", $aConfig->Members )."');\n");
                fwrite( $Config, "\tdefine('VANILLA_PRIVILEGED_GROUPS', '".implode( ",", $aConfig->Privileged )."');\n");
                fwrite( $Config, "\tdefine('VANILLA_RAIDLEAD_GROUPS', '".implode( ",", $aConfig->Raidleads )."');\n");
                fwrite( $Config, "\tdefine('VANILLA_ADMIN_GROUPS', '".implode( ",", $aConfig->Admins )."');\n");
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
                $GroupQuery = $Connector->prepare( 'SELECT RoleID, Name FROM `'.$aPrefix.'Role` ORDER BY Name' );
                $Groups = array();

                $GroupQuery->loop(function($Group) use (&$Groups)
                {
                    array_push( $Groups, array(
                        'id'   => $Group['RoleID'],
                        'name' => $Group['Name'])
                    );
                }, $aThrow);

                return $Groups;
            }

            return null;
        }

        // -------------------------------------------------------------------------

        public function getForums($aDatabase, $aPrefix, $aUser, $aPass, $aThrow)
        {
            $Connector = new Connector(SQL_HOST, $aDatabase, $aUser, $aPass, $aThrow);

            if ($Connector != null)
            {
                $Forums = array();
                $ForumQuery = $Connector->prepare( 'SELECT CategoryID, Name FROM `'.$aPrefix.'Category` '.
                                                   'WHERE CategoryID > 0 ORDER BY Name' );

                $ForumQuery->loop(function($Forum) use (&$Forums)
                {
                    array_push( $Forums, array(
                        'id'   => $Forum['CategoryID'],
                        'name' => $Forum['Name'])
                    );
                }, $aThrow);

                return $Forums;
            }

            return null;
        }

        // -------------------------------------------------------------------------

        public function getUsers($aDatabase, $aPrefix, $aUser, $aPass, $aThrow)
        {
            $Connector = new Connector(SQL_HOST, $aDatabase, $aUser, $aPass, $aThrow);

            if ($Connector != null)
            {
                $Users = array();
                $UserQuery = $Connector->prepare( 'SELECT UserID, Name FROM `'.$aPrefix.'User` '.
                                                  'ORDER BY Name' );

                $UserQuery->loop(function($User) use (&$Users)
                {
                    array_push( $Users, array(
                        'id'   => $User['UserID'],
                        'name' => $User['Name'])
                    );
                }, $aThrow);

                return $Users;
            }

            return null;
        }

        // -------------------------------------------------------------------------

        private function getGroupForUser( $aUserData )
        {
            if ($aUserData['Banned'] > 0)
            {
                return GetGroupName(ENUM_GROUP_NONE); // ### return, banned ###
            }

            $Config = $this->getConfig();
            $AssignedGroup = ENUM_GROUP_NONE;

            foreach( $aUserData['Roles'] as $RoleId )
            {
                $AssignedGroup = $Config->mapGroup($RoleId, $AssignedGroup);
            }

            return GetGroupName($AssignedGroup);
        }

        // -------------------------------------------------------------------------

        private function generateUserInfo( $aUserData )
        {
            $Info = new UserInfo();
            $Info->UserId      = $aUserData['UserID'];
            $Info->UserName    = $aUserData['Name'];
            $Info->Password    = $aUserData['Password'];
            $Info->Salt        = self::extractSaltPart($aUserData['Password']);
            $Info->SessionSalt = null;
            $Info->Group       = $this->getGroupForUser($aUserData);
            $Info->BindingName = $this->getName();
            $Info->PassBinding = $this->getName();

            return $Info;
        }

        // -------------------------------------------------------------------------

        private static function Vanilla_HashHMAC($HashMethod, $Data, $Key)

        {
            // This function is copied over from vanilla

            $PackFormats = array('md5' => 'H32', 'sha1' => 'H40');

            if (!isset($PackFormats[$HashMethod]))
                return false;

            $PackFormat = $PackFormats[$HashMethod];
            if (isset($Key[63]))
                $Key = pack($PackFormat, $HashMethod($Key));
            else
                $Key = str_pad($Key, 64, chr(0));

            $InnerPad = (substr($Key, 0, 64) ^ str_repeat(chr(0x36), 64));
            $OuterPad = (substr($Key, 0, 64) ^ str_repeat(chr(0x5C), 64));

            return $HashMethod($OuterPad . pack($PackFormat, $HashMethod($InnerPad . $Data)));
       }

        // -------------------------------------------------------------------------

        public function getExternalLoginData()
        {
            if (!defined('VANILLA_AUTOLOGIN') || !VANILLA_AUTOLOGIN)
                return null;

            $UserInfo = null;

            // Fetch user info if seesion cookie is set

            if (defined('VANILLA_COOKIE'))
            {
                list($CookieName, $CookieHashMethod, $CookieSalt) = explode(',', VANILLA_COOKIE);

                if (isset($_COOKIE[$CookieName]))
                {
                    list($KeyData, $Signature, $Time, $UserId, $Expires) = explode('|', $_COOKIE[$CookieName]);

                    $UserInfo = $this->getUserInfoById($UserId);

                    $KeyHash     = self::Vanilla_HashHMAC($CookieHashMethod, $KeyData, $CookieSalt);
                    $KeyHashHash = self::Vanilla_HashHMAC($CookieHashMethod, $KeyData, $KeyHash);

                    if ($Signature != $KeyHashHash)
                        $UserInfo = null;
                }
            }

            return $UserInfo;
        }

        // -------------------------------------------------------------------------

        private static function extractSaltPart( $aPassword )
        {
            global $gItoa64;

            $Count = strpos($gItoa64, $aPassword[3]);
            $Salt = substr($aPassword, 4, 8);

            return $Count.':'.$Salt;
        }

        // -------------------------------------------------------------------------

        public function getUserInfoByName( $aUserName )
        {
            $Connector = $this->getConnector();
            $UserQuery = $Connector->prepare('SELECT UserID, `'.VANILLA_TABLE_PREFIX.'User`.Name, Password, Banned, `'.VANILLA_TABLE_PREFIX.'Role`.RoleID '.
                                          'FROM `'.VANILLA_TABLE_PREFIX.'User` '.
                                          'LEFT JOIN `'.VANILLA_TABLE_PREFIX.'UserRole` USING(UserID) '.
                                          'LEFT JOIN `'.VANILLA_TABLE_PREFIX.'Role` USING(RoleID) '.
                                          'WHERE LOWER(`'.VANILLA_TABLE_PREFIX.'User`.Name) = :Login');

            $UserQuery->BindValue( ':Login', strtolower($aUserName), PDO::PARAM_STR );

            $Roles = array();
            $UserData = null;

            $UserQuery->loop(function($User) use (&$UserData, &$Roles)
            {
                $UserData = $User;
                array_push($Roles, $User['RoleID']);
            });

            if ($UserData == null)
                return null;

            $UserData['Roles'] = $Roles;

            return $this->generateUserInfo($UserData);
        }

        // -------------------------------------------------------------------------

        public function getUserInfoById( $aUserId )
        {
            $Connector = $this->getConnector();
            $UserQuery = $Connector->prepare('SELECT UserID, `'.VANILLA_TABLE_PREFIX.'User`.Name, Password, Banned, `'.VANILLA_TABLE_PREFIX.'Role`.RoleID '.
                                          'FROM `'.VANILLA_TABLE_PREFIX.'User` '.
                                          'LEFT JOIN `'.VANILLA_TABLE_PREFIX.'UserRole` USING(UserID) '.
                                          'LEFT JOIN `'.VANILLA_TABLE_PREFIX.'Role` USING(RoleID) '.
                                          'WHERE `'.VANILLA_TABLE_PREFIX.'User`.UserID = :UserId');

            $UserQuery->BindValue( ':UserId', $aUserId, PDO::PARAM_INT );

            $Roles = array();
            $UserData = null;

            $UserQuery->loop(function($User) use (&$UserData, &$Roles)
            {
                $UserData = $User;
                array_push($Roles, $User['RoleID']);

            });

            if ($UserData == null)
                return null;

            $UserData['Roles'] = $Roles;

            return $this->generateUserInfo($UserData);
        }

        // -------------------------------------------------------------------------

        public function getMethodFromPass( $aPassword )
        {
            return self::$HashMethod;
        }

        // -------------------------------------------------------------------------

        public function hash( $aPassword, $aSalt, $aMethod )
        {
            global $gItoa64;

            $Parts   = explode(':',$aSalt);
            $CountB2 = intval($Parts[0],10);
            $Count   = 1 << $CountB2;
            $Salt    = $Parts[1];

            $Hash = md5($Salt.$aPassword, true);

            do {
                $Hash = md5($Hash.$aPassword, true);
            } while (--$Count);

            return '$P$'.$gItoa64[$CountB2].$Salt.encode64($Hash,16);
        }

        // -------------------------------------------------------------------------

        public function post($aSubject, $aMessage)
        {
            $Connector = $this->getConnector();
            $Timestamp = time();

            try
            {
                // Create post

                $PostQuery = $Connector->prepare('INSERT INTO `'.VANILLA_TABLE_PREFIX.'Discussion` '.
                                              '(ForeignID, CategoryID, InsertUserID, Format, Name, Body, DateInserted) VALUES '.
                                              '("stub", :ForumId, :UserId, "Html", :Subject, :Text, FROM_UNIXTIME(:Now))');

                $PostQuery->BindValue( ':ForumId', VANILLA_POSTTO, PDO::PARAM_INT );
                $PostQuery->BindValue( ':UserId', VANILLA_POSTAS, PDO::PARAM_INT );
                $PostQuery->BindValue( ':Now', $Timestamp, PDO::PARAM_INT );

                $PostQuery->BindValue( ':Subject', $aSubject, PDO::PARAM_STR );
                $PostQuery->BindValue( ':Text', $aMessage, PDO::PARAM_STR );

                $PostQuery->execute(true);
            }
            catch (PDOException $Exception)
            {
                $Connector->rollBack();
                throw $Exception;
            }
        }
    }
?>
