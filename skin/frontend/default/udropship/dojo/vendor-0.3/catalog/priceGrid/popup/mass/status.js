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

        handleSave: function(){

            var grid = this.getGrid();
            var store = grid.get('store');
            var rowUpdater = grid.get('rowUpdater');
            var button = this._modal.find(".btn-primary");
            var modal = this._modal;

            var data = modal.find("form").serialize();

            button.button('loading');

            // make request
            jQuery.ajax({
                method: "post",
                data: data,
                url: this._saveUrl,
                success: function(data){

                    var data = data.content;

                    // Restore selection just changed
                    grid.
                        refresh({keepScrollPosition: true}).
                        then(function(){
                            if(data.global){
                                grid.selectAll();
                            }else{
                                jQuery.each(data.changed_ids, function(){
                                    grid.select(parseInt(this));
                                });
                            }
                        });

                    if (parseInt(data.skipped)) {
	                    jQuery('#noty_top_layout_container').remove();
                        noty({
                            text: data.skipped_msg,
                            type: 'warning'
                        });
                    }
                },
                complete: function(){
                    button.button('reset');
                    modal.modal('hide');
                },
                error: function(data){
                    alert(data.responseText)
                }
            });
        },

        _afterRender: function(data){
            this.inherited(arguments);
            if (data.status == "enable") {
                this._modal.find("h4").text(Translator.translate("Mass enable products on site"));
            } else {
                this._modal.find("h4").text(Translator.translate("Mass disable products on site"));
            }
            this._modal.find(".modal-footer .btn-default").html(Translator.translate("Cancel"));
            this._modal.find(".modal-footer .btn-primary").html(Translator.translate("Execute"));
        },

        // After load content
        _afterLoad: function(node, response){
            this.inherited(arguments);

            var form = node.parents("form"),
                btn = jQuery(".btn-primary", form),
                self = this;

            form.validate({
                submitHandler: function(){
                    self.handleSave.apply(self, arguments);
                    return false;
                }
            });
        }
    });

    return new Updater();
});