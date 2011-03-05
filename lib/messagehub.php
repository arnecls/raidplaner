<?php
    require_once(dirname(__FILE__)."/private/locale.php");
    require_once(dirname(__FILE__)."/private/users.php");
    require_once(dirname(__FILE__)."/private/tools_string.php");
    require_once(dirname(__FILE__)."/private/settings.class.php");
    
    include_once("private/raid_maintenance.php");
    include_once("private/message_raid_detail.php");
    include_once("private/message_raid_calendar.php");
    include_once("private/message_raid_list.php");
    include_once("private/message_raid_attend.php");
    include_once("private/message_raid_create.php");
    include_once("private/message_raid_update.php");
    include_once("private/message_raid_delete.php");
    include_once("private/message_query_locations.php");
    include_once("private/message_query_profile.php");
    include_once("private/message_query_settings.php");
    include_once("private/message_profile_update.php");
    include_once("private/message_comment_update.php");
    include_once("private/message_settings_update.php");
    
	$ValidUser = ValidUser();	
	
	header("Content-type: text/xml");
    
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
	echo "<messagehub>";
	
	$Settings = Settings::GetInstance();
	
	if ( isset($_REQUEST["Action"]) )
    {   
    	switch ( strtolower($_REQUEST["Action"]) )
        {
        case "raid_attend":
        	msgRaidAttend( $_REQUEST );
            break;
        
        case "raid_create":
            msgRaidCreate( $_REQUEST );
            break;
            
        case "raid_calendar":
        	lockOldRaids( $Settings->Property["LockRaids"]["IntValue"] );
        	purgeOldRaids( $Settings->Property["PurgeRaids"]["IntValue"] );
            msgRaidCalendar( $_REQUEST );
            break;
          
        case "raid_list":
            lockOldRaids( $Settings->Property["LockRaids"]["IntValue"] );
        	purgeOldRaids( $Settings->Property["PurgeRaids"]["IntValue"] );
            msgRaidList( $_REQUEST );
            break;
            
        case "raid_detail":
        	msgRaidDetail( $_REQUEST );
            break;
            
        case "raid_update":
        	msgRaidUpdate( $_REQUEST );
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
        	msgProfileUpdate( $_REQUEST );
        	break;
        	
        case "comment_update":
        	msgCommentUpdate( $_REQUEST );
        	break;
        	
        case "raid_delete":
        	msgRaidDelete( $_REQUEST );
        	break;
        	
        case "settings_update":
        	msgSettingsUpdate( $_REQUEST );
        	break;
        	
        case "user_create":
        	msgUserCreate( $_REQUEST );
            break;
            
        default:
            echo "<error>".L("Unknown request")."</error>";
            break;
        }
    }
    else
    {
        echo "<error>".L("Invalid request")."</error>";
    }
    
    echo "</messagehub>";
?>