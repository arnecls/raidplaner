<?php
    include_once_exists(dirname(__FILE__)."/../../config/config.vb3.php");

    array_push(PluginRegistry::$Classes, "VB3Binding");

    class VB3Binding extends Binding
    {
        private static $BindingName = "vb3";
        public static $HashMethod = "vb3_md5s";

        // -------------------------------------------------------------------------

        public function getName()
        {
            return self::$BindingName;
        }

        // -------------------------------------------------------------------------

        public function getConfig()
        {
            $Config = new BindingConfig();

            $Config->Database         = defined("VB3_DATABASE") ? VB3_DATABASE : RP_DATABASE;
            $Config->User             = defined("VB3_USER") ? VB3_USER : RP_USER;
            $Config->Password         = defined("VB3_PASS") ? VB3_PASS : RP_PASS;
            $Config->Prefix           = defined("VB3_TABLE_PREFIX") ? VB3_TABLE_PREFIX : "vb_";
            $Config->AutoLoginEnabled = defined("VB3_AUTOLOGIN") ? VB3_AUTOLOGIN : false;
            $Config->CookieData       = defined("VB3_COOKIE_PREFIX") ? VB3_COOKIE_PREFIX : "bb";
            $Config->PostTo           = defined("VB3_POSTTO") ? VB3_POSTTO : "";
            $Config->PostAs           = defined("VB3_POSTAS") ? VB3_POSTAS : "";
            $Config->Members          = defined("VB3_RAIDLEAD_GROUPS") ? explode(",", VB3_RAIDLEAD_GROUPS ) : array();
            $Config->Raidleads        = defined("VB3_MEMBER_GROUPS") ? explode(",", VB3_MEMBER_GROUPS ) : array();
            $Config->HasCookieConfig  = true;
            $Config->HasGroupConfig   = true;
            $Config->HasForumConfig   = true;

            return $Config;
        }

        // -------------------------------------------------------------------------

        public function getExternalConfig($aRelativePath)
        {
            $Out = Out::getInstance();
            $ConfigPath = $_SERVER["DOCUMENT_ROOT"]."/".$aRelativePath."/includes/config.php";
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
                "database"  => $config["Database"]["dbname"],
                "user"      => $config["MasterServer"]["username"],
                "password"  => $config["MasterServer"]["password"],
                "prefix"    => $config["Database"]["tableprefix"],
                "cookie"    => $config["Misc"]["cookieprefix"],
            );
        }

        // -------------------------------------------------------------------------

        public function writeConfig($aEnable, $aDatabase, $aPrefix, $aUser, $aPass, $aAutoLogin, $aPostTo, $aPostAs, $aMembers, $aLeads, $aCookieEx)
        {
            $Config = fopen( dirname(__FILE__)."/../../config/config.vb3.php", "w+" );

            fwrite( $Config, "<?php\n");
            fwrite( $Config, "\tdefine(\"VB3_BINDING\", ".(($aEnable) ? "true" : "false").");\n");

            if ( $aEnable )
            {
                fwrite( $Config, "\tdefine(\"VB3_DATABASE\", \"".$aDatabase."\");\n");
                fwrite( $Config, "\tdefine(\"VB3_USER\", \"".$aUser."\");\n");
                fwrite( $Config, "\tdefine(\"VB3_PASS\", \"".$aPass."\");\n");
                fwrite( $Config, "\tdefine(\"VB3_TABLE_PREFIX\", \"".$aPrefix."\");\n");
                fwrite( $Config, "\tdefine(\"VB3_COOKIE_PREFIX\", \"".$aCookieEx."\");\n");
                fwrite( $Config, "\tdefine(\"VB3_AUTOLOGIN\", ".(($aAutoLogin) ? "true" : "false").");\n");

                fwrite( $Config, "\tdefine(\"VB3_POSTTO\", ".$aPostTo.");\n");
                fwrite( $Config, "\tdefine(\"VB3_POSTAS\", ".$aPostAs.");\n");
                fwrite( $Config, "\tdefine(\"VB3_MEMBER_GROUPS\", \"".implode( ",", $aMembers )."\");\n");
                fwrite( $Config, "\tdefine(\"VB3_RAIDLEAD_GROUPS\", \"".implode( ",", $aLeads )."\");\n");
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
                $GroupQuery = $Connector->prepare( "SELECT usergroupid, title FROM `".$aPrefix."usergroup` ORDER BY title" );
                $Groups = array();

                $GroupQuery->loop(function($Group) use (&$Groups)
                {
                    array_push( $Groups, array(
                        "id"   => $Group["usergroupid"],

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
            if ($aUserData["bandate"] > 0)
            {
                $CurrentTime = time();
                if ( ($aUserData["bandate"] < $CurrentTime) &&
                     (($aUserData["liftdate"] == 0) || ($aUserData["liftdate"] > $CurrentTime)) )
                {
                    return "none"; // ### return, banned ###
                }
            }

            $MemberGroups   = explode(",", VB3_MEMBER_GROUPS );
            $RaidleadGroups = explode(",", VB3_RAIDLEAD_GROUPS );

            if ( in_array($aUserData["usergroupid"], $RaidleadGroups) )
                return "raidlead";

            if ( in_array($aUserData["usergroupid"], $MemberGroups) )
                return "member";

            return "none";
        }

        // -------------------------------------------------------------------------

        private function generateUserInfo( $aUserData )
        {
            $Info = new UserInfo();
            $Info->UserId      = $aUserData["userid"];
            $Info->UserName    = $aUserData["username"];
            $Info->Password    = $aUserData["password"];
            $Info->Salt        = $aUserData["salt"];
            $Info->Group       = $this->getGroupForUser($aUserData);
            $Info->BindingName = $this->getName();
            $Info->PassBinding = $this->getName();

            return $Info;
        }

        // -------------------------------------------------------------------------

        public function getExternalLoginData()
        {
            if (!defined("VB3_AUTOLOGIN") || !VB3_AUTOLOGIN)
                return null;

            $UserInfo = null;

            // Fetch cookie name
            if ( defined("VB3_COOKIE_PREFIX") )
            {
                $CookieName = VB3_COOKIE_PREFIX."sessionhash";

                // Fetch user info if seesion cookie is set

                if (isset($_COOKIE[$CookieName]))
                {
                    $Connector = $this->getConnector();
                    $UserQuery = $Connector->prepare("SELECT userid ".
                                                  "FROM `".VB3_TABLE_PREFIX."session` ".
                                                  "WHERE sessionhash = :sid LIMIT 1");

                    $UserQuery->BindValue( ":sid", $_COOKIE[$CookieName], PDO::PARAM_STR );
                    $UserData = $UserQuery->fetchFirst();

                    if ( $UserData != null )
                    {
                        // Get user info by external id

                        $UserId = $UserData["userid"];

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
            $UserQuery = $Connector->prepare("SELECT `".VB3_TABLE_PREFIX."user`.userid, `".VB3_TABLE_PREFIX."user`.usergroupid, ".
                                          "username, password, salt, bandate, liftdate ".
                                          "FROM `".VB3_TABLE_PREFIX."user` ".
                                          "LEFT JOIN `".VB3_TABLE_PREFIX."userban` USING(userid) ".
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
            $UserQuery = $Connector->prepare("SELECT `".VB3_TABLE_PREFIX."user`.userid, `".VB3_TABLE_PREFIX."user`.usergroupid, ".
                                          "username, password, salt, bandate, liftdate ".
                                          "FROM `".VB3_TABLE_PREFIX."user` ".
                                          "LEFT JOIN `".VB3_TABLE_PREFIX."userban` USING(userid) ".
                                          "WHERE userid = :UserId LIMIT 1");

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
            return md5(md5($aPassword).$aSalt);
        }

        // -------------------------------------------------------------------------

        public function post($aSubject, $aMessage)
        {

        }
    }
?>