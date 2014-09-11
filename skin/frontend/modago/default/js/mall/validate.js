
Mall.validate = {

    init: function () {
        "use strict";

        jQuery.validator.addMethod("password", this.validators.password, "Hasło musi być przynajmniej 5 literowe");
        jQuery.validator.addMethod("checkboxagreement1", this.validators.checkboxagreement1, "To pole musi być zaznaczone");

        jQuery('#co-address').validate(Mall.customer.getOptions({
            rules: {
                'account[password]': {
                    "password": {
                        minLength: 5
                    }
                },
                'agreement[1]': {
                    required: true
                }
            }
        }));
    }
};

jQuery(document).ready(function () {
    "use strict";
    Mall.validate.init();
});