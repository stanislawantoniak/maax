Mall.validate.validators = {


    email: function (value, elem, params) {
        return (/[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?/.test(value));
    },

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

        var respone = {status: false, content: ''},
            promise;

        if (!this.email(value, elem, params)) {
            return false;
        }

        if(params.form_key === undefined) {return false;}
        if(!params.url.length) {return false;}


        promise = jQuery.ajax({
            url: params.url,
            data: {
                form_key: params.form_key,
                email: value
            },
            dataType: 'json',
            cache: false,
            async: true,
            type: "POST"
        });

        return promise;
    },

    telephone: function(value, elem, params) {
        "use strict";

        return this.optional(elem) || (/^((\+)?[1-9]{1,2})?([-\s\.])?([0-9\-\ ]{9,12})$/.test(value));
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
        if(!params.hasOwnProperty('minLength')) {
            params = { minLength: 5};
        }

        if(value.length >= params.minLength || value.length == 0) {
            return true;
        } else {
            return false;
        }
    }
};