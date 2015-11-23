/**
 * Created by pawelchyl on 16.09.2014.
 */

(function () {
    "use strict";

    Mall.customer.Address = function (data) {
        /**
         * Is object new(not saved in database).
         *
         * @type {boolean}
         * @private
         */
        this._is_object_new = false;

        /**
         * Data of the address object.
         *
         * @type {{}}
         * @private
         */
        this._data = {};

        /**
         * Constructor for the object.
         *
         * @param {?Object} data
         * @returns {Mall.customer.Address}
         * @private
         */
        this._create = function (data) {
            this.setIsObjectNew(false);
            if (data === undefined) {
                this.setIsObjectNew(true);
                this._data = {entity_id: null, id: null};

                return this;
            }

            if (data.entity_id === undefined || data.entity_id === null) {
                this.setIsObjectNew(true);
            }
            this._data = data;
            return this;
        };

        this._create(data);
    };

    Mall.customer.Address.prototype = {
        /**
         * Returns all object data or given key.
         *
         * @param {String} key
         * @returns {*}
         */
        getData: function (key) {
            if (key === undefined) {
                return this._data;
            }

            return this._data[key] === undefined ? null : this._data[key];
        },

        /**
         * Sets object data - key value pair or object.
         *
         * @param {(Object|String)} key
         * @param value
         * @returns {Mall.customer.Address}
         */
        setData: function (key, value) {
            if (value === undefined) {
                // we have object as parameter;
                value = key;
                // merge objects
                jQuery.extend(this._data, value);
            } else {
                this._data[key] = value;
            }

            return this;
        },

        /**
         * Validates object data.
         *
         * @deprecated
         * @param data
         * @returns {boolean}
         */
        validate: function (data) {
            if (data.id === undefined) {
                return false;
            }
            return true;
        },

        /**
         * Return current id for the object.
         *
         * @returns {?Number}
         */
        getId: function () {
            return this.getData("entity_id");
        },

        /**
         * Saves object state to backend.
         *
         * @returns {jQuery.Deffered}
         */
        save: function () {
            var deffered,
                self = this;

            // validate object before save
            this._prepareForSave();
            deffered = jQuery.ajax({
                url: Config.url.address.save,
                cache: false,
                crossDomain: true,
                dataType: "json",
                data: this.getData(),
                type: "POST"
            });

            if (this.getIsObjectNew()) {
                deffered.done(function (data) {
                    if (Boolean(data.status) === true) {
                        self.setData(data.content);
                        self.setIsObjectNew(false);
                    }
                });
            }

            return deffered;
        },

        /**
         * Removes object from backend.
         *
         * @returns {jQuery.Deffered}
         */
        remove: function () {
            var deffered;

            this._prepareForSave();
            deffered = jQuery.ajax({
                url: Config.url.address.remove,
                cache: false,
                crossDomain: true,
                dataType: "json",
                data: this.getData(),
                type: "POST"
            });

            return deffered;
        },

        /**
         * Returns whether object is new.
         *
         * @returns {boolean}
         */
        getIsObjectNew: function () {
            return this._is_object_new;
        },

        /**
         * Sets whether object is new.
         * @param {Boolean} state
         * @returns {Mall.customer.Address}
         */
        setIsObjectNew: function (state) {
            this._is_object_new = state;

            return this;
        },

        /**
         * Returns whether address is selected for shipping.
         *
         * @returns {boolean}
         */
        getIsSelectedShippping: function () {
            return Boolean(this.getData("is_selected_shipping"));
        },

        /**
         * Sets address as selected for shipping.
         *
         * @returns {Mall.customer.Address}
         */
        setSelectedShipping: function () {
            this.setData("is_selected_shipping", true);

            return this;
        },

        /**
         * Unselects address from shipping.
         *
         * @returns {Mall.customer.Address}
         */
        setUnselectShipping: function () {
            this.setData("is_selected_shipping", false);

            return this;
        },

        /**
         * Return whether address is selected for billing.
         *
         * @returns {boolean}
         */
        getIsSelectedBilling: function () {
            return Boolean(this.getData("is_selected_billing"));
        },

        /**
         * Selects addres as billing address.
         *
         * @returns {Mall.customer.Address}
         */
        setSelectedBilling: function () {
            this.setData("is_selected_billing", true);

            return this;
        },

        /**
         * Unselects address for billing.
         *
         * @returns {Mall.customer.Address}
         */
        setUnselectBilling: function () {
            this.setData("is_selected_billing", false);

            return this;
        },

        /**
         * Sets default billing and shipping state for address.
         *
         * @param {String} type
         * @param {Number} state
         * @returns {Mall.customer.Address}
         */
        setDefaultState: function (type, state) {
            this.setData("default_" + type, state);

            return this;
        },

        unsetStreet: function () {
            this.setData("street", []);

            return this;
        },

        /**
         * Prepares object for saving to backend.
         *
         * @returns {Mall.customer.Address}
         * @private
         */
        _prepareForSave: function () {
            if (this.getData("id") === null || this.getData("entity_id") !== this.getData("id")) {
                this.setData("id", this.getId());
            }

            if (this.getData("form_key") === null) {
                this.setData("form_key", Mall.getFormKey());
            }

            if (!jQuery.isArray(this.getData("street"))) {
                this.setData("street", [this.getData("street")]);
            }

            return this;
        }

    };
})();