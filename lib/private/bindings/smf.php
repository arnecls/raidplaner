<?php
    include_once_exists(dirname(__FILE__)."/../../config/config.smf.php");

    array_push(PluginRegistry::$Classes, "SMFBinding");

    class SMFBinding extends Binding
    {
        private static $BindingName = "smf";

        public static $HashMethod = "smf_sha1s";

        // -------------------------------------------------------------------------

        public function getName()
        {
            return self::$BindingName;
        }

        // -------------------------------------------------------------------------

        public function getConfig()
        {
            $Config = new BindingConfig();

            $Config->Database         = defined("SMF_DATABASE") ? SMF_DATABASE : RP_DATABASE;
            $Config->User             = defined("SMF_USER") ? SMF_USER : RP_USER;
            $Config->Password         = defined("SMF_PASS") ? SMF_PASS : RP_PASS;
            $Config->Prefix           = defined("SMF_TABLE_PREFIX") ? SMF_TABLE_PREFIX : "smf_";
            $Config->AutoLoginEnabled = defined("SMF_AUTOLOGIN") ? SMF_AUTOLOGIN : false;
            $Config->CookieData       = defined("SMF_COOKIE") ? SMF_COOKIE : "SMFCookie956";
            $Config->PostTo           = defined("SMF_POSTTO") ? SMF_POSTTO : "";
            $Config->PostAs           = defined("SMF_POSTAS") ? SMF_POSTAS : "";
            $Config->Members          = defined("SMF_RAIDLEAD_GROUPS") ? explode(",", SMF_RAIDLEAD_GROUPS ) : array();
            $Config->Raidleads        = defined("SMF_MEMBER_GROUPS") ? explode(",", SMF_MEMBER_GROUPS ) : array();
            $Config->HasCookieConfig  = true;
            $Config->HasGroupConfig   = true;
            $Config->HasForumConfig   = true;

            return $Config;
        }

        // -------------------------------------------------------------------------

        public function getExternalConfig($aRelativePath)
        {
            $ConfigPath = $_SERVER["DOCUMENT_ROOT"]."/".$aRelativePath."/Settings.php";
            if (!file_exists($ConfigPath))
            {
                Out::getInstance()->pushError($ConfigPath." ".L("NotExisting").".");
                return null;
            }

            @include_once($ConfigPath);

            if (!isset($mbname))
            {
                Out::getInstance()->pushError(L("NoValidConfig"));
                return null;
            }

            return array(
                "database"  => $db_name,
                "user"      => $db_user,
                "password"  => $db_passwd,
                "prefix"    => $db_prefix,
                "cookie"    => (isset($cookiename)) ? $cookiename : "SMFCookie956"
            );
        }

        // -------------------------------------------------------------------------

        public function writeConfig($aEnable, $aDatabase, $aPrefix, $aUser, $aPass, $aAutoLogin, $aPostTo, $aPostAs, $aMembers, $aLeads, $aCookieEx)
        {
            $Config = fopen( dirname(__FILE__)."/../../config/config.smf.php", "w+" );

            fwrite( $Config, "<?php\n");
            fwrite( $Config, "\tdefine(\"SMF_BINDING\", ".(($aEnable) ? "true" : "false").");\n");

            if ( $aEnable )
            {
                fwrite( $Config, "\tdefine(\"SMF_DATABASE\", \"".$aDatabase."\");\n");
                fwrite( $Config, "\tdefine(\"SMF_USER\", \"".$aUser."\");\n");
                fwrite( $Config, "\tdefine(\"SMF_PASS\", \"".$aPass."\");\n");
                fwrite( $Config, "\tdefine(\"SMF_TABLE_PREFIX\", \"".$aPrefix."\");\n");
                fwrite( $Config, "\tdefine(\"SMF_COOKIE\", \"".$aCookieEx."\");\n");
                fwrite( $Config, "\tdefine(\"SMF_AUTOLOGIN\", ".(($aAutoLogin) ? "true" : "false").");\n");

                fwrite( $Config, "\tdefine(\"SMF_POSTTO\", ".$aPostTo.");\n");
                fwrite( $Config, "\tdefine(\"SMF_POSTAS\", ".$aPostAs.");\n");
                fwrite( $Config, "\tdefine(\"SMF_MEMBER_GROUPS\", \"".implode( ",", $aMembers )."\");\n");
                fwrite( $Config, "\tdefine(\"SMF_RAIDLEAD_GROUPS\", \"".implode( ",", $aLeads )."\");\n");
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
                $GroupQuery = $Connector->prepare( "SELECT id_group, group_name FROM `".$aPrefix."membergroups` ORDER BY group_name" );
                $Groups = array();

                $GroupQuery->loop(function($Group) use (&$Groups)
                {
                    array_push( $Groups, array(
                        "id"   => $Group["id_group"],

                        "name" => $Group["group_name"])
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
                $ForumQuery = $Connector->prepare( "SELECT id_board, name FROM `".$aPrefix."boards` ".
                                                   "WHERE redirect = \"\" ORDER BY name" );

                $ForumQuery->loop(function($Forum) use (&$Forums)
                {
                    array_push( $Forums, array(
                        "id"   => $Forum["id_board"],
                        "name" => $Forum["name"])
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
                $UserQuery = $Connector->prepare( "SELECT id_member, member_name FROM `".$aPrefix."members` ".
                                                  "ORDER BY member_name" );

                $UserQuery->loop(function($User) use (&$Users)
                {
                    array_push( $Users, array(
                        "id"   => $User["id_member"],
                        "name" => $User["member_name"])
                    );
                }, $aThrow);

                return $Users;
            }

            return null;
        }

        // -------------------------------------------------------------------------

        private function getGroupForUser( $aUserData )
        {
            if ($aUserData["ban_time"] > 0)
            {
                $CurrentTime = time();
                if ( ($aUserData["ban_time"] < $CurrentTime) &&
                     (($aUserData["expire_time"] == 0) || ($aUserData["expire_time"] > $CurrentTime)) )
                {
                    return "none"; // ### return, banned ###
                }
            };

            $MemberGroups   = explode(",", SMF_MEMBER_GROUPS );
            $RaidleadGroups = explode(",", SMF_RAIDLEAD_GROUPS );
            $AssignedGroup  = "none";

            $Groups = explode(",", $aUserData["additional_groups"]);
            array_push($Groups, $aUserData["id_group"] );

            foreach( $Groups as $Group )
            {
                if ( in_array($Group, $MemberGroups) )
                    $AssignedGroup = "member";

                if ( in_array($Group, $RaidleadGroups) )
                    return "raidlead"; // ### return, best possible group ###
            }

            return $AssignedGroup;
        }

        // -------------------------------------------------------------------------

        private function generateUserInfo( $aUserData )
        {
            $Info = new UserInfo();
            $Info->UserId      = $aUserData["id_member"];
            $Info->UserName    = $aUserData["member_name"];
            $Info->Password    = $aUserData["passwd"];
            $Info->Salt        = strtolower($aUserData["member_name"]);
            $Info->SessionSalt = $aUserData["password_salt"];
            $Info->Group       = $this->getGroupForUser($aUserData);
            $Info->BindingName = $this->getName();
            $Info->PassBinding = $this->getName();

            return $Info;
        }

        // -------------------------------------------------------------------------

        public function getExternalLoginData()
        {
            if (!defined("SMF_AUTOLOGIN") || !SMF_AUTOLOGIN)
                return null;

            $UserInfo = null;

            // Fetch user info if seesion cookie is set

            if (defined("SMF_COOKIE") && isset($_COOKIE[SMF_COOKIE]))
            {
                $CookieData = unserialize($_COOKIE[SMF_COOKIE]);
                $UserId  = $CookieData[0];
                $PwdHash = $CookieData[1];

                $UserInfo = $this->getUserInfoById($UserId);

                $SessionHash = sha1($UserInfo->Password.$UserInfo->SessionSalt);
                if ($PwdHash != $SessionHash)
                    $UserInfo = null;
            }

            return $UserInfo;
        }

        // -------------------------------------------------------------------------

        public function getUserInfoByName( $aUserName )
        {
            $Connector = $this->getConnector();
            $UserQuery = $Connector->prepare("SELECT id_member, member_name, passwd, password_salt, id_group, additional_groups, ban_time, expire_time ".
                                          "FROM `".SMF_TABLE_PREFIX."members` ".
                                          "LEFT JOIN `".SMF_TABLE_PREFIX."ban_items` USING(id_member) ".
                                          "LEFT JOIN `".SMF_TABLE_PREFIX."ban_groups` USING(id_ban_group) ".
                                          "WHERE LOWER(member_name) = :Login LIMIT 1");

            $UserQuery->BindValue( ":Login", strtolower($aUserName), PDO::PARAM_STR );
            $UserData = $UserQuery->fetchFirst();

            return ($UserData != null)
                ? $this->generateUserInfo($UserData)
                : null;
        }

        // -------------------------------------------------------------------------

        public function getUserInfoById( $aUserId )
        {
            $Connector = $this->getConnector();
            $UserQuery = $Connector->prepare("SELECT id_member, member_name, passwd, password_salt, id_group, additional_groups, ban_time, expire_time ".
                                          "FROM `".SMF_TABLE_PREFIX."members` ".
                                          "LEFT JOIN `".SMF_TABLE_PREFIX."ban_items` USING(id_member) ".
                                          "LEFT JOIN `".SMF_TABLE_PREFIX."ban_groups` USING(id_ban_group) ".
                                          "WHERE id_member = :UserId LIMIT 1");

            $UserQuery->BindValue( ":UserId", $aUserId, PDO::PARAM_INT );
            $UserData = $UserQuery->fetchFirst();

            return ($UserData != null)
                ? $this->generateUserInfo($UserData)
                : null;
        }

        // -------------------------------------------------------------------------

        public function getMethodFromPass( $aPassword )
        {
            return self::$HashMethod;
        }

        // -------------------------------------------------------------------------

        public function hash( $aPassword, $aSalt, $aMethod )
        {
            return sha1($aSalt.$aPassword);
        }

        // -------------------------------------------------------------------------

        public function post($aSubject, $aMessage)
        {
            $Connector = $this->getConnector();
            $Connector->beginTransaction();

            $Timestamp = time();

            // Fetch user

            try
            {
                $UserQuery = $Connector->prepare("SELECT member_name FROM `".SMF_TABLE_PREFIX."members` WHERE id_member=:UserId LIMIT 1");
                $UserQuery->BindValue( ":UserId", SMF_POSTAS, PDO::PARAM_INT );

                $UserData = $UserQuery->fetchFirst();

                // Create post

                $PostQuery = $Connector->prepare("INSERT INTO `".SMF_TABLE_PREFIX."messages` ".
                                              "(id_board, poster_time, id_member, poster_name, subject, body) VALUES ".
                                              "(:ForumId, :Now, :UserId, :Username, :Subject, :Text)");

                $PostQuery->BindValue( ":ForumId", SMF_POSTTO, PDO::PARAM_INT );
                $PostQuery->BindValue( ":UserId", SMF_POSTAS, PDO::PARAM_INT );
                $PostQuery->BindValue( ":Now", $Timestamp, PDO::PARAM_INT );
                $PostQuery->BindValue( ":Username", $UserData["member_name"], PDO::PARAM_STR );

                $PostQuery->BindValue( ":Subject", $aSubject, PDO::PARAM_STR );
                $PostQuery->BindValue( ":Text", $aMessage, PDO::PARAM_STR );

                $PostQuery->execute(true);
                $PostId = $Connector->lastInsertId();

                // Create topic

                $TopicQuery = $Connector->prepare("INSERT INTO `".SMF_TABLE_PREFIX."topics` ".
                                               "(id_board, id_member_started, id_first_msg, id_last_msg) VALUES ".
                                               "(:ForumId, :UserId, :PostId, :PostId)");

                $TopicQuery->BindValue( ":ForumId", SMF_POSTTO, PDO::PARAM_INT );
                $TopicQuery->BindValue( ":UserId", SMF_POSTAS, PDO::PARAM_INT );
                $TopicQuery->BindValue( ":PostId", $PostId, PDO::PARAM_INT );

                $TopicQuery->execute(true);
                $TopicId = $Connector->lastInsertId();
                
                // Finish topic

                $TopicFinishQuery = $Connector->prepare("UPDATE `".SMF_TABLE_PREFIX."messages` ".
                                                     "SET id_topic = :TopicId ".
                                                     "WHERE id_msg = :PostId LIMIT 1");

                $TopicFinishQuery->BindValue( ":TopicId", $TopicId, PDO::PARAM_INT );
                $TopicFinishQuery->BindValue( ":PostId", $PostId, PDO::PARAM_INT );

                $TopicFinishQuery->execute(true);
                $Connector->commit();
            }
            catch (PDOException $Exception)
            {
                $Connector->rollBack();
                throw $Exception;
            }
        }
    }
?>