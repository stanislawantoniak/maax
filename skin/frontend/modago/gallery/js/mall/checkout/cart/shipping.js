(function () {
    "use strict";

    Mall.Cart.Shipping = {
        form_id: "cart-shipping-methods-form",
        content: "#cart-shipping-methods",

        init: function () {
            var self = this;

            Mall.Cart.Shipping.updateTotals();

            var shippingMethodSelectTrigger = jQuery("[data-select-shipping-method-trigger=1]");
            //shippingMethodSelectTrigger.on("click", function (e) {
            //    self.handleShippingMethodSelect(e);
            //});
            jQuery(document).delegate("[data-select-shipping-method-trigger=1]", "click" , function(e){
                self.handleShippingMethodSelect(e);
            });


            jQuery("[data-select-shipping-method-trigger=0]").click(function (e) {
                //1. populate popup
                Mall.Cart.Shipping.populateShippingPointSelect();
            });


            if (jQuery("#cart-shipping-methods [name=_shipping_method]").length == 1) {
                jQuery("#cart-shipping-methods [name=_shipping_method]").click();
            }


            jQuery("#change-shipping-type").click(function () {
                jQuery(".shipping-method-selector").slideDown();
                jQuery(".shipping-method-selected").slideUp();
            });
        },

        getVendors: function () {
            return Mall.reg.get("vendors");
        },
        getVendorCosts: function () {
            return Mall.reg.get("vendor_costs");
        },

        getSelectedShipping: function () {
            return jQuery(Mall.Cart.Shipping.content).find("input[name=_shipping_method]:checked");
        },
        handleShippingMethodSelect: function (e) {
            Mall.Cart.Shipping.setShippingMethod(e.target);

            if (jQuery(e.target).is("a")) {
                e.preventDefault();
                jQuery("#select_inpost_point").modal("hide");
            }
        },
        populateShippingPointSelect: function () {
            //1. Get block
            var shippingMethodCode= Mall.Cart.Shipping.getSelectedShipping().val();

            jQuery.ajax({
                url: "/checkout/cart/deliveryDetails",
                data: {shipping_method_code: shippingMethodCode}
            }).done(function (block) {
                //console.log(block);
                jQuery("#select_inpost_point .modal-body").html(block);
                jQuery("#select_inpost_point").modal("show");
            });


        },
        updateTotals: function () {
            var content = jQuery("#cart-shipping-methods");

            var methodRadio = content.find("input[name=_shipping_method]:checked");
            var shippingCost;

            if (jQuery.type(methodRadio.val()) !== "undefined") {
                //shipping total
                shippingCost = jQuery(methodRadio).attr("data-method-cost");
                var shippingCostFormatted = jQuery(methodRadio).attr("data-method-cost-formatted");
                jQuery("#product_summary").find("span.val_delivery_cost").closest("li").find("span.price").html(shippingCostFormatted);

            } else {
                shippingCost = 0; //not selected yet
            }
            //Grand total
            var totalSum = parseInt(parseInt(Mall.reg.get("quote_products_total")) + parseInt(shippingCost) + -parseInt(Mall.reg.get("quote_discount_total")));
            jQuery("#sum_price .value_sum_price").html(Mall.currency(totalSum));
        },
        appendSelectedCartShipping: function (selectedMethodData) {

            var shippingMethodSelectedContainer = jQuery(".shipping-method-selected");

            shippingMethodSelectedContainer.find('[data-item="method"]').html(selectedMethodData["method"]);
            shippingMethodSelectedContainer.find('[data-item="description"]').html(selectedMethodData["description"]);
            shippingMethodSelectedContainer.find('[data-item="logo"]').html(selectedMethodData["logo"]);

            shippingMethodSelectedContainer.find('[data-item="additional"]').html(selectedMethodData["additional"]);


            jQuery(".shipping-method-selector").slideUp();
            jQuery(".shipping-method-selected").slideDown();
        },
        setShippingMethod: function (target) {
            var selectedMethodData = [];

            var vendors = Mall.Cart.Shipping.getVendors(),
                content = jQuery(Mall.Cart.Shipping.content);

            var methodRadio = content.find("input[name=_shipping_method]:checked");
            var shipping = methodRadio.val();


            selectedMethodData["logo"] = jQuery(methodRadio).attr("data-carrier-logo");
            selectedMethodData["method"] = jQuery(methodRadio).attr("data-carrier-method");
            selectedMethodData["description"] = jQuery(methodRadio).attr("data-carrier-description");
            selectedMethodData["additional"] = jQuery(target).attr("data-carrier-additional");

            var pointCode = (jQuery(target).attr("data-carrier-pointcode") !== "undefined") ? jQuery(target).attr("data-carrier-pointcode") : "";

            if (jQuery.type(shipping) !== "undefined") {
                var inputs = '';
                jQuery.each(vendors, function (i, vendor) {
                    inputs += '<input type="hidden" name="shipping_method[' + vendor + ']" value="' + shipping + '" required="required" />';
                });
                inputs += '<input type="hidden" name="shipping_point_code" value="' + pointCode + '"  />';

                content.find("form .shipping-collect").html(inputs);
            }

            Mall.Cart.Shipping.appendSelectedCartShipping(selectedMethodData);


            var formData = jQuery("#cart-shipping-methods-form").serializeArray();
            //console.log(formData);

            jQuery.ajax({
                url: jQuery("#cart-shipping-methods-form").attr("action"),
                data: formData
            }).done(function (response) {
                //console.log(response);
            });

            Mall.Cart.Shipping.updateTotals();
        }
    }
})();


jQuery(document).ready(function () {
    Mall.Cart.Shipping.init();

    jQuery("#cart-buy").on('click', function (e) {
        jQuery(this).find('i').addClass('fa fa-spinner fa-spin');
    });

});








