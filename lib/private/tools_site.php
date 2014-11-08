<?php
    require_once(dirname(__FILE__).'/connector.class.php');
    require_once(dirname(__FILE__).'/settings.class.php');
    require_once(dirname(__FILE__).'/session.class.php');
    require_once(dirname(__FILE__).'/random.class.php');
    
    $gVersion = 113.4;

    $gSite = null;
    $gGame = null;

    // ---------------------------------------------------------------

    function include_once_exists($aFile)
    {
        if (file_exists($aFile))
            include_once($aFile);
    }
    
    // ---------------------------------------------------------------
    
    function getParam($aName, $aDefault)
    {
        return getParamFrom($_REQUEST, $aName, $aDefault);
    }
    
    // ---------------------------------------------------------------
    
    function getParamFrom($aParameters, $aName, $aDefault)
    {
        $Value = (($aParameters != null) && isset($aParameters[$aName])) 
            ? $aParameters[$aName] 
            : $aDefault;
        
        switch(strtolower($Value))
        {
        case 'true':
            return true;
            
        case 'false':
            return false;
            
        default:
            return (is_numeric($Value))
                ? intval($Value)
                : $Value;
        }
    }

    // ---------------------------------------------------------------
    
    function getBaseURL()
    {
        $Protocol = (strpos($_SERVER['SERVER_PROTOCOL'], 'https') !== false) ? 'https://' : 'http://';
        $Host = $_SERVER['SERVER_NAME'].(($_SERVER['SERVER_PORT'] == 80) ? '' : ':'.$_SERVER['SERVER_PORT']);
        
        $LibIdx = strpos($_SERVER['REQUEST_URI'], 'lib/');
        $Path   = ($LibIdx !== false) 
            ? substr($_SERVER['REQUEST_URI'], 0, $LibIdx)
            : substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'],'/')+1);
        
        return $Protocol.$Host.$Path;      
    }

    // ---------------------------------------------------------------
    
    function loadSiteSettings()
    {
        global $gSite;
        global $gVersion;
        
        if ($gSite != null)
            return; // ### return, already initialized ###

        $Out = Out::getInstance();
        $Connector = Connector::getInstance();
        $Settings = Settings::getInstance();
        
        $gSite['Version']     = $gVersion;
        $gSite['Theme']       = '';
        $gSite['BannerLink']  = '';
        $gSite['HelpLink']    = '';
        $gSite['Logout']      = true;
        $gSite['PrimaryRole'] = true;
        $gSite['Banner']      = 'cataclysm.jpg';
        $gSite['Background']  = 'flower.png';
        $gSite['BGColor']     = '#898989';
        $gSite['BGRepeat']    = 'repeat-xy';
        $gSite['Iconset']     = 'wow';
        $gSite['Styles']      = array();
        $gSite['PortalMode']  = false;
        $gSite['TimeFormat']  = 24;
        $gSite['StartOfWeek'] = 1;
        $gSite['GameConfig']  = 'wow';

        foreach($Settings->getProperties() as $Name => $Data)
        {
            switch( $Name )
            {
            case 'Site':
                $gSite['BannerLink'] = $Data['TextValue'];
                break;
                
            case 'GameConfig':
                $gSite['GameConfig'] = $Data['TextValue'];
                break;

            case 'HelpPage':
                $gSite['HelpLink'] = $Data['TextValue'];
                break;

            case 'Theme':
                $gSite['Theme'] = $Data['TextValue'];
                $ThemeFile = realpath(dirname(__FILE__).'/../../themes/themes/'.$Data['TextValue'].'.xml');
                
                if ( file_exists($ThemeFile) )
                {
                    try
                    {
                        $Theme = @new SimpleXMLElement( file_get_contents($ThemeFile) );
                        
                        $gSite['Banner']     = (isset($Theme->banner))   ? (string)$Theme->banner   : $gSite['Banner'];
                        $gSite['Background'] = (isset($Theme->bgimage))  ? (string)$Theme->bgimage  : $gSite['Background'];
                        $gSite['BGColor']    = (isset($Theme->bgcolor))  ? (string)$Theme->bgcolor  : $gSite['BGColor'];
                        $gSite['BGRepeat']   = (isset($Theme->bgrepeat)) ? (string)$Theme->bgrepeat : $gSite['BGRepeat'];
                        
                        $gSite['Iconset']    = (isset($Theme->iconset))    ? (string)$Theme->iconset                : $gSite['Iconset'];
                        $gSite['PortalMode'] = (isset($Theme->portalmode)) ? ((string)$Theme->portalmode) == 'true' : $gSite['PortalMode'];
                        $gSite['Logout']     = (isset($Theme->logout))     ? ((string)$Theme->logout) != 'false'    : $gSite['Logout'];
                        
                        if (isset($Theme->style))
                        {
                            foreach($Theme->style as $Style)
                            {
                                array_push($gSite['Styles'], strval($Style));
                            }
                        }
                        
                        if (isset($Theme->random))
                        {
                            $Index = rand(0, count($Theme->random));
                            $Overwrite = $Theme->random[$Index];
                        
                            $gSite['Banner']     = (isset($Overwrite->banner))   ? (string)$Overwrite->banner   : $gSite['Banner'];
                            $gSite['Background'] = (isset($Overwrite->bgimage))  ? (string)$Overwrite->bgimage  : $gSite['Background'];
                            $gSite['BGColor']    = (isset($Overwrite->bgcolor))  ? (string)$Overwrite->bgcolor  : $gSite['BGColor'];
                            $gSite['BGRepeat']   = (isset($Overwrite->bgrepeat)) ? (string)$Overwrite->bgrepeat : $gSite['BGRepeat'];
                        }
                    }
                    catch(Exception $e)
                    {
                        $Out->pushError('Error parsing themefile '.$ThemeFile.': '.$e->getMessage());
                    }
                }
                break;

            case 'TimeFormat':
                $gSite['TimeFormat'] = $Data['IntValue'];
                break;

            case 'StartOfWeek':
                $gSite['StartOfWeek'] = $Data['IntValue'];
                break;
                
            case 'PrimaryRole':
                $gSite['PrimaryRole'] = $Data['TextValue'] == 'true';
                break;

            default:
                break;
            };
        }
    }

    // ---------------------------------------------------------------

    function loadGame($aConfigFileName)
    {
        $Game = Array(
            'GameId'        => 'none',
            'Family'        => 'wow',
            'ClassMode'     => 'single',
            'Roles'         => Array(),
            'Classes'       => Array(),
            'RaidView'      => Array(),
            'RaidViewOrder' => Array(),
            'Groups'        => Array(),
            'Locales'       => Array()
        );
        
        $ConfigFile = realpath(dirname(__FILE__).'/../../themes/games/'.$aConfigFileName.'.xml');
        $Out = Out::getInstance();
        
        if ( !file_exists($ConfigFile) )
        {
            $Out->pushError('Gameconfig file '.$ConfigFile.' not found.');
        }
        else
        {
            try
            {
                $Config = @new SimpleXMLElement( file_get_contents($ConfigFile) );
                
                // General
                
                $Game['GameId'] = strtolower($Config->id);
                $Game['Family'] = strtolower($Config->family);
                $Game['ClassMode'] = strtolower($Config->classmode);
                
                if (strlen($Game['GameId']) > 4)
                    throw new Exception('Game ids must be at least 1 and can be at most 4 characters long. '.$Game['GameId'].' does not match this rule.');
                
                if (($Game['ClassMode'] != 'single') && 
                    ($Game['ClassMode'] != 'multi'))
                {
                    throw new Exception('Classmode must either be single or multi.');
                }
                
                // Locales
                
                foreach($Config->locale as $Locale)
                {
                    if (strlen(strval($Locale['name'])) != 2)
                        throw new Exception('Locale names must be exactly 2 characters long. '.strval($Locale['name']).' does not match this rule.');
                    
                    $CurrentLocale = Array();
                    foreach($Locale->text as $Text)
                    {
                        $CurrentLocale[strval($Text['key'])] = strval($Text);
                    }
                    
                    $Game['Locales'][strval($Locale['name'])] = $CurrentLocale;
                }
                
                // Roles
                
                foreach($Config->roles->role as $Role)
                {
                    if (strlen(strval($Role['id'])) != 3)
                        throw new Exception('Role ids must be exactly 3 characters long. '.strval($Role['id']).' does not match this rule.');
                        
                    $Game['Roles'][strval($Role['id'])] = Array(
                        'id'    => strval($Role['id']),
                        'name'  => strval($Role['loca']),
                        'style' => strval($Role['style'])
                    );
                }
                
                // Classes
                
                foreach($Config->classes->class as $Class)
                {
                    if (strlen(strval($Class['id'])) != 3)
                        throw new Exception('Class ids must be exactly 3 characters long. '.strval($Class['id']).' does not match this rule.');
                    
                    $ClassData = Array(
                        'id'          => strval($Class['id']),
                        'name'        => strval($Class['loca']),
                        'style'       => strval($Class['style']),
                        'roles'       => Array(),
                        'defaultRole' => strval($Class->role[0]['id'])
                    );
                    
                    foreach($Class->role as $Role)
                    {
                        if (!isset($Game['Roles'][strval($Role['id'])]))
                            throw new Exception('Unknown role '.$Role['id'].' used in class '.$Class['id'].'.');
                        
                        array_push($ClassData['roles'], strval($Role['id']));
                        if ($Role['default'] == 'true')
                            $ClassData['defaultRole'] = strval($Role['id']);
                    }
                    
                    $Game['Classes'][strval($Class['id'])] = $ClassData;
                }
                
                // Raidview
                
                $ColsUsed = 0;
                $MaxNumCols = 6;
                $AutoCount = 0;
                $Order = Array();
                
                foreach($Config->raidview->slots as $Slot)
                {
                    if ($Slot['columns'] == '*')
                    {
                        $Columns = 0;
                        ++$AutoCount;
                    }
                    else
                    {
                        $Columns = intval($Slot['columns']);    
                    }
                                        
                    $Game['RaidView'][strval($Slot['role'])] = $Columns;
                    
                    if (isset($Slot['order']))
                        array_push($Order, intval($Slot['order']).':'.strval($Slot['role']));
                    else
                        array_push($Order, count($Game['RaidViewOrder']).':'.strval($Slot['role']));
                    
                    $ColsUsed += $Columns;
                }
                
                // Adjust auto columns
                
                if ($AutoCount > 0)
                {
                    $AutoColValue = ($MaxNumCols - $ColsUsed) / $AutoCount;
                
                    foreach($Game['RaidView'] as $Role => $Count)
                    {
                        if ($Count == 0)
                        {
                            $Game['RaidView'][$Role] = ($AutoCount == 1)
                                ? $MaxNumCols - $ColsUsed
                                : $AutoColValue;
                            
                            --$AutoCount;
                            $ColsUsed += $Game['RaidView'][$Role];
                        }
                    }
                }
                
                // Sort by order attribute
                
                sort($Order);
                
                foreach($Order as &$Role)
                {
                    array_push($Game['RaidViewOrder'], substr($Role, strpos($Role, ':')+1));
                }
                
                if ($ColsUsed != $MaxNumCols)
                    throw new Exception('The raidview must contain exactly '.$MaxNumCols.' columns. '.$ColsUsed.' columns have been configured.');
                    
                // Groups
                
                foreach($Config->groups->group as $Group)
                {
                    $GroupData = Array();
                    $GroupSize = intval($Group['count']);
                    $SlotsUsed = 0;
                    $AutoCount = 0;
                    
                    foreach($Group->role as $Role)
                    {
                        if (!isset($Game['Roles'][strval($Role['id'])]))
                            throw new Exception('Unknown role '.$Role['id'].' used in group ('.$GroupSize.').');
                        
                        if ($Role['count'] == '*')
                        {
                            $Count = 0;
                            ++$AutoCount;
                        }
                        else
                        {
                            $Count = intval($Role['count']);    
                        }
                            
                        $GroupData[strval($Role['id'])] = $Count;                        
                        $SlotsUsed += $Count;
                    }
                    
                    // Adjust auto columns
                
                    if ($AutoCount > 0)
                    {
                        $AutoCountValue = ($GroupSize - $SlotsUsed) / $AutoCount;
                    
                        foreach($GroupData as $Role => $Count)
                        {
                            if ($Count == 0)
                            {
                                $GroupData[$Role] = ($AutoCount == 1)
                                    ? $GroupSize - $SlotsUsed
                                    : $AutoColValue;
                                
                                --$AutoCount;
                                $SlotsUsed += $GroupData[$Role];
                            }
                        }
                    }
                    
                    if ($SlotsUsed != $GroupSize)
                        throw new Exception('Group size '.$GroupSize.' contains '.$SlotsUsed.' slots.');
                    
                    $Game['Groups'][$GroupSize] = $GroupData;
                }
            }
            catch(Exception $e)
            {
                $Out->pushError('Error parsing gameconfig file '.$ConfigFile.":\n\n".$e->getMessage());
            }
        }
        
        return $Game;
    }
    
    // ---------------------------------------------------------------

    function loadGameSettings()
    {
        global $gSite;
        global $gGame;
        
        if ($gGame != null)
            return; // ### return, already initialized ###
            
        loadSiteSettings();
        $Out = Out::getInstance();
        
        $gGame = loadGame($gSite['GameConfig']);
    }

    // ---------------------------------------------------------------

    function checkVersion($aSiteVersion)
    {
        try
        {
            $Connector = Connector::getInstance(true);
            $VersionQuery = $Connector->prepare('SELECT IntValue FROM `'.RP_TABLE_PREFIX.'Setting` WHERE Name = "Version" LIMIT 1' );
            $Result = $VersionQuery->fetchFirst();

            if ($Result != null)
                return intval(intval($aSiteVersion) / 10) == intval(intval($Result['IntValue']) / 10);
        }
        catch(PDOException $Exception)
        {
        }

        return false;

    }

    // ---------------------------------------------------------------

    function lockOldRaids( $aSeconds )
    {
        if ( validUser() )
        {
            $Connector = Connector::getInstance();
            $UpdateRaidQuery = $Connector->prepare('UPDATE `'.RP_TABLE_PREFIX.'Raid` SET '.
                                                   'Stage = "locked"'.
                                                   'WHERE Start < FROM_UNIXTIME(:Time) AND Stage = "open"' );

            $UpdateRaidQuery->bindValue(':Time', time() + $aSeconds, PDO::PARAM_INT);
            $UpdateRaidQuery->execute();
        }
    }

    // ---------------------------------------------------------------

    function purgeOldRaids( $aSeconds )
    {
        $Connector = Connector::getInstance();
        $DropRaidQuery = $Connector->prepare( 'DELETE `'.RP_TABLE_PREFIX.'Raid`, `'.RP_TABLE_PREFIX.'Attendance` '.
                                           'FROM `'.RP_TABLE_PREFIX.'Raid` LEFT JOIN `'.RP_TABLE_PREFIX.'Attendance` USING ( RaidId ) '.
                                           'WHERE '.RP_TABLE_PREFIX.'Raid.End < FROM_UNIXTIME(:Time)' );

        $Timestamp = time() - $aSeconds;
        $DropRaidQuery->bindValue( ':Time', $Timestamp, PDO::PARAM_INT );
        $DropRaidQuery->execute();
    }
?>