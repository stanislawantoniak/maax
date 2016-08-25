
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
            var isSelect2 = jQuery(element).hasClass("select-box-it-select");
            var elem = isSelect2 ? jQuery("#s2id_" + jQuery(element).attr("id")) : jQuery(element),
                we = elem.actual( 'innerWidth' ) + 25,
                target = isSelect2 ? elem.parent() : elem.attr('name') === 'stars' ? elem.parent() : elem.closest("div");

            if (elem.attr('id') === 'pass'
                || elem.is("textarea:not(#question_text-mobile)")
                    // gdzies moze byc uzyty textarea, aby zachowac zgodność wsteczną robię brzydki fix
                    // fix wykorzystany na stronie poduktu, mobilna sekcja zadaj pytanie sprzedawcy
                || elem.hasClass("closer-valid-ico")
                || isSelect2
            ) {
                we -= 14;
            } else if(elem.attr('name') === 'stars') {
	            we = 190;
            }

            target
                .addClass('has-error has-feedback')
                .removeClass('has-success')
                .find('.form-ico-times').remove();

            if(elem.attr('name') !== 'payment[method]'
                && elem.attr('name') !== 'payment[additional_information][provider]'
            && elem.attr('name') !== '_shipping_method')
            {
                target
                    .not( ".form-checkbox" )
                    .not( ".form-radio" )
                    .append('<i style="left:'+we+'px; right:auto" class="form-ico-times form-control-feedback "></i>');
            }

            var parentFormGroup = elem.parents(".form-group");
            if(parentFormGroup.hasClass("agreement-container")){
                we = parentFormGroup.actual( 'innerWidth' ) + 10;
                parentFormGroup
                    .append('<i style="left:'+we+'px; right:auto" class="form-ico-times form-control-feedback "></i>')
                ;
            }

            target.find('.form-ico-checked').remove();
        },

        unhighlight: function(element, errorClass, validClass) {
            var isSelect2 = jQuery(element).hasClass("select-box-it-select");

            var elem = isSelect2 ? jQuery("#s2id_" + jQuery(element).attr("id")) : jQuery(element),
                we = elem.innerWidth() + 25,
	            target = isSelect2 ? elem.parent() : elem.attr('name') === 'stars' ? elem.parent() : elem.closest("div"),
	            top = '';



            if (elem.attr('id') === 'pass'
                || elem.is("textarea:not(#question_text-mobile)")
                // gdzies moze byc uzyty textarea, aby zachowac zgodność wsteczną robię brzydki fix
                // fix wykorzystany na stronie poduktu, mobilna sekcja zadaj pytanie sprzedawcy
                || elem.hasClass("closer-valid-ico")
                || isSelect2
            ) {
                we -= 14;
            } else if(elem.attr('name') === 'stars') {
	            we = 135;
	            top = 'margin-top:-25px'
            }

            target
                .addClass('has-success has-feedback')
                .removeClass('has-error')
                .find('.form-ico-checked').remove();

            target.find('#pass-error').remove();

            if(elem.attr('name') !== 'payment[method]'
                && elem.attr('name') !== 'payment[additional_information][provider]'
                && elem.attr('name') !== '_shipping_method')
            {
                target
                    .not( ".form-checkbox" )
                    .not( ".form-radio" )
                    .append('<i style="left:'+we+'px; right:auto;'+top+'" class="form-ico-checked form-control-feedback"></i>');
            }

            var parentFormGroup = elem.parents(".form-group");
            if(parentFormGroup.hasClass("agreement-container")){
                we = parentFormGroup.actual( 'innerWidth' ) + 10;
                parentFormGroup
                    .append('<i style="left:'+we+'px; right:auto;'+top+'" class="form-ico-checked form-control-feedback"></i>')
                ;
            }

            target.find('.form-ico-times').remove();
        },

        errorPlacement: function(error, element) {
            if (element.attr("type") === "checkbox" ){
                jQuery(element).closest("div").find("span.error").remove();
                jQuery(element).closest('div').append(error);
            } else if (element.attr("type") === "radio") {
                jQuery(element).closest('div').append(error);
            }else if (jQuery(element).is("textarea")) {
                jQuery(element).closest("div").find("span.error").remove();
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
							: jQuery(validator.errorList[0].element).offset().top - Mall.getMallHeaderHeight() - 25,
				scrollMe = modal.length ? modal : jQuery('html, body');

	        if(validator.errorList[0].element.id === 'stars') {
				scrollTo = jQuery('#stars').parent().offset().top - Mall.getMallHeaderHeight() - 35;
	        }

            scrollMe.stop().animate({
                scrollTop: scrollTo
            }, "slow");
        }
    },

    init: function () {
        "use strict";

        // add customer methods
		jQuery.validator.addMethod('validate-postcode', function () {
				return Mall.validate.validators.postcode.apply(this, arguments);
		}, jQuery.validator.format(Mall.translate.__("Invalid zip-code. Zip-code should include 5 numbers in XX-XXX format.")));

        jQuery.validator.addMethod('validate-postcodeWithReplace', function () {
            return Mall.validate.validators.postcodeWithReplace.apply(this, arguments);
        }, jQuery.validator.format(Mall.translate.__("Invalid zip-code. Zip-code should include 5 numbers in XX-XXX format.")));

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
        }, jQuery.validator.format(Mall.translate.__("Tax number is incorrect. Enter as a string of digits e.g. 1234567890.")));

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

        jQuery('.has-error input, .has-error textarea').each(function() {
            Mall.validate._default_validation_options.highlight(jQuery(this));
        });
        jQuery('.has-success input, .has-success textarea').each(function() {
            Mall.validate._default_validation_options.unhighlight(jQuery(this));
        });

    });

});