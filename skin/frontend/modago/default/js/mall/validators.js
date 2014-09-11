Mall.validate.validators = {
    emailbackend: function (params) {
        "use strict";
        //pamietac o params : form key
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
    password: function(value, elem, params){
        "use strict";
        if(value.length >= params.minLength || value.length == 0) {
            return true;
        } else {
            return false;
        }
    }
};