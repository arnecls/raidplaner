<?php
    /*
     * Translation by Magali
     */

    if ( defined('LOCALE_MAIN') )
    {
        // Roles
        $gLocale[ 'Tank' ]                     = 'Tank';
        $gLocale[ 'Healer' ]                   = 'Heal';
        $gLocale[ 'Damage' ]                   = 'Dps';
        $gLocale[ 'Melee' ]                    = 'Corps à corps';
		$gLocale[ 'Range' ]                    = 'Distance';
		$gLocale[ 'Support' ]                  = 'Support';

        // Pre-loading checks
        $gLocale[ 'ContinueNoUpdate' ]         = 'Poursuivre sans mettre à jour';
        $gLocale[ 'UpdateBrowser' ]            = 'Merci de mettre votre navigateur à jour';
        $gLocale[ 'UsingOldBrowser' ]          = 'Vous utilisez une version périmée de votre navigateur';
        $gLocale[ 'OlderBrowserFeatures' ]     = 'Les vieux navigateurs ne supportent pas toutes les fonctionnalités requises ou n\'affichent pas le site correctement.';
        $gLocale[ 'DownloadNewBrowser' ]       = 'Vous devriez mettre à jour votre navigateur ou télécharger un des navigateurs suivants.';
        $gLocale[ 'RaidplanerNotConfigured' ]  = 'Raidplaner n\'est pas encore configuré ou a besoin d\'une mise à jour.';
        $gLocale[ 'PleaseRunSetup' ]           = 'Lancez <a href="setup">l\'installation</a> ou suivez les instructions pour une <a href="https://github.com/arnecls/raidplaner/wiki/Manual-Setup">installation manuelle</a>.';

        // General
        $gLocale[ 'Reserved' ]                 = 'Reservé';
        $gLocale[ 'Error' ]                    = 'Erreur';
        $gLocale[ 'Apply' ]                    = 'Appliquer les modifications';
        $gLocale[ 'AccessDenied' ]             = 'Accès refusé';
        $gLocale[ 'ForeignCharacter' ]         = 'Ce n\'est pas votre personnage';
        $gLocale[ 'DatabaseError' ]            = 'Erreur de la base de données';
        $gLocale[ 'Cancel' ]                   = 'Annuler';
        $gLocale[ 'Notification' ]             = 'Notification';
        $gLocale[ 'Busy' ]                     = 'Occupé. Merci de patienter.';
        $gLocale[ 'RequestError' ]             = 'Une requête a renvoyé une erreur.';
        $gLocale[ 'UnknownRequest' ]           = 'Requête inconnue';
        $gLocale[ 'InvalidRequest' ]           = 'Requête invalide';
        $gLocale[ 'InputRequired' ]            = 'Saisie requise';
        $gLocale[ 'UnappliedChanges' ]         = 'Voulez-vous annuler les modifications non sauvegardées?';
        $gLocale[ 'DiscardChanges' ]           = 'Oui annuler';
        $gLocale[ 'to' ]                       = 'à';
        $gLocale[ 'PHPVersionWarning' ]        = 'La version 1.1.0 PHP 5.3.4 ou supérieur est requise pour le Raidplaner.<br/>Désolé mais le serveur nécessite une mise à jour :(';

        // Login und user registration
        $gLocale[ 'Login' ]                    = 'Connexion';
        $gLocale[ 'Logout' ]                   = 'Déconnexion';
        $gLocale[ 'Username' ]                 = 'Nom d\'utilisateur';
        $gLocale[ 'Password' ]                 = 'Mot de passe';
        $gLocale[ 'RepeatPassword' ]           = 'Saisissez à nouveau le mot de passe';
        $gLocale[ 'Register' ]                 = 'Inscription';
        $gLocale[ 'EnterValidUsername' ]       = 'Vous devez saisir un nom d\'utilisateur valide.';
        $gLocale[ 'EnterNonEmptyPassword' ]    = 'Vous devez saisir un mot de passe.';
        $gLocale[ 'PasswordsNotMatch' ]        = 'Les mots de passe ne correspondent pas.';
        $gLocale[ 'NameInUse' ]                = 'Ce nom d\'utilisateur est déjà utilisé.';
        $gLocale[ 'RegistrationDone' ]         = 'Inscription terminée.';
        $gLocale[ 'AccountIsLocked' ]          = 'Votre compte est actuellement verouillé.';
        $gLocale[ 'ContactAdminToUnlock' ]     = 'Merci de contacter un administrateur pour dévérouiller votre compte.';
        $gLocale[ 'NoSuchUser' ]               = 'Utilisateur introuvable.';
        $gLocale[ 'HashingInProgress' ]        = 'Hashing du mot de passe';
        $gLocale[ 'PassStrength']              = 'Complexité du mot de passe';

        // Calendar
        $gLocale[ 'Calendar' ]                 = 'Calendrier';
        $gLocale[ 'January' ]                  = 'Janvier';
        $gLocale[ 'February' ]                 = 'Février';
        $gLocale[ 'March' ]                    = 'Mars';
        $gLocale[ 'April' ]                    = 'Avril';
        $gLocale[ 'May' ]                      = 'Mai';
        $gLocale[ 'June' ]                     = 'Juin';
        $gLocale[ 'July' ]                     = 'Juillet';
        $gLocale[ 'August' ]                   = 'Août';
        $gLocale[ 'September' ]                = 'Septembre';
        $gLocale[ 'October' ]                  = 'Octobre';
        $gLocale[ 'November' ]                 = 'Novembre';
        $gLocale[ 'December' ]                 = 'Décembre';
        $gLocale[ 'Monday' ]                   = 'Lundi';
        $gLocale[ 'Tuesday' ]                  = 'Mardi';
        $gLocale[ 'Wednesday' ]                = 'Mercredi';
        $gLocale[ 'Thursday' ]                 = 'Jeudi';
        $gLocale[ 'Friday' ]                   = 'Vendredi';
        $gLocale[ 'Saturday' ]                 = 'Samedi';
        $gLocale[ 'Sunday' ]                   = 'Dimanche';
        $gLocale[ 'Mon' ]                      = 'Lu';
        $gLocale[ 'Tue' ]                      = 'Ma';
        $gLocale[ 'Wed' ]                      = 'Me';
        $gLocale[ 'Thu' ]                      = 'Je';
        $gLocale[ 'Fri' ]                      = 'Ve';
        $gLocale[ 'Sat' ]                      = 'Sa';
        $gLocale[ 'Sun' ]                      = 'Di';
        $gLocale[ 'NotSignedUp' ]              = 'Pas inscrit';
        $gLocale[ 'Absent' ]                   = 'Absent';
		$gLocale[ 'Benched' ]                  = 'En liste d\'attente';
		$gLocale[ 'RaidingAs' ]                = 'Raid en tant que';
        $gLocale[ 'WhyAbsent' ]                = 'Merci de nous dire pourquoi vous serez absent.';
        $gLocale[ 'SetAbsent' ]                = 'Mettre absent';
        $gLocale[ 'Comment' ]                  = 'Commentaire';
        $gLocale[ 'SaveComment' ]              = 'Sauvegarder le commentaire';
        $gLocale[ 'RepeatOnce' ]               = 'Ne pas répéter';
		$gLocale[ 'RepeatDay' ]                = 'Période, quotidienne';
		$gLocale[ 'RepeatWeek' ]               = 'Période, hebdomadaire';
		$gLocale[ 'RepeatMonth' ]              = 'Période, mensuel';

        // Raid
        $gLocale[ 'Raid' ]                     = 'Raid';
        $gLocale[ 'Upcoming' ]                 = 'Raids à venir';
        $gLocale[ 'CreateRaid' ]               = 'Créer raid';
        $gLocale[ 'NewDungeon' ]               = 'Nouveau raid';
        $gLocale[ 'Description' ]              = 'Description';
        $gLocale[ 'DefaultRaidMode' ]          = 'Mode de validation par défaut';
        $gLocale[ 'RaidStatus' ]               = 'Statut';
        $gLocale[ 'RaidOpen' ]                 = 'Raid ouvert';
        $gLocale[ 'RaidLocked' ]               = 'Raid verouillé';
        $gLocale[ 'RaidCanceled' ]             = 'Raid annulé';
        $gLocale[ 'DeleteRaid' ]               = 'Supprimer ce raid';
        $gLocale[ 'ConfirmRaidDelete' ]        = 'Voulez-vous vraiment supprimer ce raid?';
        $gLocale[ 'Players' ]                  = 'Joueurs';
        $gLocale[ 'RequiredForRole' ]          = 'Places pour le role';
        $gLocale[ 'AbsentPlayers' ]            = 'Joueurs absents';
        $gLocale[ 'UndecidedPlayers' ]         = 'Joueurs non inscrits';
        $gLocale[ 'AbsentNoReason' ]           = 'Aucune raison donnée.';
        $gLocale[ 'Undecided' ]                = 'Ne s\'est pas encore inscrit.';
        $gLocale[ 'MarkAsAbesent' ]            = 'Noter comment absent';
        $gLocale[ 'MakeAbsent' ]               = 'Le joueur sera absent';
        $gLocale[ 'AbsentMessage' ]            = 'Merci de saisir la raison pour laquelle le joueur sera absent.<br/>Le message aura votre nom d\'utilisateur comme préfixe.';
        $gLocale[ 'SetupBy' ]                  = 'Validé';
        $gLocale[ 'AbsentBy' ]                 = 'Noté Absent';
        $gLocale[ 'SwitchChar' ]               = 'Changé de personnage';
        $gLocale[ 'RaidNotFound' ]             = 'Le raid n\'a pas pu être trouvé.';
		$gLocale[ 'RaidSetup' ]                = 'Configuration du raid';
		$gLocale[ 'LinkToRaid' ]               = 'Lien vers le raid';
		$gLocale[ 'Switch' ]                   = 'Changement';
        $gLocale[ 'Retire' ]                   = 'Absent';
        $gLocale[ 'Export' ]                   = 'Exporter';
        $gLocale[ 'ExportFile' ]               = 'Fichier';
        $gLocale[ 'ExportClipboard' ]          = 'Presse-Papiers';
        $gLocale[ 'CopyOk' ]                   = 'Texte a été copié dans le presse-papiers';
        $gLocale[ 'Random' ]                   = 'Inconnu';
        
        // Profile
        $gLocale[ 'Profile' ]                  = 'Profil';
        $gLocale[ 'History' ]                  = 'Historique des raids';
        $gLocale[ 'Characters' ]               = 'Vos personnages';
        $gLocale[ 'CharName' ]                 = 'Nom';
        $gLocale[ 'NoName' ]                   = 'Un nouveau personnage n\'a pas de nom défini.';
        $gLocale[ 'NoClass' ]                  = 'Un nouveau personnage n\'a pas de classe définie.';
        $gLocale[ 'DeleteCharacter' ]          = 'Effacer le personnage';
        $gLocale[ 'ConfirmDeleteCharacter' ]   = 'Voulez-vous vraiment effacer ce personnage?';
        $gLocale[ 'AttendancesRemoved' ]       = 'Toutes les inscriptions en cours seront également supprimées.';
        $gLocale[ 'RaidAttendance' ]           = 'Inscription raid';
        $gLocale[ 'RolesInRaids' ]             = 'Roles dans les raids avec inscription';
        $gLocale[ 'Queued' ]                   = 'En file d\'attente';
        $gLocale[ 'Attended' ]                 = 'Inscrit';
        $gLocale[ 'Missed' ]                   = 'Manqué';
        $gLocale[ 'ChangePassword' ]           = 'Changer le mot de passe';
        $gLocale[ 'OldPassword' ]              = 'Ancien mot de passe';
        $gLocale[ 'OldPasswordEmpty' ]         = 'L\'ancien mot de passe ne peut pas être vide.';
        $gLocale[ 'AdminPassword' ]            = 'Mot de passe administrateur';
        $gLocale[ 'AdminPasswordEmpty' ]       = 'Le mot de passe de l\'administrateur ne peut pas être vide.';
        $gLocale[ 'WrongPassword' ]            = 'Mauvais mot de passe.';
        $gLocale[ 'PasswordLocked' ]           = 'Le mot de passe ne peut pas être modifié.';
        $gLocale[ 'PasswordChanged' ]          = 'Le mot de passe a été modifié.';
        $gLocale[ 'UserNotFound' ]             = 'Utilisateur non trouvé.';
		$gLocale[ 'VacationStart' ]            = 'Premier jour de vacance';
		$gLocale[ 'VacationEnd' ]              = 'Dernier jour de vacance';
		$gLocale[ 'NoStartDate' ]              = 'Entrez votre premier jour de vacance s\'il vous plait.';
		$gLocale[ 'NoEndDate' ]                = 'Entrez votre dernier jour de vacance s\'il vous plait.';
		$gLocale[ 'VacationMessage' ]          = 'Message d\'absence';
		$gLocale[ 'ClearVacation' ]            = 'Supprimer les données de vacances';
        $gLocale[ 'AutoAttend' ]               = 'Inscription Automatique';

        // Settings
        $gLocale[ 'Settings' ]                 = 'Réglages';
        $gLocale[ 'Locked' ]                   = 'Verouillé';
        $gLocale[ 'Members' ]                  = 'Membres';
        $gLocale[ 'Raidleads' ]                = 'Raidleads';
        $gLocale[ 'Administrators' ]           = 'Administrateurs';
        $gLocale[ 'ConfirmDeleteUser' ]        = 'Voulez-vous vraiment supprimer cet utilisateur?';
        $gLocale[ 'DeleteUser' ]               = 'Effacer l\'utilisateur';
        $gLocale[ 'MoveUser' ]                 = 'Déplacer l\'utilisateur vers le groupe';
        $gLocale[ 'UnlinkUser' ]               = 'Arrêter la synchronisation et convertir en utilisateur local.';
        $gLocale[ 'LinkUser' ]                 = 'Synchroniser l\'utilisateur';
        $gLocale[ 'SyncFailed' ]               = 'Echec de la synchronisation.</br>Aucun utilisateur correspondant trouvé.';
        $gLocale[ 'EditForeignCharacters' ]    = 'Editer les personnages pour :';
        $gLocale[ 'ConfirmDeleteLocation' ]    = 'Voulez-vous vraiment effacer ce modèle de raid?';
        $gLocale[ 'NoteDeleteRaidsToo' ]       = 'Cela effacera également tous les raids de ce modèle.';
        $gLocale[ 'DeleteRaids' ]              = 'Effacer les raids';
        $gLocale[ 'DeleteLocationRaids' ]      = 'Effacer le modèle et les raids';
        $gLocale[ 'LockRaids' ]                = 'Verouiller les raids';
        $gLocale[ 'AfterDone' ]                = 'quand un raid est terminé';
        $gLocale[ 'BeforeStart' ]              = 'avant que le raid commence';
        $gLocale[ 'Seconds' ]                  = 'Seconde(s)';
        $gLocale[ 'Minutes' ]                  = 'Minute(s)';
        $gLocale[ 'Hours' ]                    = 'Heure(s)';
        $gLocale[ 'Days' ]                     = 'Jour(s)';
        $gLocale[ 'Weeks' ]                    = 'Semaine(s)';
        $gLocale[ 'Month' ]                    = 'Mois';
        $gLocale[ 'TimeFormat' ]               = 'Format de la date';
        $gLocale[ 'StartOfWeek' ]              = 'La semaine commence le';
        $gLocale[ 'DefaultStartTime' ]         = 'Heure de début du raid par défaut';
        $gLocale[ 'DefaultEndTime' ]           = 'Heure de fin du raid par défaut';
        $gLocale[ 'DefaultRaidSize' ]          = 'Format du raid par défaut';
        $gLocale[ 'BannerPage' ]               = 'Lien de la bannière de page';
        $gLocale[ 'HelpPage' ]                 = 'Lien de la page de l\'Aide';
        $gLocale[ 'Game' ]                     = 'Jeu';
        $gLocale[ 'Theme' ]                    = 'Theme';
        $gLocale[ 'ApiPrivate' ]               = 'API token (privé)';
        $gLocale[ 'RaidSetupStyle' ]           = 'Type de validation';
        $gLocale[ 'RaidModeManual' ]           = 'par le raidlead';
        $gLocale[ 'RaidModeOverbook' ]         = 'Par raidlead avec surréservation';
        $gLocale[ 'RaidModeAttend' ]           = 'par l\'inscription';
        $gLocale[ 'RaidModeAll' ]              = 'juste une liste';
        $gLocale[ 'RaidModeOptOut' ]           = 'Inscription de tous les joueurs';
        $gLocale[ 'UpdateCheck' ]              = 'Vérifier les mises à jour';
        $gLocale[ 'UpToDate' ]                 = 'Ce raidplanner est à jour.';
        $gLocale[ 'NewVersionAvailable' ]      = 'Il y a une nouvelle version disponible:';
        $gLocale[ 'VisitProjectPage' ]         = 'Visiter la page du projet';
        $gLocale[ 'AttendWithPrimary' ]        = 'Assister avec le rôle principal';
        $gLocale[ 'CalendarBigIcons' ]         = null;
    }

    if ( defined('LOCALE_SETUP') )
    {
        // General
        $gLocale[ 'Ok' ]                       = 'Ok';
        $gLocale[ 'Back' ]                     = 'Retour';
        $gLocale[ 'Continue' ]                 = 'Continuer';
        $gLocale[ 'Error' ]                    = 'Erreur';
        $gLocale[ 'Ignore' ]                   = 'Ignorer';
        $gLocale[ 'Retry' ]                    = 'Réessayer';
        $gLocale[ 'DatabaseError' ]            = 'Erreur de la base de données';

        // Menu
        $gLocale[ 'Install' ]                  = 'Installer';
        $gLocale[ 'Update' ]                   = 'Mettre à jour';
        $gLocale[ 'EditBindings' ]             = 'Editer les liens';
        $gLocale[ 'EditConfig' ]               = 'Editer la configuration';
        $gLocale[ 'ResetPassword' ]            = 'Définir le mot de passe admin';
        $gLocale[ 'RepairDatabase' ]           = 'Réparer la base de données';

        // Checks
        $gLocale[ 'FilesystemChecks' ]         = 'Vérification des permissions du système de fichiers';
        $gLocale[ 'NotWriteable' ]             = 'Non inscriptible';
        $gLocale[ 'ConfigFolder' ]             = 'Dossier de configuration';
        $gLocale[ 'MainConfigFile' ]           = 'Fichier de configuration principal';
        $gLocale[ 'DatabaseConnection' ]       = 'Connexion à la base de données';
        $gLocale[ 'WritePermissionRequired' ]  = 'La configuration a besoin des droits d\'écriture sur tous les fichiers dans le dossier de configuration situé ici ';
        $gLocale[ 'ChangePermissions' ]        = 'Si une de ces vérifications échoue vous devez changer les permissions à "inscriptible" poue l\'utilisateur de votre serveur http.';
        $gLocale[ 'FTPClientHelp' ]            = 'Pour savoir comment changer les permission, merci de consulter les fichiers d\'aide de votre client FTP.';
        $gLocale[ 'OutdatedPHP' ]              = 'Version PHP périmée';
        $gLocale[ 'PHPVersion' ]               = 'Version PHP';
        $gLocale[ 'MbStringModule' ]           = 'Mbstring module';
        $gLocale[ 'MbStringNotFound' ]         = 'Mbstring pas configuré avec PHP';
        $gLocale[ 'PDOModule' ]                = 'PDO module';
        $gLocale[ 'PDONotFound' ]              = 'PDO pas configuré avec PHP';
        $gLocale[ 'PDOMySQLModule' ]           = 'PDO MySQL driver';
        $gLocale[ 'PDOMySQLNotFound' ]         = 'PDO MySQL driver introuvable';
        $gLocale[ 'PHPRequirements' ]          = 'Le raidplaner a besoin d\'une installation PHP 5.3 configurée avec les extension PDO.';

        // Database setup
        $gLocale[ 'ConfigureDatabase' ]        = 'Merci de configurer la base de données dans laquelle le planner stockera ses données.';
        $gLocale[ 'SameAsForumDatabase' ]      = 'Si vous voulez associer le raidplaner à un fourm tiers, la base de données du forum doit être sur le même serveur que celle du raidplanner.';
        $gLocale[ 'EnterPrefix' ]              = 'Si la base de données est déjà utilisée par une autre installation vous pouvez saisir une préfixe pour éviter les conflits.';
        $gLocale[ 'DatabaseHost' ]             = 'Hôte base de données';
        $gLocale[ 'RaidplanerDatabase' ]       = 'Base de données du raidplaner';
        $gLocale[ 'UserWithDBPermissions' ]    = 'Utilsateur avec les permissions pour cette base de données';
        $gLocale[ 'UserPassword' ]             = 'Mot de passe pour cet utilisateur';
        $gLocale[ 'RepeatPassword' ]           = 'Resaisissez le mot de passe';
        $gLocale[ 'TablePrefix' ]              = 'Prefixe pour les tables dans la base de données';
        $gLocale[ 'VerifySettings' ]           = 'Vérifier cette configuration';
        $gLocale[ 'ConnectionTestFailed' ]     = 'Test de connexion échoué';
        $gLocale[ 'ConnectionTestOk' ]         = 'Test de connexion réussi';

        // Registration and admin
        $gLocale[ 'AdminName' ]                = 'Nom de l\'administrateur';
        $gLocale[ 'AdminPassword' ]            = 'Mot de passe de l\'administrateur';
        $gLocale[ 'AdminPasswordSetup']        = 'L\'administrateur (login name "admin") est un utilisateur qui a toujours toutes les autorisations.';
        $gLocale[ 'AdminNotMoveable']          = 'L\'administrateur ne peut pas être renommé ou déplacé dans un groupe différent.';
        $gLocale[ 'AdminPasswordNoMatch' ]     = 'Les mots de passe admministrateur ne correspondent pas.';
        $gLocale[ 'AdminPasswordEmpty' ]       = 'Le mot de passe admin ne peut pas être vide.';
        $gLocale[ 'DatabasePasswordNoMatch' ]  = 'Les mots de passe de la base de données ne correspondent pas.';
        $gLocale[ 'DatabasePasswordEmpty' ]    = 'Le mot de passe de la base de données ne peut pas être vide.';
        $gLocale[ 'AllowManualRegistration' ]  = 'Autoriser les utilisateurs à s\'inscrire';
        $gLocale[ 'AllowGroupSync' ]           = 'Synchroniser les groupes d\'utilisateurs externes';
        $gLocale[ 'AllowPublicMode' ]          = 'Enregistrer de nouveaux utilisateurs comme membres (non recommandé)';
        $gLocale[ 'UseClearText' ]             = 'Soumettre le mot de passe en texte clair (non recommandé)';

        // Install/Update
        $gLocale[ 'SecurityWarning' ]          = 'Alerte de sécurité';
        $gLocale[ 'UpdateComplete' ]           = 'Mise à jour terminée';
        $gLocale[ 'RaidplanerSetupDone' ]      = 'Le raidplaner a bien été installé.';
        $gLocale[ 'DeleteSetupFolder' ]        = 'Vous devriez maintenant effacer le dossier "setup" et sécuriser les dossiers suivant (ex par htaccess):';
        $gLocale[ 'ThankYou' ]                 = 'Merci d\'utliser packedpixel Raidplaner.';
        $gLocale[ 'VisitBugtracker' ]          = 'Si vous rencontrez un quelconque bug ou avez des suggestions de fonctionnalités, merci de vous rendre sur notre bugtracker ';
        $gLocale[ 'VersionDetection' ]         = 'Détection de version et mise à jour';
        $gLocale[ 'VersionDetectProgress' ]    = 'L\'installation va essayer de détecter votre version actuelle';
        $gLocale[ 'ChooseManually' ]           = 'Si la version détectée ne correspond pas avec votre installation vous devriez également toujours choisir manuelle.';
        $gLocale[ 'OnlyDBAffected' ]           = 'La mise à jour affectera seulement les modifications dans la base de données.';
        $gLocale[ 'NoChangeNoAction' ]         = 'Si la base de données n\'a pas été modifiée vous devrez recommencer cette étape.';
        $gLocale[ 'DetectedVersion' ]          = 'Version détectée';
        $gLocale[ 'NoUpdateNecessary' ]        = 'Aucune mise à jour nécessaire.';
        $gLocale[ 'UpdateFrom' ]               = 'Mettre à jour depuis la version';
        $gLocale[ 'UpdateTo' ]                 = 'vers version';
        $gLocale[ 'UpdateErrors' ]             = 'Erreurs de mise à jour';
        $gLocale[ 'ReportedErrors' ]           = 'Les erreurs suivants ont été signalées pendant la mise à jour.';
        $gLocale[ 'PartiallyUpdated' ]         = 'Cela pourra avoir des répercussions sur une base de données (partiellement) mise à jour.';
        $gLocale[ 'GameconfigNotFound' ]       = 'Le fichier gameconfig.php n\'a pas été trouvé.';
		$gLocale[ 'FailedGameconfig' ]         = 'Le fichier gameconfig.php n\'a pas pus être transformé dans le nouveau format.';
		$gLocale[ 'RemoveAndLaunch' ]          = 'Supprimer et relancer la configuration';
		$gLocale[ 'FailedRemoveSetup' ]        = 'Le dossier de configuration n\'a pu être trouvé.';

        // Repair
        $gLocale[ 'Repair' ]                   = 'Réparer';
        $gLocale[ 'RepairDone' ]               = 'Réparation effectuée.';
        $gLocale[ 'BrokenDatabase' ]           = 'La base de données semble être corrompue';
        $gLocale[ 'EnsureValidDatabase' ]      = 'Assurer une base de données valide';
        $gLocale[ 'ItemsRepaired' ]            = 'Items reparés';
        $gLocale[ 'ItemsToResolve' ]           = 'Items qui ont besoin d\'être réglés manuellement';
        $gLocale[ 'InvalidCharacters' ]        = 'Personnages invalides';
        $gLocale[ 'InvalidAttendances' ]       = 'Inscription invalides';
        $gLocale[ 'Delete' ]                   = 'Effacer';
        $gLocale[ 'Resolve' ]                  = 'Résoudre';
        $gLocale[ 'StrayRoles' ]               = 'Rôles invalides';
        $gLocale[ 'StrayCharacters' ]          = 'Personnages effacés';
        $gLocale[ 'StrayUsers' ]               = 'Utilisateurs effacés';
        $gLocale[ 'StrayBindings' ]            = 'Utilisateurs invalides';
        $gLocale[ 'RepairCharacters' ]         = 'Réparer personnages non valide';
        $gLocale[ 'TransferGameconfig' ]       = 'Convertir gameconfig.php encore (Raidplaner 1.0.x)';
        $gLocale[ 'MergeGames' ]               = 'Fusionner deux jeux';
        $gLocale[ 'SourceGame' ]               = 'Source (changée)';
        $gLocale[ 'TargetGame' ]               = 'Cible';
        $gLocale[ 'ChooseRepairs' ]            = 'Choisir une ou plusieurs réparations pour être exécutées.';
        $gLocale[ 'Fixing' ]                   = 'Fixer';
        $gLocale[ 'StrayChars' ]               = 'Personnage orphelins';
        $gLocale[ 'StrayAttends' ]             = 'Attentes orphelines';
        $gLocale[ 'InvalidCharacters' ]        = 'Personnage invalide';
        $gLocale[ 'SameGame' ]                 = 'Les deux jeux sont identiques';   
        $gLocale[ 'Merged' ]                   = 'Converti:'; 
        $gLocale[ 'Locations' ]                = 'Localisation'; 
        $gLocale[ 'Characters' ]               = 'Personnages'; 

        // Plugin setup
        $gLocale[ 'LoadGroups' ]               = 'Charger les groupes utilisant ces réglages';
		$gLocale[ 'AutoMemberLogin' ]          = '"Membres" Groupes:';
		$gLocale[ 'AutoLeadLogin' ]            = '"Raidlead" Groupes:';
        $gLocale[ 'ReloadFailed' ]             = 'Rechargement échoué';
		$gLocale[ 'LoadSettings' ]             = 'Rapatriement des options automatique';
		$gLocale[ 'BindingBasePath' ]          = 'Veuillez renseigner le chemin d\'installation de la liaison relative à \''.$_SERVER['DOCUMENT_ROOT'].'\'.';
		$gLocale[ 'RetrievalFailed' ]          = 'Échec du rapatriement automatique ';
		$gLocale[ 'RetrievalOk' ]              = 'Rapatriement des données effectué avec succès';
		$gLocale[ 'NotExisting' ]              = 'n’existe pas';
		$gLocale[ 'AllowAutoLogin' ]           = 'Autoriser la connexion automatique';
		$gLocale[ 'NoValidConfig' ]            = 'Pas de fichier de configuration valide trouvé.';
		$gLocale[ 'CookieNote' ]               = 'La connexion automatique requiert que le raidplanner soit installé dans un sous-dossier de liaison avec les cookies. '.
                                                 'Le chemin doit être changé dans la configuration des liaisons.';
		$gLocale[ 'PostToForum' ]              = 'Poster un nouveau raid sur le forum';
		$gLocale[ 'PostAsUser' ]               = 'Poster un nouveau raid sur cet utilisateur';
		$gLocale[ 'DisablePosting' ]           = 'Ne pas créer de post';
		$gLocale[ 'NoUsersFound' ]             = 'Utilisateur introuvable';
        $gLocale[ 'Version' ]                  = 'Version';

        // PHPBB3
        $gLocale[ 'phpbb3_Binding' ]            = 'PHPBB 3.x';
        $gLocale[ 'phpbb3_ConfigFile' ]         = 'PHPBB3 Fichier de configuration';
        $gLocale[ 'phpbb3_Database' ]           = 'PHPBB3 Base de données';
        $gLocale[ 'phpbb3_DatabaseEmpty' ]      = 'PHPBB3 Base de données ne peut pas être vide.';
        $gLocale[ 'phpbb3_UserEmpty' ]          = 'PHPBB3 utilisateur ne peut pas être vide.';
        $gLocale[ 'phpbb3_PasswordEmpty' ]      = 'PHPBB3 Mot de passe de la base de données ne peut pas être vide.';
        $gLocale[ 'phpbb3_DBPasswordsMatch' ]   = 'PHPBB3 Les mots de passe de la base de données ne correspondent pas.';

        // EQDKP
        $gLocale[ 'eqdkp_Binding' ]             = 'EQDKP';
        $gLocale[ 'eqdkp_ConfigFile' ]          = 'EQDKP Fichier de configuration';
        $gLocale[ 'eqdkp_Database' ]            = 'EQDKP Base de données';
        $gLocale[ 'eqdkp_DatabaseEmpty' ]       = 'EQDKP Base de données ne peut pas être vide.';
        $gLocale[ 'eqdkp_UserEmpty' ]           = 'EQDKP utilisateur ne peut pas être vide.';
        $gLocale[ 'eqdkp_PasswordEmpty' ]       = 'EQDKP Mot de passe de la base de données ne peut pas être vide.';
        $gLocale[ 'eqdkp_DBPasswordsMatch' ]    = 'EQDKP Les mots de passe de la base de données ne correspondent pas.';

        // vBulletin
        $gLocale[ 'vb3_Binding' ]               = 'vBulletin 3 / 4';
        $gLocale[ 'vb3_ConfigFile' ]            = 'vBulletin Fichier de configuration';
        $gLocale[ 'vb3_Database' ]              = 'vBulletin Base de données';
        $gLocale[ 'vb3_DatabaseEmpty' ]         = 'vBulletin Base de données ne peut pas être vide.';
        $gLocale[ 'vb3_UserEmpty' ]             = 'vBulletin utilisateur ne peut pas être vide.';
        $gLocale[ 'vb3_PasswordEmpty' ]         = 'vBulletin Mot de passe de la base de données ne peut pas être vide.';
        $gLocale[ 'vb3_DBPasswordsMatch' ]      = 'vBulletin Les mots de passe de la base de données ne correspondent pas.';
        $gLocale[ 'vb3_CookieEx' ]              = 'vBulletin cookie préfixe';

        // MyBB
        $gLocale[ 'mybb_Binding' ]              = 'MyBB 1.6+';
        $gLocale[ 'mybb_ConfigFile' ]           = 'MyBB Fichier de configuration';
        $gLocale[ 'mybb_Database' ]             = 'MyBB Base de données';
        $gLocale[ 'mybb_DatabaseEmpty' ]        = 'MyBB Base de données ne peut pas être vide.';
        $gLocale[ 'mybb_UserEmpty' ]            = 'MyBB utilisateur ne peut pas être vide.';
        $gLocale[ 'mybb_PasswordEmpty' ]        = 'MyBB Mot de passe de la base de données ne peut pas être vide.';
        $gLocale[ 'mybb_DBPasswordsMatch' ]     = 'MyBB Les mots de passe de la base de données ne correspondent pas.';

        // SMF
        $gLocale[ 'smf_Binding' ]               = 'Simple Machines Forum 2.x';
        $gLocale[ 'smf_ConfigFile' ]            = 'SMF Fichier de configuration';
        $gLocale[ 'smf_Database' ]              = 'SMF Base de données';
        $gLocale[ 'smf_DatabaseEmpty' ]         = 'SMF Base de données ne peut pas être vide.';
        $gLocale[ 'smf_UserEmpty' ]             = 'SMF utilisateur ne peut pas être vide.';
        $gLocale[ 'smf_PasswordEmpty' ]         = 'SMF Mot de passe de la base de données ne peut pas être vide.';
        $gLocale[ 'smf_DBPasswordsMatch' ]      = 'SMF Les mots de passe de la base de données ne correspondent pas.';
        $gLocale[ 'smf_CookieEx' ]              = 'SMF nom de cookie';

        // Vanilla
        $gLocale[ 'vanilla_Binding' ]           = 'Vanilla Forum 2.x';
        $gLocale[ 'vanilla_ConfigFile' ]        = 'Vanilla Fichier de configuration';
        $gLocale[ 'vanilla_Database' ]          = 'Vanilla Base de données';
        $gLocale[ 'vanilla_DatabaseEmpty' ]     = 'Vanilla Base de données ne peut pas être vide.';
        $gLocale[ 'vanilla_UserEmpty' ]         = 'Vanilla utilisateur ne peut pas être vide.';
        $gLocale[ 'vanilla_PasswordEmpty' ]     = 'Vanilla Mot de passe de la base de données ne peut pas être vide.';
        $gLocale[ 'vanilla_DBPasswordsMatch' ]  = 'Vanilla Les mots de passe de la base de données ne correspondent pas.';
        $gLocale[ 'vanilla_CookieEx' ]          = 'Le nom de cookie, la méthode de hash (par exemple md5), salt de cookie';

        // Joomla
        $gLocale[ 'jml3_Binding' ]              = 'Joomla 3.x';
        $gLocale[ 'jml3_ConfigFile' ]           = 'Joomla3 Fichier de configuration';
        $gLocale[ 'jml3_Database' ]             = 'Joomla3 Base de données';
        $gLocale[ 'jml3_DatabaseEmpty' ]        = 'Joomla3 Base de données ne peut pas être vide.';
        $gLocale[ 'jml3_UserEmpty' ]            = 'Joomla3 utilisateur ne peut pas être vide.';
        $gLocale[ 'jml3_PasswordEmpty' ]        = 'Joomla3 Mot de passe de la base de données ne peut pas être vide.';
        $gLocale[ 'jml3_DBPasswordsMatch' ]     = 'Joomla3 Les mots de passe de la base de données ne correspondent pas.';
        $gLocale[ 'jml3_CookieEx' ]             = 'Joomla3 secret';

        // Drupal
        $gLocale[ 'drupal_Binding' ]            = 'Drupal 7.6+';
        $gLocale[ 'drupal_ConfigFile' ]         = 'Drupal Fichier de configuration';
        $gLocale[ 'drupal_Database' ]           = 'Drupal Base de données';
        $gLocale[ 'drupal_DatabaseEmpty' ]      = 'Drupal Base de données ne peut pas être vide.';
        $gLocale[ 'drupal_UserEmpty' ]          = 'Drupal utilisateur ne peut pas être vide.';
        $gLocale[ 'drupal_PasswordEmpty' ]      = 'Drupal Mot de passe de la base de données ne peut pas être vide.';
        $gLocale[ 'drupal_DBPasswordsMatch' ]   = 'Drupal Les mots de passe de la base de données ne correspondent pas.';
        $gLocale[ 'drupal_CookieEx' ]           = 'Drupal URL de base';

        // Wordpress
        $gLocale[ 'wp_Binding' ]                = 'Wordpress 3 / 4';
        $gLocale[ 'wp_ConfigFile' ]             = 'Wordpress Fichier de configuration';
        $gLocale[ 'wp_Database' ]               = 'Wordpress Base de données';
        $gLocale[ 'wp_DatabaseEmpty' ]          = 'Wordpress Base de données ne peut pas être vide.';
        $gLocale[ 'wp_UserEmpty' ]              = 'Wordpress utilisateur ne peut pas être vide.';
        $gLocale[ 'wp_PasswordEmpty' ]          = 'Wordpress Mot de passe de la base de données ne peut pas être vide.';
        $gLocale[ 'wp_DBPasswordsMatch' ]       = 'Wordpress Les mots de passe de la base de données ne correspondent pas.';
        $gLocale[ 'wp_CookieEx' ]               = 'LOGGED_IN_KEY suivie LOGGED_IN_SALT';
        
        // Woltlab Burning Board
        $gLocale[ 'wbb_Binding' ]               = 'Burning Board 4.x';
        $gLocale[ 'wbb_ConfigFile' ]            = 'Burning Board Fichier de configuration';
        $gLocale[ 'wbb_Database' ]              = 'Burning Board Base de données';
        $gLocale[ 'wbb_DatabaseEmpty' ]         = 'Burning Board Base de données ne peut pas être vide.';
        $gLocale[ 'wbb_UserEmpty' ]             = 'Burning Board utilisateur ne peut pas être vide.';
        $gLocale[ 'wbb_PasswordEmpty' ]         = 'Burning Board Mot de passe de la base de données ne peut pas être vide.';
        $gLocale[ 'wbb_DBPasswordsMatch' ]      = 'Burning Board Les mots de passe de la base de données ne correspondent pas.';
        $gLocale[ 'wbb_CookieEx' ]              = 'Burning Board cookie préfixe';
    }
?>