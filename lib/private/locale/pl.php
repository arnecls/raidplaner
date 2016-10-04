<?php
    /*
     * Translation by Damian Osipiuk
     */

    if ( defined('LOCALE_MAIN') )
    {
        // Roles
        $gLocale[ 'Tank' ]                     = 'Tank';
        $gLocale[ 'Healer' ]                   = 'Healer';
        $gLocale[ 'Damage' ]                   = 'Dps';
        $gLocale[ 'Melee' ]                    = 'Melee';
        $gLocale[ 'Range' ]                    = 'Range';
        $gLocale[ 'Support' ]                  = 'Support';

        // Pre-loading checks
        $gLocale[ 'ContinueNoUpdate' ]         = 'Kontynuuj bez aktualizacji';
        $gLocale[ 'UpdateBrowser' ]            = 'Uaktualnij przeglądarkę';
        $gLocale[ 'UsingOldBrowser' ]          = 'Używasz nieaktualnej wersji przeglądarki.';
        $gLocale[ 'OlderBrowserFeatures' ]     = 'Nieaktualne przeglądarki mogą wyświetlać stronę niepoprawnie.';
        $gLocale[ 'DownloadNewBrowser' ]       = 'Uaktualnij swoją przeglądarkę lub zainstaluj jedną z poniższych.';
        $gLocale[ 'RaidplanerNotConfigured' ]  = 'Raidplanner nie jest skonfigurowany lub wymaga aktualizacji.';
        $gLocale[ 'PleaseRunSetup' ]           = 'Uruchom <a href="setup">instalator</a> lub przejdź do <a href="https://github.com/arnecls/raidplaner/wiki/Manual-Setup">instrukcji instalacji ręcznej</a>.';

        // General
        $gLocale[ 'Reserved' ]                 = 'Rezerwacja';
        $gLocale[ 'Error' ]                    = 'Błąd';
        $gLocale[ 'Apply' ]                    = 'Zapisz zmiany';
        $gLocale[ 'AccessDenied' ]             = 'Brak dostępu';
        $gLocale[ 'ForeignCharacter' ]         = 'To nie jest twoja postać';
        $gLocale[ 'DatabaseError' ]            = 'Błąd bazy danych';
        $gLocale[ 'Cancel' ]                   = 'Anuluj';
        $gLocale[ 'Notification' ]             = 'Ostrzeżenie';
        $gLocale[ 'Busy' ]                     = 'Proszę czekać.';
        $gLocale[ 'RequestError' ]             = 'Żądanie zwróciło błąd.';
        $gLocale[ 'UnknownRequest' ]           = 'Nieznane żądanie';
        $gLocale[ 'InvalidRequest' ]           = 'Niepoprawne żądanie';
        $gLocale[ 'InputRequired' ]            = 'Wymagane dodatkowe dane';
        $gLocale[ 'UnappliedChanges' ]         = 'Czy chcesz wycofać wprowadzone zmiany?';
        $gLocale[ 'DiscardChanges' ]           = 'Tak, wycofaj';
        $gLocale[ 'to' ]                       = 'do';
        $gLocale[ 'PHPVersionWarning' ]        = 'Raidplaner od wersji 1.1.0 wymaga PHP w wersji 5.3.4 lub nowszej.<br/>Niestety twój serwer wymaga aktualizacji :(';

        // Login und user registration
        $gLocale[ 'Login' ]                    = 'Zaloguj';
        $gLocale[ 'Logout' ]                   = 'Wyloguj';
        $gLocale[ 'Username' ]                 = 'Nazwa użytkownika';
        $gLocale[ 'Password' ]                 = 'Hasło';
        $gLocale[ 'RepeatPassword' ]           = 'Powtórz hasło';
        $gLocale[ 'Register' ]                 = 'Zarejestruj';
        $gLocale[ 'EnterValidUsername' ]       = 'Wprowadź poprawną nazwę użytkownika.';
        $gLocale[ 'EnterNonEmptyPassword' ]    = 'Hasło nie może być puste.';
        $gLocale[ 'PasswordsNotMatch' ]        = 'Hasło zostało powtórzone niepoprawnie.';
        $gLocale[ 'NameInUse' ]                = 'Użytkownik o podanej nazwie już istnieje.';
        $gLocale[ 'RegistrationDone' ]         = 'Rejestracja zakończona.';
        $gLocale[ 'AccountIsLocked' ]          = 'Twoje konto jest aktualnie zablokowane.';
        $gLocale[ 'ContactAdminToUnlock' ]     = 'Skontaktuj się z administratorem w celu odblokowania konta.';
        $gLocale[ 'NoSuchUser' ]               = 'Podany użytkownik nie istnieje.';
        $gLocale[ 'HashingInProgress' ]        = 'Kodowanie hasła';
        $gLocale[ 'PassStrength']              = 'Siła hasła';

        // Calendar
        $gLocale[ 'Calendar' ]                 = 'Kalendarz';
        $gLocale[ 'January' ]                  = 'Styczeń';
        $gLocale[ 'February' ]                 = 'Luty';
        $gLocale[ 'March' ]                    = 'Marzec';
        $gLocale[ 'April' ]                    = 'Kwiecień';
        $gLocale[ 'May' ]                      = 'Maj';
        $gLocale[ 'June' ]                     = 'Czerwiec';
        $gLocale[ 'July' ]                     = 'Lipiec';
        $gLocale[ 'August' ]                   = 'Sierpień';
        $gLocale[ 'September' ]                = 'Wrzesień';
        $gLocale[ 'October' ]                  = 'Październik';
        $gLocale[ 'November' ]                 = 'Listopad';
        $gLocale[ 'December' ]                 = 'Grudzień';
        $gLocale[ 'Monday' ]                   = 'Poniedziałek';
        $gLocale[ 'Tuesday' ]                  = 'Wtorek';
        $gLocale[ 'Wednesday' ]                = 'Środa';
        $gLocale[ 'Thursday' ]                 = 'Czwartek';
        $gLocale[ 'Friday' ]                   = 'Piątek';
        $gLocale[ 'Saturday' ]                 = 'Sobota';
        $gLocale[ 'Sunday' ]                   = 'Niedziela';
        $gLocale[ 'Mon' ]                      = 'Po';
        $gLocale[ 'Tue' ]                      = 'Wt';
        $gLocale[ 'Wed' ]                      = 'Śr';
        $gLocale[ 'Thu' ]                      = 'Cz';
        $gLocale[ 'Fri' ]                      = 'Pi';
        $gLocale[ 'Sat' ]                      = 'So';
        $gLocale[ 'Sun' ]                      = 'Ni';
        $gLocale[ 'NotSignedUp' ]              = 'Nie zapisany';
        $gLocale[ 'Absent' ]                   = 'Nieobecny';
        $gLocale[ 'Benched' ]                  = 'Na liście oczekujących';
        $gLocale[ 'RaidingAs' ]                = 'Potwierdzony jako';
        $gLocale[ 'WhyAbsent' ]                = 'Napisz dlaczego będziesz nieobecny/a.';
        $gLocale[ 'SetAbsent' ]                = 'Zapisz jako nieobecny';
        $gLocale[ 'Comment' ]                  = 'Komentarz';
        $gLocale[ 'SaveComment' ]              = 'Zapisz komentarz';
        $gLocale[ 'RepeatOnce' ]               = 'Nie powtarzaj';
        $gLocale[ 'RepeatDay' ]                = 'razy, co dzień';
        $gLocale[ 'RepeatWeek' ]               = 'razy, co tydzień';
        $gLocale[ 'RepeatMonth' ]              = 'razy, co miesiąc';

        // Raid
        $gLocale[ 'Raid' ]                     = 'Rajd';
        $gLocale[ 'Upcoming' ]                 = 'Nadchodzące rajdy';
        $gLocale[ 'CreateRaid' ]               = 'Utwórz rajd';
        $gLocale[ 'NewDungeon' ]               = 'Nowa instancja';
        $gLocale[ 'Description' ]              = 'Opis';
        $gLocale[ 'DefaultRaidMode' ]          = 'Domyślny sposób zapisu';
        $gLocale[ 'RaidStatus' ]               = 'Status';
        $gLocale[ 'RaidOpen' ]                 = 'Rajd otwarty';
        $gLocale[ 'RaidLocked' ]               = 'Rajd zablokowany';
        $gLocale[ 'RaidCanceled' ]             = 'Rajd anulowany';
        $gLocale[ 'DeleteRaid' ]               = 'Usuń rajd';
        $gLocale[ 'ConfirmRaidDelete' ]        = 'Czy na pewno chcesz usunąć ten rajd?';
        $gLocale[ 'Players' ]                  = 'Graczy';
        $gLocale[ 'RequiredForRole' ]          = 'Wymaganych do roli';
        $gLocale[ 'AbsentPlayers' ]            = 'Nieobecni gracze';
        $gLocale[ 'UndecidedPlayers' ]         = 'Niezdecydowani gracze';
        $gLocale[ 'AbsentNoReason' ]           = 'Nie dodano wiadomości.';
        $gLocale[ 'Undecided' ]                = 'Jeszcze się nie zdecydował/a.';
        $gLocale[ 'MarkAsAbesent' ]            = 'Zaznacz jako nieobecny';
        $gLocale[ 'MakeAbsent' ]               = 'Gracz będzie nieobecny';
        $gLocale[ 'AbsentMessage' ]            = 'Podaj powód dlaczego gracz będzie nieobecny.<br/>Wiadomość będzie poprzedzona twoim loginem.';
        $gLocale[ 'SetupBy' ]                  = 'Obecny';
        $gLocale[ 'AbsentBy' ]                 = 'Nieobecny';
        $gLocale[ 'SwitchChar' ]               = 'Zmieniono rolę';
        $gLocale[ 'RaidNotFound' ]             = 'Nie znaleziono rajdu';
        $gLocale[ 'RaidSetup' ]                = 'Ustawienia rajdu';
        $gLocale[ 'LinkToRaid' ]               = 'Link do rajdu';
        $gLocale[ 'Switch' ]                   = 'Zmień rolę';
        $gLocale[ 'Retire' ]                   = 'Nieobecny';
        $gLocale[ 'Export' ]                   = 'Eksport';
        $gLocale[ 'ExportFile' ]               = 'Plik';
        $gLocale[ 'ExportClipboard' ]          = 'Schowek';
        $gLocale[ 'CopyOk' ]                   = 'Dane zostały skopiowane do schowka.';
        $gLocale[ 'Random' ]                   = 'Rezerwacja';
        
        // Profile
        $gLocale[ 'Profile' ]                  = 'Profil';
        $gLocale[ 'History' ]                  = 'Historia rajdów';
        $gLocale[ 'Characters' ]               = 'Twoje postacie';
        $gLocale[ 'CharName' ]                 = 'Nazwa postaci';
        $gLocale[ 'NoName' ]                   = 'Nowa postać nie ma przypisanej nazwy.';
        $gLocale[ 'NoClass' ]                  = 'Nowa postać nie ma przypisanej klasy.';
        $gLocale[ 'DeleteCharacter' ]          = 'Usuń postać';
        $gLocale[ 'ConfirmDeleteCharacter' ]   = 'Czy na pewno chcesz usunąć tę postać?';
        $gLocale[ 'AttendancesRemoved' ]       = 'Wszystkie zapisane obecności zostaną również usunięte.';
        $gLocale[ 'RaidAttendance' ]           = 'Obecność na rajdach';
        $gLocale[ 'RolesInRaids' ]             = 'Role w zapisanych rajdach';
        $gLocale[ 'Queued' ]                   = 'Zapisany';
        $gLocale[ 'Attended' ]                 = 'Obecny';
        $gLocale[ 'Missed' ]                   = 'Opuszczony';
        $gLocale[ 'ChangePassword' ]           = 'Zmień hasło';
        $gLocale[ 'OldPassword' ]              = 'Stare hasło';
        $gLocale[ 'OldPasswordEmpty' ]         = 'Stare hasło nie może być puste.';
        $gLocale[ 'AdminPassword' ]            = 'Hasło administratora';
        $gLocale[ 'AdminPasswordEmpty' ]       = 'Hasło administratora nie może być puste.';
        $gLocale[ 'WrongPassword' ]            = 'Nieprawidłowe dane logowania';
        $gLocale[ 'PasswordLocked' ]           = 'Hasło nie może być zmienione.';
        $gLocale[ 'PasswordChanged' ]          = 'Hasło zostało zmienione.';
        $gLocale[ 'UserNotFound' ]             = 'Nie znaleziono użytkownika';
        $gLocale[ 'VacationStart' ]            = 'Pierwszy dzień urlopu';
        $gLocale[ 'VacationEnd' ]              = 'Ostatni dzień urlopu';
        $gLocale[ 'NoStartDate' ]              = 'Wprowadź pierwszy dzień urlopu';
        $gLocale[ 'NoEndDate' ]                = 'Wprowadź ostatni dzień urlopu';
        $gLocale[ 'VacationMessage' ]          = 'Wiadomość urlopowa';
        $gLocale[ 'ClearVacation' ]            = 'Usuń dane o urlopie';
        $gLocale[ 'AutoAttend' ]               = 'Potwierdzaj automatycznie';

        // Settings
        $gLocale[ 'Settings' ]                 = 'Ustawienia';
        $gLocale[ 'Locked' ]                   = 'Zablokowani';
        $gLocale[ 'Members' ]                  = 'Użytkownicy';
        $gLocale[ 'Raidleads' ]                = 'Rajd Liderzy';
        $gLocale[ 'Administrators' ]           = 'Administratorzy';
        $gLocale[ 'ConfirmDeleteUser' ]        = 'Czy na pewno chcesz usunąć tego użytkownika?';
        $gLocale[ 'DeleteUser' ]               = 'Usuń użytkownika';
        $gLocale[ 'MoveUser' ]                 = 'Przenieś użytkownika do grupy';
        $gLocale[ 'UnlinkUser' ]               = 'Nie synchronizuj i zamień na użytkownika lokalnego.';
        $gLocale[ 'LinkUser' ]                 = 'Synchronizuj użytkownika';
        $gLocale[ 'SyncFailed' ]               = 'Błąd synchronizacji.</br>Nie znaleziona pasującego użytkownika.';
        $gLocale[ 'EditForeignCharacters' ]    = 'Edytuj postacie użytkownika';
        $gLocale[ 'ConfirmDeleteLocation' ]    = 'Czy na pewno chcesz usunąć tę instancję?';
        $gLocale[ 'NoteDeleteRaidsToo' ]       = 'Zostaną usunięte wszystkie rajdy powiązane z tą instancją.';
        $gLocale[ 'DeleteRaids' ]              = 'Usuń rajdy';
        $gLocale[ 'DeleteLocationRaids' ]      = 'Usuń instancję i rajdy';
        $gLocale[ 'LockRaids' ]                = 'Zablokuj rajdy';
        $gLocale[ 'AfterDone' ]                = 'po skończeniu rajdu';
        $gLocale[ 'BeforeStart' ]              = 'przed rozpoczęciem rajdu';
        $gLocale[ 'Seconds' ]                  = 'Sekund(y)';
        $gLocale[ 'Minutes' ]                  = 'Minut(y)';
        $gLocale[ 'Hours' ]                    = 'Godzin(y)';
        $gLocale[ 'Days' ]                     = 'Dni';
        $gLocale[ 'Weeks' ]                    = 'Tygodni(e)';
        $gLocale[ 'Month' ]                    = 'Miesiąc(e)';
        $gLocale[ 'TimeFormat' ]               = 'Format godziny';
        $gLocale[ 'StartOfWeek' ]              = 'Pierwszy dzień tygodnia';
        $gLocale[ 'DefaultStartTime' ]         = 'Domyślny początek rajdów';
        $gLocale[ 'DefaultEndTime' ]           = 'Domyślny koniec rajdów';
        $gLocale[ 'DefaultRaidSize' ]          = 'Domyślny rozmiar rajdów';
        $gLocale[ 'BannerPage' ]               = 'Link do baneru';
        $gLocale[ 'HelpPage' ]                 = 'Link do pomocy';
        $gLocale[ 'Game' ]                     = 'Gra';
        $gLocale[ 'Theme' ]                    = 'Motyw';
        $gLocale[ 'ApiPrivate' ]               = 'Token API (prywatny)';
        $gLocale[ 'RaidSetupStyle' ]           = 'Sposób zapisów';
        $gLocale[ 'RaidModeManual' ]           = 'Przez rajd lidera';
        $gLocale[ 'RaidModeOverbook' ]         = 'Przez raid lidera z nadmiarem';
        $gLocale[ 'RaidModeAttend' ]           = 'Automatyczny';
        $gLocale[ 'RaidModeAll' ]              = 'Tylko lista';
        $gLocale[ 'RaidModeOptOut' ]           = 'Powierdź wszystkich';
        $gLocale[ 'UpdateCheck' ]              = 'Sprawdź aktualizacje';
        $gLocale[ 'UpToDate' ]                 = 'Raidplanner jest aktualny.';
        $gLocale[ 'NewVersionAvailable' ]      = 'Dostępna jest nowa wersja:';
        $gLocale[ 'VisitProjectPage' ]         = 'Odwiedź stronę projektu';
        $gLocale[ 'AttendWithPrimary' ]        = 'Zapisz się z główną rolą';
        $gLocale[ 'CalendarBigIcons' ]         = null;
    }

    if ( defined('LOCALE_SETUP') )
    {
        // General
        $gLocale[ 'Ok' ]                       = 'Ok';
        $gLocale[ 'Back' ]                     = 'Wstecz';
        $gLocale[ 'Continue' ]                 = 'Dalej';
        $gLocale[ 'Error' ]                    = 'Błąd';
        $gLocale[ 'Ignore' ]                   = 'Ignoruj';
        $gLocale[ 'Retry' ]                    = 'Spróbuj ponownie';
        $gLocale[ 'DatabaseError' ]            = 'Błąd bazy danych';

        // Menu
        $gLocale[ 'Install' ]                  = 'Instalacja';
        $gLocale[ 'Update' ]                   = 'Aktualizacja';
        $gLocale[ 'EditBindings' ]             = 'Edycja powiązań';
        $gLocale[ 'EditConfig' ]               = 'Edycja konfiguracji';
        $gLocale[ 'ResetPassword' ]            = 'Ustaw hasło administratora';
        $gLocale[ 'RepairDatabase' ]           = 'Napraw bazę danych';

        // Checks
        $gLocale[ 'FilesystemChecks' ]         = 'Testowanie uprawnień systemu plików';
        $gLocale[ 'NotWriteable' ]             = 'Niezapisywalny';
        $gLocale[ 'ConfigFolder' ]             = 'Folder konfiguracji';
        $gLocale[ 'MainConfigFile' ]           = 'Główny plik konfiguracji';
        $gLocale[ 'DatabaseConnection' ]       = 'Połączenie bazy danych';
        $gLocale[ 'WritePermissionRequired' ]  = 'Instalator potrzebuje uprawnień do zapisu dla wszystkich plików w folderze ';
        $gLocale[ 'ChangePermissions' ]        = 'Jeżeli jakikolwiek z poniższych testów nie powiedzie się dodaj uprawnienia "zapisu" dla wskazanego pliku.';
        $gLocale[ 'FTPClientHelp' ]            = 'W celu uzykania informacji na temat zmiany uprawnień, sprawdź pliki pomocy twojego klienta FTP.';
        $gLocale[ 'OutdatedPHP' ]              = 'Nieaktualna wersja PHP';
        $gLocale[ 'PHPVersion' ]               = 'Wersja PHP';
        $gLocale[ 'MbStringModule' ]           = 'Moduł mbstring';
        $gLocale[ 'MbStringNotFound' ]         = 'Mbstring nie został skonfigurowany z PHP';
        $gLocale[ 'PDOModule' ]                = 'Moduł PDO';
        $gLocale[ 'PDONotFound' ]              = 'PDO nie został skonfigurowany z PHP';
        $gLocale[ 'PDOMySQLModule' ]           = 'Sterownik PDO MySQL';
        $gLocale[ 'PDOMySQLNotFound' ]         = 'Sterownik PDO MySQL nie został odnaleziony';
        $gLocale[ 'PHPRequirements' ]          = 'Raidplanner wymaga zainstalowanego PHP 5.4 z modułami PDO.';

        // Database setup
        $gLocale[ 'ConfigureDatabase' ]        = 'Skonfiguruj bazę danych z której korzystać będzie raidplanner.';
        $gLocale[ 'SameAsForumDatabase' ]      = 'Jeżeli chcesz połączyć raidplanner z forum innego producenta, obydwie bazy danych muszą znajdować się na tym samym serwerze.';
        $gLocale[ 'EnterPrefix' ]              = 'Jeżeli baza danych jest już używana, możesz użyć prefixu, aby uniknąć konfliktów.';
        $gLocale[ 'DatabaseHost' ]             = 'Serwer bazy danych';
        $gLocale[ 'RaidplanerDatabase' ]       = 'Baza danych Raidplannera';
        $gLocale[ 'UserWithDBPermissions' ]    = 'Nazwa użytkownika bazy danych';
        $gLocale[ 'UserPassword' ]             = 'Hasło użytkownika bazy danych';
        $gLocale[ 'RepeatPassword' ]           = 'Powtórz hasło';
        $gLocale[ 'TablePrefix' ]              = 'Prefix tabeli w bazie danych';
        $gLocale[ 'VerifySettings' ]           = 'Sprawdź ustawienia';
        $gLocale[ 'ConnectionTestFailed' ]     = 'Test połączenia zakończony niepowodzeniem';
        $gLocale[ 'ConnectionTestOk' ]         = 'Test połączenia zakończony sukcesem';

        // Registration and admin
        $gLocale[ 'AdminName' ]                = 'Nazwa administratora';
        $gLocale[ 'AdminPassword' ]            = 'Hasło administratora';
        $gLocale[ 'AdminPasswordSetup']        = 'Administrator jest użytkownikiem posiadającym zawsze wszystkie uprawnienia.';
        $gLocale[ 'AdminNotMoveable']          = 'Nazwa konta administratora nie może być zmieniona, a konto nie może zostać przypisane do innej grupy.';
        $gLocale[ 'AdminPasswordNoMatch' ]     = 'Hasło administratora zostało powtórzone niepoprawnie.';
        $gLocale[ 'AdminPasswordEmpty' ]       = 'Hasło administratora nie może być puste.';
        $gLocale[ 'DatabasePasswordNoMatch' ]  = 'Hasło bazy danych zostało powtórzone niepoprawnie.';
        $gLocale[ 'DatabasePasswordEmpty' ]    = 'Hasło bazy danych nie może być puste.';
        $gLocale[ 'AllowManualRegistration' ]  = 'Pozwól użytkownikom na rejestrację lokalną';
        $gLocale[ 'AllowGroupSync' ]           = 'Synchronizuj grupy użytkowników zewnętrznych';
        $gLocale[ 'AllowPublicMode' ]          = 'Konta nowo rejestrowanych użytkowników jako aktywne (nie zalecane)';
        $gLocale[ 'UseClearText' ]             = 'Wysyłaj niezakodowane hasła (nie zalecane)';

        // Install/Update
        $gLocale[ 'SecurityWarning' ]          = 'Ostrzeżenie bezpieczeństwa';
        $gLocale[ 'UpdateComplete' ]           = 'Aktualizacja zakończona';
        $gLocale[ 'RaidplanerSetupDone' ]      = 'Raidplanner został poprawnie skonfigurowany.';
        $gLocale[ 'DeleteSetupFolder' ]        = 'Zalecane jest usunięcie katalogu "setup" i zabezpieczenie następujących katalogów (np. przez htaccess):';
        $gLocale[ 'ThankYou' ]                 = 'Dziękuję za używanie Raidplannera.';
        $gLocale[ 'VisitBugtracker' ]          = 'Jeżeli zauważyłeś jakiekolwiek błędy lub masz jakieś propozycje, odwiedź bugtracker pod adresem ';
        $gLocale[ 'VersionDetection' ]         = 'Wykrywanie wersji i aktualizacja';
        $gLocale[ 'VersionDetectProgress' ]    = 'Instalator spróbuje wykryć aktualną wersję raidplannera.';
        $gLocale[ 'ChooseManually' ]           = 'Jeżeli zostanie wykryta inna wersja, niż jest aktualnie zainstalowana, możesz wybrać poprawną wersję ręcznie.';
        $gLocale[ 'OnlyDBAffected' ]           = 'Aktualizacja dotyczy tylko bazy danych.';
        $gLocale[ 'NoChangeNoAction' ]         = 'Jeżeli baza danych nie została zmodyfikowana od ostaniej wersji, nie musisz wykonywać tego kroku.';
        $gLocale[ 'DetectedVersion' ]          = 'Wykryta wersja';
        $gLocale[ 'NoUpdateNecessary' ]        = 'Aktualizacja nie jest konieczna.';
        $gLocale[ 'UpdateFrom' ]               = 'Aktualizacja z wersji';
        $gLocale[ 'UpdateTo' ]                 = 'do wersji';
        $gLocale[ 'UpdateErrors' ]             = 'Błędy aktualizacji';
        $gLocale[ 'ReportedErrors' ]           = 'Poniższe błędy wystąpiły podczas próby aktualizacji.';
        $gLocale[ 'PartiallyUpdated' ]         = 'Może to świadczyć o częściowo zaktualizowanej bazie danych.';
        $gLocale[ 'GameconfigNotFound' ]       = 'Plik gameconfig.php nie został znaleziony.';
        $gLocale[ 'FailedGameconfig' ]         = 'Plik gameconfig.php nie może być zaktualizowany do nowego formatu.';
        $gLocale[ 'RemoveAndLaunch' ]          = 'Usuń instalator i przejdź do strony głownej.';
        $gLocale[ 'FailedRemoveSetup' ]        = 'Katalgog instalacyjny nie może być usunięty.';

        // Repair
        $gLocale[ 'Repair' ]                   = 'Napraw';
        $gLocale[ 'RepairDone' ]               = 'Naprawa zakończona.';
        $gLocale[ 'BrokenDatabase' ]           = 'Baza danych wygląda na nienaprawialną';
        $gLocale[ 'EnsureValidDatabase' ]      = 'Upewnij się czy baza danych jest poprawna';
        $gLocale[ 'ItemsRepaired' ]            = 'Naprawione elementy';
        $gLocale[ 'ItemsToResolve' ]           = 'Elementy, które muszą zostać poprawione ręcznie';
        $gLocale[ 'InvalidCharacters' ]        = 'Nieprawidłowe postacie';
        $gLocale[ 'InvalidAttendances' ]       = 'Nieprawidłowe zapisy';
        $gLocale[ 'Delete' ]                   = 'Usuń';
        $gLocale[ 'Resolve' ]                  = 'Napraw';
        $gLocale[ 'StrayRoles' ]               = 'Nieprawidłowe role';
        $gLocale[ 'StrayCharacters' ]          = 'Usunięte postacie';
        $gLocale[ 'StrayUsers' ]               = 'Usunięci użytkownicy';
        $gLocale[ 'StrayBindings' ]            = 'Nieprawidłowe powiązania';
        $gLocale[ 'RepairCharacters' ]         = 'Napraw niepoprawne postacie';
        $gLocale[ 'TransferGameconfig' ]       = 'Aktualizuj gameconfig.php (Raidplaner 1.0.x)';
        $gLocale[ 'MergeGames' ]               = 'Połącz dwie gry';
        $gLocale[ 'SourceGame' ]               = 'Źródło';
        $gLocale[ 'TargetGame' ]               = 'Cel';
        $gLocale[ 'ChooseRepairs' ]            = 'Wybież akcje, które zostaną wykonane.';
        $gLocale[ 'Fixing' ]                   = 'Naprawianie';
        $gLocale[ 'StrayChars' ]               = 'Osierocone postacie';
        $gLocale[ 'StrayAttends' ]             = 'Osierocone zapisy';
        $gLocale[ 'InvalidCharacters' ]        = 'Nieprawidłowe postacie';
        $gLocale[ 'SameGame' ]                 = 'Gry są identyczne';   
        $gLocale[ 'Merged' ]                   = 'Zaktualizowano:'; 
        $gLocale[ 'Locations' ]                = 'Lokacje'; 
        $gLocale[ 'Characters' ]               = 'Postacie'; 

        // Plugin setup
        $gLocale[ 'LoadGroups' ]               = 'Wczytaj grupy używając powyższych ustawień';
        $gLocale[ 'AutoMemberLogin' ]          = 'Grupy "Użytkowników"';
        $gLocale[ 'AutoLeadLogin' ]            = 'Grupy "Rajd Liderów"';
        $gLocale[ 'ReloadFailed' ]             = 'Wczytywanie nie powiodło się';
        $gLocale[ 'LoadSettings' ]             = 'Uzyskaj ustawienia automatycznie';
        $gLocale[ 'BindingBasePath' ]          = 'Wprowadź ścieżkę do katalogu, w którym znajduje się powiązana strona, relatywnie do \''.$_SERVER['DOCUMENT_ROOT'].'\'.';
        $gLocale[ 'RetrievalFailed' ]          = 'Automatyczne wykrywanie nie powiodło się';
        $gLocale[ 'RetrievalOk' ]              = 'Automatyczne wykrywanie powiodło się';
        $gLocale[ 'NotExisting' ]              = 'nie istnieje';
        $gLocale[ 'AllowAutoLogin' ]           = 'Zezwól na automatyczne logowanie';
        $gLocale[ 'NoValidConfig' ]            = 'Nie znaleziono odpowiedniego pliku konfiguracyjnego';
        $gLocale[ 'CookieNote' ]               = 'Automatyczne logowanie wymaga, aby Raidplaner został zainstalowany w podkatalogu powiązanej strony. '.
                                                 'Konieczna może być zmiana tej ścieżki w konfiguracji powiązanej strony.';
        $gLocale[ 'PostToForum' ]              = 'Ogłaszaj nowe rajdy na tym forum';
        $gLocale[ 'PostAsUser' ]               = 'Ogłaszaj nowe rajdy, jako ten użytkownik';
        $gLocale[ 'DisablePosting' ]           = 'Nie twórz postów';
        $gLocale[ 'NoUsersFound' ]             = 'Nie znaleziono użytkownika';
        $gLocale[ 'Version' ]                  = 'Wersja';

        // PHPBB3
        $gLocale[ 'phpbb3_Binding' ]            = 'PHPBB 3.x';
        $gLocale[ 'phpbb3_ConfigFile' ]         = 'Plik konfiguracyjny PHPBB';
        $gLocale[ 'phpbb3_Database' ]           = 'Baza danych PHPBB';
        $gLocale[ 'phpbb3_DatabaseEmpty' ]      = 'Nazwa bazy danych PHPBB nie może być pusta.';
        $gLocale[ 'phpbb3_UserEmpty' ]          = 'Nazwa użytkownika PHPBB nie może być pusta.';
        $gLocale[ 'phpbb3_PasswordEmpty' ]      = 'Hasło bazy danych PHPBB nie powinno być puste.';
        $gLocale[ 'phpbb3_DBPasswordsMatch' ]   = 'Hasło bazy danych PHPBB zostało powtórzone niepoprawnie.';

        // EQDKP
        $gLocale[ 'eqdkp_Binding' ]             = 'EQDKP';
        $gLocale[ 'eqdkp_ConfigFile' ]          = 'Plik konfiguracyjny EQDKP';
        $gLocale[ 'eqdkp_Database' ]            = 'Baza danych EQDKP';
        $gLocale[ 'eqdkp_DatabaseEmpty' ]       = 'Nazwa bazy danych EQDKP nie może być pusta.';
        $gLocale[ 'eqdkp_UserEmpty' ]           = 'Nazwa użytkownika EQDKP nie może być pusta.';
        $gLocale[ 'eqdkp_PasswordEmpty' ]       = 'Hasło bazy danych EQDKP nie powinno być puste.';
        $gLocale[ 'eqdkp_DBPasswordsMatch' ]    = 'Hasło bazy danych EQDKP zostało powtórzone niepoprawnie.';

        // vBulletin
        $gLocale[ 'vb3_Binding' ]               = 'vBulletin 3 / 4';
        $gLocale[ 'vb3_ConfigFile' ]            = 'Plik konfiguracyjny vBulletin';
        $gLocale[ 'vb3_Database' ]              = 'Baza danych vBulletin';
        $gLocale[ 'vb3_DatabaseEmpty' ]         = 'Nazwa bazy danych vBulletin nie może być pusta.';
        $gLocale[ 'vb3_UserEmpty' ]             = 'Nazwa użytkownika vBulletin nie może być pusta.';
        $gLocale[ 'vb3_PasswordEmpty' ]         = 'Hasło bazy danych vBulletin nie powinno być puste.';
        $gLocale[ 'vb3_DBPasswordsMatch' ]      = 'Hasło bazy danych vBulletin zostało powtórzone niepoprawnie.';
        $gLocale[ 'vb3_CookieEx' ]              = 'Prefix ciasteczka vBulletin';

        // MyBB
        $gLocale[ 'mybb_Binding' ]              = 'MyBB 1.6+';
        $gLocale[ 'mybb_ConfigFile' ]           = 'Plik konfiguracyjny MyBB';
        $gLocale[ 'mybb_Database' ]             = 'Baza danych MyBB';
        $gLocale[ 'mybb_DatabaseEmpty' ]        = 'Nazwa bazy danych MyBB nie może być pusta.';
        $gLocale[ 'mybb_UserEmpty' ]            = 'Nazwa użytkownika MyBB nie może być pusta.';
        $gLocale[ 'mybb_PasswordEmpty' ]        = 'Hasło bazy danych MyBB nie powinno być puste.';
        $gLocale[ 'mybb_DBPasswordsMatch' ]     = 'Hasło bazy danych MyBB zostało powtórzone niepoprawnie.';

        // SMF
        $gLocale[ 'smf_Binding' ]               = 'Simple Machines Forum 2.x';
        $gLocale[ 'smf_ConfigFile' ]            = 'Plik konfiguracyjny SMF';
        $gLocale[ 'smf_Database' ]              = 'Baza danych SMF';
        $gLocale[ 'smf_DatabaseEmpty' ]         = 'Nazwa bazy danych SMF nie może być pusta.';
        $gLocale[ 'smf_UserEmpty' ]             = 'Nazwa użytkownika SMF nie może być pusta.';
        $gLocale[ 'smf_PasswordEmpty' ]         = 'Hasło bazy danych SMF nie powinno być puste.';
        $gLocale[ 'smf_DBPasswordsMatch' ]      = 'Hasło bazy danych SMF zostało powtórzone niepoprawnie.';
        $gLocale[ 'smf_CookieEx' ]              = 'Nazwa ciasteczka SMF';

        // Vanilla
        $gLocale[ 'vanilla_Binding' ]           = 'Vanilla Forum 2.x';
        $gLocale[ 'vanilla_ConfigFile' ]        = 'Plik konfiguracyjny Vanilla';
        $gLocale[ 'vanilla_Database' ]          = 'Baza danych Vanilla';
        $gLocale[ 'vanilla_DatabaseEmpty' ]     = 'Nazwa bazy danych Vanilla nie może być pusta.';
        $gLocale[ 'vanilla_UserEmpty' ]         = 'Nazwa użytkownika Vanilla nie może być pusta.';
        $gLocale[ 'vanilla_PasswordEmpty' ]     = 'Hasło bazy danych Vanilla nie powinno być puste.';
        $gLocale[ 'vanilla_DBPasswordsMatch' ]  = 'Hasło bazy danych Vanilla zostało powtórzone niepoprawnie.';
        $gLocale[ 'vanilla_CookieEx' ]          = 'Nazwa ciasteczka, metoda haszowania (np. md5), salt ciasteczka';

        // Joomla
        $gLocale[ 'jml3_Binding' ]              = 'Joomla 3.x';
        $gLocale[ 'jml3_ConfigFile' ]           = 'Plik konfiguracyjny Joomla';
        $gLocale[ 'jml3_Database' ]             = 'Baza danych Joomla';
        $gLocale[ 'jml3_DatabaseEmpty' ]        = 'Nazwa bazy danych Joomla nie może być pusta.';
        $gLocale[ 'jml3_UserEmpty' ]            = 'Nazwa użytkownika Joomla nie może być pusta.';
        $gLocale[ 'jml3_PasswordEmpty' ]        = 'Hasło bazy danych Joomla nie powinno być puste.';
        $gLocale[ 'jml3_DBPasswordsMatch' ]     = 'Hasło bazy danych Joomla zostało powtórzone niepoprawnie.';
        $gLocale[ 'jml3_CookieEx' ]             = 'Sekret Joomla3';

        // Drupal
        $gLocale[ 'drupal_Binding' ]            = 'Drupal 7.6+';
        $gLocale[ 'drupal_ConfigFile' ]         = 'Plik konfiguracyjny Drupal';
        $gLocale[ 'drupal_Database' ]           = 'Baza danych Drupal';
        $gLocale[ 'drupal_DatabaseEmpty' ]      = 'Nazwa bazy danych Drupal nie może być pusta.';
        $gLocale[ 'drupal_UserEmpty' ]          = 'Nazwa użytkownika Drupal nie może być pusta.';
        $gLocale[ 'drupal_PasswordEmpty' ]      = 'Hasło bazy danych Drupal nie powinno być puste.';
        $gLocale[ 'drupal_DBPasswordsMatch' ]   = 'Hasło bazy danych Drupal zostało powtórzone niepoprawnie.';
        $gLocale[ 'drupal_CookieEx' ]           = 'Domyślny adres URL do Drupal';

        // Wordpress
        $gLocale[ 'wp_Binding' ]                = 'Wordpress 3 / 4';
        $gLocale[ 'wp_ConfigFile' ]             = 'Plik konfiguracyjny Wordpress';
        $gLocale[ 'wp_Database' ]               = 'Baza danych Wordpress';
        $gLocale[ 'wp_DatabaseEmpty' ]          = 'Nazwa bazy danych Wordpress nie może być pusta.';
        $gLocale[ 'wp_UserEmpty' ]              = 'Nazwa użytkownika Wordpress nie może być pusta.';
        $gLocale[ 'wp_PasswordEmpty' ]          = 'Hasło bazy danych Wordpress nie powinno być puste.';
        $gLocale[ 'wp_DBPasswordsMatch' ]       = 'Hasło bazy danych Wordpress zostało powtórzone niepoprawnie.';
        $gLocale[ 'wp_CookieEx' ]               = 'LOGGED_IN_KEY + LOGGED_IN_SALT';
        
        // Woltlab Burning Board
        $gLocale[ 'wbb_Binding' ]               = 'Burning Board 4.x';
        $gLocale[ 'wbb_ConfigFile' ]            = 'Plik konfiguracyjny Burning Board';
        $gLocale[ 'wbb_Database' ]              = 'Baza danych Burning Board';
        $gLocale[ 'wbb_DatabaseEmpty' ]         = 'Nazwa bazy danych Burning Board nie może być pusta.';
        $gLocale[ 'wbb_UserEmpty' ]             = 'Nazwa użytkownika Burning Board nie może być pusta.';
        $gLocale[ 'wbb_PasswordEmpty' ]         = 'Hasło bazy danych Burning Board nie powinno być puste.';
        $gLocale[ 'wbb_DBPasswordsMatch' ]      = 'Hasło bazy danych Burning Board zostało powtórzone niepoprawnie.';
        $gLocale[ 'wbb_CookieEx' ]              = 'Prefix ciasteczka Burning Board';
    }
?>
