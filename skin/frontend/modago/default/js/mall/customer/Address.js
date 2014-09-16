/**
 * Created by pawelchyl on 16.09.2014.
 */

(function () {
    "use strict";

    Mall.customer.Address = function (data) {
        this._is_object_new = false;

        this._data = function (data) {
            this._is_object_new = false;
            if (data === undefined) {
                this._is_object_new = true;
                return {id: null};
            }

            if (!this.validate(data)) {
                return null;
            }

            return data;
        };
    };

    Mall.customer.Address.prototype = {
        getData: function (key) {
            if (key === undefined) {
                return this._data;
            }
            return this._data[key];
        },

        setData: function (key, value) {

        },

        validate: function (data) {
            if (data.id === undefined) {
                return false;
            }
            return true;
        }
    };
})();