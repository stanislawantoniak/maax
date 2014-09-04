/**
 * Created by pawelchyl on 03.09.2014.
 */


/**
 * Javascript object for customer area in Mall.
 */
Mall.customer = {
    /**
     * Default validation options.
     */
    _default_validation_options: {
        success: "valid",
        focusInvalid: false,
        errorElement: "span",
        onfocusout: function (element) {
            jQuery(element).valid();
        },
        onsubmit: true,

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
            jQuery(element).closest("div").find('#pass-error').remove();

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
    },

    /**
     * Setup for customer area.
     */
    init: function () {
        "use strict";

        this.attachLoginValidation();
        this.attachForgotPasswordValidation();
        this.attachResetPasswordValidation();
    },

    /**
     * Attaches validation for login form.
     *
     * @returns {Mall.customer}
     */
    attachLoginValidation: function () {
        "use strict";
        
        if (jQuery("#login-form")) {

            jQuery("#login-form").validate(this.getOptions({
                rules: {
                    "login[username]": {
                        required: true,
                        email: true
                    },
                    "login[password]": {
                        required: true
                    }
                }
            }));
        }

        return this;
    },

    /**
     * Attaches validation for forgotten password form.
     *
     * @returns {Mall.customer}
     */
    attachForgotPasswordValidation: function () {
        "use strict";

        if (jQuery("#forgotpassword-form")) {

            jQuery("#forgotpassword-form").validate(this.getOptions({
                rules: {
                    "email": {
                        required: true,
                        email: true
                    }
                }
            }));
        }

        return this;
    },

    /**
     * Attaches validation for reset password form.
     *
     * @returns {Mall.customer}
     */
    attachResetPasswordValidation: function () {
        "use strict";

        if (jQuery("#resetpassword-form")) {

            jQuery("#resetpassword-form").validate(this.getOptions({
                rules: {
                    "password": {
                        required: true
                    },
                    confirmation: {
                        required: true,
                        equalTo: "#password"
                    }
                }
            }));
        }

        return this;
    },

    /**
     * Return default validation object for customer area.
     *
     * @returns {*}
     */
    getDefaultValidationOptions: function () {
        "use strict";

        return this._default_validation_options;
    },

    /**
     * Merges given options object with default options object.
     *
     * @param options
     * @returns {*}
     */
    getOptions: function (options) {
        "use strict";

        var opts = jQuery.extend({}, this.getDefaultValidationOptions());

        return jQuery.extend(opts, options);
    }
};

jQuery(document).ready(function () {
    "use strict";
    Mall.customer.init();
});