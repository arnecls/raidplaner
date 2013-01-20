var g_NewCharIndex = 0;

function CCharacterList()
{
    this.Characters = Array();
    
    // -------------------------------------------------------------------------
    
    this.AddCharacter = function( a_Id, a_Name, a_Class, a_IsMain, a_Role1, a_Role2 )
    {
        var charData = {
            id        : a_Id,
            name      : a_Name,
            charClass : a_Class,
            mainChar  : a_IsMain,
            role1     : a_Role1,
            role2     : a_Role2
        };        
        
        if ( a_IsMain )
        {
            // new main char, disable all others
            this.ToggleMainChar( this.Characters.length );
        }
        
        this.Characters.push(charData);
    }
    
    // -------------------------------------------------------------------------
    
    this.AddNewCharacter = function()
    {
        var defaultRole = g_RoleIds[ g_Classes[g_ClassIdx["empty"]].roles[0] ];
        this.AddCharacter( 0, "", "empty", this.Characters.length == 0, defaultRole, defaultRole );
    }
    
    // -------------------------------------------------------------------------
    
    this.ToggleMainChar = function( a_Idx )
    {
        for ( var i=0; i<this.Characters.length; ++i )
        {
            this.Characters[i].mainChar = (i == a_Idx);
        }
    }
    
    // -------------------------------------------------------------------------
    
    this.RemoveCharacter = function( a_Idx )
    {
        this.Characters.splice(a_Idx,1);
        this.RebuildSlots();
    }
    
    // -------------------------------------------------------------------------
    
    this.DisplaySlots = function( a_Index, a_GrpIdx, a_MaxSlots )
    {
        var HTMLString = "";
        var charIdx = a_Index;
        
        for ( var i=0; (charIdx < this.Characters.length) && (i < a_MaxSlots); ++i )
        {
            var currentChar = this.Characters[charIdx];
            
            if ( (i==0) && (charIdx != 0) )
            {
                // prev group slot
                
                var prevGroupId = a_GrpIdx - 1;                
                HTMLString += "<span class=\"nextgroup\" id=\"next" + prevGroupId + "\"></span>";
            } 
            else
            {
                // character slot
                
                HTMLString += "<span class=\"charslot\" id=\"char" + charIdx + "\">";

                HTMLString += "<div class=\"clearchar\"></div>";
                HTMLString += "<div class=\"class\" style=\"background: url(images/classesbig/" + currentChar.charClass + ".png)\">";
            
                if ( currentChar.mainChar )
                    HTMLString += "<div class=\"badge mainchar\"></div>";
                else
                    HTMLString += "<div class=\"badge twink\"></div>";
            
                HTMLString += "</div>";
                
                if ( currentChar.id == 0 )
                    HTMLString += "<input type=\"text\" class=\"newname\" value=\"" + currentChar.name + "\"/>";
                else
                    HTMLString += "<div class=\"name\">" + currentChar.name + "</div>";
                    
                HTMLString += "<span><img class=\"role newrole1\" style=\"margin-left:24px\" src=\"images/roles/" + g_RoleIdents[currentChar.role1] + ".png\"/></span>";
                HTMLString += "<span><img class=\"role newrole2\" src=\"images/roles/" + g_RoleIdents[currentChar.role2] + ".png\"/></span>";
            
                HTMLString += "</span>";
            
                ++charIdx;
            }
        }
        
        if (charIdx == this.Characters.length)
        {
            // New char button
            HTMLString += "<span class=\"newchar\"></span>";
        } 
        else
        {        
            // next group slot
            var nextGroupId = a_GrpIdx + 1;                
            HTMLString += "<span class=\"nextgroup\" id=\"next" + nextGroupId + "\"></span>";
        }
        
        return {
            HTMLString : HTMLString,
            charIdx    : charIdx
        };
    }
    
    // -------------------------------------------------------------------------
    
    this.RebuildSlots = function() 
    {
        var slotsPerGroup = 6;
        var HTMLString = "";
        
        var visibleGroup = $("#charlist").children(".charGroup:visible");
        var showGrpIdx = (visibleGroup.length == 0) ? 0 : parseInt( visibleGroup.attr("id").substr(3) );
        var charIdx = 0;
        
        if ( this.Characters.length == 0 )
        {
            HTMLString += "<div id=\"grp" + grpIdx + "\" class=\"charGroup\">";
            HTMLString += "<span class=\"newchar\"></span>";
            HTMLString += "</div>";
        }
        else
        {
            for ( var grpIdx=0; charIdx < this.Characters.length; ++grpIdx )
            {
                var generated = this.DisplaySlots( charIdx, grpIdx, slotsPerGroup );
                            
                HTMLString += "<div id=\"grp" + grpIdx + "\" class=\"charGroup\">";
                HTMLString += generated.HTMLString;
                HTMLString += "</div>";
                
                charIdx = generated.charIdx;
            }
        }
        
        $("#charlist").empty().append(HTMLString);
        
        var groups = $("#charlist").children(".charGroup");
        var charList = this;
        
        // Show currently active group
        
        showGrpIdx = Math.min(showGrpIdx, groups.length-1);        
        groups.hide();
        groups.eq(showGrpIdx).show();
        
        // Bind events
        
        groups.each( function() {
        
            // add a new character
        
            $(this).children(".newchar").click( function( event ) {
                charList.AddNewCharacter();
                charList.RebuildSlots();
                event.stopPropagation();                
                onUIDataChange();
            });
            
            // show next group
            
            $(this).children(".nextgroup").click( function( event ) {
                var nextGrpIdx = parseInt($(this).attr("id").substr(4));
                groups.hide();
                groups.eq(nextGrpIdx).show();
                hideTooltip();
                event.stopPropagation();
            });
            
            $(this).children(".charslot").each( function() {
            
                var charIdx = parseInt( $(this).attr("id").substr(4) );
                var isNew = charList.Characters[charIdx].id == 0;
                
                // Main char switcher
                
                $(this).children(".class").children(".badge").click( function( event ) { 
                    charList.ToggleMainChar( charIdx );
                    
                    groups.children(".charslot").children(".class").children(".badge")
                        .removeClass("mainchar")
                        .removeClass("twink")
                        .addClass("twink");
                        
                    $(this)
                        .removeClass("twink")
                        .addClass("mainchar");
                    
                    event.stopPropagation();                
                    onUIDataChange();
                });
                
                // Role1 switcher
                
                $(this).children("span").children(".newrole1").click( function( event ) { 
                    showTooltipRoleList( $(this), true ); 
                    event.stopPropagation();
                });
                
                // Role2 switcher
                
                $(this).children("span").children(".newrole2").click( function( event ) { 
                    showTooltipRoleList( $(this), false ); 
                    event.stopPropagation();
                });
                
                // Class switcher (only for new characters)
                // Name changer (only for new characters)
                
                if ( isNew )
                {
                    $(this).children(".class")
                        .addClass("newclass")
                        .click( function( event ) { 
                            showTooltipClassList( $(this) ); 
                            event.stopPropagation(); 
                        });
                        
                    $(this).children(".newname").change( function() {
                        charList.Characters[charIdx].name = $(this).val();
                    });
                }
                
                // Remove char
                
                $(this).children(".clearchar").click( function() {
                    if ( isNew )
                    {
                        charList.RemoveCharacter( charIdx );
                    }
                    else
                    {
                        confirm( L("ConfirmDeleteCharacter") + "<br>" + L("AttendancesRemoved"),
                                 L("DeleteCharacter"), L("Cancel"),
                                 function() { 
                                    charList.RemoveCharacter( charIdx );                
                                    onUIDataChange();
                                 });
                    }                     
                });
    
            });
        });
    }
}

// -----------------------------------------------------------------------------

function setupCharacters( a_Message )
{
    var charList = $("#charlist").data("characters");
    
    a_Message.children("character").each( function() {

        charList.AddCharacter(
            $(this).children("id").text(),
            $(this).children("name").text(),
            $(this).children("class").text(),
            ($(this).children("mainchar").text() == "true" ),
            $(this).children("role1").text(),
            $(this).children("role2").text()
        );
    });
    
    charList.RebuildSlots();
}

// -----------------------------------------------------------------------------

function setupAttendance( a_Message )
{
    // Static attend values

    var numRaids   = parseInt( a_Message.children("attendance").children("raids").text() );
    var numOk      = parseInt( a_Message.children("attendance").children("ok").text() );
    var numAvail   = parseInt( a_Message.children("attendance").children("available").text() );
    var numUnavail = parseInt( a_Message.children("attendance").children("unavailable").text() );
    var numMissed  = numRaids - (numOk + numAvail + numUnavail);

    var barSize  = 750;

    var sizeOk      = (numOk / numRaids) * barSize;
    var sizeAvail   = (numAvail / numRaids) * barSize;
    var sizeUnavail = (numUnavail / numRaids) * barSize;
    var sizeMissed  = (numMissed / numRaids) * barSize;

    // Role attend values

    var roleAttends = new Array();
    var roleBarSizes = new Array();
    var numRoleAttends = 0;

    for ( var i=0; i<g_RoleIdents.length; ++i )
    {
       roleAttends[i]  = parseInt( a_Message.children("attendance").children(g_RoleIdents[i]).text() );
       roleBarSizes[i] = (roleAttends[i] / numOk) * barSize;
       numRoleAttends += roleAttends[i];
    }

    // Render bars

    HTMLString = "<div class=\"attendanceCount\" style=\"" + barSize + "px\"><span class=\"start\"></span>";

    if (numOk > 0)      HTMLString += "<span class=\"ok\" style=\"width: " + sizeOk.toFixed() + "px\"><div class=\"count\">" + numOk + "</div></span>";
    if (numAvail > 0)   HTMLString += "<span class=\"available\" style=\"width: " + sizeAvail.toFixed() + "px\"><div class=\"count\">" + numAvail + "</div></span>";
    if (numUnavail > 0) HTMLString += "<span class=\"unavailable\" style=\"width: " + sizeUnavail.toFixed() + "px\"><div class=\"count\">" + numUnavail + "</div></span>";
    if (numMissed > 0)  HTMLString += "<span class=\"missed\" style=\"width: " + sizeMissed.toFixed() + "px\"><div class=\"count\">" + numMissed + "</div></span>";
    if (numRaids == 0)  HTMLString += "<span class=\"missed\" style=\"width: " + barSize + "px\"><div class=\"count\">&nbsp;</div></span>";

    HTMLString += "<span class=\"end\"></span></div>";
    HTMLString += "<div class=\"label\">" + L("RaidAttendance") + "</div>";
    HTMLString += "<div class=\"attendanceType\" style=\"" + barSize + "px\"><span class=\"start\"></span>";

    if (numRoleAttends == 0)
    {
       HTMLString += "<span class=\"missed\" style=\"width: " + barSize + "px\"><div class=\"count\">&nbsp;</div></span>";
    }
    else
    {
        for ( var i=0; i<g_RoleIdents.length; ++i )
        {
           if ( roleAttends[i] > 0 )
               HTMLString += "<span class=\"role"+(i+1)+"\" style=\"width: " + roleBarSizes[i].toFixed() + "px\"><div class=\"count\">" + roleAttends[i] + "</div></span>";
        }
    }

    HTMLString += "<span class=\"end\"></span></div>";

    // Print static labels

    HTMLString += "<div class=\"label\">" + L("RolesInRaids") + "</div>";

    HTMLString += "<div class=\"labels\">";

    HTMLString += "<div>";
    HTMLString += "<div class=\"box ok\"><span class=\"start\"></span><span class=\"end\"></span></div>";
    HTMLString += "<div class=\"label\">" + numOk + "x " + L("Attended") + "</div>";

    HTMLString += "<div class=\"box available\"><span class=\"start\"></span><span class=\"end\"></span></div>";
    HTMLString += "<div class=\"label\">" + numAvail + "x " + L("Queued") + "</div>";

    HTMLString += "<div class=\"box unavailable\"><span class=\"start\"></span><span class=\"end\"></span></div>";
    HTMLString += "<div class=\"label\">" + numUnavail + "x " + L("Absent") + "</div>";

    HTMLString += "<div class=\"box missed\"><span class=\"start\"></span><span class=\"end\"></span></div>";
    HTMLString += "<div class=\"label\">" + numMissed + "x " + L("Missed") + "</div>";
    HTMLString += "</div>";

    // Print role labels

    HTMLString += "<div style=\"clear: left; padding-top: 5px\">";

    for ( var i=0; i<g_RoleIdents.length; ++i )
    {
       HTMLString += "<span>";
       HTMLString += "<div class=\"box role"+(i+1)+"\"><span class=\"start\"></span><span class=\"end\"></span></div>";
       HTMLString += "<div class=\"label\">" + roleAttends[i] + "x " + g_RoleNames[g_RoleIdents[i]] + "</div>";
       HTMLString += "</span>";
    }

    HTMLString += "</div>";
    HTMLString += "</div>";

    $("#attendance").empty().append(HTMLString);
}

// -----------------------------------------------------------------------------

function displayProfile( a_XMLData )
{
    var Message = $(a_XMLData).children("messagehub");
    var HTMLString = "<div id=\"profile\">";

    HTMLString += "<h1>" + L("Characters") + "</h1>";

    HTMLString += "<div id=\"charlist\"></div>";
    HTMLString += "<button id=\"profile_apply\" class=\"button_profile\" type=\"button\">" + L("Apply") + "</button>";

    if ( Message.children("binding").text() == "none" )
    {
        HTMLString += "<button id=\"profile_password\" class=\"button_profile\" type=\"button\">" + L("ChangePassword") + "</button>";
    }

    HTMLString += "<h1 style=\"margin-top: 50px\">" + L("RaidAttendance") + "</h1>";
    HTMLString += "<div id=\"attendance\">";
    HTMLString += "</div>";

    HTMLString += "</div>";

    $("#body").empty().append(HTMLString);    
    $("#charlist").data("characters", new CCharacterList() );

    setupCharacters( Message );
    setupAttendance( Message );

    $("#profile_apply").button({ icons: { secondary: "ui-icon-disk" }})
        .click( function() { triggerProfileUpdate( false ); } )
        .css( "font-size", 11 );

    $("#profile_password").button({ icons: { secondary: "ui-icon-locked" }})
        .click( function() { displayChangePassword( false ); } )
        .css( "font-size", 11 );
}

// -----------------------------------------------------------------------------

function displayForeignProfile( a_XMLData )
{
    var Message = $(a_XMLData).children("messagehub");
    var HTMLString = "<div id=\"profile\">";

    HTMLString += "<h1>" + L("EditForeignCharacters") + " " + Message.children("name").text() + "</h1>";

    HTMLString += "<div id=\"charlist\"></div>";
    HTMLString += "<button id=\"profile_apply\" class=\"button_profile\" type=\"button\">" + L("Apply") + "</button>";

    if ( Message.children("binding").text() == "none" )
    {
        HTMLString += "<button id=\"profile_password\" class=\"button_profile\" type=\"button\">" + L("ChangePassword") + "</button>";
    }

    HTMLString += "<h1 style=\"margin-top: 50px\">" + L("RaidAttendance") + "</h1>";
    HTMLString += "<div id=\"attendance\">";
    HTMLString += "</div>";

    HTMLString += "</div>";

    $("#body").empty().append(HTMLString);
    $("#charlist").data("characters", new CCharacterList() );

    setupCharacters( Message );
    setupAttendance( Message );

    $("#charlist").data( "userid", Message.children("userid").text() );

    $("#profile_apply").button({ icons: { secondary: "ui-icon-disk" }})
        .click( function() { triggerProfileUpdate( true ); } )
        .css( "font-size", 11 );

    $("#profile_password").button({ icons: { secondary: "ui-icon-locked" }})
        .click( function() { displayChangePassword( true ); } )
        .css( "font-size", 11 );
}

// -----------------------------------------------------------------------------

function displayChangePassword( a_ForeignUser )
{
    if ( g_User == null )
        return;

    var foreignUserId  = 0;
    var requireOldPass = true;

    if ( a_ForeignUser )
    {
        foreignUserId = parseInt( $("#charlist").data("userid") );

        if ( foreignUserId != g_User.id )
            requireOldPass = false;
    }

    var HTMLString = "";

    HTMLString += "<div class=\"login\">";

    if ( requireOldPass )
    {
        HTMLString += "<div>";
        HTMLString += "<input id=\"logindummy1\" type=\"text\" class=\"text\" value=\"" + L("OldPassword") + "\"/>";
        HTMLString += "<input id=\"loginold\" type=\"password\" class=\"textactive\" name=\"old_pass\"/>";
        HTMLString += "</div>";
    }

    HTMLString += "<div>";
    HTMLString += "<input id=\"logindummy2\" type=\"text\" class=\"text\" value=\"" + L("Password") + "\"/>";
    HTMLString += "<input id=\"loginpass\" type=\"password\" class=\"textactive\" name=\"new_pass\"/>";
    HTMLString += "</div>";
    HTMLString += "<div>";
    HTMLString += "<input id=\"logindummy3\" type=\"text\" class=\"text\" value=\"" + L("RepeatPassword") + "\"/>";
    HTMLString += "<input id=\"loginpass_repeat\" type=\"password\" class=\"textactive\" name=\"pass_repeat\"/>";
    HTMLString += "</div>";
    HTMLString += "<button id=\"dochange\" style=\"margin-left: 5px; margin-top: 10px; font-size: 11px\">" + L("ChangePassword") + "</button>";
    HTMLString += "</div>";

    $("#body").empty().append(HTMLString);

    if ( requireOldPass )
    {
        // Old password

        $("#loginold").hide();
        $("#logindummy1").show();

        $("#logindummy1").focus( function() {
            $("#logindummy1").hide();
            $("#loginold").show().focus();
        });

        $("#loginold").blur( function() {
            if ( $("#loginold").val() == "" ) {
                $("#loginold").hide();
                $("#logindummy1").show();
            }
        });
    }

    // Password

    $("#loginpass").hide();
    $("#logindummy2").show();

    $("#logindummy2").focus( function() {
        $("#logindummy2").hide();
        $("#loginpass").show().focus();
    });

    $("#loginpass").blur( function() {
        if ( $("#loginpass").val() == "" ) {
            $("#loginpass").hide();
            $("#logindummy2").show();
        }
    });

    // Repeat

    $("#loginpass_repeat").hide();
    $("#logindummy3").show();

    $("#logindummy3").focus( function() {
        $("#logindummy3").hide();
        $("#loginpass_repeat").show().focus();
    });

    $("#loginpass_repeat").blur( function() {
        if ( $("#loginpass_repeat").val() == "" ) {
            $("#loginpass_repeat").hide();
            $("#logindummy3").show();
        }
    });

    $("#dochange").button().click( function() {

        if ( $("#loginpass").val() != $("#loginpass_repeat").val() )
        {
            notify( L("PasswordsNotMatch") );
        }
        else
        {
            var parameters = {
                passOld : $("#loginold").val(),
                passNew : $("#loginpass").val(),
                id      : (a_ForeignUser) ? foreignUserId : 0
            };

            if ( a_ForeignUser )
            {
                AsyncQuery( "change_password", parameters, displayForeignProfile );
            }
            else
            {
                AsyncQuery( "change_password", parameters, displayProfile );
            }
        }
    });
}

// -----------------------------------------------------------------------------

function triggerProfileUpdate( a_ForeignUser )
{
    if ( g_User == null )
        return;

    var idArray       = Array();
    var nameArray     = Array();
    var classArray    = Array();
    var mainCharArray = Array();
    var role1Array    = Array();
    var role2Array    = Array();

    var invalidData = false;

    var charList = $("#charlist").data("characters");

    for ( var i=0; i<charList.Characters.length; ++i )
    {
        var charData = charList.Characters[i];
        
        idArray.push( charData.id );
        classArray.push( charData.charClass );
        mainCharArray.push( charData.mainChar );
        role1Array.push( charData.role1 );
        role2Array.push( charData.role2 );
        
        if ( charData.id == 0 )
        {
            if ( charData.charClass == "empty" )
            {
                notify( L("Error") + ".<br>" + L("NoClass") );
                invalidData = true;
            }
            else if ( charData.name.length == 0 )
            {
                notify( L("Error") + ".<br>" + L("NoName") );
                invalidData = true;
            }

            nameArray.push( charData.name );
        }
        else
        {
            nameArray.push("");
        }
    }

    if ( !invalidData )
    {

        if ( a_ForeignUser )
        {
            var parameters = {
                userid    : $("#charlist").data("userid"),
                charId    : idArray,
                name      : nameArray,
                charClass : classArray,
                mainChar  : mainCharArray,
                role1     : role1Array,
                role2     : role2Array
            };

            onAppliedUIDataChange();
            AsyncQuery( "profile_update", parameters, displaySettings );
        }
        else
        {
            var parameters = {
                charId    : idArray,
                name      : nameArray,
                charClass : classArray,
                mainChar  : mainCharArray,
                role1     : role1Array,
                role2     : role2Array
            };

            onAppliedUIDataChange();
            AsyncQuery( "profile_update", parameters, displayProfile );
        }
    }
}

// -----------------------------------------------------------------------------

function loadProfile()
{
    reloadUser();

    if ( g_User == null )
        return;

    $("#body").empty();

    var Parameters = {
    };

    AsyncQuery( "query_profile", Parameters, displayProfile );
}