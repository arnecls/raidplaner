<?php
    include_once_exists(dirname(__FILE__).'/../../config/config.bt4.php');

    array_push(PluginRegistry::$Classes, 'BlueThrustBinding');

    class BlueThrustBinding extends Binding
    {
        private static $BindingName = 'bt4';

        public static $HashMethodBF = 'bt_bf';

        // -------------------------------------------------------------------------

        public function getName()
        {
            return self::$BindingName;
        }

        // -------------------------------------------------------------------------

        public function getConfig()
        {
            $Config = new BindingConfig();

            $Config->Database         = defined('BT4_DATABASE') ? BT4_DATABASE : RP_DATABASE;
            $Config->User             = defined('BT4_USER') ? BT4_USER : RP_USER;
            $Config->Password         = defined('BT4_PASS') ? BT4_PASS : RP_PASS;
            $Config->Prefix           = defined('BT4_TABLE_PREFIX') ? BT4_TABLE_PREFIX : '';
            $Config->Version          = defined('BT4_VERSION') ? BT4_VERSION : 40000;
            $Config->AutoLoginEnabled = defined('BT4_AUTOLOGIN') ? BT4_AUTOLOGIN : false;
            $Config->PostTo           = defined('BT4_POSTTO') ? BT4_POSTTO : '';
            $Config->PostAs           = defined('BT4_POSTAS') ? BT4_POSTAS : '';
            $Config->Members          = defined('BT4_MEMBER_GROUPS') ? explode(',', BT4_MEMBER_GROUPS ) : array();
            $Config->Privileged       = defined('BT4_PRIVILEGED_GROUPS') ? explode(',', BT4_PRIVILEGED_GROUPS ) : array();
            $Config->Raidleads        = defined('BT4_RAIDLEAD_GROUPS') ? explode(',', BT4_RAIDLEAD_GROUPS ) : array();
            $Config->Admins           = defined('BT4_ADMIN_GROUPS') ? explode(',', BT4_ADMIN_GROUPS ) : array();
            $Config->HasCookieConfig  = false;
            $Config->HasGroupConfig   = true;
            $Config->HasForumConfig   = true;

            return $Config;
        }

        // -------------------------------------------------------------------------

        public function getExternalConfig($aRelativePath)
        {
            $Out = Out::getInstance();

            $ConfigPath  = $_SERVER['DOCUMENT_ROOT'].'/'.$aRelativePath.'/_config.php';

            if (!file_exists($ConfigPath))
            {
                $Out->pushError($ConfigPath.' '.L('NotExisting').'.');
                return null;
            }

            @include_once($ConfigPath);

            return array(
                'database'  => $dbname,
                'user'      => $dbuser,
                'password'  => $dbpass,
                'prefix'    => $dbprefix,
                'version'   => 40000
            );
        }

        // -------------------------------------------------------------------------

        public function writeConfig($aEnable, $aConfig)
        {
            $Config = fopen( dirname(__FILE__).'/../../config/config.bt4.php', 'w+' );

            fwrite( $Config, "<?php\n");
            fwrite( $Config, "\tdefine('BT4_BINDING', ".(($aEnable) ? "true" : "false").");\n");

            if ( $aEnable )
            {
                fwrite( $Config, "\tdefine('BT4_DATABASE', '".$aConfig->Database."');\n");
                fwrite( $Config, "\tdefine('BT4_USER', '".$aConfig->User."');\n");
                fwrite( $Config, "\tdefine('BT4_PASS', '".$aConfig->Password."');\n");
                fwrite( $Config, "\tdefine('BT4_TABLE_PREFIX', '".$aConfig->Prefix."');\n");
                fwrite( $Config, "\tdefine('BT4_AUTOLOGIN', ".(($aConfig->AutoLoginEnabled) ? "true" : "false").");\n");
                fwrite( $Config, "\tdefine('BT4_POSTTO', ".$aConfig->PostTo.");\n");
                fwrite( $Config, "\tdefine('BT4_POSTAS', ".$aConfig->PostAs.");\n");

                fwrite( $Config, "\tdefine('BT4_MEMBER_GROUPS', '".implode( ",", $aConfig->Members )."');\n");
                fwrite( $Config, "\tdefine('BT4_PRIVILEGED_GROUPS', '".implode( ",", $aConfig->Privileged )."');\n");
                fwrite( $Config, "\tdefine('BT4_RAIDLEAD_GROUPS', '".implode( ",", $aConfig->Raidleads )."');\n");
                fwrite( $Config, "\tdefine('BT4_ADMIN_GROUPS', '".implode( ",", $aConfig->Admins )."');\n");
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
                $GroupQuery = $Connector->prepare( 'SELECT rank_id, name FROM `'.$aPrefix.'ranks` ORDER BY name' );
                $Groups = array();

                $GroupQuery->loop(function($Group) use (&$Groups)

                {
                    array_push( $Groups, array(
                        'id'   => $Group['rank_id'],
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
            $Connector = new Connector(SQL_HOST, $aDatabase, $aUser, $aPass, $aThrow);

            if ($Connector != null)
            {
                $Forums = array();
                $ForumQuery = $Connector->prepare( 'SELECT forumboard_id, name FROM `'.$aPrefix.'forum_board` '.
                                                   'ORDER BY name' );

                $ForumQuery->loop(function($Forum) use (&$Forums)
                {
                    array_push( $Forums, array(
                        'id'   => $Forum['forumboard_id'],
                        'name' => $Forum['name'])
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
                $UserQuery = $Connector->prepare('SELECT member_id, username FROM `'.$aPrefix.'members` '.
                                                 'ORDER BY username' );

                $UserQuery->loop(function($User) use (&$Users)
                {
                    array_push( $Users, array(
                        'id'   => $User['member_id'],
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
            $Config = $this->getConfig();
            return ($aUserData['disabled'] == 1)
                ? GetGroupName(ENUM_GROUP_NONE)
                : GetGroupName($Config->mapGroup($aUserData['rank_id'], ENUM_GROUP_NONE));
        }

        // -------------------------------------------------------------------------

        private function generateUserInfo( $aUserData )
        {
            $Info = new UserInfo();
            $Info->UserId      = $aUserData['member_id'];
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
            if (!defined('BT4_AUTOLOGIN') || !BT4_AUTOLOGIN)
                return null;

            session_start();
            return (isset($_SESSION['btUsername']))
                ? $this->getUserInfoByName( $_SESSION['btUsername'] )
                : null;
        }

        // -------------------------------------------------------------------------

        public function getUserInfoByName( $aUserName )
        {
            $Connector = $this->getConnector();
            $UserQuery = $Connector->prepare('SELECT member_id, rank_id, username, password, disabled '.
                'FROM `'.BT4_TABLE_PREFIX.'members` '.
                'WHERE LOWER(username) = :Login');

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
            $UserQuery = $Connector->prepare('SELECT member_id, rank_id, username, password, disabled '.
                'FROM `'.BT4_TABLE_PREFIX.'members` '.
                'WHERE member_id = :UserId');

            $UserQuery->BindValue( ':UserId', $aUserId, PDO::PARAM_INT );
            $UserData = $UserQuery->fetchFirst();

            return ($UserData != null)
                ? $this->generateUserInfo($UserData)
                : null;
        }

        // -------------------------------------------------------------------------

        private function extractSaltPart( $aPassword )
        {
            return substr($aPassword, 0, 7+22);
        }

        // -------------------------------------------------------------------------

        public function getMethodFromPass( $aPassword )
        {
            return self::$HashMethodBF;
        }

        // -------------------------------------------------------------------------

        public function hash( $aPassword, $aSalt, $aMethod )
        {
            return crypt($aPassword,$aSalt);
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

                    // Create topic

                    $TopicQuery = $Connector->prepare('INSERT INTO `'.BT4_TABLE_PREFIX.'forum_topic` '.
                       '(forumboard_id) VALUES '.
                       '(:ForumId)');

                    $TopicQuery->BindValue( ':ForumId', BT4_POSTTO, PDO::PARAM_INT );
                    $TopicQuery->execute(true);
                    $TopicId = $Connector->lastInsertId();

                    // Create post

                    $PostQuery = $Connector->prepare('INSERT INTO `'.BT4_TABLE_PREFIX.'forum_post` '.
                        '(forumtopic_id, member_id, dateposted, title, message) VALUES '.
                        '(:TopicId, :UserId, :Now, :Subject, :Text)');

                    $PostQuery->BindValue( ':TopicId', $TopicId, PDO::PARAM_INT );
                    $PostQuery->BindValue( ':UserId', BT4_POSTAS, PDO::PARAM_INT );
                    $PostQuery->BindValue( ':Now', $Timestamp, PDO::PARAM_INT );

                    $PostQuery->BindValue( ':Subject', $aSubject, PDO::PARAM_STR );
                    $PostQuery->BindValue( ':Text', $aMessage, PDO::PARAM_STR );

                    $PostQuery->execute(true);
                    $PostId = $Connector->lastInsertId();

                    // Finish topic

                    $TopicFinishQuery = $Connector->prepare('UPDATE `'.BT4_TABLE_PREFIX.'forum_topic` '.
                        'SET forumpost_id = :PostId, lastpost_id = :PostId '.
                        'WHERE forumtopic_id = :TopicId LIMIT 1');

                    $TopicFinishQuery->BindValue( ':TopicId', $TopicId, PDO::PARAM_INT );
                    $TopicFinishQuery->BindValue( ':PostId', $PostId, PDO::PARAM_INT );

                    $TopicFinishQuery->execute(true);

                    // Update board

                    $BoardFinishQuery = $Connector->prepare('UPDATE `'.BT4_TABLE_PREFIX.'forum_board` '.
                        'SET lastpost_id = :PostId '.
                        'WHERE forumboard_id = :ForumId LIMIT 1');

                    $BoardFinishQuery->BindValue( ':ForumId', BT4_POSTTO, PDO::PARAM_INT );
                    $BoardFinishQuery->BindValue( ':PostId', $PostId, PDO::PARAM_INT );
                    $BoardFinishQuery->execute(true);
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
