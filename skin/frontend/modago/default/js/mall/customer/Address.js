/**
 * Created by pawelchyl on 16.09.2014.
 */

(function () {
    "use strict";

    Mall.customer.Address = function (data) {
        this._is_object_new = false;

        this._data = function (data) {
            this.setIsObjectNew(false);
            if (data === undefined) {
                this.setIsObjectNew(true);
                return {entity_id: null, id: null};
            }

            if (data.entity_id === undefined || data.entity_id === null) {
                this.setIsObjectNew(true);
            }

            return data;
        };
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

        },

        getIsObjectNew: function () {
            return this._is_object_new;
        },

        setIsObjectNew: function (state) {
            this._is_object_new = state;

            return this;
        }
    };
})();