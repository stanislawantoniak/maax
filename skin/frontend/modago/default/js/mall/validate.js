
Mall.validate = {
    /**
     * Default validation options.
     */
    _default_validation_options: {
        success: "valid",
        focusInvalid: false,
		ignoreTitle: true,
        errorElement: "span",

        onfocusout: function (element) {
            jQuery(element).valid();
        },

        onsubmit: true,

        messages: { },

        highlight: function(element, errorClass, validClass) {
            var we = jQuery(element).actual( 'innerWidth' ) + 25,
                el = jQuery(element).attr('type');

            if (jQuery(element).attr('id') === 'pass') {
                we -= 14;
            }


            jQuery(element).closest("div").addClass('has-error has-feedback')
                .removeClass('has-success');
            jQuery(element).closest("div").find('.form-ico-times').remove();


            if(jQuery(element).attr('name') !== 'payment[method]' && jQuery(element).attr('name') !== 'payment[additional_information][provider]'){
                jQuery(element).closest("div").not( ".form-checkbox" ).not( ".form-radio" )
                    .append(
                        '<i style="left:'+we
                            +'px; right:auto" class="form-ico-times form-control-feedback "></i>');
            }


            jQuery(element).closest("div").find('.form-ico-checked').remove();
        },

        unhighlight: function(element, errorClass, validClass) {
            var we = jQuery(element).actual( 'innerWidth' ) + 25;
            if (jQuery(element).attr('id') === 'pass') {
                we -= 14;
            }


            jQuery(element).closest("div").removeClass('has-error')
                .addClass('has-success has-feedback');
            jQuery(element).closest("div").find('.form-ico-checked').remove();
            jQuery(element).closest("div").find('#pass-error').remove();

            if (jQuery(element).prop("type") === "checkbox") {
                we = jQuery(element).closest("div").find("label").actual( 'innerWidth' ) - 10;
            }
            if(jQuery(element).attr('name') !== 'payment[method]' && jQuery(element).attr('name') !== 'payment[additional_information][provider]'){
                jQuery(element).closest("div").append('<i style="left:'+
                    we+'px; right:auto" class="form-ico-checked form-control-feedback"></i>');
            }

            jQuery(element).closest("div").find('.form-ico-times').remove();
        },

        errorPlacement: function(error, element) {
            if (element.attr("type") === "checkbox" ){
                jQuery(element).closest("div").find("span.error").remove();
                jQuery(element).closest('div').append(error);
            } else if (element.attr("type") === "radio") {
                jQuery(element).closest('div').append(error);
            } else {
                error.insertAfter(element);
            }

        },

        invalidHandler: function (form, validator) {
            "use strict";

            if (!validator.numberOfInvalids()) {
                return true;
            }

			var modal = jQuery('.modal:visible');
			var scrollTo = modal.length
							? jQuery(validator.errorList[0].element).offset().top - modal.find('.modal-body').offset().top
							: jQuery(validator.errorList[0].element).offset().top - Mall.getMallHeaderHeight(),
				scrollMe = modal.length ? modal : jQuery('html, body');

            scrollMe.animate({
                scrollTop: scrollTo
            }, "slow");
        }
    },

    init: function () {
        "use strict";

        // add customer methods
		jQuery.validator.addMethod('validate-postcode', function () {
				return Mall.validate.validators.postcode.apply(this, arguments);
		}, jQuery.validator.format(Mall.translate.__("Invalid zip-cod. Zip-code should include 5 numbers in XX-XXX format.")));

        jQuery.validator.addMethod('validate-postcodeWithReplace', function () {
            return Mall.validate.validators.postcodeWithReplace.apply(this, arguments);
        }, jQuery.validator.format(Mall.translate.__("Invalid zip-cod. Zip-code should include 5 numbers in XX-XXX format.")));

        jQuery.validator.addMethod('validate-passwordbackend', function () {
            return Mall.validate.validators.passwordbackend.apply(this, arguments);
        }, jQuery.validator.format(Mall.translate.__("Password needs to have at least %s characters")));

        jQuery.validator.addMethod('validate-telephone', function () {
            return Mall.validate.validators.telephone.apply(this, arguments);
        }, jQuery.validator.format(Mall.translate.__("Telephone number is too short. Number must contain 9 digits, without spacing.")));

        jQuery.validator.addMethod('validate-emailbackend', function () {
            return Mall.validate.validators.emailbackend.apply(this, arguments);
        }, jQuery.validator.format(Mall.translate.__("We already have an account with this address. Please <a href='customer/account/login/'>log in</a> to your account.")));

        jQuery.validator.addMethod('validate-nip', function () {
            return Mall.validate.validators.nip.apply(this, arguments);
        }, jQuery.validator.format(Mall.translate.__("Tax numer is incorrect. Enter as a string of digits e.g. 1234567890.")));

        jQuery.validator.addMethod('validate-bankAccount', function () {
            return Mall.validate.validators.bankAccount.apply(this, arguments);
        }, jQuery.validator.format(Mall.translate.__("Bank account number must contain 26 digits.")));

        jQuery.validator.addMethod('validate-bankAccountWithReplace', function () {
            return Mall.validate.validators.bankAccountWithReplace.apply(this, arguments);
        }, jQuery.validator.format(Mall.translate.__("Bank account number must contain 26 digits.")));
        /*
        override default jquery validator because it can pass email like : name@host
         */
        jQuery.validator.addMethod('email', function () {
            return Mall.validate.validators.email.apply(this, arguments);
        }, jQuery.validator.format(Mall.translate.__("email", "Please enter a valid email address.")));
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
    Mall.validate.init();

    jQuery( window ).resize(function() {

        jQuery('.has-error input').each(function() {
            Mall.validate._default_validation_options.highlight(jQuery(this));
        });
        jQuery('.has-success input').each(function() {
            Mall.validate._default_validation_options.unhighlight(jQuery(this));
        });

    });

});