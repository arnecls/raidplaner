<?php

    function dumpImages( $Folder )
    {
        global $gSite;
        $FolderHandle = opendir( realpath(dirname(__FILE__)."/../../".$Folder) );

        while ( ($FolderHandle !== false) && 
                (($FileName = readdir($FolderHandle)) !== false) )
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
    dumpImages( "themes/icons/".$gSite["Iconset"]."/raidsmall" );
    dumpImages( "themes/icons/".$gSite["Iconset"]."/raidbig" );
    dumpImages( "themes/icons/".$gSite["Iconset"]."/classessmall" );
    dumpImages( "themes/icons/".$gSite["Iconset"]."/classesbig" );
    dumpImages( "themes/icons/".$gSite["Iconset"]."/roles" );
?>