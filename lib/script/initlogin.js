function loginAutoLoad()
{
    var autoLoad = window.location.hash.substring( 1, window.location.hash.length );

    switch ( autoLoad )
    {
    case "register":
        changeContext( autoLoad );
        generateRegistration();
        break;

    case "login":
    default:
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