<?php
    header("Content-type: text/javascript");
    define( "LOCALE_SETUP", true );
    require_once(dirname(__FILE__)."/../../lib/private/locale.php");
?>

function onClassesResolved( a_XMLData )
{
    $(".classResolve:last").after("<div class=\"update_step_ok\">0 <?php echo L("ItemsToResolve"); ?></div>");
    $(".classResolve").detach();
}

function resolveClasses()
{
    var parameter = {
        ids : new Array(),
        classes : new Array(),
    };
    
    $(".change_class").each( function() {
        parameter.ids.push($(this).attr("id").substr(4));
        parameter.classes.push($(this).children("option:selected").val());
    });
    
    $.ajax({
        type     : "POST",
        url      : "query/submit_repair_classes.php",
        dataType : "xml",
        async    : true,
        data     : parameter,
        success  : onClassesResolved,
        error    : function(aXHR, aStatus, aError) { alert("<?php echo L("Error"); ?>:\n\n" + aError); }
    });
}