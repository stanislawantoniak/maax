var Cart = {
    changeQuantity: function (id, val){

        var input = jQuery("[data-id='" + id + "']");

        var error = jQuery("div.product_" + parseInt(id)).find(".error");
        error.hide();

        if (val < input.attr("data-min-sale-qty")) {

            val = input.attr("data-min-sale-qty");
            error.show();

        } else if (val > input.attr("data-max-sale-qty")) {

            val = input.attr("data-max-sale-qty");
            error.show();

        }

        input.val(val);

        OrbaLib.Cart.update({
            "item_id": id,
            "qty": val
        }, function(e){
            jQuery.ajax({
                url: "",
                context: document.body,
                success: function(s,x){
                    jQuery(this).html(s);
                }
            });
        });

        jQuery("body").append('<div class="listing-overlay" style="position: fixed; width: 100%; height: 100%; left: 0px; top: 0px; z-index: 1000000; display: none; background: url(http://kosmetyki.ipson.modago.es/skin/frontend/modago/default/images/modago-ajax-loader.gif) 50% 50% no-repeat rgba(255, 255, 255, 0.498039);"></div>');
        jQuery(".listing-overlay").show( "fade", 1000 );

        return false;
    },

    less: function (id) {
        Cart.changeQuantity(
            jQuery("[data-id='" + id + "']").attr("data-id"),
            parseInt(jQuery("[data-id='" + id + "']").val()) - 1
        );
    },

    more: function (id) {
        Cart.changeQuantity(
            jQuery("[data-id='" + id + "']").attr("data-id"),
            parseInt(jQuery("[data-id='" + id + "']").val()) + 1
        );
    },

    update: function(){
        Cart.changeQuantity(
            jQuery(this).attr("data-id"),
            parseInt(jQuery(this).val())
        );
    }
};

jQuery(document).ready(function() {
    jQuery("input[name=quantity]").change(Cart.update);
});