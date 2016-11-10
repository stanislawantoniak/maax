
/**
 * Created by pawelchyl on 11.07.2014.
 */

var Mall = {
    _data: {},
    _product_template: '<tr id="product-{{number}}"><td class="thumb"><a href="{{url}}"><img src="{{image_url}}" alt=""></a></td><td class="desc"><p class="name_product"><a href="{{url}}">{{name}}</a></p><p class="size">{{attr_label}}:<span>{{attr_value}}</span></p><p class="quantity">ilość:<span>{{qty}}</span></p></td><td class="price">{{unit_price}} {{currency_symbol}}</td></tr>',
    _recently_viewed_item_template: '<div class="item"><a href="{{redirect_url}}" class="simple"><div class="box_listing_product"><figure class="img_product"><img src="{{image_url}}" alt="" /></figure><div class="name_product hidden-xs">{{title}}</div></div></a></div>',
    _summary_basket: '<ul><li>{{products_count_msg}}: {{all_products_count}}</li><li>{{products_worth_msg}}: {{total_amount}} {{currency_symbol}}</li><li>{{shipping_cost_msg}}: {{shipping_cost}}</li></ul><a href="{{show_cart_url}}" class="view_basket button button-primary medium link">{{see_your_cart_msg}}</a>',
    _delete_coupon_template: '<i class="fa-delete-coupon"></i>',
    _current_superattribute: null,
    _size_label: null,
    _query_text: '',
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

	noAccountInfo: ['/checkout/guest/index/','/checkout/singlepage/index/'],
	dispatch: function() {
		// fetch shopping cart and favourites informations
		if(jQuery.inArray(this.getUrlPath(),Mall.noAccountInfo) == -1) {
			this.getAccountInfo();
		}
	},

    getAccountInfo: function() {
	    var recentlyViewed = jQuery('#rwd-recently-viewed');
        jQuery.ajax({
            cache: false,
            dataType: "json",
            data: {
				"product_id": Mall.reg.get("varnish_product_id"),
				"category_id": Mall.reg.get("varnish_category_id"),
	            "recently_viewed": recentlyViewed.find('.rwd-carousel').length && !recentlyViewed.find('.rwd-wrapper').length ? 1 : 0,
                "crosssell_ids": jQuery('#complementary_product .like').map(function() {
                    return jQuery(this).attr("data-idproduct");
                }).get(),
	            "utm_data": Mall.reg.get('utm_data')
			},
            error: function(jqXhr, status, error) {
                // do nothing at the moment
            },
            success: function(data, status) {
                Mall.buildAccountInfo(data, status);
            },
            url: "/orbacommon/ajax_customer/get_account_information"
            ,type: "POST"
        });
    },

	getSalesManagoInfo: function () {
		jQuery.ajax({
			cache: false,
			error: function (jqXhr, status, error) {
				// do nothing at the moment
			},
			success: function (data, status) {
				if (typeof initSalesManagoScript == "function") {
					initSalesManagoScript();
				}
			},
			url: "/orbacommon/ajax_customer/get_salesmanago_information",
			type: "POST"
		});
	},

    getIsBrowserMobile: function(){
        //return jQuery.browser.device = (/android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini/i.test(navigator.userAgent.toLowerCase()));
        var mobBrowser = false;
        /* http://detectmobilebrowsers.com/ */
        (function(a,b){if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4)))mobBrowser=true;})(navigator.userAgent||navigator.vendor||window.opera,'');

        return mobBrowser;
    },
	hideBasketLoading: function() {
		jQuery('#dropdown-basket').find('.loading_basket').hide();
	},
	showBasketContent: function() {
		jQuery('#dropdown-basket').find('.summary_basket').show();
	},
    buildAccountInfo: function(data, status) {
        // determine status
        if(data.status == false) {
            return;
        }

	    if(typeof data.content.visitor_data != 'undefined') {
		    Mall.Gtm.visitorData(data.content.visitor_data);
	    }

        Mall.setUserBlockData(data.content);
        if(data.content.cart.all_products_count == null) {
            data.content.cart.all_products_count = 0;
        }
        // set products count badge
        Mall.setFavoritesCountBadge(data.content.favorites_count);
        // set products count badge
        Mall.setProductsCountBadge(data.content.cart.all_products_count);
	    //hide basket loading
	    Mall.hideBasketLoading();

	    //and show basket content
	    Mall.showBasketContent();

        var dropdownBasket = jQuery("#dropdown-basket");
        data.content.cart.total_amount = number_format(data.content.cart.total_amount, 2, ",", " ");
        data.content.cart.see_your_cart_msg = Mall.i18nValidation.__("see_your_cart_msg", "See your cart");
        data.content.cart.products_count_msg = Mall.i18nValidation.__("products_count_msg", "See your cart");
        data.content.cart.products_worth_msg = Mall.i18nValidation.__("products_worth_msg", "See your cart");
        data.content.cart.shipping_cost_msg = Mall.i18nValidation.__("shipping_cost_msg", "See your cart");
		data.content.cart.show_cart_url = dropdownBasket.find(".summary_basket a").attr('href');
	    dropdownBasket.find(".summary_basket").html(Mall.replace(Mall._summary_basket, data.content.cart));
//        dropdownBasket.html(Mall.replace(dropdownBasket.html(), data.content.cart));

        // build product list
        var products = data.content.cart.products == 0 ? [] : data.content.cart.products;
       
        // build object for filling products template
        Mall._data = data.content;
        // clear products
	    var productList = jQuery("#product-list");
	    productList.html("");
        if(products.length == 0) {
	        productList.html('<p style="text-align: center;margin-top:20px;">' + Mall.i18nValidation.__("No products in basket.", "No products in basket.") + '</p>');
        } else {
            jQuery.each(products, function(key) {
                if(typeof products[key].options != "undefined") {
                    var isSimpleProduct = typeof products[key].options[0] == "undefined";
                    products[key].attr_label = isSimpleProduct ? '' : products[key].options[0].label;
                    products[key].attr_value = isSimpleProduct ? '' : products[key].options[0].value;
                    products[key].currency_symbol = Mall._data.cart.currency_symbol;
                    products[key].unit_price = number_format(products[key].unit_price, 2, ",", " ");
                    products[key].number = key;
	                productList.append(Mall.replace(Mall._product_template, products[key]));
                    if (isSimpleProduct) {
                        //fix for size attr in template ( left ":" )
                        jQuery('#product-' + key + ' .size', productList).html('');
                    }
                }
            });
        }

        // add footer persistent infos
        //but not in checkout
        if (!jQuery('body').hasClass('checkout')) {
            var persistentContent = "";
            if (data.content.persistent) {
                persistentContent = "<a href=\"" + Mall.reg.get('persistent_url') + "\">" +
                Mall.i18nValidation.__("remove_my_data_from_this_device", "Remove my data from device") +
                " <i class=\"fa fa-angle-right\"></i>" +
                "</a>";
            }
            jQuery("#persistent-forget-mobile,#persistent-forget-desktop").
                html(persistentContent);
        }

		// Process search context
		var searchContext = jQuery("select[name=scat]").html('');
		if(data.content.search && data.content.search.select_options){
			jQuery.each(data.content.search.select_options, function(){
				searchContext.append(jQuery("<option>").attr({
					"value": this.value,
					"selected": this.selected
				}).text(this.text));
			});
		}
        jQuery('select[name=scat]').selectBoxIt({
            autoWidth: false
        });
        // Update current search query text
        jQuery("#header input[name=q]").val(this.getQueryText());

		// Process product context 


		var likeBoxes = jQuery(".product-likeboxes");

		if(data.content.product && likeBoxes.length){
			var p = data.content.product, 
				likeText, boxAdded, boxNotAdded, boxLoading;
			
			// Not added box
			if(p.wishlist_count > 0){
				//likeText = this.getFavPluralText(p.wishlist_count);
			}else{
				likeText = "";
			}
			boxNotAdded = jQuery(
				'<div class="addLike-box" id="notadded-wishlist">' +
					'<a href="#" onclick="Mall.wishlist.addToWishlistFromProduct('+p.entity_id+');return false;" class="addLike"><i class="fa fa-list" aria-hidden="true"></i>' +
						Mall.i18nValidation.__('add-to-favorites') +
					'</a>' +
				'</div>');

			// Added box
			//likeText = Mall.i18nValidation.__("you-like-this");
				
			if(p.wishlist_count > 1){
				//likeText = this.getFavPluralText(p.wishlist_count - 1, true);
			}

			boxAdded = jQuery(
				'<div class="addedLike-box" id="added-wishlist">' + 
					'<a href="#" class="likeAdded" onclick="Mall.wishlist.removeFromWishlistFromProduct('+p.entity_id+');return false;"><i class="fa fa-list" aria-hidden="true"></i>'+
					 Mall.i18nValidation.__("added-to-shopping-list") +
					'</a>' +
				'</div>');

			boxLoading = jQuery(
				'<div class="addingLike-box" id="adding-wishlist">' +
					'<i class="fa fa-spinner fa-spin fa-2x"></i>' +
				'</div>'
			);
			
			
			if(p.in_my_wishlist){
				boxAdded.removeClass("hidden");
				boxNotAdded.addClass("hidden");
				boxLoading.addClass("hidden");
			}else{
				boxNotAdded.removeClass("hidden");
				boxAdded.addClass("hidden");
				boxLoading.addClass("hidden");
			}
			
			likeBoxes.html('').
					append(boxNotAdded).
					append(boxAdded).
					append(boxLoading);
		}

        // Crosssell in_my_wishlist update for Varnish
        if (data.content.hasOwnProperty('crosssell')) {
            var c = data.content.crosssell;
            for (i = 0; i < c.length; i++) {
                if (c[i].in_my_wishlist) {
                    jQuery("#complementary_product .like[data-idproduct='" + c[i].entity_id + "']")
                        .addClass(' liked ')
                        .attr('data-status', 1)
                        .attr('onclick', 'Mall.wishlist.removeFromSmallBlock(this);return false;');
                    if (p = Mall.wishlist.getProduct(c[i].entity_id)) {
                        p.in_your_wishlist = "true";
                    }
                }
            }
        }


		if(typeof data.content.recently_viewed != 'undefined' && data.content.recently_viewed.length) {
			var rwd_recently_viewed = jQuery("#rwd-recently-viewed .rwd-carousel");
            if ( rwd_recently_viewed.length !=0 ) {

            	var recentlyViewedContent = "";

            	for(var i in data["content"]["recently_viewed"]){
            		var item = data["content"]["recently_viewed"][i];
            		var redirect_url = item.redirect_url;
            		var image_url    = item.image_url;
            		var title        = item.title;
            		var price        = item.price;
                    var old_price    =  (item.old_price !== undefined) ? ("<span class='old-price'>" + item.old_price + "</span>") : '';

            		recentlyViewedContent += "<a href='"+redirect_url+"' class='simple'>";
            		recentlyViewedContent += "<div class='container-fluid'>";
            		recentlyViewedContent += " <div class='row'>";
            		recentlyViewedContent += "  <div class='left-col'>";
            		recentlyViewedContent += "   <figure>";
            		recentlyViewedContent += "    <img src='"+ image_url +"' class='img-responsive-slider'>";
            		recentlyViewedContent += "   </figure>";
            		recentlyViewedContent += "  </div>";
            		recentlyViewedContent += "  <div class='col-lg-8 col-md-8 col-sm-8 col-xs-6 right-col no-gutter'>";
            		recentlyViewedContent += "   <div class='prod_name'>"+ title +"<div class='prod_name_fade'></div></div>";
            		recentlyViewedContent += "   <span class='wrapper-price'>";
                    recentlyViewedContent += old_price
            		recentlyViewedContent += "    <span class='price'>"+price+"</span>"
            		recentlyViewedContent += "   </span>";
            		recentlyViewedContent += "  </div>";
            		recentlyViewedContent += " </div>";
            		recentlyViewedContent += "</div>";
            		recentlyViewedContent += "</a>";
            	}
            	jQuery(rwd_recently_viewed).html(recentlyViewedContent);
            	jQuery("#recently-viewed.recently-viewed-cls").show();

            	rwd_recently_viewed.rwdCarousel({
            		items : 3,
            		itemsCustom : [
                        [1000,3],
                        [800,2],
                        [670,2],
                        [320,1]
                    ],
            		itemsDesktop : [1000,2],
            		itemsDesktopSmall : [800,2],
            		itemsTablet: [670,1],
            		pagination : false,
            		navigation: true,
            		rewindNav : false,
            		itemsScaleUp:false,
            		navigationText : false,
            		afterUpdate: function(){
            			var imgHeight = rwd_recently_viewed.find('img').height();
            			var imgHeightplus = rwd_recently_viewed.find('img').height();
            			rwd_recently_viewed.next('.customNavigation').find('.prev').css({top:imgHeight+'px'});
            			rwd_recently_viewed.next('.customNavigation').find('.next').css({top:imgHeight+'px'});
            			rwd_recently_viewed.find('.rwd-controls').find('.rwd-prev').css({top:imgHeightplus+'px'});
            			rwd_recently_viewed.find('.rwd-controls').find('.rwd-next').css({top:imgHeightplus+'px'});
            		},
            		afterInit:function(){
            			imagesLoaded( document.querySelector('#rwd-recently-viewed'), function( instance ) {
            				var imgHeight = rwd_recently_viewed.find('img').height();
            				var imgHeightplus = rwd_recently_viewed.find('img').height();
            				rwd_recently_viewed.next('.customNavigation').find('.prev').css({top:imgHeight+'px'});
            				rwd_recently_viewed.next('.customNavigation').find('.next').css({top:imgHeight+'px'});
            				rwd_recently_viewed.find('.rwd-controls').find('.rwd-prev').css({top:imgHeightplus+'px'});
            				rwd_recently_viewed.find('.rwd-controls').find('.rwd-next').css({top:imgHeightplus+'px'});
            			});
            		}
            	});

            	// Custom Navigation Events
            	var rwdRecentlyViewed = jQuery("#rwd-recently-viewed");
            	rwdRecentlyViewed.find(".next").click(function(){
            		rwd_recently_viewed.trigger('rwd.next');
            	});
            	rwdRecentlyViewed.find(".prev").click(function(){
            		rwd_recently_viewed.trigger('rwd.prev');
            	});
            }
		}

        // Customer info for contact form in product page
        if (data.content.logged_in && data.content.customer_email) {
            Mall.product.updateQuestionFormForLoggedIn(data.content.customer_name, data.content.customer_email);
        }
        // When customer send question, form need to be populated when error
        if (data.content.data_populate) {
            Mall.product.populateQuestionForm(data.content.data_populate.customer_name, data.content.data_populate.customer_email, data.content.data_populate.question_text);
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
	    var newClass = count > 9 ? 'badgeDouble' : (count > 99 ? 'badgeTriple' : 'badgeSingle');
	    count = count == 0 ? "" : (count > 99 ? "99+" : count);
	    var badge = jQuery("#link_basket>a>div>span.badge");
	    badge.removeClass('badgeDouble badgeTriple badgeSingle').addClass(newClass).text(count);
    },

    setFavoritesCountBadge : function(count) {
	    var newClass = count > 9 ? 'badgeDouble' : (count > 99 ? 'badgeTriple' : 'badgeSingle');
	    count = count == 0 ? "" : (count > 99 ? "99+" : count);
	    var badge = jQuery("#link_favorites>a>div>span.badge");
	    badge.removeClass('badgeDouble badgeTriple badgeSingle').addClass(newClass).text(count);
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
                jQuery("#link_your_account>a,a#link_your_account_br").attr("href", jQuery("#link_your_account>a").attr('data-user-account-url'));
            } else {
                //Desktop
                jQuery("#link_your_account>a,a#link_your_account_br").attr("href", jQuery("#link_your_account>a").attr('data-user-account-url-orders'));
            }
            //on window resize
            jQuery( window ).resize(function() {
                var windowWidth = jQuery(this).width();
                if(windowWidth < desktopW){
                    //Tablet
                    jQuery("#link_your_account>a,a#link_your_account_br").attr("href", jQuery("#link_your_account>a").attr('data-user-account-url'));
                } else {
                    //Desktop
                    jQuery("#link_your_account>a,a#link_your_account_br").attr("href", jQuery("#link_your_account>a").attr('data-user-account-url'));
                }
            });
        } else {
            jQuery("#link_your_account>a,a#link_your_account_br").attr("href", jQuery("#link_your_account>a").attr('data-user-account-url'));


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
		jQuery("#add-to-cart").tooltip('destroy');
        this._current_superattribute = currentSelection;
        // change prices
        var optionId = jQuery(this._current_superattribute).attr("value");
        var superOptionId = jQuery(this._current_superattribute).attr("data-superattribute");
        jQuery.each(Mall.product._options.attributes[superOptionId].options, function(index, opt) {
            if(optionId == opt.id) {
                Mall.product.setPrices((parseFloat(Mall.product._options.basePrice) + parseFloat(opt.price)), (parseFloat(Mall.product._options.oldPrice) + parseFloat(opt.oldPrice)), Mall.product._options.template);
            }
            return ;
        });
    },

    validateAddingToCart: function(id){
        jQuery("#qty-error").hide();
        var selectedSizeOption = jQuery(".sizes-content select option:selected");
        var quantity =  parseInt(jQuery("#product-options input[name=quantity]").val());
        var attrId;
        var defaultText = jQuery(".size-box select").selectBoxIt().data("selectBox-selectBoxIt").options.defaultText;
        if(Mall.getIsBrowserMobile() && jQuery(".size-box .selectboxit-text").text() != defaultText){
            attrId = jQuery(".sizes-content select option:selected").val();
        } else {
            attrId = jQuery(".size-box ul.selectboxit-options li.selectboxit-focus").attr('data-val');
        }

        if(!attrId){
            return false;
        }

        var maxQty = parseInt(selectedSizeOption.attr('maxqty'));
        var minQty = parseInt(selectedSizeOption.attr('minqty'));


        if(quantity >= minQty && quantity <= maxQty && quantity > 0){
            Mall.addToCart(id, quantity);
        }else{
            jQuery("#qty-error").show();
        }
        return false;
    },

    validateSimpleAddingToCart: function(id, minQty, maxQty, productType){
        jQuery("#qty-error").hide();
        var quantity =  jQuery("#product-options input[name=quantity]").val();
        if((quantity >= minQty && quantity <= maxQty && quantity > 0) || productType == 'bundle'){
            Mall.addToCart(id, quantity);
        }else{
            jQuery("#qty-error").show();
        }
        return false;
    },

    validateAddingToCartListing: function(id, minQty, maxQty){
		var elem = jQuery("#input-box-" + id);
        var quantity =  elem.find("input[name=quantity]").val();
		var productType = elem.parents(".box_listing_product").find("a:eq(0)").attr("data-product-type");
		// skip validation for bundle product
        if((quantity >= minQty && quantity <= maxQty) || productType == 'bundle' ){
            Mall.addToCartListing(id, quantity);
        }else{
			elem.tooltip('show');
        }
        return false;
    },

    addToCart: function(id, qty) {
        if(Mall._current_superattribute == null && Mall.product._current_product_type == "configurable") {
            return false;
        }
        jQuery('#full-width-popup-table .quantity span').text('');
        var superLabel = jQuery(this._current_superattribute).attr("name");
        var attr = {};
        attr[jQuery(this._current_superattribute).attr("data-superattribute")] = jQuery(this._current_superattribute).attr("value");
	    var popup = jQuery("#popup-after-add-to-cart");
	    popup.find(".modal-error").hide();
	    popup.find(".modal-loaded").hide();
	    popup.find(".modal-loading").show();
	    popup.modal('show');
	    popup.css('pointer-events','none');
	    jQuery('#add-to-cart').css('pointer-events','none');
	    jQuery('#full-width-popup-table .quantity span').text(qty);
        OrbaLib.Cart.add({
            "product_id": id,
            "super_attribute": attr,
            "qty": qty
        }, addtocartcallback);
        return false;
    },

    addToCartListing: function(id, qty) {
        Mall.product = {_current_product_type:'simple'};
        var attr = {undefined: undefined};

        jQuery("#popup-after-add-to-cart .name_product").html(jQuery('[data-entity=' + id + '] .name_product').html());
        jQuery("#popup-after-add-to-cart .thumb img").attr("src", jQuery('[data-entity=' + id + '] img').attr("src"));
        jQuery("#popup-after-add-to-cart .quantity span").html(jQuery('#input-box-' + id + ' input').val());

        var popup = jQuery("#popup-after-add-to-cart");
        popup.find(".modal-error").hide();
        popup.find(".modal-loaded").hide();
        popup.find(".modal-loading").show();
        popup.modal('show');
        popup.css('pointer-events','none');
        jQuery('#add-to-cart').css('pointer-events','none');
        OrbaLib.Cart.add({
            "product_id": id,
            "super_attribute": attr,
            "qty": qty
        }, addtocartlistingcallback.bind({id: id}));
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
	    var header = jQuery("#header");

        if (header.length) {
            return header.outerHeight();
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
        if(typeof str === "undefined"){
            return "";
        }
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
    },

    disableSearchNoQuery: function() {
        jQuery('#header_top_block_left form, #dropdown-search form').submit(function(e) {
            if(!jQuery.trim(jQuery('input[name=q]', this).val()).length) {
                e.preventDefault();
            }
        });
    },

    setQueryText: function(q) {
        this._query_text = q;
    },

    getQueryText: function() {
        return this._query_text;
    }
};


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
        });

        return height;
    },

    alignComplementaryProductsPrices: function(obj) {
        //var tallestItem = this.findTallestItem(obj);
        //var h = 0;
        //var diff = 0;
        //jQuery.each(obj.rwd.rwdItems, function() {
        //
        //    if((h = this.clientHeight) < tallestItem) {
        //        diff = tallestItem - h;
        //        if (navigator.userAgent.indexOf('Safari') != -1 && navigator.userAgent.indexOf('Chrome') == -1) {
        //            diff /= 2;
        //        }
        //        jQuery(this).find(".price").css("top", diff);
        //    }
        //});
    }
};

Mall.Breakpoint = {
	xs: 480,
	xssm: 600,
	sm: 768,
    smmd: 810,
	md: 992,
	lg: 1200
};

Mall.isGoogleBot = function() {
	return jQuery('body').hasClass('googlebot');
};

Mall.isMobile = function(breakpoint) {
    if (breakpoint) {
        return Mall.windowWidth() < breakpoint;
    } else {
        return Mall.windowWidth() < Mall.Breakpoint.sm;
    }
};

Mall.windowWidth = function() {
	return window.innerWidth;
};

Mall.windowHeight = function() {
	return window.innerHeight;
};

// http://kenwheeler.github.io/slick/
Mall.Slick = {
	events: {
		afterChange: 'afterChange',
		beforeChange: 'beforeChange',
		edge: 'edge',
		init: 'init',
		reInit: 'reInit',
		setPosition: 'setPosition',
		swipe: 'swipe'
	},
	init: function() {
		Mall.Slick.top.init();
		Mall.Slick.boxes.init();
	},
	sliderAvailable: function(sliderId) {
		return jQuery(sliderId).length > 0;
	},
	unslick: function(slickChild) {
		slickChild.slider = false;
		jQuery(slickChild.sliderId).slick('unslick');
	},
	top: {
		slider: false,
		sliderId: '#topSlider',
		options: {
			autoplaySpeed: 4000,
			arrows: false,
			dots: true,
			adaptiveHeight: true
		},
		init: function() {
			var _ = this;
			if(_.slider === false && _.sliderAvailable()) {
				_.slider = jQuery(_.sliderId);
				if(!Mall.isMobile()) {
					_.options.autoplay = true;
				}
				_.slider.slick(_.options);
				_.attachEvents();
			}
		},
		sliderAvailable: function() {
			var _ = this;
			return Mall.Slick.sliderAvailable(_.sliderId);
		},
		attachEvents: function() {
			var _ = this;
			if(!Mall.isMobile()) {
				jQuery(window).scroll(function () {
					if (_.inViewport()) {
						_.slider.slick('slickPlay');
					} else {
						_.slider.slick('slickPause');
					}
				});
			}
		},
		inViewport: function() {
			var _ = this;
			var sliderOffset = _.slider.offset().top,
				windowOffset = jQuery(window).scrollTop();
			return windowOffset < sliderOffset;
		}
	},
	boxes: {
		boxRatio: false,
		slider: false,
		sliderId: '#boxesSlider',
		slideClass: '.boxesSlideIn',
		sliderHasArrowsClass: 'boxesSliderHasArrows',
		boxesAmount: false,
		options: {
			slidesToShow: false, //configured in init below
			slidesToScroll: false, //configured in init below
			speed: 500,
			dots: false,
			arrows: true,
			prevArrow: '<div class="boxesArrow boxesArrowPrev"><i class="fa fa-chevron-left"></i></div>',
			nextArrow: '<div class="boxesArrow boxesArrowNext"><i class="fa fa-chevron-right"></i></div>',
			responsive: [
				{
					breakpoint: Mall.Breakpoint.sm,
					settings: {
						slidesToShow: false,
						slidesToScroll: false
					}
				},
				{
					breakpoint: Mall.Breakpoint.xssm,
					settings: {
						slidesToShow: false,
						slidesToScroll: false
					}
				},
				{
					breakpoint: Mall.Breakpoint.xs,
					settings: {
						slidesToShow: 1,
						slidesToScroll: 1,
						adaptiveHeight: true
					}
				}
			]
		},
		eventsAttached: false,
		mobileUnslick: false,
		init: function() {
			var _ = this;
			_.slider = jQuery(_.sliderId);

			_.mobileUnslick = _.slider.data('boxesMobileUnslick') ? true : false;

			if(!_.isSlick() && _.sliderAvailable() && !(Mall.isMobile() && _.mobileUnslick)) {
				_.options.slidesToShow = _.options.slidesToScroll = _.getBoxesAmount();
				if(_.slider.data('boxesMobileUnslick')) {
					_.mobileUnslick = true;
					_.options.responsive = [
						{
							breakpoint: Mall.Breakpoint.sm,
							settings: 'unslick'
						}
					]
				} else {
					_.options.responsive[0].settings.slidesToShow =
						_.options.responsive[0].settings.slidesToScroll =
							(_.getBoxesAmount() < 3 ? _.getBoxesAmount : 3);
					_.options.responsive[1].settings.slidesToShow =
						_.options.responsive[1].settings.slidesToScroll =
							(_.getBoxesAmount() < 2 ? _.getBoxesAmount : 2);
				}
				_.slider.slick(_.options);
				_.resizeBoxes();
			} else {
				_.resizeBoxesUnslicked();
			}
			_.attachEvents();
		},
		getBoxesAmount: function() {
			var _ = this;
			var amount = _.slider.data('boxesAmount');
			if(_.slider !== false && amount) {
				_.boxesAmount = amount;
			} else {
				_.boxesAmount = 4;
			}
			return _.boxesAmount;
		},
		getResponsiveBoxesAmount: function() {
			var _ = this;
			if(Mall.isMobile() && !_.mobileUnslick) {
				var ww = Mall.windowWidth();
				if(ww < Mall.Breakpoint.xs) {
					return 1;
				} else if(ww < Mall.Breakpoint.xssm) {
					return _.options.responsive[1].settings.slidesToShow;
				} else {
					return _.options.responsive[0].settings.slidesToShow;
				}
			} else {
				return _.getBoxesAmount();
			}
		},
		getBoxRatio: function() {
			var _ = this;
			if(_.boxRatio === false) {
				var ratio = 999;
				jQuery(_.slideClass).each(function() {
					var currentSlideRatio = jQuery(this).data('ratio');
					ratio = currentSlideRatio && currentSlideRatio < ratio ? currentSlideRatio : ratio;
				});
				_.boxRatio = ratio;
			}
			return _.boxRatio;
		},
		sliderAvailable: function() {
			var _ = this;
			return Mall.Slick.sliderAvailable(_.sliderId);
		},
		isSlick: function() {
			return Mall.Slick.boxes.slider !== false ? Mall.Slick.boxes.slider.hasClass('slick-slider') : false;
		},
		attachEvents: function() {
			var _ = this;
			if(!_.eventsAttached) {
				_.eventsAttached = true;
				Mall.Slick.boxes.slider
					.on(
						Mall.Slick.events.setPosition + ' ' + Mall.Slick.events.init,
						Mall.Slick.boxes.resizeBoxes
					);
				if(_.mobileUnslick) {
					jQuery(window).resize(function() {
						if(Mall.windowWidth() < Mall.Breakpoint.sm) {
							Mall.Slick.boxes.resizeBoxesUnslicked();
						} else {
							Mall.Slick.boxes.slider = false;
							Mall.Slick.boxes.init();
						}
					});
				}
			}
		},
		resizeBoxes: function() {
			if(Mall.Slick.boxes.isSlick()) {
				var boxesAmount = Mall.Slick.boxes.getResponsiveBoxesAmount(),
					width = jQuery(Mall.Slick.boxes.sliderId).width();
				if(boxesAmount > 1) {
					var boxWidth = (width - ((boxesAmount) * 7)) / boxesAmount,
						boxHeight = boxWidth / Mall.Slick.boxes.getBoxRatio();

					Mall.Slick.boxes.slider.find(Mall.Slick.boxes.slideClass).css({
						'width': boxWidth + 'px',
						'height': boxHeight + 'px',
						'margin-right': false
					}).parents('.boxesSlide').removeClass('mobile');
				} else {
					Mall.Slick.boxes.slider.find(Mall.Slick.boxes.slideClass).each(function() {
						var box = jQuery(this);
						var height = width / box.data('ratio');
						box.css({
							'width': (width - 2)+'px',
							'height': height+'px',
							'margin-right': 1+'px',
							'margin-bottom': false
						}).parents('.boxesSlide').removeClass('mobile');
					});
				}
				Mall.Slick.boxes.positionArrows();
			} else {
				Mall.Slick.boxes.resizeBoxesUnslicked();
			}
		},
		resizeBoxesUnslicked: function() {
			if(!Mall.Slick.boxes.isSlick()) {
				Mall.Slick.boxes.positionArrows();
				var boxWidth =  (jQuery(Mall.Slick.boxes.sliderId).width() / 2) - 15,
					boxHeight = boxWidth / Mall.Slick.boxes.getBoxRatio();

				Mall.Slick.boxes.slider.find(Mall.Slick.boxes.slideClass).css({
					'width': boxWidth + 'px',
					'height': boxHeight + 'px'
				}).parents('.boxesSlide').addClass('mobile');
			}
		},
		positionArrows: function() {
			var _ = this,
				arrows = _.slider.find('.boxesArrow').find('i');

			if(arrows.length) {
				var height = _.slider.height(),
					arrowsHeight = arrows.height(),
					top = (height - arrowsHeight) / 2;
				arrows.css('margin-top',top+'px');
				_.slider.addClass(_.sliderHasArrowsClass);
			} else {
				_.slider.removeClass(_.sliderHasArrowsClass);
			}
		}
	}
};

// Google Tag Manager
Mall.Gtm = {
	// Steps defined
	STEP_CHECKOUT_CART: 1,
	STEP_CHECKOUT_LOGIN_GUEST: 2,
	STEP_CHECKOUT_ADDRESS_GUEST: 3,
	STEP_CHECKOUT_ADDRESS: 4,
	STEP_CHECKOUT_SHIPPING_PAYMENT: 5,
	STEP_CHECKOUT_SUMMARY: 6,
	STEP_CHECKOUT_ORDER: 7,
	STEP_CHECKOUT_THANK_YOU: 8,
	pushedSteps: [],

	init: function () {
		if (jQuery('body').hasClass("checkout-cart-index")) {
			Mall.Gtm.checkoutStep(Mall.Gtm.STEP_CHECKOUT_CART);
		} else if (jQuery('body').hasClass("customer-account-login") && /checkout\/guest\/login/g.test(window.location)) {
			Mall.Gtm.checkoutStep(Mall.Gtm.STEP_CHECKOUT_LOGIN_GUEST);
		}
		else if (jQuery('body').hasClass("checkout")) {
			if (jQuery('body').hasClass("checkout-guest-index")) {
				Mall.Gtm.checkoutStep(Mall.Gtm.STEP_CHECKOUT_ADDRESS_GUEST);
			} else {
				Mall.Gtm.checkoutStep(Mall.Gtm.STEP_CHECKOUT_ADDRESS);
			}
		}
		// Next steps manually triggered in steps.js
		else if (jQuery('body').hasClass("checkout-guest-success") || jQuery('body').hasClass("checkout-singlepage-success")) {
			Mall.Gtm.checkoutStep(Mall.Gtm.STEP_CHECKOUT_THANK_YOU);
		}
	},

	checkoutStep: function (step) {
		if (typeof dataLayer != "undefined" && jQuery.inArray(step, this.pushedSteps) == -1) {
			dataLayer.push({
				'event': 'checkout',
				'ecommerce': {
					'checkout': {
						'actionField': {'step': step},
					}
				},
			});
			this.pushedSteps.push(step);
		}
	},

	visitorData: function(data) {
		if(typeof dataLayer != 'undefined') {
			dataLayer.push(data);
		}
	}
};

Mall.Scrolltop = {
	heightToShow: false,
	options: { //these are set in /app/design/frontend/modago/default/template/page/html/scrolltop.phtml
		percentAppears: 100,
		showOnScroll: true,
		hideAfterTime: 1000
     },
	lastScrollTop: 0,
	timeout: 0,
	scrollTop: false,
	scrollTopId: '#scrollTop',
	disabled: false,
	init: function() {
		var _ = this;
		if(_.options !== false) {
			_.scrollTop = jQuery(_.scrollTopId);
			_.attachEvents();
		}
	},
	attachEvents: function() {
		var _ = this;
		jQuery(window).on('scroll',Mall.Scrolltop.onScroll);
		jQuery(window).on('resize',Mall.Scrolltop.setHeightToShow);
		_.scrollTop
			.on('touchstart mouseenter',function() {
				_.scrollTop.addClass('hover');
			})
			.on('touchend mouseleave',function() {
				_.scrollTop.removeClass('hover');
			});
		_.setHeightToShow();
	},
	onScroll: function() {
		var _ = Mall.Scrolltop,
			currentScrollTop = jQuery(window).scrollTop(),
			canShow = _.heightToShow <= jQuery(document).height();


		if(!_.disabled && canShow) {
			if (_.options.showOnScroll) {
				if (currentScrollTop < _.lastScrollTop && currentScrollTop != 0 && currentScrollTop > _.heightToShow) {
					_.show();
					_.hideDelayed();
				} else if(currentScrollTop - _.lastScrollTop > 20 || currentScrollTop < _.heightToShow) {
					clearTimeout(_.timeout);
					_.hide();
				}
			} else {
				if (currentScrollTop > _.heightToShow) {
					_.scrollTop.addClass('show');
				} else {
					_.scrollTop.removeClass('show');
				}
			}
		}
		_.lastScrollTop = currentScrollTop;
	},
	setHeightToShow: function() {
		var _ = Mall.Scrolltop;
		_.heightToShow = (Mall.windowHeight() * _.options.percentAppears / 100) + Mall.windowHeight();
	},
	hideDelayed: function() {
		var _ = Mall.Scrolltop;
		_.clearTimeout();
		_.timeout = setTimeout(_.hide, _.options.hideAfterTime);
	},
	clearTimeout: function() {
		var _ = Mall.Scrolltop;
		if(_.timeout) {
			clearTimeout(_.timeout);
		}
	},
	show: function() {
		if(Mall.Scrolltop.scrollTop !== false) {
			Mall.Scrolltop.scrollTop.addClass('show');
		}
	},
	hide: function() {
		if(Mall.Scrolltop.scrollTop !== false) {
			Mall.Scrolltop.scrollTop.removeClass('show hover');
		}
	},
	hopToTop: function() {
		var _ = Mall.Scrolltop;
		_.tempDisable();
		jQuery('body,html').animate({
			scrollTop: 0
		}, 200);
		_.clearTimeout();
		_.hide();
	},
	tempDisable: function() {
		var _ = Mall.Scrolltop;
		_.disabled = true;
		setTimeout(function() {
			_.disabled = false;
		},300);
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
};

Mall.Cookie = {
	get: function(cname) {
		var name = cname + "=";
		var ca = document.cookie.split(';');
		for (var i = 0; i < ca.length; i++) {
			var c = ca[i];
			while (c.charAt(0) == ' ') c = c.substring(1);
			if (c.indexOf(name) == 0) return c.substring(name.length, c.length);
		}
		return "";
	},
	set: function setCookie(cname, cvalue, exhours) {
		var d = new Date(), expires = "";
		var h = typeof exhours !== 'undefined' ? exhours : false;
		if(h) {
			d.setTime(d.getTime() + (h * 60 * 60 * 1000));
			expires = "expires=" + d.toUTCString();
		}
		document.cookie = cname + "=" + cvalue + "; " + expires;
	}
};

// callbacks

function addtocartcallback(response) {
	var popup = jQuery("#popup-after-add-to-cart");
	var gtmEvent = '';
    if(response.status == false) {
	    popup.find(".modal-loading").hide();
	    popup.find(".modal-loaded").hide();
	    popup.find(".modal-error").show();
		var title = typeof response.details != "undefined" ? response.details.title_section : 'Error occurred';
	    popup.find(".modal-error.modal-header .title_section").text(title);
        popup.find(".modal-error-txt").html(response.message);
		if (typeof dataLayer != "undefined") {
			dataLayer.push({
				'event': 'addToCartError',
				'details': title
			});
		}
    } else {
        if(Mall.product._current_product_type == 'configurable') {
            var superAttr = jQuery(Mall._current_superattribute);
            var label = Mall.product.getLabelById(superAttr.val(), superAttr.attr("data-superattribute"));
            popup.find("p.size>span").show();
            popup.find("p.size>span").html(label);
        } else {
            popup.find("p.size>span").hide();
        }
		popup.find("td.price").text(jQuery(".price-box-bundle span.price").text());
	    popup.find(".modal-error").hide();
	    popup.find(".modal-loading").hide();
	    popup.find(".modal-loaded").show();
	    popup.modal("show");
		if (typeof dataLayer != "undefined") {
			var ecommerce = {};
			if (typeof response.details != "undefined") {
				ecommerce = {
					'currencyCode': response.details.currencyCode,
					'add': {
						'products': [{
							'name': response.details.products.name,
							'id': response.details.products.id,
							'skuv': response.details.products.skuv,
							'simple_sku': response.details.products.simple_sku,
							'simple_skuv': response.details.products.simple_skuv,
							'category': response.details.products.category,
							'price': response.details.products.price,
							'quantity': response.details.products.quantity,
							'vendor': response.details.products.vendor,
							'brandshop': response.details.products.brandshop,
							'brand': response.details.products.brand,
							'variant': response.details.products.variant,
						}]
					}
				}
			}
			dataLayer.push({
				'event': 'addToCart',
				'ecommerce': ecommerce
			});
		}
        Mall.getAccountInfo();
    }
	popup.css({display: 'block','pointer-events':'auto'});
	jQuery("#add-to-cart").css("pointer-events","auto");
}


function addtocartlistingcallback(response) {
    addtocartcallback(response);
    jQuery("#popup-after-add-to-cart .price").html(jQuery("#prod-" + this.id + " .col-price span").html());
}

function number_format(number, decimals, dec_point, thousands_sep) {
	if(thousands_sep == " ") {
		thousands_sep = "&nbsp;";
	}
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
        if (!jQuery(".basket-dropdown:hover").length > 0 || !jQuery("#link_basket:hover").length > 0 || !jQuery("#dropdown_basket:hover").length > 0) {
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

function initToggleSearch() {
	var toggle = jQuery("#toggleSearch");
	var dropdown = jQuery('#dropdown-search');

	toggle.on('click', function(e) {
		e.stopPropagation();

		if(toggle.offset().left + 320 > jQuery(window).width()) {
			dropdown.css({left: '', right: '0'});
		} else {
			dropdown.css({left: toggle.offset().left+'px', right: ''});
		}
		dropdown.show();
		toggle.parent().addClass("open");
		toggle.css('pointer-events','none');
        toggle.parent().toggleClass('not-open');
		jQuery('#dropdown-search input[name=q]').focus();
	});

	jQuery(document).click(function(e){
		if (dropdown.is(":visible")) {
			e.preventDefault();
			toggle.parent().removeClass('open');
			dropdown.hide();
			toggle.css('pointer-events','');
            toggle.parent().toggleClass('not-open');
		}
	});
}
function positionToggleSearch() {
	var dropdown = jQuery('#dropdown-search');
	var toggle = jQuery("#toggleSearch");
	if (dropdown.is(":visible")) {
		if(toggle.offset().left + 320 > jQuery(window).width()) {
			dropdown.css({left: '', right: '0'});
		} else {
			dropdown.css({left: toggle.offset().left+'px', right: ''});
		}
	}
}


Mall.Footer = {
	footerId: '#footer',
	footerMargin: 20,
	containerId: '#sb-site',
	init: function() {
		var _ = this;
		_.setContainerPadding();
		jQuery(window).resize(_.setContainerPadding);
	},
	setContainerPadding: function() {
		var height = jQuery(Mall.Footer.footerId).height() + Mall.Footer.footerMargin;
		jQuery(Mall.Footer.containerId).css('padding-bottom', height+'px');
	}
};

Mall.CustomEvents = {
    _timeoutForScroll: undefined,
    _timeoutForResize: undefined,
    _time: 500,

    init: function(time) {
        this._time = time;

        // Event: Mall.onScrollEnd
        jQuery(window).scroll(function() {
            clearTimeout(Mall.CustomEvents._timeoutForScroll);
	        Mall.CustomEvents._timeoutForScroll = setTimeout(function() {
                jQuery(window).trigger('Mall.onScrollEnd');
            }, Mall.CustomEvents._time);
        });

        // Event: Mall.onResizeEnd
        jQuery(window).on('resize', function() {
            clearTimeout(Mall.CustomEvents._timeoutForResize);
	        Mall.CustomEvents._timeoutForResize = setTimeout(function() {
                jQuery(window).trigger('Mall.onResizeEnd');
            },  Mall.CustomEvents._time);
        });
    }
};

Mall.initUrls = function(baseUrl,baseUrlNoVendor) {
	Mall.baseUrl = baseUrl;
	Mall.baseUrlNoVendor = baseUrlNoVendor;
	Mall.mediaUrl = Mall.baseUrlNoVendor + "media/";
	Mall.productImagesUrl = Mall.mediaUrl + "catalog/product/cache/";
	Mall.manufacturerImagesUrl = Mall.mediaUrl + "m-image/";
};

Mall.isFirefox = function() {
	return jQuery.browser.mozilla === true;
};

Mall.swipeOptions = {
	swipeLeft:function(event) {
		if (jQuery('body').hasClass('sb-open')) {
			closeHamburgerMenu(event);
            jQuery('#link_menu').toggleClass('not-open');
		} else if (jQuery('#solr_search_facets.filters-mobile').is(':visible')) {
			Mall.listing.closeMobileFilters();
		}
		setTimeout(function() {
			jQuery(window).swipe("destroy");
		},100);
	},
	triggerOnTouchEnd: true,
	excludedElements: "label, button, input, select, textarea, .noSwipe",
	threshold: 5
};


/**
 * Attaches events to products likes.
 */
Mall.delegateLikeEvents = function() {
	if(!Mall.getIsBrowserMobile()) {
		jQuery(document).delegate('div.like', 'mouseenter mouseleave', function (e) {
			if (e.type === 'mouseenter') { //hover
				var textLike;
				if (jQuery(this).hasClass('liked')) {
					textLike = 'Dodane do ulubionych';
				} else {
					textLike = 'Dodaj do ulubionych';
				}
				jQuery(this).find('.toolLike').show().text(textLike);
			} else { //hover out
				jQuery(this).find('.toolLike').hide().text('');
			}
		});
		jQuery(document).delegate('div.like.liked', 'mousedown', function(e) {
			var textLike = 'Usunięte z ulubionych';
			jQuery(this).find('.toolLike').text(textLike);
		});
	}
	jQuery(document).delegate('div.like .icoLike', 'mousedown', function(e) {
		jQuery(this).animate({transform: 'scale(1.2)'}, 200);
	});
	jQuery(document).delegate('div.like .icoLike', 'mouseup', function(e) {
		jQuery(this).animate({transform: 'scale(1)'}, 200);
	});
	jQuery(document).delegate('div.like','click',function(e) {
		e.preventDefault();
		var like = jQuery(this);
		if(like.hasClass('liked')) { //unlike now
			Mall.wishlist.removeFromSmallBlock(like);
		} else { //like now
			Mall.wishlist.addFromSmallBlock(like);
		}
		return false;
	});
};



Mall.storeCheckoutValues = function() {
	if(typeof localStorage != "undefined") {
		var form = jQuery('form#co-address'),
			dataToStore = {};

		//firstname, lastname and email - propably not needed as they'll be filled by social login
		dataToStore.account_firstname = form.find('#account_firstname').val();
		dataToStore.account_lastname = form.find('#account_lastname').val();
		dataToStore.account_email = form.find('#account_email').val();

		//needed data
		dataToStore.account_telephone = form.find('#account_telephone').val();

		//ordering for someone else
		dataToStore.orders_someone_else = form.find('#orders_someone_else:checked').length ? 1 : 0;
		if (dataToStore.orders_someone_else) {
			dataToStore.shipping_firstname = form.find('#shipping_firstname').val();
			dataToStore.shipping_lastname = form.find('#shipping_lastname').val();
			dataToStore.shipping_telephone = form.find('#shipping_telephone').val();
		}

		//address data
		dataToStore.shipping_street = form.find('#shipping_street').val();
		dataToStore.shipping_postcode = form.find('#shipping_postcode').val();
		dataToStore.shipping_city = form.find('#shipping_city').val();
		dataToStore.shipping_company = form.find('#shipping_company').val();

		//need invoice
		dataToStore.invoice_vat = form.find('#invoice_vat:checked').length ? 1 : 0;
		if (dataToStore.invoice_vat) {
			//same data as shipping
			dataToStore.invoice_data_address = form.find('#invoice_data_address:checked').length ? 1 : 0;
			if (!dataToStore.invoice_data_address) {
				//invoice data
				dataToStore.billing_company = form.find('#billing_company').val();
				dataToStore.billing_vat_id = form.find('#billing_vat_id').val();
				dataToStore.billing_street = form.find('#billing_street').val();
				dataToStore.billing_postcode = form.find('#billing_postcode').val();
				dataToStore.billing_city = form.find('#billing_city').val();
			}
		}

		//password - propably not needed too
		dataToStore.account_password = form.find('#account_password').val();

		//agreements
		dataToStore.agreement_tos = form.find('#agreement_tos:checked').length ? 1 : 0;
		dataToStore.agreement_newsletter = form.find('#agreement_newsletter:checked').length ? 1 : 0;

		localStorage.setItem('checkoutData', JSON.stringify(dataToStore));
	}
};

Mall.restoreCheckoutValues = function() {
	if(typeof localStorage != "undefined" && jQuery('form#co-address').length) {
		var dataToRestore = localStorage.getItem('checkoutData');
		if (dataToRestore) {
			localStorage.removeItem('checkoutData');
			dataToRestore = JSON.parse(dataToRestore);

			jQuery.each(dataToRestore, function(id,val) {
				//click checkbox and trigger change event
				if(jQuery('#'+id).is(":checkbox")) {
					var nowIsChecked = jQuery('#'+id+':checked').length ? 1 : 0;
					if(nowIsChecked != val) {
						jQuery('#'+id).click();
					}
				} else if(jQuery('#'+id).length && (jQuery('#'+id).is('input') || jQuery('#'+id).is('textarea'))) {
					if(id != 'account_email' || (id == 'account_email' && !jQuery('#account_email').val())) {
						jQuery('#' + id).val(val);
					}
				}
			});
		}
	}
};

Mall.getUrlPart = function (name,url) {
	var query_string = {};
	if(!url) {
		var query = window.location.search.substring(1);
	} else if(url.indexOf('?') === -1) {
		return false;
	} else {
		var query = url.split("?")[1];
	}
	var vars = query.split("&");
	for (var i=0;i<vars.length;i++) {
		var pair = vars[i].split("=");
		// If first entry with this name
		if (typeof query_string[pair[0]] === "undefined") {
			query_string[pair[0]] = decodeURIComponent(pair[1]);
			// If second entry with this name
		} else if (typeof query_string[pair[0]] === "string") {
			var arr = [ query_string[pair[0]],decodeURIComponent(pair[1]) ];
			query_string[pair[0]] = arr;
			// If third or later entry with this name
		} else {
			query_string[pair[0]].push(decodeURIComponent(pair[1]));
		}
	}
	if(query_string[name]) {
		return query_string[name];
	} else {
		return '';
	}
};

Mall.getUrlPath = function() {
	return window.location.href.replace(Mall.getBaseUrl(),"");
}

Mall.showAgreement = function(target) {
	jQuery(target).hide();
	jQuery(target).parent().find('.agreement-more').show();
	jQuery(target).parent().find('.agreement-less-btn').show();
    jQuery('.checkout_agreement_vendors').tooltip('show');
	jQuery(window).resize();
	return false;
};

Mall.hideAgreement = function(target) {
	jQuery(target).hide();
	jQuery(target).parent().find('.agreement-more').hide();
	jQuery(target).parent().find('.agreement-more-btn').show();
	jQuery(window).resize();
	return false;
};

Mall.preventAgreementClick = function() {
	var target = jQuery('.agreement-btn');
	if(target.length) {
		target.click(function(e) {
			e.preventDefault();
			return false;
		});
	}
};

Mall.featuredProductsInit = function () {
    jQuery("#featured_products_tabs").tabs();
};

Mall.featuredProductsSliderAdjustVisibleTabsHeight = function(){
    jQuery.each(jQuery("#featured_products_tabs div[role=tabpanel][aria-expanded=true] .owl-carousel"), function(){
        Mall.featuredProductsSliderAdjustTabHeight(this);
    });
};

Mall.featuredProductsSliderAdjustTabHeight = function(carousel){
    var featuredProductsTabProductNameLink = jQuery(carousel).find(".owl-item.active .product-name a");

    var elementHeights = jQuery(featuredProductsTabProductNameLink).map(function (n, el) {

            return jQuery(el).height();

    }).get();

    // Math.max takes a variable number of arguments
    // `apply` is equivalent to passing each height as an argument
    var maxHeight = Math.max.apply(null, elementHeights);
    
    if(maxHeight > 0){
        jQuery(carousel).find(".owl-item .product-name").each(function (i, item) {
            jQuery(item).animate({ height: maxHeight }, 100);
        });
    }
};

Mall.inspirationsSliderInit = function () {
    var rwdInspiration = jQuery('#rwd-inspiration'),
        rwdInspirationCarousel = rwdInspiration.find('.rwd-carousel');

    if (!rwdInspiration.length || !rwdInspirationCarousel.length) {
        return;
    }

    rwdInspirationCarousel.rwdCarousel({
        itemsCustom: [
            [1000, 3],
            [800, 3],
            [670, 3],
            [475, 2],
            [320, 1]
        ],
        items: 3, //10 items above 1000px browser width
        itemsDesktop: [1000, 3], //5 items between 1000px and 901px
        itemsDesktopSmall: [900, 3], // between 900px and 601px
        itemsTablet: [767, 2], //2 items between 600 and 0
        itemsMobile: [480, 1], // itemsMobile disabled - inherit from itemsTablet option
        pagination: true,
        navigation: true,
        navigationText: ['<i class="fa fa-chevron-left"></i>', '<i class="fa fa-chevron-right"></i>'],
        rewindNav: false,
        itemsScaleUp: true,
        afterUpdate: function () {
            // Mall.inspirationsSliderAdjustHeight();
        },
        afterAction: function () {
            // Mall.inspirationsSliderAdjustHeight();
        },
        afterMove: function () {
            setTimeout(function(){ Mall.inspirationsSliderAdjustHeight(); }, 1000);
        },
        afterInit: function () {
            // Mall.inspirationsSliderAdjustHeight();
        }
    });

    Mall.inspirationsSliderAdjustHeight();

    // Custom Navigation Events
    rwdInspiration.find(".next").click(function () {
        rwdInspirationCarousel.trigger('rwd.next');
    });
    rwdInspiration.find(".prev").click(function () {
        rwdInspirationCarousel.trigger('rwd.prev');
    });
    rwdInspiration.find(".play").click(function () {
        rwdInspirationCarousel.trigger('rwd.play', 1000); //rwd.play event accept autoPlay speed as second parameter
    });
    rwdInspiration.find(".stop").click(function () {
        rwdInspirationCarousel.trigger('rwd.stop');
    });
};

Mall.inspirationsSliderAdjustHeight = function () {
    var rwdInspiration = jQuery('#rwd-inspiration'),
        rwdInspirationTitleCaption = rwdInspiration.find(".rwd-item .item .carousel-caption .title-caption");

    var elementHeights = rwdInspirationTitleCaption.map(function(n, el) {
        if(jQuery(el).visible(true)){
            return jQuery(el).height();
        }
    }).get();
    
    // Math.max takes a variable number of arguments
    // `apply` is equivalent to passing each height as an argument
    var maxHeight = Math.max.apply(null, elementHeights);
    if(maxHeight > 0){
        rwdInspiration.find(".rwd-item .item .carousel-caption .title-caption-a").each(function (i, item) {
            jQuery(item).animate({ height: maxHeight }, 100);
        });
    }

};


Mall.refresh = function() {
    location.reload();
};

Mall.createInlineSvgs = function() {
	jQuery('img[src$=".svg"]').each(function(){
		var $img = jQuery(this);
		var imgID = $img.attr('id');
		var imgClass = $img.attr('class');
		var imgURL = $img.attr('src');

		jQuery.get(imgURL, function(data) {
			// Get the SVG tag, ignore the rest
			var $svg = jQuery(data).find('svg');

			// Add replaced image's ID to the new SVG
			if(typeof imgID !== 'undefined') {
				$svg = $svg.attr('id', imgID);
			}
			// Add replaced image's classes to the new SVG
			if(typeof imgClass !== 'undefined') {
				$svg = $svg.attr('class', imgClass+' replaced-svg');
			}

			// Remove any invalid XML tags as per http://validator.w3.org
			$svg = $svg.removeAttr('xmlns:a');

			$svg.find('path').css('fill','');

			// Replace image with new SVG
			$img.replaceWith($svg);

		}, 'xml');

	});
};

Mall.debug = {
	isOn: false,
	on: function() {
		this.isOn = true;
	},
	off: function() {
		this.isOn = false;
	}
};

Mall.Utm = {
	cookieName: 'ghutm',
	attrPrefix: 'utm_',
	utm_data: false,

	init: function() {
		this.utm_data = this.getUtms();
		var properties = Object.getOwnPropertyNames(this.utm_data),
			cookieVal = decodeURIComponent(Mall.Cookie.get(this.cookieName));

		if(properties.length) { //act only if there are utm_ attributes in href
			if (!cookieVal) {
				this.setUtmsUpdate();
				return;
			} else {
				try {
					var cookieObj = JSON.parse(cookieVal);
					for(var utmName in this.utm_data) {
						if(!cookieObj.hasOwnProperty(utmName) || cookieObj[utmName] != this.utm_data[utmName]) {
							//cookie does not contain one of utm keys provided by url or
							//cookie has different value than utms in url
							this.setUtmsUpdate();
							return;
						}
						
					}
				} catch (e) {
					//catches json parse error which means that cookie has wrong value so we need to create new one
					this.setUtmsUpdate();
				}
			}
		}
	},
	getUtms: function() {
		var query_string = {},
			query = window.location.search.substring(1),
			self = this;

		if(Mall.getUrlPath().search(this.attrPrefix) !== -1) {
			var vars = query.split("&");
			for (var i = 0; i < vars.length; i++) {
				var pair = vars[i].split("=");
				if (pair[0].search(self.attrPrefix) === 0) {
					query_string[pair[0]] = decodeURIComponent(pair[1]);
				}
			}
		}

		return query_string;
	},
	setUtmsUpdate: function() {
		Mall.reg.set('utm_data',JSON.stringify(this.utm_data));
	}
};

jQuery(document).ready(function() {
	Mall.Gtm.init();
	Mall.Utm.init();
	if (Mall.reg.get('package_skin') != 'modago_gallery') {
		Mall.createInlineSvgs();
	}
    Mall.CustomEvents.init(300);
    Mall.dispatch();
    Mall.i18nValidation.apply();

	Mall.Slick.init();
	Mall.Footer.init();

    if (Mall.getIsBrowserMobile()) {
        jQuery(".header_top").headroom({
            offset: 60
        });
    }

	initToggleSearch();
    Mall.disableSearchNoQuery();

    if (Mall.getIsBrowserMobile()) { jQuery('html').addClass('is-browser-mobile'); }

    //hack for vendor main page (turpentine shows global messages only one time)
    if(jQuery(".page-messages-block ul.messages").length > 0){
        var messages = jQuery(".page-messages-block ul.messages").clone();
        jQuery(".messages-block-mobile .col-sm-12, .messages-block-desktop .col-sm-12").html(messages);
    }

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

    jQuery(".no-size").tooltip({
        template: '<div class="tooltip top" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner" style="color: #ea687e"></div></div>'
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
        }

    });
    jQuery('#popup-after-add-to-cart').on('show.bs.modal', function (e) {
        jQuery('html').find('body > .modal-backdrop').remove();
    });
    jQuery('#popup-after-add-to-cart').on('hidden.bs.modal', function (e) {
        jQuery('html').find('.modal-backdrop').remove();

    });


    jQuery(".size-box select").selectBoxIt({
        native: Mall.getIsBrowserMobile(),
        defaultText: (jQuery(".size-box option").length > 1) ? Mall.product._default_select_text : '',
//        defaultText: (jQuery(".size-box option").length > 1) ? Mall.i18nValidation.__('Size') : '',
        autoWidth: false
    });
    var optionsCount = jQuery(".size-box option").length;

        if (optionsCount == 1) {
            Mall.setSuperAttribute(jQuery(".size-box option:not(:disabled)"));
        }


    jQuery(".size-box select").bind({
        "option-click": function () {
            jQuery("#add-to-cart").tooltip('destroy');
            var selectedOption = jQuery(this).find('option:selected');
            Mall.setSuperAttribute(selectedOption);
        }
    });
    jQuery('#shopping-cart .select-styled').selectBoxIt({
        autoWidth: false
    });



    //#######################
    //## END SIZE-BOX -> SELECTBOX
    //#######################

    basket_dropdown();
    sales_order_details_top_resize();

	if(jQuery("body").hasClass("catalog-product-view")) {
		setTimeout(function() {
			if(!jQuery("#rwd-color, .rwd-color").length) {
				jQuery("#product-options .size-box .size .size-label").css({width: "auto", "margin-right": "10px"})
			}
		},200);
	}

	/**HOMEPAGE**/
	Mall.inspirationsSliderInit();
    Mall.inspirationsSliderAdjustHeight();
	jQuery(window).on('Mall.onResizeEnd', function() {
        Mall.inspirationsSliderAdjustHeight();
        Mall.featuredProductsSliderAdjustVisibleTabsHeight();
    });
	jQuery(window).on('Mall.onScrollEnd', function() {
        Mall.inspirationsSliderAdjustHeight();
        Mall.featuredProductsSliderAdjustVisibleTabsHeight();
    });
    Mall.featuredProductsInit();
    //on window resize
    /**HOMEPAGE**/


	//init like events
	Mall.delegateLikeEvents();

	//prevent link behavior on showmore in agreements
	Mall.preventAgreementClick();

	//restore checkout values on social login
	setTimeout(Mall.restoreCheckoutValues,100);
});

jQuery(window).resize(function() {
    sales_order_details_top_resize();
    positionToggleSearch();
});
