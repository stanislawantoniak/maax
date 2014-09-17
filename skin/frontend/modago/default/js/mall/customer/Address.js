/**
 * Created by pawelchyl on 16.09.2014.
 */

(function () {
    "use strict";

    Mall.customer.Address = function (data) {
        this._is_object_new = false;

        this._data = {};

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
        getData: function (key) {
            if (key === undefined) {
                return this._data;
            }

            return this._data[key] === undefined ? null : this._data[key];
        },

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

        validate: function (data) {
            if (data.id === undefined) {
                return false;
            }
            return true;
        },

        getId: function () {
            return this.getData("entity_id");
        },

        save: function () {
            var deffered,
                self = this;

            // validate object before save
            this._validateBeforeSave();
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
                        console.log(self);
                        self.setData(data.content);
                        self.setIsObjectNew(false);
                    }
                });
            }

            return deffered;
        },

        remove: function () {
            var deffered;

            this._validateBeforeSave();
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

        getIsObjectNew: function () {
            return this._is_object_new;
        },

        setIsObjectNew: function (state) {
            this._is_object_new = state;

            return this;
        },

        getIsSelectedShippping: function () {
            return Boolean(this.getData("is_selected_shipping"));
        },

        setSelectedShipping: function () {
            this.setData("is_selected_shipping", true);

            return this;
        },

        setUnselectShipping: function () {
            this.setData("is_selected_shipping", false);

            return this;
        },

        getIsSelectedBilling: function () {
            return Boolean(this.getData("is_selected_billing"));
        },

        setSelectedBilling: function () {
            this.setData("is_selected_billing", true);

            return this;
        },

        setUnselectBilling: function () {
            this.setData("is_selected_billing", false);

            return this;
        },

        _validateBeforeSave: function () {
            if (this.getData("id") === null || this.getData("entity_id") !== this.getData("id")) {
                this.setData("id", this.getId());
            }

            if (this.getData("form_key") === null) {
                this.setData("form_key", Mall.getFormKey());
            }

            return this;
        }
    };
})();