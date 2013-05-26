function menuAutoLoad()
{
    if ( checkUser() )
    {
        var AutoLoad = window.location.hash.substring( 1, window.location.hash.length );
        var Tokens   = AutoLoad.split(",");
        
        switch ( Tokens[0] )
        {
        default:
        case "calendar":
            changeContext( AutoLoad );
            loadDefaultCalendar();
            break;

        case "raid":
            changeContext( AutoLoad );

            if ( Tokens.length == 1 )
            {
                loadAllRaids();
            }
            else
            {
                var SubContext = "";

                if ( Tokens.length == 3 )
                    loadRaidPanel( Tokens[1], parseInt(Tokens[2], 10) );
                else
                    loadRaid( parseInt(Tokens[1], 10), "setup" );
            }
            break;

        case "profile":
            changeContext( AutoLoad );
            loadProfile();
            break;

        case "settings":
            if ( gUser.isAdmin )
            {
                changeContext( AutoLoad );

                if ( Tokens.length == 1 )
                {
                    loadSettings("users");
                }
                else
                {
                    var SubContext = parseInt(Tokens[1], 10);

                    if ( SubContext > 0 )
                        loadForeignProfile( SubContext );
                    else
                        loadSettingsPanel( Tokens[1] );

                }
            }
            break;
        }
    }
}

// -----------------------------------------------------------------------------

function onLogOut( aCallback )
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

    if ( gUser != null )
    {
        if ( gUser.characterIds.length === 0 )
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