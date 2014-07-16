<?php
    define( 'LOCALE_SETUP', true );
    require_once(dirname(__FILE__).'/locale.php');
    require_once(dirname(__FILE__).'/out.class.php');

    $Out = Out::getInstance();
    header('Content-type: application/json');
    
    function DelTree( $aFolder) 
    { 
        $Files = array_diff(scandir($aFolder), array('.','..'));
        
        foreach ($Files as $File) 
        {
            $FullPath = $aFolder.DIRECTORY_SEPARATOR.$File;
            $Success = (is_dir($FullPath))
                ? DelTree($FullPath)
                : @unlink($FullPath);
                
            if (!$Success)
                return false;
        }
        
        return @rmdir($aFolder); 
    }     
    
    if (!DelTree(realpath(dirname(__FILE__).'/../../setup')))
    {
        $Out->pushError(L('FailedRemoveSetup'));
        $Out->pushError(error_get_last()['message']);
    }
    
    $Out->flushJSON();
?>