/**
 * Created by pawelchyl on 16.09.2014.
 */

(function () {
    "use strict";
	if(typeof Mall.customer != "object"){
		Mall.customer = {};
	}
    Mall.customer.AddressBook = function () {

        /**
         * Addressbook
         *
         * @type {Array}
         * @private
         */
        this._book = [];

        /**
         * Customer instancef
         *
         * @type {null}
         * @private
         */
        this._customer = null;
		
        /**
         * Is invoice needed
         *
         * @type {null}
         * @private
         */
		this._needInvoice = false;

        /**
         * Default billing address id.
         *
         * @type {null}
         * @private
         */
		this._default_billing = null;

        /**
         * Default shipping address id.
         *
         * @type {null}
         * @private
         */
		this._default_shipping = null;

        /**
         * Data key for ID.
         *
         * @type {string}
         */
        this.ENTITY_ID_KEY = "entity_id";
    };

    Mall.customer.AddressBook.prototype = {

        getEntityIdKey: function () {
            return this.ENTITY_ID_KEY;
        },
        /**
         * Sets customer instance.
         *
         * @param {Mall.customer} customer
         * @returns {Mall.customer.AddressBook}
         */
        setCustomer: function (customer) {
            this._customer = customer;

            return this;
        },

        /**
         * Returns customer instance.
         *
         * @returns {?Mall.customer}
         */
        getCustomer: function () {
            return this._customer;
        },
		
		/**
		 * @param {type} flag
		 * @returns Deffered
		 */
		setNeedInvoice: function(flag){
			var billing = this.getSelectedBilling();
			
			billing.setData('need_invoice', flag ? "1" : "0");
			
			return this._edit(billing);
		},
		
		/**
		 * @returns {bool}
		 */
		getNeedInvoice: function(){
			return this.getSelectedBilling().getData('need_invoice')=="1";
		},

        /**
         * Return current addressbook.
         *
         * @returns {Array}
         */
        getAddressBook: function () {
            return this._book;
        },

        /**
         * Sets addressbook to given array value.
         *
         * @param {Array} addresses
         * @returns {Mall.customer.AddressBook}
         */
        setAddressBook: function (addresses) {
			var self = this;
			jQuery.each(addresses, function(){
				self._book.push(new Mall.customer.Address(this));
			});
            return this;
        },

        /**
         * Adds new address to addressbook. This performs ajax request.
         *
         * @param obj
         * @returns {*[]}
         * @private
         */
        _add: function (obj) {
            var deffered,
                address,
                error,
                self = this;

            address = new Mall.customer.Address(obj);
            this.beforeAdd.call(this, address);
            deffered = address.save();
			
			this.beforeRequest(deffered, address);
			
            deffered.done(function (data) {
                if (Boolean(data.status) === true){
					self._book.push(address);
				}else{
					//...
				}
            });

			deffered.always(function () {
				self.afterRequest(deffered, address);
				self.afterAdd.call(self, deffered, address);
			});

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

        /**
         * Removes address from addressbook and performs ajax request.
         *
         * @param {Number} id
         * @returns {?jQuery.Deffered}
         */
        remove: function (id) {
            var deffered = null,
                error = false,
                removedAddress,
                self = this;

            if (!this.getIsAddressExists(id) || !this.isRemoveable(id)) {
                return null;
            }

            this.beforeRemove(this.get(id));
            removedAddress = this.get(id);
            deffered = this.get(id).remove();
			
			this.beforeRequest(deffered, removedAddress);
			
            deffered.done(function (data) {
                if (data.status === undefined || Boolean(data.status) === false) {
                    error = true;
                }else{
					self._remove(id);
				}
            });

            deffered.always(function () {
				self.afterRequest(deffered, removedAddress);
                self.afterRemove.call(self, deffered, removedAddress);
            });

            return deffered;
        },

        /**
         * Returns if address exists.
         *
         * @param {Number} id
         * @returns {boolean}
         */
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

        /**
         * Edits address. This will return array: deffered and address object.
         *
         * @param obj
         * @returns {Array}
         * @private
         */
        _edit: function (obj) {
            var address,
                deffered,
                self = this;

            // check if object exists
            if (! obj instanceof Mall.customer.Address && ! this.getIsAddressExists(obj[this.ENTITY_ID_KEY])) {
                return [null, null];
            }

            address = obj instanceof Mall.customer.Address ? obj : this.get(obj[this.ENTITY_ID_KEY]);
            this.beforeEdit.call(this, address);
            if (obj instanceof Mall.customer.Address === false) {
                address.setData(obj);
            }

            deffered = address.save();
			self.beforeRequest(deffered, address);
			
            deffered.always(function () {
				self.afterRequest(deffered, address);
                self.afterEdit.call(self, deffered, address);
            });

            return [deffered, address];
        },

        /**
         * Returns whether address can be removed.
         *
         * @param {Number|Mall.customer.Address} address
         * @returns {boolean}
         */
        isRemoveable: function (address) {
			if(!(address instanceof Mall.customer.Address)){
				address = this.get(address);
			}
			
			//console.log(
			//		address.getId(), 
			//		this.getDefaultShipping().getId(),  
			//		this.getSelectedShipping().getId()
			//);
			
			if ((this.getDefaultBilling() && address === this.getDefaultBilling()) ||
				(this.getDefaultShipping() && address === this.getDefaultShipping()) || 
				(this.getSelectedShipping() !== null && address === this.getSelectedShipping()) || 
				(this.getSelectedBilling() !== null && address === this.getSelectedBilling()) || 
				this.getAddressBook().length < 2) {
                
				return false;
            }

            return true;
        },

        /**
         * Saves address to backend. This function will work both on update and new address.
         *
         * @param {(Object|Mall.customer.Address)} obj
         * @returns {jQuery.Deffered}
         */
        save: function (obj) {
            var id,
                deffered,
                address,
                _result,
                self = this;

            if (obj instanceof Mall.customer.Address) {
                id = obj.getId();
            } else if (obj instanceof Object) {
                id = obj[this.ENTITY_ID_KEY] === undefined ? null : obj[this.ENTITY_ID_KEY];
            }

            this.beforeSave.call(this, obj);
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
                self.afterSave.call(this, deffered, address);
            });

            return deffered;
        },

        /**
         * Sets address as default address for customer.
         *
         * @param address
         * @param type
         * @returns {Mall.customer.AddressBook}
         * @private
         */
		_setDefault: function(address, type){
			if(typeof address === "object" && address) {
                if (!isNaN(parseInt(address, 10))) {
                    address = this.get(address);
                }

                // sets default state
                jQuery.each(this.getAddressBook(), function () {
                    if (this.getId() === address.getId()) {
                        address.setDefaultState(type, 1);
                    } else {
                        this.setDefaultState(type, 0);
                    }
                });
				address = address.getId();
			}
            this["_default_" + type] = address;

            return this;
		},

        /**
         * Returns default address for customer.
         *
         * @param type
         * @returns {?Mall.customer.Address}
         */
		getDefault: function(type){
			if(this["_default_" + type]){
				return this.get(this["_default_" + type]);
			}
			return null;
		},

        /**
         * Sets default shipping address.
         *
         * @param {Mall.customer.Address} address
         * @returns {Mall.customer.AddressBook}
         */
        setDefaultShipping: function (address) {
            var id;
            this.beforeDefaultShipping.call(this, address);
			id = this._setDefault(address, "shipping");
            this.afterDefaultShipping.call(this, address);

            return id;
        },

        /**
         * Sets default billing address in customer scope.
         *
         * @param {Mall.customer.Address} address
         * @returns {Mall.customer.AddressBook}
         */
        setDefaultBilling: function (address) {
            var id;

            this.beforeDefaultBilling.call(this, address);
			id = this._setDefault(address, "billing");
            this.afterDefaultBilling.call(this, address);

            return id;
        },

        /**
         * Return default billing address.
         *
         * @returns {?Mall.customer.Address}
         */
        getDefaultBilling: function () {
           return this.getDefault("billing");
        },

        /**
         * Return default shipping address.
         *
         * @returns {?Mall.customer.Address}
         */
        getDefaultShipping: function () {
           return this.getDefault("shipping");
        },

        /**
         * Returns selected shipping address.
         *
         * @returns {?Mall.customer.Address}
         */
        getSelectedShipping: function () {
            var selectedShipping = null;
            jQuery.each(this.getAddressBook(), function (idx, item) {
                if (item.getIsSelectedShippping()) {
                    selectedShipping = item;
                    return true;
                }
            });

			// Selected found
			if(selectedShipping){
				return selectedShipping;
			}
			
			// Return by fallback
			return this.getDefaultShipping() || this.getAddressBook()[0];
        },

        /**
         * Sets selected shipping address.
         *
         * @param {(Mall.customer.Address|Number)} address
         * @returns {?Mall.customer.Address}
         */
        setSelectedShipping: function (address) {
            if (!isNaN(parseInt(address, 10))) {
                address = this.get(address);
            }
			
			if(typeof address != "object"){
				throw new Error("Adress object is not vaild");
			}

            this.beforeSelectShipping.call(this, address);
            if (address !== null) {
                jQuery.each(this.getAddressBook(), function (idx, item) {
                    if (item.getId() !== address.getId()) {
                        item.setUnselectShipping();
                    }
                });

                address.setSelectedShipping();
            }
            this.afterSelectShipping.call(this, address);

            return address;
        },

        /**
         * Returns selected billing address.
         *
         * @returns {?Mall.customer.Address}
         */
        getSelectedBilling: function () {
            var selectedBilling = null;
            jQuery.each(this.getAddressBook(), function (idx, item) {
                if (item.getIsSelectedBilling()) {
                    selectedBilling = item;
                    return true;
                }
            });
			
			// Selected found
			if(selectedBilling){
				return selectedBilling;
			}
			
			// Return by fallback
			return this.getDefaultBilling() || this.getAddressBook()[0];
        },

        /**
         * Sets seleted shipping address.
         *
         * @param {(Mall.customer.Address|Number)} address
         * @returns {?Mall.customer.Address}
         */
        setSelectedBilling: function (address) {
            if (!isNaN(parseInt(address, 10))) {
                address = this.get(address);
            }

            this.beforeSelectBilling.call(this, address);
            if (address !== null) {
                jQuery.each(this.getAddressBook(), function (idx, item) {
                    if (item.getId() !== address.getId()) {
                        item.setUnselectBilling();
                    }
                });
                address.setSelectedBilling();
            }
            this.afterSelectBilling.call(this, address);

            return address;
        },

        /**
         * Return seleteced address basen on given type
         * @param {String} type
         * @returns {?Mall.customer.Address}
         */
        getSelected: function (type) {
            var address = null;
            switch (type) {
                case "billing" :
                    address = this.getSelectedBilling();
                    break;

                case "shipping" :
                    address = this.getSelectedShipping();
                    break;

                default:
                    address = this.getSelectedShipping();
                    break;
            }

            return address;
        },

        /**
         * Saves default state of address to backend.
         *
         * @param {String} type
         * @returns {?jQuery.Deffered}
         */
        saveDefault: function (type) {
            var address = this.getDefault(type),
                deffered,
                self    = this;
            if (address === null) {
                return null;
            }
            this.beforeSaveDefault(address);
            deffered = address.save();
            deffered.done(function () {
                self.afterSaveDefault.call(self, deffered, address);
            });

            return deffered;
        },

        /**
         * Removes address from current addressbook object.
         *
         * @param {Number} id
         * @returns {Mall.customer.AddressBook}
         * @private
         */
        _remove: function (id) {
            var _id = null;
            jQuery.each(this.getAddressBook(), function (idx, item) {
                if (id === item.getId()) {
                    _id = idx;

                    return true;
                }
            });

            delete this._book[_id];
            this._book.splice(_id, 1);

            return this;
        },

        /**
         * Events
         */

        beforeAdd: function (address) {
            return this;
        },

        afterAdd: function (deffered, address) {
            return this;
        },

        beforeEdit: function (address) {
            return this;
        },

        afterEdit: function (deffered, address) {
            return this;
        },

        beforeSave: function (address) {
            return this;
        },

        afterSave: function (deffered, address) {
            return this;
        },

        beforeRemove: function (address) {
            return this;
        },

        afterRemove: function (deffered, address) {
            return this;
        },

        beforeDefaultShipping: function (address) {
            return this;
        },

        afterDefaultShipping: function (address) {
            return this;
        },

        beforeDefaultBilling: function (address) {
            return this;
        },

        afterDefaultBilling: function (address) {
            return this;
        },

        beforeSelectShipping: function (address) {
            return this;
        },

        afterSelectShipping: function (address) {
            return this;
        },

        beforeSelectBilling: function (address) {
            return this;
        },

        afterSelectBilling: function (address) {
            return this;
        },

        beforeSaveDefault: function (address) {
            return this;
        },

        afterSaveDefault: function (deffered, address) {
            return this;
        },

		beforeRequest: function(deffered, data){
			//console.log("Loading start");
		},
		afterRequest: function(deffered, data){
			//console.log("Loading stop");
		}
    };

})();