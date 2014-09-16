/**
 * Created by pawelchyl on 16.09.2014.
 */

(function () {
    "use strict";

    Mall.customer.AddressBook = function () {

        this._book = {};

        this._customer = null;
    };

    Mall.customer.AddressBook.prototype = {
        setCustomer: function (customer) {
            this._customer = customer;

            return this;
        },

        getCustomer: function () {
            return this._customer;
        },

        getAddressBook: function () {
            return this._book;
        },

        add: function (data) {
            var _data = {
                id: 1
            };

            this._book[_data.id] = new Mall.customer.Address(_data);
        }
    };

})();