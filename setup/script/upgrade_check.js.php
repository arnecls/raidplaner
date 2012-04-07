<?php
	header("Content-type: text/javascript");
    define( "LOCALE_SETUP", true );
	require_once(dirname(__FILE__)."/../../lib/private/locale.php");
?>

function hideError()
{
    $("#error").hide();
}

function showLog( a_XMLData )
{
    var Message = $(a_XMLData).children("upgrade");
    var numErrors = 0;
    
    $("#error").empty().append(
        "<h2><?php echo L("Update errors"); ?></h2>" +
        "<?php echo L("The following errors were reported during update."); ?><br/>" +
        "<?php echo L("This may hint on an already (partially) updated database."); ?>" +
        "<br/><br/>" );
    
    $("#error").append( "<button onclick=\"loadCleanup()\"><?php echo L("Ignore"); ?></button>" );
    $("#error").append( "<button onclick=\"hideError()\"><?php echo L("Retry"); ?></button><br/><br/>" );
    
    Message.children("update").each(function() {
        var version = parseInt($(this).attr("version"));
       
        $(this).children("step").each( function() {
            var step = $(this).attr("name");
            var errors = $(this).children("error");
           
            if ( errors.length > 0 )
            {
                var verPatch = version % 10;
                var verMinor = parseInt(version / 10) % 10;
                var verMajor = parseInt(version / 100) % 10;
                
                var HTMLString = "<div class=\"item\">" +
                    "<div><span class=\"version\">update " + verMajor + "." + verMinor + "." + verPatch + "</span>&nbsp;" +
                    "<span class=\"step\">\"" + step + "\"</span></div>" + 
                    "<div class=\"message\">";
                   
                errors.each( function() {
                    HTMLString += $(this).text() + "<br/>";
                    numErrors++;
                });
                
                HTMLString += "</div></div>";
                   
                $("#error").append( HTMLString ); 
            }
        });    
    });
    
    if ( numErrors > 0 )
    {
        $("#error").show();
    }
    else
    {
        loadCleanup()
    }
}

function updateDatabase()
{
    var parameter = {
		version  : $("#version").val()
	};
	
	$.ajax({
		type     : "POST",
		url      : "query/upgrade.php",
		dataType : "xml",
		async    : true,
		data     : parameter,
		success  : showLog
	});
}

jQuery(document).ready( function() {
    $("#error").hide();
});