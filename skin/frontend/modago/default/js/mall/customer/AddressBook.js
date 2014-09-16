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
                error;

            address = new Mall.customer.Address(obj);
            this.beforeAdd(address);
            deffered = address.save();
            deffered = deffered.done(function (data) {
                if (Boolean(data.status) === false) {
                    error = true;
                }
            }).fail( function () {
                error = true;
            });

            if (!error) {
                this._book.push(address);
            }

            this.afterAdd(deffered, address);

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
                error = false;

            if (!this.getIsAddressExists(id)) {
                return null;
            }

            this.beforeRemove(this.get(id));
            deffered = this.get(id).remove();
            deffered = deffered.done(function (data) {
                if (data.status === undefined || Boolean(data.status) === false) {
                    error = true;
                }
            }).fail(function () {
                error = true;
            });
            if (!error) {
                this.afterRemove(deffered, this.get(id));
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
            deffered = address.setData(obj).save();

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
                _result;

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
            this.afterSave(deffered, address);
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
        },

        afterAdd: function (deffered, address) {
            console.log("after add");
        },

        beforeEdit: function (address) {

        },

        afterEdit: function (deffered, address) {

        },

        beforeSave: function (address) {

        },

        afterSave: function (deffered, address) {

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
        }

    };

})();