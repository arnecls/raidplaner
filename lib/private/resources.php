<?php

    function dumpImages( $Folder )
    {
        global $siteVersion;
        $folderHandle = opendir( realpath( dirname(__FILE__)."/../../".$Folder ) );

        while ( ($fileName = readdir( $folderHandle )) !== false )
        {
            $filePath = realpath( dirname(__FILE__)."/../../".$Folder."/".$fileName );

            if ( !is_dir( $filePath ) )
            {
                $extension = substr( $fileName, strrpos( $fileName, "." ) + 1 );

                switch ( strtolower($extension) )
                {
                case "jpg":
                case "jpeg":
                case "png":
                case "gif":
                    echo "<img src=\"".$Folder."/".$fileName."\"/>";
                    break;

                default:
                    break;
                }
            }
        }
    }

    dumpImages( "lib/layout/images" );
    dumpImages( "images/raidsmall" );
    dumpImages( "images/raidbig" );
    dumpImages( "images/classessmall" );
    dumpImages( "images/classesbig" );
    dumpImages( "images/roles" );
?>