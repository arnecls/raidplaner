<?php
    define( "LOCALE_MAIN", true );
    date_default_timezone_set('UTC');

    require_once(dirname(__FILE__)."/private/locale.php");
    require_once(dirname(__FILE__)."/private/userproxy.class.php");
    require_once(dirname(__FILE__)."/private/tools_string.php");
    require_once(dirname(__FILE__)."/private/tools_site.php");
    require_once(dirname(__FILE__)."/private/settings.class.php");
    require_once(dirname(__FILE__)."/private/out.class.php");

    include_once("private/message_query_calendar.php");
    include_once("private/message_raid_list.php");
    include_once("private/message_raid_attend.php");
    include_once("private/message_raid_create.php");
    include_once("private/message_raid_update.php");
    include_once("private/message_raid_delete.php");
    include_once("private/message_query_raid.php");
    include_once("private/message_query_locations.php");
    include_once("private/message_query_newraid.php");
    include_once("private/message_query_profile.php");
    include_once("private/message_query_settings.php");
    include_once("private/message_query_credentials.php");
    include_once("private/message_query_config.php");
    include_once("private/message_query_user.php");
    include_once("private/message_profile_update.php");
    include_once("private/message_comment_update.php");
    include_once("private/message_settings_update.php");
    include_once("private/message_user_create.php");
    include_once("private/message_user_link.php");
    
    // Init user first (if required)

    switch ( strtolower($_REQUEST["Action"]) )
    {
    case "try_auto_login":        
        UserProxy::getInstance(true);
        break;
        
    default:
        UserProxy::getInstance();
        break;
    }
    
    // Process message
        
    $Out = Out::getInstance();

    header("Content-type: application/json");
    header("Cache-Control: no-cache, max-age=0, s-maxage=0");

    if ( isset($_REQUEST["Action"]) )
    {
        switch ( strtolower($_REQUEST["Action"]) )
        {
        case "try_auto_login":
            break;
            
        case "query_locale":
            msgQueryLocale( $_REQUEST );
            break;

        case "query_user":
            msgQueryUser( $_REQUEST );
            break;

        case "query_config":
            msgQueryConfig( $_REQUEST );
            break;

        case "query_credentials":
            msgQueryCredentials( $_REQUEST );
            break;

        case "query_credentials_id":
            msgQueryLocalCredentialsById( $_REQUEST );
            break;
            
        case "login":
            msgLogin( $_REQUEST );
            break;
            
        case "logout":
            msgLogout( $_REQUEST );
            break;

        case "raid_attend":
            msgRaidAttend( $_REQUEST );
            break;

        case "raid_create":
            msgRaidCreate( $_REQUEST );
            break;

        case "query_calendar":
            $Settings = Settings::getInstance();
            lockOldRaids( $Settings->Property["LockRaids"]["IntValue"] );
            purgeOldRaids( $Settings->Property["PurgeRaids"]["IntValue"] );
            msgQueryCalendar( $_REQUEST );
            break;

        case "raid_list":
            $Settings = Settings::getInstance();
            lockOldRaids( $Settings->Property["LockRaids"]["IntValue"] );
            purgeOldRaids( $Settings->Property["PurgeRaids"]["IntValue"] );
            msgRaidList( $_REQUEST );
            break;

        case "raid_detail":
            msgRaidDetail( $_REQUEST );
            break;

        case "raid_update":
            msgRaidupdate( $_REQUEST );
            break;

        case "query_newraiddata":
            msgQueryNewRaidData( $_REQUEST );
            break;

        case "query_locations":
            msgQueryLocations( $_REQUEST );
            break;

        case "query_profile":
            msgQueryProfile( $_REQUEST );
            break;

        case "query_settings":
            msgQuerySettings( $_REQUEST );
            break;

        case "profile_update":
            msgProfileupdate( $_REQUEST );
            break;

        case "comment_update":
            msgCommentupdate( $_REQUEST );
            break;

        case "raid_delete":
            msgRaidDelete( $_REQUEST );
            break;

        case "settings_update":
            msgSettingsupdate( $_REQUEST );
            break;

        case "user_create":
            msgUserCreate( $_REQUEST );
            break;

        case "user_link":
            msgUserLink( $_REQUEST );
            break;

        default:
            echo "<error>".L("UnknownRequest")."</error>";
            break;
        }
    }
    else
    {
        $Out->pushError(L("InvalidRequest"));
    }

    $Out->flushJSON();
?>