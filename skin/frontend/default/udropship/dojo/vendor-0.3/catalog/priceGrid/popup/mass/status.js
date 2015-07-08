define([
    "dojo/_base/declare",
    "vendor/catalog/priceGrid/popup/_base",
], function(declare, _base){

    var Updater = declare([_base], {
        _url: "/udprod/vendor_price/massStatus",
        _saveUrl: "/udprod/vendor_price/massStatusSave",
        _className: "status-modal",

        handleDbClick: function(params){
            this.setStoreId(params.store_id);
            var modal = this._triggerModal(params);
            modal.modal('show');
            this.loadContent(params);
        },

        handleClick: function(){
            this.handleDbClick.apply(this, arguments);
        },

        getLoadData: function(inputData){
            return inputData;
        },

        _afterRender: function(data){
            this.inherited(arguments);
            this._modal.find("h4").text(Translator.translate("Mass status change"));
        },

        // After load content
        _afterLoad: function(node, response){
            this.inherited(arguments);

            var form = node.parents("form"),
                btn = jQuery(".btn-primary", form),
                self = this;

            var refreshSaveBtn = function(){

            };

            refreshSaveBtn();
        }
    });

    return new Updater();
});