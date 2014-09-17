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
                deffered = deffered.done(function (data) {
                    if (Boolean(data.status) === true) {
                        self.setData("entity_id", data.content.entity_id);
                        self.setIsObjectNew(false);
                    }
                    console.log("deffered w save modelu");
                });
            }

            return deffered;
        },

        getIsObjectNew: function () {
            return this._is_object_new;
        },

        setIsObjectNew: function (state) {
            this._is_object_new = state;

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