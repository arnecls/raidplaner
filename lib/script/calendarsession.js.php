<?php
    if (!defined("UNIFIED_SCRIPT"))
    {
        session_name("ppx_raidplaner");

        ini_set("session.cookie_httponly", true);
        ini_set("session.hash_function", 1);

        session_start();

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