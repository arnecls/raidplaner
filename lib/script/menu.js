function menuAutoLoad()
{
    var AutoLoad = window.location.hash.substring( 1, window.location.hash.length );
    var Tokens   = AutoLoad.split(",");

    if (gUser == null)
    {
        // No user logged in

        switch ( Tokens[0] )
        {

        default:
            changeContext( "login" );
            generateLogin();
            break;

        case "register":
            changeContext( AutoLoad );
            generateRegistration();
            break;

        case "login":
            changeContext( AutoLoad );
            generateLogin();
            break;
        }
    }
    else if (gUser.validUser)
    {
        // User logged in

        switch ( Tokens[0] )
        {
        default:
            if ((gUser.characterNames.length == 0) && (!gUser.isAdmin))
            {
                changeContext("profile");
                loadProfile("characters", 0);
            }
            else
            {
                changeContext("calendar");
                loadDefaultCalendar();
            }
            break;

        case "calendar":
            changeContext( AutoLoad );
            loadDefaultCalendar();
            break;

        case "profile":
            changeContext( AutoLoad );

            if ( Tokens.length <= 1 )
                loadProfile("characters", 0);
            else if ( Tokens.length == 2 )
                loadProfilePanel(Tokens[1], 0);
            else
                loadProfilePanel(Tokens[1], parseInt(Tokens[2], 10));

            break;

        case "raid":
            changeContext( AutoLoad );

            if ( Tokens.length <= 1 )
                loadAllRaids();
            else if ( Tokens.length == 2 )
                loadRaid( parseInt(Tokens[1], 10), "setup" );
            else
                loadRaidPanel( Tokens[1], parseInt(Tokens[2], 10) );

            break;

        case "settings":
            if ( gUser.isAdmin )
            {
                changeContext( AutoLoad );

                if ( Tokens.length <= 1 )
                    loadSettings("users");
                else
                    loadSettingsPanel( Tokens[1] );

            }
            break;
        }

    }
}

// -----------------------------------------------------------------------------

function doLogOut()
{
    var logout = function()
    {
        asyncQuery( "logout", null, function() {
            gUnappliedChanges = false;
            reloadUser();
            initMenu();
        });
    };

    if ( gUnappliedChanges )
    {
        confirm(L("UnappliedChanges"), L("DiscardChanges"), L("Cancel"), function() {
            logout();
        });
    }
    else
    {
        logout();
    }
}

// -----------------------------------------------------------------------------

function initMenu()
{
    $("#menu").empty();
    $("#body").empty();
    
    // Login screen if no user could be loaded
    
    if (gUser == null)
    {
        // Create menu
        
        var HTMLString = "<span id=\"button_login\" class=\"menu_button\"><div class=\"icon\"></div><div class=\"text\">" + L("Login") + "</div><div class=\"indicator\"></div></span>";
        
        if (gSite.AllowRegistration)
            HTMLString += "<span id=\"button_register\" class=\"menu_button\"><div class=\"icon\"></div><div class=\"text\">" + L("Register") + "</div><div class=\"indicator\"></div></span>";
        
        $("#menu").append(HTMLString);
        
        // Bind functions                    
    
        $("#button_login")
            .click( onChangeContext );

        $("#button_register")
            .click( onChangeContext );

        forceChangeContext("login");
        return; // ### return, login screen ###
    }
    
    // Blocked screen if user is blocked
    
    if (!gUser.validUser && gUser.registeredUser)
    {
        var  HTMLString = "<div id=\"logout\""+((gSite.Logout) ? "" : " style=\"display:none\"")+"><button class=\"button_logout\">"+L("Logout")+"</button></div>";
    
        $("#menu").append(HTMLString);
        
        $(".button_logout").button({
            icons: { secondary: "ui-icon-eject" }
        });
        
        $(".button_logout")
            .click( doLogOut );
    
        HTMLString = "<div id=\"lockMessage\">";
        HTMLString += L("AccountIsLocked")+"<br/>";
        HTMLString += L("ContactAdminToUnlock");
        HTMLString += "</div>";
        
        $("#body").empty().append(HTMLString);
        return; // ### return, blocked user ###
    }
    
    // Init main menu, etc. for valid users    
    // Create menu
    
    var HTMLString = "";
    
    HTMLString += "<span id=\"button_calendar\" class=\"menu_button\"><div class=\"icon\"></div><div class=\"text\">" + L("Calendar") + "</div><div class=\"indicator\"></div></span>";
    HTMLString += "<span id=\"button_raid\" class=\"menu_button\"><div class=\"icon\"></div><div class=\"text\">" + L("Raid") + "</div><div class=\"indicator\"></div></span>";
    HTMLString += "<span id=\"button_profile\" class=\"menu_button\"><div class=\"icon\"></div><div class=\"text\">" + L("Profile") + "</div><div class=\"indicator\"></div></span>";
    
    if (gUser.isAdmin)
        HTMLString += "<span id=\"button_settings_users\" class=\"menu_button\"><div class=\"icon\"></div><div class=\"text\">" + L("Settings") + "</div><div class=\"indicator\"></div></span>";
        
    HTMLString += "<div id=\"logout\""+((gSite.Logout) ? "" : " style=\"display:none\"")+"><button class=\"button_logout\">"+L("Logout")+"</button></div>";
    HTMLString += "<span id=\"help\""+((gSite.HelpLink != "") ? "" : " style=\"display:none\"")+"><button class=\"button_help\"></button></span>";
    
    $("#menu").append(HTMLString);
        
    $(".button_logout").button({
        icons: { secondary: "ui-icon-eject" }
    });   
        
    $(".button_help").button({
        icons: { secondary: "ui-icon-help" }
    });
    
    // Bind functions
        
    $("#button_calendar")
        .click( onChangeContext );

    $("#button_raid")
        .click( onChangeContext );

    $("#button_profile")
        .click( onChangeContext );

    $("#button_settings_users")
        .click( onChangeContext );
    
    $(".button_logout")
        .click( doLogOut );

    $(".button_help").click(function() { 
        openLink(gSite.HelpLink); 
    });
    
    // Load initial section
    
    if (window.location.hash.length > 0)
    {
        menuAutoLoad()
    }
    else if ( gUser.characterIds.length === 0 )
    {
        forceChangeContext("profile");
    }
    else
    {
        forceChangeContext("calendar");
    }
}