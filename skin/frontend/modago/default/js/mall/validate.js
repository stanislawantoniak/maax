
Mall.validate = {

    init: function () {
        "use strict";

//        this.validators.checkboxagreement1();
//        this.validators.checkboxagreement2();
//        this.validators.password();

        jQuery.validator.addMethod("password", this.validators.password, "Złe hasło");

        jQuery('#co-address').validate(Mall.customer.getOptions({
            rules: {
                'account[password]': {
                    "password": true
                }
            }
        }));
    }
};

jQuery(document).ready(function () {
    "use strict";
    Mall.validate.init();
});