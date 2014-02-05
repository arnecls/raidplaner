<?php
    require_once(dirname(__FILE__)."/../../lib/private/connector.class.php");
    require_once(dirname(__FILE__)."/../../lib/config/config.php");
    require_once(dirname(__FILE__)."/tools_install.php");
    
    // -------------------------------------------------------------------------
       
    function ValidateTableLayout()
    {
        global $gDatabaseLayout;
        
        // Get all tables that should exist
        
        $TablesRemain = Array();
        
        reset($gDatabaseLayout);
        while(list($Name, $Rows) = each($gDatabaseLayout))
        {
            array_push($TablesRemain, RP_TABLE_PREFIX.$Name);
        }
        
        // Loop all tables
        
        $Connector = Connector::getInstance();        
        $Tables = $Connector->prepare("SHOW TABLES");
        $Tables->setErrorsAsHTML(true);
        
        $Tables->loop(function($aTable) use ($Connector, &$TablesRemain, &$gDatabaseLayout)
        {
            $FullName = $aTable["Tables_in_".RP_DATABASE];
            $Index = array_search($FullName, $TablesRemain);
            
            if ($Index !== false)
            {
                array_splice($TablesRemain, $Index, 1);
                
                $BaseName = substr($FullName, strlen(RP_TABLE_PREFIX));
                $TableLayout = $gDatabaseLayout[$BaseName];
                
                // Get all columns that should exist
                
                $ColumnsRemain = Array();
                foreach($TableLayout as $ColumnLayout)
                {
                    if (is_a($ColumnLayout, "Column"))
                        $ColumnsRemain[$ColumnLayout->Name] = $ColumnLayout; 
                }
                
                // Loop all columns
                
                $Columns = $Connector->prepare("SHOW COLUMNS FROM `".$FullName."`");
                
                $Columns->setErrorsAsHTML(true);
                $Columns->loop( function($aColumn) use ($Connector, $FullName, &$ColumnsRemain, &$TableLayout)
                {
                    $ColumnName = $aColumn["Field"];
                    
                    // Try to find current column
                    
                    $ColumnLayout = null;
                    foreach($TableLayout as $Layout)
                    {
                        if ($Layout->Name == $ColumnName)
                        {
                            $ColumnLayout = $Layout;
                            break;
                        }
                    }                    
                    
                    if ($ColumnLayout != null)
                    {
                        unset( $ColumnsRemain[$ColumnName] );
                        
                        if (!$ColumnLayout->HasType($aColumn["Type"]) ||
                            !$ColumnLayout->IsNull($aColumn["Null"] != "NO") ||
                            !$ColumnLayout->HasDefault($aColumn["Default"]) ||
                            !$ColumnLayout->HasExtra($aColumn["Extra"]))
                        {
                            // Modify column
                            
                            echo "<div class=\"update_step_warning\">".L("Fixing")." ".$FullName.".".$ColumnName."</div>";
                            
                            $Alter = $Connector->prepare($ColumnLayout->AlterText($FullName));
                            
                            $Alter->setErrorsAsHTML(true);                        
                            $Alter->execute();
                        }
                    }
                    else
                    {
                        // Drop column
                        
                        /*echo "<div class=\"update_step_warning\">".L("Removing")." ".$FullName.".".$ColumnName."</div>";
                        
                        $Drop = $Connector->prepare("ALTER TABLE `".$FullName."` DROP `".$aColumn["Field"]."`");
                        
                        $Drop->setErrorsAsHTML(true);                       
                        $Drop->execute();*/
                    }
                });
                
                // Add missing columns
                
                foreach($ColumnsRemain as $ColumnLayout)
                {
                    echo "<div class=\"update_step_warning\">".L("Fixing")." ".$FullName.".".$ColumnLayout->Name."</div>";
                    
                    $Add = $Connector->prepare($ColumnLayout->AddText($FullName));
                    
                    $Add->setErrorsAsHTML(true);
                    $Add->execute();
                }
            }
        });
                
        // Add missing tables
        
        foreach($TablesRemain as $TableName)
        {
            echo "<div class=\"update_step_warning\">".L("Fixing")." ".$TableName."</div>";
            
            $QueryString = "CREATE TABLE IF NOT EXISTS `".$TableName."` (";
            $FirstRow = true;
            
            $BaseName = substr($TableName, strlen(RP_TABLE_PREFIX));
            
            foreach($gDatabaseLayout[$BaseName] as $Row)
            {
                $QueryString .= (($FirstRow) ? "" : ",").$Row->CreateText();
                $FirstRow = false;
            }
            
            $QueryString .= ") ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
            $Create = $Connector->prepare($QueryString);
            
            $Create->setErrorsAsHTML(true);
            $Create->execute();
        }
    }
    
    // -------------------------------------------------------------------------
    
    function ValidateClassesAndRoles()
    {
        
    }
    
    // -------------------------------------------------------------------------
    
    function ClearStrayUserData()
    {
        
    }
    
    // -------------------------------------------------------------------------
    
    function MergeGames($aSourceFile, $aTargetFile)
    {
        
    }
?>