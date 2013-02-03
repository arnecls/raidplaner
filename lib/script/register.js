function validateRegistration()
{
    if ( ($("#loginname").val() == "") ||
         ($("#loginname").val() == L("Username")) )
    {
        notify( L("EnterValidUsername") );
        return false;
    }

    if ( ($("#loginpass").val() == "") ||
         ($("#loginpass").val() == L("Password")) )
    {
        notify( L("EnterNonEmptyPassword") );
        return false;
    }

    if ( $("#loginpass").val() != $("#loginpass_repeat").val() )
    {
        notify( L("PasswordsNotMatch") );
        return false;
    }

    var Parameters = {
        name : $("#loginname").val(),
        pass : $("#loginpass").val()
    };

    AsyncQuery( "user_create", Parameters, function( a_XMLData ) {
        var Message = $(a_XMLData).children("messagehub");

        if ( Message.children("error").size() == 0 )
        {
            notify( L("RegistrationDone") + "<br/>" + L("ContactAdminToUnlock") );
            changeContext("login");
            displayLogin();
        }
    });
}

// -----------------------------------------------------------------------------

function switchRegisterPassField()
{
    $("#loginpass").after("<input id=\"loginpass\" type=\"password\" class=\"textactive\" name=\"pass\"/>");
    $("#loginpass:first").detach();

    $("#loginpass").focus();
    $("#loginpass").blur( function() {
        if ( $(this).val() == "" )
        {
            $(this).unbind("blur"); // avoid  additional call once entered
            $(this).detach();
            $("#loginpass_repeat").before("<input id=\"loginpass\" type=\"text\" class=\"text\" value=\"" + L("Password") + "\">")
            $("#loginpass").focus( switchRegisterPassField );
        }
    });
    
    $("#loginpass").keyup( function() {
        var pass  = $("#loginpass").val();
        var color = "#ccc";
        
        if ( pass.length > 0 )
        {
            var charTypes = new Array( 
                { used : 0, min: 32,  max: 48  },  // special chars 1
                { used : 0, min: 48,  max: 58  },  // number
                { used : 0, min: 58,  max: 65  },  // special chars 2
                { used : 0, min: 65,  max: 91  },  // A-Z
                { used : 0, min: 91,  max: 97  },  // special chars 2
                { used : 0, min: 97,  max: 123 },  // a-z
                { used : 0, min: 123, max: 127 }); // special chars 3
                
            var colors = new Array(
                { r: 255, g: 0,   b: 0 },
                { r: 255, g: 255, b: 0 },
                { r: 0,   g: 255, b: 0 });
                
            // Analyze charset
        
            for ( i=0; i < pass.length; ++i )
            {
                var charCode = pass.charCodeAt(i);
                for ( ctIdx=0; ctIdx < charTypes.length; ++ctIdx )
                {
                    if ( (charCode >= charTypes[ctIdx].min) && (charCode < charTypes[ctIdx].max) )
                    {
                        ++charTypes[ctIdx].used;
                        break;
                    }
                }
            }
            
            var variantBase = 0;
            var asciiChars  = 0;
            
            for ( ctIdx=0; ctIdx < charTypes.length; ++ctIdx )
            {
                if ( charTypes[ctIdx].used > 0 )
                {
                    asciiChars  += charTypes[ctIdx].used;
                    variantBase += charTypes[ctIdx].max - charTypes[ctIdx].min;
                }
            }
            
            if ( asciiChars < pass.length )
                variantBase += 32;
            
            // Choose correct color and progress
            
            quality = Math.min(1.0, Math.pow(variantBase, pass.length/10.0) / 128.0 );
            
            color = "#";
            var segmentSize  = 1.0 / (colors.length-1);
            var baseColorIdx = Math.min( parseInt(quality / segmentSize), colors.length-2 );
            var scale        = (quality - (segmentSize * baseColorIdx)) / segmentSize;
            
            var minColor = colors[baseColorIdx];
            var maxColor = colors[baseColorIdx+1];
            
            var r = parseInt(minColor.r * (1-scale) + maxColor.r * scale);
            var g = parseInt(minColor.g * (1-scale) + maxColor.g * scale);
            var b = parseInt(minColor.b * (1-scale) + maxColor.b * scale);
            
            color += ((r<16) ? "0" : "") + r.toString(16);
            color += ((g<16) ? "0" : "") + g.toString(16);
            color += ((b<16) ? "0" : "") + b.toString(16);
        }
        
        var width = parseInt(quality*100);
                
        $("#strength").css("background-color",color).css("width",width+"%");
    });
}

// -----------------------------------------------------------------------------

function switchRegisterPassRepeatField()
{
    $("#loginpass_repeat").after("<input id=\"loginpass_repeat\" type=\"password\" class=\"textactive\" name=\"pass_repeat\"/>");
    $("#loginpass_repeat:first").detach();

    $("#loginpass_repeat").focus();
    $("#loginpass_repeat").blur( function() {
        if ( $(this).val() == "" )
        {
            $(this).unbind("blur"); // avoid  additional call once entered
            $(this).detach();
            $("#loginpass").after("<input id=\"loginpass_repeat\" type=\"text\" class=\"text\" value=\"" + L("RepeatPassword") + "\">")
            $("#loginpass_repeat").focus( switchRegisterPassRepeatField );
        }
    });
}

// -----------------------------------------------------------------------------

function displayRegistration()
{
    var HTMLString = "";

    HTMLString += "<div class=\"login\" style=\"margin-top:-80px\">";
    HTMLString += "<input type=\"hidden\" name=\"register\"/>";
    HTMLString += "<input type=\"hidden\" name=\"nocheck\"/>";
    HTMLString += "<div>";
    HTMLString += "<input id=\"loginname\" type=\"text\" class=\"text\" value=\"" + L("Username") + "\"/>";
    HTMLString += "</div>";
    HTMLString += "<div id=\"strprogress\"><span class=\"pglabel\">"+L("PassStrength")+"</span><span id=\"strength\"></span></div>";
    HTMLString += "<div>";
    HTMLString += "<input id=\"loginpass\" type=\"text\" class=\"text\" value=\"" + L("Password") + "\"/>";
    HTMLString += "</div>";
    HTMLString += "<div>";
    HTMLString += "<input id=\"loginpass_repeat\" type=\"text\" class=\"text\" value=\"" + L("RepeatPassword") + "\"/>";
    HTMLString += "</div>";
    HTMLString += "<button id=\"doregister\" onclick=\"validateRegistration()\" style=\"margin-left: 5px\" class=\"button_register\">" + L("Register") + "</button>";
    HTMLString += "</div>";

    $("#body").empty().append(HTMLString);

    $("#loginname").focus( function() {
        $("#loginname").removeClass("text").addClass("textactive");

        if ( $("#loginname").val() == L("Username") )
            $("#loginname").val("");
    });

    $("#loginname").blur( function() {
        if ( $("#loginname").val() == "" )
        {
            $("#loginname").removeClass("textactive").addClass("text");
            $("#loginname").val(L("Username"));
        }
    });

    $("#loginpass").focus( switchRegisterPassField );
    $("#loginpass_repeat").focus( switchRegisterPassRepeatField );

    $("#doregister").button().css( "font-size", 11 );
}