"use strict";

jQuery(document).ready(function ($) {

    $("#add_slider").click(function(){
        var sliderNumber = $("[data-type=slider]").size();

        var template = '<div data-type=slider data-slidernumber=${sliderNumber}>' +
            '<div class="form-group">' +
            '<a id="remove_slider" href="" style="position: relative;top: 10px;left: 60px;"><span class="glyphicon glyphicon-remove-circle"></span></a>' +

            '<div class="row">' +
            '<label class="col-md-3 control-label"><label for="slider_image">Slider Desktop Image</label></label>' +
            '<div class="col-md-9"><input type="file" class="input-file" value="" name="slider[${sliderNumber}][image_desktop]"></div>' +
            '</div>' +
            '<div class="row">' +
            '<label class="col-md-3 control-label"><label for="slider_image">Slider Mobile Image</label></label>' +
            '<div class="col-md-9"><input type="file" class="input-file" value="" name="slider[${sliderNumber}][image_mobile]"></div>' +
            '</div>' +

            '<div data-type="url-container">' +
            '<div data-type="url-container-item">' +
            '<div class="row" >' +
            '<label class="col-md-3 control-label"><label for="slider_link_url">Slider Link Url 1 </label></label>' +
            '<div class="col-md-9"><input type="text" class="form-control input-text required-entry" value="" name="slider[${sliderNumber}][0][ulr]"></div>' +
            '</div>' +

            '<div class="row"><label class="col-md-3 control-label"><label for="slider_link_text">Slider Link Text 1 </label>' +
            '</label>' +
            '<div class="col-md-9"><input type="text" class="form-control input-text required-entry" value="" name="slider[${sliderNumber}][0][text]"></div>' +
            '</div>' +

            '</div>' +
            '<div data-type="url-container-item">' +
            '<div class="row" ><label class="col-md-3 control-label"><label for="slider_link_url">Slider Link Url 2 </label>' +
            '</label>' +
            '<div class="col-md-9"><input type="text" class="form-control input-text required-entry" value="" name="slider[${sliderNumber}][1][ulr]"></div>' +
            '</div>' +

            '<div class="row"><label class="col-md-3 control-label"><label for="slider_link_text">Slider Link Text 2 </label>' +
            '</label>' +
            '<div class="col-md-9"><input type="text" class="form-control input-text required-entry" value="" name="slider[${sliderNumber}][1][text]"></div>' +
            '</div>' +

            '</div>' +
            '</div>' +
            '</div>' +
            '</div>';
        var layout = $.tmpl(template, { "sliderNumber": sliderNumber });

        $(this).parents('.form-group').after(layout);
        return false;
    })
    $(document.body).on('click', '#remove_slider', function(e){
        e.preventDefault();
        $(this).parents('[data-type=slider]').remove();

    })

    $(document.body).on('click', '#remove_slider_link', function(e){
        e.preventDefault();
        $(this).parents('[data-type=url-container-item]').remove();
    })

});
