<?php
    define( "LOCALE_SETUP", true );
    require_once(dirname(__FILE__)."/../lib/private/locale.php");
?>
<?php readfile("layout/header.html"); ?>

<script type="text/javascript">
    $(document).ready( function() {
        $(".button_back").click( function() { open("index.php"); });
        $(".button_next").click( function() { 
            var QueryString = "";
                        
            if ($("#repair_db").prop("checked"))          QueryString += ((QueryString == "") ? "?" : "&") + "db";
            if ($("#repair_chars").prop("checked"))       QueryString += ((QueryString == "") ? "?" : "&") + "char";
            if ($("#convert_gameconfig").prop("checked")) QueryString += ((QueryString == "") ? "?" : "&") + "conf";
            if ($("#merge_games").prop("checked")) {
                QueryString += ((QueryString == "") ? "?" : "&") + "merge";
                QueryString += "&source=" + $("#from_game").val();
                QueryString += "&target=" + $("#to_game").val();
            }
            
            open("repair_done.php" + QueryString); 
        });
    });
</script>

<h2><?php echo L("Repair"); ?></h2>
<?php echo L("ChooseRepairs"); ?>
<br/><br/>

<div style="margin-bottom: 5px"><input type="checkbox" id="repair_db" style="margin-right: 10px"/><?php echo L("RepairDatabase") ?></div>
<div style="margin-bottom: 5px"><input type="checkbox" id="repair_chars" style="margin-right: 10px"/><?php echo L("RepairCharacters") ?></div>
<div style="margin-bottom: 5px"><input type="checkbox" id="convert_gameconfig" style="margin-right: 10px"/><?php echo L("TransferGameconfig") ?></div>
<div style="margin-bottom: 5px"><input type="checkbox" id="merge_games" style="margin-right: 10px"/><?php echo L("MergeGames") ?></div>
<?php
    $GameFiles = scandir( "../themes/games" );
    $Games = Array();
    
    foreach ( $GameFiles as $GameFileName )
    {
        try
        {
            if (strpos($GameFileName,".xml") > 0)
            {
                $Game = @new SimpleXMLElement( file_get_contents("../themes/games/".$GameFileName) );
                $SimpleGameFileName = substr($GameFileName, 0, strrpos($GameFileName, "."));
                
                if ($Game->name != "")
                    $GameName = strval($Game->name);
                else
                    $GameName = str_replace("_", " ", $SimpleGameFileName);
                
                array_push($Games, Array(
                    "name" => $GameName,
                    "file" => $SimpleGameFileName,
                ));
            }
        }
        catch (Exception $e)
        {
            $Out->pushError("Error parsing gameconfig ".$GameFileName.": ".$e->getMessage());
        }
    }
?>
<div style="padding-left: 25px; margin-bottom: 5px">
    <select id="from_game" style="margin-right: 10px">
    <?php
        foreach ($Games as $Game)
            echo "<option value=\"".$Game["file"]."\">".$Game["name"]."</option>";
    ?>
    </select><?php echo L("SourceGame") ?>
    <br/>
    <select id="to_game" style="margin-right: 10px">
    <?php
        foreach ($Games as $Game)
            echo "<option value=\"".$Game["file"]."\">".$Game["name"]."</option>";
    ?>
    </select><?php echo L("TargetGame") ?>
</div>


</div>
<div class="bottom_navigation">
    <div class="button_back" style="background-image: url(layout/install_white.png)"><?php echo L("Back"); ?></div>
    <div class="button_next" style="background-image: url(layout/repair_white.png)"><?php echo L("Continue"); ?></div>

<?php readfile("layout/footer.html"); ?>
