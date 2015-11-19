Mall.validate.validators = {

    config: {
        passwordMinLength: undefined
    },

    email: function (value, elem, params) {
        return (/[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?/i.test(value));
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

        if (!this.email(value, elem, params)) {
            return false;
        }

        if(params.form_key === undefined) {return false;}
        if(!params.url.length) {return false;}

        var promise = jQuery.ajax({
            url: params.url,
            data: {
                form_key: params.form_key,
                email: jQuery.trim(value)
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

        return this.optional(elem) || value.replace(/\D/g,"").length >= 9; //9 digits
    },

    postcode: function(value, elem, params){
        "use strict";
        value = value.replace(/\D/g, "");
        if (value.length > 5) { //if there is more then 5 digit
            return false;
        }
        if (value.length == 5) {
            if (value == "00000") {
                return false;
            }
            return true;
        } else {
            return false;
        }

    },

    postcodeWithReplace: function(value, elem, params){
        "use strict";
        var test = Mall.validate.validators.postcode(value, elem, params);
        value = value.replace(/\D/g, "");
        if (test) {
            var matched = value.match(/([0-9]{2})([0-9]{3})/);
            jQuery(elem).val(matched[1] + "-" + matched[2]);
        }
        return test;

    },
	

    passwordbackend: function(value, elem, params){
        "use strict";
        if(!params.hasOwnProperty('minLength')) {

            params =  { minLength : parseInt(Mall.validate.validators.config.passwordMinLength) };

            if(isNaN(params.minLength)) {
                return true;
            }
        }

        if(value.length >= params.minLength || value.length == 0) {
            return true;
        } else {
            return false;
        }
    },

    nip: function(value, elem, params){

        if (value.length == 0) {
            return true;
        }
        if (value.length < 10) {
            return this.optional(elem) | false;
        }
        if (value.length > 16) {
            return this.optional(elem) | false;
        }
        return true;
    },

    bankAccount: function(value, elem, params){
        value = value.replace(/\D/g, "");
        return this.optional(elem) || value.length == 26; //26 digits
    },

    bankAccountWithReplace: function(value, elem, params){
	    cutoutValue = value.match(/\d|-|\s/g, "");
	    if(cutoutValue && cutoutValue.length) {
		    value = cutoutValue.join("");
	    } else {
		    value = "";
	    }
        jQuery(elem).val(value);

        var test1 = this.optional(elem);
        var test2 = value.replace(/\D/g,"").length == 26; //26 digits

        return test1 || test2;

    }
};