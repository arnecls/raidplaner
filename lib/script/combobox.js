(function( $ ) {
    $.widget( "ui.combobox", {

        _create: function() {
		    var selectNode = this.element;
            var selected = selectNode.children( ":selected" ).last();

            var nodeId   = selectNode.attr("id");
            var valueId  = "value_" + nodeId;
            var editId   = "edit_" + nodeId;
            var openId   = "open_" + nodeId;
            var optionId = "options_" + nodeId;

            HTMLString  = "<span class=\"combobox\">";
            HTMLString += "<input class=\"combobox_edit\" type=\"text\" id=\"" + editId + "\"/>"
            HTMLString += "<button class=\"combobox_value\" id=\"" + valueId + "\">" + selected.text() + "</button>";
			HTMLString += "<button class=\"combobox_open\" id=\"" + openId + "\"></button>";
            HTMLString += "<div class=\"combobox_list ui-corner-all\" id=\"" + optionId + "\"></div>";
            HTMLString += "</span>";
			
			selectNode.after( HTMLString );
            selectNode.hide();
			
            this.replacement = selectNode.next();
            this.editField   = $("#"+editId).hide();
            this.valueField  = $("#"+valueId);
            this.optionField = $("#"+optionId);

            // Fixing jQuery UI CSS problems ...

            $("#"+openId).button({ 
                icons: { primary: "ui-icon-triangle-2-n-s" },
                text: false 
            }).css( "width", 21 );
			
            $("#"+editId).parent().css( "margin-right", "3px" );

            var height = $("#"+valueId).height();
            var valueWidth = selectNode.outerWidth() - $("#"+openId).width();
            
            if ( /mozilla/.test(navigator.userAgent.toLowerCase()) )
            {
                $("#"+openId).children(".ui-icon").css("top", 8 );
            }

            this.valueField
                .button( { disabled: true } );

            this.valueField.children(".ui-button-text:first")
                .addClass( "combobox_valuetext" )
                .css("padding", "2px")
                .css("padding-left", "6px");

            this.valueField.parent()
                .buttonset()
                .addClass( selectNode.attr( "class" ) )
                .css("display", "inline-block");

            this.valueField
                .width( valueWidth );

            selectNode.children("option").each( function() {
                $("#"+optionId)
                    .append("<div class=\"option\">" + $(this).text() + "</div>")
                    .children().last()
                        .data( "value", $(this).attr("value") );
            });

            this.optionField.children(".option").eq( selectNode.context.selectedIndex ).addClass("selected");
            this.optionField.hide();

            // open the combobox

            $("#" + openId).click( function( event ) {
                var xOffset     = 0;
                var yOffset     = 0;
                var listWidth   = $("#" + optionId).width();
                var selectWidth = $("#" + valueId).width() + $("#" + openId).width();

                if ( listWidth > selectWidth )
                {
                    xOffset = listWidth - selectWidth;
                    $("#" + optionId).css( "width", listWidth );
                }
                else
                {
                    $("#" + optionId).css( "width", selectWidth );
                }

                var option = $("#"+optionId).children(".option").first();

                for ( i=0; i<selectNode.context.selectedIndex; ++i )
                {
                    yOffset += 18;//option.outerHeight( true );
                    option = option.next();
                }

                var yListOffset   = Math.min(yOffset, 90);
                var yScrollOffset = Math.max(0, yOffset - yListOffset);
                var listHeight    = selectNode.context.options.length * 18;

                if ( (yScrollOffset > 0) && (yScrollOffset > listHeight-198) )
                {
                    yListOffset += yScrollOffset - (listHeight-198);
                }

                var displayPosition   = $("#" + valueId).offset();
                displayPosition.top  -= yListOffset + 2;
                displayPosition.left -= xOffset;

                $(".combobox_list").hide();
                $("#" + optionId).show()
                    .offset( displayPosition )
                    .scrollTop( yScrollOffset );

                event.stopPropagation();
            });

            // selecting an option

            this.optionField.children( ".option" ).click( function() {

                $("#"+optionId).hide();
                $("#"+optionId).children(".option").removeClass("selected");

                $("#"+valueId).button( "option", "label", $(this).text() )
                    .children(".ui-button-text:first")
                        .removeClass( "combobox_valuetext" )
                        .addClass( "combobox_valuetext" )
                        .css("padding", "2px")
                        .css("padding-left", "6px");

                $(this).addClass("selected");

                selectNode.context.selectedIndex = $(this).index();
                selectNode.change();
            });
        },

        editable: function( a_Enable ) {
			if ( a_Enable )
            {
                this.editField.show();
                this.editField.width( this.valueField.width() - 8 );
                this.editField.val( this.element.context.options[ this.element.context.selectedIndex ].text );
                this.valueField.button( "option", "label", "" );
                this.editField.focus();
            }
            else
            {
                this.editField.hide();
            }
        },

        destroy: function() {
            this.element.show();
            this.replacement.detach();

            $.Widget.prototype.destroy.call( this );
        }

    });
})( jQuery );

$(document).click( function() {
    $(".combobox_list").hide();
});