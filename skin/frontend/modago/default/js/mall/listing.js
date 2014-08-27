/**
 * Created by pawelchyl on 26.08.2014.
 */

/**
 *
 */
Mall.listing = {
    _current_page: 1,

    _current_dir: "asc",

    _current_sort: "",

    _current_query: "",

    _current_scat: "",

    _current_filters: [],

    _current_visible_items: 0,

    _current_total: 0,

    _current_price_rage: [0, 0],

    _scroll_load_lock: false,

    _scroll_load_offset: 8,

    _load_next_offset: 500,

    _load_next_start: 0,

    _scroll_load_bottom_offset: 100,

    _product_queue: [],

    _autoappend: false,

    init: function () {
        this.attachShowMoreEvent();
        this.attachFilterColorEvents();
        this.attachFilterEnumEvents();
        this.attachFilterDroplistEvents();
        this.attachFilterFlagEvents();
        this.attachFilterPriceSliderEvents();
        this.attachFilterSizeEvents();
        // set next load start
        this.setLoadNextStart(this.getCurrentVisibleItems());
        this.reloadListingItemsAfterPageLoad();
        this.loadProductsOnScroll();

        // load additional products to queue after page is loaded
        this.loadToQueue();
    },

    getMoreProducts: function () {
        var query = this.getQuery(),
            page = this.getPage(),
            sort = this.getSort(),
            dir = this.getDir(),
            scat = this.getScat(),
            filtersArray = this.getFiltersArray();

        OrbaLib.Listing.getProducts({
            q: query,
            page: page,
            sort: sort,
            dir: dir,
            scat: scat,
            fq: filtersArray === [] ? [] : filtersArray.fq
        }, Mall.listing.getMoreProductsCallback);
    },

    getMoreProductsCallback: function (data) {
        if (data.status === true) {
            var container = jQuery("#items-product").masonry(),
                sortArray = typeof data.content.sort === "string"
                    || data.content.sort instanceof String ? data.content.sort.split(" ") : [],
                items;
            Mall.listing.setPageIncrement();
            Mall.listing.setSort(sortArray[0] === undefined ? "" : sortArray[0]);
            Mall.listing.setDir(sortArray[1] === undefined ? "" : sortArray[1]);
            items = Mall.listing.appendToList(data.content.products);
            container.imagesLoaded(function () {
                container.masonry("reloadItems");
                container.masonry();
//                container.masonry("appended", items);
                setTimeout(function () {Mall.listing.placeListingFadeContainer();}, 1000);
            });
            // set current items count
            Mall.listing.addToVisibleItems(data.content.rows);
            Mall.listing.setTotal(data.content.total);
            Mall.listing.placeListingFadeContainer();
//            Mall.listing.canLoadMoreProducts();
        } else {
            // do something to inform customer that something went wrong
            alert("Something went wrong, try again");
            return false;
        }
        return true;
    },

    /**
     * Removes lock from queue.
     *
     * @returns {Mall.listing}
     */
    removeLockFromQueue: function () {
        this._scroll_load_lock = false;

        return this;
    },

    loadProductsOnScroll: function () {
        // detect if this is good time for showing next part of products
        jQuery(window).scroll(function () {
            if (jQuery(window).scrollTop() > jQuery(document).height() - jQuery(window).height() -
                    Mall.listing.getScrollBottomOffset()) {
                if (!Mall.listing.getScrollLoadLock()
                    && Mall.listing.getProductQueue().length > 0
                    && !Mall.listing.getScrollLoadLock()) {
                    Mall.listing.setQueueLoadLock();
                    Mall.listing.appendFromQueue();
                }
            }
        });
    },

    appendFromQueue: function () {
        "use strict";

        // append products to list
        var products = Mall.listing.getProductQueue().slice(
                0, Mall.listing.getScrollLoadOffset()),
            items = Mall.listing.appendToList(products),
            container = jQuery("#items-product").masonry();
        container.imagesLoaded(function () {
            container.masonry("appended", items).masonry("reloadItems").masonry();
            setTimeout(function () {Mall.listing.placeListingFadeContainer();}, 1000);
        });
        Mall.listing.addToVisibleItems(products.length);

        Mall.listing.setProductQueue(
            Mall.listing.getProductQueue().slice(
                Mall.listing.getScrollLoadOffset()
                , Mall.listing.getProductQueue().length));

        // remove lock from queue
        Mall.listing.removeLockFromQueue();
        // load next part of images
        Mall.listing.loadPartImagesFromQueue();
        // show load more button
        if (Mall.listing.canShowLoadMoreButton()) {
            Mall.listing.showLoadMoreButton();
            Mall.listing.showShapesListing();
            Mall.listing.placeListingFadeContainer();
        }
    },

    loadMoreProducts: function () {
        "use strict";
        this.setAutoappend(true);
        this.loadToQueue();
    },

    loadToQueue: function () {
        // ajax load
        var query = this.getQuery(),
            page = this.getPage(),
            sort = this.getSort(),
            dir = this.getDir(),
            scat = this.getScat(),
            start = this.getLoadNextStart(),
            offset = this.getLoadNextOffset(),
            filtersArray = this.getFiltersArray();

        this.setQueueLoadLock();
        OrbaLib.Listing.getProducts({
            q: query,
            page: page,
            sort: sort,
            dir: dir,
            scat: scat,
            start: start,
            rows: offset,
            fq: filtersArray === [] ? [] : filtersArray.fq
        }, Mall.listing.appendToQueueCallback);
    },

    /**
     * Loads proper part of images from queue.
     *
     * @returns {Mall.listing}
     */
    loadPartImagesFromQueue: function () {
        "use strict";
        // select part of queue
        var part = Mall.listing.getProductQueue().slice(0, Mall.listing.getScrollLoadOffset());

        // load images
        if (part.length > 0) {
            jQuery.each(part, function (index, item) {
                Mall.listing.loadImageInBackground(item.listing_resized_image_url);
            });
        }

        return this;
    },

    /**
     * Return whether product queue is empty or not.
     *
     * @returns {boolean}
     */
    canPrependFromQueue: function () {
        "use strict";
        return (this.getProductQueue().length > 0);
    },

    /**
     * Return whether load more button can be shown based on total and queue length.
     *
     * @returns {boolean}
     */
    canShowLoadMoreButton: function () {
        "use strict";
        return (!this.canPrependFromQueue() && this.getTotal() > this.getCurrentVisibleItems());
    },

    /**
     * Loads image in background.
     *
     * @param url
     * @returns {Mall.listing}
     */
    loadImageInBackground: function (url) {
        "use strict";
        var image = new Image ();
        image.src = url;
        image.onLoad = function () {
        };

        return this;
    },

    appendToQueueCallback: function (data) {
        if (!jQuery.isEmptyObject(data) && data.status !== undefined && data.status === true) {
            var sortArray = typeof data.content.sort === "string"
                || data.content.sort instanceof String ? data.content.sort.split(" ") : [];
            Mall.listing.setSort(sortArray[0] === undefined ? "" : sortArray[0]);
            Mall.listing.setDir(sortArray[1] === undefined ? "" : sortArray[1]);

            if (data.content !== undefined && data.content.products !== undefined
                && !jQuery.isEmptyObject(data.content.products)) {
                Mall.listing.setProductQueue(
                    Mall.listing.getProductQueue().concat(data.content.products)
                );
                Mall.listing.setLoadNextStart(data.content.rows);
                // load images in background
                if (Mall.listing.canPrependFromQueue()) {
                    Mall.listing.loadPartImagesFromQueue();
                }
                if(Mall.listing.getAutoappend()) {
                    Mall.listing.appendFromQueue();
                    Mall.listing.setAutoappend(false);
                }
            } else {
                // @todo hide buttons etc
                alert("Products list is empty");
            }
        } else {
            alert("Something went wrong, try again later");
        }

        Mall.listing.removeLockFromQueue();
    },

    appendToList: function (products) {
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

    /**
     * Creates single product container for listing.
     *
     * @param product
     * @returns {*}
     */
    createProductEntity: function(product) {
        var container = jQuery("<div/>", {
            "class": "item col-phone col-xs-4 col-sm-4 col-md-3 col-lg-3 size14"
        });
        var box = jQuery("<div/>", {
            "class": "box_listing_product"
        }).appendTo(container);

        var link = jQuery("<a/>", {
            href: product.current_url
        }).appendTo(box);

        var figure = jQuery("<figure/>", {
            "class": "img_product"
        }).appendTo(link);

        jQuery("<img/>", {
            src: product.listing_resized_image_url,
            alt: product.name,
            "class": "img-responsive"
        }).appendTo(figure);

        var vendor = jQuery("<div/>", {
            "class": "logo_manufacturer"
        }).appendTo(link);

        jQuery("<img/>", {
            src: product.udropship_vendor_logo_url,
            alt: product.udropship_vendor_name
        }).appendTo(vendor);

        jQuery("<div/>", {
            "class": "name_product",
            html: product.name
        }).appendTo(link);

        var priceBox = jQuery("<div/>", {
            "class": "price clearfix"
        }).appendTo(link);

        var colPrice = jQuery("<div/>", {
            "class": "col-price"
        }).appendTo(priceBox);

        if(product.price != product.final_price) {
            jQuery("<span/>", {
                "class": "old",
                html: number_format(product.price, 2, ",", " ") + " "
                    + Mall.getCurrencyBasedOnCode(product.currency)
            }).appendTo(colPrice);
        }
        jQuery("<span/>", {
            html: number_format(product.final_price, 2, ",", " ") + " "
                + Mall.getCurrencyBasedOnCode(product.currency)
        }).appendTo(colPrice);

        var likeClass = "like",
            likeText = "<span></span>";
        if(product.in_my_wishlist) {
            likeClass += " liked";
            likeText = "<span>Ty+</span>";
        }
        var like = jQuery("<div/>", {
            "class": likeClass,
            "data-idproduct": product.entity_id,
            "data-status": product.in_my_wishlist,
            onclick: "Mall.toggleWishlist(this);"
        }).appendTo(priceBox);

        var likeIco = jQuery("<span/>", {
            "class": "icoLike"
        }).appendTo(like);

        jQuery("<img/>", {
            src: Config.path.heartLike,
            "class": "img-01",
            alt: ""
        }).appendTo(likeIco);

        jQuery("<img/>", {
            src: Config.path.heartLiked,
            alt: "",
            "class": "img-02"
        }).appendTo(likeIco);

        jQuery("<span/>", {
            "class": "like_count",
            html: likeText + product.wishlist_count
        }).appendTo(like);

        jQuery("<div/>", {
            "class": "toolLike"
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

    canLoadMoreProducts: function() {
        if(this._current_visible_items >= this._current_total) {
            this.hideLoadMoreButton()
                .hideShapesListing();
        }

        return this;
    },

    hideShapesListing: function() {
        jQuery("#items-product").find(".shapes_listing").hide();

        return this;
    },

    showShapesListing: function() {
        jQuery("#items-product").find(".shapes_listing").show();

        return this;
    },

    hideLoadMoreButton: function() {
        jQuery("#content-main").find(".addNewPositionListProductWrapper").hide();

        return this;
    },

    showLoadMoreButton: function() {
        jQuery("#content-main").find(".addNewPositionListProductWrapper").show();

        return this;
    },

    insertMobileSidebar: function() {
        if(this.getCurrentMobileFilterState() == 0) {
            var currentSidebar = jQuery("#sidebar").clone(true, true);
            jQuery("#sidebar").find(".sidebar").remove();
            jQuery(".fb-slidebar-inner").find('.sidebar').remove();
            jQuery(".fb-slidebar-inner").html(currentSidebar.html());
            this.setCurrentMobileFilterState(1);
        }

        return this;
    },

    insertDesktopSidebar: function() {
        if(this.getCurrentMobileFilterState() == 1) {
            var currentSidebar = jQuery(".fb-slidebar-inner").clone(true, true);
            jQuery(".fb-slidebar-inner").find('.sidebar').remove();
            jQuery("#sidebar").find(".sidebar").remove();
            jQuery("#sidebar").append(currentSidebar);
            this.setCurrentMobileFilterState(0);
        }

        return this;
    },

    getCurrentMobileFilterState: function() {
        return this._current_mobile_filter_state;
    },

    attachShowMoreEvent: function() {
        jQuery(".showmore-filters").on("click", function(e) {
            var target = e.target;
            e.preventDefault();
            if(jQuery(this).attr("data-state") == "0") {
                jQuery(this).parents(".content").find("[data-state='hidden']").show(500);
            } else {
                jQuery(this).parents(".content").find("[data-state='hidden']").hide(500);
            }
            Mall.listing.toggleShowMoreState(this);
        });
    },

    attachFilterColorEvents: function() {
        jQuery(".filter-color").find("[data-url]").on("click", function(e) {
            // @todo ajax logic
            location.href = jQuery(this).attr("data-url");
        });
    },

    attachFilterFlagEvents: function() {
        jQuery(".filter-flags").find("[data-url]").on("click", function(e) {
            // @todo ajax logic
            location.href = jQuery(this).attr("data-url");
        });
    },

    attachFilterEnumEvents: function() {
        jQuery(".filter-enum").find("[data-url]").on("click", function(e) {
            // @todo ajax logic
            location.href = jQuery(this).attr("data-url");
        });
    },

    attachFilterSizeEvents: function() {
        jQuery(".filter-size").find("[data-url]").on("click", function(e) {
            // @todo ajax logic
            location.href = jQuery(this).attr("data-url");
        });
    },

    attachFilterDroplistEvents: function() {
        var headList = jQuery('.button-select.ajax');
        var listSelect = jQuery('.dropdown-select ul');
        headList.on('click', function(event) {
            event.preventDefault();
            jQuery(this).next('.dropdown-select').stop(true).slideToggle(200);
        });
        listSelect.on('click', 'a', function(event) {
            event.preventDefault();
            var thisVal = jQuery(this).html();
            var thisUrl = jQuery(this).attr("data-url");
            jQuery(this).closest('.select-group').find('.button-select').html(thisVal+'<span class="down"></span>');
            jQuery(this).closest('.dropdown-select').slideUp(200);
            window.location.href = thisUrl;
        });
        jQuery(document).click(function(e) {
            if (!jQuery(e.target).parents().andSelf().is('.select-group')) {
                jQuery(".dropdown-select").slideUp(200);
            }
        });

    },

    attachFilterPriceSliderEvents: function() {
        var sliderRange = jQuery( "#slider-range" );
        if (sliderRange.length >= 1) {
            jQuery( "#slider-range" ).slider({
                range: true,
                min: Mall.listing.getCurrentPriceRange()[0],
                max: Mall.listing.getCurrentPriceRange()[1],
                values: Mall.listing.getCurrentPriceRange(),
                slide: function(event, ui) {
                    jQuery("#zakres_min").val(ui.values[0]);
                    jQuery("#zakres_max").val(ui.values[1]);
                }
            });

            jQuery("#zakres_min").val(jQuery("#slider-range").slider("values", 0));
            jQuery("#zakres_max").val(jQuery("#slider-range").slider("values", 1));
            jQuery('#slider-range').on('click', 'a', function(event) {
                var checkSlider = jQuery('#checkSlider').find('input');
                if (!checkSlider.is(':checked')) {
                    checkSlider.prop('checked', true);
                    jQuery('#filter_price').find('.action').removeClass('hidden');
                }
            });
        };

        jQuery("#filter_price").find("input.filter-price-range-submit").on("click", function(e) {
            e.preventDefault();
            // pop price from fq
            Mall.listing.removeSingleFilterType(this);
            var fq = Mall.listing.getFiltersArray();
            if(jQuery.isEmptyObject(fq) || jQuery.isEmptyObject(fq.fq)) {
                fq = {fq: {}};
            }

            fq.fq.price = Mall.listing.getMinPriceFromSlider() + " TO " + Mall.listing.getMaxPriceFromSlider();
            Mall.listing.setFiltersArray(fq);
            Mall.listing.reloadListing();
        });
    },

    toggleShowMoreState: function(item) {
        var state = jQuery(item).attr("data-state");
        if(state == "0") {
            jQuery(item).text("Pokaż mniej");
            jQuery(item).attr("data-state", "1");
        } else {
            jQuery(item).text("Pokaż więcej");
            jQuery(item).attr("data-state", "0");
        }
    },

    reloadListing: function() {
        var protocol  = window.location.protocol;
        var host = window.location.host;
        var pathname = window.location.pathname;

        window.location.href = protocol + "//" + host + pathname + "?" + jQuery.param(this.getQueryParams());
    },

    getQueryParams: function() {
        var q = {
            fq: this.getFiltersArray() == [] ? [] : this.getFiltersArray().fq,
            q: this.getQuery(),
            page: this.getPage(),
            sort: this.getSort(),
            dir: this.getDir(),
            scat: this.getScat(),
            rows: this.getLoadNextOffset(),
            start: this.getLoadNextStart()
        };

        return q;
    },

    removeSingleFilterType: function(filter) {
        var filterType = jQuery(filter).attr("data-filter-type");
        var filters = jQuery.isEmptyObject(this.getFiltersArray()) || jQuery.isEmptyObject(this.getFiltersArray().fq) ? [] : this.getFiltersArray().fq;
        delete filters[filterType];

        return this;
    },

    placeListingFadeContainer: function() {
        // check if body has proper class
        if(jQuery("body").hasClass("node-type-list")
            && this.getTotal() > 20
            && this.canLoadMoreProducts()
            && this.canShowLoadMoreButton()) {
            var heights = new Array();
            jQuery('.node-type-list #items-product .item').each(function() {
                heights.push(jQuery(this).height());
            });
            var min = Math.min.apply( Math, heights );
            var con = jQuery('#items-product').innerHeight();
            jQuery('#items-product').not('.list-shop-product').css('height', 'auto');
            jQuery('#items-product').not('.list-shop-product').css('height', con-min);
            jQuery(".shapes_listing").css("top", jQuery(".addNewPositionListProduct").position().top - 40);
        } else {
            this.hideLoadMoreButton();
            this.hideShapesListing();
        }
    },

    /**
     * Sets load next start attribute.
     *
     * @param count
     * @returns {Mall.listing}
     */
    setLoadNextStart: function (count) {
        this._load_next_start += parseInt(Math.abs(count), 10);

        return this;
    },

    /**
     * Fixes wrong placement for masonry listing items adter page is loaded.
     *
     * @returns {Mall.listing}
     */
    reloadListingItemsAfterPageLoad: function() {
        jQuery("#items-product").masonry().imagesLoaded(function() {
            var container = jQuery("#items-product").masonry();
            container.masonry("reloadItems");
            container.masonry();
            setTimeout(function() {Mall.listing.placeListingFadeContainer()}, 1000);

            // hide load more button
            if (Mall.listing.getTotal() > Mall.listing.getCurrentVisibleItems()) {
                Mall.listing.hideLoadMoreButton()
                    .hideShapesListing();
            }

        });

        return this;
    },

    /**
     *
     * SETTERS / GETTERS
     *
     */

    /**
     * Returns min price which is set in price slider
     *
     * @returns {number}
     */
    getMinPriceFromSlider: function() {
        var price = jQuery("#zakres_min").val();
        return price == '' ? 0 : price;
    },

    getMaxPriceFromSlider: function() {
        var price = jQuery("#zakres_max").val();
        return price == '' ? 0 : price;
    },

    getQuery: function() {
        return this._current_query;
    },

    getPage: function() {
        // for now it's turned off
        return "";
//        return this._current_page;
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
        return this._current_filters;
    },

    getCurrentVisibleItems: function() {
        return this._current_visible_items;
    },

    getTotal: function() {
        return this._current_total;
    },

    getCurrentPriceRange: function() {
        return this._current_price_rage;
    },

    getScrollBottomOffset: function() {
        return this._scroll_load_bottom_offset;
    },

    getScrollLoadOffset: function () {
        return this._scroll_load_offset;
    },

    getScrollLoadLock: function () {
        return this._scroll_load_lock;
    },

    getLoadNextStart: function () {
        return this._load_next_start;
    },

    getProductQueue: function () {
        return this._product_queue;
    },

    getLoadNextOffset: function () {
        return this._load_next_offset;
    },

    /**
     * Returns autoappend mode state.
     *
     * @returns {boolean}
     */
    getAutoappend: function () {
        "use strict";
        return this._autoappend;
    },


    /**
     * Sets autoappend mode to given state.
     *
     * @param state
     * @returns {Mall.listing}
     */
    setAutoappend: function (state) {
        "use strict";
        this._autoappend = state;

        return this;
    },

    setProductQueue: function (queue) {
        this._product_queue = queue;

        return this;
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
    },

    setFiltersArray: function(filters) {
        this._current_filters = filters;

        return this;
    },

    addToVisibleItems: function(itemsCount) {
        this._current_visible_items += parseInt(itemsCount);

        return this;
    },

    setTotal: function(total) {
        this._current_total = parseInt(total);

        return this;
    },

    setCurrentMobileFilterState: function(state) {
        this._current_mobile_filter_state = state;

        return this;
    },

    setCurrentPriceRange: function(min, max) {
        this._current_price_rage = [min, max];

        return this;
    },

    /**
     * Sets lock for loading products in queue.
     *
     * @returns {Mall.listing}
     */
    setQueueLoadLock: function () {
        "use strict";
        this._scroll_load_lock = true;

        return this;
    }
};