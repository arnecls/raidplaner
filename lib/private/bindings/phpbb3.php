<?php
    include_once_exists(dirname(__FILE__).'/../../config/config.phpbb3.php');

    array_push(PluginRegistry::$Classes, 'PHPBB3Binding');

    class PHPBB3Binding extends Binding
    {
        private static $BindingName = 'phpbb3';

        public static $HashMethod_md5r = 'phpbb3_md5r';
        public static $HashMethod_bf   = 'phpbb3_bf';
        public static $HashMethod_md5  = 'phpbb3_md5';

        // -------------------------------------------------------------------------

        public function getName()
        {
            return self::$BindingName;
        }

        // -------------------------------------------------------------------------

        public function getConfig()
        {
            $Config = new BindingConfig();

            $Config->Database         = defined('PHPBB3_DATABASE') ? PHPBB3_DATABASE : RP_DATABASE;
            $Config->User             = defined('PHPBB3_USER') ? PHPBB3_USER : RP_USER;
            $Config->Password         = defined('PHPBB3_PASS') ? PHPBB3_PASS : RP_PASS;
            $Config->Prefix           = defined('PHPBB3_TABLE_PREFIX') ? PHPBB3_TABLE_PREFIX : 'phpbb_';
            $Config->Version          = defined('PHPBB3_VERSION') ? PHPBB3_VERSION : 30000;
            $Config->AutoLoginEnabled = defined('PHPBB3_AUTOLOGIN') ? PHPBB3_AUTOLOGIN : false;
            $Config->PostTo           = defined('PHPBB3_POSTTO') ? PHPBB3_POSTTO : '';
            $Config->PostAs           = defined('PHPBB3_POSTAS') ? PHPBB3_POSTAS : '';
            $Config->Members          = defined('PHPBB3_MEMBER_GROUPS') ? explode(',', PHPBB3_MEMBER_GROUPS ) : array();
            $Config->Privileged       = defined('PHPBB3_PRIVILEGED_GROUPS') ? explode(',', PHPBB3_PRIVILEGED_GROUPS ) : array();
            $Config->Raidleads        = defined('PHPBB3_RAIDLEAD_GROUPS') ? explode(',', PHPBB3_RAIDLEAD_GROUPS ) : array();
            $Config->Admins           = defined('PHPBB3_ADMIN_GROUPS') ? explode(',', PHPBB3_ADMIN_GROUPS ) : array();
            $Config->HasGroupConfig   = true;
            $Config->HasForumConfig   = true;

            return $Config;
        }

        // -------------------------------------------------------------------------

        public function getExternalConfig($aRelativePath)
        {
            $Out = Out::getInstance();

            $ConfigPath = $_SERVER['DOCUMENT_ROOT'].'/'.$aRelativePath.'/config.php';
            if (!file_exists($ConfigPath))
            {
                $Out->pushError($ConfigPath.' '.L('NotExisting').'.');
                return null;
            }

            @include_once($ConfigPath);

            if (!defined('PHPBB_INSTALLED'))
            {
                $Out->pushError(L('NoValidConfig'));
                return null;
            }

            $Version = 30000;
            $Connector = new Connector(SQL_HOST, $dbname, $dbuser, $dbpasswd, false);
            if ($Connector != null)
            {
                $VersionQuery = $Connector->prepare( 'SELECT config_value FROM `'.$table_prefix.'config` WHERE config_name="version" LIMIT 1' );
                $VersionData  = $VersionQuery->fetchFirst();
                $VersionParts = explode('.', $VersionData['config_value']);

                $Version = intval($VersionParts[0]) * 10000 + intval($VersionParts[1]) * 100 + intval($VersionParts[2]);
            }

            return array(
                'database'  => $dbname,
                'user'      => $dbuser,
                'password'  => $dbpasswd,
                'prefix'    => $table_prefix,
                'cookie'    => null,
                'version'   => $Version
            );
        }

        // -------------------------------------------------------------------------

        public function writeConfig($aEnable, $aConfig)
        {
            $Config = fopen( dirname(__FILE__).'/../../config/config.phpbb3.php', 'w+' );

            fwrite( $Config, "<?php\n");
            fwrite( $Config, "\tdefine('PHPBB3_BINDING', ".(($aEnable) ? "true" : "false").");\n");

            if ( $aEnable )
            {
                fwrite( $Config, "\tdefine('PHPBB3_DATABASE', '".$aConfig->Database."');\n");
                fwrite( $Config, "\tdefine('PHPBB3_USER', '".$aConfig->User."');\n");
                fwrite( $Config, "\tdefine('PHPBB3_PASS', '".$aConfig->Password."');\n");
                fwrite( $Config, "\tdefine('PHPBB3_TABLE_PREFIX', '".$aConfig->Prefix."');\n");
                fwrite( $Config, "\tdefine('PHPBB3_AUTOLOGIN', ".(($aConfig->AutoLoginEnabled) ? "true" : "false").");\n");
                fwrite( $Config, "\tdefine('PHPBB3_POSTTO', ".$aConfig->PostTo.");\n");
                fwrite( $Config, "\tdefine('PHPBB3_POSTAS', ".$aConfig->PostAs.");\n");

                fwrite( $Config, "\tdefine('PHPBB3_MEMBER_GROUPS', '".implode( ",", $aConfig->Members )."');\n");
                fwrite( $Config, "\tdefine('PHPBB3_PRIVILEGED_GROUPS', '".implode( ",", $aConfig->Privileged )."');\n");
                fwrite( $Config, "\tdefine('PHPBB3_RAIDLEAD_GROUPS', '".implode( ",", $aConfig->Raidleads )."');\n");
                fwrite( $Config, "\tdefine('PHPBB3_ADMIN_GROUPS', '".implode( ",", $aConfig->Admins )."');\n");

                fwrite( $Config, "\tdefine('PHPBB3_VERSION', ".$aConfig->Version.");\n");
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
                $Groups = array();
                $GroupQuery = $Connector->prepare( 'SELECT group_id, group_name FROM `'.$aPrefix.'groups` ORDER BY group_name' );

                $GroupQuery->loop(function($Group) use (&$Groups)
                {
                    array_push( $Groups, array(
                        'id'   => $Group['group_id'],
                        'name' => $Group['group_name'])
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
                $ForumQuery = $Connector->prepare( 'SELECT forum_id, forum_name FROM `'.$aPrefix.'forums` '.
                                                   'WHERE forum_type = 1 ORDER BY forum_name' );

                $ForumQuery->loop(function($Forum) use (&$Forums)
                {
                    array_push( $Forums, array(
                        'id'   => $Forum['forum_id'],
                        'name' => $Forum['forum_name'])
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
                $UserQuery = $Connector->prepare('SELECT user_id, username FROM `'.$aPrefix.'users` '.
                                                 'LEFT JOIN `'.$aPrefix.'groups` USING(group_id) '.
                                                 'WHERE group_name != "BOTS" '.
                                                 'ORDER BY username' );

                $UserQuery->loop(function($User) use (&$Users)
                {
                    array_push( $Users, array(
                        'id'   => $User['user_id'],
                        'name' => $User['username'])
                    );
                }, $aThrow);

                return $Users;
            }

            return null;
        }

        // -------------------------------------------------------------------------

        private function getGroupForUser( $aUserId )
        {
            $Config = $this->getConfig();
            $AssignedGroup = ENUM_GROUP_NONE;

            $Connector = $this->getConnector();
            $GroupQuery = $Connector->prepare('SELECT user_type, `'.$Config->Prefix.'user_group`.group_id, ban_start, ban_end '.
                                           'FROM `'.$Config->Prefix.'users` '.
                                           'LEFT JOIN `'.$Config->Prefix.'user_group` USING(user_id) '.
                                           'LEFT JOIN `'.$Config->Prefix.'banlist` ON user_id = ban_userid '.
                                           'WHERE user_id = :UserId');

            $GroupQuery->bindValue(':UserId', $aUserId, PDO::PARAM_INT);

            $GroupQuery->loop(function($Group) use (&$AssignedGroup, $Config)
            {
                if ( ($Group['user_type'] == 1) ||
                     ($Group['user_type'] == 2) )
                {
                    // 1 equals 'inactive'
                    // 2 equals 'ignore'
                    $AssignedGroup = ENUM_GROUP_NONE;
                    return false; // ### return, disabled ###
                }

                if ($Group['ban_start'] > 0)
                {
                    $CurrentTime = time();
                    if ( ($Group['ban_start'] < $CurrentTime) &&
                         (($Group['ban_end'] == 0) || ($Group['ban_end'] > $CurrentTime)) )
                    {
                        $AssignedGroup = ENUM_GROUP_NONE;
                        return false; // ### return, banned ###
                    }
                }

                $AssignedGroup = $Config->mapGroup($Group['group_id'], $AssignedGroup);
            });

            return GetGroupName($AssignedGroup);
        }

        // -------------------------------------------------------------------------

        private function generateUserInfo( $aUserData )
        {
            $Info = new UserInfo();
            $Info->UserId      = $aUserData['user_id'];
            $Info->UserName    = $aUserData['username_clean'];
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
            if (!defined('PHPBB3_AUTOLOGIN') || !PHPBB3_AUTOLOGIN)
                return null;

            $Connector = $this->getConnector();
            $UserInfo = null;

            // Fetch cookie name

            $CookieQuery = $Connector->prepare('SELECT config_value '.
                'FROM `'.PHPBB3_TABLE_PREFIX.'config` '.
                'WHERE config_name = "cookie_name" LIMIT 1');

            $ConfigData = $CookieQuery->fetchFirst();

            if ( $ConfigData != null )
            {
                $CookieName = $ConfigData['config_value'].'_sid';

                // Fetch user info if seesion cookie is set

                if (isset($_COOKIE[$CookieName]))
                {
                    $UserQuery = $Connector->prepare('SELECT session_user_id '.
                        'FROM `'.PHPBB3_TABLE_PREFIX.'sessions` '.
                        'WHERE session_id = :sid LIMIT 1');

                    $UserQuery->BindValue( ':sid', $_COOKIE[$CookieName], PDO::PARAM_STR );
                    $UserData = $UserQuery->fetchFirst();

                    if ( $UserData != null )
                    {
                        // Get user info by external id

                        $UserId = intval($UserData['session_user_id']);
                        $UserInfo = ($UserId == 1) ? null : $this->getUserInfoById($UserId);
                    }
                }
            }

            return $UserInfo;
        }

        // -------------------------------------------------------------------------

        public function getUserInfoByName( $aUserName )
        {
            $Connector = $this->getConnector();
            $UserQuery = $Connector->prepare('SELECT user_id, username_clean, user_password '.
                                          'FROM `'.PHPBB3_TABLE_PREFIX.'users` '.
                                          'WHERE LOWER(username_clean) = :Login LIMIT 1');

            $UserQuery->BindValue( ':Login', strtolower($aUserName), PDO::PARAM_STR );
            $UserData = $UserQuery->fetchFirst();

            return ($UserData != null)
                ? $this->generateUserInfo($UserData)
                : null;
        }

        // -------------------------------------------------------------------------

        public function getUserInfoById( $aUserId )
        {
            $Connector = $this->getConnector();
            $UserQuery = $Connector->prepare('SELECT user_id, username_clean, user_password '.
                                          'FROM `'.PHPBB3_TABLE_PREFIX.'users` '.
                                          'WHERE user_id = :UserId LIMIT 1');

            $UserQuery->BindValue( ':UserId', $aUserId, PDO::PARAM_INT );
            $UserData = $UserQuery->fetchFirst();

            return ($UserData != null)
                ? $this->generateUserInfo($UserData)
                : null;
        }

        // -------------------------------------------------------------------------

        private function extractSaltPart( $aPassword )
        {
            global $gItoa64;

            switch ( $this->getMethodFromPass($aPassword) )
            {
            case self::$HashMethod_bf:
                return substr($aPassword, 0, 7+22);

            case self::$HashMethod_md5r:
                $Count = strpos($gItoa64, $aPassword[3]);
                $Salt = substr($aPassword, 4, 8);

                return $Count.':'.$Salt;

            default:
            case self::$HashMethod_md5:
                return '';
            }
        }

        // -------------------------------------------------------------------------

        public function getMethodFromPass( $aPassword )
        {
            if ( strpos($aPassword, '$2y$') === 0 )
                return self::$HashMethod_bf;

            if ( strpos($aPassword, '$2a$') === 0 )
                return self::$HashMethod_bf;

            if ( strpos($aPassword, '$H$') === 0 )
                return self::$HashMethod_md5r;

            return self::$HashMethod_md5;
        }

        // -------------------------------------------------------------------------

        public function hash( $aPassword, $aSalt, $aMethod )
        {
            global $gItoa64;

            switch ($aMethod)
            {
            case self::$HashMethod_bf:
                return crypt($aPassword,$aSalt);

            default:
            case self::$HashMethod_md5:
                return md5($aPassword);

            case self::$HashMethod_md5r:
                $Parts   = explode(':',$aSalt);
                $CountB2 = intval($Parts[0],10);
                $Count   = 1 << $CountB2;
                $Salt    = $Parts[1];

                $Hash = md5($Salt.$aPassword, true);

                do {
                    $Hash = md5($Hash.$aPassword, true);
                } while (--$Count);

                return '$H$'.$gItoa64[$CountB2].$Salt.encode64($Hash,16);
            }
        }

        // -------------------------------------------------------------------------

        public function post( $aSubject, $aMessage )
        {
            $Connector = $this->getConnector();
            $Timestamp = time();

            // Fetch user

            try
            {
                do
                {
                    $Connector->beginTransaction();

                    $UserQuery = $Connector->prepare('SELECT username, user_colour FROM `'.PHPBB3_TABLE_PREFIX.'users` WHERE user_id=:UserId LIMIT 1');
                    $UserQuery->BindValue( ':UserId', PHPBB3_POSTAS, PDO::PARAM_INT );

                    $UserData = $UserQuery->fetchFirst();

                    // Create topic

                    if (!defined("PHPBB3_VERSION") || PHPBB3_VERSION < 30100)
                    {
                        $TopicQuery = $Connector->prepare('INSERT INTO `'.PHPBB3_TABLE_PREFIX.'topics` '.
                                                       '(forum_id, topic_poster, topic_title, topic_last_post_subject, topic_time, topic_first_poster_name, '.
                                                       'topic_first_poster_colour, topic_last_poster_name, topic_last_poster_colour, topic_last_post_time) VALUES '.
                                                       '(:ForumId, :UserId, :Subject, :Subject, :Now, :Username, :Color, :Username, :Color, :Now)');
                    }
                    else
                    {
                         $TopicQuery = $Connector->prepare('INSERT INTO `'.PHPBB3_TABLE_PREFIX.'topics` '.
                                                       '(forum_id, topic_poster, topic_title, topic_last_post_subject, topic_time, topic_first_poster_name, '.
                                                       'topic_first_poster_colour, topic_last_poster_name, topic_last_poster_id, topic_last_poster_colour, '.
                                                       'topic_last_post_time, topic_visibility, topic_posts_approved) VALUES '.
                                                       '(:ForumId, :UserId, :Subject, :Subject, :Now, :Username, :Color, :Username, :UserId, :Color, :Now, 1, 1)');

                    }

                    $TopicQuery->BindValue( ':ForumId', PHPBB3_POSTTO, PDO::PARAM_INT );
                    $TopicQuery->BindValue( ':UserId', PHPBB3_POSTAS, PDO::PARAM_INT );
                    $TopicQuery->BindValue( ':Now', $Timestamp, PDO::PARAM_INT );
                    $TopicQuery->BindValue( ':Username', $UserData['username'], PDO::PARAM_STR );
                    $TopicQuery->BindValue( ':Color', $UserData['user_colour'], PDO::PARAM_STR );
                    $TopicQuery->BindValue( ':Subject', $aSubject, PDO::PARAM_STR );

                    $TopicQuery->execute(true);
                    $TopicId = $Connector->lastInsertId();

                    // Create post

                    if (!defined("PHPBB3_VERSION") || PHPBB3_VERSION < 30100)
                    {
                        $PostQuery = $Connector->prepare('INSERT INTO `'.PHPBB3_TABLE_PREFIX.'posts` '.
                                                      '(forum_id, topic_id, post_time, post_username, poster_id, post_subject, post_text, post_checksum) VALUES '.
                                                      '(:ForumId, :TopicId, :Now, :Username, :UserId, :Subject, :Text, :TextMD5)');
                    }
                    else
                    {
                        $PostQuery = $Connector->prepare('INSERT INTO `'.PHPBB3_TABLE_PREFIX.'posts` '.
                                                      '(forum_id, topic_id, post_time, post_username, poster_id, post_subject, post_text, post_checksum, post_visibility) VALUES '.
                                                      '(:ForumId, :TopicId, :Now, :Username, :UserId, :Subject, :Text, :TextMD5, 1)');
                    }

                    $PostQuery->BindValue( ':ForumId', PHPBB3_POSTTO, PDO::PARAM_INT );
                    $PostQuery->BindValue( ':TopicId', $TopicId, PDO::PARAM_INT );
                    $PostQuery->BindValue( ':UserId', PHPBB3_POSTAS, PDO::PARAM_INT );
                    $PostQuery->BindValue( ':Now', $Timestamp, PDO::PARAM_INT );
                    $PostQuery->BindValue( ':Username', $UserData['username'], PDO::PARAM_STR );

                    $PostQuery->BindValue( ':Subject', $aSubject, PDO::PARAM_STR );
                    $PostQuery->BindValue( ':Text', $aMessage, PDO::PARAM_STR );
                    $PostQuery->BindValue( ':TextMD5', md5($aMessage), PDO::PARAM_STR );

                    $PostQuery->execute(true);
                    $PostId = $Connector->lastInsertId();

                    // Finish topic

                    $TopicFinishQuery = $Connector->prepare('UPDATE `'.PHPBB3_TABLE_PREFIX.'topics` '.
                                                         'SET topic_first_post_id = :PostId, topic_last_post_id = :PostId '.
                                                         'WHERE topic_id = :TopicId LIMIT 1');

                    $TopicFinishQuery->BindValue( ':TopicId', $TopicId, PDO::PARAM_INT );
                    $TopicFinishQuery->BindValue( ':PostId', $PostId, PDO::PARAM_INT );

                    $TopicFinishQuery->execute(true);

                    // Topic posted

                    $TopicPostedQuery = $Connector->prepare('INSERT INTO `'.PHPBB3_TABLE_PREFIX.'topics_posted` '.
                                                  '(user_id, topic_id, topic_posted) VALUES '.
                                                  '(:UserId, :TopicId, 1)');

                    $TopicPostedQuery->BindValue( ':TopicId', $TopicId, PDO::PARAM_INT );
                    $TopicPostedQuery->BindValue( ':UserId', PHPBB3_POSTAS, PDO::PARAM_INT );

                    $TopicPostedQuery->execute(true);

                    // Update forum

                    $VersionBasedQuery = " ";

                    if (!defined("PHPBB3_VERSION") || PHPBB3_VERSION < 30100)
                    {
                        $VersionBasedQuery .= ', forum_posts = forum_posts+1, forum_topics = forum_topics+1, forum_topics_real = forum_topics_real+1 ';
                    }

                    $ForumUpdateQuery = $Connector->prepare('UPDATE `'.PHPBB3_TABLE_PREFIX.'forums` '.
                                                            'SET forum_last_post_subject = :Subject, forum_last_post_time = :Now, '.
                                                                'forum_last_poster_name = :Username,  forum_last_poster_colour = :Color, '.
                                                                'forum_last_post_id = :PostId, forum_last_poster_id = :UserId '.
                                                                $VersionBasedQuery.
                                                            'WHERE forum_id = :ForumId LIMIT 1');

                    $ForumUpdateQuery->BindValue( ':ForumId', PHPBB3_POSTTO, PDO::PARAM_INT );
                    $ForumUpdateQuery->BindValue( ':Subject', $aSubject, PDO::PARAM_STR );
                    $ForumUpdateQuery->BindValue( ':UserId', PHPBB3_POSTAS, PDO::PARAM_INT );
                    $ForumUpdateQuery->BindValue( ':Now', $Timestamp, PDO::PARAM_INT );
                    $ForumUpdateQuery->BindValue( ':Username', $UserData['username'], PDO::PARAM_STR );
                    $ForumUpdateQuery->BindValue( ':Color', $UserData['user_colour'], PDO::PARAM_STR );
                    $ForumUpdateQuery->BindValue( ':PostId', $PostId, PDO::PARAM_INT );

                    $ForumUpdateQuery->execute(true);
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
