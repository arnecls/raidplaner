<?php
    if (!defined("UNIFIED_SCRIPT"))
    {
        require_once dirname(__FILE__)."../private/tools_site.php";
    
        beginSession();

        header("Content-type: text/javascript");
        header("Cache-Control: no-cache, max-age=0, s-maxage=0");
    }
?>

function loadDefaultCalendar()
{
    <?php if ( isset( $_SESSION["Calendar"] ) ) { ?>
    loadCalendar( <?php echo $_SESSION["Calendar"]["month"]-1 ?>, <?php echo $_SESSION["Calendar"]["year"] ?>, 0 );
    <?php } else { ?>
    loadCalendarForToday();
    <?php } ?> 
}