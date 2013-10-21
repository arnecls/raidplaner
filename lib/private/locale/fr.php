<?php
    if ( defined("LOCALE_MAIN") )
    {
        // Pre-loading checks
        $gLocale[ "ContinueNoUpdate" ]         = "Poursuivre sans mettre à jour";
        $gLocale[ "UpdateBrowser" ]            = "Merci de mettre votre navigateur à jour";
        $gLocale[ "UsingOldBrowser" ]          = "Vous utilisez une version périmée de votre navigateur";
        $gLocale[ "OlderBrowserFeatures" ]     = "Les vieux navigateurs ne supportent pas toutes les fonctionnalités requises ou n'affichent pas le site correctement.";
        $gLocale[ "DownloadNewBrowser" ]       = "Vous devriez mettre à jour votre navigateur ou télécharger un des navigateurs suivants.";
        $gLocale[ "RaidplanerNotConfigured" ]  = "Raidplaner n'est pas encore configuré ou a besoin d'une mise à jour.";
        $gLocale[ "PleaseRunSetup" ]           = "Lancez <a href=\"setup\">l'installation</a> ou suivez les instructions pour une <a href=\"http://code.google.com/p/ppx-raidplaner/wiki/ManualSetup\">installation manuelle</a>.";

        // General
        $gLocale[ "Reserved" ]                 = "Reservé";
        $gLocale[ "Error" ]                    = "Erreur";
        $gLocale[ "Apply" ]                    = "Appliquer les modifications";
        $gLocale[ "AccessDenied" ]             = "Accès refusé";
        $gLocale[ "ForeignCharacter" ]         = "Pas votre personnage";
        $gLocale[ "DatabaseError" ]            = "Erreur de la base de données";
        $gLocale[ "Cancel" ]                   = "Annuler";
        $gLocale[ "Notification" ]             = "Notification";
        $gLocale[ "Busy" ]                     = "Occupé. Merci de patienter.";
        $gLocale[ "RequestError" ]             = "Une requête a renvoyé une erreur.";
        $gLocale[ "UnknownRequest" ]           = "Requête inconnue";
        $gLocale[ "InvalidRequest" ]           = "Requête invalide";
        $gLocale[ "InputRequired" ]            = "Saisie requise";        
        $gLocale[ "UnappliedChanges" ]         = "Voulez vous annuler les modifications non sauvegardées?";
        $gLocale[ "DiscardChanges" ]           = "Oui annuler";
        $gLocale[ "to" ]                       = "vers";
        
        // Login und user registration
        $gLocale[ "Login" ]                    = "Connexion";
        $gLocale[ "Logout" ]                   = "Déconnexion";
        $gLocale[ "Username" ]                 = "Nom d'utilisateur";
        $gLocale[ "Password" ]                 = "Mot de passe";        
        $gLocale[ "RepeatPassword" ]           = "Saisissez à nouveau le mot de passe";
        $gLocale[ "Register" ]                 = "Inscription";
        $gLocale[ "EnterValidUsername" ]       = "Vous devez saisir un nom d'utilisateur valide.";
        $gLocale[ "EnterNonEmptyPassword" ]    = "Vous devez saisir un mot de passe.";
        $gLocale[ "PasswordsNotMatch" ]        = "Les mots de passe ne correspondent pas.";
        $gLocale[ "NameInUse" ]                = "Ce nom d'utilisateur est déjà utilisé.";    
        $gLocale[ "RegistrationDone" ]         = "Inscription terminée.";
        $gLocale[ "AccountIsLocked" ]          = "Votre compte est actuellement verouillé.";
        $gLocale[ "ContactAdminToUnlock" ]     = "Merci de contacter un administrateur pour dévérouiller votre compte.";
        $gLocale[ "NoSuchUser" ]               = "Cet utilisateur ne peut pas être trouvé.";
        $gLocale[ "HashingInProgress" ]        = "Hashing du mot de passe";
        $gLocale[ "PassStrength"]              = "Complexité du mot de passe";
        
        // Calendar
        $gLocale[ "Calendar" ]                 = "Calendrier";
        $gLocale[ "January" ]                  = "Janvier";
        $gLocale[ "February" ]                 = "Février";
        $gLocale[ "March" ]                    = "Mars";
        $gLocale[ "April" ]                    = "Avril";
        $gLocale[ "May" ]                      = "Mai";
        $gLocale[ "June" ]                     = "Juie";
        $gLocale[ "July" ]                     = "Juillet";
        $gLocale[ "August" ]                   = "Août";
        $gLocale[ "September" ]                = "Septembre";
        $gLocale[ "October" ]                  = "Octobre";
        $gLocale[ "November" ]                 = "Novembre";
        $gLocale[ "December" ]                 = "Décembre";    
        $gLocale[ "Monday" ]                   = "Lundi";
        $gLocale[ "Tuesday" ]                  = "Mardi";
        $gLocale[ "Wednesday" ]                = "Mercredi";
        $gLocale[ "Thursday" ]                 = "Jeudi";
        $gLocale[ "Friday" ]                   = "Vendredi";
        $gLocale[ "Saturday" ]                 = "Samedi";
        $gLocale[ "Sunday" ]                   = "Dimanche";          
        $gLocale[ "Mon" ]                      = "Lu";
        $gLocale[ "Tue" ]                      = "Ma";
        $gLocale[ "Wed" ]                      = "Me";
        $gLocale[ "Thu" ]                      = "Je";
        $gLocale[ "Fri" ]                      = "Ve";
        $gLocale[ "Sat" ]                      = "Sa";
        $gLocale[ "Sun" ]                      = "Di";
        $gLocale[ "NotSignedUp" ]              = "Pas inscrit";
        $gLocale[ "Absent" ]                   = "Absent";
        $gLocale[ "QueuedAs" ]                 = "Inscrit en tant que ";
        $gLocale[ "Raiding" ]                  = "Raideur en tant que ";
        $gLocale[ "WhyAbsent" ]                = "Merci de nous dire pourquoi vous serez absent.";
        $gLocale[ "SetAbsent" ]                = "Mettre absent";
        $gLocale[ "Comment" ]                  = "Commentaire";
        $gLocale[ "SaveComment" ]              = "Sauvegarder le commentaire";
        
        // Raid
        $gLocale[ "Raid" ]                     = "Raid";
        $gLocale[ "Upcoming" ]                 = "Raids à venir";
        $gLocale[ "CreateRaid" ]               = "Créer raid";
        $gLocale[ "NewDungeon" ]               = "Nouveau raid";
        $gLocale[ "Description" ]              = "Description";
        $gLocale[ "DefaultRaidMode" ]          = "Mode de validation par défaut"; 
        $gLocale[ "RaidStatus" ]               = "Statut";
        $gLocale[ "RaidOpen" ]                 = "Raid ouvert";
        $gLocale[ "RaidLocked" ]               = "Raid verouillé";
        $gLocale[ "RaidCanceled" ]             = "Raid annulé";
        $gLocale[ "DeleteRaid" ]               = "Supprimer ce raid";
        $gLocale[ "ConfirmRaidDelete" ]        = "Voulez vous vraiment supprimer ce raid?";
        $gLocale[ "Players" ]                  = "Joueurs";
        $gLocale[ "RequiredForRole" ]          = "Places pour le role";
        $gLocale[ "AbsentPlayers" ]            = "Joueurs absents";
        $gLocale[ "UndecidedPlayers" ]         = "Joueurs non inscrits";
        $gLocale[ "AbsentNoReason" ]           = "Aucune raison donnée.";
        $gLocale[ "Undecided" ]                = "Ne s'est pas encore inscrit.";
        $gLocale[ "MarkAsAbesent" ]            = "Noter comment absent";
        $gLocale[ "MakeAbsent" ]               = "Le joueur sera absent";
        $gLocale[ "AbsentMessage" ]            = "Merci de saisir la raison pour laquelle le joueur sera absent.<br/>Le message aura votre nom d'utilisateur comme préfixe.";
        $gLocale[ "SetupBy" ]                  = "Validé par ";
        $gLocale[ "SwitchChar" ]               = "Changé de personnage";
        
        // Classes
        $gLocale[ "Deathknight" ]              = "DK";
        $gLocale[ "Druid" ]                    = "Druide";
        $gLocale[ "Hunter" ]                   = "Chasseur";
        $gLocale[ "Mage" ]                     = "Mage";
        $gLocale[ "Monk" ]                     = "Moine";
        $gLocale[ "Paladin" ]                  = "Paladin";
        $gLocale[ "Priest" ]                   = "Prêtre";
        $gLocale[ "Rogue" ]                    = "Voleur";
        $gLocale[ "Shaman" ]                   = "chaman";
        $gLocale[ "Warlock" ]                  = "Démoniste";
        $gLocale[ "Warrior" ]                  = "Guerrier";
        
        // Roles
        $gLocale[ "Tank" ]                     = "Tank";
        $gLocale[ "Healer" ]                   = "Heal";
        $gLocale[ "Damage" ]                   = "Dps";
        
        // Profile        
        $gLocale[ "Profile" ]                  = "Profil";
        $gLocale[ "History" ]                  = "Historique des raids";
        $gLocale[ "Characters" ]               = "Vos personnages";
        $gLocale[ "CharName" ]                 = "nom";
        $gLocale[ "NoName" ]                   = "Un nouveau personnage n'a pas de nom défini.";
        $gLocale[ "NoClass" ]                  = "Un nouveau personnage n'a pas de classe définie.";
        $gLocale[ "DeleteCharacter" ]          = "Effacer le personnage";
        $gLocale[ "ConfirmDeleteCharacter" ]   = "Voulez vous vraiment effacer ce personnage?";
        $gLocale[ "AttendancesRemoved" ]       = "Toutes les inscriptions en cours seront également supprimées.";
        $gLocale[ "RaidAttendance" ]           = "Inscription raid";
        $gLocale[ "RolesInRaids" ]             = "Roles dans les raids avec inscription";            
        $gLocale[ "Queued" ]                   = "En file d'attente";
        $gLocale[ "Attended" ]                 = "Inscrit";
        $gLocale[ "Missed" ]                   = "Manqué";
        $gLocale[ "ChangePassword" ]           = "Changer le mot de passe";
        $gLocale[ "OldPassword" ]              = "Ancien mot de passe";
        $gLocale[ "OldPasswordEmpty" ]         = "L'ancien mot de passe ne peut pas être vide.";
        $gLocale[ "AdminPassword" ]            = "Mot de passe administrateur";
        $gLocale[ "AdminPasswordEmpty" ]       = "Le mot de passe de l'administrateur ne peut pas être vide.";
        $gLocale[ "WrongPassword" ]            = "Mauvais mot de passe";
        $gLocale[ "PasswordLocked" ]           = "Le mot de passe ne peut pas être modifié.";
        $gLocale[ "PasswordChanged" ]          = "Le mot de passe a été modifié.";
                
        // Settings
        $gLocale[ "Settings" ]                 = "Réglages";
        $gLocale[ "Locked" ]                   = "Verouillé";
        $gLocale[ "Members" ]                  = "Membres";
        $gLocale[ "Raidleads" ]                = "Raidleads";
        $gLocale[ "Administrators" ]           = "Administrateurs";
        $gLocale[ "ConfirmDeleteUser" ]        = "Voulez vous vraiment supprimer cet utilisateur?";
        $gLocale[ "DeleteUser" ]               = "Effacer l'utilisateur";
        $gLocale[ "MoveUser" ]                 = "Déplacer l'utilisateur vers le groupe";
        $gLocale[ "UnlinkUser" ]               = "Arrêter la synchronisation et convertir en utilisateur local.";
        $gLocale[ "LinkUser" ]                 = "Synchroniser l'utilisateur";
        $gLocale[ "SyncFailed" ]               = "Echec de la synchronisation.</br>Aucun utilisateur correspondant trouvé.";
        $gLocale[ "EditForeignCharacters" ]    = "Editer les personnages pour :";
        $gLocale[ "ConfirmDeleteLocation" ]    = "Voulez vous vraiment effacer ce modèle de raid?";
        $gLocale[ "NoteDeleteRaidsToo" ]       = "Cela effacera également tous les raids de ce modèle.";
        $gLocale[ "DeleteRaids" ]              = "Effacer les raids";
        $gLocale[ "DeleteLocationRaids" ]      = "Effacer le modèle et les raids";
        $gLocale[ "LockRaids" ]                = "Verouiller les raids";
        $gLocale[ "AfterDone" ]                = "quand un raid est terminé";
        $gLocale[ "BeforeStart" ]              = "avant que le raid commence";
        $gLocale[ "Seconds" ]                  = "Seconde(s)";
        $gLocale[ "Minutes" ]                  = "Minute(s)";
        $gLocale[ "Hours" ]                    = "Heure(s)";
        $gLocale[ "Days" ]                     = "Jour(s)";
        $gLocale[ "Weeks" ]                    = "Semaine(s)";
        $gLocale[ "Month" ]                    = "Mois";
        $gLocale[ "TimeFormat" ]               = "Format de la date";
        $gLocale[ "StartOfWeek" ]              = "La semaine commence le";
        $gLocale[ "DefaultStartTime" ]         = "Heure de début du raid par défaut";
        $gLocale[ "DefaultEndTime" ]           = "Heure de fin du raid par défaut";
        $gLocale[ "DefaultRaidSize" ]          = "Format du raid par défaut";
        $gLocale[ "BannerPage" ]               = "Lien de la bannière de page";
        $gLocale[ "HelpPage" ]                 = "Lien de la page de l'Aide";
        $gLocale[ "Theme" ]                    = "Theme";
        $gLocale[ "RaidSetupStyle" ]           = "Type de validation";        
        $gLocale[ "RaidModeManual" ]           = "par le raidlead";
        $gLocale[ "RaidModeOverbook" ]         = "Par raidlead avec surréservation";
        $gLocale[ "RaidModeAttend" ]           = "par l'inscription";
        $gLocale[ "RaidModeAll" ]              = "juste une liste";                    
        $gLocale[ "UpdateCheck" ]              = "Vérifier les mises à jour";
        $gLocale[ "UpToDate" ]                 = "Ce raidplanner est à jour.";
        $gLocale[ "NewVersionAvailable" ]      = "Il y a une nouvelle version disponible:";
        $gLocale[ "VisitProjectPage" ]         = "Visit la page du projet";
    }
    
    if ( defined("LOCALE_SETUP") )
    {
        // General
        $gLocale[ "Ok" ]                       = "Ok";
        $gLocale[ "Back" ]                     = "Retour";
        $gLocale[ "Continue" ]                 = "Continuer";
        $gLocale[ "Error" ]                    = "Erreur";
        $gLocale[ "Ignore" ]                   = "Ignorer";
        $gLocale[ "Retry" ]                    = "Réessayer";
        $gLocale[ "DatabaseError" ]            = "Erreur de la base de données";
        
        // Menu
        $gLocale[ "Install" ]                  = "Installer";
        $gLocale[ "Update" ]                   = "Mettre à jour";
        $gLocale[ "EditBindings" ]             = "Editer les liens";
        $gLocale[ "EditConfig" ]               = "Editer la configuration";
        $gLocale[ "ResetPassword" ]            = "Définir le mot de passe admin";
        $gLocale[ "RepairDatabase" ]           = "Réparer la base de données";
                        
        // Checks
        $gLocale[ "FilesystemChecks" ]         = "Vérification des permissions du système de fichiers";
        $gLocale[ "NotWriteable" ]             = "Non inscriptible";
        $gLocale[ "ConfigFolder" ]             = "Dossier de configuration";
        $gLocale[ "MainConfigFile" ]           = "Fichier de configuration principal";
        $gLocale[ "DatabaseConnection" ]       = "Connexion à la base de données";
        $gLocale[ "WritePermissionRequired" ]  = "La configuration a besoin des droits d'écriture sur tous les fichiers dans le dossier de configuration situé ici ";
        $gLocale[ "ChangePermissions" ]        = "Si une de ces vérifications échoue vous devez changer les permissions à \"inscriptible\" poue l'utilisateur de votre serveur http.";
        $gLocale[ "FTPClientHelp" ]            = "Pour savoir comment changer les permission, merci de consulter les fichiers d'aide de votre client FTP.";
        $gLocale[ "OutdatedPHP" ]              = "Version PHP périmée";
        $gLocale[ "PHPVersion" ]               = "Version PHP";
        $gLocale[ "McryptModule" ]             = "module mcrypt";
        $gLocale[ "McryptNotFound" ]           = "Mcrypt pas configuré avec PHP";
        $gLocale[ "PDOModule" ]                = "PDO module";
        $gLocale[ "PDONotFound" ]              = "PDO pas configuré avec PHP";
        $gLocale[ "PDOMySQLModule" ]           = "PDO MySQL driver";
        $gLocale[ "PDOMySQLNotFound" ]         = "PDO MySQL driver introuvable";
        $gLocale[ "PHPRequirements" ]          = "Le raidplaner a besoin d'une installation PHP 5.2 configurée avec les extensions mcrypt et PDO.";
        
        // Database setup
        $gLocale[ "ConfigureDatabase" ]        = "Merci de configurer la base de données dans laquelle le planner stockera ses données.";
        $gLocale[ "SameAsForumDatabase" ]      = "Si vous voulez associer le raidplaner à un fourm tiers, la base de données du forum doit être sur le même serveur que celle du raidplanner.";
        $gLocale[ "EnterPrefix" ]              = "Si la base de données est déjà utilisée par une autre installation vous pouvez saisir une préfixe pour éviter les conflits.";
        $gLocale[ "DatabaseHost" ]             = "Hôte base de données";
        $gLocale[ "RaidplanerDatabase" ]       = "Base de données du raidplaner";
        $gLocale[ "UserWithDBPermissions" ]    = "Utilsateur avec les permissions pour cette base de données";
        $gLocale[ "UserPassword" ]             = "Mot de passe pour cet utilisateur";
        $gLocale[ "RepeatPassword" ]           = "Resaisissez le mot de passe";
        $gLocale[ "TablePrefix" ]              = "Prefixe pour les tables dans la base de données";
        $gLocale[ "VerifySettings" ]           = "Vérifier cette configuration";
        $gLocale[ "ConnectionTestFailed" ]     = "Test de connexion échoué";
        $gLocale[ "ConnectionTestOk" ]         = "Test de connexion réussi";
        
        // Registration and admin
        $gLocale[ "AdminPassword" ]            = "Mot de passe de l'administrateur";
        $gLocale[ "AdminPasswordSetup"]        = "L'administrateur (login name \"admin\") est un utilisateur qui a toujours toutes les autorisations.";
        $gLocale[ "AdminNotMoveable"]          = "L'administrateur ne peut pas être renommé ou déplacé dans un groupe différent.";
        $gLocale[ "AdminPasswordNoMatch" ]     = "Les mots de passe admministrateur ne correspondent pas.";
        $gLocale[ "AdminPasswordEmpty" ]       = "Le mot de passe admin ne peut pas être vide.";
        $gLocale[ "DatabasePasswordNoMatch" ]  = "Les mots de passe de la base de données ne correspondent pas.";
        $gLocale[ "DatabasePasswordEmpty" ]    = "Le mot de passe de la base de données ne peut pas être vide.";
        $gLocale[ "AllowManualRegistration" ]  = "Autoriser les utilisateurs à s'inscrire";
        $gLocale[ "AllowGroupSync" ]           = "Synchroniser les groupes d'utilisateurs externes";
        $gLocale[ "AllowPublicMode" ]          = "Enregistrer de nouveaux utilisateurs comme membres (non recommandé)";
        $gLocale[ "UseClearText" ]             = "Soumettre le mot de passe en texte clair (non recommandé)";
        
        // Install/Update
        $gLocale[ "SetupComplete" ]            = "Installation terminée";
        $gLocale[ "UpdateComplete" ]           = "Mise à jour terminée";
        $gLocale[ "RaidplanerSetupDone" ]      = "Le raidplaner a bien été installé.";
        $gLocale[ "DeleteSetupFolder" ]        = "Vous devriez maintenant effacer le dossier \"setup\" et sécuriser les dossiers suivant (ex par htaccess):";
        $gLocale[ "ThankYou" ]                 = "Merci d'utliser packedpixel Raidplaner.";
        $gLocale[ "VisitBugtracker" ]          = "Si vous rencontrez un quelconque bug ou avez des suggestions de fonctionnalités, merci de vous rendre sur notre bugtracker ";
        $gLocale[ "VersionDetection" ]         = "Détection de version et mise à jour";
        $gLocale[ "VersionDetectProgress" ]    = "L'installation va essayer de détecter votre version actuelle";
        $gLocale[ "ChooseManually" ]           = "Si la version détectée ne correspond pas avec votre installation vous devriez également toujours choisir manuelle.";
        $gLocale[ "OnlyDBAffected" ]           = "La mise à jour affectera seulement les modifications dans la base de données.";
        $gLocale[ "NoChangeNoAction" ]         = "Si la base de données n'a pas été modifiée vous devrez recommencer cette étape.";
        $gLocale[ "DetectedVersion" ]          = "Version détectée";
        $gLocale[ "NoUpdateNecessary" ]        = "Aucune mise à jour nécessaire.";
        $gLocale[ "UpdateFrom" ]               = "Mettre à jour depuis la version";
        $gLocale[ "UpdateTo" ]                 = "vers version";
        $gLocale[ "UpdateErrors" ]             = "Erreurs de mise à jour";
        $gLocale[ "ReportedErrors" ]           = "Les erreurs suivants ont été signalées pendant la mise à jour.";
        $gLocale[ "PartiallyUpdated" ]         = "Cela pourra avoir des répercussions sur une base de données (partiellement) mise à jour.";
        
        // Repair
        $gLocale[ "Repair" ]                   = "Réparer les irrégularités de la base de données";
        $gLocale[ "GameconfigProblems" ]       = "En modifiant lib/gameconfig.php des irrégularités dans la base de données peuvent apparaître (ex : personnages avec des rôles invalides).";
        $gLocale[ "RepairTheseProblems" ]      = "Ce script règle le problème du mieux possible.";
        $gLocale[ "RepairDone" ]               = "Réparation effectuée.";
        $gLocale[ "BrokenDatabase" ]           = "La base de données semble être corrompue";
        $gLocale[ "EnsureValidDatabase" ]      = "Assurer une base de données valide";        
        $gLocale[ "ItemsRepaired" ]            = "Items reparés";
        $gLocale[ "ItemsToResolve" ]           = "Items qui ont besoin d'être réglés manuellement";
        $gLocale[ "InvalidCharacters" ]        = "Personnages invalides";
        $gLocale[ "InvalidAttendances" ]       = "Inscription invalides";
        $gLocale[ "Delete" ]                   = "Effacer";
        $gLocale[ "Resolve" ]                  = "Résoudre";
        $gLocale[ "StrayRoles" ]               = "Rôles invalides";
        $gLocale[ "StrayCharacters" ]          = "Personnages effacés";
        $gLocale[ "StrayUsers" ]               = "Utilisateurs effacés";
        
        // Plugin setup
        $gLocale[ "LoadGroups" ]               = "Charger les groupes utilisant ces réglages";
        $gLocale[ "AutoMemberLogin" ]          = "Les utilisateurs des groupes sélectionnés suivants se connecteront en tant que \"membres\":";
        $gLocale[ "AutoLeadLogin" ]            = "Les utilisateurs des groupes sélectionnés suivants se connecteront en tant que \"raidlead\":";
        $gLocale[ "ReloadFailed" ]             = "Rechargement échoué";
        
        
        // PHPBB3        
        $gLocale[ "PHPBB3Binding" ]            = "PHPBB3";
        $gLocale[ "PHPBB3ConfigFile" ]         = "PHPBB3 Fichier de configuration";
        $gLocale[ "PHPBB3Database" ]           = "PHPBB3 Base de données";
        $gLocale[ "PHPBBPasswordEmpty" ]       = "PHPBB Mot de passe de la base de données ne peut pas être vide.";
        $gLocale[ "PHPBBDBPasswordsMatch" ]    = "PHPBB Les mots de passe de la base de données ne correspondent pas.";
        
        // EQDKP
        $gLocale[ "EQDKPBinding" ]             = "EQDKP";
        $gLocale[ "EQDKPConfigFile" ]          = "EQDKP Fichier de configuration";
        $gLocale[ "EQDKPDatabase" ]            = "EQDKP Base de données";
        $gLocale[ "EQDKPPasswordEmpty" ]       = "EQDKP Mot de passe de la base de données ne peut pas être vide.";
        $gLocale[ "EQDKPDBPasswordsMatch" ]    = "EQDKP Les mots de passe de la base de données ne correspondent pas.";
        
        // vBulletin
        $gLocale[ "VBulletinBinding" ]         = "vBulletin3";
        $gLocale[ "VBulletinConfigFile" ]      = "vBulletin Fichier de configuration";
        $gLocale[ "VBulletinDatabase" ]        = "vBulletin Base de données";
        $gLocale[ "VBulletinPasswordEmpty" ]   = "vBulletin Mot de passe de la base de données ne peut pas être vide.";
        $gLocale[ "VBulletinDBPasswordsMatch" ]= "vBulletin Les mots de passe de la base de données ne correspondent pas.";
        
        // MyBB
        $gLocale[ "MyBBBinding" ]              = "MyBB";
        $gLocale[ "MyBBConfigFile" ]           = "MyBB Fichier de configuration";
        $gLocale[ "MyBBDatabase" ]             = "MyBB Base de données";
        $gLocale[ "MyBBPasswordEmpty" ]        = "MyBB Mot de passe de la base de données ne peut pas être vide.";
        $gLocale[ "MyBBDBPasswordsMatch" ]     = "MyBB Les mots de passe de la base de données ne correspondent pas.";
        
        // SMF
        $gLocale[ "SMFBinding" ]               = "SMF";
        $gLocale[ "SMFConfigFile" ]            = "SMF Fichier de configuration";
        $gLocale[ "SMFDatabase" ]              = "SMF Base de données";
        $gLocale[ "SMFPasswordEmpty" ]         = "SMF Mot de passe de la base de données ne peut pas être vide.";
        $gLocale[ "SMFDBPasswordsMatch" ]      = "SMF Les mots de passe de la base de données ne correspondent pas.";
        
        // Vanilla
        $gLocale[ "VanillaBinding" ]           = "Vanilla";
        $gLocale[ "VanillaConfigFile" ]        = "Vanilla Fichier de configuration";
        $gLocale[ "VanillaDatabase" ]          = "Vanilla Base de données";
        $gLocale[ "VanillaPasswordEmpty" ]     = "Vanilla Mot de passe de la base de données ne peut pas être vide.";
        $gLocale[ "VanillaDBPasswordsMatch" ]  = "Vanilla Les mots de passe de la base de données ne correspondent pas.";
        
        // Joomla
        $gLocale[ "JoomlaBinding" ]            = "Joomla3";
        $gLocale[ "JoomlaConfigFile" ]         = "Joomla3 Fichier de configuration";
        $gLocale[ "JoomlaDatabase" ]           = "Joomla3 Base de données";
        $gLocale[ "JoomlaPasswordEmpty" ]      = "Joomla3 Mot de passe de la base de données ne peut pas être vide.";
        $gLocale[ "JoomlaDBPasswordsMatch" ]   = "Joomla3 Les mots de passe de la base de données ne correspondent pas.";
        
        // Drupal
        $gLocale[ "DrupalBinding" ]            = "Drupal";
        $gLocale[ "DrupalConfigFile" ]         = "Drupal Fichier de configuration";
        $gLocale[ "DrupalDatabase" ]           = "Drupal Base de données";
        $gLocale[ "DrupalPasswordEmpty" ]      = "Drupal Mot de passe de la base de données ne peut pas être vide.";
        $gLocale[ "DrupalDBPasswordsMatch" ]   = "Drupal Les mots de passe de la base de données ne correspondent pas.";
        
        // Wordpress
        $gLocale[ "WpBinding" ]                = "Wordpress";
        $gLocale[ "WpConfigFile" ]             = "Wordpress Fichier de configuration";
        $gLocale[ "WpDatabase" ]               = "Wordpress Base de données";
        $gLocale[ "WpPasswordEmpty" ]          = "Wordpress Mot de passe de la base de données ne peut pas être vide.";
        $gLocale[ "WpDBPasswordsMatch" ]       = "Wordpress Les mots de passe de la base de données ne correspondent pas.";
    }
?>