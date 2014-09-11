
Mall.validate = {

    init: function () {
        "use strict";
        console.log('coko');
        this.validators.checkboxagreement1();
        this.validators.checkboxagreement2();
        this.validators.password();
    }
};

jQuery(document).ready(function () {
    "use strict";
    Mall.validate.init();
});