<?php
    if ( defined('LOCALE_MAIN') )
    {
        // Roles
        $gLocale[ 'Tank' ]                     = 'Tank';
        $gLocale[ 'Healer' ]                   = 'Healer';
        $gLocale[ 'Damage' ]                   = 'Fighter';
        $gLocale[ 'Melee' ]                    = 'Melee';
        $gLocale[ 'Range' ]                    = 'Ranged';
        $gLocale[ 'Support' ]                  = 'Support';

        // Pre-loading checks
        $gLocale[ 'ContinueNoUpdate' ]         = 'Continue without updating';
        $gLocale[ 'UpdateBrowser' ]            = 'Please update your browser';
        $gLocale[ 'UsingOldBrowser' ]          = 'You are using an out of date version of your browser.';
        $gLocale[ 'OlderBrowserFeatures' ]     = 'Older browser do not support all required features or display the site incorrectly.';
        $gLocale[ 'DownloadNewBrowser' ]       = 'You should update your browser or download one of the following Browsers.';
        $gLocale[ 'RaidplanerNotConfigured' ]  = 'Raidplaner is not configured yet or requires an update.';
        $gLocale[ 'PleaseRunSetup' ]           = 'Please run <a href="setup">setup</a> or follow the <a href="https://github.com/arnecls/raidplaner/wiki/Manual-Setup">manual installation</a> instructions.';

        // General
        $gLocale[ 'Reserved' ]                 = 'Reserved';
        $gLocale[ 'Error' ]                    = 'Error';
        $gLocale[ 'Apply' ]                    = 'Apply changes';
        $gLocale[ 'AccessDenied' ]             = 'Access denied';
        $gLocale[ 'ForeignCharacter' ]         = 'Not your character';
        $gLocale[ 'DatabaseError' ]            = 'Database error';
        $gLocale[ 'Cancel' ]                   = 'Cancel';
        $gLocale[ 'Notification' ]             = 'Notification';
        $gLocale[ 'Busy' ]                     = 'Busy. Please wait.';
        $gLocale[ 'RequestError' ]             = 'A request returned an error.';
        $gLocale[ 'UnknownRequest' ]           = 'Unknown request';
        $gLocale[ 'InvalidRequest' ]           = 'Invalid request';
        $gLocale[ 'InputRequired' ]            = 'Input required';
        $gLocale[ 'UnappliedChanges' ]         = 'Do you want to discard unapplied changes?';
        $gLocale[ 'DiscardChanges' ]           = 'Yes, discard';
        $gLocale[ 'to' ]                       = 'to';
        $gLocale[ 'PHPVersionWarning' ]        = 'As of Raidplaner version 1.1.0 PHP 5.3.4 or better is required.<br/>I\'m sorry but your server requires an update :(';

        // Login und user registration
        $gLocale[ 'Login' ]                    = 'Login';
        $gLocale[ 'Logout' ]                   = 'Logout';
        $gLocale[ 'Username' ]                 = 'Username';
        $gLocale[ 'Password' ]                 = 'Password';
        $gLocale[ 'RepeatPassword' ]           = 'Repeat password';
        $gLocale[ 'Register' ]                 = 'Register';
        $gLocale[ 'EnterValidUsername' ]       = 'You must enter a valid username.';
        $gLocale[ 'EnterNonEmptyPassword' ]    = 'You must enter a non-empty password.';
        $gLocale[ 'PasswordsNotMatch' ]        = 'Passwords did not match.';
        $gLocale[ 'NameInUse' ]                = 'This username is already in use.';
        $gLocale[ 'RegistrationDone' ]         = 'Registration complete.';
        $gLocale[ 'AccountIsLocked' ]          = 'Your account is currently locked.';
        $gLocale[ 'ContactAdminToUnlock' ]     = 'Please contact your admin to get your account unlocked.';
        $gLocale[ 'NoSuchUser' ]               = 'The given user could not be found.';
        $gLocale[ 'HashingInProgress' ]        = 'Hashing password';
        $gLocale[ 'PassStrength']              = 'Passwort strength';

        // Calendar
        $gLocale[ 'Calendar' ]                 = 'Calendar';
        $gLocale[ 'January' ]                  = 'January';
        $gLocale[ 'February' ]                 = 'February';
        $gLocale[ 'March' ]                    = 'March';
        $gLocale[ 'April' ]                    = 'April';
        $gLocale[ 'May' ]                      = 'May';
        $gLocale[ 'June' ]                     = 'June';
        $gLocale[ 'July' ]                     = 'July';
        $gLocale[ 'August' ]                   = 'August';
        $gLocale[ 'September' ]                = 'September';
        $gLocale[ 'October' ]                  = 'October';
        $gLocale[ 'November' ]                 = 'November';
        $gLocale[ 'December' ]                 = 'December';
        $gLocale[ 'Monday' ]                   = 'Monday';
        $gLocale[ 'Tuesday' ]                  = 'Tuesday';
        $gLocale[ 'Wednesday' ]                = 'Wednesday';
        $gLocale[ 'Thursday' ]                 = 'Thursday';
        $gLocale[ 'Friday' ]                   = 'Friday';
        $gLocale[ 'Saturday' ]                 = 'Saturday';
        $gLocale[ 'Sunday' ]                   = 'Sunday';
        $gLocale[ 'Mon' ]                      = 'Mo';
        $gLocale[ 'Tue' ]                      = 'Tu';
        $gLocale[ 'Wed' ]                      = 'We';
        $gLocale[ 'Thu' ]                      = 'Th';
        $gLocale[ 'Fri' ]                      = 'Fr';
        $gLocale[ 'Sat' ]                      = 'Sa';
        $gLocale[ 'Sun' ]                      = 'Su';
        $gLocale[ 'NotSignedUp' ]              = 'Not signed up';
        $gLocale[ 'Absent' ]                   = 'Absent';
        $gLocale[ 'Benched' ]                  = 'On waiting list';
        $gLocale[ 'RaidingAs' ]                = 'Raiding as';
        $gLocale[ 'WhyAbsent' ]                = 'Please tell us why you will be absent.';
        $gLocale[ 'SetAbsent' ]                = 'Set to absent';
        $gLocale[ 'Comment' ]                  = 'Comment';
        $gLocale[ 'SaveComment' ]              = 'Save comment';
        $gLocale[ 'RepeatOnce' ]               = 'Do not repeat';
        $gLocale[ 'RepeatDay' ]                = 'times, repeat daily';
        $gLocale[ 'RepeatWeek' ]               = 'times, repeat weekly';
        $gLocale[ 'RepeatMonth' ]              = 'times, repeat monthly';

        // Raid
        $gLocale[ 'Raid' ]                     = 'Raid';
        $gLocale[ 'Upcoming' ]                 = 'Upcoming raids';
        $gLocale[ 'CreateRaid' ]               = 'Create raid';
        $gLocale[ 'NewDungeon' ]               = 'New dungeon';
        $gLocale[ 'Description' ]              = 'Description';
        $gLocale[ 'DefaultRaidMode' ]          = 'Default attendance mode';
        $gLocale[ 'RaidStatus' ]               = 'Status';
        $gLocale[ 'RaidOpen' ]                 = 'Raid open';
        $gLocale[ 'RaidLocked' ]               = 'Raid locked';
        $gLocale[ 'RaidCanceled' ]             = 'Raid canceled';
        $gLocale[ 'DeleteRaid' ]               = 'Delete raid';
        $gLocale[ 'ConfirmRaidDelete' ]        = 'Do you really want to delete this Raid?';
        $gLocale[ 'Players' ]                  = 'Players';
        $gLocale[ 'RequiredForRole' ]          = 'Required for role';
        $gLocale[ 'AbsentPlayers' ]            = 'Absent players';
        $gLocale[ 'UndecidedPlayers' ]         = 'Undecided players';
        $gLocale[ 'AbsentNoReason' ]           = 'No message given.';
        $gLocale[ 'Undecided' ]                = 'Has not made a statement, yet.';
        $gLocale[ 'MarkAsAbesent' ]            = 'Mark as absent';
        $gLocale[ 'MakeAbsent' ]               = 'Player will be absent';
        $gLocale[ 'AbsentMessage' ]            = 'Please enter the reason why the player will be absent.<br/>The message will be prefixed with your login name.';
        $gLocale[ 'SetupBy' ]                  = 'Attended';
        $gLocale[ 'AbsentBy' ]                 = 'Unattended';
        $gLocale[ 'SwitchChar' ]               = 'Switched character';
        $gLocale[ 'RaidNotFound' ]             = 'Raid could not be found.';
        $gLocale[ 'RaidSetup' ]                = 'Raid setup';
        $gLocale[ 'LinkToRaid' ]               = 'Link to raid';
        $gLocale[ 'Switch' ]                   = 'Switch';
        $gLocale[ 'Retire' ]                   = 'Absent';
        $gLocale[ 'Export' ]                   = 'Export';
        $gLocale[ 'ExportFile' ]               = 'File';
        $gLocale[ 'ExportClipboard' ]          = 'Clipboard';
        $gLocale[ 'CopyOk' ]                   = 'Text has been copied to clipboard.';
        $gLocale[ 'Random' ]                   = 'Unknown';
       
        // Profile
        $gLocale[ 'Profile' ]                  = 'Profile';
        $gLocale[ 'History' ]                  = 'Raid history';
        $gLocale[ 'Characters' ]               = 'Your characters';
        $gLocale[ 'CharName' ]                 = 'name';
        $gLocale[ 'NoName' ]                   = 'A new character has no name assigned.';
        $gLocale[ 'NoClass' ]                  = 'A new character has no class assigned.';
        $gLocale[ 'DeleteCharacter' ]          = 'Delete character';
        $gLocale[ 'ConfirmDeleteCharacter' ]   = 'Do you really want to delete this character?';
        $gLocale[ 'AttendancesRemoved' ]       = 'All existing attendances will be removed, too.';
        $gLocale[ 'RaidAttendance' ]           = 'Raid attendance';
        $gLocale[ 'RolesInRaids' ]             = 'Roles in attended raids';
        $gLocale[ 'Queued' ]                   = 'Queued';
        $gLocale[ 'Attended' ]                 = 'Attended';
        $gLocale[ 'Missed' ]                   = 'Missed';
        $gLocale[ 'ChangePassword' ]           = 'Change password';
        $gLocale[ 'OldPassword' ]              = 'Old password';
        $gLocale[ 'OldPasswordEmpty' ]         = 'The old password must not be empty.';
        $gLocale[ 'AdminPassword' ]            = 'Administrator password';
        $gLocale[ 'AdminPasswordEmpty' ]       = 'The administrator password must not be empty.';
        $gLocale[ 'WrongPassword' ]            = 'Invalid password';
        $gLocale[ 'PasswordLocked' ]           = 'Password cannot be changed.';
        $gLocale[ 'PasswordChanged' ]          = 'The password has been changed.';
        $gLocale[ 'UserNotFound' ]             = 'User could not be found.';
        $gLocale[ 'VacationStart' ]            = 'First day of vacation';
        $gLocale[ 'VacationEnd' ]              = 'Last day of vacation';
        $gLocale[ 'NoStartDate' ]              = 'Please enter the first day of your vacation.';
        $gLocale[ 'NoEndDate' ]                = 'Please enter the last day of your vacation.';
        $gLocale[ 'VacationMessage' ]          = 'Vacation message';
        $gLocale[ 'ClearVacation' ]            = 'Clear vacation data';
        $gLocale[ 'AutoAttend' ]               = 'Attend automatically';

        // Settings
        $gLocale[ 'Settings' ]                 = 'Settings';
        $gLocale[ 'Locked' ]                   = 'Locked';
        $gLocale[ 'Members' ]                  = 'Members';
        $gLocale[ 'Raidleads' ]                = 'Raidleads';
        $gLocale[ 'Administrators' ]           = 'Administrators';
        $gLocale[ 'ConfirmDeleteUser' ]        = 'Do you really want to delete this user?';
        $gLocale[ 'DeleteUser' ]               = 'Delete user';
        $gLocale[ 'MoveUser' ]                 = 'Move user to group';
        $gLocale[ 'UnlinkUser' ]               = 'Stop synchronisation and convert to local user.';
        $gLocale[ 'LinkUser' ]                 = 'Synchronize user';
        $gLocale[ 'SyncFailed' ]               = 'Failed to synchronize.</br>No fitting user found.';
        $gLocale[ 'EditForeignCharacters' ]    = 'Edit characters for';
        $gLocale[ 'ConfirmDeleteLocation' ]    = 'Do you really want to delete this location?';
        $gLocale[ 'NoteDeleteRaidsToo' ]       = 'This will also delete all raids at this location.';
        $gLocale[ 'DeleteRaids' ]              = 'Delete raids';
        $gLocale[ 'DeleteLocationRaids' ]      = 'Delete location and raids';
        $gLocale[ 'LockRaids' ]                = 'Lock raids';
        $gLocale[ 'AfterDone' ]                = 'after a raid is done';
        $gLocale[ 'BeforeStart' ]              = 'before a raid starts';
        $gLocale[ 'Seconds' ]                  = 'Second(s)';
        $gLocale[ 'Minutes' ]                  = 'Minute(s)';
        $gLocale[ 'Hours' ]                    = 'Hour(s)';
        $gLocale[ 'Days' ]                     = 'Day(s)';
        $gLocale[ 'Weeks' ]                    = 'Week(s)';
        $gLocale[ 'Month' ]                    = 'Month';
        $gLocale[ 'TimeFormat' ]               = 'Time format';
        $gLocale[ 'StartOfWeek' ]              = 'Week starts on';
        $gLocale[ 'DefaultStartTime' ]         = 'Default raid start time';
        $gLocale[ 'DefaultEndTime' ]           = 'Default raid end time';
        $gLocale[ 'DefaultRaidSize' ]          = 'Default raid size';
        $gLocale[ 'BannerPage' ]               = 'Page banner link';
        $gLocale[ 'HelpPage' ]                 = 'Help link';
        $gLocale[ 'Game' ]                     = 'Game';
        $gLocale[ 'Theme' ]                    = 'Theme';
        $gLocale[ 'ApiPrivate' ]               = 'API token (private)';
        $gLocale[ 'RaidSetupStyle' ]           = 'Attendance style';
        $gLocale[ 'RaidModeManual' ]           = 'Setup by raidlead';
        $gLocale[ 'RaidModeOverbook' ]         = 'By raidlead with overbooking';
        $gLocale[ 'RaidModeAttend' ]           = 'Setup by attend';
        $gLocale[ 'RaidModeAll' ]              = 'Just list';
        $gLocale[ 'RaidModeOptOut' ]           = 'Attend all players';
        $gLocale[ 'UpdateCheck' ]              = 'Check for updates';
        $gLocale[ 'UpToDate' ]                 = 'This raidplaner is up to date.';
        $gLocale[ 'NewVersionAvailable' ]      = 'There is a new version available:';
        $gLocale[ 'VisitProjectPage' ]         = 'Visit the project homepage';
        $gLocale[ 'AttendWithPrimary' ]        = 'Attend with primary role';
    }

    if ( defined('LOCALE_SETUP') )
    {
        // General
        $gLocale[ 'Ok' ]                       = 'Ok';
        $gLocale[ 'Back' ]                     = 'Back';
        $gLocale[ 'Continue' ]                 = 'Continue';
        $gLocale[ 'Error' ]                    = 'Error';
        $gLocale[ 'Ignore' ]                   = 'Ignore';
        $gLocale[ 'Retry' ]                    = 'Retry';
        $gLocale[ 'DatabaseError' ]            = 'Database error';

        // Menu
        $gLocale[ 'Install' ]                  = 'Install';
        $gLocale[ 'Update' ]                   = 'Update';
        $gLocale[ 'EditBindings' ]             = 'Edit bindings';
        $gLocale[ 'EditConfig' ]               = 'Edit configuration';
        $gLocale[ 'ResetPassword' ]            = 'Set admin password';
        $gLocale[ 'RepairDatabase' ]           = 'Repair database';

        // Checks
        $gLocale[ 'FilesystemChecks' ]         = 'Filesystem permission checks';
        $gLocale[ 'NotWriteable' ]             = 'Not writeable';
        $gLocale[ 'ConfigFolder' ]             = 'Config folder';
        $gLocale[ 'MainConfigFile' ]           = 'Main config file';
        $gLocale[ 'DatabaseConnection' ]       = 'Database connection';
        $gLocale[ 'WritePermissionRequired' ]  = 'Setup needs write permission on all files in the config folder located at ';
        $gLocale[ 'ChangePermissions' ]        = 'If any of these checks fails you have to change permissions to "writeable" for your http server\'s user.';
        $gLocale[ 'FTPClientHelp' ]            = 'On how to change permissions, please consult your FTP client\'s helpfiles.';
        $gLocale[ 'OutdatedPHP' ]              = 'Outdated PHP version';
        $gLocale[ 'PHPVersion' ]               = 'PHP version';
        $gLocale[ 'MbStringModule' ]           = 'Mbstring module';
        $gLocale[ 'MbStringNotFound' ]         = 'Mbstring not configured with PHP';
        $gLocale[ 'PDOModule' ]                = 'PDO module';
        $gLocale[ 'PDONotFound' ]              = 'PDO not configured with PHP';
        $gLocale[ 'PDOMySQLModule' ]           = 'PDO MySQL driver';
        $gLocale[ 'PDOMySQLNotFound' ]         = 'PDO MySQL driver not found';
        $gLocale[ 'PHPRequirements' ]          = 'The raidplaner needs a PHP 5.3 installation configured with PDO extensions.';

        // Database setup
        $gLocale[ 'ConfigureDatabase' ]        = 'Please configure the database the raidplaner will place it\'s data into.';
        $gLocale[ 'SameAsForumDatabase' ]      = 'If you want to bind the raidplaner to a third party forum the raidplaner database must be on the same server as the forum\'s database.';
        $gLocale[ 'EnterPrefix' ]              = 'If the database is already in use by another installation you can enter a prefix to avoid name conflicts.';
        $gLocale[ 'DatabaseHost' ]             = 'Database host';
        $gLocale[ 'RaidplanerDatabase' ]       = 'Raidplaner database';
        $gLocale[ 'UserWithDBPermissions' ]    = 'User with permissions for that database';
        $gLocale[ 'UserPassword' ]             = 'Password for that user';
        $gLocale[ 'RepeatPassword' ]           = 'Please repeat the password';
        $gLocale[ 'TablePrefix' ]              = 'Prefix for tables in the database';
        $gLocale[ 'VerifySettings' ]           = 'Verify these settings';
        $gLocale[ 'ConnectionTestFailed' ]     = 'Connection test failed';
        $gLocale[ 'ConnectionTestOk' ]         = 'Connection test succeeded';

        // Registration and admin
        $gLocale[ 'AdminName' ]                = 'Name of the admin user';
        $gLocale[ 'AdminPassword' ]            = 'Password for the admin user';
        $gLocale[ 'AdminPasswordSetup']        = 'The administrator (login name "admin") is a user that always has all available rights.';
        $gLocale[ 'AdminNotMoveable']          = 'The admin user cannot be renamed or moved into a different group.';
        $gLocale[ 'AdminPasswordNoMatch' ]     = 'Admin passwords do not match.';
        $gLocale[ 'AdminPasswordEmpty' ]       = 'Admin password must not be empty.';
        $gLocale[ 'DatabasePasswordNoMatch' ]  = 'Database passwords do not match.';
        $gLocale[ 'DatabasePasswordEmpty' ]    = 'Database password must not be empty.';
        $gLocale[ 'AllowManualRegistration' ]  = 'Allow users to register manually';
        $gLocale[ 'AllowGroupSync' ]           = 'Synchronize groups of external users';
        $gLocale[ 'AllowPublicMode' ]          = 'Register new users as members (not recommended)';
        $gLocale[ 'UseClearText' ]             = 'Submit cleartext password (not recommended)';

        // Install/Update
        $gLocale[ 'SecurityWarning' ]          = 'Security warning';
        $gLocale[ 'UpdateComplete' ]           = 'Update complete';
        $gLocale[ 'RaidplanerSetupDone' ]      = 'Raidplaner has been successfully set up.';
        $gLocale[ 'DeleteSetupFolder' ]        = 'If no longer needed, you should now delete the "setup" folder to avoid unwanted changes to your installtion.';
        $gLocale[ 'ThankYou' ]                 = 'Thank you for using packedpixel Raidplaner.';
        $gLocale[ 'VisitBugtracker' ]          = 'If you encounter any bugs or if you have feature requests, please visit the bugtracker at ';
        $gLocale[ 'VersionDetection' ]         = 'Version detection and update';
        $gLocale[ 'VersionDetectProgress' ]    = 'Setup will try to detect your current version.';
        $gLocale[ 'ChooseManually' ]           = 'If the detected version does not match your installed version you may always choose manually, too.';
        $gLocale[ 'OnlyDBAffected' ]           = 'The update will only affect changes in the database.';
        $gLocale[ 'NoChangeNoAction' ]         = 'If the database did not change you will not need to do this step.';
        $gLocale[ 'DetectedVersion' ]          = 'Detected version';
        $gLocale[ 'NoUpdateNecessary' ]        = 'No update necessary.';
        $gLocale[ 'UpdateFrom' ]               = 'Update from version';
        $gLocale[ 'UpdateTo' ]                 = 'to version';
        $gLocale[ 'UpdateErrors' ]             = 'Update errors';
        $gLocale[ 'ReportedErrors' ]           = 'The following errors were reported during update.';
        $gLocale[ 'PartiallyUpdated' ]         = 'This may hint on an already (partially) updated database.';
        $gLocale[ 'GameconfigNotFound' ]       = 'The file gameconfig.php could not be found.';
        $gLocale[ 'FailedGameconfig' ]         = 'The file gameconfig.php could not be translated into the new format.';
        $gLocale[ 'RemoveAndLaunch' ]          = 'Delete setup and start';
        $gLocale[ 'FailedRemoveSetup' ]        = 'The setup folder could not be deleted.';

        // Repair
        $gLocale[ 'Repair' ]                   = 'Repair';
        $gLocale[ 'RepairDone' ]               = 'Repair done.';
        $gLocale[ 'BrokenDatabase' ]           = 'Database seems to be broken';
        $gLocale[ 'EnsureValidDatabase' ]      = 'Ensure a valid database';
        $gLocale[ 'ItemsRepaired' ]            = 'Items repaired';
        $gLocale[ 'ItemsToResolve' ]           = 'Items need to be resolved manually';
        $gLocale[ 'InvalidCharacters' ]        = 'Invalid characters';
        $gLocale[ 'InvalidAttendances' ]       = 'Invalid attendances';
        $gLocale[ 'Delete' ]                   = 'Delete';
        $gLocale[ 'Resolve' ]                  = 'Resolve';
        $gLocale[ 'StrayRoles' ]               = 'Invalid roles';
        $gLocale[ 'StrayCharacters' ]          = 'Deleted characters';
        $gLocale[ 'StrayUsers' ]               = 'Deleted users';
        $gLocale[ 'StrayBindings' ]            = 'Invalid users';
        $gLocale[ 'RepairCharacters' ]         = 'Repair invalid characters';
        $gLocale[ 'TransferGameconfig' ]       = 'Convert gameconfig.php again (Raidplaner 1.0.x)';
        $gLocale[ 'MergeGames' ]               = 'Merge two games';
        $gLocale[ 'SourceGame' ]               = 'Source (gets changed)';
        $gLocale[ 'TargetGame' ]               = 'Target';
        $gLocale[ 'ChooseRepairs' ]            = 'Choose one or multiple repairs to be executed.';
        $gLocale[ 'Fixing' ]                   = 'Fixing';
        $gLocale[ 'StrayChars' ]               = 'Orphaned characters';
        $gLocale[ 'StrayAttends' ]             = 'Orphaned attends';
        $gLocale[ 'InvalidCharacters' ]        = 'Invalid characters';
        $gLocale[ 'SameGame' ]                 = 'Both games are identical';   
        $gLocale[ 'Merged' ]                   = 'Converted:'; 
        $gLocale[ 'Locations' ]                = 'Locations'; 
        $gLocale[ 'Characters' ]               = 'Characters'; 

        // Plugin setup
        $gLocale[ 'LoadGroups' ]               = 'Load groups using these settings';
        $gLocale[ 'AutoMemberLogin' ]          = '"Member" groups:';
        $gLocale[ 'AutoLeadLogin' ]            = '"Raidlead" groups:';
        $gLocale[ 'ReloadFailed' ]             = 'Reload failed';
        $gLocale[ 'LoadSettings' ]             = 'Retrieve settings automatically';
        $gLocale[ 'BindingBasePath' ]          = 'Please enter the install path of this binding relative to \''.$_SERVER['DOCUMENT_ROOT'].'\'.';
        $gLocale[ 'RetrievalFailed' ]          = 'Automatic retrieval failed';
        $gLocale[ 'RetrievalOk' ]              = 'Automatic retrieval has been successful';
        $gLocale[ 'NotExisting' ]              = 'does not exist';
        $gLocale[ 'AllowAutoLogin' ]           = 'Allow automatic login';
        $gLocale[ 'NoValidConfig' ]            = 'No valid configuration file found.';
        $gLocale[ 'CookieNote' ]               = 'Automatic login requires the raidplaner to be installed in a subfolder of this binding\'s cookie path. '.
                                                 'This path might have to be changed in this binding\'s configuration.';
        $gLocale[ 'PostToForum' ]              = 'Post new raids in this forum';
        $gLocale[ 'PostAsUser' ]               = 'Post new raids as this user';
        $gLocale[ 'DisablePosting' ]           = 'Do not create postings';
        $gLocale[ 'NoUsersFound' ]             = 'No users found';
        $gLocale[ 'Version' ]                  = 'Version';

        // PHPBB3
        $gLocale[ 'phpbb3_Binding' ]            = 'PHPBB 3.x';
        $gLocale[ 'phpbb3_ConfigFile' ]         = 'PHPBB3 config file';
        $gLocale[ 'phpbb3_Database' ]           = 'PHPBB3 database';
        $gLocale[ 'phpbb3_DatabaseEmpty' ]      = 'PHPBB3 database name must not be empty.';
        $gLocale[ 'phpbb3_UserEmpty' ]          = 'PHPBB3 user must not be empty';
        $gLocale[ 'phpbb3_PasswordEmpty' ]      = 'PHPBB3 database password must not be empty.';
        $gLocale[ 'phpbb3_DBPasswordsMatch' ]   = 'PHPBB3 database passwords did not match.';

        // EQDKP
        $gLocale[ 'eqdkp_Binding' ]             = 'EQDKP';
        $gLocale[ 'eqdkp_ConfigFile' ]          = 'EQDKP config file';
        $gLocale[ 'eqdkp_Database' ]            = 'EQDKP database';
        $gLocale[ 'eqdkp_DatabaseEmpty' ]       = 'EQDKP database name must not be empty.';
        $gLocale[ 'eqdkp_UserEmpty' ]           = 'EQDKP user must not be empty';
        $gLocale[ 'eqdkp_PasswordEmpty' ]       = 'EQDKP database password must not be empty.';
        $gLocale[ 'eqdkp_DBPasswordsMatch' ]    = 'EQDKP database passwords did not match.';

        // vBulletin
        $gLocale[ 'vb3_Binding' ]               = 'vBulletin 3 / 4';
        $gLocale[ 'vb3_ConfigFile' ]            = 'vBulletin config file';
        $gLocale[ 'vb3_Database' ]              = 'vBulletin database';
        $gLocale[ 'vb3_DatabaseEmpty' ]         = 'vBulletin database name must not be empty.';
        $gLocale[ 'vb3_UserEmpty' ]             = 'vBulletin user must not be empty';
        $gLocale[ 'vb3_PasswordEmpty' ]         = 'vBulletin Database password must not be empty.';
        $gLocale[ 'vb3_DBPasswordsMatch' ]      = 'vBulletin Database passwords did not match.';
        $gLocale[ 'vb3_CookieEx' ]              = 'vBulletin cookie prefix';

        // MyBB
        $gLocale[ 'mybb_Binding' ]              = 'MyBB 1.6+';
        $gLocale[ 'mybb_ConfigFile' ]           = 'MyBB config file';
        $gLocale[ 'mybb_Database' ]             = 'MyBB database';
        $gLocale[ 'mybb_DatabaseEmpty' ]        = 'MyBB database name must not be empty.';
        $gLocale[ 'mybb_UserEmpty' ]            = 'MyBB user must not be empty';
        $gLocale[ 'mybb_PasswordEmpty' ]        = 'MyBB Database password must not be empty.';
        $gLocale[ 'mybb_DBPasswordsMatch' ]     = 'MyBB Database passwords did not match.';

        // SMF
        $gLocale[ 'smf_Binding' ]               = 'Simple Machines Forum 2.x';
        $gLocale[ 'smf_ConfigFile' ]            = 'SMF config file';
        $gLocale[ 'smf_Database' ]              = 'SMF database';
        $gLocale[ 'smf_DatabaseEmpty' ]         = 'SMF database name must not be empty.';
        $gLocale[ 'smf_UserEmpty' ]             = 'SMF user must not be empty';
        $gLocale[ 'smf_PasswordEmpty' ]         = 'SMF Database password must not be empty.';
        $gLocale[ 'smf_DBPasswordsMatch' ]      = 'SMF Database passwords did not match.';
        $gLocale[ 'smf_CookieEx' ]              = 'SMF cookie name';

        // Vanilla
        $gLocale[ 'vanilla_Binding' ]           = 'Vanilla Forum 2.x';
        $gLocale[ 'vanilla_ConfigFile' ]        = 'Vanilla config file';
        $gLocale[ 'vanilla_Database' ]          = 'Vanilla database';
        $gLocale[ 'vanilla_DatabaseEmpty' ]     = 'Vanilla database name must not be empty.';
        $gLocale[ 'vanilla_UserEmpty' ]         = 'Vanilla user must not be empty';
        $gLocale[ 'vanilla_PasswordEmpty' ]     = 'Vanilla Database password must not be empty.';
        $gLocale[ 'vanilla_DBPasswordsMatch' ]  = 'Vanilla Database passwords did not match.';
        $gLocale[ 'vanilla_CookieEx' ]          = 'Cookie name, hash method (e.g. md5), cookie salt';

        // Joomla
        $gLocale[ 'jml3_Binding' ]              = 'Joomla 3.x';
        $gLocale[ 'jml3_ConfigFile' ]           = 'Joomla3 config file';
        $gLocale[ 'jml3_Database' ]             = 'Joomla3 database';
        $gLocale[ 'jml3_DatabaseEmpty' ]        = 'Joomla3 database name must not be empty.';
        $gLocale[ 'jml3_UserEmpty' ]            = 'Joomla3 user must not be empty';
        $gLocale[ 'jml3_PasswordEmpty' ]        = 'Joomla3 Database password must not be empty.';
        $gLocale[ 'jml3_DBPasswordsMatch' ]     = 'Joomla3 Database passwords did not match.';
        $gLocale[ 'jml3_CookieEx' ]             = 'Joomla3 secret';

        // Drupal
        $gLocale[ 'drupal_Binding' ]            = 'Drupal 7.6+';
        $gLocale[ 'drupal_ConfigFile' ]         = 'Drupal config file';
        $gLocale[ 'drupal_Database' ]           = 'Drupal database';
        $gLocale[ 'drupal_DatabaseEmpty' ]      = 'Drupal database name must not be empty.';
        $gLocale[ 'drupal_UserEmpty' ]          = 'Drupal user must not be empty';
        $gLocale[ 'drupal_PasswordEmpty' ]      = 'Drupal Database password must not be empty.';
        $gLocale[ 'drupal_DBPasswordsMatch' ]   = 'Drupal Database passwords did not match.';
        $gLocale[ 'drupal_CookieEx' ]           = 'Drupal base URL';

        // Wordpress
        $gLocale[ 'wp_Binding' ]                = 'Wordpress 3 / 4';
        $gLocale[ 'wp_ConfigFile' ]             = 'Wordpress config file';
        $gLocale[ 'wp_Database' ]               = 'Wordpress database';
        $gLocale[ 'wp_DatabaseEmpty' ]          = 'Wordpress database name must not be empty.';
        $gLocale[ 'wp_UserEmpty' ]              = 'Wordpress user must not be empty';
        $gLocale[ 'wp_PasswordEmpty' ]          = 'Wordpress Database password must not be empty.';
        $gLocale[ 'wp_DBPasswordsMatch' ]       = 'Wordpress Database passwords did not match.';
        $gLocale[ 'wp_CookieEx' ]               = 'LOGGED_IN_KEY followed by LOGGED_IN_SALT';
        
        // Woltlab Burning Board
        $gLocale[ 'wbb_Binding' ]               = 'Burning Board 4.x';
        $gLocale[ 'wbb_ConfigFile' ]            = 'Burning Board config file';
        $gLocale[ 'wbb_Database' ]              = 'Burning Board database';
        $gLocale[ 'wbb_DatabaseEmpty' ]         = 'Burning Board database name must not be empty.';
        $gLocale[ 'wbb_UserEmpty' ]             = 'Burning Board user must not be empty';
        $gLocale[ 'wbb_PasswordEmpty' ]         = 'Burning Board Database password must not be empty.';
        $gLocale[ 'wbb_DBPasswordsMatch' ]      = 'Burning Board Database passwords did not match.';
        $gLocale[ 'wbb_CookieEx' ]              = 'Burning Board Cookie Prefix';
    }
?>