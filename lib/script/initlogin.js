function loginAutoLoad()
{
    var AutoLoad = window.location.hash.substring( 1, window.location.hash.length );

    switch ( AutoLoad )
    {
    case "register":
        changeContext( AutoLoad );
        generateRegistration();
        break;

    default:
    case "login":
        changeContext( "login" );
        generateLogin();
    }
}

// -----------------------------------------------------------------------------

function initLogin()
{
    $("#button_login")
        .click( onChangeContext );

    if ( $("#button_register").size() > 0 )
    {
        $("#button_register")
            .click( onChangeContext );
    }

    $(window).bind('hashchange', loginAutoLoad );
    loginAutoLoad();
}

// -----------------------------------------------------------------------------

function initMenu()
{
}