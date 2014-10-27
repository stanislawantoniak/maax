/**
 * Created by pawelchyl on 16.09.2014.
 */

Mall.account = {
    _validate: null,

    init: function () {
        "use strict";

        this.attachForgotPasswordValidation();
        this.attachLoginValidation();
        this.attachResetPasswordValidation();

        //rma
        this.attachContactVendorRmaForm();
    },

    getValidate: function () {
        "use strict";

        if (this._validate === null) {
            this.setValidate(Mall.validate);
        }

        return this._validate;
    },

    setValidate: function (validate) {
        "use strict";

        this._validate = validate;

        return this;
    },

    /**
     * Attaches validation for login form.
     *
     * @returns {Mall.customer}
     */
    attachLoginValidation: function () {
        "use strict";

        if (jQuery("#login-form")) {

            jQuery("#login-form").validate(this.getValidate().getOptions({
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

            jQuery("#forgotpassword-form").validate(this.getValidate().getOptions({
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

            jQuery("#resetpassword-form").validate(this.getValidate().getOptions({
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
     *
     * @returns {Mall.account}
     */
    attachContactVendorRmaForm: function () {
        "use strict";

        jQuery(".rma-connect-vendor-pannel p").click(function () {
            var contactVendorForm = jQuery(".rma-connect-vendor .rma-connect-vendor-form");
            if(contactVendorForm.is(":hidden")){
                contactVendorForm.slideDown();
                jQuery(this).find("i.fa")
                    .removeClass("fa-chevron-down")
                    .addClass("fa-chevron-up");
            } else {
                contactVendorForm.slideUp();
                jQuery(this).find("i.fa")
                    .removeClass("fa-chevron-up")
                    .addClass("fa-chevron-down");
            }
        });
        if (jQuery("#rma-connect-vendor-form")) {
            jQuery("#rma-connect-vendor-form").validate();
        }


        return this;
    }
};

jQuery(document).ready(function () {
    "use strict";

    Mall.account.init();
});