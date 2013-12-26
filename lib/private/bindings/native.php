<?php
    require_once dirname(__FILE__)."/../connector.class.php";
    require_once dirname(__FILE__)."/../../config/config.php";

    class NativeBinding extends Binding
    {
        private static $BindingName = "none";
        public static $HashMethod = "native_sha256s";

        // -------------------------------------------------------------------------

        public function isActive()
        {
            return true;
        }

        // -------------------------------------------------------------------------

        public function postRequested()
        {
            return false;
        }

        // -------------------------------------------------------------------------

        public function getName()
        {
            return self::$BindingName;
        }

        // -------------------------------------------------------------------------

        public function getConfig()
        {
            $Config = new BindingConfig();

            $Config->Database = RP_DATABASE;
            $Config->User     = RP_USER;
            $Config->Password = RP_PASS;
            $Config->Prefix   = RP_TABLE_PREFIX;

            return $Config;
        }

        // -------------------------------------------------------------------------

        public function writeConfig($aEnable, $aDatabase, $aPrefix, $aUser, $aPass, $aAutoLogin, $aPostTo, $aPostAs, $aMembers, $aLeads, $aCookieEx)
        {
        }

        // -------------------------------------------------------------------------

        public function getExternalConfig($aRelativePath)
        {
            return null;
        }

        // -------------------------------------------------------------------------

        public function getGroups($aDatabase, $aPrefix, $aUser, $aPass, $aThrow)
        {
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

        public function post($aSubject, $aMessage)
        {
        }

        // -------------------------------------------------------------------------

        public function getExternalLoginData()
        {
            return null;
        }

        // -------------------------------------------------------------------------

        private function generateInfo( $aUserData )
        {
            $Info = new UserInfo();
            $Info->UserName    = $aUserData["Login"];
            $Info->Password    = $aUserData["Password"];
            $Info->Salt        = $aUserData["Salt"];
            $Info->Group       = $aUserData["Group"];
            $Info->PassBinding = $aUserData["ExternalBinding"];

            if (($aUserData["ExternalBinding"] != "none") &&

                ($aUserData["BindingActive"] == "true"))

            {
                $Info->UserId      = $aUserData["ExternalId"];
                $Info->BindingName = $aUserData["ExternalBinding"];
            }
            else
            {
                $Info->UserId      = $aUserData["UserId"];
                $Info->BindingName = $this->getName();
            }

            return $Info;
        }

        // -------------------------------------------------------------------------

        public function getUserInfoByName( $aUserName )
        {
            $Connector = Connector::getInstance();
            $UserQuery = $Connector->prepare("SELECT * FROM ".RP_TABLE_PREFIX."User ".
                                          "WHERE Login = :Login LIMIT 1");

            $UserQuery->BindValue( ":Login", strtolower($aUserName), PDO::PARAM_STR );
            $UserData = $UserQuery->fetchFirst();

            return ($UserData != null)
                ? $this->generateInfo($UserData)
                : null;
        }

        // -------------------------------------------------------------------------

        public function getUserInfoById( $aUserId )
        {
            $Connector = Connector::getInstance();
            $UserQuery = $Connector->prepare("SELECT * FROM ".RP_TABLE_PREFIX."User ".
                                          "WHERE UserId = :UserId LIMIT 1");

            $UserQuery->BindValue( ":UserId", $aUserId, PDO::PARAM_INT );
            $UserData = $UserQuery->fetchFirst();

            return ($UserData != null)
                ? $this->generateInfo($UserData)
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
            return hash("sha256", sha1($aPassword).$aSalt);
        }
    }
?>