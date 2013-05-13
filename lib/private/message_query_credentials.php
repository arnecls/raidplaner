<?php

function msgQueryCredentials( $aRequest )
{
    $Credentials = UserProxy::getInstance()->getUserCredentials($aRequest["Login"]);

    if ($Credentials == null )
    {
        echo "<error>".L("NoSuchUser")."</error>";
    }
    else
    {    
        echo "<salt>".$Credentials["salt"]."</salt>";
        echo "<pubkey>".$Credentials["key"]."</pubkey>";
        echo "<method>".$Credentials["method"]."</method>";
    }
}

// -----------------------------------------------------------------------------

function msgQueryCredentialsById( $aRequest )
{
    $Credentials = UserProxy::getInstance()->getUserCredentialsById($aRequest["UserId"]);

    if ($Credentials == null )
    {
        echo "<error>".L("NoSuchUser")."</error>";
    }
    else
    {    
        echo "<salt>".$Credentials["salt"]."</salt>";
        echo "<pubkey>".$Credentials["key"]."</pubkey>";
        echo "<method>".$Credentials["method"]."</method>";
    }
}

?>