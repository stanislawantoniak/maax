Mall.validate.validators = {

    /**
     *
     * @param value
     * @param elem
     * @param params
     * param.url (required) example : http://.../ + checkout/singlepage/checkExistingAccount
     * param.form_key (required)
     * @returns {boolean}
     */
    emailbackend: function (value, elem, params) {
        "use strict";
        if(value.length < 3) { return false;}
        if(params.form_key === undefined) {return false;}
        if(!params.url.length) {return false;}

        var respone = {status: false, content: ''};

        jQuery.ajax({
            url: params.url,
            data: {
                form_key: params.form_key,
                email: value
            },
            dataType: 'json',
            cache: false,
            async: false,
            type: "POST"
        }).done(function(data){
            respone = data;
        });

        if(respone.status){
            return true;
        } else {
            return false;
        }
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