<?php
    if ( defined("LOCALE_MAIN") )
    {
        // Pre-loading checks
        $gLocale[ "ContinueNoUpdate" ]         = "Ohne Aktualisierung fortfahren";
        $gLocale[ "UpdateBrowser" ]            = "Bitte aktualisiere deinen Browser";
        $gLocale[ "UsingOldBrowser" ]          = "Du verwendest eine veraltete Version deines Browsers.";
        $gLocale[ "OlderBrowserFeatures" ]     = "&Auml;ltere Browser unterst&uuml;tzen nicht alle ben&ouml;tigten Funktionen oder stellen die Seite falsch dar.";
        $gLocale[ "DownloadNewBrowser" ]       = "Du solltest deinen Browser aktualisieren oder einen der folgenden Browser herunterladen.";
        $gLocale[ "RaidplanerNotConfigured" ]  = "Der Raidplaner ist noch nicht konfiguriert oder ben&ouml;tigt ein Update.";
        $gLocale[ "PleaseRunSetup" ]           = "Bitte f&uuml;hre den <a href=\"setup\">Setup</a> aus oder befolge die Instruktionen f&uuml;r eine <a href=\"http://code.google.com/p/ppx-raidplaner/wiki/ManualSetup\">manuelle Installation</a>.";
        
        // General
        $gLocale[ "Reserved" ]                 = "Reserviert";
        $gLocale[ "Error" ]                    = "Fehler.";    
        $gLocale[ "Apply" ]                    = "&Auml;nderungen speichern";
        $gLocale[ "AccessDenied" ]             = "Zugriff verweigert";
        $gLocale[ "ForeignCharacter" ]         = "Fremder Charakter";
        $gLocale[ "DatabaseError" ]            = "Datenbank Fehler";
        $gLocale[ "Cancel" ]                   = "Abbrechen";
        $gLocale[ "Notification" ]             = "Hinweis";
        $gLocale[ "Busy" ]                     = "Inhalte werden geladen. Bitte warten.";
        $gLocale[ "RequestError" ]             = "Eine Anfrage hat einen Fehler gemeldet.";
        $gLocale[ "UnknownRequest" ]           = "Unbekannte Anfrage";
        $gLocale[ "InvalidRequest" ]           = "Ung&#252;ltige Anfrage";
        $gLocale[ "InputRequired" ]            = "Eingabe erwartet";
        $gLocale[ "UnappliedChanges" ]         = "M&ouml;chtest du die ungesicherten &Auml;nderungen verwerfen?";
        $gLocale[ "DiscardChanges" ]           = "Ja, verwerfen";
        $gLocale[ "to" ]                       = "bis";
        
        // Login und user registration
        $gLocale[ "Login" ]                    = "Anmelden";
        $gLocale[ "Logout" ]                   = "Abmelden";
        $gLocale[ "Username" ]                 = "Benutzername";
        $gLocale[ "Password" ]                 = "Passwort";
        $gLocale[ "RepeatPassword" ]           = "Passwort wiederholen";
        $gLocale[ "Register" ]                 = "Registrieren";
        $gLocale[ "EnterValidUsername" ]       = "Du musst einen g&uuml;ltigen Benutzernamen angeben.";
        $gLocale[ "EnterNonEmptyPassword" ]    = "Das Passwort darf nicht leer sein.";
        $gLocale[ "PasswordsNotMatch" ]        = "Passw&ouml;rter stimmen nicht &uuml;berein.";
        $gLocale[ "NameInUse" ]                = "Dieser Benutzername wird bereits verwendet.";
        $gLocale[ "RegistrationDone" ]         = "Registrierung erfolgreich.";
        $gLocale[ "AccountIsLocked" ]          = "Dein Account ist momentan gesperrt.";
        $gLocale[ "ContactAdminToUnlock" ]     = "Bitte wende dich an deinen Administrator um deinen Account freizuschalten.";
        $gLocale[ "NoSuchUser" ]               = "Der angegebene Benutzer konnte nicht gefunden werden.";
        $gLocale[ "HashingInProgress" ]        = "Passwort hashing";
        $gLocale[ "PassStrength"]              = "Passwortst&auml;rke";
        
        // Calendar
        $gLocale[ "Calendar" ]                 = "Kalender";
        $gLocale[ "January" ]                  = "Januar";
        $gLocale[ "February" ]                 = "Februar";
        $gLocale[ "March" ]                    = "M&auml;rz";
        $gLocale[ "April" ]                    = "April";
        $gLocale[ "May" ]                      = "Mai";
        $gLocale[ "June" ]                     = "Juni";
        $gLocale[ "July" ]                     = "Juli";
        $gLocale[ "August" ]                   = "August";
        $gLocale[ "September" ]                = "September";
        $gLocale[ "October" ]                  = "Oktober";
        $gLocale[ "November" ]                 = "November";
        $gLocale[ "December" ]                 = "Dezember";    
        $gLocale[ "Monday" ]                   = "Montag";
        $gLocale[ "Tuesday" ]                  = "Dienstag";
        $gLocale[ "Wednesday" ]                = "Mittwoch";
        $gLocale[ "Thursday" ]                 = "Donnerstag";
        $gLocale[ "Friday" ]                   = "Freitag";
        $gLocale[ "Saturday" ]                 = "Samstag";
        $gLocale[ "Sunday" ]                   = "Sonntag";          
        $gLocale[ "Mon" ]                      = "Mo";
        $gLocale[ "Tue" ]                      = "Di";
        $gLocale[ "Wed" ]                      = "Mi";
        $gLocale[ "Thu" ]                      = "Do";
        $gLocale[ "Fri" ]                      = "Fr";
        $gLocale[ "Sat" ]                      = "Sa";
        $gLocale[ "Sun" ]                      = "So";
        $gLocale[ "NotSignedUp" ]              = "Nicht angemeldet";
        $gLocale[ "Absent" ]                   = "Abgemeldet";
        $gLocale[ "QueuedAs" ]                 = "Auf der Warteliste als ";
        $gLocale[ "Raiding" ]                  = "Aufgestellt als ";    
        $gLocale[ "WhyAbsent" ]                = "Bitte teile uns mit warum du abwesend bist.";
        $gLocale[ "SetAbsent" ]                = "Ich bin abwesend";
        $gLocale[ "Comment" ]                  = "Kommentar";
        $gLocale[ "SaveComment" ]              = "Kommentar speichern";
        
        // Raid
        $gLocale[ "Raid" ]                     = "Raid";
        $gLocale[ "Upcoming" ]                 = "Anstehende Raids";
        $gLocale[ "CreateRaid" ]               = "Raid anlegen";    
        $gLocale[ "NewDungeon" ]               = "Neue Instanz";
        $gLocale[ "Description" ]              = "Beschreibung";
        $gLocale[ "DefaultRaidMode" ]          = "Voreingestellte Anmeldeform";
        $gLocale[ "RaidStatus" ]               = "Status";
        $gLocale[ "RaidOpen" ]                 = "Raid freigegeben";
        $gLocale[ "RaidLocked" ]               = "Raid gesperrt";
        $gLocale[ "RaidCanceled" ]             = "Raid abgesagt";
        $gLocale[ "DeleteRaid" ]               = "Raid entfernen";    
        $gLocale[ "ConfirmRaidDelete" ]        = "Soll dieser Raid wirklich gel&ouml;schen werden?";
        $gLocale[ "Players" ]                  = "Spieler";
        $gLocale[ "RequiredForRole" ]          = "Ben&ouml;tigt f&uuml;r Rolle";
        $gLocale[ "AbsentPlayers" ]            = "Abgemeldete Spieler";
        $gLocale[ "UndecidedPlayers" ]         = "Unentschlossene Spieler";
        $gLocale[ "AbsentNoReason" ]           = "Kein Grund angegeben.";   
        $gLocale[ "Undecided" ]                = "Hat noch keine Aussage getroffen.";
        $gLocale[ "MarkAsAbesent" ]            = "Als abwesend melden";
        $gLocale[ "MakeAbsent" ]               = "Spieler wird nicht anwesend sein";
        $gLocale[ "AbsentMessage" ]            = "Bitte gebe den Grund f&uuml;r die Abwesenheit an.<br/>Der Nachricht wird dein Login-Name vorangestellt.";
        $gLocale[ "SetupBy" ]                  = "Aufgestellt durch ";
        
        // Classes
        $gLocale[ "Deathknight" ]              = "Todesritter";
        $gLocale[ "Druid" ]                    = "Druide";
        $gLocale[ "Hunter" ]                   = "J&auml;ger";
        $gLocale[ "Mage" ]                     = "Magier";
        $gLocale[ "Monk" ]                     = "M&ouml;nch";
        $gLocale[ "Paladin" ]                  = "Paladin";
        $gLocale[ "Priest" ]                   = "Priester";
        $gLocale[ "Rogue" ]                    = "Schurke";
        $gLocale[ "Shaman" ]                   = "Schamane";
        $gLocale[ "Warlock" ]                  = "Hexenmeister";
        $gLocale[ "Warrior" ]                  = "Krieger";
        
        // Roles
        $gLocale[ "Tank" ]                     = "Tank";
        $gLocale[ "Healer" ]                   = "Heiler";
        $gLocale[ "Damage" ]                   = "Schaden";
        
        // Profile
        $gLocale[ "Profile" ]                  = "Profil";
        $gLocale[ "History" ]                  = "Vergangene Raids";
        $gLocale[ "Characters" ]               = "Deine Charaktere";
        $gLocale[ "CharName" ]                 = "Name";
        $gLocale[ "NoName" ]                   = "Einem neuen Charakter wurde kein Name zugewiesen.";
        $gLocale[ "NoClass" ]                  = "Einem neuen Charakter wurde keine Klasse zugewiesen.";
        $gLocale[ "DeleteCharacter" ]          = "Charakter entfernen";
        $gLocale[ "ConfirmDeleteCharacter" ]   = "Charakter wirklich l&ouml;schen?";
        $gLocale[ "AttendancesRemoved" ]       = "Alle bestehenden Anmeldungen werden ebenfalls gel&ouml;scht.";
        $gLocale[ "RaidAttendance" ]           = "Raidteilnahme";
        $gLocale[ "RolesInRaids" ]             = "Rollenverteilung in raids";
        $gLocale[ "Queued" ]                   = "Angemeldet";
        $gLocale[ "Attended" ]                 = "Teilgenommen";
        $gLocale[ "Missed" ]                   = "Verpasst";
        $gLocale[ "ChangePassword" ]           = "Passwort &auml;ndern";
        $gLocale[ "OldPassword" ]              = "Altes Passwort";
        $gLocale[ "OldPasswordEmpty" ]         = "Das alte Passwort darf nicht leer sein.";
        $gLocale[ "AdminPassword" ]            = "Administrator passwort";
        $gLocale[ "AdminPasswordEmpty" ]       = "Das Administrator Passwort darf nicht leer sein.";
        $gLocale[ "WrongPassword" ]            = "Falsches Passwort";
        $gLocale[ "PasswordLocked" ]           = "Passwort kann nicht ver&auml;ndert werden.";
        $gLocale[ "PasswordChanged" ]          = "Das Passwort wurde ge&auml;ndert.";
        
        // Settings           
        $gLocale[ "Settings" ]                 = "Einstellungen";
        $gLocale[ "Locked" ]                   = "Gesperrt";
        $gLocale[ "Members" ]                  = "Mitglieder";
        $gLocale[ "Raidleads" ]                = "Raidleiter";
        $gLocale[ "Administrators" ]           = "Administratoren";        
        $gLocale[ "ConfirmDeleteUser" ]        = "Willst du diesen Benutzer wirklich l&ouml;schen?";
        $gLocale[ "DeleteUser" ]               = "Benutzer entfernen";
        $gLocale[ "MoveUser" ]                 = "Benutzer verschieben";
        $gLocale[ "UnlinkUser" ]               = "Synchronisation beenden und in lokalen Nutzer konvertieren.";
        $gLocale[ "LinkUser" ]                 = "Synchronisiere Nutzer";
        $gLocale[ "SyncFailed" ]               = "Synchronisation fehlgeschlagen.</br>Es konnte kein passender Benutzer gefunden werden.";        
        $gLocale[ "EditForeignCharacters" ]    = "Charaktere bearbeiten f&uuml;r Benutzer";
        $gLocale[ "ConfirmDeleteLocation" ]    = "M&ouml;chtest du diese Instanz wirklich l&ouml;schen?";
        $gLocale[ "NoteDeleteRaidsToo" ]       = "Alle Raids in dieser Instanz werden ebenfalls gel&ouml;scht.";
        $gLocale[ "DeleteRaids" ]              = "L&ouml;sche Raids ";
        $gLocale[ "DeleteLocationRaids" ]      = "Instanz und Raids entfernen";        
        $gLocale[ "LockRaids" ]                = "Sperre Raids";
        $gLocale[ "AfterDone" ]                = "nach Ende eines Raids";
        $gLocale[ "BeforeStart" ]              = "bevor ein Raid startet";
        $gLocale[ "Seconds" ]                  = "Sekunde(n)";
        $gLocale[ "Minutes"     ]              = "Minute(n)";
        $gLocale[ "Hours" ]                    = "Stunde(n)";
        $gLocale[ "Days" ]                     = "Tag(e)";
        $gLocale[ "Weeks" ]                    = "Woche(n)";
        $gLocale[ "Month" ]                    = "Monat(e)";
        $gLocale[ "TimeFormat" ]               = "Zeitformat";
        $gLocale[ "StartOfWeek" ]              = "Die Woche beginnt am";
        $gLocale[ "DefaultStartTime" ]         = "Voreingestellter Raidstart";
        $gLocale[ "DefaultEndTime" ]           = "Voreingestelltes Raidende";
        $gLocale[ "DefaultRaidSize" ]          = "Voreingestellte Raidgr&ouml;&szlig;e";
        $gLocale[ "BannerPage" ]               = "Link f&uuml;r Seitenbanner";
        $gLocale[ "Theme" ]                    = "Thema";
        $gLocale[ "RaidSetupStyle" ]           = "Anmeldeform";        
        $gLocale[ "RaidModeManual" ]           = "Aufstellung durch Raidleiter";
        $gLocale[ "RaidModeAttend" ]           = "Aufstellung nach Anmeldung";
        $gLocale[ "RaidModeAll" ]              = "Nur Liste";        
        $gLocale[ "UpdateCheck" ]              = "Suche nach Updates";
        $gLocale[ "UpToDate" ]                 = "Dieser Raidplaner ist aktuell.";
        $gLocale[ "NewVersionAvailable" ]      = "Es ist eine neue Version verf&uuml;gbar:";
        $gLocale[ "VisitProjectPage" ]         = "Zur Projekt-Homepage";
    }
    
    if ( defined("LOCALE_SETUP") )
    {
        // General
        $gLocale[ "Ok" ]                       = "Ok";
        $gLocale[ "Back" ]                     = "Zur&uuml;ck";
        $gLocale[ "Continue" ]                 = "Weiter";
        $gLocale[ "Error" ]                    = "Fehler";
        $gLocale[ "Ignore" ]                   = "Ignorieren";
        $gLocale[ "Retry" ]                    = "Wiederholen";
        $gLocale[ "DatabaseError" ]            = "Datenbank Fehler";
        
        // Menu
        $gLocale[ "Install" ]                  = "Installieren";
        $gLocale[ "Update" ]                   = "Updaten";        
        $gLocale[ "EditBindings" ]             = "Anbindungen bearbeiten";
        $gLocale[ "EditConfig" ]               = "Konfiguration bearbeiten";
        $gLocale[ "ResetPassword" ]            = "Admin Passwort setzen";
        $gLocale[ "RepairDatabase" ]           = "Datenbank reparieren";
        
        // Checks
        $gLocale[ "FilesystemChecks" ]         = "&Uuml;berpr&uuml;fung der Dateisystemrechte.";
        $gLocale[ "NotWriteable" ]             = "Keine Schreibrechte";
        $gLocale[ "ConfigFolder" ]             = "Konfigurations-Ordner";
        $gLocale[ "MainConfigFile" ]           = "Raidplaner Konfigurationsdatei";
        $gLocale[ "DatabaseConnection" ]       = "Datenbank Konfiguration";
        $gLocale[ "WritePermissionRequired" ]  = "Setup ben&ouml;tigt Schreibrechte auf allen Datein im Konfigurationsordner. Dieser befindet sich unter ";
        $gLocale[ "ChangePermissions" ]        = "Wenn einer dieser Tests fehlschl&auml;gt musst du Schreiberechte f&uuml;r den Benutzer deines HTTP-Servers vergeben.";
        $gLocale[ "FTPClientHelp" ]            = "Wie du die Rechte &auml;ndern kannst entnimmst du am besten der Hilfe deines FTP-Programms.";
        $gLocale[ "OutdatedPHP" ]              = "Veraltete PHP Version";
        $gLocale[ "PHPVersion" ]               = "PHP Version";
        $gLocale[ "McryptModule" ]             = "mcrypt Modul";
        $gLocale[ "McryptNotFound" ]           = "PHP wurde nicht mit mcrypt konfiguriert";
        $gLocale[ "PDOModule" ]                = "PDO Modul";
        $gLocale[ "PDONotFound" ]              = "PHP wurde nicht mit PDO konfiguriert";
        $gLocale[ "PDOMySQLModule" ]           = "PDO MySQL Treiber";
        $gLocale[ "PDOMySQLNotFound" ]         = "PDO MySQL Treiber nicht gefunden";        
        $gLocale[ "PHPRequirements" ]          = "Der Raidplaner ben&ouml;tigt eine PHP 5.2 Installation die mit der mcrypt und PDO Erweiterung Konfiguriert wurde.";
        
        // Database setup
        $gLocale[ "ConfigureDatabase" ]        = "Bitte gib die Konfigurationsdaten der Datenbank an in der die Raidplaner Daten abgelegt werden k&ouml;nnen.";
        $gLocale[ "SameAsForumDatabase" ]      = "Wenn der Raidplaner an ein externes Forum gebunden werden soll, so m&uuml;ssen die Datenbanken auf dem gleichen Server liegen.";
        $gLocale[ "EnterPrefix" ]              = "Wenn die Datenank bereits von einer anderen Installation verwendet wird kann ein Pr&auml;fix angegeben werden um Namenskonflikte zu vermeiden.";
        $gLocale[ "DatabaseHost" ]             = "Adresse des Datenbankservers";
        $gLocale[ "RaidplanerDatabase" ]       = "Name der Raidplaner Datenbank";
        $gLocale[ "UserWithDBPermissions" ]    = "Benutzer mit Zugriffsrechten auf diese Datenbank";
        $gLocale[ "UserPassword" ]             = "Das Passwort f&uuml;r diesen Benutzer";
        $gLocale[ "RepeatPassword" ]           = "Passwort bitte wiederholen";
        $gLocale[ "TablePrefix" ]              = "Prefix f&uuml;r die Tabellen in der Datenbank";
        $gLocale[ "VerifySettings" ]           = "&Uuml;berpr&uuml;fe diese Einstellung";
        $gLocale[ "ConnectionTestFailed" ]     = "Verbindungstest fehlgeschlagen";
        $gLocale[ "ConnectionTestOk" ]         = "Verbindungstest erfolgreich";
        
        // Registration and admin
        $gLocale[ "AdminPassword" ]            = "Passwort f&uuml;r den Administrator";
        $gLocale[ "AdminPasswordSetup"]        = "Der Administrator (Anmeldename \"admin\") ist ein Benutzer der immer alle verf&uuml;gbaren Rechte besitzt.";
        $gLocale[ "AdminNotMoveable"]          = "Der Nutzer admin kann nicht umbenannt oder in eine andere Gruppe verschoben werden.";
        $gLocale[ "AdminPasswordNoMatch" ]     = "Administrator-Passw&ouml;rter stimmen nicht &uuml;berein";
        $gLocale[ "AdminPasswordEmpty" ]       = "Administrator-Passwort darf nicht leer sein";
        $gLocale[ "DatabasePasswordNoMatch" ]  = "Datanebnk-Passw&ouml;rter stimmen nicht &uuml;berein";
        $gLocale[ "DatabasePasswordEmpty" ]    = "Datenbank-Passwort darf nicht leer sein";
        $gLocale[ "AllowManualRegistration" ]  = "Erlaube es Nutzern sich manuell zu registrieren";
        $gLocale[ "AllowGroupSync" ]           = "Gruppen externer Nutzer synchronisieren";
        $gLocale[ "AllowPublicMode" ]          = "Neue Benutzer als Mitglieder registrieren (nicht empfohlen)";
        $gLocale[ "UseClearText" ]             = "&Uuml;bertragung von Klartext Passw&ouml;rtern (nicht empfohlen)";
        
        // Install/Update
        $gLocale[ "SetupComplete" ]            = "Installation abgeschlossen";
        $gLocale[ "UpdateComplete" ]           = "Update abgeschlossen";
        $gLocale[ "RaidplanerSetupDone" ]      = "Der Raidplaner ist jetzt erfolgreich konfiguriert.";
        $gLocale[ "DeleteSetupFolder" ]        = "Du solltest nun den \"setup\" Ordner l&ouml;schen und die folgenden Ordner (durch z.B. htaccess) sch&uuml;tzen:";
        $gLocale[ "ThankYou" ]                 = "Danke das du den packedpixel Raidplaner verwendest.";
        $gLocale[ "VisitBugtracker" ]          = "Bei Fehlern oder Ideen f&uuml;r neue Features besuche bitte unseren Bugtracker unter ";
        $gLocale[ "VersionDetection" ]         = "Versionserkennung und Update";
        $gLocale[ "VersionDetectProgress" ]    = "Setup versucht die aktuelle Version zu erkennen.";
        $gLocale[ "ChooseManually" ]           = "Wenn die erkannte Version nicht der installierten Version entspricht kann die korrekte Version manuell ausgew&auml;hlt werden.";
        $gLocale[ "OnlyDBAffected" ]           = "Das update betrifft nur &auml;nderungen in der Datenbank.";
        $gLocale[ "NoChangeNoAction" ]         = "Wurde die Datenbank nicht ver&auml;ndert, kann dieser Schritt &uuml;bersprungen werden.";
        $gLocale[ "DetectedVersion" ]          = "Erkannte Version";
        $gLocale[ "NoUpdateNecessary" ]        = "Kein Update notwendig";
        $gLocale[ "UpdateFrom" ]               = "Update der Version";
        $gLocale[ "UpdateTo" ]                 = "auf Version";
        $gLocale[ "UpdateErrors" ]             = "Fehler w&auml;hrend des Updates";
        $gLocale[ "ReportedErrors" ]           = "Die folgenden Fehler wurden w&auml;hrend des updates gemeldet.";
        $gLocale[ "PartiallyUpdated" ]         = "Dies kann auf eine bereits (teilweise) upgedatete Datenbank hinweisen.";
        
        // Repair
        $gLocale[ "Repair" ]                   = "Datenbank Inkonsistenzen reparieren";
        $gLocale[ "GameconfigProblems" ]       = "Durch Ver&auml;nderungen in der lib/gameconfig.php k&ouml;nnen ung&uuml;tige Datenbankeintr&auml;ge entstehen (z.B. Charaktere mit ung&uuml;tigen Rollen).";
        $gLocale[ "RepairTheseProblems" ]      = "Dieses Script behebt diese Probleme so gut wie m&ouml;glich.";
        $gLocale[ "RepairDone" ]               = "Reparatur abgeschlossen.";
        $gLocale[ "BrokenDatabase" ]           = "Die Datenbank scheint defekt zu sein";
        $gLocale[ "EnsureValidDatabase" ]      = "Korrektheit der Datenbank sicherstellen";
        $gLocale[ "ItemsRepaired" ]            = "Eintr&auml;ge repariert";
        $gLocale[ "ItemsToResolve" ]           = "Eintr&auml;ge m&uuml;ssen manuell aufgel&ouml;st werden";        
        $gLocale[ "InvalidCharacters" ]        = "Ung&uuml;ltige Charaktere";
        $gLocale[ "InvalidAttendances" ]       = "Ung&uuml;ltige Anmeldungen";
        $gLocale[ "Delete" ]                   = "L&ouml;schen";
        $gLocale[ "Resolve" ]                  = "Aufl&ouml;sen";
        $gLocale[ "StrayRoles" ]               = "Ung&uuml;ltige Rollen";
        $gLocale[ "StrayCharacters" ]          = "Gel&ouml;schte Charaktere";
        $gLocale[ "StrayUsers" ]               = "Gel&ouml;schte Benutzer";
        
        // Plugin setup
        $gLocale[ "LoadGroups" ]               = "Lade Gruppen mit den angegebenen Verbindungsdaten";
        $gLocale[ "AutoMemberLogin" ]          = "Benutzer der folgenden, ausgew&auml;lten Gruppe(n) werden als \"Mitglieder\" angemeldet:"; 
        $gLocale[ "AutoLeadLogin" ]            = "Benutzer der folgenden, ausgew&auml;lten Gruppe(n) werden als \"Raidleiter\" angemeldet:";
        $gLocale[ "ReloadFailed" ]             = "Ladevorgang fehlgeschlagen";
        
        // PHPBB3 
        $gLocale[ "PHPBB3Binding" ]            = "PHPBB3";
        $gLocale[ "PHPBB3ConfigFile" ]         = "PHPBB3 Konfigurationsdatei";
        $gLocale[ "PHPBB3Database" ]           = "Name der PHPBB3 Datenbank";
        $gLocale[ "PHPBBPasswordEmpty" ]       = "PHPBB Datenbank Passwort darf nicht leer sein";
        $gLocale[ "PHPBBDBPasswordsMatch" ]    = "PHPBB Datenbank-Passwoerter stimmen nicht &uuml;berein";
        
        // EQDKP
        $gLocale[ "EQDKPBinding" ]             = "EQDKP";
        $gLocale[ "EQDKPConfigFile" ]          = "EQDKP Konfigurationsdatei";
        $gLocale[ "EQDKPDatabase" ]            = "Name der EQDKP Datenbank";
        $gLocale[ "EQDKPPasswordEmpty" ]       = "EQDKP Datenbank Passwort darf nicht leer sein";
        $gLocale[ "EQDKPDBPasswordsMatch" ]    = "EQDKP Datenbank-Passw&ouml;rter stimmen nicht &uuml;berein";
        
        // vBulletin
        $gLocale[ "VBulletinBinding" ]         = "vBulletin3";
        $gLocale[ "VBulletinConfigFile" ]      = "vBulletin Konfigurationsdatei";
        $gLocale[ "VBulletinDatabase" ]        = "Name der vBulletin Datenbank";
        $gLocale[ "VBulletinPasswordEmpty" ]   = "vBulletin Datenbank Passwort darf nicht leer sein";
        $gLocale[ "VBulletinDBPasswordsMatch" ]= "vBulletin Datenbank-Passw&ouml;rter stimmen nicht &uuml;berein";
        
        // MyBB
        $gLocale[ "MyBBBinding" ]              = "MyBB";
        $gLocale[ "MyBBConfigFile" ]           = "MyBB Konfigurationsdatei";
        $gLocale[ "MyBBDatabase" ]             = "Name der MyBB Datenbank";
        $gLocale[ "MyBBPasswordEmpty" ]        = "MyBB Datenbank Passwort darf nicht leer sein.";
        $gLocale[ "MyBBDBPasswordsMatch" ]     = "MyBB Datenbank-Passw&ouml;rter stimmen nicht &uuml;berein.";        
        
        // SMF
        $gLocale[ "SMFBinding" ]               = "SMF";
        $gLocale[ "SMFConfigFile" ]            = "SMF Konfigurationsdatei";
        $gLocale[ "SMFDatabase" ]              = "Name der SMF Datenbank";
        $gLocale[ "SMFPasswordEmpty" ]         = "SMF Datenbank Passwort darf nicht leer sein.";
        $gLocale[ "SMFDBPasswordsMatch" ]      = "SMF Datenbank-Passw&ouml;rter stimmen nicht &uuml;berein.";        
        
        // Vanilla
        $gLocale[ "VanillaBinding" ]           = "Vanilla";
        $gLocale[ "VanillaConfigFile" ]        = "Vanilla Konfigurationsdatei";
        $gLocale[ "VanillaDatabase" ]          = "Name der Vanilla Datenbank";
        $gLocale[ "VanillaPasswordEmpty" ]     = "Vanilla Datenbank Passwort darf nicht leer sein.";
        $gLocale[ "VanillaDBPasswordsMatch" ]  = "Vanilla Datenbank-Passw&ouml;rter stimmen nicht &uuml;berein.";       
        
        // Joomla
        $gLocale[ "JoomlaBinding" ]            = "Joomla3";
        $gLocale[ "JoomlaConfigFile" ]         = "Joomla3 Konfigurationsdatei";
        $gLocale[ "JoomlaDatabase" ]           = "Name der Joomla3 Datenbank";
        $gLocale[ "JoomlaPasswordEmpty" ]      = "Joomla3 Datenbank Passwort darf nicht leer sein.";
        $gLocale[ "JoomlaDBPasswordsMatch" ]   = "Joomla3 Datenbank-Passw&ouml;rter stimmen nicht &uuml;berein.";      
        
        // Drupal
        $gLocale[ "DrupalBinding" ]            = "Drupal";
        $gLocale[ "DrupalConfigFile" ]         = "Drupal Konfigurationsdatei";
        $gLocale[ "DrupalDatabase" ]           = "Name der Drupal Datenbank";
        $gLocale[ "DrupalPasswordEmpty" ]      = "Drupal Datenbank Passwort darf nicht leer sein.";
        $gLocale[ "DrupalDBPasswordsMatch" ]   = "Drupal Datenbank-Passw&ouml;rter stimmen nicht &uuml;berein.";
    }
?>