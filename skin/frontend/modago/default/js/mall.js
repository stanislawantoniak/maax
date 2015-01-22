/**
 * Created by pawelchyl on 11.07.2014.
 */

var Mall = {
    _data: {},
    _product_template: '<tr><td class="thumb"><a href="{{url}}"><img src="{{image_url}}" alt=""></a></td><td class="desc"><p class="name_product"><a href="{{url}}">{{name}}</a></p><p class="size">{{attr_label}}:<span>{{attr_value}}</span></p><p class="quantity">ilość:<span>{{qty}}</span></p></td><td class="price">{{unit_price}} {{currency_symbol}}</td></tr>',
    _recently_viewed_item_template: '<div class="item"><a href="{{redirect_url}}" class="simple"><div class="box_listing_product"><figure class="img_product"><img src="{{image_url}}" alt="" /></figure><div class="name_product hidden-xs">{{title}}</div></div></a></div>',
    _summary_basket: '<ul><li>{{products_count_msg}}: {{all_products_count}}</li><li>{{products_worth_msg}}: {{total_amount}} {{currency_symbol}}</li><li>{{shipping_cost_msg}}: {{shipping_cost}}</li></ul><a href="{{show_cart_url}}" class="view_basket button button-primary medium link">{{see_your_cart_msg}}</a>',
    _delete_coupon_template: '<i class="fa-delete-coupon"></i>',
    _current_superattribute: null,

    extend: function(subclass, superclass) {
        function Dummy(){}
        Dummy.prototype = superclass.prototype;
        subclass.prototype = new Dummy();
        subclass.prototype.constructor = subclass;
        subclass.superclass = superclass;
        subclass.superproto = superclass.prototype;
    },
    replace: function(markup, data) {
        jQuery.each(data, function(key) {
            markup = markup.replace(new RegExp("\{\{" + key + "\}\}", "g"), typeof data[key] != "undefined" ? data[key] : "");
        });
        return markup;
    },
    price: function(v) {
        /* @todo locale... */
        var p = (Math.round(v * Math.pow(10, 2)) / Math.pow(10, 2) + "").split(".");

        if (p.length == 1) {
            p[1] = "00";
        } else if (p.length == 2 && p[1].length == 1) {
            p[1] += "0";
        }
        return p.join(",");
    },
    currency: function(price){
        return this.price(price) + " " + this.getCurrencyBasedOnCode();
    },
    // Registry object
    reg: {
        _data: {},
        get: function(id){
            return this._data[id];
        },
        set: function(id, data){
            this._data[id] = data;
        },
        unset: function(id){
            if(typeof this._data[id] != "undefined"){
                delete this._data[id];
            }
        }
    },
    checkLength: function(string) {
        var escapedStr = encodeURI(string);
        if (escapedStr.search("%") >= 0) {
            var count = escapedStr.split("%").length - 1;
            if (count === 0)
                count++;  //perverse case; can't happen with real UTF-8
            var tmp = escapedStr.length - (count * 3);
            count = count + tmp;
        } else {
            count = escapedStr.length;
        }
        return count;
    },

    // Registry trans
    _translations: {},
    __: function(s){
        if(typeof global == "object" && global.i18n && typeof global.i18n[s] == "string"){
            return global.i18n[s];
        }
        if(typeof this._translations[s] == "string"){
            return this._translations[s];
        }
        return s;
    },
    addTranslations: function(obj){
        jQuery.extend(this._translations, obj);
    },
    escapeRegExp: function(str) {
        return str.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
    },
    triggerError: function(error) {
        error = error || global.i18n["general_error"];
        new Mohito.Widget.PopUp({
            type: "flash",
            flashType: "error",
            message: error,
            autoopen: true
        });
    },
    getBaseUrl: function() {
        var pathArray = window.location.href.split( '/' );
        var protocol = pathArray[0];
        var host = pathArray[2];
        return protocol + '//' + host;
    },

    dispatch: function() {
        // fetch shopping cart and favourites informations
        this.getAccountInfo();
    },

    getAccountInfo: function() {
        jQuery.ajax({
            cache: false,
            dataType: "json",
            data: {
				"product_id": Mall.reg.get("varnish_product_id"),
				"category_id": Mall.reg.get("varnish_category_id")
			},
            error: function(jqXhr, status, error) {
                // do nothing at the moment
            },
            success: function(data, status) {
                Mall.buildAccountInfo(data, status);
            },
            url: "/orbacommon/ajax_customer/get_account_information"
        });
    },
    getIsBrowserMobile: function(){
        return jQuery.browser.device = (/android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini/i.test(navigator.userAgent.toLowerCase()));
    },
    buildAccountInfo: function(data, status) {
        // determine status
        if(data.status == false) {
            return;
        }
        Mall.setUserBlockData(data.content);
        if(data.content.cart.all_products_count == null) {
            data.content.cart.all_products_count = 0;
        }
        // set products count badge
        Mall.setFavoritesCountBadge(data.content.favorites_count);
        // set products count badge
        Mall.setProductsCountBadge(data.content.cart.all_products_count);
        var dropdownBasket = jQuery("#dropdown-basket");
        data.content.cart.total_amount = number_format(data.content.cart.total_amount, 2, ",", " ");
        data.content.cart.see_your_cart_msg = Mall.i18nValidation.__("see_your_cart_msg", "See your cart");
        data.content.cart.products_count_msg = Mall.i18nValidation.__("products_count_msg", "See your cart");
        data.content.cart.products_worth_msg = Mall.i18nValidation.__("products_worth_msg", "See your cart");
        data.content.cart.shipping_cost_msg = Mall.i18nValidation.__("shipping_cost_msg", "See your cart");
        jQuery("#dropdown-basket").find(".summary_basket").html(Mall.replace(Mall._summary_basket, data.content.cart));
//        dropdownBasket.html(Mall.replace(dropdownBasket.html(), data.content.cart));

        // build product list
        var products = data.content.cart.products == 0 ? [] : data.content.cart.products;
        // build object for filling products template
        Mall._data = data.content;
        // clear products
        jQuery("#product-list").html("");
        if(products.length == 0) {
            jQuery("#product-list").html('<p style="text-align: center;margin-top:20px;">Brak produktów w koszyku.</p>');
        } else {
            jQuery.each(products, function(key) {
                if(typeof products[key].options[0] != "undefined") {
                    products[key].attr_label = products[key].options[0].label;
                    products[key].attr_value = products[key].options[0].value;
                    products[key].currency_symbol = Mall._data.cart.currency_symbol;
                    products[key].unit_price = number_format(products[key].unit_price, 2, ",", " ");
                    jQuery("#product-list").append(Mall.replace(Mall._product_template, products[key]));
                }
            });
        }
        // replace favorites url
        jQuery("#link_favorites > a").attr("href", data.content.favorites_url);
		
		// add footer persistent infos
		var persistentContent = "";
		if(data.content.persistent){
			persistentContent = "<a href=\"" + data.content.persistent_url + "\">" + 
				Mall.i18nValidation.__("remove_my_data_from_this_device", "Remove my data from device") + 
				" <i class=\"fa fa-angle-right\"></i>" +
				"</a>";
		}
		jQuery("#persistent-forget-mobile,#persistent-forget-desktop").
				html(persistentContent);
		
		// Process search context
		var searchContext = jQuery(".search-context").html('');
		if(data.content.search && data.content.search.select_options){
			jQuery.each(data.content.search.select_options, function(){
				searchContext.append(jQuery("<option>").attr({
					"value": this.value,
					"selected": this.selected
				}).text(this.text));
			});
		}
		// Wood-based... how to replace opts?
		searchContext.selectbox("detach");
		searchContext.selectbox("attach");
		
		// Process product context 

		var likeBoxes = jQuery("#product-likeboxes");
		if(data.content.product && likeBoxes.length){
			var p = data.content.product, 
				likeText, boxAdded, boxNotAdded;
			
			// Not added box
			if(p.wishlist_count > 0){
				likeText = this.getFavPluralText(p.wishlist_count);
			}else{
				likeText = "";
			}
			boxNotAdded = jQuery(
				'<div class="addLike-box" id="notadded-wishlist">' + 
					'<span class="product-context-like-count">&nbsp;' + likeText + '</span>' +
					'<a href="#" onclick="Mall.wishlist.addToWishlistFromProduct('+p.entity_id+');return false;" class="addLike">' + 
						Mall.i18nValidation.__('add-to-br-favorites') +
					'</a>' + 
				'</div>');

			// Added box
			likeText = Mall.i18nValidation.__("you-like-this");
				
			if(p.wishlist_count > 1){
				likeText = this.getFavPluralText(p.wishlist_count - 1, true);
			}

			boxAdded = jQuery(
				'<div class="addedLike-box" id="added-wishlist">' + 
					'<a href="#" class="likeAdded" onclick="Mall.wishlist.removeFromWishlistFromProduct('+p.entity_id+');return false;">'+ 
					likeText +
					'<br><span>'  + Mall.i18nValidation.__("remove-from-favorites") + '</span>'+ 
					'</a>' + 
				'</div>');
			
			
			if(p.in_my_wishlist){
				boxAdded.removeClass("hidden");
				boxNotAdded.addClass("hidden");
			}else{
				boxNotAdded.removeClass("hidden");
				boxAdded.addClass("hidden");
			}
			
			likeBoxes.html('').
					append(boxNotAdded).
					append(boxAdded);
		}
		
    },                               
	
	getFavPluralText: function(count, you){
		var text = Mall.i18nValidation.__("people-polish-more-than-few");
		if(count==1){
			text = Mall.i18nValidation.__("person");
		}else if(count < 5){
			text = Mall.i18nValidation.__("people");
		}
		
		if(count > 1){
			text += ' ' + Mall.i18nValidation.__("likes-this-product");
		}else{
			if(you){
				text += ' ' + Mall.i18nValidation.__("like-this-product");
			}else{
				text += ' ' + Mall.i18nValidation.__("likes-this-product");
			}
		}
		
		return (you ? Mall.i18nValidation.__("you-and") + " " : "") + count  + " " + text;
	},

    setProductsCountBadge : function(count) {
        if(count == 0) {
            jQuery("#link_basket>a>span.badge").remove();
            jQuery("#link_basket").removeClass();
            jQuery("#link_basket").addClass("no-badge");
        } else {
            jQuery("#link_basket").removeClass();
            // check if badge exists
            if(jQuery("#link_basket>a>span.badge").length > 0) {
                // change only number in badge
                jQuery("#link_basket>a>span.badge").text(count);
            } else {
                jQuery("#link_basket>a>i").before('<span class="badge pull-right">'+ count + '</span>');
            }
        }
    },

    setFavoritesCountBadge : function(count) {
        if(count == 0) {
            jQuery("#link_favorites>a>span.badge").remove();
            jQuery("#link_favorites").removeClass();
            jQuery("#link_favorites").addClass("no-badge");
        } else {
            jQuery("#link_favorites").removeClass();
            // check if badge exists
            if(jQuery("#link_favorites>a>span.badge").length > 0) {
                // change only number in badge
                jQuery("#link_favorites>a>span.badge").text(count);
            } else {
                jQuery("#link_favorites>a>i").before('<span class="badge pull-right">'+ count + '</span>');
            }
        }
    },

    setUserBlockData : function(content) {
        var userBlock = jQuery("#header_top_block_right");
        // set customer account url
		
        var desktopW = 992;
        var windowW = jQuery(window).width();
        if(content.logged_in){
            //on load

            if(windowW < desktopW){
                //Tablet
                jQuery("#link_your_account>a, a#link_your_account_br").attr("href", content.user_account_url);
            } else {
                //Desktop
                jQuery("#link_your_account>a,a#link_your_account_br").attr("href", content.user_account_url_orders);
            }
            //on window resize
            jQuery( window ).resize(function() {
                var windowWidth = jQuery(this).width();
                if(windowWidth < desktopW){
                    //Tablet
                    jQuery("#link_your_account>a,a#link_your_account_br").attr("href", content.user_account_url);
                } else {
                    //Desktop
                    jQuery("#link_your_account>a,a#link_your_account_br").attr("href", content.user_account_url_orders);
                }
            });
        } else {
            jQuery("#link_your_account>a,a#link_your_account_br").attr("href", content.user_account_url);


            if(windowW < desktopW){
                //Tablet
                jQuery("[name=mobile_device_type]").val(1);
            } else {
                //Desktop
                jQuery("[name=mobile_device_type]").val(0);
            }
            //on window resize
            jQuery( window ).resize(function() {
                var windowWidth = jQuery(this).width();
                if(windowWidth < desktopW){
                    //Tablet
                    jQuery("[name=mobile_device_type]").val(1);
                } else {
                    //Desktop
                    jQuery("[name=mobile_device_type]").val(0);
                }
            });
        }


        // set basket url
        jQuery("#link_basket>a").attr("href", content.cart.show_cart_url);
        userBlock.show();
    },

    addToWishlist: function(id, context) {
        OrbaLib.Wishlist.add({product: id}, function(){
            context = context || "product";
            id = id || 0;
            if(arguments[0].status == true) {
                if(context == "product") {
                    // we are in product context
                    jQuery("#notadded-wishlist").hide();
                    jQuery("#added-wishlist").removeClass("hidden");
                    jQuery("#added-wishlist").show();
                    jQuery("#added-wishlist .product-context-like-count").first().html(parseInt(jQuery("#added-wishlist .product-context-like-count").text()) + 1);
                    jQuery("#notadded-wishlist .product-context-like-count").first().html(parseInt(jQuery("#added-wishlist .product-context-like-count").text()) - 1);
                } else {
                    if(id == 0) {
                        // we are in product context
                        jQuery("#notadded-wishlist").hide();
                        jQuery("#added-wishlist").removeClass("hidden");
                        jQuery("#added-wishlist").show();
                    } else {
                        var item = jQuery('div[data-idproduct="'+ id +'"]');
                        item.addClass("liked");
                        item.attr("data-status", 1);
                        item.find("span.like_count>span").html("Ty +");
                    }
                }
                Mall.buildAccountInfo(arguments[0], true);
            }
        });
    },

    removeFromWishlist: function(id, context) {
        OrbaLib.Wishlist.remove({product: id}, function(){
            context = context || "product";
            id = id || 0;
                if(arguments[0].status == true) {
                    if(context == "product") {
                        // we are in product context
                        jQuery("#notadded-wishlist").show().removeClass("hidden");
                        jQuery("#added-wishlist").hide();
                        jQuery("#added-wishlist .product-context-like-count").first().html(parseInt(jQuery("#added-wishlist .product-context-like-count").text()) - 1);
                        jQuery("#notadded-wishlist .product-context-like-count").first().html(parseInt(jQuery("#added-wishlist .product-context-like-count").text()) + 1);
                    } else {
                        if(id == 0) {
                            // we are in product context
                            jQuery("#notadded-wishlist").show();
                            jQuery("#added-wishlist").hide();
                        } else {
                            var item = jQuery('div[data-idproduct="'+ id +'"]');
                            item.removeClass("liked");
                            item.attr("data-status", 0);
                            item.find("span.like_count>span").html("");
                        }
                    }
                    Mall.buildAccountInfo(arguments[0], true);
                }
        });
    },

    toggleWishlist: function(item) {
        var status = jQuery(item).attr("data-status");
        var id = jQuery(item).attr("data-idproduct");
        if(status == 0) {
            Mall.addToWishlist(id, "small-box");
        } else {
            Mall.removeFromWishlist(id, "small-box");
        }
    },

    setSuperAttribute: function(currentSelection) {
        this._current_superattribute = currentSelection;
        // change prices
        var optionId = jQuery(this._current_superattribute).attr("value");
        var superOptionId = jQuery(this._current_superattribute).attr("data-id");
        jQuery.each(Mall.product._options.attributes[superOptionId].options, function(index, opt) {
            if(optionId == opt.id) {
                Mall.product.setPrices((parseFloat(Mall.product._options.basePrice) + parseFloat(opt.price)), (parseFloat(Mall.product._options.oldPrice) + parseFloat(opt.oldPrice)), Mall.product._options.template);
            }
            return ;
        });
    },

    addToCart: function(id, qty) {
        if(Mall._current_superattribute == null && Mall.product._current_product_type == "configurable") {
            return false;
        }
        var superLabel = jQuery(this._current_superattribute).attr("name");
        var attr = {};
        attr[jQuery(this._current_superattribute).attr("data-id")] = jQuery(this._current_superattribute).attr("value");
        OrbaLib.Cart.add({
            "product_id": id,
            "super_attribute": attr,
            "qty": qty
        }, addtocartcallback);
        return false;
    },

    showMessage: function(message, type) {
        switch(type) {
            case "success":
                alert(message);
                break;

            case "error":
                alert(message);
                break;

            case "notice":
                alert(message);
                break;
        }
    },

    getCurrencyBasedOnCode: function(code) {
        var currency = "zł";
        switch(code) {
            case "PLN":
                currency = "zł";
                break;
        }

        return currency;
    },

    getMallHeaderHeight: function () {
        "use strict";

        if (jQuery("#header")) {
            return jQuery("#header").outerHeight();
        }

        return 0;
    },

    getFormKey: function () {
        "use strict";

        var key = jQuery("input[name='form_key']").first();
        if (key.length > 0) {
            return key.val();
        }

        return "";
    },

    //transform postcode like: 99999, 99 999, 99/999, 99-999, 99_999
    //to our format: 99-999
    postcodeTransform: function(str) {
        var strTrans = str.replace(/\D/g,"");//remove spaces
        strTrans = strTrans.match(/.*?([0-9]{2}).?([0-9]{3}).*?/i);
        if (strTrans == null) {
            return str;
        } else {
            if (strTrans.length >= 1) {
                return strTrans[1] + "-" + strTrans[2];
            } else {
                return str;
            }
        }
    }

}


Mall.i18nValidation = {
    _translate_messages: {},
    add: function(key, translation) {
        this._translate_messages[key] = translation;
    },

    apply: function() {
        jQuery.extend(jQuery.validator.messages, this._translate_messages);
    },

    __: function(key, defaultMsg) {
        var msg = "";
        if(typeof this._translate_messages[key] != "undefined") {
            return this._translate_messages[key];
        } else {
            jQuery.each(this._translate_messages, function(index, value) {
                if(value == key) {
                    msg = value;
                    return msg;
                }
            });
        }

        return msg != "" ? msg : defaultMsg;
    }
};

Mall.translate = {};
jQuery.extend(Mall.translate, Mall.i18nValidation);
Mall.translate.ext = {
    __: function (key) {
        "use strict";
		
        if (this._translate_messages[key] === undefined) {
            return key;
        }

        return this._translate_messages[key];
    }
};

Mall.translate.__ = Mall.translate.ext.__;

// function that extends rwdCarousel
Mall.rwdCarousel = {
    findTallestItem: function(obj) {
        var height = 0;
        jQuery.each(obj.rwd.rwdItems, function() {
            if(this.clientHeight > height) {
                height = this.clientHeight;
            }
        })

        return height;
    },

    alignComplementaryProductsPrices: function(obj) {
        var tallestItem = this.findTallestItem(obj);
        var h = 0;
        var diff = 0;
        jQuery.each(obj.rwd.rwdItems, function() {

            if((h = this.clientHeight) < tallestItem) {
                diff = tallestItem - h;
                if (navigator.userAgent.indexOf('Safari') != -1 && navigator.userAgent.indexOf('Chrome') == -1) {
                    diff /= 2;
                }
                jQuery(this).find(".price").css("top", diff);
            }
        });
    }
};

Mall.Cart = {
    applyCoupon: function() {
        var coupon = jQuery("#num_discount_voucher").val();
        if(coupon == '') {
            OrbaLib.Cart.Coupon.remove({}, cart_remove_coupon_callback);
        } else {
            OrbaLib.Cart.Coupon.add({
                code: coupon
            }, cart_add_coupon_callback);
        }
    },

    removeCoupon: function() {
        jQuery("#num_discount_voucher").val("");
        Mall.Cart.applyCoupon();
    }
}

Mall.product = {
    _size_table_template: "",
    _options_group_template: "",
    _options: {},
    _current_product_type: "simple",

    productOptions: function(jsonOptions) {
        this._options = jsonOptions;
        // set prices
        this.setPrices(jsonOptions.basePrice, jsonOptions.oldPrice, jsonOptions.template);
        if(typeof jsonOptions.attributes != "undefined") {

            this.setAttributes(jsonOptions.attributes, jsonOptions.useSizeboxList);
        }
    },

    setChooseText: function(text) {

    },

    setPrices: function(price, oldPrice, template) {
        // set old price
        var old_price_selector = jQuery(".price-box").find(".old-price");
        var price_selector = jQuery(".price-box").find("span.price");
        if(price != oldPrice) {
            old_price_selector.html(template.replace("#{price}", number_format(oldPrice, "2", ",", " ")));
        } else {
            old_price_selector.html("");
        }

        // set price
        price_selector.html(template.replace("#{price}", number_format(price, "2", ",", " ")));
    },

    setAttributes: function(attributes, useSizeboxList) {
        this.clearAttributesContainer();

        jQuery.each(attributes, function(index, e) {
            Mall.product.createOptionGroup(e, useSizeboxList);
        });
    },

    clearAttributesContainer: function() {
        this._size_table_template = jQuery(".size-box").find("a.view-sizing")[0].outerHTML;
        jQuery(".size-box").find("div.size").remove();
    },

    applyAdditionalRules: function(optionGroup, selector) {
        if(optionGroup.code == "size") {
            selector.append(this._size_table_template);
        }
    },

    createOptionGroup: function(group, useSizeboxList) {
        if(!useSizeboxList) {
            // insert option group
            var groupElement = jQuery("<div/>", {
                "class": "size"
            }).appendTo(".size-box");
            jQuery(".size-box").append(this._options_group_template);
            // create label group
            jQuery("<span/>", {
                "class": "size-label",
                "html": (group.label + ":")
            }).appendTo(groupElement);

            // create form group for options
            var formGroupElement = jQuery("<div/>", {
                class: "form-group form-radio"
            }).appendTo(groupElement);

            jQuery.each(group.options, function(index, option) {
                Mall.product.createOption(group.id, option, formGroupElement);
            });

            this.applyAdditionalRules(group, formGroupElement);
        } else { //selectbox

            // insert option group
            var groupElement = jQuery("<div/>", {
                "class": "size"
            }).appendTo(".size-box");
            jQuery(".size-box").append(this._options_group_template);
            // create label group
            jQuery("<span/>", {
                "class": "size-label",
                "html": (group.label + ":")
            }).appendTo(groupElement);

            var deskTopDevice = !Mall.getIsBrowserMobile();

            // create form group for selectbox options
            var formGroupElementClass = (deskTopDevice) ? ' styledSelected scrollbar' : '';
            var formGroupElement = jQuery("<div/>", {
                class: "form-group" + formGroupElementClass
            }).appendTo(groupElement);

            //create select part
            var formGroupElementSelectClass = (deskTopDevice) ? 'form-control select-styled' : 'mobile-native-select';
            var formGroupElementSelect = jQuery("<select/>", {
                id: "select-data-id-"+group.id,
                class: formGroupElementSelectClass
            }).appendTo(formGroupElement);
            jQuery.each(group.options, function(index, option) {
                Mall.product.createOptionSelectbox(group.id, option, formGroupElementSelect);
            });
			
            this.applyAdditionalRules(group,formGroupElementSelect.parent()); // jQuery('div.size-box div.size'));
			jQuery('div.size-box div.size a').css('position','relative');
			jQuery('div.size-box div.size a').css('top','5px');
        }


    },

    createOption: function(id, option, groupElement) {
        var label = jQuery("<label/>", {
            "for": ("size_" + option.id),
            "class": option.is_salable == false ? "no-size" : ""
        }).appendTo(groupElement);
        var _options = {
            type: "radio",
            id: ("size_" + option.id),
            "data-id": id,
            name: ("super_attribute["+ id +"]"),
            value: option.id,
            onclick: "Mall.setSuperAttribute(this);"
        };

        if(!option.is_salable) {
            _options["disabled"] = "";
        }
        var optElement = jQuery("<input/>", _options).appendTo(label);
        jQuery("<span/>", {
            "html": option.label
        }).appendTo(label);
    },

    createOptionSelectbox: function(id, option, groupElement){
        if(!option.is_salable){
            return;
        }
        var option = jQuery("<option/>", {
            value: option.id,
            html: option.label,
            id: ("size_" + option.id),
            "data-id": id,
            name: ("super_attribute["+ id +"]"),
        }).appendTo(groupElement);
    },

    getLabelById: function(id, superId) {
        var label = "";
        if(this._options && typeof this._options.attributes[superId] != "undefined") {
            jQuery.each(this._options.attributes[superId].options, function(index, opt) {
                if(opt.id == id) {
                    label = opt.label;
                }this._current_total
            });
        }

        return label;
    },

    getTextWidth: function(elem, text, font){
        fakeElem = jQuery('<span>').hide().appendTo(document.body);
        fakeElem.text(text || elem.val() || elem.text()).css('font', font || elem.css('font'));
        var width = fakeElem.width();
        fakeElem.remove();
        return width;
    },

    setWidthSizeSquares: function(){;
        jQuery('.size-box label').each(function(){

            var wSizeLabel = jQuery(this).find('span').text().length;
            var wLabel = Mall.product.getTextWidth(jQuery(this).find('span')) + 10;
            if(wSizeLabel >= 4) {
                jQuery(this).closest('label').css({width:wLabel+ 'px'})
                jQuery(this).closest('label').children('span').css({width:wLabel+ 'px'})
            }
        })
    },

    setDiagonalsOnSizeSquare: function(){

        Mall.product.setWidthSizeSquares();

        var elFilterSize = jQuery('.size-box-bundle .form-group label');
        elFilterSize.each(function(){

            elFilterSizeWidth = jQuery(this).width();
            elFilterSizeHeight = jQuery(this).height();;
            obliczaniePrzekatnej = Math.pow(elFilterSizeWidth, 2) + Math.pow(elFilterSizeHeight, 2);
            przekatna = Math.sqrt(obliczaniePrzekatnej);
            obliczenieWyrownania = (przekatna - elFilterSizeWidth)/2;
            obliczenieWyrownaniaOryginal = obliczenieWyrownania + 2;

            var angle = Math.tan(elFilterSizeHeight/elFilterSizeWidth);

            if (elFilterSizeWidth > 31) {
                var angle = -(angle * (180 / Math.PI));
            } else {
                var angle = 135;
            }

            if (jQuery(this).hasClass('no-size')) {
                jQuery(this).find('span').append('<canvas class="diagonal" width="'+elFilterSizeWidth+'" height="'+elFilterSizeHeight+'"></canvas>');
            }

            jQuery(this).find('canvas').drawLine({
                strokeStyle: '#afafaf',
                strokeWidth: 1.5,
                x1: -1, y1: elFilterSizeHeight-1,
                x2: elFilterSizeWidth, y2: -1
            });
        });
    }

};

// callbacks

function addtocartcallback(response) {
    if(response.status == false) {
        Mall.showMessage(response.message, "error");
    } else {
        var popup = jQuery("#popup-after-add-to-cart");
        if(Mall.product._current_product_type == 'configurable') {
            var superAttr = jQuery(Mall._current_superattribute);
            var label = Mall.product.getLabelById(superAttr.val(), superAttr.attr("data-id"));
            popup.find("p.size>span").show();
            popup.find("p.size>span").html(label);
        } else {
            popup.find("p.size>span").hide();
        }
		popup.find("td.price").text(jQuery(".price-box-bundle span.price").text());
        jQuery("#popup-after-add-to-cart").modal('show');
        Mall.getAccountInfo();
    }
}

function number_format(number, decimals, dec_point, thousands_sep) {
    number = (number + '')
        .replace(/[^0-9+\-Ee.]/g, '');
    var n = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s = '',
        toFixedFix = function (n, prec) {
            var k = Math.pow(10, prec);
            return '' + (Math.round(n * k) / k)
                .toFixed(prec);
        };
    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n))
        .split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '')
        .length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1)
            .join('0');
    }
    return s.join(dec);
}

function cart_add_coupon_callback(response) {
    if(response.status == false) {
        // show message
        jQuery('.coupon-errors').html(response.message);
        //location.reload();
    } else {
        jQuery('.coupon-errors').html('');
        location.reload();
    }
}

function cart_remove_coupon_callback(response) {
    var type = response.status == true ? "success" : "error";
    location.reload();
}

function basket_dropdown() {
    jQuery(".basket-dropdown").hover(function() {
        var intFrameWidth = window.innerWidth;
        if(intFrameWidth > 991) {
            jQuery("#link_basket").addClass('open');
            jQuery("#dropdown-basket").show();
        }
    },function() {
        if (!jQuery(".basket-dropdown").is(":hover") || !jQuery("#link_basket").is(":hover") || !jQuery("#dropdown_basket").is(":hover")) {
            jQuery("#link_basket").removeClass('open');
            jQuery("#dropdown-basket").hide();
        }
    });
}

function sales_order_details_top_resize() {
    jQuery('.sales-order-top-row').each(function() {
        var block1 = jQuery(this).find('.sales-order-shipment');
        var block2 = jQuery(this).find('.sales-order-info');
        block1.css('height','');
        block2.css('height','');
        var intFrameWidth = window.innerWidth;
        if(intFrameWidth > 767) {
            var height = jQuery(this).height();
            block1.css('height', height + 'px');
            block2.css('height', height + 'px');
        }
    });
}
jQuery(window).resize(function() {
    sales_order_details_top_resize();
});

jQuery(document).ready(function() {
    Mall.dispatch();
    Mall.i18nValidation.apply();

    jQuery(".messages").find('span').append('<i class="fa fa-times"></i>');
    jQuery(".messages").find("i").bind('click', function() {
        var curentUL = jQuery(this).closest('ul');
        jQuery(this).parents("li").first().remove();

        if (jQuery(curentUL).find('li').length === 0) {
            jQuery(curentUL).parent().remove();
        }

        if(jQuery('.messages li').length === 0) {
            jQuery('#content').css('margin-top', '');
        }
    });
    if (jQuery('.messages i').length) {
        jQuery('#content').css('margin-top', '0px');
    }

    jQuery("#add-to-cart").tooltip({
        template: '<div class="tooltip top" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner" style="color: #ea687e"></div></div>'
    });
    jQuery("#add-to-cart").on('mouseover', function() {
        if(Mall._current_superattribute != null) {
            jQuery("#add-to-cart").tooltip('destroy');
        }
    });

    jQuery('#popup-after-add-to-cart').on('shown.bs.modal', function (e) {
        var backdrop =  jQuery('#sb-site').find('.modal-backdrop');
        if (backdrop.length == 0) {
            jQuery('#sb-site').append('<div class="modal-backdrop fade in"></div>');
        };

    });
    jQuery('#popup-after-add-to-cart').on('show.bs.modal', function (e) {
        jQuery('html').find('body > .modal-backdrop').remove();
    });
    jQuery('#popup-after-add-to-cart').on('hidden.bs.modal', function (e) {
        jQuery('html').find('.modal-backdrop').remove();

    });

    jQuery("#product-listing-sort-control").selectbox({
        onOpen: function (inst) {
//            initScrollBarFilterStyle();
        },

        onChange: function(value, inst) {
            location.href = value;
        }
    });
    //#######################
    //## SIZE-BOX -> SELECTBOX
    //#######################
    var deskTopDevice = !Mall.getIsBrowserMobile();

    if(deskTopDevice){
        jQuery(".size-box select").selectbox({
            mobile: true,
            onOpen: function (inst) {
                var uid = jQuery(this).attr('sb');
                var height = parseFloat(jQuery(".size-box li").first().css('line-height'));
                height += parseInt(jQuery(".size-box li a").first().css('padding-top'));
                height += parseInt(jQuery(".size-box li a").first().css('padding-bottom'));

                var n = jQuery(".size-box li").length;
                if( n < 4 ) {
                    height = n * height;
                    if (isNaN(height)) {
                        height = 29;//px
                    }
                    jQuery('.size-box .mCSB_scrollTools, .size-box .mCSB_1_scrollbar').css("visibility", "hidden");
                    jQuery('.size-box .mCSB_container').css("margin-right", "0px");
                } else {
                    height = 4 * height;
                }

                jQuery('.size-box #sbOptionsWrapper_' + uid).css('max-height', height);
                jQuery('.size-box #sbOptionsWrapper_' + uid).css('width', jQuery('.size-box .sbHolder').outerWidth());

            },

            onChange: function(value, inst) {
                Mall.setSuperAttribute(jQuery("#size_" + value));
            }
        });
    } else {
        jQuery(".size-box select").change(function () {
            Mall.setSuperAttribute(jQuery(this).find('option:selected'));
        })
    }


    if(jQuery(".size-box option").length == 1) {
        Mall.setSuperAttribute(jQuery("#size_" + jQuery(".size-box li a").first().attr('rel')));
    }
    if (jQuery('.size-box option').length >= 2) {
        jQuery('.size-box a.sbSelector').text(Mall.translate.__('Select size'));
    }



    //#######################
    //## END SIZE-BOX -> SELECTBOX
    //#######################

    Mall.product.setDiagonalsOnSizeSquare();

    basket_dropdown();
    sales_order_details_top_resize();

	jQuery(document)
		.on('show.bs.modal', '.modal', function () {
			jQuery('html,body').addClass('modal-open');
		})
		.on('hidden.bs.modal', '.modal', function () {
			jQuery('html,body').removeClass('modal-open');
		});
});''
