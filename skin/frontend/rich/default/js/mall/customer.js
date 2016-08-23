/**
 * Created by pawelchyl on 03.09.2014.
 */


/**
 * Javascript object for customer area in Mall.
 */
Mall.customer = {

    _address_book: null,

    /**
     * Setup for customer area.
     */
    init: function () {
        "use strict";

        this._address_book = new Mall.customer.AddressBook();
        this._address_book.setCustomer(this);
    },

    getAddressBook: function () {
        "use strict";

        return this._address_book;
    }
};

jQuery(document).ready(function () {
    "use strict";
    Mall.customer.init();
});