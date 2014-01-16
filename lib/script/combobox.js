(function( $ ) {
    $.widget( "ui.combobox", {
        options :  {
            editable : false,
            darkBackground : false,
            inlineStyle : {},
            icons : {}
        },

        _create: function() {
            var SelectNode = this.element;
            var SelectedElement = this.element.children("option:selected").last();
            var SelectedIndex = this.element.children("option").index(SelectedElement);

            SelectNode.context.selectedIndex = SelectedIndex;

            var NodeId = SelectNode.attr("id");
            var Width  = SelectNode.outerWidth(true);
            var Height = Math.max(20, SelectNode.height());

            var ButtonWidth = 16;
            var MaxAdditionalElements = 6;
            
            var ValueWidth = Width - ButtonWidth - 11; // subtract padding

            this.elementId = NodeId;
            this.editWidth = ValueWidth - 4;

            var ValueClassName = (this.options.darkBackground) ? "combobox_value_bright" : "combobox_value_dark";

            HTMLString  = "<span id=\"" + NodeId + "_combobox\" class=\"combobox\" style=\"width: "+Width+"px; height: "+Height+"px\">";

            HTMLString += "<div id=\"" + NodeId + "_value\" style=\"width: "+ValueWidth+"px; line-height: "+Height+"px\" class=\"combobox_value "+ValueClassName+" clickable ui-button ui-widget ui-state-default ui-button-disabled ui-state-disabled ui-button-text-only ui-corner-left\">";
            HTMLString += "</div>";

            HTMLString += "<div id=\"" + NodeId + "_open\" class=\"combobox_open ui-button ui-widget ui-state-default ui-corner-right\">"
            HTMLString += "<span class=\"ui-icon ui-icon-triangle-2-n-s\" style=\"margin-top: 1px\"></span>";
            HTMLString += "</div>";

            HTMLString += "<div id=\"" + NodeId + "_options\" class=\"combobox_list ui-corner-all\"></div>";
            HTMLString += "</span>";

            SelectNode.after( HTMLString );
            SelectNode.hide();

            this.editable( this.options.editable );
            var Icons = this.options.icons;

            // Copy over Options and add style

            $("#" + NodeId + "_combobox").css(this.options.inlineStyle);

            SelectNode.children("option").each( function() {

                if ($(this).val() == "-")
                {
                    $("#"+NodeId+ "_options").append("<div class=\"separator\"></div>");
                }
                else
                {
                    if ( Icons[$(this).val()] != undefined )
                        $("#"+NodeId+ "_options").append("<div class=\"option\"><img src=\"" + Icons[$(this).val()] + "\"/><div class=\"text\">" + $(this).text() + "</div></div>");
                    else
                        $("#"+NodeId+ "_options").append("<div class=\"option\"><div class=\"text\">" + $(this).text() + "</div></div>");
                }
            });

            this.optionField = $("#"+NodeId+"_options");
            this.optionField.children(".option, .separator").eq(SelectedIndex).addClass("selected");
            
            // On open

            $("#" + NodeId + "_open, #"+NodeId+"_value").click( function( aEvent ) {
                $(".combobox_list").hide();

                var SelectedElement = SelectNode.children("option:selected").last();
                var SelectedIndex = SelectNode.children("option").index(SelectedElement);
                var StartIdx = SelectedIndex;
                var EndIdx = StartIdx-MaxAdditionalElements;

                var ListOffset = 0;
                var ListHeight = 0;
                var ScrollOffset = 0;

                var OptionList = $("#" + NodeId + "_options");
                var OptionFields = OptionList.children(".option, .separator");
                var Option = OptionFields.eq(StartIdx-1);

                // Calculate scroll/top offset and top height
                // n elements above current max.

                for ( var i=StartIdx-1; i >= 0; --i )
                {

                    var ElementHeight = Option.outerHeight(true);
                    ScrollOffset += ElementHeight;

                    if (i>EndIdx)
                    {
                        ListOffset += ElementHeight;
                        ListHeight += ElementHeight;
                    }

                    Option = Option.prev();
                }

                // Calculate bottom height
                // n elements below current max.

                EndIdx = Math.min(StartIdx+MaxAdditionalElements, OptionFields.length);
                Option = OptionFields.eq(StartIdx);

                for ( i=StartIdx; i < EndIdx; ++i )
                {
                    var ElementHeight = Option.outerHeight(true);
                    ListHeight += ElementHeight;
                    Option = Option.next();
                }

                // Show list
                
                OptionList
                    .css( "top", -ListOffset )
                    .height(ListHeight)
                    .show()
                    .scrollTop( ScrollOffset-ListOffset );
                
                // Adjust width
                    
                var maxWidth = $("#" + NodeId + "_combobox").width();                
                OptionFields.each( function() {
                    maxWidth = Math.max($(this).outerWidth(), maxWidth);
                });
                
                OptionList.width(maxWidth);
                
                // Stop events
                
                aEvent.stopPropagation();
            });

            // on select

            var Widget = this;

            this.optionField.children(".option").click( function() {
                $("#"+NodeId+"_options > .option").removeClass("selected");
                $(this).addClass("selected");

                SelectNode.context.selectedIndex = $(this).index();
                SelectNode.change();

                var ValueField = $("#"+NodeId+"_value:first");
                var OptionValue = SelectNode.val();

                if ( Icons[OptionValue] != undefined )
                {
                    ValueField.empty().append("<img class=\"combobox_valueimg\" src=\"" + Icons[OptionValue] + "\"/><div class=\"combobox_valuetext\">" + $(this).text() + "</div>");
                }
                else if (Widget.options.editable)
                {
                    ValueField.children("input").val($(this).text()).focus();
                }
                else
                {
                    ValueField.empty().append("<div class=\"combobox_valuetext\">" + $(this).text() + "</div>");
                }

                $("#"+NodeId+"_options").hide();
            });
        },

        editable: function( aEnable ) {
            this.options.editable = aEnable;
            var SelectedElement = this.element.children("option:selected").last();
            var SelectedValue = SelectedElement.text();

            if ( aEnable )
            {
                HTMLString = "<input id=\"" + this.elementId + "_edit\" class=\"combobox_edit\" style=\"width: "+this.editWidth+"px\" type=\"text\"/>";

                $("#"+this.elementId+"_value").empty().append(HTMLString);
                $("#"+this.elementId+"_edit").val( SelectedValue ).focus()
                    .click(function(aEvent) { aEvent.stopPropagation(); });
            }
            else
            {
                ValueField = $("#"+this.elementId+"_value").empty();

                if ( this.options.icons[SelectedElement.val()] != undefined )
                    ValueField.append("<img class=\"combobox_valueimg\" src=\"" + this.options.icons[SelectedElement.val()] + "\"/><div class=\"combobox_valuetext\">" + SelectedValue + "</div>");
                else
                    ValueField.append("<div class=\"combobox_valuetext\">" + SelectedValue + "</div>");
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