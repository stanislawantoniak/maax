var Cart = {
    changeQuantity: function (id, val){

        var input = jQuery("[data-id='" + id + "']");

        var error = jQuery("div.product_" + parseInt(id)).find(".error");
        error.hide();

        if (val < input.attr("data-min-sale-qty") || isNaN(val)) {

            val = input.attr("data-min-sale-qty");

        } else if (val > input.attr("data-max-sale-qty")) {

            val = input.attr("data-max-sale-qty");
            error.show();

        }

        input.val(val);

        if (val != input.attr("old-value")) {

            jQuery("#cart-form").submit();

        }

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
    jQuery(".qty input").change(Cart.update);
});