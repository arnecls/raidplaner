<?php

    function dumpImages( $Folder )
    {
        global $gSite;
        $FolderHandle = opendir( realpath( dirname(__FILE__)."/../../".$Folder ) );

        while ( ($FileName = readdir( $FolderHandle )) !== false )
        {
            $FilePath = realpath( dirname(__FILE__)."/../../".$Folder."/".$FileName );

            if ( !is_dir( $FilePath ) )
            {
                $Extension = substr( $FileName, strrpos( $FileName, "." ) + 1 );

                switch ( strtolower($Extension) )
                {
                case "jpg":
                case "jpeg":
                case "png":
                case "gif":
                    echo "<img src=\"".$Folder."/".$FileName."\"/>";
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