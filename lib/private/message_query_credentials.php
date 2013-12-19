<?php

function msgQueryCredentials( $aRequest )
{
    $Credentials = UserProxy::getInstance()->getUserCredentials($aRequest["Login"]);
    $Out = Out::getInstance();
        
    if ($Credentials == null )
    {
        $Out->pushError(L("NoSuchUser"));
    }
    else
    {
        $Out->pushValue("salt", $Credentials["salt"]);
        $Out->pushValue("pubkey", $Credentials["key"]);
        $Out->pushValue("method", $Credentials["method"]);
    }
}

// -----------------------------------------------------------------------------

function msgQueryLocalCredentialsById( $aRequest )
{
    $Credentials = UserProxy::getInstance()->getUserCredentialsById($aRequest["UserId"], "none");
    $Out = Out::getInstance();
        
    if ($Credentials == null )
    {
        $Out->pushError(L("NoSuchUser"));
    }
    else
    {
        $Out->pushValue("id", $aRequest["UserId"]);
        $Out->pushValue("salt", $Credentials["salt"]);
        $Out->pushValue("pubkey", $Credentials["key"]);
        $Out->pushValue("method", $Credentials["method"]);
    }
}

?>