<?php
    if ( defined("LOCALE_MAIN") )
    {
        
        // Classes
        $gLocale[ "Deathknight" ]              = "Todesritter";
        $gLocale[ "Druid" ]                    = "Druide";
        $gLocale[ "Hunter" ]                   = "Jäger";
        $gLocale[ "Mage" ]                     = "Magier";
        $gLocale[ "Monk" ]                     = "Mönch";
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
        
        // Pre-loading checks
        $gLocale[ "ContinueNoUpdate" ]         = "Ohne Aktualisierung fortfahren";
        $gLocale[ "UpdateBrowser" ]            = "Bitte aktualisiere deinen Browser";
        $gLocale[ "UsingOldBrowser" ]          = "Du verwendest eine veraltete Version deines Browsers.";
        $gLocale[ "OlderBrowserFeatures" ]     = "Ältere Browser unterstützen nicht alle benötigten Funktionen oder stellen die Seite falsch dar.";
        $gLocale[ "DownloadNewBrowser" ]       = "Du solltest deinen Browser aktualisieren oder einen der folgenden Browser herunterladen.";
        $gLocale[ "RaidplanerNotConfigured" ]  = "Der Raidplaner ist noch nicht konfiguriert oder benötigt ein Update.";
        $gLocale[ "PleaseRunSetup" ]           = "Bitte führe den <a href=\"setup\">Setup</a> aus oder befolge die Instruktionen für eine <a href=\"http://code.google.com/p/ppx-raidplaner/wiki/ManualSetup\">manuelle Installation</a>.";
        
        // General
        $gLocale[ "Reserved" ]                 = "Reserviert";
        $gLocale[ "Error" ]                    = "Fehler.";    
        $gLocale[ "Apply" ]                    = "Änderungen speichern";
        $gLocale[ "AccessDenied" ]             = "Zugriff verweigert";
        $gLocale[ "ForeignCharacter" ]         = "Fremder Charakter";
        $gLocale[ "DatabaseError" ]            = "Datenbank Fehler";
        $gLocale[ "Cancel" ]                   = "Abbrechen";
        $gLocale[ "Notification" ]             = "Hinweis";
        $gLocale[ "Busy" ]                     = "Inhalte werden geladen. Bitte warten.";
        $gLocale[ "RequestError" ]             = "Eine Anfrage hat einen Fehler gemeldet.";
        $gLocale[ "UnknownRequest" ]           = "Unbekannte Anfrage";
        $gLocale[ "InvalidRequest" ]           = "Ungültige Anfrage";
        $gLocale[ "InputRequired" ]            = "Eingabe erwartet";
        $gLocale[ "UnappliedChanges" ]         = "Möchtest du die ungesicherten Änderungen verwerfen?";
        $gLocale[ "DiscardChanges" ]           = "Ja, verwerfen";
        $gLocale[ "to" ]                       = "bis";
        
        // Login und user registration
        $gLocale[ "Login" ]                    = "Anmelden";
        $gLocale[ "Logout" ]                   = "Abmelden";
        $gLocale[ "Username" ]                 = "Benutzername";
        $gLocale[ "Password" ]                 = "Passwort";
        $gLocale[ "RepeatPassword" ]           = "Passwort wiederholen";
        $gLocale[ "Register" ]                 = "Registrieren";
        $gLocale[ "EnterValidUsername" ]       = "Du musst einen gültigen Benutzernamen angeben.";
        $gLocale[ "EnterNonEmptyPassword" ]    = "Das Passwort darf nicht leer sein.";
        $gLocale[ "PasswordsNotMatch" ]        = "Passwörter stimmen nicht überein.";
        $gLocale[ "NameInUse" ]                = "Dieser Benutzername wird bereits verwendet.";
        $gLocale[ "RegistrationDone" ]         = "Registrierung erfolgreich.";
        $gLocale[ "AccountIsLocked" ]          = "Dein Account ist momentan gesperrt.";
        $gLocale[ "ContactAdminToUnlock" ]     = "Bitte wende dich an deinen Administrator um deinen Account freizuschalten.";
        $gLocale[ "NoSuchUser" ]               = "Der angegebene Benutzer konnte nicht gefunden werden.";
        $gLocale[ "HashingInProgress" ]        = "Passwort hashing";
        $gLocale[ "PassStrength"]              = "Passwortstärke";
        
        // Calendar
        $gLocale[ "Calendar" ]                 = "Kalender";
        $gLocale[ "January" ]                  = "Januar";
        $gLocale[ "February" ]                 = "Februar";
        $gLocale[ "March" ]                    = "März";
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
        $gLocale[ "ConfirmRaidDelete" ]        = "Soll dieser Raid wirklich gelöschen werden?";
        $gLocale[ "Players" ]                  = "Spieler";
        $gLocale[ "RequiredForRole" ]          = "Benötigt für Rolle";
        $gLocale[ "AbsentPlayers" ]            = "Abgemeldete Spieler";
        $gLocale[ "UndecidedPlayers" ]         = "Unentschlossene Spieler";
        $gLocale[ "AbsentNoReason" ]           = "Kein Grund angegeben.";   
        $gLocale[ "Undecided" ]                = "Hat noch keine Aussage getroffen.";
        $gLocale[ "MarkAsAbesent" ]            = "Als abwesend melden";
        $gLocale[ "MakeAbsent" ]               = "Spieler wird nicht anwesend sein";
        $gLocale[ "AbsentMessage" ]            = "Bitte gebe den Grund für die Abwesenheit an.<br/>Der Nachricht wird dein Login-Name vorangestellt.";
        $gLocale[ "SetupBy" ]                  = "Aufgestellt";
        $gLocale[ "SwitchChar" ]               = "Character gewechselt";
        
        // Profile
        $gLocale[ "Profile" ]                  = "Profil";
        $gLocale[ "History" ]                  = "Vergangene Raids";
        $gLocale[ "Characters" ]               = "Deine Charaktere";
        $gLocale[ "CharName" ]                 = "Name";
        $gLocale[ "NoName" ]                   = "Einem neuen Charakter wurde kein Name zugewiesen.";
        $gLocale[ "NoClass" ]                  = "Einem neuen Charakter wurde keine Klasse zugewiesen.";
        $gLocale[ "DeleteCharacter" ]          = "Charakter entfernen";
        $gLocale[ "ConfirmDeleteCharacter" ]   = "Charakter wirklich löschen?";
        $gLocale[ "AttendancesRemoved" ]       = "Alle bestehenden Anmeldungen werden ebenfalls gelöscht.";
        $gLocale[ "RaidAttendance" ]           = "Raidteilnahme";
        $gLocale[ "RolesInRaids" ]             = "Rollenverteilung in raids";
        $gLocale[ "Queued" ]                   = "Angemeldet";
        $gLocale[ "Attended" ]                 = "Teilgenommen";
        $gLocale[ "Missed" ]                   = "Verpasst";
        $gLocale[ "ChangePassword" ]           = "Passwort ändern";
        $gLocale[ "OldPassword" ]              = "Altes Passwort";
        $gLocale[ "OldPasswordEmpty" ]         = "Das alte Passwort darf nicht leer sein.";
        $gLocale[ "AdminPassword" ]            = "Administrator passwort";
        $gLocale[ "AdminPasswordEmpty" ]       = "Das Administrator Passwort darf nicht leer sein.";
        $gLocale[ "WrongPassword" ]            = "Falsches Passwort";
        $gLocale[ "PasswordLocked" ]           = "Passwort kann nicht verändert werden.";
        $gLocale[ "PasswordChanged" ]          = "Das Passwort wurde geändert.";
        
        // Settings           
        $gLocale[ "Settings" ]                 = "Einstellungen";
        $gLocale[ "Locked" ]                   = "Gesperrt";
        $gLocale[ "Members" ]                  = "Mitglieder";
        $gLocale[ "Raidleads" ]                = "Raidleiter";
        $gLocale[ "Administrators" ]           = "Administratoren";        
        $gLocale[ "ConfirmDeleteUser" ]        = "Willst du diesen Benutzer wirklich löschen?";
        $gLocale[ "DeleteUser" ]               = "Benutzer entfernen";
        $gLocale[ "MoveUser" ]                 = "Benutzer verschieben";
        $gLocale[ "UnlinkUser" ]               = "Synchronisation beenden und in lokalen Nutzer konvertieren.";
        $gLocale[ "LinkUser" ]                 = "Synchronisiere Nutzer";
        $gLocale[ "SyncFailed" ]               = "Synchronisation fehlgeschlagen.</br>Es konnte kein passender Benutzer gefunden werden.";        
        $gLocale[ "EditForeignCharacters" ]    = "Charaktere bearbeiten für Benutzer";
        $gLocale[ "ConfirmDeleteLocation" ]    = "Möchtest du diese Instanz wirklich löschen?";
        $gLocale[ "NoteDeleteRaidsToo" ]       = "Alle Raids in dieser Instanz werden ebenfalls gelöscht.";
        $gLocale[ "DeleteRaids" ]              = "Lösche Raids ";
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
        $gLocale[ "DefaultRaidSize" ]          = "Voreingestellte Raidgröße";
        $gLocale[ "BannerPage" ]               = "Link für Seitenbanner";
        $gLocale[ "HelpPage" ]                 = "Link für Hilfe";
        $gLocale[ "Theme" ]                    = "Thema";
        $gLocale[ "RaidSetupStyle" ]           = "Anmeldeform";
        $gLocale[ "RaidModeManual" ]           = "Aufstellung durch Raidleiter";
        $gLocale[ "RaidModeOverbook" ]         = "Durch Raidleiter mit Überbuchen";
        $gLocale[ "RaidModeAttend" ]           = "Aufstellung nach Anmeldung";
        $gLocale[ "RaidModeAll" ]              = "Nur Liste";        
        $gLocale[ "UpdateCheck" ]              = "Suche nach Updates";
        $gLocale[ "UpToDate" ]                 = "Dieser Raidplaner ist aktuell.";
        $gLocale[ "NewVersionAvailable" ]      = "Es ist eine neue Version verfügbar:";
        $gLocale[ "VisitProjectPage" ]         = "Zur Projekt-Homepage";
    }
    
    if ( defined("LOCALE_SETUP") )
    {
        // General
        $gLocale[ "Ok" ]                       = "Ok";
        $gLocale[ "Back" ]                     = "Zurück";
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
        $gLocale[ "FilesystemChecks" ]         = "Überprüfung der Dateisystemrechte.";
        $gLocale[ "NotWriteable" ]             = "Keine Schreibrechte";
        $gLocale[ "ConfigFolder" ]             = "Konfigurations-Ordner";
        $gLocale[ "MainConfigFile" ]           = "Raidplaner Konfigurationsdatei";
        $gLocale[ "DatabaseConnection" ]       = "Datenbank Konfiguration";
        $gLocale[ "WritePermissionRequired" ]  = "Setup benötigt Schreibrechte auf allen Datein im Konfigurationsordner. Dieser befindet sich unter ";
        $gLocale[ "ChangePermissions" ]        = "Wenn einer dieser Tests fehlschlägt musst du Schreiberechte für den Benutzer deines HTTP-Servers vergeben.";
        $gLocale[ "FTPClientHelp" ]            = "Wie du die Rechte ändern kannst entnimmst du am besten der Hilfe deines FTP-Programms.";
        $gLocale[ "OutdatedPHP" ]              = "Veraltete PHP Version";
        $gLocale[ "PHPVersion" ]               = "PHP Version";
        $gLocale[ "McryptModule" ]             = "mcrypt Modul";
        $gLocale[ "McryptNotFound" ]           = "PHP wurde nicht mit mcrypt konfiguriert";
        $gLocale[ "PDOModule" ]                = "PDO Modul";
        $gLocale[ "PDONotFound" ]              = "PHP wurde nicht mit PDO konfiguriert";
        $gLocale[ "PDOMySQLModule" ]           = "PDO MySQL Treiber";
        $gLocale[ "PDOMySQLNotFound" ]         = "PDO MySQL Treiber nicht gefunden";        
        $gLocale[ "PHPRequirements" ]          = "Der Raidplaner benötigt eine PHP 5.2 Installation die mit der mcrypt und PDO Erweiterung Konfiguriert wurde.";
        
        // Database setup
        $gLocale[ "ConfigureDatabase" ]        = "Bitte gib die Konfigurationsdaten der Datenbank an in der die Raidplaner Daten abgelegt werden können.";
        $gLocale[ "SameAsForumDatabase" ]      = "Wenn der Raidplaner an ein externes Forum gebunden werden soll, so müssen die Datenbanken auf dem gleichen Server liegen.";
        $gLocale[ "EnterPrefix" ]              = "Wenn die Datenank bereits von einer anderen Installation verwendet wird kann ein Präfix angegeben werden um Namenskonflikte zu vermeiden.";
        $gLocale[ "DatabaseHost" ]             = "Adresse des Datenbankservers";
        $gLocale[ "RaidplanerDatabase" ]       = "Name der Raidplaner Datenbank";
        $gLocale[ "UserWithDBPermissions" ]    = "Benutzer mit Zugriffsrechten auf diese Datenbank";
        $gLocale[ "UserPassword" ]             = "Das Passwort für diesen Benutzer";
        $gLocale[ "RepeatPassword" ]           = "Passwort bitte wiederholen";
        $gLocale[ "TablePrefix" ]              = "Prefix für die Tabellen in der Datenbank";
        $gLocale[ "VerifySettings" ]           = "Überprüfe diese Einstellung";
        $gLocale[ "ConnectionTestFailed" ]     = "Verbindungstest fehlgeschlagen";
        $gLocale[ "ConnectionTestOk" ]         = "Verbindungstest erfolgreich";
        
        // Registration and admin
        $gLocale[ "AdminPassword" ]            = "Passwort für den Administrator";
        $gLocale[ "AdminPasswordSetup"]        = "Der Administrator (Anmeldename \"admin\") ist ein Benutzer der immer alle verfügbaren Rechte besitzt.";
        $gLocale[ "AdminNotMoveable"]          = "Der Nutzer admin kann nicht umbenannt oder in eine andere Gruppe verschoben werden.";
        $gLocale[ "AdminPasswordNoMatch" ]     = "Administrator-Passwörter stimmen nicht überein";
        $gLocale[ "AdminPasswordEmpty" ]       = "Administrator-Passwort darf nicht leer sein";
        $gLocale[ "DatabasePasswordNoMatch" ]  = "Datanebnk-Passwörter stimmen nicht überein";
        $gLocale[ "DatabasePasswordEmpty" ]    = "Datenbank-Passwort darf nicht leer sein";
        $gLocale[ "AllowManualRegistration" ]  = "Erlaube es Nutzern sich manuell zu registrieren";
        $gLocale[ "AllowGroupSync" ]           = "Gruppen externer Nutzer synchronisieren";
        $gLocale[ "AllowPublicMode" ]          = "Neue Benutzer als Mitglieder registrieren (nicht empfohlen)";
        $gLocale[ "UseClearText" ]             = "Übertragung von Klartext Passwörtern (nicht empfohlen)";
        
        // Install/Update
        $gLocale[ "SetupComplete" ]            = "Installation abgeschlossen";
        $gLocale[ "UpdateComplete" ]           = "Update abgeschlossen";
        $gLocale[ "RaidplanerSetupDone" ]      = "Der Raidplaner ist jetzt erfolgreich konfiguriert.";
        $gLocale[ "DeleteSetupFolder" ]        = "Du solltest nun den \"setup\" Ordner löschen und die folgenden Ordner (durch z.B. htaccess) schützen:";
        $gLocale[ "ThankYou" ]                 = "Danke das du den packedpixel Raidplaner verwendest.";
        $gLocale[ "VisitBugtracker" ]          = "Bei Fehlern oder Ideen für neue Features besuche bitte unseren Bugtracker unter ";
        $gLocale[ "VersionDetection" ]         = "Versionserkennung und Update";
        $gLocale[ "VersionDetectProgress" ]    = "Setup versucht die aktuelle Version zu erkennen.";
        $gLocale[ "ChooseManually" ]           = "Wenn die erkannte Version nicht der installierten Version entspricht kann die korrekte Version manuell ausgewählt werden.";
        $gLocale[ "OnlyDBAffected" ]           = "Das update betrifft nur änderungen in der Datenbank.";
        $gLocale[ "NoChangeNoAction" ]         = "Wurde die Datenbank nicht verändert, kann dieser Schritt übersprungen werden.";
        $gLocale[ "DetectedVersion" ]          = "Erkannte Version";
        $gLocale[ "NoUpdateNecessary" ]        = "Kein Update notwendig";
        $gLocale[ "UpdateFrom" ]               = "Update der Version";
        $gLocale[ "UpdateTo" ]                 = "auf Version";
        $gLocale[ "UpdateErrors" ]             = "Fehler während des Updates";
        $gLocale[ "ReportedErrors" ]           = "Die folgenden Fehler wurden während des updates gemeldet.";
        $gLocale[ "PartiallyUpdated" ]         = "Dies kann auf eine bereits (teilweise) upgedatete Datenbank hinweisen.";
        
        // Repair
        $gLocale[ "Repair" ]                   = "Datenbank Inkonsistenzen reparieren";
        $gLocale[ "GameconfigProblems" ]       = "Durch Veränderungen in der lib/gameconfig.php können ungütige Datenbankeinträge entstehen (z.B. Charaktere mit ungütigen Rollen).";
        $gLocale[ "RepairTheseProblems" ]      = "Dieses Script behebt diese Probleme so gut wie möglich.";
        $gLocale[ "RepairDone" ]               = "Reparatur abgeschlossen.";
        $gLocale[ "BrokenDatabase" ]           = "Die Datenbank scheint defekt zu sein";
        $gLocale[ "EnsureValidDatabase" ]      = "Korrektheit der Datenbank sicherstellen";
        $gLocale[ "ItemsRepaired" ]            = "Einträge repariert";
        $gLocale[ "ItemsToResolve" ]           = "Einträge müssen manuell aufgelöst werden";        
        $gLocale[ "InvalidCharacters" ]        = "Ungültige Charaktere";
        $gLocale[ "InvalidAttendances" ]       = "Ungültige Anmeldungen";
        $gLocale[ "Delete" ]                   = "Löschen";
        $gLocale[ "Resolve" ]                  = "Auflösen";
        $gLocale[ "StrayRoles" ]               = "Ungültige Rollen";
        $gLocale[ "StrayCharacters" ]          = "Gelöschte Charaktere";
        $gLocale[ "StrayUsers" ]               = "Gelöschte Benutzer";
        $gLocale[ "StrayBindings" ]            = "Ungütige Benutzer";
        
        // Plugin setup
        $gLocale[ "LoadGroups" ]               = "Lade Gruppen mit den angegebenen Verbindungsdaten";
        $gLocale[ "AutoMemberLogin" ]          = "Benutzer der folgenden, ausgewälten Gruppe(n) werden als \"Mitglieder\" angemeldet:"; 
        $gLocale[ "AutoLeadLogin" ]            = "Benutzer der folgenden, ausgewälten Gruppe(n) werden als \"Raidleiter\" angemeldet:";
        $gLocale[ "ReloadFailed" ]             = "Ladevorgang fehlgeschlagen";
        
        // PHPBB3 
        $gLocale[ "PHPBB3Binding" ]            = "PHPBB3";
        $gLocale[ "PHPBB3ConfigFile" ]         = "PHPBB3 Konfigurationsdatei";
        $gLocale[ "PHPBB3Database" ]           = "Name der PHPBB3 Datenbank";
        $gLocale[ "PHPBB3DatabaseEmpty" ]      = "Name der PHPBB3 Datenbank darf nicht leer sein";
        $gLocale[ "PHPBB3UserEmpty" ]          = "PHPBB3 Benutzer darf nicht leer sein";
        $gLocale[ "PHPBB3PasswordEmpty" ]      = "PHPBB3 Datenbank Passwort darf nicht leer sein";
        $gLocale[ "PHPBB3DBPasswordsMatch" ]   = "PHPBB3 Datenbank-Passwoerter stimmen nicht überein";
        
        // EQDKP
        $gLocale[ "EQDKPBinding" ]             = "EQDKP";
        $gLocale[ "EQDKPConfigFile" ]          = "EQDKP Konfigurationsdatei";
        $gLocale[ "EQDKPDatabase" ]            = "Name der EQDKP Datenbank";
        $gLocale[ "EQDKPDatabaseEmpty" ]       = "Name der EQDKP Datenbank darf nicht leer sein";
        $gLocale[ "EQDKPUserEmpty" ]           = "EQDKP Benutzer darf nicht leer sein";
        $gLocale[ "EQDKPPasswordEmpty" ]       = "EQDKP Datenbank Passwort darf nicht leer sein";
        $gLocale[ "EQDKPDBPasswordsMatch" ]    = "EQDKP Datenbank-Passwörter stimmen nicht überein";
        
        // vBulletin
        $gLocale[ "VBulletinBinding" ]         = "vBulletin3";
        $gLocale[ "VBulletinConfigFile" ]      = "vBulletin Konfigurationsdatei";
        $gLocale[ "VBulletinDatabase" ]        = "Name der vBulletin Datenbank";
        $gLocale[ "VBulletinDatabaseEmpty" ]   = "Name der vBulletin Datenbank darf nicht leer sein";
        $gLocale[ "VBulletinUserEmpty" ]       = "vBulletin Benutzer darf nicht leer sein";
        $gLocale[ "VBulletinPasswordEmpty" ]   = "vBulletin Datenbank Passwort darf nicht leer sein";
        $gLocale[ "VBulletinDBPasswordsMatch" ]= "vBulletin Datenbank-Passwörter stimmen nicht überein";
        
        // MyBB
        $gLocale[ "MyBBBinding" ]              = "MyBB";
        $gLocale[ "MyBBConfigFile" ]           = "MyBB Konfigurationsdatei";
        $gLocale[ "MyBBDatabase" ]             = "Name der MyBB Datenbank";
        $gLocale[ "MyBBDatabaseEmpty" ]        = "Name der MyBB Datenbank darf nicht leer sein";
        $gLocale[ "MyBBUserEmpty" ]            = "MyBB Benutzer darf nicht leer sein";
        $gLocale[ "MyBBPasswordEmpty" ]        = "MyBB Datenbank Passwort darf nicht leer sein.";
        $gLocale[ "MyBBDBPasswordsMatch" ]     = "MyBB Datenbank-Passwörter stimmen nicht überein.";        
        
        // SMF
        $gLocale[ "SMFBinding" ]               = "SMF";
        $gLocale[ "SMFConfigFile" ]            = "SMF Konfigurationsdatei";
        $gLocale[ "SMFDatabase" ]              = "Name der SMF Datenbank";
        $gLocale[ "SMFDatabaseEmpty" ]         = "Name der SMF Datenbank darf nicht leer sein";
        $gLocale[ "SMFUserEmpty" ]             = "SMF Benutzer darf nicht leer sein";
        $gLocale[ "SMFPasswordEmpty" ]         = "SMF Datenbank Passwort darf nicht leer sein.";
        $gLocale[ "SMFDBPasswordsMatch" ]      = "SMF Datenbank-Passwörter stimmen nicht überein.";        
        
        // Vanilla
        $gLocale[ "VanillaBinding" ]           = "Vanilla";
        $gLocale[ "VanillaConfigFile" ]        = "Vanilla Konfigurationsdatei";
        $gLocale[ "VanillaDatabase" ]          = "Name der Vanilla Datenbank";
        $gLocale[ "VanillaDatabaseEmpty" ]     = "Name der Vanilla Datenbank darf nicht leer sein";
        $gLocale[ "VanillaUserEmpty" ]         = "Vanilla Benutzer darf nicht leer sein";
        $gLocale[ "VanillaPasswordEmpty" ]     = "Vanilla Datenbank Passwort darf nicht leer sein.";
        $gLocale[ "VanillaDBPasswordsMatch" ]  = "Vanilla Datenbank-Passwörter stimmen nicht überein.";       
        
        // Joomla
        $gLocale[ "JoomlaBinding" ]            = "Joomla3";
        $gLocale[ "JoomlaConfigFile" ]         = "Joomla3 Konfigurationsdatei";
        $gLocale[ "JoomlaDatabase" ]           = "Name der Joomla3 Datenbank";
        $gLocale[ "JoomlaDatabaseEmpty" ]      = "Name der Joomla3 Datenbank darf nicht leer sein";
        $gLocale[ "JoomlaUserEmpty" ]          = "Joomla3 Benutzer darf nicht leer sein";
        $gLocale[ "JoomlaPasswordEmpty" ]      = "Joomla3 Datenbank Passwort darf nicht leer sein.";
        $gLocale[ "JoomlaDBPasswordsMatch" ]   = "Joomla3 Datenbank-Passwörter stimmen nicht überein.";      
        
        // Drupal
        $gLocale[ "DrupalBinding" ]            = "Drupal";
        $gLocale[ "DrupalConfigFile" ]         = "Drupal Konfigurationsdatei";
        $gLocale[ "DrupalDatabase" ]           = "Name der Drupal Datenbank";
        $gLocale[ "DrupalDatabaseEmpty" ]      = "Name der Drupal Datenbank darf nicht leer sein";
        $gLocale[ "DrupalUserEmpty" ]          = "Drupal Benutzer darf nicht leer sein";
        $gLocale[ "DrupalPasswordEmpty" ]      = "Drupal Datenbank Passwort darf nicht leer sein.";
        $gLocale[ "DrupalDBPasswordsMatch" ]   = "Drupal Datenbank-Passwörter stimmen nicht überein.";      
        
        // Wordpress
        $gLocale[ "WpBinding" ]                = "Wordpress";
        $gLocale[ "WpConfigFile" ]             = "Wordpress Konfigurationsdatei";
        $gLocale[ "WpDatabase" ]               = "Name der Wordpress Datenbank";
        $gLocale[ "WpDatabaseEmpty" ]          = "Name der Wordpress Datenbank darf nicht leer sein";
        $gLocale[ "WpUserEmpty" ]              = "Wordpress Benutzer darf nicht leer sein";
        $gLocale[ "WpPasswordEmpty" ]          = "Wordpress Datenbank Passwort darf nicht leer sein.";
        $gLocale[ "WpDBPasswordsMatch" ]       = "Wordpress Datenbank-Passwörter stimmen nicht überein.";
    }
?>