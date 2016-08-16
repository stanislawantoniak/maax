var Cart = {
    changeQuantity: function (productId, maxQty, minQty, action){
        var productQuantityBlock = jQuery("div.product_" + parseInt(productId));
        productQuantityBlock.find(".error").hide();
        var quantity =  productQuantityBlock.find("input[name=quantity]").val();
        if(action == 'less'){
            if(Cart.validateLessQuantity(quantity, maxQty, minQty)){
                quantity--;
                productQuantityBlock.find("input[name=quantity]").val(quantity);
            }else{
                //jQuery("div.product_"+productId).find(".error").show();
            }
        }
        if(action == 'more'){
            if(Cart.validateMoreQuantity(quantity, maxQty, minQty)){
                quantity++;
                productQuantityBlock.find("input[name=quantity]").val(quantity);
                Cart.addToCart(productId, quantity);
            }else{
                productQuantityBlock.find("input[name=quantity]").val(maxQty);
                jQuery("div.product_"+productId).find(".error").show();
            }
        }
        return false;
    },

    validateLessQuantity: function(quantity, maxQty, minQty){
        if(parseInt(quantity) > parseInt(minQty) && parseInt(quantity) > 0){
            return true;
        }
        return false;
    },

    validateMoreQuantity: function(quantity, maxQty, minQty){
        if(parseInt(quantity) < parseInt(maxQty) && parseInt(quantity) > 0){
            return true;
        }
        return false;
    },

    addToCart: function(id, qty) {
//        if(Mall._current_superattribute == null && Mall.product._current_product_type == "configurable") {
//            return false;
//        }
//        jQuery('#full-width-popup-table .quantity span').text('');
//        var superLabel = jQuery(this._current_superattribute).attr("name");
        var attr = {};
//        attr[jQuery(this._current_superattribute).attr("data-superattribute")] = jQuery(this._current_superattribute).attr("value");
//	    var popup = jQuery("#popup-after-add-to-cart");
//	    popup.find(".modal-error").hide();
//	    popup.find(".modal-loaded").hide();
//	    popup.find(".modal-loading").show();
//	    popup.modal('show');
//	    popup.css('pointer-events','none');
//	    jQuery('#add-to-cart').css('pointer-events','none');
//	    jQuery('#full-width-popup-table .quantity span').text(qty);
        OrbaLib.Cart.add({
            "product_id": id,
            "super_attribute": attr,
            "qty": qty
        }, addtocartcallback);
        return false;
    }
};

jQuery(document).ready(function() {
	jQuery("input[name=quantity]").change(function(){
//	    var quantityR = jQuery(this).val();
//	    var maxQtyR = jQuery(this).closest("quantity_blocks").find("input[name=max_qty]").val(); console.log(jQuery(maxQtyR));
//	    var minQtyR = jQuery(this).closest("quantity_blocks").find("input[name=min_qty]").val();
//	    if(parseInt(quantityR) >= parseInt(minQtyR) && parseInt(quantityR) <= parseInt(maxQtyR) && parseInt(quantityR) > 0){
//	        Cart.addToCart(productId, quantityR);
//	    }else{
//	        jQuery(this).val(maxQtyR);
//            jQuery(this).parent("quantity_blocks").find(".error").show();
//
//        }
	});
});
