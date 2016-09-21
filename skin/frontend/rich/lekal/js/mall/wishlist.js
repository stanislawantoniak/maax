/**
 * Created by pawelchyl on 28.08.2014.
 */
Mall.wishlist = {
    /**
     * Products array, stores wishlist data
     */
    _products: {},

    /**
     * Defaults for favorites product object.
     */
    _default_options: {
        wishlist_count: 0,
        in_your_wishlist: false
    },

    init: function () {
        "use strict";
        if(Mall.listing) {
	        setTimeout(function () {
		        Mall.wishlist.calculateWidths();
	        }, 500);
        }
    },

    /**
     * Returns all products that are in current context.
     *
     * @returns {*}
     */
    getProducts: function () {
        "use strict";
        return this._products;
    },

    /**
     * Adds product to wishlist object in current context.
     *
     * @param options
     * @returns {boolean}
     */
    addProduct: function (options) {
        "use strict";

        var data = {},
            id;
        if (!this.validateProduct (options)) {
            return false;
        }

        options = this.getOptions(options);
        id = parseInt(options.id, 10);
        data[id] = options;
        jQuery.extend(this._products, data);

        return true;
    },

    /**
     * Validates product options against required fields.
     *
     * @param options
     * @returns {boolean}
     */
    validateProduct: function (options) {
        "use strict";
        if (jQuery.isEmptyObject(options)
            || options.id === undefined
            || isNaN(parseInt(options.id, 10))) {
            return false;
        }

        return true;
    },

    /**
     * Returns default options that product object should have.
     *
     * @returns {*}
     */
    getDefaults: function () {
        "use strict";

        return this._default_options;
    },

    /**
     * Get product fields. Requires only id to be set.
     * This function will extend given options with defaults.
     *
     * @param options
     * @returns {*}
     */
    getOptions: function (options) {
        "use strict";

        var _opts = jQuery.extend({}, this.getDefaults());

        return jQuery.extend(_opts, options);
    },

    /**
     * Get product from current wishlist context.
     *
     * @param id
     * @returns {*}
     */
    getProduct: function (id) {
        "use strict";

        var product = null;
        if (this.getIsProductExists(id)) {
            product = this.getProducts()[parseInt(id, 10)];
        }

        return product;
    },

    /**
     * Checks if product exists in current wishlist context.
     *
     * @param id
     * @returns {boolean}
     */
    getIsProductExists: function (id) {
        "use strict";

        if(isNaN(parseInt(id, 10))
            || this.getProducts()[parseInt(id, 10)] === undefined) {
            return false;
        }

        return true;
    },

    /**
     * Returns wishlist count for given product.
     *
     * @param id
     * @returns {number}
     */
    getWishlistCount: function (id) {
        "use strict";

        var product = this.getProduct(id);
        if (product === null) {
            return 0;
        }

        return product.wishlist_count;
    },

    /**
     * Returns whether product is in current user wishlist.
     *
     * @param id
     * @returns {boolean}
     */
    getIsInYourWishlist: function (id) {
        "use strict";

        var product = this.getProduct(id);
        if (product === null) {
            return false;
        }

        return product.in_your_wishlist;
    },

    /**
     * Adds product to wishlist from product page.
     *
     * @param id
     */
    addToWishlistFromProduct: function (id) {
        "use strict";
		var addedLikeBox = jQuery(".addedLike-box");
	    var addLikeBox = jQuery(".addLike-box");
	    var addingLikeBox = jQuery(".addingLike-box");

	    addedLikeBox.addClass("hidden");
	    addLikeBox.addClass("hidden");
	    addingLikeBox.removeClass("hidden");
        OrbaLib.Wishlist.add({product: id}, function(data) {
            if(data.status === true) {
                Mall.wishlist.added(id);
                Mall.wishlist.actionsAfterAddingProductProduct(id, data);
            }
        });
    },

    /**
     * Removes product from wishlist - this function is made to be used on product page.
     *
     * @param id
     */
    removeFromWishlistFromProduct: function (id) {
        "use strict";

	    var addedLikeBox = jQuery(".addedLike-box");
	    var addLikeBox = jQuery(".addLike-box");
	    var addingLikeBox = jQuery(".addingLike-box");

	    addedLikeBox.addClass("hidden");
	    addLikeBox.addClass("hidden");
	    addingLikeBox.removeClass("hidden");
        OrbaLib.Wishlist.remove({product: id}, function (data) {
            Mall.wishlist.removed(id);
            Mall.wishlist.actionsAfterRemovingProductProduct(id, data);
        });
    },

    /**
     * Performs internal actions after adding product to wishlist.
     *
     * @param id
     * @returns {*}
     */
    added: function (id) {
        "use strict";

        var product = this.getProduct(id);
        if (product === null) {
            return false;
        }

        product.in_your_wishlist = true;
        product.wishlist_count += 1;
        this.addProduct(product);

        return this;
    },

    /**
     * Performs internal actions whe product is removed from wishlist.
     *
     * @param id
     * @returns {*}
     */
    removed: function (id) {
        "use strict";

        var product = this.getProduct(id);
        if (product === null) {
            return false;
        }

        product.in_your_wishlist = false;
        product.wishlist_count -= 1;
        if (product.wishlist_count < 0) {
            product.wishlist_count = 0;
        }

        this.addProduct(product);

        return this;
    },

    /**
     * Actions performed after successful action of adding product to wishlist from product page.
     *
     * @param id
     * @param data
     * @returns {Mall.wishlist}
     */
    actionsAfterAddingProductProduct: function (id, data) {
        "use strict";

        // build elements
        var likeHtml = "",
            product;

        product = this.getProduct(id);
        if (this.getWishlistCount(id) === 1) {
            likeHtml += jQuery("<span/>", {
                "class": "product-context-like-count",
                "style": "color: #4f4f4f"
            }).wrap("<div/>").parent().html();
            likeHtml += jQuery("<span/>", {
                html: '<i class="fa fa-list" aria-hidden="true"></i>' + Mall.translate.__("added-to-shopping-list", "Added to shopping list")
            }).wrap("<div/>").parent().html();
        } else {
            likeHtml += jQuery("<span/>", {
                html: '<i class="fa fa-list" aria-hidden="true"></i>' +  Mall.translate.__("added-to-shopping-list", "Added to shopping list")
            }).wrap("<div/>").parent().html();
        }

	    var addingLikeBox = jQuery(".addingLike-box");

	    addingLikeBox.addClass("hidden");

        jQuery("#notadded-wishlist").hide();
        jQuery("#added-wishlist").removeClass("hidden");
        jQuery("#added-wishlist").show();
        jQuery("#added-wishlist").find(".likeAdded").html(likeHtml);		

        return this;
    },

    /**
     * Actions performed after removing product from wishlist on product page.
     *
     * @param id
     * @param data
     */
    actionsAfterRemovingProductProduct: function (id, data) {
        "use strict";

        var likeHtml = "",
            product;

        product = this.getProduct(id);
        if (this.getWishlistCount(id) === 0) {
            likeHtml += jQuery("<span/>", {
                "class": "product-context-like-count",
                html: "&nbsp;"
            }).wrap("<div/>").parent().html();
            likeHtml += jQuery("<a/>", {
                href: "#",
                onclick: "Mall.wishlist.addToWishlistFromProduct(" + id + ");return false;",
                "class": "addLike",
                html: '<i class="fa fa-list" aria-hidden="true"></i>' + Mall.translate.__("add-to-favorites", "Add to shopping list")
            }).wrap("<div/>").parent().html();
        } else {
            likeHtml += jQuery("<a/>", {
                href: "#",
                onclick: "Mall.wishlist.addToWishlistFromProduct(" + id + ");return false;",
                "class": "addLike",
                html: '<i class="fa fa-list" aria-hidden="true"></i>' + Mall.translate.__("add-to-favorites", "Add to shopping list")
            }).wrap("<div/>").parent().html();
        }

	    var addingLikeBox = jQuery(".addingLike-box");

	    addingLikeBox.addClass("hidden");

        jQuery("#notadded-wishlist").show().removeClass("hidden");
        jQuery("#added-wishlist").hide();
        jQuery("#notadded-wishlist").html(likeHtml);

		// set products count badge
		Mall.setFavoritesCountBadge(data.content.favorites_count);
    },

    /**
     * Adds to wishlist from small product block - listing and crosssells.
     *
     * @param obj
     * @returns {boolean}
     */
    addFromSmallBlock: function (obj) {
        "use strict";

        // fetch id
        var id = jQuery(obj).attr("data-idproduct"),
            product,
            ico, likeCount, wrapper;
        // prceed only if product is in wishlist collection
        if (!this.getIsProductExists(id)) {
            return false;
        }

        product = this.getProduct(id);
        this.added(id);

	    obj.find('.icoLike').addClass('fa fa-spinner fa-spin');

        OrbaLib.Wishlist.add({product: id}, function (data) {
            if(data.status === true) {

                // build element
                wrapper = jQuery("<div/>", {
                    "class": "like liked",
                    "data-idproduct": id
                });

	            likeCount = jQuery("<span/>", {
		            "class": "like_count"
	            }).appendTo(wrapper);

                ico = jQuery("<span/>", {
                    "class": "icoLike"
                }).appendTo(wrapper);

                jQuery("<span/>", {
                    html: Mall.translate.__("you")
                        + (Mall.wishlist.getWishlistCount(id) > 1
                        ? " + " + (Mall.wishlist.getWishlistCount(id) - 1 > 99 ? "99+" : Mall.wishlist.getWishlistCount(id) - 1) : "") + " "
                }).prependTo(likeCount);

                jQuery("<div/>", {
                    "class": "toolLike"
                }).appendTo(wrapper);

                // replace blocks
                jQuery(obj).parent().append(wrapper);
                jQuery(obj).remove();

				// set products count badge
				Mall.setFavoritesCountBadge(data.content.favorites_count);
                Mall.wishlist.calculateWidths();
            }
        });

    },

    /**
     * Removes from wishlist in small product block.
     *
     * @param obj
     * @returns {boolean}
     */
    removeFromSmallBlock: function (obj) {
        "use strict";

        // fetch id
        var id = jQuery(obj).attr("data-idproduct"),
            product,
            ico, likeCount, wrapper;
        // prceed only if product is in wishlist collection
        if (!this.getIsProductExists(id)) {
            return false;
        }

        product = this.getProduct(id);
        this.removed(id);

	    obj.find('.icoLike').addClass('fa fa-spinner fa-spin');

        OrbaLib.Wishlist.remove({product: id}, function (data) {
            if(data.status === true) {

                // build element
                wrapper = jQuery("<div/>", {
                    "class": "like",
                    "data-idproduct": id
                });

	            likeCount = jQuery("<span/>", {
		            "class": "like_count",
		            html: (Mall.wishlist.getWishlistCount(id) > 0
			            ? (Mall.wishlist.getWishlistCount(id) > 99 ? "99+" : Mall.wishlist.getWishlistCount(id)) : "") + " "
	            }).appendTo(wrapper);

                ico = jQuery("<span/>", {
                    "class": "icoLike"
                }).appendTo(wrapper);

                jQuery("<span/>", {
                    html: ""
                }).prependTo(likeCount);

                jQuery("<div/>", {
                    "class": "toolLike"
                }).appendTo(wrapper);

                // replace blocks
	            if(typeof unlikingOnWishlist != 'undefined' && unlikingOnWishlist) {
		            //remove item
					jQuery(obj).parents('.item').remove();
		            //fix for footer
		            jQuery(window).resize();
	            } else {
		            jQuery(obj).parent().append(wrapper);
		            jQuery(obj).remove();
	            }
				// set products count badge
                Mall.wishlist.calculateWidths();
            }
        });

        return true;
    },

    /**
     * Calculates and set like count with ico in one line or two.
     *
     * @returns {Mall.wishlist}
     */
    calculateWidths: function () {
        "use strict";
	    Mall.wishlist._prev_window_width = '';
	    Mall.wishlist.likePriceView();
	    return this;
    },

	_prev_window_width: '',

    likePriceView: function(){
	    if(this._prev_window_width != jQuery(window).width()) {
		    jQuery('#items-product').find('.price').each(function () {
			    var self = jQuery(this),
				    fullWidth = self.width() -10,
				    price = self.find('.col-price'),
				    priceWidth = price.outerWidth(),
				    like = self.find('.like'),
				    likeCount = like.find('.like_count'),
				    likeCountWidth = likeCount.outerWidth(),
				    likeIco = like.find('.icoLike'),
				    likeIcoWidth = likeIco.outerWidth(),
				    priceClass = 'price-two-line',
				    likeClass = 'like-two-line';

			    self.removeClass(priceClass + ' ' + likeClass);

			    if(priceWidth + likeCountWidth > fullWidth) {
				    self.addClass(likeClass + ' ' + priceClass);
			    } else if(priceWidth + likeCountWidth + likeIcoWidth > fullWidth) {
				    self.addClass(likeClass);
			    }
		    });
		    this._prev_window_width = jQuery(window).width();
		    //jQuery(window).resize();
	    }
    }
};

jQuery(document).ready(function () {
    "use strict";
    jQuery.extend(Mall.wishlist, Mall.translate.ext);
    Mall.wishlist.init();
});