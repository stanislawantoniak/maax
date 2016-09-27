/**
 * Created by pawelchyl on 26.08.2014.
 */

/**
 * Object for processing product listing.
 */
Mall.listing = {

	/**
	 * @type Number
	 */
	_ajaxTimeout: 500,

	/**
	 * @type Number
	 */
	_ajaxTimer: null,

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
	 * Total items for current listing.
	 */
	_current_total: 0,

	/**
	 * Current price range - filter.
	 */
	_current_price_rage: [0, 0],

	/**
	 * Current filters state - mobile / desktop.
	 */
	_current_mobile_filter_state: 0,

	/**
	 * Currently opened sections
	 */
	_current_opened: {},

	/**
	 * Currently show more
	 */
	_current_filters_show_more: {},

	/**
	 * Currently searches
	 */
	_current_search: {},

	/**
	 * Cache for ajax request
	 */
	_ajaxCache: {},

	/**
	 * @type Boolean
	 * Determines if history state should not be pushed
	 */
	_noPushState: false,

	/**
	 * @type Boolean
	 * Determines if ajax loading should not be delayed
	 */
	_noDelay: false,
	
    /**
     * clicked url
     */
	_current_url: '',

	/**
	 * products on page (php determines it based on user agent)
	 */
	_products_per_page: 0,

	/**
	 * Performs initialization for listing object.
	 */
	init: function () {
        //hide btn filter product if no products (search for example)
        this.processActionViewFilter();

		// Reset form
		this.resetForm();

		// fill cache from static contents - not modified by js
		this._rediscoverCache();

		// Init thinks
		this.initFilterEvents();

		// filters delegation - better performance than initiation of each filter event on every ajax reload
		this.delegatePaginationEvents();
		this.delegateFilterEvents();
        this.delegateSaveContextForProductPage();

		this.initOnpopstateEvent();
		this.initSortEvents(false);
		this.initActiveEvents();
		this.initListingLinksEvents();

		// Add custom events to products
		this.preprocessProducts();

		//prevents filters from overlapping lp banner
		this.positionFiltersAfterLpBannerLoad();

		this.initShuffle();
	},

    initShuffle: function() {
        jQuery(document).ready(function() {
            jQuery('#grid')
                .on('layout.shuffle', function() {
                    Mall.listing.hideListingOverlay();
                    Mall.listing.likePriceView();
		            Mall.listing.placeListingFadeContainer();
		            Mall.Footer.setContainerPadding();
                })
                .shuffle({throttleTime: 800, speed: 0, easing: 'linear' });
	        jQuery(window).resize();
        });
    },

    /**
     * hide btn filter product if no products/no sidebar (search for example)
     */
    processActionViewFilter: function() {
        if (!jQuery('#solr_search_facets').length && !this.isVendorLandingpage()) {
            jQuery('.view_filter:has(.actionViewFilter)').hide();
        }
    },

	/**
	 * Prepare response object based on static html data
	 * @returns {undefined}
	 */
	_rediscoverCache: function(){
		var content = {
				filters: this.getFilters().prop("outerHTML"),
				toolbar: this.getToolbar().prop("outerHTML"),
				pager: this.getPager().prop("outerHTML"),
                category_with_filters: this.getCategoryWithFilters().prop("outerHTML"),
                header: this.getHeader().prop("outerHTML"),
				products: this.getInitProducts(),
				total: this.getTotal(),
				query: this.getQuery(),
				sort: this.getSort(),
				dir: this.getDir(),
				url: document.location.href,
				document_title: document.title
			},
			ajaxData = Mall.listing.getQueryParamsAsArray(),
			ajaxKey = Mall.listing.getAjaxHistoryKey(ajaxData),
			title = document.title;

		if(this.getPushStateSupport()) {
			window.history.replaceState({title: title, ajaxKey: ajaxKey, ajaxData: ajaxData}, title, document.location.href);
		}

		// Bind to cache
		this._ajaxCache[ajaxKey] = {
			status: 1,
			content: content
		};
	},

	resetForm: function(){
        if(this.getFilters().find("form").length) {
            this.getFilters().find("form").get(0).reset();
        }
	},

	initFilterEvents: function(scope){
		scope = scope || jQuery(".solr_search_facets");
		this.preprocessFilterContent(scope);
		this.initScrolls(scope);
		this.attachFilterColorEvents(scope);
		this.attachFilterIconEvents(scope);
		this.attachFilterEnumEvents(scope);
		this.attachFilterPriceEvents(scope);
		this.attachFilterDroplistEvents(scope);
		this.attachFilterLongListEvents(scope);
		this.attachFilterFlagEvents(scope);
		this.attachFilterPriceSliderEvents(scope);
		this.attachFilterSizeEvents(scope);
		this.attachDeleteCurrentFilter();
		this.initListingLinksEvents();
		this.updateFilters();
	},


	// Do remember roll downs & showmors
	preprocessFilterContent: function(content){
		var self = this;
		// Restore state of rolling
		jQuery.each(this.getCurrentOpened(), function(idx, value){
			var el = jQuery("#" + idx, content);
			if(!el.length){
				return;
			}
			self._doRollSection(el.parent(), value, false);
		});

		// Restore state of show more
		jQuery.each(this.getCurrentFiltersShowMore(), function(idx, value){
			var el = jQuery("#" + idx, content);
			if(!el.length){
				return;
			}
			self._doShowMore(el.parent(), value);
		});
	},

	/**
	 * Handle clear handle after add/rem favorites
	 */
	preprocessProducts: function(){
		var self = this;
		this.getProducts().find(".item:not(.processed)").each(function(){
			var el = jQuery(this);
			el.find(".like").click(function(){
				self._clearAjaxCache();
			});
			el.addClass("processed");
		});
	},

    likePriceView: function(){
        Mall.wishlist.likePriceView();
    },


	_doRollSection: function(section, state){
		var title     = section.find("h3"),
			content   = section.find(".content"),
            contentXS = section.find(".content-xs"),
			i = title.find('i'),
			open = 'open',
			closed = 'closed',
			arrowUp = 'fa-chevron-up',
			arrowDown = 'fa-chevron-down',
			isMobile = this.getCurrentMobileFilterState(),
			dataAttr = 'data-' + (isMobile ? 'xs' : 'lg') + '-rolled';
		if(state){
			content.show();
			title.removeClass(closed).addClass(open);
			i.removeClass(arrowDown).addClass(arrowUp);
			section.attr(dataAttr,open);
			contentXS.hide();
		} else {
			content.hide();
			title.removeClass(open).addClass(closed);
			i.removeClass(arrowUp).addClass(arrowDown);
			section.attr(dataAttr,closed);
			if(isMobile) {
				contentXS.show();
			}
		}
		if(!isMobile) {
			contentXS.hide();
		}
        var mallListing = Mall.listing,
	        categoryWithFilters = mallListing.getCategoryWithFilters(),
	        facetsHeight;
        content = mallListing.getContentBlock();

        if(!isMobile) {
            facetsHeight = mallListing.getFilters().height();
            categoryWithFilters
                .removeClass(mallListing.getFiltersClassMobile())
                .addClass(mallListing.getFiltersClassDesktop())
                .css({
                    "left": content.offset().left + 15,
                    "top": (facetsHeight + content.offset().top + 15),
                    'height': ''
                });
        } else {
            facetsHeight = mallListing.getFilters().find("[name=searchFacets]").height();
            categoryWithFilters
                .removeClass(mallListing.getFiltersClassDesktop())
                .addClass(mallListing.getFiltersClassMobile())
                .css({
                    "left": "",
                    "top": (facetsHeight)
                });
        }

		Mall.listing.setMainSectionHeight();
	},

	_doShowMore: function(section, state) {
		var link = section.find(".showmore-filters"),
			content = section.find("[data-state='hidden']");

		if (state) {
			content.show();
			link.text(Mall.translate.__("Show less"));
			link.attr("data-state", "1");
		} else {
			content.hide();
			link.text(Mall.translate.__("Show more"));
			link.attr("data-state", "0");
		}
	},

	/**
	 * Shows listing loading.
	 * @returns {Mall.listing}
	 */
	showLoading: function () {
		jQuery("#listing-loading").show();
		return this;
	},

	/**
	 * Hides listing loading.
	 * @returns {Mall.listing}
	 */
	hideLoading: function () {
		jQuery("#listing-loading").hide();
		return this;
	},

	/**
	 * Appending products to listing's HTML.
	 * Returns array of appended products - html nodes.
	 *
	 * @param products
	 * @returns Array
	 */
	appendToList: function (products) {
        var grid = jQuery('#grid'),
	        eachItemsHtml = [];

		jQuery.each(products, function(index, item) {
            eachItemsHtml.push( Mall.listing.createProductEntityImprove(item) );
            Mall.wishlist.addProduct({
                id: item[0],
                       wishlist_count: item[5],
                in_your_wishlist: item[6] ? true : false
                    });
		});

        grid.append(eachItemsHtml);
        grid.shuffle('appended', grid.find('.item:not(.shuffle-item)'));

		// attach events
		this.preprocessProducts();

		return eachItemsHtml;
	},

	/**
	 * Creates single product container for listing.
     *
     * ALERT:
     * product is no longer an object, now it's array with numeric indexes:
     * product[0] = id
     * product[1] = name
     * product[2] = url
     * product[3] = price
     * product[4] = final_price
     * product[5] = wishlist_count
     * product[6] = in_my_wishlist
     * product[7] = image_url
     * product[8] = image_ratio
     * product[9] = manufacturer_logo_url
	 * product[10]= sku
	 * product[11]= skuv
     *
	 * @param product
     * @returns {string}
	 */
    createProductEntityImprove: function(product) {
        var oldPrice = product[3] > product[4] ?  "<span class='old'>" + number_format(product[3], 2, ",", " ") + " " + Mall.getCurrencyBasedOnCode('PLN') +"</span>" : "",
	        likeClass = "like" + (product[6] ? " liked" : ""),
	        likeText = (product[6] ? "<span>Ty + </span>" : "<span></span>") + (parseInt(product[5], 10) > 0 ? (product[5] > 99) ? "99+ " : product[5] : "") + " ",
	        likeOnClick = product[6] ? "Mall.wishlist.removeFromSmallBlock(this);return false;" : "Mall.wishlist.addFromSmallBlock(this);return false;";


		return "<div id='prod-" + product[0] + "' class='item col-phone col-xs-4 col-sm-4 col-md-3 col-lg-3 size14'>"+
            "<div class='box_listing_product'>"+
                "<a href='" + product[2] +"' data-entity='" + product[0] +"' data-sku='" + product[10] + "' data-skuv='" + product[11] + "'>"+
                    "<figure class='img_product' style='padding-bottom: " + product[8] +"%'>"+
                        "<img src='" + Mall.productImagesUrl + product[7] + "' alt='" + product[1] + "' class='img-responsive'>"+
                    "</figure>"+
                    "<div class='logo_manufacturer' style='background-image:url(" + Mall.manufacturerImagesUrl + product[9] +")' ></div>"+
                    "<div class='name_product'>" + product[1] + "</div>"+
                "</a>"+
                "<div class='price clearfix'>"+
                    "<div class='col-price'>" + oldPrice +
                        "<span>" + (number_format(product[4], 2, ",", " ") + " " + Mall.getCurrencyBasedOnCode(product.currency)) + "</span>"+
                    "</div>"+
                    "<div class='"+likeClass+"' data-idproduct='"+product[0]+"'>"+
                        "<span class='like_count'>" + likeText + "</span>"+
                        "<span class='icoLike'></span>"+
                        "<div class='toolLike'></div>"+
                    "</div>"+
                "</div>"+
            "</div>"+
        "</div>";
    },

	_listing_overlay_class: 'listing-overlay',

	getListingOverlay: function() {
		return jQuery('.'+ this._listing_overlay_class);
	},

	showListingOverlay: function() {
		return this.getListingOverlay().show();
	},

	hideListingOverlay: function() {
		return Mall.listing.getListingOverlay().hide();
	},

	/**
	 * @param node
	 * @returns {Mall.listing}
	 */
	nodeChanged: function (node){
		this._current_url = node.data('url');
		this.reloadListing();
		return this;
	},

	/**
	 * @returns {Mall.listing}
	 */
	reloadListing: function(){
		if(this.getPushStateSupport()) {
			this._captureListingMeta();
			this._doAjaxRequest();
		}
		return this;
	},

	/**
	 * @returns {Mall.listing}
	 */
	reloadListingNow: function(){
		this.setNoDelay(true);
		return this.reloadListing();
	},

	/**
	 * @returns {Mall.listing}
	 */
	reloadListingNoPushState: function(){
		this.setNoDelay(true);
		this.setNoPushstate(true);
		return this.reloadListing();
	},

	/**
	 *
	 */
	_captureListingMeta: function(){
		// Collect
		var filters = this.getFilters().find(".section"),
			self = this;

		// Overrride existing fields - remember inactive diel
		filters.each(function(){
			var el = jQuery(this),
				id = el.find(".content").attr("id"),
				sm = el.find(".showmore-filters"),
				sr = el.find(".longListSearch");

			self._current_opened[id] = el.find("h3").hasClass("open");

			// Show more is optional
			if(sm.length){
				self._current_filters_show_more[id] = sm.attr("data-state")=="1";
			}

			// Search strings
			if(sr.length){
				self._current_search[id] = sr.val();
			}
		});
	},

	/**
	 * @returns {type|Number}
	 */
	getAjaxTimeout: function(){
		return this._ajaxTimeout;
	},

	/**
	 * @returns {void}
	 */
	_doAjaxRequest: function(){
		this._ajaxStop();
		this._ajaxStart();
	},

	/**
	 * Clear ajax cache to prevent
	 * @param {bool} onlyCurrentUrl
	 */
	_clearAjaxCache: function(onlyCurrentUrl){
		if(onlyCurrentUrl){
			var key = this._buildKey(Mall.listing.getQueryParamsAsArray());
			if(this._ajaxCache[key]){
				delete this._ajaxCache[key];
			}
			return;
		}
		this._ajaxCache = {};
	},

	/**
	 * @returns {void}
	 */
	_ajaxStart: function(){
		var self = this;
		if(this.getNoDelay()) {
			this.setNoDelay(false);
			self._ajaxSend.apply(self);
		} else {
			this._ajaxTimer = setTimeout(
				function(){self._ajaxSend.apply(self)},
				this.getAjaxTimeout()
			);
		}
	},

	/**
	 * @returns {void}
	 */
	_ajaxStop: function(){
		if(this._ajaxTimer){
			clearTimeout(this._ajaxTimer);
		}
	},

	/**
	 * @param {array} data
	 * @returns {String}
	 */
	_buildKey: function(data){
		var out = [];

		jQuery.each(data, function(){
			out.push(encodeURIComponent(this.name) + "=" + encodeURIComponent(this.value));
		});

		return out.join("&");
	},

	/**
	 * @returns {string} - url without parameters
	 */
	_getUrlNoParams: function() {
		return location.protocol + '//' + location.host + location.pathname;
	},

	/**
	 * @returns {object} - all parameters as object of objects
	 */
	_getUrlObjects: function() {
		var url = decodeURI(window.location.href.replace(Mall.listing._getUrlNoParams()+"?","")),
			result = {};

		var tmpObj = url.split("&");

		for(var key in tmpObj) {
			if (tmpObj.hasOwnProperty(key)) {
				var tmp = tmpObj[key].split("=");
				result[key] = {};
				result[key][decodeURIComponent(tmp[0])] = decodeURIComponent(tmp[1]);
			}
		}

		return result;
	},

	/**
	 * variable that tells ajax if it should get cache key from site or from window.history.state
	 */
	onPopStated: false,
	/**
	 * @returns {void} - event initialized
	 */
	initOnpopstateEvent: function() {
		if(!window.onpopstate) {
			var self = this;
			window.onpopstate = function() {
				//check url for selected filters
				var filters = self._getUrlObjects(),
					sort = false,
					dir = false;
				if (Object.keys(filters).length) {
					//uncheck all filters
					jQuery("input[type=checkbox]").prop('checked', false);
					for (var filter in filters) {
						if(filters.hasOwnProperty(filter)) {
							for (var key in filters[filter]) {
								if(filters[filter].hasOwnProperty(key)) {
									var value = filters[filter][key];
									if (key.substring(0, 2) == 'fq') {
										var importantKeyPart = key.match(/fq\[(.*?)\]/)[1];
										if(typeof importantKeyPart != "undefined") {
											key = "fq["+importantKeyPart+"][]";
										}
										jQuery("input[type=checkbox][name='" + key + "'][value='" + value + "']").prop("checked", true);
										if (key == "fq[price]") {
											// Set values of range
											var slider = jQuery("#slider-range"),
												values = value.split(" TO "),
												start = parseInt(values[0], 10),
												stop = parseInt(values[1], 10);
											slider.slider("option", "values", [start, stop]);
											jQuery("#zakres_min").val(start);
											jQuery("#zakres_max").val(stop);
											self._transferValuesToCheckbox(start, stop);
										}
									} else if (key == 'sort') {
										sort = value;
									} else if (key == 'dir') {
										dir = value;
									} else if (key == "slider" && value == "1") {
										jQuery("input[type=checkbox]#filter_slider").prop("checked", true);
									}
								}
							}
						}
					}
					if(sort && dir) {
						self.setSort(sort);
						self.setDir(dir);
					} else {
						self.setSort('wishlist_count');
						self.setDir('desc');
					}
					self.setSortSelect();
				}
				//reload listing
				self.onPopStated = true;
				self.reloadListingNoPushState();
			}
		}
	},

	/**
	 * @returns {void}
	 */
	_pushHistoryState: function(ajaxKey,ajaxData) {
		var self = this;
		if(!this.getNoPushstate()) {
			var url = self._getCurrentUrl(),
				title = document.title;
			window.history.pushState({page: title, ajaxKey: ajaxKey, ajaxData: ajaxData}, title, url);
		} else {
			self.setNoPushstate(false);
		}
	},
	_getCurrentUrl: function() {
		var url = this._current_url;
		if (url == '') {
			url = window.location.href;
		}
		return url;
	},
	/**
	 * @returns {void}
	 */
	_ajaxSend: function(forceObject){
		var self = this,
			ajaxData = self.onPopStated ? window.history.state.ajaxData : Mall.listing.getQueryParamsAsArray(forceObject),
			ajaxKey = self.onPopStated ? window.history.state.ajaxKey : Mall.listing.getAjaxHistoryKey(ajaxData);

		if(self.onPopStated) {
			self.onPopStated = false;
		}

		if(self._ajaxCache[ajaxKey]) {
			this._handleAjaxRepsonse(self._ajaxCache[ajaxKey]);
			return;
		}


		this.showAjaxLoading();
		OrbaLib.Listing.getBlocks(
			ajaxData,
			function(response){
				if(response.status){
					// Cache only success response
					self._ajaxCache[ajaxKey] = response;
				}
				self._handleAjaxRepsonse(response,ajaxKey,ajaxData)
			}
		);
	},

	_handleResponseError: function(response){
		console.log(response);
	},

	_handleAjaxRepsonse: function(response,ajaxKey,ajaxData){
		var hideAjaxLoading = true;
		if(!response.status){
			this._handleResponseError(response);
		}else{
			var rebuildContents = this.rebuildContents(response.content,ajaxKey,ajaxData);

			if(!rebuildContents){
				hideAjaxLoading = false;
			}
		}

		if(hideAjaxLoading){
			//do not hide loader if page will be refreshed
			//(For example on Landing page last active filter remove)
			this.hideAjaxLoading();
		}

	},

	showAjaxLoading: function(){
		if(this.getFilters().length) {
			this.positionFilters();
		}
		this.getAjaxLoader().show();
	},

	hideAjaxLoading: function(){
		this.getAjaxLoader().hide();
		if(this.getFilters().length) {
			this.positionFilters();
		}
	},

	isLoading: function(){
		this.getAjaxLoader().is(":visisble");
	},

	/**
	 * var and function that stores / generates ajax loader div
	 */
	_ajax_loader: '',
	getAjaxLoader: function(){
		if(!this._ajax_loader.length) {
			var ajaxLoaderId = 'ajax-filter-loader';
			var overlay = jQuery("<div>").css({
				"background":	"rgba(255,255,255,0.8) \
					url('/skin/frontend/modago/default/images/modago-ajax-loader.gif') \
					center center no-repeat",
				"position":		"fixed",
				"width":		"100%",
				"height":		"100%",
				"left":			"0",
				"top":			"0",
				"z-index":		"1000000",
				"color":		"#fff"
			}).attr("id", ajaxLoaderId);
			jQuery("body").append(jQuery(overlay));
			this._ajax_loader = jQuery('#'+ajaxLoaderId);
		}
		return this._ajax_loader;
	},


	rebuildContents: function(content,ajaxKey,ajaxData){

		if(typeof ajaxData == 'undefined' || typeof ajaxKey == 'undefined') {
			ajaxData = Mall.listing.getQueryParamsAsArray();
			ajaxKey = Mall.listing.getAjaxHistoryKey(ajaxData);
		}

		if(content.reload_to_cms){
			//hack to reload cms page
			window.location = content.url;
			return false;
		}
		Mall.listing.showAjaxLoading();
		Mall.Scrolltop.hopToTop();

		// All filters
		var filters = jQuery(content.filters);
		if(filters.length) {
			if (this.getMobileFiltersOverlay().is(":visible")) {
				filters.show();
			}
			this.getFilters().replaceWith(filters);
			this.initFilterEvents(filters);
		}

		var header = jQuery(content.header);
		if(header.length) {
			this.getHeader().replaceWith(jQuery(content.header));
		}

		//Category with filters
		var categoryWithFilters = jQuery(content.category_with_filters);
		this.getCategoryWithFilters().replaceWith(categoryWithFilters);

		// Finally product
		this.replaceProducts(content);
		this.replacePager(content);

		// Set pushstate
		this._current_url = content.url;
		this._pushHistoryState(ajaxKey,ajaxData);

		if(typeof content.document_title != "undefined" && content.document_title.length && content.document_title != document.title) {
			try {
				document.getElementsByTagName('title')[0].innerHTML = content.document_title.replace('<','&lt;').replace('>','&gt;').replace(' & ',' &amp; ');
			} catch(e){}
		}

		this.initActiveEvents();
		this.initListingLinksEvents();

		return true;
	},

	replaceProducts: function(data){
		// 1. Reset the content
		this.getProducts().find(".item").remove();

		// 2. Reset the page and values
		this.setTotal(data.total);

		this.appendToList(data.products);
	},

	replacePager: function (data) {
		this.getPager().html(data.pager);
	},

	initListingLinksEvents: function() {
		var links = jQuery('.listing-link'),
			self = this;

		function labelClick(obj) {
			jQuery(obj).closest("label").trigger("click");
		}

		if(this.getPushStateSupport()) {
			links.off("click").on("click",function() {
				labelClick(this);
				return false;
			});
		} else {
			links.off("click").on("click",function() {
				labelClick(this);
				self.showAjaxLoading();
			});
			jQuery('input[type="checkbox"]').off("click").on("click",function() {
				self.showAjaxLoading();
				window.location = jQuery('label[for="'+jQuery(this).prop('id')+'"]').find('.listing-link').prop('href');
			});
		}
	},

	_removeActiveFilters: false, //tells ajax cache key builder to drop start parameter
	initActiveEvents: function(scope) {
		scope = scope || Mall.listing.getActiveId();
		var self = this,
			active = this.getActiveLabel(scope),
			remove = this.getActiveRemove(scope);

		if(this.getPushStateSupport()) {

			function unCheckbox(id) {
				jQuery("input[type=checkbox]#"+id).prop('checked',false);
			}

			function detachActive() {
				self.getActive(scope).find('dl *').detach();
			}

			active.click(function() {
				var me = jQuery(this);

				self._current_url = me.attr('href');

				self._removeActiveFilters = true;

				if(!me.parent().hasClass('query-text-iks')) { //not search
					me.parents('.label').detach();
					unCheckbox(me.data('input'));
					if (active.length == 1) {
						detachActive();
					}
					self.reloadListingNow();
					return false;
				} else {
					self.showAjaxLoading();
				}
			});

			remove.click(function() {
				Mall.listing._removeActiveFilters = true;
				var me = jQuery(this);

				self._current_url = me.attr('href');
				active.each(function() {
					unCheckbox(jQuery(this).data('input'));
				});
				detachActive();
				self.reloadListingNow();
				return false;
			});
		} else {
			active.click(function() {
				self.showAjaxLoading();
			});
			remove.click(function() {
				self.showAjaxLoading();
			});
		}

		var mobileFilterBtn = Mall.listing.getMobileFilterBtn();

		mobileFilterBtn.click(Mall.listing.openMobileFilters);
	},


	/**
	 *
	 *
	 * FILTERS START
	 *
	 *
	 **/
	openMobileFilters: function(e) {
		e.preventDefault();
		var self = Mall.listing;
		self.getFilters().show();

        var facetsHeight = self.getFilters().find("[name=searchFacets]").height();
        self.getCategoryWithFilters()
            .removeClass(self.getFiltersClassDesktop())
            .addClass(self.getFiltersClassMobile())
            .css({
                "left": "",
                "top": facetsHeight
            });

		jQuery('html').addClass(self.getMobileFiltersOpenedClass());
		jQuery('#sort-by').css('pointer-events','none'); //fix for clicking through filters overlay and open sorting (mobile)
		self.showMobileFiltersOverlay();
		jQuery('#'+self.getMobileFiltersOverlayId()).click(self.closeMobileFilters);
		jQuery(window).swipe(Mall.swipeOptions);
	},

	closeMobileFilters: function() {
		var self = Mall.listing;
		self.getFilters().hide();
		jQuery('html').removeClass(self.getMobileFiltersOpenedClass());
		jQuery('#sort-by').css('pointer-events','auto'); //fix for clicking through filters overlay and open sorting (mobile)
		self.hideMobileFiltersOverlay();
		jQuery('#'+self.getMobileFiltersOverlayId()).off('click');
	},

	getMobileFiltersOpenedClass: function() {
		return 'noscroll-filters';
	},

	/**
	 * variable that stores overlay
	 */
	_mobile_filters_overlay: '',

	showMobileFiltersOverlay: function() {
		this.getMobileFiltersOverlay().show();
	},

	hideMobileFiltersOverlay: function() {
		this.getMobileFiltersOverlay().hide();
	},

	getMobileFiltersOverlay: function() {
		if(!this._mobile_filters_overlay.length) {
			var overlayId = this.getMobileFiltersOverlayId();
			jQuery('#sb-site').append(
				jQuery('<div id="' + overlayId + '"></div>')
			);
			this._mobile_filters_overlay = jQuery('#'+overlayId);
		}
		return this._mobile_filters_overlay;
	},

	getMobileFiltersOverlayId: function() {
		return 'filters-overlay';
	},

	getMobileFilterBtn: function() {
		return jQuery("#filters-btn").find(".actionViewFilter");
	},

	getProducts: function(){
		return jQuery("#items-product");
	},
	getHeader: function(){
		return jQuery("#header-main");
	},

	getActive: function(scope){
		scope = scope || Mall.listing.getActiveId();
        if(!jQuery(scope).length) {
            scope = '#active-filters-wrapper';
        }
		return jQuery(scope);
	},

	getActiveId: function(){
		return "#active-filters";
	},

	getActiveLabel: function() {
		return jQuery(".active-filter-label");
	},

	getActiveRemove: function(scope) {
		scope = scope || Mall.listing.getActiveId();
		return jQuery(scope+" .active-filters-remove");
	},

	getFilters: function(){
		return jQuery("#solr_search_facets");
	},
    getCategoryWithFilters: function(){
        return jQuery("#category_with_filters");
    },

	initScrolls: function(scope, opts){
		scope = scope || jQuery(".solr_search_facets");
		opts = opts || {};

		// Destroy scrolls if exists;
		this.destroyScrolls(scope);

		var fm = jQuery(".scrollable", scope);
		if (fm.length >= 1) {
			fm.mCustomScrollbar(jQuery.extend({}, {
				scrollButtons:{
					enable:true
				},
				mouseWheel:{ scrollAmount: 50 },
				advanced:{
					updateOnBrowserResize:true
				} // removed extra commas
			}, opts));
		}
	},

	destroyScrolls: function(scope) {
		scope = scope || jQuery(".solr_search_facets");
		jQuery(".scrollable.mCustomScrollbar", scope).mCustomScrollbar("destroy");
	},

	isVendorLandingpage: function() {
		return jQuery('.umicrosite-index-landingpage').length;
	},

	/**
	 * Moves filters sidebar to mobile container.
	 *
	 * @returns {Mall.listing}
	 */
	updateFiltersMobile: function() {
		if(this.getCurrentMobileFilterState() == 0 && !this.isVendorLandingpage()) {
			this.positionFilters();
			this.setCurrentMobileFilterState(1);
		}
		if(!jQuery('#'+this.getMobileFiltersOverlayId()).length) {
			this.getFilters().hide();
		}

		return this;
	},

	/**
	 * roll and unroll sidebar sections for mobile and desktop resolution
	 * depends on current state of sidebar (mobile or desktop)
	 *
	 * @param scope
	 * @private
	 */
	_processRollSections: function(scope) {
		"use strict";
		var self = this,
			parent    = scope || this.getFilters(),
			attr     = self.getCurrentMobileFilterState() ? 'data-xs-rolled' : 'data-lg-rolled',
			sections = jQuery(".section", parent);

		sections.each(function() {
			var state = jQuery(this).attr(attr) == 'open' ? 1 : 0;
			self._doRollSection(jQuery(this), state, false);
		});
	},
	/**
	 * Attaches delete single filter action.
	 *
	 * @returns {Mall.listing}
	 */
	attachDeleteCurrentFilter: function () {
		"use strict";

		jQuery('.current-filter, .view_filter')
			.on('click', '.label>i', function(event) {
				location.href = jQuery(event.target).attr("data-params");
				event.preventDefault();
				var lLabel = jQuery(this).closest('dd').find('.label').length - 1;
				if (lLabel >= 1) {
					jQuery(this).closest('.label').remove();

				} else {
					jQuery(this).closest('dl').remove();
				}
				if (lLabel == 0) {
					jQuery('#view-current-filter').find('.view_filter').css('margin-top', 20);
				}
			})
			.on('click', '.action a', function() {
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
	updateFiltersDesktop: function() {
		if(this.getCurrentMobileFilterState() == 1 && !this.isVendorLandingpage()) {
			this.setCurrentMobileFilterState(0);
			this._processRollSections();
			this.closeMobileFilters();
		}
		return this;
	},

	/**
	 * Updates filters sidebar variables according to screen width
	 */
	updateFilters: function() {
		var self = Mall.listing;
		if(self.isDisplayMobile()) {
			self.updateFiltersMobile();
		} else {
			self.updateFiltersDesktop();
		}
		self._processRollSections();
		self.positionFilters();
	},

	positionFilters: function() {
		var self = Mall.listing;
		var filters = self.getFilters();
        var categoryWithFilters = self.getCategoryWithFilters();
		if(!filters.length) {
			return;
		}
		if(this.isDisplayMobile()) {
			filters
				.removeClass(self.getFiltersClassDesktop())
				.addClass(self.getFiltersClassMobile())
				.css({
					top: '',
					left: '',
					height: jQuery(window).height()
				});

            var category_with_filtersHtml = categoryWithFilters.find(".solr_search_facets").html();
            var category_with_filters = filters.find(".category_with_filters");

            categoryWithFilters.hide();
            if(category_with_filters.length > 0){
                category_with_filters.replaceWith(category_with_filtersHtml);
            } else {
                filters.append(category_with_filtersHtml);
            }


		} else {
			var content = self.getContentBlock(),
				containerOffset = jQuery('#sb-site').offset(),
				leftOffset = content.offset().left + 15,
                topOffset = content.offset().top;

			if(containerOffset.left != 0) {
				leftOffset = leftOffset - containerOffset.left;
			}

			filters
				.removeClass(self.getFiltersClassMobile())
				.addClass(self.getFiltersClassDesktop())
				.css({
					'top': topOffset,
					'left': leftOffset,
					'height': ''
				});
            var facetsHeight = filters.height();
            categoryWithFilters
                .removeClass(self.getFiltersClassMobile())
                .addClass(self.getFiltersClassDesktop())
                .css({
                "left": leftOffset,
                "top": (facetsHeight + topOffset + 15),
                    'height': ''
            });
            filters.find(".category_with_filters").remove();

		}
		self.setMainSectionHeight();
		return self;
	},

	setMainSectionHeight: function() {
		var mainSection = jQuery('section#main'),
			height = '';
		if(!this.isDisplayMobile()) {
			var filters = Mall.listing.getFilters();
            var categoryWithFiltersBlock = Mall.listing.getCategoryWithFilters();
            height = (filters.height() + categoryWithFiltersBlock.height() + 50) + 'px';
		}
		mainSection.css('min-height',height);
	},

	isDisplayMobile: function() {
		return Mall.isMobile(Mall.Breakpoint.smmd);
	},

	getContentBlock: function() {
		return jQuery('#content');
	},

	getFiltersClassMobile: function() {
		return "filters-mobile";
	},

	getFiltersClassDesktop: function() {
		return "filters-desktop";
	},

	delegateFilterEvents: function() {
		var self = this,
			filtersId = '#solr_search_facets';

		//filters slide up/down
		jQuery(document).delegate(filtersId+' h3','click',function(e) {
			e.preventDefault();
			var me = jQuery(this);
			self._doRollSection(
				me.parent(),
				!me.hasClass("open"),
				false
			);
			if(self.getCurrentMobileFilterState()) {
				self.getFilters().find('h3.open').not(me).each(function() {
					self._doRollSection(
						jQuery(this).parents().first(),
						false
					);
				});
			}
		});

		// filters show more btn
		jQuery(document).delegate(filtersId+' .showmore-filters','click',function(e) {
			e.preventDefault();
			var me = jQuery(this);
			self._doShowMore(
				me.parents('.section'),
				me.attr('data-state') != '1'
			);
		});

		// show/hide clear button on filter select/deselect
		var hiddenClass = 'hidden';
		jQuery(document).delegate(filtersId+' :checkbox','change',function(e) {
			Mall.listing._removeActiveFilters = true;
			e.preventDefault();
			var me = jQuery(this).parents('.section'),
				button = me.find('.action.clear');
			if(me.find(":checkbox:checked").length) {
				if(button.hasClass(hiddenClass)) {

					button.removeClass(hiddenClass);


                    if (!Mall.listing.isDisplayMobile()) {
                        var content = self.getContentBlock();
                        var leftOffset = content.offset().left + 15;
                        var topOffset = content.offset().top;
                        var filters = Mall.listing.getFilters();

                        var facetsHeight = filters.height();
                        Mall.listing.getCategoryWithFilters()
                            .removeClass(Mall.listing.getFiltersClassMobile())
                            .addClass(Mall.listing.getFiltersClassDesktop())
                            .css({
                                "left": leftOffset,
                                "top": (facetsHeight + topOffset + 15),
                                'height': ''
                            });
                        filters.find(".category_with_filters").remove();
                    }


                }

			} else {
				button.addClass(hiddenClass);
			}
		});

		// handle filters clearing
		var clearBtnSelector = filtersId+' .action.clear a';
		if(self.getPushStateSupport()) {
			jQuery(document).delegate(clearBtnSelector,'click',function(e) {
				Mall.listing._removeActiveFilters = true;
				e.preventDefault();
				var me = jQuery(this);
				self._current_url = me.attr('href');
				self.removeSingleFilterType(me);
				me.parent().addClass(hiddenClass);
			});
		} else {
			jQuery(document).delegate(clearBtnSelector,'click',function() {
				self.showAjaxLoading();
			});
		}

		// handle mobile filters overlay click
		jQuery(document).delegate('#'+self.getMobileFiltersOverlayId(),'click', self.closeMobileFilters);

	},

	/**
	 *
	 *

	   FILTERS END
	 *
	 *
	 **/

	delegatePaginationEvents: function() {
		var self = this;
		jQuery(document).delegate('a.page-next, a.page-prev','click', self.pageChange);

		return self;
	},

	pageChangeForceObject: {},
	pageChangeUri: false,
	pageChange: function(e) {
		Mall.listing.pageChangeUri = jQuery(this).attr('href');
		var start = Mall.listing.getStart();

		if (!Mall.isGoogleBot()) {
			if(start) {
				Mall.listing.pageChangeForceObject = {
					start: start
				};
			}

			var params = Mall.listing.getQueryParamsAsArray(Mall.listing.pageChangeForceObject),
				ajaxKey = Mall.listing.getAjaxHistoryKey(params);

			if(typeof Mall.listing._ajaxCache[ajaxKey] != "undefined") {
				Mall.listing.goToNextPage(Mall.listing._ajaxCache[ajaxKey]);
			} else {
				if(!start) {
					params["start"] = 1;
				}

				Mall.listing.showAjaxLoading();
				// Ajax load
				OrbaLib.Listing.getProducts(
					params,
					Mall.listing.goToNextPage
				);
			}
		}

		e.preventDefault();
		return false;
	},

	goToNextPage: function (data) {
		if (!jQuery.isEmptyObject(data)
			&& data.status !== undefined
			&& data.status && data.content !== undefined
			&& data.content.products !== undefined
			&& !jQuery.isEmptyObject(data.content.products)) {

			Mall.Scrolltop.hopToTop();

			Mall.listing.replaceProducts(data.content);
			Mall.listing.replacePager(data.content);

			var ajaxData = self.onPopStated ? window.history.state.ajaxData : Mall.listing.getQueryParamsAsArray(Mall.listing.pageChangeForceObject),
				ajaxKey = self.onPopStated ? window.history.state.ajaxKey : Mall.listing.getAjaxHistoryKey(ajaxData);
			// Set pushstate
			Mall.listing._current_url = Mall.listing.pageChangeUri;
			Mall.listing.pageChangeUri = false;
			Mall.listing._pushHistoryState(ajaxKey,ajaxData);

			//include header in order to keep active filters header labels always up to date
			data.content.header = Mall.listing.getHeader().prop("outerHTML");
			Mall.listing._ajaxCache[ajaxKey] = data;

			Mall.listing.hideAjaxLoading();

		} else {
			console.log("Something went wrong, please contact support");
		}
	},

	getToolbar: function(){
		return jQuery("#sort-criteria");
	},
	getPager: function(){
		return jQuery(".pagination-line");
	},

	getSortSelect: function(scope) {
		var parent = scope || this.getToolbar();
		return jQuery('#sort-by',parent);
	},

	getDirInput: function(scope) {
		var parent = scope || this.getToolbar();
		return jQuery('#sort-dir',parent);
	},

	getSortInput: function(scope) {
		var parent = scope || this.getToolbar();
		return jQuery('#sort-val',parent);
	},

	/**
	 *
	 * @param {type|bool} scope
	 * @returns {undefined}
	 */
	initSortEvents: function(scope){
		var sortingSelect = this.getSortSelect(scope),
			self = this;
		//sortingSelect.selectbox();
        sortingSelect.selectBoxIt({
            autoWidth: false
        });
		if(this.getPushStateSupport()) {
			sortingSelect.change(function () {
				var selected = jQuery(this).find(":selected");
				self._current_url = selected.data('url');
				self.setSort(selected.data('sort'));
				self.setDir(selected.data('dir'));
				self.reloadListing();
			});
		} else {
			sortingSelect.change(function () {
				var url = jQuery(this).find(":selected").data("url");
				self.showAjaxLoading();
				window.location = url;
			});
		}
	},

	/**
	 * Attaches events to color filter.
	 *
	 * @returns {Mall.listing}
	 */
	attachFilterColorEvents: function(scope) {
		var self = this;
		jQuery(".filter-color", scope).find("[data-url]").on("click", function() {
			self.nodeChanged(jQuery(this));
		});

		jQuery('.filter-color label', scope).each(function() {
			var el = jQuery(this);
			var colorFilter = el.data('color');
			var srcImg = el.data('img');
			var srcImgHover = el.data('imghover');

			el.find('span').children('span').css({
				'background-color': colorFilter
			});

			if (el.attr("data-img")) {
				el.find('span').children('span').css({
					'background-image': 'url('+srcImg+')'
				});
			}

			el
				.on('mouseenter', function(){
					if (colorFilter && !el.attr("data-img")) {
						el.find('span').children('span').css({
							'background-image': 'none'
						});
					}
					if (el.attr("data-imgHover")) {
						el.find('span').children('span').css({
							'background-image': 'url('+srcImgHover+')'
						});
					}
				})
				.on('mouseleave', function(){
					if (srcImg) {
						el.find('span').children('span').css({
							'background-image':  'url('+srcImg+')'
						});
					}
				});
		});

		return this;
	},
	/**
	 * Attaches events to icon filter.
	 *
	 * @returns {Mall.listing}
	 */
	attachFilterIconEvents: function(scope) {
		var self = this;
		jQuery(".filter-type", scope).find(":checkbox").on("change", function() {
			self.nodeChanged(jQuery(this));
		});
		return this;
	},

	/**
	 * Attaches events to flag filters.
	 *
	 * @returns {Mall.listing}
	 */
	attachFilterFlagEvents: function(scope) {
		var self = this;
		jQuery(".filter-flags", scope).find(":checkbox").on("change", function() {
			self.nodeChanged(jQuery(this));
		});
		return this;
	},

	/**
	 * Attaches events to Enum filters.
	 * @returns {Mall.listing}
	 */
	attachFilterEnumEvents: function(scope) {
		var self = this;
		jQuery(".filter-enum", scope).find(":checkbox").on("change", function() {
			self.nodeChanged(jQuery(this));
		});
		return this;
	},

	/**
	 * Attaches events to Price range filters.
	 * @returns {Mall.listing}
	 */
	attachFilterPriceEvents: function(scope) {
		var self = this;
		jQuery("#filter_price", scope).find(":checkbox").on("change", function() {
			// Remove all other selections
			jQuery(this).
				parents(".section").
				find(":checkbox:checked").
				not(this).
				prop("checked", false);
			// Trigger listing
			self.nodeChanged(jQuery(this));
		});
		return this;
	},

	/**
	 * Attaches events to size filters.
	 * @returns {Mall.listing}
	 */
	attachFilterSizeEvents: function(scope) {
		var self = this;
		jQuery('.filter-size', scope).find(":checkbox").on("change", function() {
			self.nodeChanged(jQuery(this));
		});
		return this;
	},

	/**
	 * Attaches events for long list filters.
	 *
	 * @returns {Mall.listing}
	 */
	attachFilterLongListEvents: function(scope) {
		// Handle long list
		var filters = jQuery('.filter-longlist', scope);
		var self = this;

		filters.find(":checkbox").on("change", function() {
			self._rebuildLongListContent(jQuery(this).parents(".content"));
			self.nodeChanged(jQuery(this));
		});

		filters.find(".action .clear").click(function(){
			self._rebuildLongListContent(jQuery(this).parents(".content"));
		});

		filters.find(".longListSearch").keyup(function(){
			self._searchLongList(jQuery(this).parents(".content"));
		});

		filters.find("input[type='image']").click(function(){
			self._searchLongList(jQuery(this).parents(".content"));
			return false;
		});

		// Rebuild selected filters
		filters.each(function(){
			var el = jQuery(this);
			jQuery(":checkbox", this).each(function(idx){
				jQuery(this).prop('sort', idx);
			});
			self._rebuildLongListContent(el);
			self._searchLongList(el);
		});

		return this;
	},

	_searchLongList: function(scope){
		var items = jQuery(".longListItems li", scope),
			noResult = jQuery(".no-result", scope),
			list = jQuery(".scrollable", scope),
			listUl = jQuery(".longListItems", scope),
			matches = 0,
			term = jQuery(".longListSearch", scope).val().trim().toLowerCase();

		if(!items.length){
			// No avaialble items to search
			noResult.addClass("hidden");
			list.addClass("hidden");
		} else if(!term.length) {
			// No term entered
			items.removeClass("hidden");
			list.removeClass("hidden");
			noResult.addClass("hidden");
		} else {
			// Term entered
			items.each(function() {
				var el = jQuery(this),
					text = el.find("label > span:eq(0)").text().trim().toLowerCase(),
					searchPosition = text.search(term);

				if(searchPosition>-1){
					el.removeClass("hidden");
					matches++;
					if(text==term){ // Same terms
						el.addClass("perfectMatch");
					}else if(searchPosition==0){ // result start with the term
						el.addClass("almostPerfect");
					}
				}else{
					el.addClass("hidden");
				}
			});

			if(!matches) {
				noResult.removeClass("hidden");
				list.addClass("hidden");
			} else {
				noResult.addClass("hidden");
				list.removeClass("hidden");
			}
		}

		var checkboxes = jQuery(":checkbox", items);
		if(list.find("li").not(".hidden").length){
			// Make sort
			this._sortLongListContent(checkboxes);
			checkboxes.each(function(){
				var el = jQuery(this).parents("li");
				if(!el.is(".hidden")){
					listUl.append(el);
				}
			});

			// Almost perfect match - move as first
			var almostPerfect = checkboxes.parents("li.almostPerfect").
					removeClass("almostPerfect").
					find(":checkbox");
			if(almostPerfect.length){
				this._sortLongListContent(almostPerfect, true);
				almostPerfect.each(function(){
					jQuery(this).parents('li').prependTo(listUl);
				});
			}

			// Perfect match - move as first
			var perfectMatch = checkboxes.parents("li.perfectMatch").
					removeClass("perfectMatch").
					find(":checkbox");
			if(perfectMatch.length){
				this._sortLongListContent(perfectMatch, true);
				perfectMatch.each(function(){
					jQuery(this).parents('li').prependTo(listUl);
				});
			}

			// Move scroll top
			list.mCustomScrollbar("scrollTo", "top");
		}

	},

	_sortLongListContent: function(items, desc){
		desc = desc ? -1 : 1;
		items.sort(function(a,b){
			return (a.sort-b.sort)*desc;
		});
	},

	_rebuildLongListContent: function(scope){

		var noSelectedContianer = jQuery(".longListItems", scope),
			selectedContianer = jQuery(".longListChecked", scope),
			scrollable = jQuery(".scrollable", scope),
			items = jQuery(":checkbox", scope);

		this._sortLongListContent(items);

		items.each(function() {
			var el = jQuery(this),
				parent = el.parents('li');
			if(el.is(":checked")){
				selectedContianer.append(parent);
			} else {
				noSelectedContianer.append(parent);
			}
		});

		if(selectedContianer.children().length) {
			selectedContianer.show();
		} else {
			selectedContianer.hide();
		}

		var wrapper = jQuery(".longListWrapper", scope);
		if(noSelectedContianer.children().length) {
			wrapper.show();
			scrollable.removeClass('hidden');
			selectedContianer.removeClass('noChooseFields');
		} else {
			wrapper.hide();
			selectedContianer.addClass('noChooseFields');
		}

	},

	/**
	 * Attaches events for droplist filters.
	 *
	 * @returns {Mall.listing}
	 */
	attachFilterDroplistEvents: function(scope) {
		var listSelect = jQuery('.dropdown-select ul', scope),
			self = this;

		listSelect.on('click', 'a', function() {
			self.nodeChanged(jQuery(this));
		});

		return this;
	},

	/**
	 * Attaches events for price slider filters.
	 *
	 * @returns {Mall.listing}
	 */
	attachFilterPriceSliderEvents: function(scope) {
		var sliderRange = jQuery( "#slider-range", scope),
			self = this;

		if (sliderRange.length >= 1) {
			sliderRange.slider({
				range: true,
				/*min: Mall.listing.getCurrentPriceRange()[0],
				max: Mall.listing.setCurrentPriceRangegetCurrentPriceRange()[1],*/
				min: parseInt(sliderRange.data("min"),10),
				max: parseInt(sliderRange.data("max"),10),
				values: Mall.listing.getCurrentPriceRange(),

				stop: function(event, ui) {
                    var checkSlider = jQuery('#checkSlider').find('input');
                    if (!checkSlider.is(':checked')) {

                        checkSlider.prop('checked', true).change();
                        jQuery('#filter_price').find('.action').removeClass('hidden');
                    } else {
						self._current_url = self._preparePriceUrl(ui.values[0],ui.values[1]);
					}
					self._triggerRefresh(scope, 1, true);
				},
				slide: function(event, ui) {
					jQuery("#zakres_min").val(ui.values[0]);
					jQuery("#zakres_max").val(ui.values[1]);
					self._transferValuesToCheckbox(ui.values[0], ui.values[1]);
				}
			});

			jQuery("#zakres_min",scope).val(sliderRange.slider("values", 0));
			jQuery("#zakres_max",scope).val(sliderRange.slider("values", 1));
			jQuery('#slider-range',scope).on('click', 'a', function() {
				var checkSlider = jQuery('#checkSlider',scope).find('input');
				if (!checkSlider.is(':checked')) {
					checkSlider.prop('checked', true).change();
					jQuery('#filter_price').find('.action').removeClass('hidden');
				}
			});
		}

		var minPrice, maxPrice, filterPrice = jQuery("#filter_price", scope);
		filterPrice.find("input.filter-price-range-submit").on("click",
			function(e) {
				e.preventDefault();
				// validate prices

				minPrice = Mall.listing.getMinPriceFromSlider();
				maxPrice = Mall.listing.getMaxPriceFromSlider();
				if(!self._validateRange(minPrice, maxPrice)) {
					return false;
				}

				var checkSlider = self.getSliderCheckbox(scope);
				if (!checkSlider.is(':checked')) {
					checkSlider.prop('checked', true).change();
					jQuery('#filter_price').find('.action').removeClass('hidden');
				} else {
					self._current_url = self._preparePriceUrl(minPrice,maxPrice);
				}
				self._transferValuesToCheckbox(minPrice, maxPrice, scope);
				self._triggerRefresh(scope, 1, true);
			}
		);

		filterPrice.find("input.filter-price-range-submit").on("mouseover",
			function() {
				"use strict";
				minPrice = Mall.listing.getMinPriceFromSlider();
				maxPrice = Mall.listing.getMaxPriceFromSlider();
				jQuery(this).tooltip({
					title: Mall.translate.__("price-filter-not-valid", "Prices in filter are not valid.")
				});
				if(!self._validateRange(minPrice, maxPrice)) {
					jQuery(this).tooltip("enable");
					jQuery(this).tooltip("show");
				} else {
					jQuery(this).tooltip("hide");
					jQuery(this).tooltip("disable");
				}
			}
		);

		jQuery("#zakres_min, #zakres_max", scope).on("keyup keypress",
			function (e) {
				"use strict";
				var code = e.keyCode || e.which;
				Mall.listing.unmarkPrice();

				filterPrice.find('input[name="fq[price]"]').prop('checked', false);

				var checkSlider = jQuery('#checkSlider').find('input');
				if (!checkSlider.is(':checked')) {
					checkSlider.prop('checked', true);
					filterPrice.find('.action').removeClass('hidden');
				}
				if (code === 13) {
					//e.preventDefault();
					filterPrice.find('input[data-filter-type="price"]').click();
					return false;
				}
			}
		);

		this.getSliderCheckbox(scope).change(function(){
			self._transferValuesToCheckbox(
				Mall.listing.getMinPriceFromSlider(),
				Mall.listing.getMaxPriceFromSlider(),
				scope
			);
		});

		return this;
	},
	_validateRange: function(minPrice,maxPrice){

		var reg = /^\d+$/;

		if(!reg.test(minPrice) || !reg.test(maxPrice)){
			return false;
		}

		var min = parseInt(minPrice, 10);
		var max = parseInt(maxPrice, 10);

		if(isNaN(min) || isNaN(max)){
			return false;
		}
		return min>=0 && max>=0 && min<=max;
	},

	_triggerRefresh: function(scope, force, triggerChange){
		var checkbox = jQuery('#checkSlider',scope),
			action = jQuery('#filter_price', scope).find('.action'),
			canTriggerChange = triggerChange || true,
			self = this;

		if(force!==undefined){
			checkbox.prop('checked', force);
		}

		if(!checkbox.is(':checked')){
			action.removeClass('hidden');
		}else{
			action.addClass('hidden');
		}

		if(canTriggerChange){
			if(self.getPushStateSupport()){
				self.reloadListing();
			}else{
				self.showAjaxLoading();
				document.location = checkbox.parent().find("a").attr('href');
			}
		}

	},
	_preparePriceUrl: function(min,max) {
		var url = jQuery("#price_submit").data("url");
		url = url.replace('__min',parseInt(min));
		url = url.replace('__max',parseInt(max));
		return url;
	},
	_transferValuesToCheckbox: function(min,max,scope){
		this.getSliderCheckbox(scope).attr('value',
			parseInt(min) + " TO " + parseInt(max));
	},
	getSliderCheckbox: function(scope){
		return jQuery('#filter_slider',scope);
	},
	getIsSliderActive: function(){
		return this.getSliderCheckbox().prop("checked")
	},
	setIsSliderActive: function(val){
		return this.getSliderCheckbox().prop("checked", val);
	},


	/**
	 * Returns complete array of params for querying solr.
	 * Reloading supresses page, and start param.
	 *
	 * @returns {{fq: Array, q: *, page: *, sort: *, dir: *, scat: *, rows: *, start: *}}
	 */
	getQueryParams: function() {
		var q = {
			fq: jQuery.isEmptyObject(this.getFiltersArray()) || this.getFiltersArray().fq === undefined ? [] : this.getFiltersArray().fq,
			q: this.getQuery(),
			sort: this.getSort(),
			dir: this.getDir(),
			scat: this.getScat(),
			start: 0
		};

		if(this.getIsSliderActive()){
			q.slider = 1;
		}

		return q;
	},

	getAjaxHistoryKey: function(data) {
		var out = [],
			names = [];

		data.forEach(function(entry) {
			if((entry.name == 'start' && entry.value > 1 && !Mall.listing._removeActiveFilters) || entry.name != 'start') {
				out.push(entry.name + "=" + entry.value);
				names.push(entry.name);
			}
		});

		if(names.indexOf("start") == -1 && !Mall.listing._removeActiveFilters) {
			var start = Mall.listing.getStart();
			if (start > 1) {
				out.push("start=" + start);
			}
		}

		if(Mall.listing._removeActiveFilters) {
			Mall.listing._removeActiveFilters = false; //handles all filters removal and drops the start parameter from ajaxCache key
		}

		return out.join("|");
	},

	getQueryParamsAsArray: function(forceObject){
		var force = forceObject || {};
		var defaults = {
			q: this.getQuery(),
			sort: this.getSort(),
			dir: this.getDir(),
			scat: this.getScat()
		};
		if(this.getIsSliderActive()){
			defaults.slider = 1;
		}

		var out = [];

		jQuery.extend(defaults, force);

		// Collect defualt params
		jQuery.each(defaults, function(index){
			out.push({name: index, value: this});
		});

		// Collect fq's
		jQuery.each(this.getFqByInterface(), function(){
			out.push({name: this.name, value: this.value});
		});

		return out;
	},

	/**
	 * Calculates position and places shape listing fade block.
	 *
	 * @returns {Mall.listing}
	 */
	placeListingFadeContainer: function() {
		if(!Mall.isGoogleBot()) {
			if (this.nextPageExists()) {
				this.showShapesListing();
				var grid = jQuery('#grid'),
					gridHeight = grid.height(),
					cutFromBottom = [];

				var lastProducts = jQuery('#grid').find(
					'.item:eq('+(this.getProductsPerPage()-4)+'),'+
					'.item:eq('+(this.getProductsPerPage()-3)+'),'+
					'.item:eq('+(this.getProductsPerPage()-2)+'),'+
					'.item:eq('+(this.getProductsPerPage()-1)+')'); //last 4 products

				lastProducts.each(function() {
					var top = jQuery(this).position().top,
						elemHeight = jQuery(this).height();

					cutFromBottom.push(gridHeight - top - elemHeight - 60); //60 = shapes_listing height
				});

				cutFromBottom = parseInt(Math.min.apply(Math, cutFromBottom));

				var newHeight = gridHeight - cutFromBottom;
				if ((grid.height() - cutFromBottom ) <= newHeight) {
					grid.not('.list-shop-product').height(newHeight);
				}

			} else {
				this.hideShapesListing();
			}
		}

		return this;
	},

	showShapesListing: function() {
		jQuery("#items-product").find(".shapes_listing").show();
		return this;
	},

	hideShapesListing: function() {
		jQuery("#items-product").find(".shapes_listing").hide();
		return this;
	},

	nextPageExists: function() {
		return jQuery('.page-next').length ? true : false;
	},

	/**
	 * Removes single filter from filters array.
	 *
	 * @param filter
	 * @returns {Mall.listing}
	 */
	removeSingleFilterType: function(filter) {
		var thisFilter = jQuery(filter);
		thisFilter.parents(".section").
			find(":checkbox").
			prop("checked", false);
		this.reloadListing();

		return this;
	},


    delegateSaveContextForProductPage: function() {
        jQuery(document).delegate('.box_listing_product a','mousedown',function(e) {
	        var breadcrumb = jQuery('ol.breadcrumb');
            e.preventDefault();
            localStorage.setItem(jQuery(this).attr("data-entity"), jQuery('#breadcrumbs-header').find('ol').html());

        });
    },

	beforeResizeWidth: window.innerWidth,

	positionFiltersAfterLpBannerLoad: function() {
		var bannersContainer = jQuery('.lp-banners');
		if(bannersContainer.length) {
			bannersContainer.find('img').load(function() {
				Mall.listing.positionFilters();
			});
		}
	},

	/**
	 *
	 * SETTERS / GETTERS
	 *
	 */

	getNoPushstate: function() {
		return this._noPushState;
	},

	setNoPushstate: function(bool) {
		this._noPushState = bool;
		return this;
	},

	getNoDelay: function() {
		return this._noDelay;
	},

	setNoDelay: function(bool) {
		this._noDelay = bool;
		return this;
	},

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
	 * @returns {object}
	 */
	getCurrentFiltersShowMore: function(){
		return this._current_filters_show_more;
	},

	/**
	 * @returns {object}
	 */
	getCurrentSearch: function(){
		return this._current_search;
	},

	/**
	 * @returns {object}
	 */
	getCurrentOpened: function(){
		return this._current_opened;
	},

	/**
	 * Returns current sort type.
	 *
	 * @returns {string}
	 */
	getSort: function() {
		return this.getSortInput().val();
	},

	/**
	 * Returns current sort direction.
	 *
	 * @returns {string}
	 */
	getDir: function() {
		return this.getDirInput().val();
	},

	/**
	 * Returns current sort type.
	 *
	 * @returns {Mall.listing}
	 */
	setSort: function(sort) {
		this.getSortInput().val(sort);
		this.setSortSelect();
		return this;
	},

	/**
	 * Returns current sort direction.
	 *
	 * @returns {Mall.listing}
	 */
	setDir: function(dir) {
		this.getDirInput().val(dir);
		this.setSortSelect();
		return this;
	},

	setSortSelect: function() {
		var select = this.getSortSelect(),
			sort = this.getSort(),
			dir = this.getDir();
		if(select.find("option[value='"+sort+"||"+dir+"']")) {
			select.val(this.getSort() + '||' + this.getDir());
		} else {
			select.val(select.find(":first-child").val());
		}

        select.selectBoxIt('destroy');
        select.selectBoxIt({
            autoWidth: false
        });
		return this;
	},
	/**
	 * @returns Boolean
	 * Determines if browser supports history.pushState
	 */
	getPushStateSupport: function() {
		return window.history.pushState ? true : false;
	},

	/**
	 * Returns current filters.
	 * @deprecated since getFqByInterface
	 * @returns {*}
	 */
	getFiltersArray: function() {
		return this._current_filters;
	},



	/**
	 * @returns {array} [{name: "", value:""},...]
	 */
	getFqByInterface: function(scope){
		scope = scope || jQuery(".solr_search_facets").first();
		return scope.find(':checkbox:checked').serializeArray();
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
	 * Sets query.
	 *
	 * @param query
	 * @returns {Mall.listing}
	 */
	setQuery: function(query) {
		this._current_query = query;

		return this;
	},

	getStart: function() {
		var href = Mall.listing.pageChangeUri ? Mall.listing.pageChangeUri : window.location.href;
		var start = Mall.getUrlPart('start',href);

		return start ? start : 1;
	},

	/* Functions that set and get current category id */
	setScat: function(scat) {
		this._current_scat = scat;

		return this;
	},
	getScat: function() {
		return this._current_scat;
	},

	/* Functions that set and get current total products count */
	setTotal: function(total) {
		this._current_total = parseInt(total);
		return this;
	},
	getTotal: function() {
		return this._current_total;
	},

	/* Functions that set and get current filters state - mobile or not */
	setCurrentMobileFilterState: function(state) {
		this._current_mobile_filter_state = state;

		return this;
	},
	getCurrentMobileFilterState: function() {
		return this._current_mobile_filter_state;
	},

	/* Functions that set and get ajaxCache first products (not loaded by ajax) */
	setInitProducts: function(products){
		this._init_products = products;
		return this;
	},
	getInitProducts: function(){
		return this._init_products;
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

	/* Functions that set and get how many products per page should be displayed */
	setProductsPerPage: function(num) {
		this._products_per_page = num;
		return this;
	},
	getProductsPerPage: function() {
		return this._products_per_page;
	}

};

jQuery(document).ready(function () {
	"use strict";
    jQuery('#toggleSearch').click(function(){
        jQuery('#sort-criteria').find('.selectboxit-container').css('pointer-events', 'none');
    });

    jQuery('body').click(function (e) {

        if(jQuery(e.target).parents("#dropdown-search").length>0){
            jQuery('#sort-criteria').find('.selectboxit-container').css('pointer-events', 'none');
        } else {
            jQuery('#sort-criteria').find('.selectboxit-container').css('pointer-events', 'auto');
        }
    });
    if (jQuery('body.filter-sidebar').length) {
        Mall.listing.init();
        jQuery(window).resize(function() {
	        Mall.listing.updateFilters();
        });
    } else {
        Mall.listing.initShuffle();
    }
});