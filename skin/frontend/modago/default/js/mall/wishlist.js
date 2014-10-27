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
        setTimeout(function () {Mall.wishlist.calculateWidths();}, 500);
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
            likeHtml += Mall.translate.__("you-like-this", "You like this");
            likeHtml += jQuery("<span/>", {
                "class": "product-context-like-count",
                "style": "color: #4f4f4f"
            }).wrap("<div/>").parent().html();
            likeHtml += "<br>";
            likeHtml += jQuery("<span/>", {
                html: Mall.translate.__("remove-from-favorites", "remove from favorites")
            }).wrap("<div/>").parent().html();
        } else {
            likeHtml += Mall.translate.__("you-and", "You and") + " ";
            likeHtml += jQuery("<span/>", {
                "class": "product-context-like-count",
                style: "color: #4f4f4f",
                html: Mall.wishlist.getWishlistCount(id) - 1
            }).wrap("<div/>").parent().html();
            likeHtml += " " + Plural.get(Mall.wishlist.getWishlistCount(id) - 1
                , [
                    Mall.translate.__("person", "person"),
                    Mall.translate.__("people", "people"),
                    Mall.translate.__("people-polish-more-than-few", "osób")
                ]) + " lubicie ten product";
            likeHtml += "<br>";
            likeHtml += jQuery("<span/>", {
                html: Mall.translate.__("remove-from-favorites", "remove from favorites")
            }).wrap("<div/>").parent().html();
        }

        jQuery("#notadded-wishlist").hide();
        jQuery("#added-wishlist").removeClass("hidden");
        jQuery("#added-wishlist").show();
        jQuery("#added-wishlist").find(".likeAdded").html(likeHtml);

        Mall.buildAccountInfo(data, true);

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
                html: Mall.translate.__("add-to-br-favorites", "Add to<br />favorites")
            }).wrap("<div/>").parent().html();
        } else {
            likeHtml += jQuery("<span/>", {
                "class": "product-context-like-count",
                html: Mall.wishlist.getWishlistCount(id)
            }).wrap("<div/>").parent().html();
            likeHtml += " " + Plural.get(this.getWishlistCount(id), [
                 Mall.translate.__("person like", "person like"),
                 Mall.translate.__("people likes", "people likes"),
                 Mall.translate.__("people-polish-more-than-few likes", "osób lubi")
            ]) + " "
                +  Mall.translate.__("likes-this-product", "likes this product") + " ";
            likeHtml += jQuery("<a/>", {
                href: "#",
                onclick: "Mall.wishlist.addToWishlistFromProduct(" + id + ");return false;",
                "class": "addLike",
                html: Mall.translate.__("add-to-br-favorites", "Add to<br />favorites")
            }).wrap("<div/>").parent().html();
        }

        jQuery("#notadded-wishlist").show().removeClass("hidden");
        jQuery("#added-wishlist").hide();
        jQuery("#notadded-wishlist").html(likeHtml);

        Mall.buildAccountInfo(data, true);
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

        OrbaLib.Wishlist.add({product: id}, function (data) {
            if(data.status === true) {

                // build element
                wrapper = jQuery("<div/>", {
                    "class": "like liked",
                    "data-idproduct": id,
                    "data-status": (Mall.wishlist.getIsInYourWishlist(id) === true ? 1 : 0),
                    onclick: "Mall.wishlist.removeFromSmallBlock(this);"
                });
                ico = jQuery("<span/>", {
                    "class": "icoLike"
                }).appendTo(wrapper);

                jQuery("<img/>", {
                    "class": "img-01",
                    src: Config.path.heartLike,
                    alt: ""
                }).appendTo(ico);

                jQuery("<img/>", {
                    "class": "img-02",
                    src: Config.path.heartLiked,
                    alt: ""
                }).appendTo(ico);

                likeCount = jQuery("<span/>", {
                    "class": "like_count"
                }).appendTo(wrapper);

                jQuery("<span/>", {
                    html: Mall.wishlist.__("you", "You")
                        + (Mall.wishlist.getWishlistCount(id) > 1
                        ? " + " + (Mall.wishlist.getWishlistCount(id) - 1) : "")
                }).prependTo(likeCount);

                jQuery("<div/>", {
                    "class": "toolLike"
                }).appendTo(wrapper);

                // replace blocks
                jQuery(obj).parent().append(wrapper);
                jQuery(obj).remove();

                Mall.buildAccountInfo(data, true);
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

        OrbaLib.Wishlist.remove({product: id}, function (data) {
            if(data.status === true) {

                // build element
                wrapper = jQuery("<div/>", {
                    "class": "like",
                    "data-idproduct": id,
                    "data-status": (Mall.wishlist.getIsInYourWishlist(id) === true ? 1 : 0),
                    onclick: "Mall.wishlist.addFromSmallBlock(this);"
                });
                ico = jQuery("<span/>", {
                    "class": "icoLike"
                }).appendTo(wrapper);

                jQuery("<img/>", {
                    "class": "img-01",
                    src: Config.path.heartLike,
                    alt: ""
                }).appendTo(ico);

                jQuery("<img/>", {
                    "class": "img-02",
                    src: Config.path.heartLiked,
                    alt: ""
                }).appendTo(ico);

                likeCount = jQuery("<span/>", {
                    "class": "like_count",
                    html: (Mall.wishlist.getWishlistCount(id) > 0
                        ? Mall.wishlist.getWishlistCount(id) : "")
                }).appendTo(wrapper);

                jQuery("<span/>", {
                    html: ""
                }).prependTo(likeCount);

                jQuery("<div/>", {
                    "class": "toolLike"
                }).appendTo(wrapper);

                // replace blocks
                jQuery(obj).parent().append(wrapper);
                jQuery(obj).remove();

                Mall.buildAccountInfo(data, true);
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

        jQuery(".like").each(function (index, item) {

            var wrapperWidth = jQuery(item).parent().outerWidth(true),
                priceWidth = jQuery(item).parent().find(".col-price").outerWidth(true),
                icoWidth = jQuery(item).find(".icoLike").outerWidth(true),
                likeCountWidth = jQuery(item).find(".like_count").outerWidth(true);

                if(wrapperWidth > 0){ //To prevent using this script before products loaded
                    if (icoWidth + likeCountWidth + priceWidth + 10 < wrapperWidth) {
                        jQuery(item).parent().removeClass("like-two-line");
                    } else {
                        jQuery(item).parent().addClass("like-two-line");
                    }
                }

        });

        return this;
    }
};

jQuery(document).ready(function () {
    "use strict";
    jQuery.extend(Mall.wishlist, Mall.translate.ext);
    Mall.wishlist.init();
});