jQuery(document).ready( function() {
    $(".button")
        .mousedown( function() { $(this).removeClass("up").addClass("down"); })
        .mouseup( function() { $(this).removeClass("down").addClass("up"); })
        .mouseout( function() { $(this).removeClass("down").addClass("up"); });
        
    $("#install").click( function() { document.location.href = "setup_check.php"; });
    $("#upgrade").click( function() { document.location.href = "upgrade_check.php"; });
    $("#binding").click( function() { document.location.href = "setup_bindings.php"; });
});