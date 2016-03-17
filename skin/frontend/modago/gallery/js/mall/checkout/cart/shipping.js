jQuery(document).ready(function() {
    
   jQuery(".select-inpost-code").click(function (e) {
        e.preventDefault();
        var selectInPostCodeLink = jQuery(this);
        var code = selectInPostCodeLink.text(),
        inPostCodeInput = '<input type="hidden" name="inpost_code" value="' + code + '"  />',
        selectedMethodData = [];
        jQuery("#cart-shipping-methods .shipping-collect-inpost").html(inPostCodeInput);
        
        jQuery("#select_inpost_point").modal("hide");
        setShippingMethod("input[name=_shipping_method][data-carrier-inpost=1]");
        var inPostRadio = jQuery("input[name=_shipping_method][data-carrier-inpost=1]");
        
        //jQuery(".inpost-code-holder").html(code);
        selectedMethodData["logo"] = '<img  src="'+Mall.reg.get("inpost_logo")+'" height="50px"/>';
        selectedMethodData["name"] = inPostRadio.attr("data-carrier-name");
        selectedMethodData["method"] = inPostRadio.attr("data-carrier-method");
        selectedMethodData["additional"] = selectInPostCodeLink.attr("data-inpost-address");
        
        appendSelectedCartShipping(selectedMethodData);
        toggleCartShippingSelectors(); 
    });
    jQuery("input[name=_shipping_method]").click(function (e) {
        setShippingMethod(this);
    });

    function setShippingMethod(methodRadio){
        console.log(jQuery(methodRadio).attr("data-carrier-inpost"));
        var selectedMethodData = [];
        if(jQuery(methodRadio).attr("data-carrier-inpost") == 1){
            //Open modal to select paczkomat
            jQuery("#select_inpost_point").modal("show");
        } else {
            jQuery("#cart-shipping-methods .shipping-collect-inpost").html("");
            
            selectedMethodData["logo"] = '<figure class="logo-courier pull-right"><div class="shipment-icon"><i class="fa fa-truck fa-3x"></i></div></figure>';
            selectedMethodData["name"] = jQuery(methodRadio).attr("data-carrier-name");
            selectedMethodData["method"] = jQuery(methodRadio).attr("data-carrier-method");
            selectedMethodData["additional"] = "";
        
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
        
        if(jQuery(methodRadio).attr("data-carrier-inpost") == 0){
           jQuery("#cart-shipping-methods .shipping-collect-inpost").html("");
           appendSelectedCartShipping(selectedMethodData);
           toggleCartShippingSelectors(); 
        }
        
    }
    
    jQuery("#change-shipping-type").click(function(){        
        jQuery(".shipping-method-selector").slideToggle();
        jQuery(".shipping-method-selected").slideToggle();
    });
    
    function appendSelectedCartShipping(selectedMethodData){
        console.log(selectedMethodData);
        var shippingMethodSelectedContainer = jQuery(".shipping-method-selected");
        
        shippingMethodSelectedContainer.find('[data-item="name"]').html(selectedMethodData["name"]);
        shippingMethodSelectedContainer.find('[data-item="method"]').html(selectedMethodData["method"]);
        shippingMethodSelectedContainer.find('[data-item="logo"]').html(selectedMethodData["logo"]);
        
        shippingMethodSelectedContainer.find('[data-item="additional"]').html(selectedMethodData["additional"]);
    }
    
    function toggleCartShippingSelectors(){
        jQuery(".shipping-method-selector").slideToggle();
        jQuery(".shipping-method-selected").slideToggle();
    }

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




