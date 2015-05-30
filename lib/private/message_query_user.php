<?php

    function msgQueryUser($aRequest)
    {
        $Out = Out::getInstance();

        if (registeredUser())
        {
            $CurrentUser = UserProxy::getInstance();

            $CharacterIds = array();
            $CharacterGames = array();
            $CharacterNames = array();
            $CharacterClasses = array();
            $CharacterRoles1 = array();
            $CharacterRoles2 = array();
            $Settings = array();

            foreach( $CurrentUser->Characters as $Character )
            {
                array_push($CharacterIds, $Character->CharacterId);
                array_push($CharacterGames, $Character->Game);
                array_push($CharacterNames, $Character->Name);
                array_push($CharacterClasses, explode(':',$Character->ClassName));
                array_push($CharacterRoles1, $Character->Role1);
                array_push($CharacterRoles2, $Character->Role2);
            }

            $Out->pushValue('registeredUser', true);
            $Out->pushValue('id', $CurrentUser->UserId);
            $Out->pushValue('name', $CurrentUser->UserName);
            $Out->pushValue('characterIds', $CharacterIds);
            $Out->pushValue('characterGames', $CharacterGames);
            $Out->pushValue('characterNames', $CharacterNames);
            $Out->pushValue('characterClass', $CharacterClasses);
            $Out->pushValue('role1', $CharacterRoles1);
            $Out->pushValue('role2', $CharacterRoles2);

            $Out->pushValue('validUser', validUser());
            $Out->pushValue('isPrivileged', validPrivileged());
            $Out->pushValue('isRaidlead', validRaidlead());
            $Out->pushValue('isAdmin', validAdmin());
            $Out->pushValue('settings', $CurrentUser->Settings);

            $Session = Session::get();

            if (isset($Session['Calendar']))
            {
                $Out->pushValue('calendar', $Session['Calendar']);
            }
            else
            {
                $Out->pushValue('calendar', null);
            }
        }
        else
        {
            $Out->pushValue('registeredUser', false);
        }
    }

?>