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
	    this.attachEditCustomerValidation();
	    this.attachEditPasswordValidation();
	    this.attachTooltips();

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
		var loginForm = jQuery("#login-form");
        if (loginForm.length) {

            loginForm.validate(this.getValidate().getOptions({
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
	    var forgotPasswordForm = jQuery("#forgotpassword-form");

        if (forgotPasswordForm.length) {
            forgotPasswordForm.validate(this.getValidate().getOptions({
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

	attachEditCustomerValidation: function() {
		"use strict";

		var editCustomerForm = jQuery("#editCustomer-form");

		if(editCustomerForm.length) {
			addAccountEditTranslations();
			editCustomerForm.validate(this.getValidate().getOptions({
				rules: {
					email: {
						required: true,
						email: true
					},
					phone: {
						required: true,
						"validate-telephone": true
					},
					firstname: {
						required: true
					},
					lastname: {
						required: true
					}
				},
				messages: {
					email: {
						required: Mall.translate.__("Please enter email."),
						email: Mall.translate.__("Please enter correct email.")
					},
					phone: {
						required: Mall.translate.__("Please enter phone number."),
						"validate-telephone": Mall.translate.__("Telephone number is too short. Number must contain 9 digits, without spacing.")
					},
					firstname: {
						required: Mall.translate.__("This field is required")
					},
					lastname: {
						required: Mall.translate.__("This field is required")
					}
				}
			}));
		}

		return this;
	},

	attachEditPasswordValidation: function() {
		"use strict";

		var editPasswordForm = jQuery("#editPassword-form");

		if(editPasswordForm.length) {
			addPasswordEditTranslations();
			editPasswordForm.validate(this.getValidate().getOptions({
				rules: {
					current_password: {
						required: true
					},
					password: {
						required: true,
						minlength: 6
					},
					confirmation: {
						required: true,
						minlength: 6,
						equalTo: '#customer_password'
					}
				},
				messages: {
					current_password: {
						required: Mall.translate.__("Please enter current password.")
					},
					password: {
						required: Mall.translate.__("Please enter new password."),
						minlength: Mall.translate.__("Password needs to have at least 6 characters")
					},
					confirmation: {
						required: Mall.translate.__("Please repeat new password."),
						minlength: Mall.translate.__("Password needs to have at least 6 characters"),
						equalTo: Mall.translate.__("Passwords must match")
					}
				}
			}));
		}

		return this;
	},

	attachTooltips: function() {
		jQuery('input[type=text].hint,input[type=email].hint,input[type=password].hint,textarea.hint').tooltip({
			placement: function(a, element) {
				var viewport = window.innerWidth;
				var placement = "right";
				if (viewport < 600) {
					placement = "bottom";
				}
				return placement;
			},
			html: true,
			trigger: "focus"
		});
		jQuery('input[type=text].hint,input[type=email].hint,input[type=password].hint,textarea.hint').off('shown.bs.tooltip').on('shown.bs.tooltip', function () {
			if(jQuery(this).parent(':has(i)').length && jQuery(this).parent().find('i').is(":visible")) {
				jQuery(this).next('div.tooltip.right').animate({left: "+=25"}, 100, function () {
				});
			}
		});
	},

    /**
     * Attaches validation for reset password form.
     *
     * @returns {Mall.customer}
     */
    attachResetPasswordValidation: function () {
        "use strict";
		var resetPasswordForm = jQuery("#resetpassword-form");
        if (resetPasswordForm.length) {

            resetPasswordForm.validate(this.getValidate().getOptions({
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