;(function(window, $) {
    $(function() {
        $('#mf_general_display').live('change', function() {
            if ($('#mf_general_display').is(':checked')) {
                $('#tabs_colors').parent().show();
            } else {
                $('#tabs_colors').parent().hide();
            }
        });
        $('#mf_colors_header_image_width').live('change', function() {
            $('td.c-color div, td.c-normal_image div, td.c-selected_image div, td.c-normal_hovered_image div, td.c-selected_hovered_image div, ' +
                '#image_mf_colors_header_image_normal, #image_mf_colors_header_image_selected, ' +
                '#image_mf_colors_header_image_normal_hovered, #image_mf_colors_header_image_selected_hovered')
                .css({'width': $(this).val() + 'px'});
        });
        $('#mf_colors_header_image_height').live('change', function() {
            $('td.c-color div, td.c-normal_image div, td.c-selected_image div, td.c-normal_hovered_image div, td.c-selected_hovered_image div, ' +
                '#image_mf_colors_header_image_normal, #image_mf_colors_header_image_selected, ' +
                '#image_mf_colors_header_image_normal_hovered, #image_mf_colors_header_image_selected_hovered')
                .css({'height': $(this).val() + 'px'});
        });
        $('#mf_colors_header_image_border_radius').live('change', function() {
            $('td.c-color div, td.c-normal_image div, td.c-selected_image div, td.c-normal_hovered_image div, td.c-selected_hovered_image div, ' +
                '#image_mf_colors_header_image_normal, #image_mf_colors_header_image_selected, ' +
                '#image_mf_colors_header_image_normal_hovered, #image_mf_colors_header_image_selected_hovered')
                .css({
                    '-webkit-border-radius': $(this).val() + 'px',
                    '-moz-border-radius': $(this).val() + 'px',
                    'border-radius': $(this).val() + 'px'
                });
        });
        $('#mf_colors_header_state_width').live('change', function() {
            $('td.c-state_image div, #image_mf_colors_header_state_image')
                .css({'width': $(this).val() + 'px'});
        });
        $('#mf_colors_header_state_height').live('change', function() {
            $('td.c-state_image div, #image_mf_colors_header_state_image')
                .css({'height': $(this).val() + 'px'});
        });
        $('#mf_colors_header_state_border_radius').live('change', function() {
            $('td.c-state_image div, #image_mf_colors_header_state_image')
                .css({
                    '-webkit-border-radius': $(this).val() + 'px',
                    '-moz-border-radius': $(this).val() + 'px',
                    'border-radius': $(this).val() + 'px'
                });
        });
    });
})(window, jQuery);
