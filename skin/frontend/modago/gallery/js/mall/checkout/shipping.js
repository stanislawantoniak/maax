jQuery(document).ready(function() {
    
   jQuery(".select-inpost-code").click(function (e) {
        e.preventDefault();
        var code = jQuery(this).text();
        var inPostCodeInput = '<input type="hidden" name="inpost_code" value="' + code + '"  />';
        jQuery("#cart-shipping-methods .shipping-collect-inpost").html(inPostCodeInput);
        jQuery(".inpost-code-holder").html(code);
        jQuery("#select_inpost_point").modal("hide");
        setShippingMethod("input[name=_shipping_method][data-carrier-inpost=1]");
    });
    jQuery("input[name=_shipping_method]").click(function (e) {
        setShippingMethod(this);
    });

    function setShippingMethod(methodRadio){
        console.log(jQuery(methodRadio).attr("data-carrier-inpost"));
        if(jQuery(methodRadio).attr("data-carrier-inpost") == 1){
            //Open modal to select paczkomat
            jQuery("#select_inpost_point").modal("show");
        } else {
            jQuery("#cart-shipping-methods .shipping-collect-inpost").html("");
        }


        var vendors = Mall.reg.get("vendors"),
            content = jQuery("#cart-shipping-methods");

        var shipping = content.find("input[name=_shipping_method]:checked").val();

        if (jQuery.type(shipping) !== "undefined") {
            var inputs = '';
            jQuery.each(vendors, function (i, vendor) {
                inputs += '<input type="hidden" name="shipping_method[' + vendor + ']" value="' + shipping + '" required="required" />';
            });
            content.find("form .shipping-collect").html(inputs);

        }
    }
    
    jQuery("#change-shipping-type").click(function(){        
        jQuery(".shipping-method-selector").slideToggle();
        //jQuery(".shipping-method-selector").sildeToggle();
    })

    jQuery("#cart-buy").on('click', function (e) {
        e.preventDefault();
        jQuery(this).find('i').addClass('fa fa-spinner fa-spin');
        var button = jQuery(this);

        var formData = jQuery("#cart-shipping-methods-form").serializeArray();

        jQuery.ajax({
            url: jQuery("#cart-shipping-methods-form").attr("action"),
            data: formData
        }).done(function (response) {
            console.log(response);
            if (response.status) {
                window.location = button.attr("href");
            }

        });
    }); 
    
});




