function menuAutoLoad()
{
    if ( checkUser() )
    {
        var autoLoad = window.location.hash.substring( 1, window.location.hash.length );
        var tokens   = autoLoad.split(",");
        
        switch ( tokens[0] )
        {
        default:
        case "calendar":
            changeContext( autoLoad );
            loadDefaultCalendar();
            break;

        case "raid":
            changeContext( autoLoad );

            if ( tokens.length == 1 )
            {
                loadAllRaids();
            }
            else
            {
                var subContext = "";

                if ( tokens.length == 3 )
                    loadRaidPanel( tokens[1], parseInt(tokens[2]) );
                else
                    loadRaid( parseInt(tokens[1]), "setup" );
            }
            break;

        case "profile":
            changeContext( autoLoad );
            loadProfile();
            break;

        case "settings":
            if ( g_User.isAdmin )
            {
                changeContext( autoLoad );

                if ( tokens.length == 1 )
                {
                    loadSettings("users");
                }
                else
                {
                    var subContext = parseInt(tokens[1]);

                    if ( subContext > 0 )
                        loadForeignProfile( subContext );
                    else
                        loadSettingsPanel( tokens[1] );

                }
            }
            break;
        }
    }
}

// -----------------------------------------------------------------------------

function initMenu()
{
    $("#button_calendar")
        .click( onChangeContext );

    $("#button_raid")
        .click( onChangeContext );

    $("#button_profile")
        .click( onChangeContext );

    $("#button_settings_users")
        .click( onChangeContext );

    $("#button_calendar").addClass("on");

    $(window).bind('hashchange', menuAutoLoad );

    if ( g_User != null )
    {
        if ( g_User.characterIds.length == 0 )
        {
            if ( !changeContext( "profile" ) )
                menuAutoLoad();
        }
        else
        {
            if ( !changeContext( "calendar" ) )
                menuAutoLoad();
        }
    }
}


// -----------------------------------------------------------------------------

function initLogin()
{
}