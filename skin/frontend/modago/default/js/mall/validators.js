Mall.validate.validators = {

    emailbackend: function (value, elem, params) {
        "use strict";
        var respone;
        var baseUrl = window.location.protocol+"//"+window.location.host+"/";

        console.log('emailbackend start');
        jQuery.ajax({
            url: baseUrl + "customer/account/checkExistingAccount",
            data: {
                formkey: params.formkey,
                email: value
            },
            dataType: 'json',
            cache: false,
            async: false
        }).done(function(data){
            console.log('done');
//            console.log(data);
            respone = data;
        });

//        if(){
//
//        }


        console.log('emailbackend stop');
        return true;
    },

    telephone: function(value, elem, params) {
        "use strict";

        return (/^((\+)?[1-9]{1,2})?([-\s\.])?([0-9\-\ ]{9,12})$/.test(value));
    },

    postcode: function(value, elem, params){
        "use strict";
        var r = /^\d{2}-\d{3}$/.test(value);
        if(/^00-000$/.test(value)) {
            r = false;
        }
        return r;
    },

    passwordbackend: function(value, elem, params){
        "use strict";
        if(value.length >= params.minLength || value.length == 0) {
            return true;
        } else {
            return false;
        }
    }
};