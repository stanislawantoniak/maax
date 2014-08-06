/**
 * Created by pawelchyl on 11.07.2014.
 */

var Mall = {
    _data: {},
    _product_template: '<tr><td class="thumb"><img src="{{image_url}}" alt=""></td><td class="desc"><p class="name_product">{{name}}</p><p class="size">{{attr_label}}:<span>{{attr_value}}</span></p><p class="quantity">ilość:<span>{{qty}}</span></p></td><td class="price">{{unit_price}} {{currency_symbol}}</td></tr>',
    _recently_viewed_item_template: '<div class="item"><a href="{{redirect_url}}" class="simple"><div class="box_listing_product"><figure class="img_product"><img src="{{image_url}}" alt="" /></figure><div class="name_product hidden-xs">{{title}}</div></div></a></div>',
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
        return this.price(price) + " " + global.i18n.currency;
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
            data: {},
            error: function(jqXhr, status, error) {
                // do nothing at the moment
            },
            success: function(data, status) {
                Mall.buildAccountInfo(data, status);
            },
            url: "/orbacommon/ajax_customer/get_account_information"
        });
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
        dropdownBasket.html(Mall.replace(dropdownBasket.html(), data.content.cart));

        // build product list
        var products = data.content.cart.products;
        // build object for filling products template
        Mall._data = data.content;
        // clear products
        jQuery("#product-list").html('');
        if(data.content.cart.all_products_count == 0) {
            jQuery("#product-list").html('<p style="text-align: center;margin-top:20px;">Brak produktów w koszyku.</p>');
        } else {
            jQuery.each(products, function(key) {
                if(typeof products[key].options[0] != "undefined") {
                    products[key].attr_label = products[key].options[0].label;
                    products[key].attr_value = products[key].options[0].value;
                    products[key].currency_symbol = Mall._data.cart.currency_symbol;
                    jQuery("#product-list").append(Mall.replace(Mall._product_template, products[key]));
                }
            });
        }

        // replace favorites url
        jQuery("#link_favorites > a").attr("href", data.content.favorites_url);
    },

    setProductsCountBadge : function(count) {
        if(count == 0) {
            jQuery("#link_basket>a.dropdown-toggle>span.badge").remove();
            jQuery("#link_basket").removeClass();
            jQuery("#link_basket").addClass("no-badge");
        } else {
            jQuery("#link_basket").removeClass();
            // check if badge exists
            if(jQuery("#link_basket>a.dropdown-toggle>span.badge").length > 0) {
                // change only number in badge
                jQuery("#link_basket>a.dropdown-toggle>span.badge").text(count);
            } else {
                jQuery("#link_basket>a.dropdown-toggle>i").before('<span class="badge pull-right">'+ count + '</span>');
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
        jQuery("#link_your_account>a").attr("href", content.user_account_url);
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
    },

    addToCart: function(id, qty) {
        var superLabel = jQuery(this._current_superattribute).attr("name");
        var attr = {};
        attr[jQuery(this._current_superattribute).attr("data-id")] = jQuery(this._current_superattribute).attr("value");
        OrbaLib.Cart.add({
            "product_id": id,
            "super_attribute": attr,
            "qty": qty
        }, addtocartcallback);
        return false;
    }

}

function addtocartcallback(response) {
    if(response.status == false) {
        alert(response.message);
    } else {
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

jQuery(document).ready(function() {
    Mall.dispatch();
});