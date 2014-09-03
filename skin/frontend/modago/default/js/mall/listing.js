/**
 * Created by pawelchyl on 26.08.2014.
 */

/**
 * Object for processing product listing.
 */
Mall.listing = {
    /**
     * Current listing page.
     */
    _current_page: 1,

    /**
     * Selected sorting direction.
     */
    _current_dir: "asc",

    /**
     * Selected sorting type.
     */
    _current_sort: "",

    /**
     * Current query string - search listing.
     */
    _current_query: "",

    /**
     * Current category.
     */
    _current_scat: "",

    /**
     * Current filters set.
     */
    _current_filters: [],

    /**
     * How many items are visible on listing at the moment.
     */
    _current_visible_items: 0,

    /**
     * Total items for current listing.
     */
    _current_total: 0,

    /**
     * Current price range - filter.
     */
    _current_price_rage: [0, 0],

    /**
     * Lock for loading and showing items when scrolling.
     */
    _scroll_load_lock: false,

    /**
     * How many items will be appended to listing when scrolling.
     */
    _scroll_load_offset: 20,

    /**
     * How many items will be loaded when load more action is performed.
     */
    _load_next_offset: 100,

    /**
     * From which point products loading will be started.
     */
    _load_next_start: 0,

    /**
     * When to start showing new products - from bottom of the page.
     */
    _scroll_load_bottom_offset: 500,

    /**
     * Queue for preloaded products.
     */
    _product_queue: [],

    /**
     * Can auto append method can be used for autoloaded products?
     */
    _autoappend: false,

    /**
     * Current filters state - mobile / desktop.
     */
    _current_mobile_filter_state: 0,

    /**
     * Performs initialization for listing object.
     */
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

    /**
     * Loads products after clicking on Load more button.
     *
     * @deprecated since loadMoreProducts method intruduced.
     */
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

    /**
     * Callback for getMoreProducts
     *
     * @see Mall.listing.getMoreProducts
     * @param data
     * @returns {boolean}
     */
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
                setTimeout(function () {Mall.listing.placeListingFadeContainer();}, 1000);
            });
            // set current items count
            Mall.listing.addToVisibleItems(data.content.rows);
            Mall.listing.setTotal(data.content.total);
            Mall.listing.placeListingFadeContainer();
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

    /**
     * Listens for scroll event and loads products from queue.
     *
     * @returns {Mall.listing}
     */
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

        return this;
    },

    /**
     * Appends products from queue to listing.
     *
     * @returns {Mall.listing}
     */
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

        return this;
    },

    /**
     * Loads products to queue and auto append first part of them to listing.
     *
     * @returns {Mall.listing}
     */
    loadMoreProducts: function () {
        "use strict";
        this.setAutoappend(true);
        this.loadToQueue();

        return this;
    },

    /**
     * Loads products to queue.
     */
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

    /**
     * Callback function called after loadToQueue.
     *
     * @param data
     */
    appendToQueueCallback: function (data) {
        if (!jQuery.isEmptyObject(data) && data.status !== undefined && data.status === true) {
            var sortArray = typeof data.content.sort === "string"
                || data.content.sort instanceof String ? data.content.sort.split(" ") : [];
            Mall.listing.setSort(sortArray[0] === undefined ? "" : sortArray[0]);
            Mall.listing.setDir(data.content.dir === undefined ? "" : data.content.dir);

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
                // build wishlist collection
                jQuery.each(data.content.products, function (index, item) {
                    "use strict";
                    Mall.wishlist.addProduct({
                        id: item.entity_id,
                        wishlist_count: item.wishlist_count,
                        in_your_wishlist: item.in_my_wishlist ? true : false
                    });
                });
            } else {
                // @todo hide buttons etc
                Mall.listing.removeLockFromQueue(); // this is dummy expression
            }
        } else {
            alert("Something went wrong, try again later");
        }

        Mall.listing.removeLockFromQueue();
    },

    /**
     * Appending products to listing's HTML.
     * Returns array of appended products - html nodes.
     *
     * @param products
     * @returns {Array}
     */
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
        var container,
            box,
            link,
            figure,
            vendor,
            priceBox,
            colPrice,
            likeClass,
            likeText,
            like,
            likeIco;

        container = jQuery("<div/>", {
            "class": "item col-phone col-xs-4 col-sm-4 col-md-3 col-lg-3 size14"
        });
        box = jQuery("<div/>", {
            "class": "box_listing_product"
        }).appendTo(container);

        link = jQuery("<a/>", {
            href: product.current_url
        }).appendTo(box);

        figure = jQuery("<figure/>", {
            "class": "img_product"
        }).appendTo(link);

        jQuery("<img/>", {
            src: product.listing_resized_image_url,
            alt: product.name,
            "class": "img-responsive"
        }).appendTo(figure);

        vendor = jQuery("<div/>", {
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

        priceBox = jQuery("<div/>", {
            "class": "price clearfix"
        }).appendTo(link);

        colPrice = jQuery("<div/>", {
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

        likeClass = "like";
        likeText = "<span></span>";
        if(product.in_my_wishlist) {
            likeClass += " liked";
            likeText = "<span>Ty + </span>";
        }
        like = jQuery("<div/>", {
            "class": likeClass,
            "data-idproduct": product.entity_id,
            "data-status": product.in_my_wishlist,
            onclick: product.in_my_wishlist
                ? "Mall.wishlist.removeFromSmallBlock(this);return false;"
                : "Mall.wishlist.addFromSmallBlock(this);return false;"
        }).appendTo(priceBox);

        likeIco = jQuery("<span/>", {
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
            html: likeText + (parseInt(product.wishlist_count, 10) > 0
                ? product.wishlist_count : "")
        }).appendTo(like);

        jQuery("<div/>", {
            "class": "toolLike"
        }).appendTo(like);

        return container;
    },

    /**
     * Attaches events to products inserted to listing.
     */
    attachEventsToProducts: function() {

        var itemProduct = jQuery('.box_listing_product'),
            textLike,
            itemProductId;
        itemProduct.on('click', '.like', function(event) {
            event.preventDefault();
            /* Act on the event */
            itemProductId = jQuery(this).data('idproduct');
        });
        itemProduct.on('mouseenter', '.like', function(event) {
            event.preventDefault();
            if (jQuery(this).hasClass('liked')) {
                textLike = 'Dodane do ulubionych';
            } else {
                textLike = 'Dodaj do ulubionych';
            }
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
            jQuery(this).find('img:visible').animate({transform: 'scale(1.0)'}, 200);
        });
        itemProduct.on('mousedown', '.liked', function(event) {
            event.preventDefault();
            var textLike = 'Usunięte z ulubionych';
            jQuery(this).find('.toolLike').show().text(textLike);
        });
    },

    /**
     * Return whether loading more products is possible,
     * in addition hide show more button and shape listing.
     *
     * @returns {Mall.listing}
     */
    canLoadMoreProducts: function() {
        if(this._current_visible_items >= this._current_total) {
            this.hideLoadMoreButton()
                .hideShapesListing();
        }

        return this;
    },

    /**
     * Hides shape listing.
     *
     * @returns {Mall.listing}
     */
    hideShapesListing: function() {
        jQuery("#items-product").find(".shapes_listing").hide();

        return this;
    },

    /**
     * Shows shape listing.
     *
     * @returns {Mall.listing}
     */
    showShapesListing: function() {
        jQuery("#items-product").find(".shapes_listing").show();

        return this;
    },

    /**
     * Hides load more button from frontend.
     *
     * @returns {Mall.listing}
     */
    hideLoadMoreButton: function() {
        jQuery("#content-main").find(".addNewPositionListProductWrapper").hide();

        return this;
    },

    /**
     * Shows load more button.
     *
     * @returns {Mall.listing}
     */
    showLoadMoreButton: function() {
        jQuery("#content-main").find(".addNewPositionListProductWrapper").show();

        return this;
    },

    /**
     * Moves filters sidebar to mobile container.
     *
     * @returns {Mall.listing}
     */
    insertMobileSidebar: function() {
        if(this.getCurrentMobileFilterState() == 0) {
            var currentSidebar = jQuery("#sidebar").clone(true, true);
            jQuery("#sidebar").find(".sidebar").remove();
            jQuery(".fb-slidebar-inner").find('.sidebar').remove();
            jQuery(".fb-slidebar-inner").html(currentSidebar.html());
            this.setCurrentMobileFilterState(1);
            this.attachShowMoreEvent();
            this.attachFilterColorEvents();
            this.attachFilterEnumEvents();
            this.attachFilterDroplistEvents();
            this.attachFilterFlagEvents();
            this.attachFilterPriceSliderEvents();
            this.attachFilterSizeEvents();
            this.attachDeleteCurrentFilter();
        }

        return this;
    },

    /**
     * Attaches delete single filter action.
     *
     * @returns {Mall.listing}
     */
    attachDeleteCurrentFilter: function () {
        "use strict";
        jQuery('.current-filter, .view_filter').on('click', '.label>i', function(event) {
            var removeUrl = jQuery(event.target).attr("data-params");
            location.href = removeUrl;
            event.preventDefault();
            var lLabel = jQuery(this).closest('dd').find('.label').length - 1;
            if (lLabel >= 1) {
                jQuery(this).closest('.label').remove();

            } else {
                jQuery(this).closest('dl').remove();
            };
            if (lLabel == 0) {
                jQuery('#view-current-filter').find('.view_filter').css('margin-top', 20);
            }
        });
        jQuery('.current-filter, .view_filter').on('click', '.action a', function(event) {
            jQuery(this).closest('dl').remove();
            jQuery('#view-current-filter').find('.view_filter').css('margin-top', 24);
        });

        return this;
    },

    /**
     * Moves filters sidebar to desktop container.
     *
     * @returns {Mall.listing}
     */
    insertDesktopSidebar: function() {
        if(this.getCurrentMobileFilterState() == 1) {
            var currentSidebar = jQuery(".fb-slidebar-inner").clone(true, true);
            jQuery(".fb-slidebar-inner").find('.sidebar').remove();
            jQuery("#sidebar").find(".sidebar").remove();
            jQuery("#sidebar").append(currentSidebar);
            this.setCurrentMobileFilterState(0);
            this.attachShowMoreEvent();
            this.attachFilterColorEvents();
            this.attachFilterEnumEvents();
            this.attachFilterDroplistEvents();
            this.attachFilterFlagEvents();
            this.attachFilterPriceSliderEvents();
            this.attachFilterSizeEvents();
        }

        return this;
    },

    /**
     * Return current mobile filters state. Is mobile or not.
     *
     * @returns {*}
     */
    getCurrentMobileFilterState: function() {
        return this._current_mobile_filter_state;
    },

    /**
     * Attaches event for show more button in filter section.
     *
     * @returns {Mall.listing}
     */
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

        return this;
    },

    /**
     * Attaches events to color filter.
     *
     * @returns {Mall.listing}
     */
    attachFilterColorEvents: function() {
        jQuery(".filter-color").find("[data-url]").on("click", function(e) {
            // @todo ajax logic
            location.href = jQuery(this).attr("data-url");
        });

        return this;
    },

    /**
     * Attaches events to flag filters.
     *
     * @returns {Mall.listing}
     */
    attachFilterFlagEvents: function() {
        jQuery(".filter-flags").find("[data-url]").on("click", function(e) {
            // @todo ajax logic
            location.href = jQuery(this).attr("data-url");
        });

        return this;
    },

    /**
     * Attaches events to Enum filters.
     * @returns {Mall.listing}
     */
    attachFilterEnumEvents: function() {
        jQuery(".filter-enum").find("[data-url]").on("click", function(e) {
            // @todo ajax logic
            location.href = jQuery(this).attr("data-url");
        });

        return this;
    },

    /**
     * Attaches events to size filters.
     *
     * @returns {Mall.listing}
     */
    attachFilterSizeEvents: function() {
        var filterSize = jQuery('.filter-size'),
            btnClear = jQuery('.action.clear'),
            filterSizeLength;

        jQuery(".filter-size").find("[data-url]").on("click", function(e) {
            // @todo ajax logic
            location.href = jQuery(this).attr("data-url");
        });

        filterSize.on('click', ':checkbox', function(e) {
            filterSizeLength = jQuery(this).parents(".filter-size").find(':checked').length;
            if (filterSizeLength > 0) {
                jQuery(this).parents(".filter-size").find("div.action.clear").removeClass("hidden");
            } else {
                jQuery(this).parents(".filter-size").find("div.action.clear").addClass("hidden");
            }
        });

        filterSize.on('click', '.clear', function(event) {
            event.preventDefault();
            jQuery(this).closest('div.action.clear').addClass('hidden');

        });

        return this;
    },

    /**
     * Attaches events for droplist filters.
     *
     * @returns {Mall.listing}
     */
    attachFilterDroplistEvents: function() {
        var headList = jQuery('.button-select.ajax'),
            listSelect = jQuery('.dropdown-select ul'),
            thisVal,
            thisUrl;
        headList.on('click', function(event) {
            event.preventDefault();
            jQuery(this).next('.dropdown-select').stop(true).slideToggle(200);
        });
        listSelect.on('click', 'a', function(event) {
            event.preventDefault();
            thisVal = jQuery(this).html();
            thisUrl = jQuery(this).attr("data-url");
            jQuery(this).closest('.select-group').find('.button-select')
                .html(thisVal+'<span class="down"></span>');
            jQuery(this).closest('.dropdown-select').slideUp(200);
            window.location.href = thisUrl;
        });
        jQuery(document).click(function(e) {
            if (!jQuery(e.target).parents().andSelf().is('.select-group')) {
                jQuery(".dropdown-select").slideUp(200);
            }
        });

        return this;
    },

    /**
     * Attaches events for price slider filters.
     *
     * @returns {Mall.listing}
     */
    attachFilterPriceSliderEvents: function() {
        var sliderRange = jQuery( "#slider-range"),
            minPrice,
            maxPrice;
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
        }

        jQuery("#filter_price").find("input.filter-price-range-submit").on("click", function(e) {
            e.preventDefault();
            // validate prices
            minPrice = Mall.listing.getMinPriceFromSlider();
            maxPrice = Mall.listing.getMaxPriceFromSlider();
            if(isNaN(parseInt(minPrice, 10))
                || isNaN(parseInt(maxPrice, 10))
                || parseInt(minPrice, 10) < 0
                || parseInt(maxPrice, 10) < 0
                || parseInt(minPrice, 10) >= parseInt(maxPrice, 10)) {

            } else {
                // pop price from fq
                Mall.listing.removeSingleFilterType(this);
                var fq = Mall.listing.getFiltersArray();
                if(jQuery.isEmptyObject(fq) || jQuery.isEmptyObject(fq.fq)) {
                    fq = {fq: {}};
                }

                fq.fq.price = Mall.listing.getMinPriceFromSlider()
                    + " TO "
                    + Mall.listing.getMaxPriceFromSlider();
                Mall.listing.setFiltersArray(fq);
                Mall.listing.reloadListing();
            }
        });

        jQuery("#filter_price").find("input.filter-price-range-submit").on("mouseover"
            , function(e) {
            "use strict";
            minPrice = Mall.listing.getMinPriceFromSlider();
            maxPrice = Mall.listing.getMaxPriceFromSlider();
            jQuery(this).tooltip({
                title: Mall.translate.__("price-filter-not-valid", "Prices in filter are not valid.")
            });
            if(isNaN(parseInt(minPrice, 10))
                || isNaN(parseInt(maxPrice, 10))
                || parseInt(minPrice, 10) < 0
                || parseInt(maxPrice, 10) < 0
                || parseInt(minPrice, 10) >= parseInt(maxPrice, 10)) {
                jQuery(this).tooltip("enable");
                jQuery(this).tooltip("show");
            } else {
                jQuery(this).tooltip("hide");
                jQuery(this).tooltip("disable");
            }
        });

        jQuery("#zakres_min, #zakres_max").on("keyup keypress", function (e) {
            "use strict";
            var code = e.keyCode || e.which;
            if (code === 13) {
                e.preventDefault();
                return false;
            }
        });


        return this;
    },

    /**
     * Toggles show more / hide more state link in filter section.
     *
     * @param item
     * @returns {Mall.listing}
     */
    toggleShowMoreState: function(item) {
        var state = jQuery(item).attr("data-state");
        if(state == "0") {
            jQuery(item).text("Pokaż mniej");
            jQuery(item).attr("data-state", "1");
        } else {
            jQuery(item).text("Pokaż więcej");
            jQuery(item).attr("data-state", "0");
        }

        return this;
    },

    /**
     * Reloads listing - takes current params in count.
     *
     */
    reloadListing: function() {
        var protocol  = window.location.protocol,
            host = window.location.host,
            pathname = window.location.pathname;

        window.location.href = protocol + "//" + host + pathname + "?"
            + jQuery.param(this.getQueryParams());
    },

    /**
     * Returns complete array of params for querying solr.
     * Reloading supresses page, and start param.
     *
     * @returns {{fq: Array, q: *, page: *, sort: *, dir: *, scat: *, rows: *, start: *}}
     */
    getQueryParams: function() {
        var q = {
            fq: jQuery.isEmptyObject(this.getFiltersArray())
                || this.getFiltersArray().fq === undefined ? [] : this.getFiltersArray().fq,
            q: this.getQuery(),
            page: this.getPage(),
            sort: this.getSort(),
            dir: this.getDir(),
            scat: this.getScat(),
            rows: this.getScrollLoadOffset(),
            start: 0
        };

        return q;
    },

    /**
     * Removes single filter from filters array.
     *
     * @param filter
     * @returns {Mall.listing}
     */
    removeSingleFilterType: function(filter) {
        var filterType = jQuery(filter).attr("data-filter-type"),
            filters = jQuery.isEmptyObject(this.getFiltersArray())
                || jQuery.isEmptyObject(this.getFiltersArray().fq) ? [] : this.getFiltersArray().fq;
        delete filters[filterType];

        return this;
    },

    /**
     * Calculates position and places shape listing fade block.
     *
     * @returns {Mall.listing}
     */
    placeListingFadeContainer: function() {
        var heights = [],
            min,
            con;
        // check if body has proper class
        if(jQuery("body").hasClass("node-type-list")
            && this.getTotal() > 20
            && this.canLoadMoreProducts()
            && this.canShowLoadMoreButton()) {

            jQuery('.node-type-list #items-product .item').each(function() {
                heights.push(jQuery(this).height());
            });
                min = Math.min.apply( Math, heights );
                con = jQuery('#items-product').innerHeight();
            jQuery('#items-product').not('.list-shop-product').css('height', 'auto');
            jQuery('#items-product').not('.list-shop-product').css('height', con-min);
            jQuery(".shapes_listing").css("top"
                , jQuery(".addNewPositionListProduct").position().top - 40);
        } else {
            this.hideLoadMoreButton();
            this.hideShapesListing();
        }

        return this;
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
            setTimeout(function() {Mall.listing.placeListingFadeContainer();}, 1000);

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

    /**
     * Returns max price set in slider price.
     *
     * @returns {number}
     */
    getMaxPriceFromSlider: function() {
        var price = jQuery("#zakres_max").val();
        return price == '' ? 0 : price;
    },

    /**
     * Returns query from search field which is set by backend.
     *
     * @returns {string}
     */
    getQuery: function() {
        return this._current_query;
    },

    /**
     * Returns current page. Currently turned off.
     *
     * @returns {string}
     */
    getPage: function() {
        // for now it's turned off
        return "";
//        return this._current_page;
    },

    /**
     * Returns current sort type.
     *
     * @returns {string}
     */
    getSort: function() {
        return this._current_sort;
    },

    /**
     * Returns current sort direction.
     *
     * @returns {string}
     */
    getDir: function() {
        return this._current_dir;
    },

    /**
     * Returns current category.
     *
     * @returns {string}
     */
    getScat: function() {
        return this._current_scat;
    },

    /**
     * Returns current filters.
     *
     * @returns {*}
     */
    getFiltersArray: function() {
        return this._current_filters;
    },

    /**
     * Returns currently visible items on listing.
     *
     * @returns {number}
     */
    getCurrentVisibleItems: function() {
        return this._current_visible_items;
    },

    /**
     * Returns total number of items in current filter/category listing.
     *
     * @returns {number}
     */
    getTotal: function() {
        return this._current_total;
    },

    /**
     * Returns current price range from price filter.
     *
     * @returns {*}
     */
    getCurrentPriceRange: function() {
        return this._current_price_rage;
    },

    /**
     * Returns how many pixels from bottom show new products will be started.
     *
     * @returns {number}
     */
    getScrollBottomOffset: function() {
        return this._scroll_load_bottom_offset;
    },

    /**
     * Returns current scroll load offset.
     *
     * @returns {number}
     */
    getScrollLoadOffset: function () {
        return this._scroll_load_offset;
    },

    /**
     * Returns state for scroll load lock.
     *
     * @returns {boolean}
     */
    getScrollLoadLock: function () {
        return this._scroll_load_lock;
    },

    /**
     * Returns start point for load more products.
     *
     * @returns {number}
     */
    getLoadNextStart: function () {
        return this._load_next_start;
    },

    /**
     * Returns current product queue.
     *
     * @returns {*}
     */
    getProductQueue: function () {
        return this._product_queue;
    },

    /**
     * Returns how many products should be loaded in next load request.
     *
     * @returns {number}
     */
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

    /**
     * Sets product queue to given value.
     *
     * @param queue
     * @returns {Mall.listing}
     */
    setProductQueue: function (queue) {
        this._product_queue = queue;

        return this;
    },

    /**
     * Increments current page.
     *
     * @returns {Mall.listing}
     */
    setPageIncrement: function() {
        this._current_page += 1;

        return this;
    },

    /**
     * Sets sort direction.
     *
     * @param dir
     * @returns {Mall.listing}
     */
    setDir: function(dir) {
        this._current_dir = dir;

        return this;
    },

    /**
     * Sets sort type.
     *
     * @param sort
     * @returns {Mall.listing}
     */
    setSort: function(sort) {
        this._current_sort = sort;

        return this;
    },

    /**
     * Sets query.
     *
     * @param query
     * @returns {Mall.listing}
     */
    setQuery: function(query) {
        this._current_query = query;

        return this;
    },

    /**
     * Sets current category.
     *
     * @param scat
     * @returns {Mall.listing}
     */
    setScat: function(scat) {
        this._current_scat = scat;

        return this;
    },

    /**
     * Sets current filters.
     *
     * @param filters
     * @returns {Mall.listing}
     */
    setFiltersArray: function(filters) {
        this._current_filters = filters;

        return this;
    },

    /**
     * Adds given items count to visible items on listing.
     *
     * @param itemsCount
     * @returns {Mall.listing}
     */
    addToVisibleItems: function(itemsCount) {
        this._current_visible_items += parseInt(itemsCount);

        return this;
    },

    /**
     * Sets total items.
     *
     * @param total
     * @returns {Mall.listing}
     */
    setTotal: function(total) {
        this._current_total = parseInt(total);

        return this;
    },

    /**
     * Sets current mobile filters state.
     *
     * @param state
     * @returns {Mall.listing}
     */
    setCurrentMobileFilterState: function(state) {
        this._current_mobile_filter_state = state;

        return this;
    },

    /**
     * Sets current price range selected in price slider filter.
     *
     * @param min
     * @param max
     * @returns {Mall.listing}
     */
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

jQuery(document).ready(function () {
    "use strict";
    Mall.listing.init();
});
