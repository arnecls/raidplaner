<?php
    include_once_exists(dirname(__FILE__).'/../../config/config.wbb.php');

    array_push(PluginRegistry::$Classes, 'WbbBinding');

    class WbbBinding extends Binding
    {
        private static $BindingName = 'wbb';

        public static $HashMethodBF = 'wbb_bf';

        // -------------------------------------------------------------------------

        public function getName()
        {
            return self::$BindingName;
        }

        // -------------------------------------------------------------------------

        public function getConfig()
        {
            $Config = new BindingConfig();

            $Config->Database         = defined('WBB_DATABASE') ? WBB_DATABASE : RP_DATABASE;
            $Config->User             = defined('WBB_USER') ? WBB_USER : RP_USER;
            $Config->Password         = defined('WBB_PASS') ? WBB_PASS : RP_PASS;
            $Config->Prefix           = defined('WBB_TABLE_PREFIX') ? WBB_TABLE_PREFIX : '1';
            $Config->Version          = defined('WBB_VERSION') ? WBB_VERSION : 40000;
            $Config->AutoLoginEnabled = defined('WBB_AUTOLOGIN') ? WBB_AUTOLOGIN : false;
            $Config->CookieData       = defined('WBB_COOKIE_PREFIX') ? WBB_COOKIE_PREFIX : 'wcf_';
            $Config->PostTo           = defined('WBB_POSTTO') ? WBB_POSTTO : '';
            $Config->PostAs           = defined('WBB_POSTAS') ? WBB_POSTAS : '';
            $Config->Members          = defined('WBB_MEMBER_GROUPS') ? explode(',', WBB_MEMBER_GROUPS ) : array();
            $Config->Privileged       = defined('WBB_PRIVILEGED_GROUPS') ? explode(',', WBB_PRIVILEGED_GROUPS ) : array();
            $Config->Raidleads        = defined('WBB_RAIDLEAD_GROUPS') ? explode(',', WBB_RAIDLEAD_GROUPS ) : array();
            $Config->Admins           = defined('WBB_ADMIN_GROUPS') ? explode(',', WBB_ADMIN_GROUPS ) : array();
            $Config->HasCookieConfig  = true;
            $Config->HasGroupConfig   = true;
            $Config->HasForumConfig   = true;

            return $Config;
        }

        // -------------------------------------------------------------------------

        public function getExternalConfig($aRelativePath)
        {
            $Out = Out::getInstance();

            $ConfigPath = $_SERVER['DOCUMENT_ROOT'].'/'.$aRelativePath.'/wcf/config.inc.php';
            if (!file_exists($ConfigPath))
            {
                $Out->pushError($ConfigPath.' '.L('NotExisting').'.');
                return null;
            }

            @include_once($ConfigPath);

            // Read cookie

            $Connector = new Connector(SQL_HOST, $dbName, $dbUser, $dbPassword, false);

            $OptionQuery = $Connector->prepare( 'SELECT optionValue FROM `wcf'.WCF_N.'_option` '.
                'WHERE optionName = "cookie_prefix" AND categoryName = "general.system.cookie" LIMIT 1' );

            $OptionData = $OptionQuery->fetchFirst();

            // Build result

            return array(
                'database'  => $dbName,
                'user'      => $dbUser,
                'password'  => $dbPassword,
                'prefix'    => WCF_N,
                'cookie'    => $OptionData['optionValue'],
                'version'   => 40000
            );
        }

        // -------------------------------------------------------------------------

        public function writeConfig($aEnable, $aConfig)
        {
            $Config = fopen( dirname(__FILE__).'/../../config/config.wbb.php', 'w+' );

            fwrite( $Config, "<?php\n");
            fwrite( $Config, "\tdefine('WBB_BINDING', ".(($aEnable) ? "true" : "false").");\n");

            if ( $aEnable )
            {
                fwrite( $Config, "\tdefine('WBB_DATABASE', '".$aConfig->Database."');\n");
                fwrite( $Config, "\tdefine('WBB_USER', '".$aConfig->User."');\n");
                fwrite( $Config, "\tdefine('WBB_PASS', '".$aConfig->Password."');\n");
                fwrite( $Config, "\tdefine('WBB_TABLE_PREFIX', '".$aConfig->Prefix."');\n");
                fwrite( $Config, "\tdefine('WBB_AUTOLOGIN', ".(($aConfig->AutoLoginEnabled) ? "true" : "false").");\n");
                fwrite( $Config, "\tdefine('WBB_COOKIE_PREFIX', '".$aConfig->CookieData."');\n");
                fwrite( $Config, "\tdefine('WBB_POSTTO', ".$aConfig->PostTo.");\n");
                fwrite( $Config, "\tdefine('WBB_POSTAS', ".$aConfig->PostAs.");\n");

                fwrite( $Config, "\tdefine('WBB_MEMBER_GROUPS', '".implode( ",", $aConfig->Members )."');\n");
                fwrite( $Config, "\tdefine('WBB_PRIVILEGED_GROUPS', '".implode( ",", $aConfig->Privileged )."');\n");
                fwrite( $Config, "\tdefine('WBB_RAIDLEAD_GROUPS', '".implode( ",", $aConfig->Raidleads )."');\n");
                fwrite( $Config, "\tdefine('WBB_ADMIN_GROUPS', '".implode( ",", $aConfig->Admins )."');\n");
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
                $GroupQuery = $Connector->prepare( 'SELECT groupID, groupName, languageItemValue FROM `wcf'.$aPrefix.'_user_group` '.
                    'LEFT JOIN `wcf'.$aPrefix.'_language_item` ON groupName = languageItem '.
                    'ORDER BY groupName' );

                $Groups = array();
                $GroupQuery->loop(function($Group) use (&$Groups)

                {
                    array_push( $Groups, array(
                        'id'   => $Group['groupID'],
                        'name' => ($Group['languageItemValue'] == NULL) ? $Group['groupName'] : $Group['languageItemValue'])
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
                $ForumQuery = $Connector->prepare( 'SELECT boardID, title FROM `wbb'.$aPrefix.'_board` '.
                    'WHERE boardType = 0 ORDER BY title' );

                $ForumQuery->loop(function($Forum) use (&$Forums)
                {
                    array_push( $Forums, array(
                        'id'   => $Forum['boardID'],
                        'name' => $Forum['title'])
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
                $UserQuery = $Connector->prepare('SELECT userID, username FROM `wcf'.$aPrefix.'_user` '.
                    'ORDER BY username' );

                $UserQuery->loop(function($User) use (&$Users)
                {
                    array_push( $Users, array(
                        'id'   => $User['userID'],
                        'name' => $User['username'])
                    );
                }, $aThrow);

                return $Users;
            }

            return null;
        }

        // -------------------------------------------------------------------------

        private function getGroupForUser( $aUserData )
        {
            if ($aUserData['banned'] != 0)
                return 'none'; // ### return, banned ###

            $Config = $this->getConfig();
            $AssignedGroup = ENUM_GROUP_NONE;

            foreach( $aUserData['Groups'] as $Group )
            {
                $AssignedGroup = $Config->mapGroup($Group, $AssignedGroup);
            }

            return GetGroupName($AssignedGroup);
        }

        // -------------------------------------------------------------------------

        private function generateUserInfo( $aUserData )
        {
            $Info = new UserInfo();
            $Info->UserId      = $aUserData['userID'];
            $Info->UserName    = $aUserData['username'];
            $Info->Password    = $aUserData['password'];
            $Info->Salt        = $this->extractSaltPart($aUserData['password']);
            $Info->SessionSalt = null;
            $Info->Group       = $this->getGroupForUser($aUserData);
            $Info->BindingName = $this->getName();
            $Info->PassBinding = $this->getName();

            return $Info;
        }

        // -------------------------------------------------------------------------

        public function getExternalLoginData()
        {
            if (!defined('WBB_AUTOLOGIN') || !WBB_AUTOLOGIN)
                return null;

            $UserInfo = null;

            // Fetch user info if session cookie is set

            $CookieName = WBB_COOKIE_PREFIX.'cookieHash';

            if (isset($_COOKIE[$CookieName]))
            {
                $Connector = $this->getConnector();
                $UserQuery = $Connector->prepare('SELECT userID '.
                    'FROM `wcf'.WBB_TABLE_PREFIX.'_session` '.
                    'WHERE sessionID = :sid LIMIT 1');

                $UserQuery->BindValue( ':sid', $_COOKIE[$CookieName], PDO::PARAM_STR );
                $UserData = $UserQuery->fetchFirst();

                if ( $UserData != null )
                {
                    // Get user info by external id

                    $UserId = $UserData['userID'];
                    $UserInfo = $this->getUserInfoById($UserId);
                }
            }

            return $UserInfo;
        }

        // -------------------------------------------------------------------------

        public function getUserInfoByName( $aUserName )
        {
            $Connector = $this->getConnector();
            $UserQuery = $Connector->prepare('SELECT userID, groupID, username, password, banned '.
                'FROM `wcf'.WBB_TABLE_PREFIX.'_user` '.
                'LEFT JOIN `wcf'.WBB_TABLE_PREFIX.'_user_to_group` USING(userID) '.
                'WHERE LOWER(username) = :Login');

            $UserQuery->BindValue( ':Login', strtolower($aUserName), PDO::PARAM_STR );

            $UserData = null;
            $Groups = array();

            $UserQuery->loop(function($Data) use (&$UserData, &$Groups)
            {
                $UserData = $Data;
                array_push($Groups, $UserData['groupID']);
            });

            if ($UserData == null)
                return null; // ### return, no users ###

            $UserData['Groups'] = $Groups;
            return $this->generateUserInfo($UserData);
        }

        // -------------------------------------------------------------------------

        public function getUserInfoById( $aUserId )
        {
            $Connector = $this->getConnector();
            $UserQuery = $Connector->prepare('SELECT userID, groupID, username, password, banned '.
                                             'FROM `wcf'.WBB_TABLE_PREFIX.'_user` '.
                                             'LEFT JOIN `wcf'.WBB_TABLE_PREFIX.'_user_to_group` USING(userID) '.
                                             'WHERE userID = :UserId');

            $UserQuery->BindValue( ':UserId', $aUserId, PDO::PARAM_INT );
            $UserData = null;
            $Groups = array();

            $UserQuery->loop(function($Data) use (&$UserData, &$Groups)
            {
                $UserData = $Data;
                array_push($Groups, $UserData['groupID']);
            });

            if ($UserData == null)
                return null; // ### return, no users ###

            $UserData['Groups'] = $Groups;
            return $this->generateUserInfo($UserData);
        }

        // -------------------------------------------------------------------------

        private function extractSaltPart( $aPassword )
        {
            return substr($aPassword, 0, 7+22);
        }

        // -------------------------------------------------------------------------

        public function getMethodFromPass( $aPassword )
        {
            if ( strpos($aPassword, '$2y$') === 0 )
                return self::$HashMethodBF;

            if ( strpos($aPassword, '$2a$') === 0 )
                return self::$HashMethodBF;

            return 'unsupported';
        }

        // -------------------------------------------------------------------------

        public function hash( $aPassword, $aSalt, $aMethod )
        {
            return crypt(crypt($aPassword,$aSalt),$aSalt);
        }

        // -------------------------------------------------------------------------

        public function post($aSubject, $aMessage)
        {
            $Connector = $this->getConnector();
            $Timestamp = time();

            // Fetch user

            try
            {
                do
                {
                    $Connector->beginTransaction();

                    $UserQuery = $Connector->prepare('SELECT username FROM `wcf'.WBB_TABLE_PREFIX.'_user` '.
                        'WHERE userID=:UserId LIMIT 1');

                    $UserQuery->BindValue( ':UserId', WBB_POSTAS, PDO::PARAM_INT );
                    $UserData = $UserQuery->fetchFirst();

                    // Create topic

                    $ThreadQuery = $Connector->prepare('INSERT INTO `wbb'.WBB_TABLE_PREFIX.'_thread` '.
                        '(boardId, userId, topic, time, username, lastPostTime, lastPoster, lastPosterID) VALUES '.
                        '(:BoardId, :UserId, :Subject, :Now, :Username, :Now, :Username, :UserId)');

                    $ThreadQuery->BindValue( ':BoardId', WBB_POSTTO, PDO::PARAM_INT );
                    $ThreadQuery->BindValue( ':UserId', WBB_POSTAS, PDO::PARAM_INT );
                    $ThreadQuery->BindValue( ':Subject', xmlToUTF8($aSubject), PDO::PARAM_STR );
                    $ThreadQuery->BindValue( ':Now', $Timestamp, PDO::PARAM_INT );
                    $ThreadQuery->BindValue( ':Username', $UserData['username'], PDO::PARAM_STR );

                    $ThreadQuery->execute(true);
                    $ThreadId = $Connector->lastInsertId();

                    // Create post

                    $FormattedMessage = HTMLToBBCode($aMessage);

                    $PostQuery = $Connector->prepare('INSERT INTO `wbb'.WBB_TABLE_PREFIX.'_post` '.
                        '(threadId, time, username, userId, message) VALUES '.
                        '(:ThreadId, :Now, :Username, :UserId, :Text)');

                    $PostQuery->BindValue( ':ThreadId', $ThreadId, PDO::PARAM_INT );
                    $PostQuery->BindValue( ':Now', $Timestamp, PDO::PARAM_INT );
                    $PostQuery->BindValue( ':Username', $UserData['username'], PDO::PARAM_STR );
                    $PostQuery->BindValue( ':UserId', WBB_POSTAS, PDO::PARAM_INT );
                    $PostQuery->BindValue( ':Text', $FormattedMessage, PDO::PARAM_STR );

                    $PostQuery->execute(true);
                    $PostId = $Connector->lastInsertId();

                    // Finish topic

                    $TopicFinishQuery = $Connector->prepare('UPDATE `wbb'.WBB_TABLE_PREFIX.'_thread` '.
                        'SET firstPostID = :PostId, lastPostID = :PostId '.
                        'WHERE threadID = :ThreadId LIMIT 1');

                    $TopicFinishQuery->BindValue( ':PostId', $PostId, PDO::PARAM_INT );
                    $TopicFinishQuery->BindValue( ':ThreadId', $ThreadId, PDO::PARAM_INT );

                    $TopicFinishQuery->execute(true);

                    // Update board

                    $BoardQuery = $Connector->prepare('UPDATE `wbb'.WBB_TABLE_PREFIX.'_board_last_post` '.
                        'SET threadID = :ThreadId '.
                        'WHERE boardId = :BoardId LIMIT 1');

                    $BoardQuery->BindValue( ':ThreadId', $ThreadId, PDO::PARAM_INT );
                    $BoardQuery->BindValue( ':BoardId', WBB_POSTTO, PDO::PARAM_INT );

                    $BoardQuery->execute(true);
                }
                while (!$Connector->commit());
            }
            catch (PDOException $Exception)
            {
                $Connector->rollBack();
                throw $Exception;
            }
        }
    }
?>
