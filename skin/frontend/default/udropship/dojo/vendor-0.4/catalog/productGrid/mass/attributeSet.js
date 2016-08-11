define([
    "dojo/_base/declare",
    "vendor/grid/mass/_base"
], function(declare, _base){

    return declare([_base], {
        _getRequestData: function(){
            var ret = this.inherited(arguments);
            return ret;
        },
        _saveSuccess: function(response){
            this.inherited(arguments);
            window.attributeSet.closeModal();
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