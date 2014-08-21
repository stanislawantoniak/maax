/**
 * Created by pawelchyl on 11.07.2014.
 */

var Mall = {
    _data: {},
    _product_template: '<tr><td class="thumb"><img src="{{image_url}}" alt=""></td><td class="desc"><p class="name_product">{{name}}</p><p class="size">{{attr_label}}:<span>{{attr_value}}</span></p><p class="quantity">ilość:<span>{{qty}}</span></p></td><td class="price">{{unit_price}} {{currency_symbol}}</td></tr>',
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
            this.setAttributes(jsonOptions.attributes);
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

    setAttributes: function(attributes) {
        this.clearAttributesContainer();

        jQuery.each(attributes, function(index, e) {
            Mall.product.createOptionGroup(e);
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

    createOptionGroup: function(group) {
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
    },

    createOption: function(id, option, groupElement) {
        var label = jQuery("<label/>", {
            "for": ("size_" + option.id)
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

    getLabelById: function(id, superId) {
        var label = "";
        if(this._options && typeof this._options.attributes[superId] != "undefined") {
            jQuery.each(this._options.attributes[superId].options, function(index, opt) {
                if(opt.id == id) {
                    label = opt.label;
                }
            });
        }

        return label;
    }
};

Mall.listing = {
    _current_page: 1,

    _current_dir: "asc",

    _current_sort: "",

    _current_query: "",

    _current_scat: "",

    getMoreProducts: function() {
        var query = this.getQuery();
        var page = this.getPage();
        var sort = this.getSort();
        var dir = this.getDir();
        var scat = this.getScat();
        var filtersArray = this.getFiltersArray();

        OrbaLib.Listing.getProducts({
            q: query,
            page: page,
            sort: sort,
            dir: dir,
            scat: scat,
            fq: filtersArray
        }, Mall.listing.getMoreProductsCallback);
    },

    getMoreProductsCallback: function(data) {
        if(data.status == true) {
            var container = jQuery("#items-product").masonry();
            var sortArray = data.content.sort.split(" ");
            var items;
            Mall.listing.setPageIncrement();
            Mall.listing.setSort(sortArray[0]);
            Mall.listing.setDir(sortArray[1]);
            items = Mall.listing.appendToList(data.content.products);
            container.imagesLoaded(function() {
                container.masonry("appended", items);
                jQuery(".shapes_listing").css("top", jQuery(".addNewPositionListProduct").position().top - 180);
            });
        } else {
            // do something to inform customer that something went wrong
            alert("Something went wrong, try again");
            return false;
        }
    },

    appendToList: function(products) {
        var items = [];
        var _item;
        jQuery.each(products, function(index, item) {
            _item = Mall.listing.createProductEntity(item);
            jQuery("#items-product").find(".shapes_listing").before(_item);
            items.push(_item[0]);
        });

        // attach events
        this.attachEventsToProducts();

        return items;
    },

    createProductEntity: function(product) {
        var container = jQuery("<div/>", {
            class: "item col-phone col-xs-4 col-sm-4 col-md-3 col-lg-3 size14"
        });
        var box = jQuery("<div/>", {
            class: "box_listing_product"
        }).appendTo(container);

        var link = jQuery("<a/>", {
            href: product.current_url
        }).appendTo(box);

        var figure = jQuery("<figure/>", {
            class: "img_product"
        }).appendTo(link);

        jQuery("<img/>", {
            src: product.listing_resized_image_url,
            alt: product.name,
            class: "img-responsive"
        }).appendTo(figure);

        var vendor = jQuery("<div/>", {
            class: "logo_manufacturer"
        }).appendTo(link);

        jQuery("<img/>", {
            src: product.udropship_vendor_logo_url,
            alt: product.udropship_vendor_name
        }).appendTo(vendor);

        jQuery("<div/>", {
            class: "name_product",
            html: product.name
        }).appendTo(link);

        var priceBox = jQuery("<div/>", {
            class: "price clearfix"
        }).appendTo(link);

        var colPrice = jQuery("<div/>", {
            class: "col-price"
        }).appendTo(priceBox);

        if(product.price != product.final_price) {
            jQuery("<span/>", {
                class: "old",
                html: number_format(product.price, 2, ",", " ") + " " + Mall.getCurrencyBasedOnCode(product.currency)
            }).appendTo(colPrice);
        }
        jQuery("<span/>", {
            html: number_format(product.final_price, 2, ",", " ") + " " + Mall.getCurrencyBasedOnCode(product.currency)
        }).appendTo(colPrice);

        var likeClass = "like";
        var likeText = "<span></span>";
        if(product.in_my_wishlist) {
            likeClass += " liked";
            likeText = "<span>Ty+</span>";
        }
        var like = jQuery("<div/>", {
            class: likeClass,
            "data-idproduct": product.entity_id,
            "data-status": product.in_my_wishlist,
            onclick: "Mall.toggleWishlist(this);"
        }).appendTo(priceBox);

        var likeIco = jQuery("<span/>", {
            class: "icoLike"
        }).appendTo(like);

        jQuery("<img/>", {
            src: Config.path.heartLike,
            class: "img-01",
            alt: ""
        }).appendTo(likeIco);

        jQuery("<img/>", {
            src: Config.path.heartLiked,
            alt: "",
            class: "img-02"
        }).appendTo(likeIco);

        jQuery("<span/>", {
            class: "like_count",
            html: likeText + product.wishlist_count
        }).appendTo(like);

        jQuery("<div/>", {
            class: "toolLike"
        }).appendTo(like);

        return container;
    },

    attachEventsToProducts: function() {

        var itemProduct = jQuery('.box_listing_product');
        itemProduct.on('click', '.like', function(event) {
            event.preventDefault();
            /* Act on the event */
            var itemProductId = jQuery(this).data('idproduct');
        });
        itemProduct.on('mouseenter', '.like', function(event) {
            event.preventDefault();
            if (jQuery(this).hasClass('liked')) {
                var textLike = 'Dodane do ulubionych';
            } else {
                var textLike = 'Dodaj do ulubionych';
            };
            jQuery(this).find('.toolLike').show().text(textLike);
        });
        itemProduct.on('mouseleave mouseup', '.like', function(event) {
            event.preventDefault();
            jQuery(this).find('.toolLike').hide().text('');
        });
        itemProduct.on('mousedown', '.like', function(event) {
            event.preventDefault();
            jQuery(this).find('img:visible').animate({transform: 'scale(1.2)'}, 200);
        });
        itemProduct.on('mouseup', '.like', function(event) {
            event.preventDefault();
            jQuery(this).find('img:visible').animate({transform: 'scale(1.0)'}, 200)
        });
        itemProduct.on('mousedown', '.liked', function(event) {
            event.preventDefault();
            var textLike = 'Usunięte z ulubionych';
            jQuery(this).find('.toolLike').show().text(textLike);
        });
    },

    getQuery: function() {
        return this._current_query;
    },

    getPage: function() {
        return this._current_page + 1;
    },

    getSort: function() {
        return this._current_sort;
    },

    getDir: function() {
        return this._current_dir;
    },

    getScat: function() {
        return this._current_scat;
    },

    getFiltersArray: function() {
        return {};
    },

    setPageIncrement: function() {
        this._current_page += 1;
    },

    setDir: function(dir) {
        this._current_dir = dir;
    },

    setSort: function(sort) {
        this._current_sort = sort;
    },

    setQuery: function(query) {
        this._current_query = query;
    },

    setScat: function(scat) {
        this._current_scat = scat;
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
        Mall.showMessage(response.message, "error");
    } else {
        // show message and reload the page
        Mall.showMessage(response.content.message, "success");
        location.reload();
    }
}

function cart_remove_coupon_callback(response) {
    var type = response.status == true ? "success" : "error";
    Mall.showMessage(response.content.message, type);
    location.reload();
}

jQuery(document).ready(function() {
    Mall.dispatch();
    Mall.i18nValidation.apply();

    jQuery(".messages").find('span').append('<i class="fa fa-times"></i>');
    jQuery(".messages").find("i").bind('click', function() {
        jQuery(this).parents("li").first().hide();
    });

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
});