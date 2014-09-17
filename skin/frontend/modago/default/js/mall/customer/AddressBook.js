/**
 * Created by pawelchyl on 16.09.2014.
 */

(function () {
    "use strict";

    Mall.customer.AddressBook = function () {

        this._book = [];

        this._customer = null;

        this.ENTITY_ID_KEY = "entity_id";
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

        _add: function (obj) {
            var deffered,
                address,
                error,
                self = this;

            address = new Mall.customer.Address(obj);
            this.beforeAdd(address);
            deffered = address.save();
            deffered.done(function (data) {
                if (Boolean(data.status) === false) {
                    error = true;
                }
            }).fail( function () {
                error = true;
            });

            if (!error) {
                this._book.push(address);
                deffered.always(function () {
                    self.afterAdd(deffered, address);
                });
            }

            return [deffered, address];
        },

        /**
         * @deprecated use Mall.customer.AddressBook.get instead.
         * @param id
         * @returns {*}
         */
        getAddress: function (id) {
            return this.get(id);
        },

        remove: function (id) {
            var deffered = null,
                error = false,
                self = this;

            if (!this.getIsAddressExists(id)) {
                return null;
            }

            this.beforeRemove(this.get(id));
            deffered = this.get(id).remove();
            deffered.done(function (data) {
                if (data.status === undefined || Boolean(data.status) === false) {
                    error = true;
                }
            }).fail(function () {
                error = true;
            });

            deffered.always(function () {
                self.afterRemove(deffered, self.get(id));
            });

            if (!error) {
                this._remove(id);
            }

            return deffered;

        },

        getIsAddressExists: function (id) {
            if (this.get(id) === null) {
                return false;
            }

            return true;
        },

        /**
         * Getter for address.
         *
         * @param id
         * @returns {Mall.customer.Address}
         */
        get: function (id) {
            var address = null;
            jQuery.each(this.getAddressBook(), function (idx, item) {
                if (item.getId() === id) {
                    address = item;
                    return true;
                }
            });

            return address;
        },

        _edit: function (obj) {
            var address,
                deffered;
            // check if object exists
            if (! this.getIsAddressExists(obj[this.ENTITY_ID_KEY])) {
                return [null, null];
            }

            address = this.get(obj[this.ENTITY_ID_KEY]);
            this.beforeEdit(address);
            deffered = address.setData(obj).save();
            deffered.done(function (done) {

            });

            return [deffered, address];
        },

        isRemoveable: function (id) {
            // @todo implement

            return true;
        },

        save: function (obj) {
            var id = obj[this.ENTITY_ID_KEY] === undefined ? null : obj[this.ENTITY_ID_KEY],
                deffered,
                address,
                _result,
                self = this;

            this.beforeSave(obj);
            if (id) {
                // perform update action
                _result = this._edit(obj);
            } else {
                // perform add action
                _result = this._add(obj);
            }

            deffered = _result[0];
            address = _result[1];

            deffered = deffered.always(function () {
                self.afterSave(deffered, address);
            });

            return deffered;
        },

        setDefaultShipping: function (address) {
            this.get(address.getId()).setDefaultShipping();
        },

        getDefaultShipping: function () {
            var defaultShipping = null;
            jQuery.each(this.getAddressBook(), function (idx, item) {
                if (item.getIsDefaultShippping()) {
                    defaultShipping = item;
                    return true;
                }
            });

            return defaultShipping;
        },

        setDefaultBilling: function (address) {
            this.get(address.getId()).setDefaultBilling();
        },

        getDefaultBilling: function () {
            var defaultBilling = null;
            jQuery.each(this.getAddressBook(), function (idx, item) {
                if (item.getIsDefaultBilling()) {
                    defaultBilling = item;
                    return true;
                }
            });

            return defaultBilling;
        },

        getSelectedShipping: function () {
            var selectedShipping = null;
            jQuery.each(this.getAddressBook(), function (idx, item) {
                if (item.getIsSelectedShippping()) {
                    selectedShipping = item;
                    return true;
                }
            });

            return selectedShipping;
        },

        setSelectedShipping: function (address) {
            this.get(address.getId()).setSelectedShipping();
        },

        getSelectedBilling: function () {
            var selectedBilling = null;
            jQuery.each(this.getAddressBook(), function (idx, item) {
                if (item.getIsSelectedBilling()) {
                    selectedBilling = item;
                    return true;
                }
            });

            return selectedBilling;
        },

        setSelectedBilling: function (address) {
            this.get(address.getId()).setSelectedBilling();
        },

        beforeAdd: function (address) {
            console.log("before add");
            console.log(arguments);
        },

        afterAdd: function (deffered, address) {
            console.log("after add");
            console.log(arguments);
        },

        beforeEdit: function (address) {

        },

        afterEdit: function (deffered, address) {

        },

        beforeSave: function (address) {
            console.log("before save");
            console.log(arguments);
        },

        afterSave: function (deffered, address) {
            console.log("after save");
            console.log(arguments);
        },

        beforeRemove: function (address) {

        },

        afterRemove: function (deffered, address) {

        },

        beforeDefaultShipping: function (address) {

        },

        afterDefaultShipping: function (address) {

        },

        beforeDefaultBilling: function (address) {

        },

        afterDefaultBilling: function (address) {

        },

        beforeSelectShipping: function (address) {

        },

        afterSelectShipping: function (address) {

        },

        beforeSelectBilling: function (address) {

        },

        afterSelectBilling: function (address) {

        },

        _remove: function (id) {
            var _id = null;
            jQuery.each(this.getAddressBook(), function (idx, item) {
                if (id === item.getId()) {
                    _id = idx;

                    return true;
                }
            });

            delete this._book[_id];

            return this;
        },

        /**
         * TEST FUNCTIONS
         */

        simulateSaveNewAddress: function (obj) {
            var result;

            if (obj === undefined) {
                obj = {
                    firstname: "Pawcio",
                    lastname: "Chyl",
                    company: "ORBA",
                    street: [
                        "Bukowińska 1",
                        "20-262"
                    ],
                    city: "Lublin",
                    country_id: "PL",
                    region_id: 487,
                    postcode: "20-262",
                    vat_id: "9462619603",
                    need_invoice: true,
                    telephone: "531 338 668"
                };
            }

            console.log(this.save(obj));
        }

    };

})();