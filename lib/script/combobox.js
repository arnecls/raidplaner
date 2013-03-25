(function( $ ) {
    $.widget( "ui.combobox", {
        options :  {
            editable : false,
            inlineStyle : {}
        },
        
        _create: function() {
		    var selectNode = this.element;
		    var selectedElement = this.element.children("option:selected").last();
            var selectedIndex = this.element.children("option").index(selectedElement);
            
            selectNode.context.selectedIndex = selectedIndex;
            
            var nodeId = selectNode.attr("id");
            var width  = selectNode.outerWidth(true);
            var height = Math.max(18, selectNode.height());
            
            var buttonWidth = 16;
            var valueWidth = width - buttonWidth - 11; // subtract padding
            var valueHeight = height; // subtract padding
            
            this.elementId = nodeId;
            this.editWidth = valueWidth - 4;
                        
            HTMLString  = "<span id=\"" + nodeId + "_combobox\" class=\"combobox\" style=\"width: "+width+"px; height: "+height+"px; font-size: "+(height-6)+"px\">";
            
            HTMLString += "<div id=\"" + nodeId + "_value\" style=\"width: "+valueWidth+"px; height: "+valueHeight+"px; cursor: pointer!important\" "+
                          "class=\"combobox_value ui-button ui-widget ui-state-default ui-button-disabled ui-state-disabled ui-button-text-only ui-corner-left\">";
            HTMLString += "</div>";
            
            HTMLString += "<div id=\"" + nodeId + "_open\" style=\"width: "+buttonWidth+"px; height: "+height+"px\" "+
                          "class=\"combobox_open ui-button ui-widget ui-state-default ui-corner-right ui-icon ui-icon-triangle-2-n-s\">";
            HTMLString += "</div>";
            
            HTMLString += "<div id=\"" + nodeId + "_options\" class=\"combobox_list ui-corner-all\"></div>";            
            HTMLString += "</span>";
            
            selectNode.after( HTMLString );
            selectNode.hide();
            
            this.editable( this.options.editable );
            
            // Copy over options and add style
            
            $("#" + nodeId + "_combobox").css(this.options.inlineStyle);
            
            selectNode.children("option").each( function() {
                $("#"+nodeId+ "_options")
                    .append("<div class=\"option\">" + $(this).text() + "</div>");
            
            });
            
            this.optionField = $("#"+nodeId+"_options");
            this.optionField.children(".option").eq(selectedIndex).addClass("selected");
            this.optionField.width(Math.max(this.optionField.width(),width+4));
            
            var maxAdditionalElements = 6;
            
            // On open
            
            $("#" + nodeId + "_open, #"+nodeId+"_value").click( function( event ) {
                var listWidth = $("#"+nodeId+"_options").width();
                
                // IE7 overlapping fix
                // All comoboboxes get z=0, current gets z=1
                {
                    $(".combobox").css("z-index", 0);
                    selectNode.next().css("z-index", 1);
                }
                
                $(".combobox_list").hide();
                
                var selectedElement = selectNode.children("option:selected").last();
                var selectedIndex = selectNode.children("option").index(selectedElement);
                var startIdx = selectedIndex;
                var endIdx = startIdx-maxAdditionalElements;
                
                var listOffset = 0;
                var listHeight = 0;
                var scrollOffset = 0;
                
                var options = $("#"+nodeId+"_options").children(".option");
                var option = options.eq(startIdx-1);
                
                // Calculate scroll/top offset and top height
                // n elements above current max.
                
                for ( var i=startIdx-1; i >= 0; --i )
                {                    
                    var elementHeight = option.outerHeight(true);
                    scrollOffset += elementHeight;
                    
                    if (i>endIdx)
                    {
                        listOffset += elementHeight;
                        listHeight += elementHeight;
                    }
                    
                    option = option.prev();
                }
                
                // Calculate bottom height
                // n elements below current max.
                
                endIdx = Math.min(startIdx+maxAdditionalElements, options.length);
                option = options.eq(startIdx);
                
                for ( var i=startIdx; i < endIdx; ++i )
                {
                    var elementHeight = option.outerHeight(true);
                    listHeight += elementHeight;
                    option = option.next();
                }
                
                // Show list

                var displayPosition  = $("#" + nodeId + "_value").offset();
                displayPosition.top -= listOffset;

                $("#" + nodeId + "_options")
                    .css( "top", -listOffset )
                    .height(listHeight)
                    .show()                    
                    .scrollTop( scrollOffset-listOffset );

                event.stopPropagation();
            });
            
            // on select
            
            this.optionField.children(".option").click( function() {
                $("#"+nodeId+"_options").children(".option").removeClass("selected");
                $(this).addClass("selected");

                selectNode.context.selectedIndex = $(this).index();
                selectNode.change();
                
                $("#"+nodeId+"_value").children("span").empty().append($(this).text());
                $("#"+nodeId+"_options").hide();
            });
        },

        editable: function( a_Enable ) {
            this.options.editable = a_Enable;
            var selectedElement = this.element.children("option:selected").last();
            var selectedValue = selectedElement.text();
            
			if ( a_Enable )
            {
                HTMLString = "<input id=\"" + this.elementId + "_edit\" class=\"combobox_edit\" style=\"width: "+this.editWidth+"px\" type=\"text\"/>";
                
                $("#"+this.elementId+"_value").empty().append(HTMLString);
                $("#"+this.elementId+"_edit").val( selectedValue ).focus()
                    .click(function(event) { event.stopPropagation(); });
            }
            else
            {
                HTMLString = "<span class=\"combobox_valuetext\">" + selectedValue + "</span>";
                $("#"+this.elementId+"_value").empty().append(HTMLString);
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