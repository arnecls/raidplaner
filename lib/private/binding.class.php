<?php
    require_once dirname(__FILE__)."/connector.class.php";
    
    // Helper class for external bindings, so we don't have to use string
    // based associative arrays.
    class UserInfo
    {
        public $UserId;
        public $UserName;
        public $Password;
        public $Salt;
        public $SessionSalt;
        public $Group;
        public $BindingName;
        public $PassBinding;
    }
    
    // Helper class for available configuration data
    class BindingConfig
    {
        public $Database;
        public $User;
        public $Password;
        public $Prefix;
        public $CookieData;
        public $Members;
        public $Raidleads;
        public $PostTo;
        public $PostAs;
        public $AutoLoginEnabled;
        public $ForumPostEnabled;
        public $HasCookieConfig;
        public $HasGroupConfig;
        public $HasForumConfig;
        
        public function __construct()
        {
            $this->Database         = "";
            $this->User             = "";
            $this->Password         = "";
            $this->Prefix           = "";
            $this->CookieData       = "";
            $this->Members          = array();
            $this->Raidleads        = array();
            $this->PostTo           = 0;
            $this->PostAs           = 0;
            $this->AutoLoginEnabled = false;
            $this->ForumPostEnabled = false;
            $this->HasCookieConfig  = false;
            $this->HasGroupConfig   = false;
            $this->HasForumConfig   = false;
        }
    }
    
    // Common interface for forum/cms bindings
    abstract class Binding 
    {
        private $mConnector;
        
        // -------------------------------------------------------------------------
        
        abstract public function getName();
        
        abstract public function getConfig();
        abstract public function writeConfig($aEnable, $aDatabase, $aPrefix, $aUser, $aPass, $aAutoLogin, $aPostTo, $aPostAs, $aMembers, $aLeads, $aCookieEx);
        
        abstract public function getExternalConfig($aRelativePath);
        abstract public function getGroups($aDatabase, $aPrefix, $aUser, $aPass, $aThrow);
        abstract public function getForums($aDatabase, $aPrefix, $aUser, $aPass, $aThrow);
        abstract public function getUsers($aDatabase, $aPrefix, $aUser, $aPass, $aThrow);
        abstract public function post($aSubject, $aMessage);
        
        abstract public function getExternalLoginData();
        abstract public function getUserInfoByName( $aUserName );
        abstract public function getUserInfoById( $aUserId );
        abstract public function getMethodFromPass( $aPassword );
        abstract public function hash( $aPassword, $aSalt, $aMethod );
        
        // -------------------------------------------------------------------------
        
        public function isActive()
        {
            $Name = strtoupper($this->getName());
            return defined($Name."_BINDING") && constant($Name."_BINDING");
        }
        
        // -------------------------------------------------------------------------
        
        public function postRequested()
        {
            $Name = strtoupper($this->getName());
            return defined($Name."_POSTTO") && (constant($Name."_POSTTO") != 0);
        }
        
        // -------------------------------------------------------------------------
        
        public function isConfigWriteable()
        {
            $ConfigFolder = dirname(__FILE__)."/../../config";
            $ConfigFile   = $ConfigFolder."/config.".$this->getName().".php";
            
            return (!file_exists($ConfigFile) && is_writable($ConfigFolder)) || is_writable($ConfigFile);
        }
        
        // -------------------------------------------------------------------------
        
        public function getConnector()
        {
            if ($this->mConnector == null)
            {
                $Config = $this->getConfig();
                $this->mConnector = new Connector(SQL_HOST, $Config->Database, $Config->User, $Config->Password, false);    
            }
                
            return $this->mConnector;
        }
        
        // -------------------------------------------------------------------------
        
        public function getGroupsFromConfig()
        {
            $Config = $this->getConfig();
            return $this->getGroups($Config->Database, $Config->Prefix, $Config->User, $Config->Password, false);
        }
        
        // -------------------------------------------------------------------------
        
        public function getForumsFromConfig()
        {
            $Config = $this->getConfig();
            return $this->getForums($Config->Database, $Config->Prefix, $Config->User, $Config->Password, false);
        }
        
        // -------------------------------------------------------------------------
        
        public function getUsersFromConfig()
        {
            $Config = $this->getConfig();
            return $this->getUsers($Config->Database, $Config->Prefix, $Config->User, $Config->Password, false);
        }
    }

?>