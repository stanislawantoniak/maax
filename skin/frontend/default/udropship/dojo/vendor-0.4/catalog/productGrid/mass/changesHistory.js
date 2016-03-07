define([
    "dojo/_base/declare",
    "vendor/grid/mass/_base",
    "dojo/_base/lang",
    "vendor/misc"
], function (declare, _base, lang, misc) {

    return declare([_base], {
        send: function (requestData) {
            return jQuery.post(
                this._saveUrl,
                lang.mixin(requestData || {}, this._getRequestData())
            ).then(
                lang.hitch(this, this._saveSuccess),
                lang.hitch(this, this._saveError)
            );
        },

        _getRequestData: function () {
            return {};
        },

        _saveSuccess: function(response){
            this.inherited(arguments);
            window.changesHistory.updateModal();
            noty ({
                text: response.message,
                type: response.status ? 'success' : 'error',
                timeout: 10000
            });
        },

        _saveError: function (response) {

        }
    });
});