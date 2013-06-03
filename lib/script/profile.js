var g_NewCharIndex = 0;

function CCharacterList()
{
    this.mCharacters = Array();
    
    // -------------------------------------------------------------------------
    
    this.addCharacter = function( aId, aName, aClass, aIsMain, aRole1, aRole2 )
    {
        var charData = {
            id        : aId,
            name      : aName,
            charClass : aClass,
            mainChar  : aIsMain,
            role1     : aRole1,
            role2     : aRole2
        };        
        
        if ( aIsMain )
        {
            // new main char, disable all others
            this.toggleMainChar( this.mCharacters.length );
        }
        
        this.mCharacters.push(charData);
    };
    
    // -------------------------------------------------------------------------
    
    this.addNewCharacter = function()
    {
        this.addCharacter( 0, "", "empty", this.mCharacters.length === 0, -1, -1 );
    };
    
    // -------------------------------------------------------------------------
    
    this.toggleMainChar = function( aIdx )
    {
        for ( var i=0; i<this.mCharacters.length; ++i )
        {
            this.mCharacters[i].mainChar = (i == aIdx);
        }
    };
    
    // -------------------------------------------------------------------------
    
    this.removeCharacter = function( aIdx )
    {
        this.mCharacters.splice(aIdx,1);
        this.rebuildSlots();
    };
    
    // -------------------------------------------------------------------------
    
    this.generateSlots = function( aIndex, aGrpIdx, aMaxSlots )
    {
        var HTMLString = "";
        var CharIdx = aIndex;
        
        for ( var i=0; (CharIdx < this.mCharacters.length) && (i < aMaxSlots); ++i )
        {
            var CurrentChar = this.mCharacters[CharIdx];
            
            if ( (i===0) && (CharIdx !== 0) )
            {
                // prev group slot
                
                var PrevGroupId = aGrpIdx - 1;                
                HTMLString += "<span class=\"nextgroup\" id=\"next" + PrevGroupId + "\"></span>";
            } 
            else
            {
                // character slot
                
                HTMLString += "<span class=\"charslot\" id=\"char" + CharIdx + "\">";

                HTMLString += "<div class=\"clearchar\"></div>";
                HTMLString += "<div class=\"class\" style=\"background: url(images/classesbig/" + CurrentChar.charClass + ".png)\">";
            
                if ( CurrentChar.mainChar )
                    HTMLString += "<div class=\"badge mainchar\"></div>";
                else
                    HTMLString += "<div class=\"badge twink\"></div>";
            
                HTMLString += "</div>";
                
                if ( CurrentChar.id === 0 )
                {
                    HTMLString += "<div class=\"newname_label\">"+L("CharName")+"</div>";
                    HTMLString += "<input type=\"text\" class=\"newname\" value=\"" + CurrentChar.name + "\"/>";
                }
                else
                {
                    HTMLString += "<div class=\"name\">" + CurrentChar.name + "</div>";
                }
                
                HTMLString += "<div class=\"role_group\" style=\"position: relative; left: 24px; top: 10px\">";
                
                if ( CurrentChar.charClass == "empty" )
                {
                    HTMLString += "<div class=\"role newrole1\" style=\"background-image:url(images/classessmall/empty.png)\"><div class=\"mainrole\"></div></div>";
                    HTMLString += "<div class=\"role newrole2\" style=\"background-image:url(images/classessmall/empty.png); left: 10px\"></div>";
                }   
                else
                {
                    HTMLString += "<div class=\"role newrole1\" style=\"background-image:url(images/roles/" + gRoleIdents[CurrentChar.role1] + ".png)\"><div class=\"mainrole\"></div></div>";
                    HTMLString += "<div class=\"role newrole2\" style=\"background-image:url(images/roles/" + gRoleIdents[CurrentChar.role2] + ".png); left: 10px\"></div>";
                }
                          
                HTMLString += "</div>";
                HTMLString += "</span>";
            
                ++CharIdx;
            }
        }
        
        if (CharIdx == this.mCharacters.length)
        {
            // New char button
            HTMLString += "<span class=\"newchar\"></span>";
        } 
        else
        {        
            // next group slot
            var NextGroupId = aGrpIdx + 1;                
            HTMLString += "<span class=\"nextgroup\" id=\"next" + NextGroupId + "\"></span>";
        }
        
        return {
            HTMLString : HTMLString,
            charIdx    : CharIdx
        };
    };
    
    // -------------------------------------------------------------------------
    
    this.rebuildSlots = function() 
    {
        var SlotsPerGroup = 6;
        var HTMLString = "";
        
        var VisibleGroup = $("#charlist > .charGroup:visible");
        var ShowGrpIdx = (VisibleGroup.length === 0) ? 0 : parseInt( VisibleGroup.attr("id").substr(3), 10 );
        var CharIdx = 0;
        
        if ( this.mCharacters.length === 0 )
        {
            HTMLString += "<div id=\"grp0\" class=\"charGroup\">";
            HTMLString += "<span class=\"newchar\"></span>";
            HTMLString += "</div>";
        }
        else
        {
            for ( var GrpIdx=0; CharIdx < this.mCharacters.length; ++GrpIdx )
            {
                var Generated = this.generateSlots( CharIdx, GrpIdx, SlotsPerGroup );
                            
                HTMLString += "<div id=\"grp" + GrpIdx + "\" class=\"charGroup\">";
                HTMLString += Generated.HTMLString;
                HTMLString += "</div>";
                
                CharIdx = Generated.charIdx;
            }
        }
        
        $("#charlist").empty().append(HTMLString);
        
        var Groups = $("#charlist > .charGroup");
        var CharList = this;
        
        // Show currently active group
        
        ShowGrpIdx = Math.min(ShowGrpIdx, Groups.length-1);        
        Groups.hide();
        Groups.eq(ShowGrpIdx).show();
        
        // Bind events
        
        Groups.each( function() {
        
            // add a new character
        
            $(this).children(".newchar").click( function( aEvent ) {
                CharList.addNewCharacter();
                CharList.rebuildSlots();
                aEvent.stopPropagation();                
                onUIDataChange();
            });
            
            // show next group
            
            $(this).children(".nextgroup").click( function( aEvent ) {
                var nextGrpIdx = parseInt($(this).attr("id").substr(4), 10);
                Groups.hide();
                Groups.eq(nextGrpIdx).show();
                hideTooltip();
                aEvent.stopPropagation();
            });
            
            $(this).children(".charslot").each( function() {
            
                var CharIdx = parseInt( $(this).attr("id").substr(4), 10 );
                var IsNew = CharList.mCharacters[CharIdx].id === 0;
                
                // Main char switcher
                
                $(".class:first > .badge:first", this).click( function( aEvent ) { 
                    CharList.toggleMainChar( CharIdx );
                    
                    $(".charslot > .class > .badge", Groups)
                        .removeClass("mainchar")
                        .removeClass("twink")
                        .addClass("twink");
                        
                    $(this)
                        .removeClass("twink")
                        .addClass("mainchar");
                    
                    aEvent.stopPropagation();                
                    onUIDataChange();
                });
                
                // Role1 switcher
                
                $("div > .newrole1", this).click( function( aEvent ) { 
                    showTooltipRoleList( $(this), true ); 
                    aEvent.stopPropagation();
                });
                
                // Role2 switcher
                
                $("div > .newrole2", this).click( function( aEvent ) { 
                    showTooltipRoleList( $(this), false ); 
                    aEvent.stopPropagation();
                });
                
                // Class switcher (only for new characters)
                // Name changer (only for new characters)
                
                if ( IsNew )
                {
                    $(this).children(".class")
                        .addClass("newclass")
                        .click( function( aEvent ) { 
                            showTooltipClassList( $(this) ); 
                            aEvent.stopPropagation(); 
                        });
                        
                    var NameInputField = $(this).children(".newname:first");
                    var NameLabelField = $(this).children(".newname_label:first");
                        
                    if ( NameInputField.val() !== "" )
                    {
                        NameInputField.show();
                        NameLabelField.hide();
                    }
                    
                    NameLabelField.click( function() { 
                        NameLabelField.hide();
                        NameInputField.show().focus();
                    });
                    
                    NameInputField.blur( function() {
                        if ($(this).val() === "")
                        {
                            NameInputField.hide();
                            NameLabelField.show();
                        }
                    });
                        
                    NameInputField.change( function() {
                        CharList.mCharacters[CharIdx].name = $(this).val();
                    });
                }
                
                // Remove char
                
                $(this).children(".clearchar").click( function() {
                    if ( IsNew )
                    {
                        CharList.removeCharacter( CharIdx );
                    }
                    else
                    {
                        confirm( L("ConfirmDeleteCharacter") + "<br>" + L("AttendancesRemoved"),
                                 L("DeleteCharacter"), L("Cancel"),
                                 function() { 
                                    CharList.removeCharacter( CharIdx );                
                                    onUIDataChange();
                                 });
                    }                     
                });
    
            });
        });
    };
}

// -----------------------------------------------------------------------------

function setupCharacters( aMessage )
{
    var CharList = $("#charlist").data("characters");
    
    aMessage.children("character").each( function() {

        CharList.addCharacter(
            $(this).children("id:first").text(),
            $(this).children("name:first").text(),
            $(this).children("class:first").text(),
            ($(this).children("mainchar:first").text() == "true" ),
            $(this).children("role1:first").text(),
            $(this).children("role2:first").text()
        );
    });
    
    CharList.rebuildSlots();
}

// -----------------------------------------------------------------------------

function generateUserAttendance( aMessage )
{
    // Static attend values
    
    var Attendances = aMessage.children("attendance");
    
    var NumRaids   = parseInt( $("raids:first", Attendances).text(), 10 );
    var NumOk      = parseInt( $("ok:first", Attendances).text(), 10 );
    var NumAvail   = parseInt( $("available:first", Attendances).text(), 10 );
    var NumUnavail = parseInt( $("unavailable:first", Attendances).text(), 10 );
    var NumMissed  = NumRaids - (NumOk + NumAvail + NumUnavail);

    var BarSize  = 750;

    var SizeOk      = (NumOk / NumRaids) * BarSize;
    var SizeAvail   = (NumAvail / NumRaids) * BarSize;
    var SizeUnavail = (NumUnavail / NumRaids) * BarSize;
    var SizeMissed  = (NumMissed / NumRaids) * BarSize;

    // Role attend values

    var RoleAttends = [];
    var RoleBarSizes = [];
    var NumRoleAttends = 0;

    for ( var i=0; i<gRoleIdents.length; ++i )
    {
       RoleAttends[i]  = parseInt( $("attendance > " + gRoleIdents[i], aMessage).text(), 10 );
       RoleBarSizes[i] = (RoleAttends[i] / NumOk) * BarSize;
       NumRoleAttends += RoleAttends[i];
    }

    // generate bars

    HTMLString = "<div class=\"attendanceCount\" style=\"" + BarSize + "px\"><span class=\"start\"></span>";

    if (NumOk > 0)      HTMLString += "<span class=\"ok\" style=\"width: " + SizeOk.toFixed() + "px\"><div class=\"count\">" + NumOk + "</div></span>";
    if (NumAvail > 0)   HTMLString += "<span class=\"available\" style=\"width: " + SizeAvail.toFixed() + "px\"><div class=\"count\">" + NumAvail + "</div></span>";
    if (NumUnavail > 0) HTMLString += "<span class=\"unavailable\" style=\"width: " + SizeUnavail.toFixed() + "px\"><div class=\"count\">" + NumUnavail + "</div></span>";
    if (NumMissed > 0)  HTMLString += "<span class=\"missed\" style=\"width: " + SizeMissed.toFixed() + "px\"><div class=\"count\">" + NumMissed + "</div></span>";
    if (NumRaids === 0)  HTMLString += "<span class=\"missed\" style=\"width: " + BarSize + "px\"><div class=\"count\">&nbsp;</div></span>";

    HTMLString += "<span class=\"end\"></span></div>";
    HTMLString += "<div class=\"label\">" + L("RaidAttendance") + "</div>";
    HTMLString += "<div class=\"attendanceType\" style=\"" + BarSize + "px\"><span class=\"start\"></span>";

    if (NumRoleAttends === 0)
    {
       HTMLString += "<span class=\"missed\" style=\"width: " + BarSize + "px\"><div class=\"count\">&nbsp;</div></span>";
    }
    else
    {
        for ( i=0; i<gRoleIdents.length; ++i )
        {
           if ( RoleAttends[i] > 0 )
               HTMLString += "<span class=\"role"+(i+1)+"\" style=\"width: " + RoleBarSizes[i].toFixed() + "px\"><div class=\"count\">" + RoleAttends[i] + "</div></span>";
        }
    }

    HTMLString += "<span class=\"end\"></span></div>";

    // Print static labels

    HTMLString += "<div class=\"label\">" + L("RolesInRaids") + "</div>";

    HTMLString += "<div class=\"labels\">";

    HTMLString += "<div>";
    HTMLString += "<div class=\"box ok\"><span class=\"start\"></span><span class=\"end\"></span></div>";
    HTMLString += "<div class=\"label\">" + NumOk + "x " + L("Attended") + "</div>";

    HTMLString += "<div class=\"box available\"><span class=\"start\"></span><span class=\"end\"></span></div>";
    HTMLString += "<div class=\"label\">" + NumAvail + "x " + L("Queued") + "</div>";

    HTMLString += "<div class=\"box unavailable\"><span class=\"start\"></span><span class=\"end\"></span></div>";
    HTMLString += "<div class=\"label\">" + NumUnavail + "x " + L("Absent") + "</div>";

    HTMLString += "<div class=\"box missed\"><span class=\"start\"></span><span class=\"end\"></span></div>";
    HTMLString += "<div class=\"label\">" + NumMissed + "x " + L("Missed") + "</div>";
    HTMLString += "</div>";

    // Print Role labels

    HTMLString += "<div style=\"clear: left; padding-top: 5px\">";

    for ( i=0; i<gRoleIdents.length; ++i )
    {
       HTMLString += "<span>";
       HTMLString += "<div class=\"box role"+(i+1)+"\"><span class=\"start\"></span><span class=\"end\"></span></div>";
       HTMLString += "<div class=\"label\">" + RoleAttends[i] + "x " + gRoleNames[gRoleIdents[i]] + "</div>";
       HTMLString += "</span>";
    }

    HTMLString += "</div>";
    HTMLString += "</div>";

    $("#attendance").empty().append(HTMLString);
}

// -----------------------------------------------------------------------------

function generateProfile( aXMLData )
{
    var Message = $(aXMLData).children("messagehub:first");
    
    if ( Message.children("error").length > 0 )
    {
        notify(Message.children("error").text());
        return false;
    }
    
    var HTMLString = "<div id=\"profile\">";

    HTMLString += "<h1>" + L("Characters") + "</h1>";

    HTMLString += "<div id=\"charlist\"></div>";
    HTMLString += "<button id=\"profile_apply\" class=\"button_profile apply_changes\" type=\"button\" disabled=\"disabled\">" + L("Apply") + "</button>";

    if ( (Message.children("bindingActive").text() == "false") || 
         (Message.children("binding").text() == "none") )
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
    generateUserAttendance( Message );

    $("#profile_apply").button({ icons: { secondary: "ui-icon-disk" }})
        .click( function() { triggerProfileUpdate(gUser.id); } )
        .css( "font-size", 11 );

    $("#profile_password").button({ icons: { secondary: "ui-icon-locked" }})
        .click( function() { generateChangePassword(gUser.id); } )
        .css( "font-size", 11 );
        
    return true;
}

// -----------------------------------------------------------------------------

function generateForeignProfile( aXMLData )
{
    var Message = $(aXMLData).children("messagehub:first");
    
    if ( Message.children("error").length > 0 )
    {
        notify(Message.children("error").text());
        return false;
    }
    
    var HTMLString = "<div id=\"profile\">";

    HTMLString += "<h1>" + L("EditForeignCharacters") + " " + Message.children("name").text() + "</h1>";

    HTMLString += "<div id=\"charlist\"></div>";
    HTMLString += "<button id=\"profile_apply\" class=\"button_profile\" type=\"button\">" + L("Apply") + "</button>";

    if ( (Message.children("bindingActive").text() == "false") || 
         (Message.children("binding").text() == "none") )
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
    generateUserAttendance( Message );
    
    var foreignId = Message.children("userid").text();

    $("#profile_apply").button({ icons: { secondary: "ui-icon-disk" }})
        .click( function() { triggerProfileUpdate(foreignId); } )
        .css( "font-size", 11 );

    $("#profile_password").button({ icons: { secondary: "ui-icon-locked" }})
        .click( function() { generateChangePassword(foreignId); } )
        .css( "font-size", 11 );
    
    return true;
}

// -----------------------------------------------------------------------------

function generateChangePassword( aUserId )
{
    if ( gUser == null )
        return;
        
    var HTMLString = "<div class=\"login\" style=\"margin-top:-80px\">";    
    var OldPassText = (aUserId == gUser.id) ? L("OldPassword") : L("AdminPassword");

    HTMLString += "<div id=\"oldPassField\">";
    HTMLString += "<input id=\"userid\" type=\"hidden\" value=\"" + aUserId +"\"/>";
    HTMLString += "<input id=\"dummy_old\" type=\"text\" class=\"text\" value=\"" + OldPassText + "\"/>";
    HTMLString += "<input id=\"pass_old\" type=\"password\" class=\"textactive\" name=\"old_pass\"/>";
    HTMLString += "</div>";
    
    HTMLString += "<div id=\"strprogress\"><span class=\"pglabel\">"+L("PassStrength")+"</span><span id=\"strength\"></span></div>";
    
    HTMLString += "<div>";
    HTMLString += "<input id=\"dummy_new\" type=\"text\" class=\"text\" value=\"" + L("Password") + "\"/>";
    HTMLString += "<input id=\"pass_new\" type=\"password\" class=\"textactive\" name=\"new_pass\"/>";
    HTMLString += "</div>";
    HTMLString += "<div>";
    HTMLString += "<input id=\"dummy_repeat\" type=\"text\" class=\"text\" value=\"" + L("RepeatPassword") + "\"/>";
    HTMLString += "<input id=\"pass_repeat\" type=\"password\" class=\"textactive\" name=\"pass_repeat\"/>";
    HTMLString += "</div>";
    HTMLString += "<button id=\"dochange\" style=\"margin-left: 5px; margin-top: 10px; font-size: 11px\">" + L("ChangePassword") + "</button>";
    HTMLString += "</div>";

    $("#body").empty().append(HTMLString);

    // Validation password

    $("#pass_old").hide();
    $("#dummy_old").show();

    $("#dummy_old").focus( function() {
        $("#dummy_old").hide();
        $("#pass_old").show().focus();
    });

    $("#pass_old").blur( function() {
        if ( $("#pass_old").val() === "" ) {
            $("#pass_old").hide();
            $("#dummy_old").show();
        }
    });
    
    // New password

    $("#pass_new").hide();
    $("#dummy_new").show();

    $("#dummy_new").focus( function() {
        $("#dummy_new").hide();
        $("#pass_new").show().focus();
    });

    $("#pass_new").blur( function() {
        if ( $("#pass_new").val() === "" ) {
            $("#pass_new").hide();
            $("#dummy_new").show();
        }
    });
    
    $("#pass_new").keyup( function() {
        var strength = getPasswordStrength( $("#pass_new").val() );
        var width = parseInt(strength.quality*100, 10);
                
        $("#strength").css({ "background-color": strength.color, "width": width+"%" });
    });

    // Repeat

    $("#pass_repeat").hide();
    $("#dummy_repeat").show();

    $("#dummy_repeat").focus( function() {
        $("#dummy_repeat").hide();
        $("#pass_repeat").show().focus();
    });

    $("#pass_repeat").blur( function() {
        if ( $("#pass_repeat").val() === "" ) {
            $("#pass_repeat").hide();
            $("#dummy_repeat").show();
        }
    });
    
    // Submit button

    $("#dochange").button().click( function() {
        if ( $("#pass_old").val() === "" )
        {
            if (aUserId == gUser.id) 
                notify(L("OldPasswordEmpty"));
            else
                notify(L("AdminPasswordEmpty"));
        }
        else if ( $("#pass_new").val() === "" ) 
        {
            notify(L("EnterNonEmptyPassword"));
        }
        else if ( $("#pass_new").val() != $("#pass_repeat").val() ) 
        {
            notify(L("PasswordsNotMatch"));
        }
        else
        {
            var Parameters = {
                UserId : gUser.id // always use logged in user to allow foreign edit
            };
            
            asyncQuery( "query_credentials_id", Parameters, triggerChangePassword );
        }
        
        return false;
    });
}

// -----------------------------------------------------------------------------

function updateNewPasswordProgress( aProgress )
{
    if ( aProgress == 100 )
    {
        $("#hashing").remove();
    }
    else
    {
        if ( $("#hashing").length === 0 )
            $("#oldPassField").before("<div id=\"hashing\"><span class=\"pglabel\">"+L("HashingInProgress")+"</span><span id=\"hashprogress\"></span></div>");
        
        $("#hashprogress").css("width", aProgress+"%");
    }
}

// -----------------------------------------------------------------------------

function triggerChangePassword( aXMLData )
{
    var Message = $(aXMLData).children("messagehub:first");
    
    if ( Message.children("error").length > 0 )
    {
        notify( Message.children("error").text() );
    }
    else
    {
        var Salt   = Message.children("salt").text();
        var Key    = Message.children("pubKey").text();
        var Method = Message.children("method").text();
        var Pass   = $("#pass_old").val();
        
        hash( Key, Method, Pass, Salt, updateNewPasswordProgress, function(aEncodedPass) {
            
            var Parameters = {
                passOld : aEncodedPass,
                passNew : $("#pass_new").val(),
                id      : $("#userid").val()
            };
        
            if ( Parameters.id != gUser.id )
            {
                asyncQuery( "change_password", Parameters, function(aXMLData) {
                    if (generateForeignProfile(aXMLData))
                        notify(L("PasswordChanged"));
                });
            }
            else
            {
                asyncQuery( "change_password", Parameters, function(aXMLData) {
                    if (generateProfile(aXMLData)) 
                        notify(L("PasswordChanged"));
                });
            }
        });
    }
}

// -----------------------------------------------------------------------------

function triggerProfileUpdate( aUserId )
{
    if ( gUser == null )
        return;

    var IdArray       = Array();
    var NameArray     = Array();
    var ClassArray    = Array();
    var MainCharArray = Array();
    var Role1Array    = Array();
    var Role2Array    = Array();

    var InvalidData = false;

    var CharList = $("#charlist").data("characters");

    for ( var i=0; i<CharList.mCharacters.length; ++i )
    {
        var CharData = CharList.mCharacters[i];
        
        IdArray.push( CharData.id );
        ClassArray.push( CharData.charClass );
        MainCharArray.push( CharData.mainChar );
        Role1Array.push( CharData.role1 );
        Role2Array.push( CharData.role2 );
        
        if ( CharData.id === 0 )
        {
            if ( CharData.charClass == "empty" )
            {
                notify( L("Error") + ".<br>" + L("NoClass") );
                InvalidData = true;
            }
            else if ( CharData.name.length === 0 )
            {
                notify( L("Error") + ".<br>" + L("NoName") );
                InvalidData = true;
            }

            NameArray.push( CharData.name );
        }
        else
        {
            NameArray.push("");
        }
    }

    if ( !InvalidData )
    {
        var Parameters = {
            id        : aUserId,
            charId    : IdArray,
            name      : NameArray,
            charClass : ClassArray,
            mainChar  : MainCharArray,
            role1     : Role1Array,
            role2     : Role2Array
        };
        
        onAppliedUIDataChange();
        asyncQuery("profile_update", Parameters, function(aXMLData) { reloadUser(); generateProfile(aXMLData); });
    }
}

// -----------------------------------------------------------------------------

function loadProfile()
{
    reloadUser();

    if ( gUser == null )
        return;

    $("#body").empty();

    var Parameters = {
    };

    asyncQuery( "query_profile", Parameters, generateProfile );
}