<?php
    include_once_exists(dirname(__FILE__)."/../../config/config.mybb.php");

    array_push(PluginRegistry::$Classes, "MYBBBinding");

    class MYBBBinding extends Binding
    {
        private static $BindingName = "mybb";

        public static $HashMethod = "mybb_md5s";

        // -------------------------------------------------------------------------

        public function getName()
        {
            return self::$BindingName;
        }

        // -------------------------------------------------------------------------

        public function getConfig()
        {
            $Config = new BindingConfig();

            $Config->Database         = defined("MYBB_DATABASE") ? MYBB_DATABASE : RP_DATABASE;
            $Config->User             = defined("MYBB_USER") ? MYBB_USER : RP_USER;
            $Config->Password         = defined("MYBB_PASS") ? MYBB_PASS : RP_PASS;
            $Config->Prefix           = defined("MYBB_TABLE_PREFIX") ? MYBB_TABLE_PREFIX : "mybb_";
            $Config->AutoLoginEnabled = defined("MYBB_AUTOLOGIN") ? MYBB_AUTOLOGIN : false;
            $Config->PostTo           = defined("MYBB_POSTTO") ? MYBB_POSTTO : "";
            $Config->PostAs           = defined("MYBB_POSTAS") ? MYBB_POSTAS : "";
            $Config->Members          = defined("MYBB_RAIDLEAD_GROUPS") ? explode(",", MYBB_RAIDLEAD_GROUPS ) : array();
            $Config->RaidLeads        = defined("MYBB_MEMBER_GROUPS") ? explode(",", MYBB_MEMBER_GROUPS ) : array();
            $Config->HasGroupConfig   = true;
            $Config->HasForumConfig   = true;

            return $Config;
        }

        // -------------------------------------------------------------------------

        public function getExternalConfig($aRelativePath)
        {
            $Out = Out::getInstance();

            $ConfigPath = $_SERVER["DOCUMENT_ROOT"]."/".$aRelativePath."/inc/config.php";
            if (!file_exists($ConfigPath))
            {
                $Out->pushError($ConfigPath." ".L("NotExisting").".");
                return null;
            }

            @include_once($ConfigPath);

            if (!isset($config))
            {
                $Out->pushError(L("NoValidConfig"));
                return null;
            }

            return array(
                "database"  => $config["database"]["database"],
                "user"      => $config["database"]["username"],
                "password"  => $config["database"]["password"],
                "prefix"    => $config["database"]["table_prefix"],
                "cookie"    => null
            );
        }

        // -------------------------------------------------------------------------

        public function writeConfig($aEnable, $aDatabase, $aPrefix, $aUser, $aPass, $aAutoLogin, $aPostTo, $aPostAs, $aMembers, $aLeads, $aCookieEx)
        {
            $Config = fopen( dirname(__FILE__)."/../../config/config.mybb.php", "w+" );

            fwrite( $Config, "<?php\n");
            fwrite( $Config, "\tdefine(\"MYBB_BINDING\", ".(($aEnable) ? "true" : "false").");\n");

            if ( $aEnable )
            {
                fwrite( $Config, "\tdefine(\"MYBB_DATABASE\", \"".$aDatabase."\");\n");
                fwrite( $Config, "\tdefine(\"MYBB_USER\", \"".$aUser."\");\n");
                fwrite( $Config, "\tdefine(\"MYBB_PASS\", \"".$aPass."\");\n");
                fwrite( $Config, "\tdefine(\"MYBB_TABLE_PREFIX\", \"".$aPrefix."\");\n");
                fwrite( $Config, "\tdefine(\"MYBB_AUTOLOGIN\", ".(($aAutoLogin) ? "true" : "false").");\n");

                fwrite( $Config, "\tdefine(\"MYBB_POSTTO\", ".$aPostTo.");\n");
                fwrite( $Config, "\tdefine(\"MYBB_POSTAS\", ".$aPostAs.");\n");
                fwrite( $Config, "\tdefine(\"MYBB_MEMBER_GROUPS\", \"".implode( ",", $aMembers )."\");\n");
                fwrite( $Config, "\tdefine(\"MYBB_RAIDLEAD_GROUPS\", \"".implode( ",", $aLeads )."\");\n");
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
                $GroupQuery = $Connector->prepare( "SELECT gid, title FROM `".$aPrefix."usergroups` ORDER BY title" );
                $Groups = array();

                $GroupQuery->loop(function($Group) use (&$Groups)
                {
                    array_push( $Groups, array(
                        "id"   => $Group["gid"],

                        "name" => $Group["title"])
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

        private function getGroupForUser( $aUserData )
        {
            if ($aUserData["dateline"] > 0)
            {
                $CurrentTime = time();
                if ( ($aUserData["dateline"] < $CurrentTime) &&
                     (($aUserData["lifted"] == 0) || ($aUserData["lifted"] > $CurrentTime)) )
                {
                    return "none"; // ### return, banned ###
                }
            }

            $AssignedGroup  = "none";
            $MemberGroups   = explode(",", MYBB_MEMBER_GROUPS );
            $RaidleadGroups = explode(",", MYBB_RAIDLEAD_GROUPS );

            $Groups = explode(",", $aUserData["additionalgroups"]);
            array_push($Groups, $aUserData["usergroup"] );

            foreach( $Groups as $Group )
            {
                if ( in_array($Group, $MemberGroups) )
                    $AssignedGroup = "member";

                if ( in_array($Group, $RaidleadGroups) )
                    return "raidlead"; // ### return, highest possible group ###
            }

            return $AssignedGroup;
        }

        // -------------------------------------------------------------------------

        private function generateUserInfo( $aUserData )
        {
            $Info = new UserInfo();
            $Info->UserId      = $aUserData["uid"];
            $Info->UserName    = $aUserData["username"];
            $Info->Password    = $aUserData["password"];
            $Info->Salt        = $aUserData["salt"];
            $Info->SessionSalt = null;
            $Info->Group       = $this->getGroupForUser($aUserData);
            $Info->BindingName = $this->getName();
            $Info->PassBinding = $this->getName();

            return $Info;
        }

        // -------------------------------------------------------------------------

        public function getExternalLoginData()
        {
            if (!defined("MYBB_AUTOLOGIN") || !MYBB_AUTOLOGIN)
                return null;

            $Connector = $this->getConnector();
            $UserInfo = null;

            // Fetch cookie name

            $CookieQuery = $Connector->prepare("SELECT value ".
                "FROM `".MYBB_TABLE_PREFIX."settings` ".
                "WHERE name = 'cookieprefix' LIMIT 1");

            $ConfigData = $CookieQuery->fetchFirst();

            if ( $ConfigData != null )
            {
                $CookieName = $ConfigData["value"]."sid";

                // Fetch user info if seesion cookie is set

                if (isset($_COOKIE[$CookieName]))
                {
                    $UserQuery = $Connector->prepare("SELECT uid ".
                        "FROM `".MYBB_TABLE_PREFIX."sessions` ".
                        "WHERE sid = :sid LIMIT 1");

                    $UserQuery->BindValue( ":sid", $_COOKIE[$CookieName], PDO::PARAM_STR );
                    $UserData = $UserQuery->fetchFirst();

                    if ( $UserData != null )
                    {
                        // Get user info by external id

                        $UserId = $UserData["uid"];

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
            $UserQuery = $Connector->prepare("SELECT uid, username, password, salt, usergroup, additionalgroups, dateline, lifted ".
                                          "FROM `".MYBB_TABLE_PREFIX."users` ".
                                          "LEFT JOIN `".MYBB_TABLE_PREFIX."banned` USING(uid) ".
                                          "WHERE LOWER(username) = :Login LIMIT 1");

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
            $UserQuery = $Connector->prepare("SELECT uid, username, password, salt, usergroup, dateline, additionalgroups ".
                                          "FROM `".MYBB_TABLE_PREFIX."users` ".
                                          "LEFT JOIN `".MYBB_TABLE_PREFIX."banned` USING(uid) ".
                                          "WHERE uid = :UserId LIMIT 1");

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
            return md5(md5($aSalt).md5($aPassword));
        }

        // -------------------------------------------------------------------------

        public function post($aSubject, $aMessage)
        {

        }
    }
?>