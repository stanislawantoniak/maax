jQuery(document).ready(function () {


    setTotals();
    jQuery("[data-select-shipping-method-trigger=1]").click(function (e) {
        setShippingMethod(e.target);
        if (jQuery(e.target).is("a")) {
            e.preventDefault();
            jQuery("#select_inpost_point").modal("hide");
        }
    });


    jQuery("[data-select-shipping-method-trigger=0]").click(function (e) {
        jQuery("#select_inpost_point").modal("show");
    });


    jQuery("#change-shipping-type").click(function () {
        jQuery(".shipping-method-selector").slideDown();
        jQuery(".shipping-method-selected").slideUp();
    });


    jQuery("#cart-buy").on('click', function (e) {
        jQuery(this).find('i').addClass('fa fa-spinner fa-spin');
    });

});

function setTotals(){
    var content = jQuery("#cart-shipping-methods");

    var methodRadio = content.find("input[name=_shipping_method]:checked");

    //shipping total
    var shippingCost = jQuery(methodRadio).attr("data-method-cost");
    var shippingCostFormatted = jQuery(methodRadio).attr("data-method-cost-formatted");
    jQuery("#product_summary").find("span.val_delivery_cost").closest("li").find("span.price").html(shippingCostFormatted);


    console.log(Mall.reg.get("quote_products_total"));
    console.log(Mall.reg.get("quote_discount_total"));
    console.log(shippingCost);



    //Grand total
    var totalSumm = parseInt( parseInt(Mall.reg.get("quote_products_total")) + parseInt(shippingCost) +  - parseInt(Mall.reg.get("quote_discount_total")) );
    jQuery("#sum_price .value_sum_price").html(Mall.currency(totalSumm));
}

function appendSelectedCartShipping(selectedMethodData) {
    console.log(selectedMethodData);
    var shippingMethodSelectedContainer = jQuery(".shipping-method-selected");

    shippingMethodSelectedContainer.find('[data-item="name"]').html(selectedMethodData["name"]);
    shippingMethodSelectedContainer.find('[data-item="method"]').html(selectedMethodData["method"]);
    shippingMethodSelectedContainer.find('[data-item="logo"]').html(selectedMethodData["logo"]);

    shippingMethodSelectedContainer.find('[data-item="additional"]').html(selectedMethodData["additional"]);


    jQuery(".shipping-method-selector").slideUp();
    jQuery(".shipping-method-selected").slideDown();
}


function setShippingMethod(target) {
    var selectedMethodData = [];

    var vendors = Mall.reg.get("vendors"),
        content = jQuery("#cart-shipping-methods");

    var methodRadio = content.find("input[name=_shipping_method]:checked");
    var shipping = methodRadio.val();
    console.log(shipping);

    console.log(jQuery(methodRadio).attr("data-carrier-name"));
    console.log(jQuery(methodRadio).attr("data-carrier-method"));

    selectedMethodData["logo"] = jQuery(methodRadio).attr("data-carrier-logo");
    selectedMethodData["name"] = jQuery(methodRadio).attr("data-carrier-name");
    selectedMethodData["method"] = jQuery(methodRadio).attr("data-carrier-method");
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

    appendSelectedCartShipping(selectedMethodData);



    var formData = jQuery("#cart-shipping-methods-form").serializeArray();
    console.log(formData);

    jQuery.ajax({
        url: jQuery("#cart-shipping-methods-form").attr("action"),
        data: formData
    }).done(function (response) {
        console.log(response);
    });

    setTotals();
}




