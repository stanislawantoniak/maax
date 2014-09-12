
Mall.validate = {
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

        messages: { },

        highlight: function(element, errorClass, validClass) {
            var we = jQuery(element).innerWidth() + 25,
                el = jQuery(element).attr('type');

            if (jQuery(element).attr('id') === 'pass') {
                we -= 14;
            }

            jQuery(element).closest("div").addClass('has-error has-feedback')
                .removeClass('has-success');
            jQuery(element).closest("div").find('.form-ico-times').remove();

            jQuery(element).closest("div").not( ".form-checkbox" ).not( ".form-radio" )
                .append(
                    '<i style="left:'+we
                        +'px; right:auto" class="form-ico-times form-control-feedback "></i>');

            jQuery(element).closest("div").find('.form-ico-checked').remove();
        },

        unhighlight: function(element, errorClass, validClass) {
            var we = jQuery(element).innerWidth() + 25;
            if (jQuery(element).attr('id') === 'pass') {
                we -= 14;
            }
            jQuery(element).closest("div").removeClass('has-error')
                .addClass('has-success has-feedback');
            jQuery(element).closest("div").find('.form-ico-checked').remove();
            jQuery(element).closest("div").find('#pass-error').remove();

            jQuery(element).closest("div").append('<i style="left:'+
                we+'px; right:auto" class="form-ico-checked form-control-feedback"></i>');
            jQuery(element).closest("div").find('.form-ico-times').remove();
        },

        errorPlacement: function(error, element) {
            if (element.attr("type") === "checkbox" ){
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

            jQuery('html, body').animate({
                scrollTop: jQuery(validator.errorList[0].element).offset().top
                    - Mall.getMallHeaderHeight()
            }, 1000);
        }
    },

    init: function () {
        "use strict";

        // add customer methods
		jQuery.validator.addMethod('validate-postcode', function () {
				return Mall.validate.validators.postcode.apply(this, arguments);
		}, jQuery.validator.format("Kod pocztowy nie jest poprawny"));

        jQuery.validator.addMethod('validate-passwordbackend', function () {
            return Mall.validate.validators.passwordbackend.apply(this, arguments);
        }, jQuery.validator.format("Hasło musi być przynajmniej 5 literowe"));

        jQuery.validator.addMethod('validate-telephone', function () {
            return Mall.validate.validators.telephone.apply(this, arguments);
        }, jQuery.validator.format("Numer telefonu musi być dziewięciocyfrowy"));

        jQuery.validator.addMethod('validate-emailbackend', function () {
            return Mall.validate.validators.emailbackend.apply(this, arguments);
        }, jQuery.validator.format("Nie ma jeszcze takiego konta"));

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
});