/**
 * Created by pawelchyl on 03.09.2014.
 */


Mall.customer = {
    attachLoginValidation: function () {
        "use strict";
        
        if (jQuery("#login-form")) {

            jQuery("#login-form").validate({
                success: "valid",
                focusInvalid: false,
                errorElement: "span",
                onfocusout: function (element) {
                    jQuery(element).valid();
                },
                onsubmit: true,
                rules: {
                    "login[username]": {
                        required: true,
                        email: true
                    },
                    "login[password]": {
                        required: true
                    }
                },

                messages: {

                },
                highlight: function(element, errorClass, validClass) {
                    var we = jQuery(element).innerWidth()+25;
                    if (jQuery(element).attr('id') === 'pass') {
                        we -= 14;
                    }
                    var el = jQuery(element).attr('type');
                    jQuery(element).closest("div").addClass('has-error has-feedback').removeClass('has-success');
                    jQuery(element).closest("div").find('.form-ico-times').remove();

                    jQuery(element).closest("div").not( ".form-checkbox" ).not( ".form-radio" ).append('<i style="left:'+we+'px; right:auto" class="form-ico-times form-control-feedback "></i>');

                    jQuery(element).closest("div").find('.form-ico-checked').remove();
                },
                unhighlight: function(element, errorClass, validClass) {
                    var we = jQuery(element).innerWidth()+25;
                    if (jQuery(element).attr('id') === 'pass') {
                        we -= 14;
                    }
                    jQuery(element).closest("div").removeClass('has-error').addClass('has-success has-feedback');
                    jQuery(element).closest("div").find('.form-ico-checked').remove();
                    //if (element.attr("type") != "checkbox"){

                    jQuery(element).closest("div").append('<i style="left:'+we+'px; right:auto" class="form-ico-checked form-control-feedback"></i>');
                    //}
                    jQuery(element).closest("div").find('.form-ico-times').remove();
                },
                errorPlacement: function(error, element) {
                    if (element.attr("type") == "checkbox" ){
                        jQuery(element).closest('div').append(error);
                        //error.prepend(element).hide().slideToggle(300);
                    } else if (element.attr("type") == "radio") {
                        jQuery(element).closest('div').append(error);
                    }else {
                        error.insertAfter(element);
                    }

                }
            });
        }
    },
    
    init: function () {
        "use strict";
        
        this.attachLoginValidation();
    }
};

jQuery(document).ready(function () {
    "use strict";
    Mall.customer.init();
});