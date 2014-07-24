<?php
    require_once dirname(__FILE__).'/connector.class.php';
    require_once dirname(__FILE__).'/binding.class.php';
    require_once dirname(__FILE__).'/tools_site.php';
    @include_once dirname(__FILE__).'/../config/config.php';
    require_once dirname(__FILE__).'/pluginregistry.class.php';

    // Helper class for character information for the current user.
    class CharacterInfo
    {
        public $CharacterId;
        public $Game;
        public $Name;
        public $ClassName;
        public $IsMainChar;
        public $Role1;
        public $Role2;
    }

    // The UserProxy wraps away data for the currently logged in user (if any)
    // and provides functions for user authentication, modification, etc.
    class UserProxy
    {
        private static $Instance = null;
        private static $StickyLifeTime = 2592000; // 30 Days           
        private static $Bindings;
        private static $BindingsByName;

        public $UserId     = 0;
        public $UserName   = '';
        public $UserGroup  = 'none';
        public $Characters = array();
        public $Settings   = array();
        private $SiteId    = '';

        // --------------------------------------------------------------------------------------------
        
        public static function registerInstance($aPluginInstance)
        {
            array_push(self::$Bindings, $aPluginInstance);
            self::$BindingsByName[$aPluginInstance->getName()] = $aPluginInstance;
        }
        
        // --------------------------------------------------------------------------------------------

        public static function initBindings()
        {
            $NativeBinding = new NativeBinding();
            self::$Bindings = array(
                $NativeBinding // native has to be first
            );

            self::$BindingsByName[$NativeBinding->getName()] = $NativeBinding;            
            PluginRegistry::ForEachBinding(function($PluginInstance) {
                UserProxy::registerInstance($PluginInstance);
            });
        }

        // --------------------------------------------------------------------------------------------

        public function __construct($aAllowAutoLogin)
        {
            assert(self::$Instance == NULL);
            
            // 1. Try to reactive from a running session
            // 2. Try auto login via external cookie if requested

            if ( !$this->reactivateFromSession() && !$this->processLoginRequest(null, $aAllowAutoLogin) )
            {
                 $this->resetUser();
            }
        }

        // --------------------------------------------------------------------------------------------

        public static function getInstance($aAllowAutoLogin=false)
        {
            if (self::$Instance == NULL)
                self::$Instance = new UserProxy($aAllowAutoLogin);

            return self::$Instance;
        }

        // --------------------------------------------------------------------------------------------

        public function resetUser()
        {
            $this->UserGroup  = 'none';
            $this->UserId     = 0;
            $this->UserName   = '';
            $this->Characters = array();
            $this->Settings   = array();
            
            Session::release();
        }

        // --------------------------------------------------------------------------------------------

        public static function generateKey32()
        {
            return md5(Random::getBytes(2048));
        }

        // --------------------------------------------------------------------------------------------

        public function invalidateOneTimeKey( $aUserId )
        {
            $OneTimeKey = self::generateKey32();
            $Connector  = Connector::getInstance();

            $OtkQuery = $Connector->prepare('UPDATE `'.RP_TABLE_PREFIX.'User` SET OneTimeKey = :Key '.
                                            'WHERE UserId = :UserId LIMIT 1' );

            $OtkQuery->bindValue( ':Key',    $OneTimeKey, PDO::PARAM_STR );
            $OtkQuery->bindValue( ':UserId', $aUserId,    PDO::PARAM_INT );
            $OtkQuery->execute();
        }

        // --------------------------------------------------------------------------------------------

        private function validateCleartextPassword( $aPassword, $aUserId, $aBindingName, $aLookupBindingName )
        {
            $Binding  = self::$BindingsByName[$aLookupBindingName];
            $UserInfo = $Binding->getUserInfoById($aUserId);

            if ($UserInfo == null)
                return false;
            
            if ($aBindingName != $aLookupBindingName)    
                $Binding = self::$BindingsByName[$aBindingName];
                
            $Method = $Binding->getMethodFromPass($UserInfo->Password);
            $Hashed = $Binding->hash($aPassword, $UserInfo->Salt, $Method);

            return $UserInfo->Password == $Hashed;
        }

        // --------------------------------------------------------------------------------------------

        private function reactivateFromSession()
        {
            $Session = Session::get();
            
            if ( $Session != null )
            {
                $UserInfo = self::$BindingsByName['none']->GetUserInfoById($Session->GetUserId());
                
                if ( $UserInfo != null )
                {
                    if ( $UserInfo->BindingName != 'none' )
                    {                                         
                        $UserInfo = $this->getUserInfoById($UserInfo->BindingName, $UserInfo->UserId);
                        
                        $UpdateUserQuery = $this->updateUserMirror( $UserInfo, false, self::generateKey32() );
                        $UpdateUserQuery->execute();
                    }
                    
                    $this->UserGroup = $UserInfo->Group;
                    $this->UserName  = $UserInfo->UserName;
                    $this->UserId    = $Session->GetUserId();
                                                            
                    $this->updateCharacters();
                    $this->updateSettings();
                    
                    $Session->refresh();                    
                    return true;
                }
            }

            return false;
        }

        // --------------------------------------------------------------------------------------------

        private function getUserCredentialsFromInfo( $aUserInfo, $aBinding )
        {
            if ( $aUserInfo != null )
            {
                // UserInfo could be retrieved.
                // Generate a suitable, (mostly) random one-time-key,
                // store that key and return the required userInfo data.
                // By using the external id we prevent creating the same
                // user twice after an external rename.

                $OneTimeKey = self::generateKey32();
                $IsLocalInfo = $aUserInfo->BindingName == 'none'; // In this case UserId != ExternalUserId (!)

                $UpdateUserQuery = $this->updateUserMirror( $aUserInfo, $IsLocalInfo, $OneTimeKey );
                $UpdateUserQuery->execute();

                if ( $UpdateUserQuery->getAffectedRows() == 0 )
                {
                    if ( $IsLocalInfo )
                        return null; // ### return, rare case guard (e.g. race condition) ###

                    // Update did not succeed, so the user is not yet registered to
                    // the local database. Create a new local hook for that user.

                    if ( self::createUser($aUserInfo->Group, $aUserInfo->UserId, $aUserInfo->BindingName,
                                          $aUserInfo->UserName, $aUserInfo->Password, $aUserInfo->Salt) === false )
                    {
                        return null; // ### return, user could not be created ###
                    }

                    // Set the one time key for the now existing user

                    $UpdateUserQuery->execute();
                }

                if (defined('USE_CLEARTEXT_PASSWORDS') && USE_CLEARTEXT_PASSWORDS)
                    $HashMethod = 'cleartext';
                else
                    $HashMethod = self::$BindingsByName[$aUserInfo->PassBinding]->getMethodFromPass($aUserInfo->Password);

                return Array( 'salt'   => $aUserInfo->Salt,
                              'key'    => $OneTimeKey,
                              'method' => $HashMethod );

                // ### return, found user ###
            }

            return null;
        }

        // --------------------------------------------------------------------------------------------

        public function getExternalLoginData()
        {
            // Iterate all bindings and search for the given user

            foreach( self::$Bindings as $Binding )
            {
                if ( $Binding->isActive() )
                {
                    $UserInfo = $Binding->getExternalLoginData();

                    if ($UserInfo != null)
                    {
                        // Fetch the user data so UserId and Binding fields
                        // can be set to correct values for getUserCredentialsFromInfo

                        $ExternalUserId = $UserInfo->UserId;

                        $Connector = Connector::getInstance();
                        $UserQuery = $Connector->prepare( 'SELECT * FROM `'.RP_TABLE_PREFIX.'User` '.
                                                          'WHERE ExternalId = :UserId AND ExternalBinding = :Binding LIMIT 1' );

                        $UserQuery->bindValue(':UserId', $UserInfo->UserId, PDO::PARAM_INT );
                        $UserQuery->bindValue(':Binding', $UserInfo->BindingName, PDO::PARAM_STR );

                        // The query might fail if the user is not yet registered

                        $UserData = $UserQuery->fetchFirst();

                        if ($UserData != null)
                        {

                            if ($UserData['BindingActive'] == 'false')
                            {
                                // We are querying a local user in that case we need to
                                // modify the userinfo for getUserCredentialsFromInfo
                                // to work as expected

                                $UserInfo->BindingName = 'none';
                                $UserInfo->UserId = $UserData['UserId'];
                            }
                        }

                        // Checking the credentials will create the user if necessary.

                        if ( $this->getUserCredentialsFromInfo($UserInfo, $Binding) != null )
                        {
                            // Revert user info to database values

                            $UserInfo->UserId = $ExternalUserId;
                            $UserInfo->BindingName = $Binding->GetName();

                            return $UserInfo;
                        }
                    }
                }
            }

            // No external login detected

            return null;
        }

        // --------------------------------------------------------------------------------------------

        public function getUserCredentials( $aUserName )
        {
            // Iterate all bindings and search for the given user

            foreach( self::$Bindings as $Binding )
            {
                if ( $Binding->isActive() )
                {
                    $UserInfo    = $Binding->getUserInfoByName($aUserName);
                    $Credentials = $this->getUserCredentialsFromInfo($UserInfo, $Binding);

                    if ( $Credentials != null )
                        return $Credentials;
                }
            }

            // User not found

            return null;
        }

        // --------------------------------------------------------------------------------------------

        public function getUserCredentialsById( $aUserId, $aBindingName )
        {
            // Iterate all bindings and search for the given user

            if (isset(self::$BindingsByName[$aBindingName]))
            {
                $Binding = self::$BindingsByName[$aBindingName];
                if ( $Binding->isActive() )
                {
                    $UserInfo    = $Binding->getUserInfoById($aUserId);
                    $Credentials = $this->getUserCredentialsFromInfo($UserInfo, $Binding);

                    if ( $Credentials != null )
                        return $Credentials;
                }
            }

            // User not found

            return null;
        }

        // --------------------------------------------------------------------------------------------

        public function getUserInfoById( $aBindingName, $aExternalId )
        {
            $Binding = self::$BindingsByName[$aBindingName];

            if ( $Binding->isActive() )
            {
                return $Binding->getUserInfoById($aExternalId);
            }

            return null;
        }

        // --------------------------------------------------------------------------------------------

        public static function getAllUserInfosById( $aExternalId )
        {
            $Candidates = array();

            foreach( self::$Bindings as $Binding )
            {
                if ( $Binding->isActive() )
                {
                    $Info = $Binding->getUserInfoById($aExternalId);
                    if ( $Info != null )
                    {
                        $Candidates[$Binding->getName()] = $Info;
                    }
                }
            }

            return $Candidates;
        }

        // --------------------------------------------------------------------------------------------

        public function getAllUserInfosByName( $aUserName )
        {
            $Candidates = array();

            foreach( self::$Bindings as $Binding )
            {
                if ( $Binding->isActive() )
                {
                    $Info = $Binding->getUserInfoByName($aUserName);
                    if ( $Info != null )
                    {
                        $Candidates[$Binding->getName()] = $Info;
                    }
                }
            }

            return $Candidates;
        }

        // --------------------------------------------------------------------------------------------

        public function validateCredentials( $aSignedPassword )
        {
            $Connector = Connector::getInstance();

            $UserQuery = $Connector->prepare( 'SELECT OneTimeKey, Password, ExternalBinding, BindingActive, ExternalId '.
                'FROM `'.RP_TABLE_PREFIX.'User` WHERE UserId = :UserId LIMIT 1' );
            
            $UserQuery->bindValue(':UserId', $this->UserId, PDO::PARAM_INT );
            $UserData = $UserQuery->fetchFirst();

            if ($UserData != null)
            {
                if ( defined('USE_CLEARTEXT_PASSWORDS') && USE_CLEARTEXT_PASSWORDS )
                {
                    // Cleartext mode fallback
                    
                    if (($UserData['BindingActive'] == 'false') || ($UserData['ExternalBinding'] == 'none'))
                    {
                        $LookupBinding = 'none';
                        $UserId = $UserData['UserId'];
                    }
                    else
                    {
                        $LookupBinding = $UserData['ExternalBinding'];
                        $UserId = $UserData['ExternalId'];
                    }
                        
                    return $this->validateCleartextPassword( $aSignedPassword, $UserId, $UserData['ExternalBinding'], $LookupBinding );
                }

                $this->invalidateOneTimeKey( $this->UserId );

                $HashedStoredPassword = hash('sha256', $UserData['OneTimeKey'].$UserData['Password']);

                if ( $aSignedPassword == $HashedStoredPassword )
                    return true;
            }

            return false;
        }

        // --------------------------------------------------------------------------------------------

        public function processLoginRequest( $aLoginUser, $aAllowAutoLogin )
        {
            $Connector = Connector::getInstance();
            $UserData = null;

            if ($aLoginUser == null)
            {
                if ( !$aAllowAutoLogin )
                    return false; // ### return, no data ###

                // Check for external logins

                $ExternalUser = $this->getExternalLoginData();

                if ($ExternalUser == null)
                    return false; // ### return, no data ###

                // Try to fetch the externally bound user.
                // We need to do this again (has already been done in getExternalLoginData)

                // to fetch any updated/newly created data

                $UserQuery = $Connector->prepare( 'SELECT * FROM `'.RP_TABLE_PREFIX.'User` '.
                    'WHERE ExternalId = :UserId AND ExternalBinding = :Binding LIMIT 1' );

                $UserQuery->bindValue(':UserId', $ExternalUser->UserId, PDO::PARAM_INT );
                $UserQuery->bindValue(':Binding', $ExternalUser->BindingName, PDO::PARAM_STR );
                $UserData = $UserQuery->fetchFirst();

                if ($UserData == null)
                    return false; // ### return, not registered ###
            }
            else
            {
                // Try to login by $aLoginUser

                $PasswordCheckOk = false;

                $UserQuery = $Connector->prepare( 'SELECT * FROM `'.RP_TABLE_PREFIX.'User` WHERE Login = :Login LIMIT 1' );

                $UserQuery->bindValue(':Login', $aLoginUser['Login'], PDO::PARAM_STR );
                $UserData = $UserQuery->fetchFirst();

                if ($UserData != null)
                {
                    if ( defined('USE_CLEARTEXT_PASSWORDS') && USE_CLEARTEXT_PASSWORDS )
                    {
                        // User logged in using a cleartext password.
                        // In this case we encrypt locally via php.
                        
                        if (($UserData['BindingActive'] == 'false') || ($UserData['ExternalBinding'] == 'none'))
                        {
                            $LookupBinding = 'none';
                            $UserId = $UserData['UserId'];
                        }
                        else
                        {
                            $LookupBinding = $UserData['ExternalBinding'];
                            $UserId = $UserData['ExternalId'];
                        }
                        
                        $PasswordCheckOk = $this->validateCleartextPassword( $aLoginUser['Password'], $UserId, $UserData['ExternalBinding'], $LookupBinding );
                    }
                    else
                    {
                        // User logged in using one-time-key authentication
                        // In this case we get a HMAC based password and need

                        // to reset the key

                        $this->invalidateOneTimeKey( $UserData['UserId'] );
                        $HashedStoredPassword = hash('sha256', $UserData['OneTimeKey'].$UserData['Password']);
                        $PasswordCheckOk = ($aLoginUser['Password'] == $HashedStoredPassword);
                    }
                }

                if ( !$PasswordCheckOk )
                    return false; // ### return, invalid password ###
            }

            // Login successfull. Prepare session.
            // Update the current user entry to fix the external data binding (password, etc.)
            // and create a new session key while at it.

            $this->UserGroup  = $UserData['Group'];
            $this->UserId     = $UserData['UserId'];
            $this->UserName   = $UserData['Login'];

            $this->updateCharacters();
            $this->updateSettings();

            // Set the expiration time to a higher value when logging in as 'sticky'
            
            if ( (isset($_REQUEST['sticky']) && ($_REQUEST['sticky'] == 'true')) )
            {
                $Session = Session::create($this->UserId, self::$StickyLifeTime);
            }
            else
            {
                $Session = Session::create($this->UserId);
            }
            
            return true; // ### return, logged in ###
        }

        // --------------------------------------------------------------------------------------------

        public function updateCharacters()
        {
            if ( $this->UserGroup != 'none' )
            {
                $Connector = Connector::getInstance();
                $CharacterQuery = $Connector->prepare( 'SELECT * FROM `'.RP_TABLE_PREFIX.'Character` '.
                                                       'WHERE UserId = :UserId '.
                                                       'ORDER BY Mainchar, Name' );

                $CharacterQuery->bindValue(':UserId', $this->UserId, PDO::PARAM_INT);

                $Characters = array();
                $CharacterQuery->loop( function($Row) use (&$Characters)
                {
                    $Character = new CharacterInfo();

                    $Character->CharacterId = $Row['CharacterId'];
                    $Character->Game        = $Row['Game'];
                    $Character->Name        = $Row['Name'];
                    $Character->ClassName   = $Row['Class'];
                    $Character->IsMainChar  = $Row['Mainchar'] == 'true';
                    $Character->Role1       = $Row['Role1'];
                    $Character->Role2       = $Row['Role2'];

                    array_push($Characters, $Character);
                });

                $this->Characters = $Characters;
            }
        }

        // --------------------------------------------------------------------------------------------

        public function updateSettings()
        {
            if ( $this->UserGroup != 'none' )
            {
                $Connector = Connector::getInstance();
                $SettingQuery = $Connector->prepare( 'SELECT * FROM `'.RP_TABLE_PREFIX.'UserSetting` '.
                    'WHERE UserId = :UserId' );

                $SettingQuery->bindValue(':UserId', $this->UserId, PDO::PARAM_INT);

                $Settings = array();

                $SettingQuery->loop( function($Row) use (&$Settings)
                {
                    $Settings[$Row['Name']] = array('number' => $Row['IntValue'], 'text' => $Row['TextValue']);
                });

                $this->Settings = $Settings;
            }
        }

        // --------------------------------------------------------------------------------------------

        public static function createUser( $aGroup, $aExternalUserId, $aBindingName, $aLogin, $aHashedPassword, $aSalt )
        {
            $Connector = Connector::getInstance();

            // Pre-check:
            // Login must be unique

            $UserQuery = $Connector->prepare('SELECT UserId FROM `'.RP_TABLE_PREFIX.'User` '.
                                             'WHERE Login = :Login LIMIT 1');

            $UserQuery->bindValue(':Login', strtolower($aLogin), PDO::PARAM_STR);

            if ( $UserQuery->execute() && ($UserQuery->getAffectedRows() == 0) )
            {
                // User does not exist, so we can create one

                $UserQuery = $Connector->prepare('INSERT INTO `'.RP_TABLE_PREFIX.'User` '.
                    '(`Group`, ExternalId, ExternalBinding, BindingActive, Login, Password, Salt, Created, OneTimeKey) '.
                    'VALUES (:Group, :ExternalUserId, :Binding, :Active, :Login, :Password, :Salt, FROM_UNIXTIME(:Created), "")');

                $Active = ($aBindingName != 'none') ? 'true' : 'false';

                $UserQuery->bindValue(':Group',          $aGroup,                  PDO::PARAM_STR);
                $UserQuery->bindValue(':ExternalUserId', $aExternalUserId, PDO::PARAM_INT);
                $UserQuery->bindValue(':Binding',        $aBindingName,            PDO::PARAM_STR);
                $UserQuery->bindValue(':Active',         $Active,                  PDO::PARAM_STR);
                $UserQuery->bindValue(':Login',          strtolower($aLogin),      PDO::PARAM_STR);
                $UserQuery->bindValue(':Password',       $aHashedPassword,         PDO::PARAM_STR);
                $UserQuery->bindValue(':Salt',           $aSalt,                   PDO::PARAM_STR);
                $UserQuery->bindValue(':Created',        time(),                   PDO::PARAM_INT);

                $UserQuery->execute();
                return $Connector->lastInsertId(); // ### return, inserted ###
            }

            return false;
        }

        // --------------------------------------------------------------------------------------------

        public function updateUserMirror( &$UserInfo, $aIsStoredLocally, $aKey )
        {
            // TODO: $UserInfo->UserId referres to UserId when updating a local user
            //       and ExternalUserId otherwise. This is inconvenient.

            $Out = Out::getInstance();
            $Connector = Connector::getInstance();

            if ($aIsStoredLocally)
            {
                // Local users don't change externally, so just update the key
            
                $MirrorQuery = $Connector->prepare(
                    'UPDATE `'.RP_TABLE_PREFIX.'User` SET OneTimeKey = :Key '.
                    'WHERE UserId = :UserId LIMIT 1' );

                $MirrorQuery->bindValue( ':Key',    $aKey,             PDO::PARAM_STR );
                $MirrorQuery->bindValue( ':UserId', $UserInfo->UserId, PDO::PARAM_INT );
            }
            else
            {
                $UserIsBound = true;
                
                if (!isset(self::$BindingsByName[$UserInfo->PassBinding]))
                {
                    $Out->pushError($UserInfo->PassBinding.' binding did not register correctly.');
                }
                else
                {
                    $Binding = self::$BindingsByName[$UserInfo->PassBinding];

                    if ($Binding->isActive())
                    {
                        $ExternalInfo = $Binding->getUserInfoById($UserInfo->UserId);
                        
                        if ( $ExternalInfo == null )
                            $UserIsBound = false;
                        else
                            $UserInfo = $ExternalInfo;
                    }
                    else
                    {
                        $UserIsBound = false;
                        $Out->pushError($UserInfo->PassBinding.' binding has been disabled.');
                    }
                }

                // Local users may update externally, so sync the credentials

                $SyncGroup = !defined('ALLOW_GROUP_SYNC') || ALLOW_GROUP_SYNC;

                $MirrorQuery = $Connector->prepare('UPDATE `'.RP_TABLE_PREFIX.'User` SET '.
                    'Login = :Login, Password = :Password, BindingActive = :Active, Salt = :Salt, OneTimeKey = :Key'.
                    (($SyncGroup) ? ', `Group` = :Group ' : ' ').
                    'WHERE ExternalBinding = :Binding AND ExternalId = :UserId LIMIT 1' );

                $MirrorQuery->bindValue( ':Login',     $UserInfo->UserName,             PDO::PARAM_STR );
                $MirrorQuery->bindValue( ':Password',  $UserInfo->Password,             PDO::PARAM_STR );
                $MirrorQuery->bindValue( ':Salt',      $UserInfo->Salt,                 PDO::PARAM_STR );
                $MirrorQuery->bindValue( ':Key',       $aKey,                           PDO::PARAM_STR );
                $MirrorQuery->bindValue( ':Binding',   $UserInfo->BindingName,          PDO::PARAM_STR );
                $MirrorQuery->bindValue( ':Active',    $UserIsBound ? 'true' : 'false', PDO::PARAM_STR );
                $MirrorQuery->bindValue( ':UserId',    $UserInfo->UserId,               PDO::PARAM_INT );

                if ($SyncGroup)
                    $MirrorQuery->bindValue( ':Group', $UserInfo->Group, PDO::PARAM_STR );
            }

            return $MirrorQuery;
        }

        // --------------------------------------------------------------------------------------------

        public static function changePassword( $aUserId, $aHashedPassword, $aSalt )
        {
            $IsCurrentUser = self::getInstance()->UserId == $aUserId;

            if ( !$IsCurrentUser && !validAdmin() )
                return false; // ### return, security check failed ###

            // Change password to new values.
            // Only accounts with an inactive binding may be changed.

            $Connector = Connector::getInstance();
            $UpdateQuery  = $Connector->prepare('UPDATE `'.RP_TABLE_PREFIX.'User` SET '.
                                                'ExternalBinding = "none", Password = :Password, Salt = :Salt '.
                                                'WHERE UserId = :UserId AND (BindingActive="false" OR ExternalBinding="none") LIMIT 1');

            $UpdateQuery->bindValue(':UserId',   $aUserId,         PDO::PARAM_INT);
            $UpdateQuery->bindValue(':Password', $aHashedPassword, PDO::PARAM_STR);
            $UpdateQuery->bindValue(':Salt',     $aSalt,           PDO::PARAM_STR);

            $UpdateQuery->execute();
            return true;
        }
    }

    // --------------------------------------------------------------------------------------------

    UserProxy::initBindings();

    // --------------------------------------------------------------------------------------------
    
    function msgLogin($aRequest)
    {
        $LoginUser = array( 'Login'    => $aRequest['user'],
                            'Password' => $aRequest['pass'] );
                            
        $Proxy = UserProxy::getInstance();
        
        if ( !$Proxy->processLoginRequest($LoginUser, false) )
        {
            msgLogout();
            
            $Out = Out::getInstance();
            $Out->pushError(L('WrongPassword'));
        }
    }
    
    // --------------------------------------------------------------------------------------------

    function msgLogout()
    {
        $Proxy = UserProxy::getInstance();
        $Proxy->resetUser();
    }
    
    // --------------------------------------------------------------------------------------------

    function registeredUser()
    {
        UserProxy::getInstance();
        return Session::isActive();
    }

    // --------------------------------------------------------------------------------------------

    function validUser()
    {
        $Group = UserProxy::getInstance()->UserGroup;
        return Session::isActive() && ($Group != 'none');
    }

    // --------------------------------------------------------------------------------------------

    function validRaidlead()
    {
        $Group = UserProxy::getInstance()->UserGroup;
        return Session::isActive() && (($Group == 'raidlead') || ($Group == 'admin'));
    }

    // --------------------------------------------------------------------------------------------

    function validAdmin()
    {
        $Group = UserProxy::getInstance()->UserGroup;
        return Session::isActive() && ($Group == 'admin');
    }
?>
