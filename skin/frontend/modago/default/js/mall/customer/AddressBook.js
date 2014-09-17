/**
 * Created by pawelchyl on 16.09.2014.
 */

(function () {
    "use strict";

    Mall.customer.AddressBook = function () {

        this._book = [];

        this._customer = null;

		this._default_billing = null;

		this._default_shipping = null;

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
		
        setAddressBook: function (addresses) {
			var self = this;
			jQuery.each(addresses, function(){
				self._book.push(new Mall.customer.Address(this));
			});
            return this;
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
                deffered,
                self = this;
            // check if object exists
            if (! this.getIsAddressExists(obj[this.ENTITY_ID_KEY])) {
                return [null, null];
            }

            address = this.get(obj[this.ENTITY_ID_KEY]);
            this.beforeEdit(address);
            deffered = address.setData(obj).save();
            deffered.always(function () {
                self.afterEdit(deffered, address);
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

		_setDefault: function(address, type){
			if(typeof address === "object"){
				address = address.getId();
			}
            this["_default_" + type] = address;

            return this;
		},
		
		_getDefault: function(type){
			if(this["_default_" + type]){
				return this.get(this["_default_" + type]);
			}
			return null;
		},
		
        setDefaultShipping: function (address) {
			return this._setDefault(address, "shipping");
        },
		
        setDefaultBilling: function (address) {
			return this._setDefault(address, "billing");
        },

        getDefaultBilling: function () {
           return this._getDefault("billing");
        },
		
        getDefaultShipping: function () {
           return this._getDefault("shipping");
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
            jQuery.each(this.getAddressBook(), function (idx, item) {
                if (item.getId() !== address.getId()) {
                    item.setUnselectShipping();
                }
            });
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

        },

        afterAdd: function (deffered, address) {

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