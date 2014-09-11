Mall.validate.validators = {
    emailbackend: function (params) {
        "use strict";
        return true;
    },
    phone_numbermber: function(params) {
        "use strict";
        return true;
    },
    zipcode: function(params){
        "use strict";
        return true;
    },
    password: function(){
        "use strict";

        jQuery('#co-address').validate(Mall.customer.getOptions({
            rules: {
                "account[firstname]": {
                    minlength: 4
                }
            }
        }));

        return this;
    },
    checkboxagreement1: function(){
        "use strict";

        jQuery('#co-address').validate(Mall.customer.getOptions({
            debug: true,
            rules: {
                "agreement[1]": {
                    required: true
                }
            }
        }));

        return this;
    },
    checkboxagreement2: function(){
        "use strict";

        jQuery('#co-address').validate(Mall.customer.getOptions({
            debug: true,
            rules: {
                "agreement[2]": {
                    required: true
                }
            }
        }));

        return this;
    }

};