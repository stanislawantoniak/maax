if (!Itoris) {
    var Itoris = {};
}

Itoris.GroupedPromotions = Class.create({
    initialize : function(qtyProducts, qtyCurrentProduct, promodata) {
        this.promoData = promodata;
        this.pricesWithOptions = {};
        this.rulesInCheckoutCart();
        this.deleteTitle();
        this.changeQtyFromOtherProducts(qtyProducts);
        for (var i = 0; i < $$('.itoris_groupedproductpromotions_config_add_to_cart').length; i++) {
            Event.observe($$('.itoris_groupedproductpromotions_config_add_to_cart')[i], 'click', this.changeQtyFromCurrentProduct.bind(this, qtyCurrentProduct, $$('.itoris_groupedproductpromotions_config_add_to_cart')[i]));
        }
        this.changePrice();
    },
    deleteTitle : function() {
        if ($$('.box-itoris-product-promotions').length > 1) {
            for (var i = 1; i < $$('.box-itoris-product-promotions').length; i++) {
                if ($$('.box-itoris-product-promotions')[i].select('h2')[0]) {
                    $$('.box-itoris-product-promotions')[i].select('h2')[0].remove();
                }
                if (!$$('.box-itoris-product-promotions')[i].select('form').length) {
                    $$('.box-itoris-product-promotions')[i].hide();
                }
            }
        }
        if (!$$('.box-itoris-product-promotions')[0].select('form').length) {
            $$('.box-itoris-product-promotions')[0].hide();
        }
    },
    rulesInCheckoutCart : function() {
        if ($$('.checkout-cart-index')[0] && $$('.checkout-cart-index')[0].select('.cart')[0] && $$('.box-itoris-product-promotions')[0] && $$('.cart-collaterals')[0]) {
            $$('.checkout-cart-index')[0].select('.cart')[0].insertBefore($$('.box-itoris-product-promotions')[0], $$('.cart-collaterals')[0]);
        }
    },
    changeQtyFromOtherProducts : function(qtyProducts) {
        if (qtyProducts) {
            if ($('super-product-table')) {
                var input = $('super-product-table').select('.input-text.qty');
                for (var i = 0; i < input.length; i++) {
                    var matches = input[i].name.match(/[[0-9]+]/);
                    if (matches.length) {
                        var productId = matches[0].substr(1, matches[0].length - 2);
                    }
                    if (productId && qtyProducts[productId]) {
                        input[i].value = qtyProducts[productId];
                    }
                }
            }
        }
    },
    changeQtyFromCurrentProduct : function(qtyCurrentProduct, buttonConf) {
        if (buttonConf.up('form')) {
            var part = buttonConf.up('form').id.split('_');
            var ruleId = part[part.length - 1];
            var configForUpdate = qtyCurrentProduct[ruleId];
            if ($('super-product-table')) {
                var input = $('super-product-table').select('.input-text.qty');
                for (var i = 0; i < input.length; i++) {
                    var matches = input[i].name.match(/[[0-9]+]/);
                    if (matches.length) {
                        var productId = matches[0].substr(1, matches[0].length - 2);
                    }
                    if (productId && configForUpdate[productId]) {
                        input[i].value = configForUpdate[productId];
                    }
                }
                if (typeof itorisGroupedProduct != 'undefined') {
                    var inputTextQty = $('super-product-table').select('.input-text.qty');
                    for (var i = 0; i < inputTextQty.length; i++) {
                        var acenter = 0;
                        for (var j = 0; j < $('super-product-table').select('.a-center').length; j++) {
                            if ($('super-product-table').select('.a-center')[j] == inputTextQty[i].up('td')) {
                                acenter = j;
                                break;
                            }
                        }
                        itorisGroupedProduct.displayOption(i, acenter);
                    }
                }
                Effect.ScrollTo($('super-product-table'));
            }
        }
    },
    changePrice : function () {
        $$('.itoris_groupedproductpromotions_form').each(function(form) {
            var part = form.id.split('_');
            var ruleId = parseInt(part[part.length - 1]);
            if (!this.pricesWithOptions[ruleId]) {
                this.pricesWithOptions[ruleId] = {};
            }
            form.select('.itoris_groupedproductpromotions_option').each(function(elm) {
                var part = elm.id.split('_');
                var productId = part[part.length - 1];
                for (var j = 0; j < elm.select('.product-custom-option').length; j++) {
                    Event.observe(elm.select('.product-custom-option')[j], 'change', function(e) {
                        e.stop();
                        var optionPrice = 0;
                        if (itorisGroupedPromoOptions[ruleId][productId]) {
                            itorisGroupedPromoOptions[ruleId][productId].reloadPrice();
                            var customPrices = eval('optionsPrice' + productId + '_' + ruleId).customPrices;
                            for (var key in customPrices) {
                                if (customPrices[key].price) {
                                    optionPrice += parseFloat(customPrices[key].price);
                                }
                            }
                            this.preparePrices(optionPrice, productId, ruleId);
                        }
                    }.bind(this));
                }
                for (var i = 0; i < elm.select('.super-attribute-select').length; i++) {
                    Event.observe(elm.select('.super-attribute-select')[i], 'change', function(e) {
                        e.stop();
                        if (itorisGroupedPromoOptions[ruleId][productId]) {
                            var optionPrice = itorisGroupedPromoOptions[ruleId][productId].reloadPrice();
                            this.preparePrices(optionPrice, productId, ruleId);
                        }
                    }.bind(this));
                }
                for (var j = 0; j < elm.select('.change-container-classname').length; j++) {
                    Event.observe(elm.select('.change-container-classname')[j], 'change', function(e) {
                        e.stop();
                        if (itorisGroupedPromoOptions[ruleId][productId]) {
                            var optionPrice = itorisGroupedPromoOptions[ruleId][productId].reloadPrice();
                            this.preparePrices(optionPrice, productId, ruleId);
                        }
                    }.bind(this));
                }
            }.bind(this));
        }.bind(this));
    },
    preparePrices : function(optionPrice, productId, ruleId) {
        var promoData = this.promoData[ruleId];
        var ruleData = this.promoData.rule[ruleId];
        var normalPriceTotal = 0;
        var newPromoPrice = 0;
        var normalPrice = 0;
        var promoPriceTotal = 0;
        for (var i = 0; i < promoData.length; i++) {
            var discountPrice = 0;
            if (promoData[i].product_id) {
                if(promoData[i].product_id == productId) { // add option
                    normalPrice = (promoData[i].price + optionPrice);
                    var discount = ruleData.price_method != 0 ? parseNumber(ruleData.discount_promoset) : parseNumber(promoData[i].discount);
                    if (ruleData.price_method == 0) {
                        if (!promoData[i].type_discount) { // 0 = fixed
                            newPromoPrice = normalPrice - discount;
                        } else { // 1 = percent
                            newPromoPrice = normalPrice * (1 - discount/100);
                        }
                    } else if (ruleData.price_method == 1) {
                        if (ruleData.code_promoset == 0) {// 1 = percent, 0 = fixed
                            newPromoPrice = promoData[i].price_promo + (normalPrice - promoData[i].price);
                        } else {
                            newPromoPrice = normalPrice * (1 - discount/100);
                        }
                    } else if (ruleData.price_method == 2) {
                        newPromoPrice = promoData[i].price_promo;
                    }
                    normalPriceTotal += normalPrice * promoData[i].qty;
                    promoPriceTotal += newPromoPrice * promoData[i].qty;
                    this.pricesWithOptions[ruleId][productId] = {
                        promo_price : newPromoPrice * promoData[i].qty,
                        normal_price : normalPrice * promoData[i].qty
                    };
                    discountPrice = normalPrice - newPromoPrice;
                    if ($('itoris_groupedproductpromo_' + ruleId + '_' + productId) && $('itoris_groupedproductpromo_' + ruleId + '_' + productId).up()
                        && $('itoris_groupedproductpromo_' + ruleId + '_' + productId).up().select('.itoris_groupedproductpromotions_discount')[0]
                    ) {
                        var productDiscountSpan = $('itoris_groupedproductpromo_' + ruleId + '_' + productId).up().select('.itoris_groupedproductpromotions_discount')[0];
                        if (promoData[i].qty > 1) {
                            productDiscountSpan.update(eval('optionsPrice' + productId + '_' + ruleId).formatPrice(discountPrice) + ' OFF each!');
                        } else {
                            productDiscountSpan.update(eval('optionsPrice' + productId + '_' + ruleId).formatPrice(discountPrice) + ' OFF!');
                        }
                    }
                } else {
                    if (this.pricesWithOptions[ruleId][promoData[i].product_id]) {
                        promoPriceTotal += this.pricesWithOptions[ruleId][promoData[i].product_id]['promo_price'];
                        normalPriceTotal += this.pricesWithOptions[ruleId][promoData[i].product_id]['normal_price'];
                    } else {
                        promoPriceTotal += promoData[i].price_promo * promoData[i].qty;
                        normalPriceTotal += promoData[i].price * promoData[i].qty;
                    }
                }
            }
        }
        promoPriceTotal = promoPriceTotal.toFixed(2);
        var newDiscount = normalPriceTotal - promoPriceTotal;
        $('itoris_promotions_product_addtocart_form_' + ruleId).select('td')[1].select('.itoris_groupedproductpromotions_pricevalue')[0].update(eval('optionsPrice' + productId + '_' + ruleId).formatPrice(normalPriceTotal));
        $('itoris_promotions_product_addtocart_form_' + ruleId).select('td')[1].select('.itoris_groupedproductpromotions_pricevalue')[1].update(eval('optionsPrice' + productId + '_' + ruleId).formatPrice(newDiscount));
        $('itoris_promotions_product_addtocart_form_' + ruleId).select('td')[1].select('.itoris_groupedproductpromotions_pricevalue')[2].update(eval('optionsPrice' + productId + '_' + ruleId).formatPrice(promoPriceTotal));
    }
});