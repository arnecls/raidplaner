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

            changeContext( "calendar" );
            loadDefaultCalendar();
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

function onLogOut()
{
    if ( gUnappliedChanges )
    {
        confirm(L("UnappliedChanges"), L("DiscardChanges"), L("Cancel"), function() {
            $("#logout").submit();
        });

        return false;
    }

    $("#logout").submit();
    return false;
}

// -----------------------------------------------------------------------------

function initMenu()
{
    if (gUser == null)
    {
        $("#button_login")
            .click( onChangeContext );

        $("#button_register")
            .click( onChangeContext );

        forceChangeContext("login");
    }
    else
    {
        $(".button_logout").button({
            icons: { secondary: "ui-icon-eject" }
        }).css( "font-size", 11 );

        $(".button_help").button({
            icons: { secondary: "ui-icon-help" }
        }).css( "font-size", 11 );

        $("#button_calendar")
            .click( onChangeContext );

        $("#button_raid")
            .click( onChangeContext );

        $("#button_profile")
            .click( onChangeContext );

        $("#button_settings_users")
            .click( onChangeContext );

        $("#button_calendar").addClass("on");

        if ( gUser.characterIds.length === 0 )
            forceChangeContext("profile");
        else
            forceChangeContext("calendar");
    }
}