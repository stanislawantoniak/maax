Mall.product = {
	_size_table_template: "",
	_options_group_template: "",
	_options: {},
	_current_product_type: "simple",
	_entity_id: '',
	_path_back_to_category_text: '',
	_path_back_to_category_link: '',

	init: function() {
		if(jQuery("body").hasClass("catalog-product-view")) {
			Mall.product.updateContextBreadcrumbs();
			Mall.product.setDiagonalsOnSizeSquare();
			Mall.product.rating.init();
            Mall.product.review.init();
            Mall.product.gallery.init();
		}
	},

    /**
     * Update contact form in product page from not logged in state to logged in state
     * @param name
     * @param email
     */
    updateQuestionFormForLoggedIn: function(name, email){
        var form       = jQuery('#question-form');
        var formMobile = jQuery('#question-form-mobile');
        form.find('#question-form-customer-name').html( "<input type='hidden' name='question[customer_name]'  value='"+name+"' />");
        form.find('#question-form-customer-email').html("<input type='hidden' name='question[customer_email]' value='"+email+"' />");
        formMobile.find('#question-form-mobile-customer-name').html( "<input type='hidden' name='question[customer_name]'  value='"+name+"' />");
        formMobile.find('#question-form-mobile-customer-email').html("<input type='hidden' name='question[customer_email]' value='"+email+"' />");
    },

    /**
     * Populate inputs fields when backend throw error (wrong email od something)
     * @param customerName
     * @param customerEmail
     * @param questionText
     */
    populateQuestionForm: function(customerName, customerEmail, questionText){
        var form       = jQuery('#question-form');
        var formMobile = jQuery('#question-form-mobile');
        form.find('#question-form-customer-name').find('input').val(customerName);
        form.find('#question-form-customer-email').find('input').val(customerEmail);
        form.find('#question-form-question-text').find('textarea').val(questionText);
        formMobile.find('#question-form-mobile-customer-name').find('input').val(customerName);
        formMobile.find('#question-form-mobile-customer-email').find('input').val(customerEmail);
        formMobile.find('#question-form-mobile-question-text').find('textarea').val(questionText);
    },

	updateContextBreadcrumbs: function() {
        var contextBreadcrumbsHtml = localStorage.getItem(this._entity_id);

        var searchBreadcrumb = localStorage.getItem(this._entity_id+"_search_breadcrumb");

        localStorage.removeItem(this._entity_id);

        localStorage.removeItem(this._entity_id+"_search_breadcrumb");

        if (contextBreadcrumbsHtml != null) {
			sessionStorage.setItem(this._entity_id, contextBreadcrumbsHtml);
        }

        if (searchBreadcrumb != null) {
            sessionStorage.setItem(this._entity_id+"_search_breadcrumb", searchBreadcrumb);
        }
        contextBreadcrumbsHtml = sessionStorage.getItem(this._entity_id);

        searchBreadcrumb = sessionStorage.getItem(this._entity_id+"_search_breadcrumb");


        if (contextBreadcrumbsHtml) {
			var productHtml = jQuery('#breadcrumbs .product');
			jQuery('#breadcrumbs ol').html(contextBreadcrumbsHtml);
			this._path_back_to_category_link = jQuery('#breadcrumbs ol li:last').attr('data-link');
			this._path_back_to_category_text = jQuery('#breadcrumbs ol li:last').text();

			jQuery('#breadcrumbs ol li:last').html("<a href='" + this._path_back_to_category_link + "'>" + this._path_back_to_category_text + "</a>");
			jQuery('#breadcrumbs ol').append(productHtml);

			// Update context path back to category for mobile
			jQuery('.path_back_to_category #pbtc_link').attr('href', this._path_back_to_category_link);
			jQuery('.path_back_to_category #pbtc_link').html("<i class='fa fa-angle-left'></i>" + this._path_back_to_category_text);
        }

        if(searchBreadcrumb){
            jQuery("ol.breadcrumb li:not(.home,.search,.vendor,.product)").each(function(i,val){
                jQuery(val).remove();
            });
            //desktop
            jQuery("ol.breadcrumb li.home").after(searchBreadcrumb);
            //mobile
            var mobileLink = jQuery("ol.breadcrumb li:not(.home,.search,.vendor,.product):last").find("a").attr("href");
            var mobileLabel = jQuery("ol.breadcrumb li:not(.home,.search,.vendor,.product):last").find("a").html();

            jQuery('.path_back_to_category #pbtc_link').attr('href', mobileLink);
            jQuery('.path_back_to_category #pbtc_link').html("<i class='fa fa-angle-left'></i>  " + mobileLabel);
        }


        // Update info about highlighted navigation
        Mall.Navigation.destroy();
        Mall.Navigation.currentCategoryId = [];
        jQuery('#breadcrumbs ol li').each(function( index ) {
            var id = jQuery( this ).attr('data-catid');
            if (id !== undefined){
                if (id.length) {
                    Mall.Navigation.currentCategoryId.push(id);
                }
            }
        });
        Mall.Navigation.init();
        sessionStorage.removeItem(this._entity_id);
        sessionStorage.removeItem(this._entity_id+"_search_breadcrumb");

	},

	productOptions: function(jsonOptions) {
		this._options = jsonOptions;
		// set prices
		this.setPrices(jsonOptions.basePrice, jsonOptions.oldPrice, jsonOptions.template);
		if(typeof jsonOptions.attributes != "undefined") {

			this.setAttributes(jsonOptions.attributes, jsonOptions.useSizeboxList);
		}
	},

	setPrices: function(price, oldPrice, template) {
		// set old price
		var price_box = jQuery(".price-box"),
			old_price_selector = price_box.find(".old-price"),
			price_selector = price_box.find("span.price");
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
		var size_box = jQuery(".size-box");
		this._size_table_template = size_box.find("a.view-sizing")[0].outerHTML;
		this._size_label = jQuery('.size-box .size-label').text();
		size_box.find("div.size").remove();
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
				"class": "size-label size-label-radios",
				"html": (this._size_label + ":")
			}).appendTo(groupElement);

			// create form group for options
			var formGroupElement = jQuery("<div/>", {
				class: "form-group form-radio"
			}).appendTo(groupElement);

			jQuery.each(group.options, function(index, option) {
				Mall.product.createOption(group.id, option, formGroupElement);
			});
			var sizesCount = jQuery('input[type=radio][id^=size_]:not(:disabled)').length;

			if (sizesCount == 1) {
				var singleInput = jQuery('input[type=radio][id^=size_]:not(:disabled)');
				singleInput.attr('checked', true).trigger('click');
			}

			this.applyAdditionalRules(group, formGroupElement);
		} else { //selectbox



			var deskTopDevice = !Mall.getIsBrowserMobile();

			//check if selectbox should be shown
			var showSelect = false;
			jQuery.each(group.options, function (index, option) {
				if (option.is_salable) {
					showSelect = true;
				}
			});

			if (showSelect) {
				var labelText = jQuery('.size-label').text();
				// insert option group
				var groupElement = jQuery("<div/>", {
					"class": "size"
				}).appendTo(".size-box");
				jQuery(".size-box").append(this._options_group_template);
				// create label group
				jQuery("<span/>", {
					"class": "size-label col-sm-6 col-md-6 col-xs-12",
					"html": (this._size_label + ":")

				}).appendTo(groupElement);


				// create form group for selectbox options
				var formGroupElementClass = (deskTopDevice) ? ' sizes-content col-sm-6 col-md-5 col-xs-4' : ' sizes-content form-group col-sm-6 col-md-5 col-xs-5 select-size-mobile-trigger';
				var formGroupElement = jQuery("<div/>", {
					class: "" + formGroupElementClass
				}).appendTo(groupElement);

				//create select part
				var formGroupElementSelectClass = (deskTopDevice) ? '  mobile-native-select-w' : '  mobile-native-select-w';
				var formGroupElementSelect = jQuery("<select/>", {
					id: "select-data-id-" + group.id,
					class: formGroupElementSelectClass
				}).appendTo(formGroupElement);


				jQuery.each(group.options, function (index, option) {
					Mall.product.createOptionSelectbox(group.id, option, formGroupElementSelect);
				});

				this.applyAdditionalRules(group, formGroupElementSelect.parent()); // jQuery('div.size-box div.size'));
				if (deskTopDevice) {
					jQuery('div.size-box div.size a').css('position', 'relative');
					jQuery('div.size-box div.size a').css('top', '5px');
				}
			} else {
				jQuery('div.size-box').remove();
			}

		}

	},

	createOption: function(id, option, groupElement) {
		var label = jQuery("<label/>", {
			"for": ("size_" + option.id),
			"class": option.is_salable == false ? "no-size" : "",
			'data-toggle': 'tooltip'
		}).appendTo(groupElement);
		var _options = {
			type: "radio",
			id: ("size_" + option.id),
			"data-superattribute": id,
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
			"data-superattribute": id,
			name: ("super_attribute["+ id +"]")
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
	},

	rating: {
		init: function() {
			// RATY{ path: 'assets/images' }
			jQuery('#average_rating').raty({
				path: Config.path.averageRating.averageRatingPath,
				starOff : Config.path.averageRating.averageRatingStarOff,
				starOn  : Config.path.averageRating.averageRatingStarOn,
				starHalf  : Config.path.averageRating.averageRatingStarHalf,
				size   : 22,
				readOnly: true,
				hints: ['', '', '', '', ''],
				half     : true,
				number: function() {
					return jQuery(this).attr('data-number');
				},
				score: function() {
					return jQuery(this).attr('data-score');
				}
			});

			jQuery('.raty_note dd div').raty({
				path: Config.path.ratyNote.ratyNotePath,
				starOff : Config.path.ratyNote.ratyNoteStarOff,
				starOn  : Config.path.ratyNote.ratyNoteStarOn,
				hints: ['', '', '', '', ''],
				readOnly: true,
				size   : 17,

				number: function() {
					return jQuery(this).attr('data-number');
				},
				score: function() {
					return jQuery(this).attr('data-score');
				}
			});

			jQuery('.ratings tr td div').raty({
				path: Config.path.ratings.ratingsPath,
				starOff : Config.path.ratings.ratingsStarOff,
				starOn  : Config.path.ratings.ratingsStarOn,
				hints: ['', '', '', '', ''],
				size   : 17,
				scoreName: function() {
					return jQuery(this).attr("data-score-name");
				},

				number: function() {
					return jQuery(this).attr('data-number');
				},
				score: function() {
					return jQuery(this).attr('data-score');
				}
			});

			jQuery('.comment_rating').raty({
				path: Config.path.commentRating.commentRatingPath,
				starOff : Config.path.commentRating.commentRatingStarOff,
				starOn  : Config.path.commentRating.commentRatingStarOn,
				size   : 17,
				readOnly: true,
				hints: ['', '', '', '', ''],
				number: function() {
					return jQuery(this).attr('data-number');
				},
				score: function() {
					return jQuery(this).attr('data-score');
				}
			});
			jQuery('#average_note_client .note').raty({
				path: Config.path.averageNoteClient.averageNoteClientPath,
				starOff : Config.path.averageNoteClient.averageNoteClientStarOff,
				starOn  : Config.path.averageNoteClient.averageNoteClientStarOn,
				size   : 13,
				readOnly: true,
				hints: ['', '', '', '', ''],
				number: function() {
					return jQuery(this).attr('data-number');
				},
				score: function() {
					return jQuery(this).attr('data-score');
				}
			});

// RATING
			jQuery('body').find('.rating').each(function(index, el) {
				var rating = jQuery(this).data('percent');
				jQuery(this).children('span').css({width:rating+'%'});
			});
		}
	},

	review: {
		formId: '#review-form',
		formElement: false,
		reviewSummaryTableClass: '.review-summary-table',
		reviewSummaryTableElement: false,
		_starsValidation: false,
		_formValidation: false,
		init: function() {
			var _ = this;
			if(!_.formElement) {
				_.formElement = jQuery(_.formId);
				if(_.formElement.length) {
					if(!_.reviewSummaryTableElement) {
						_.reviewSummaryTableElement = jQuery(_.reviewSummaryTableClass);
					}
					if(!_._starsValidation) {
						_.addStarsValidationMethod();
					}
					if(!_._formValidation) {
						_.addValidation();
					}
				}
			}
		},
		addStarsValidationMethod: function() {
			var _ = this;
			jQuery.validator.addMethod("stars", function() {
				var valid = true;
				_.reviewSummaryTableElement.find('input[type="hidden"][name^="ratings["]').each(function(){
					valid = !jQuery(this).val() ? false : valid;
				});
				return valid;
			}, Mall.i18nValidation.__("All star groups must be selected."));
			_._starsValidation = true;
		},
		addValidation: function() {
			var _ = this,
				validationOptions = jQuery.extend({}, Mall.validate.getDefaultValidationOptions(), {
					ignore: '[type=hidden]',
					rules: {
						title: {
							required:true
						},
						detail: {
							required:true
						},
						recommend_product : {
							required:true
						},
						stars: {
							stars: true
						}
					}
				});

			_.formElement.validate(validationOptions);
			_._formValidation = true;

			_.reviewSummaryTableElement.find('img').click(function() {
				var isOk = true;
				_.formElement.find('input[name^="ratings["]').each(function() {
					if(!jQuery(this).val() || !isOk) {
						isOk = false;
					}
				});
				if(isOk) {
					_.formElement.find('[name=stars]').valid();
				}
			});
		}
	},

    gallery: {

        init: function () {
            // For not open
            this.initBigMediaCarousel();
            this.initThumbsCarousel();
            this.flagBigMedia();
            this.initReleatedCarousel();

            // For open
            //this.initThumbsOpenCarousel();
            //this.initBigMediaOpenCarousel();
            //this.flagBigMediaOpen();
            this.initLogic();

        },

        /**
         * Init carousel for big images
         */
        initBigMediaCarousel: function () {
            var productGalleryBigMedia = this.getBigMedia();
            productGalleryBigMedia.rwdCarousel({
                singleItem: true,
                slideSpeed: 1000,
                navigation: true,
                pagination: true,
                responsiveRefreshRate: 200,
                mouseDrag: false,
                rewindNav: false,
                itemsScaleUp: true,
                afterAction: function() {
                    var current = this.currentItem;
                    var thumbs  = Mall.product.gallery.getThumbs();
                    thumbs.find(".rwd-item")
                        .removeClass("synced")
                        .eq(current)
                        .addClass("synced")
                }
            });


        },

        /**
         * Init carousel for thumbs
         */
        initThumbsCarousel: function() {
            var productGalleryThumbMedia = this.getThumbs();
            productGalleryThumbMedia.rwdCarousel({
                items : 1,
                pagination:false,
                navigation: false,
                touchDrag: false,
                mouseDrag:false,
                afterInit : function(el) {
                    el.find(".rwd-item").eq(0).addClass("synced");
                    var items = Mall.product.gallery.getThumbs().find('.rwd-item');
                    if (items.length <= 4 ) {
                        Mall.product.gallery.getThumbsWrapper().find('.up, .down').addClass('disabled');
                    }
                }
            });

            this.getThumbs().on("click", ".rwd-item", function(e) {
                e.preventDefault();
                Mall.product.gallery.getBigMedia().trigger("rwd.goTo", jQuery(this).data("rwdItem"));
            });

            this.getThumbsWrapper().on('click', '.up', function(event) {
                event.preventDefault();
                var thumbsWrapper = Mall.product.gallery.getThumbsWrapper();
                var item = thumbsWrapper.find('.rwd-item');
                var itemHeight = item.height()+10;
                var wrapper = thumbsWrapper.find('.rwd-wrapper');
                var position = parseInt(wrapper.css('margin-top'));
                var sumItem = itemHeight * (item.length-5);

                wrapper.filter(':not(:animated)').animate({
                    'margin-top': '+='+itemHeight
                });

                if (position == '-'+itemHeight) {
                    thumbsWrapper.find('.up').addClass('disabled');
                }
                if (position != '-'+sumItem) {
                    thumbsWrapper.find('.down').removeClass('disabled');
                }
            });

            this.getThumbsWrapper().on('click', '.down', function(event) {
                event.preventDefault();
                var thumbsWrapper = Mall.product.gallery.getThumbsWrapper();
                var item = thumbsWrapper.find('.rwd-item');
                var itemHeight = item.height()+10;
                var wrapper = thumbsWrapper.find('.rwd-wrapper');
                var position = parseInt(wrapper.css('margin-top'));
                var sumItem = itemHeight * (item.length-5);

                wrapper.filter(':not(:animated)').animate({
                    'margin-top': '-='+itemHeight
                });
                if (position != '-'+itemHeight) {
                    thumbsWrapper.find('.up').removeClass('disabled');
                }
                if (position == '-'+sumItem) {
                    thumbsWrapper.find('.down').addClass('disabled');
                }
            });

        },

        /**
         * Add flag sale/promo image to big images
         */
        flagBigMedia: function() {
            var items = this.getBigMedia().find('.rwd-item');
            items.each(function(i) {
                var flags = jQuery(this).find('a');
                var flag = flags.data('flags');
                flags.append('<i class="flag '+flag+'"></i>');
            });
        },

        /**
         * add flag sale/promo image to big images when gallery open
         */
        flagBigMediaOpen: function() {
            var items = this.getBigMediaOpen().find('.rwd-item');
            items.each(function(i) {
                var flags = jQuery(this).find('.inner-item');
                var flag = flags.data('flags');
                    flags.append('<i class="flag '+flag+'"></i>');
            });
        },

        initLogic: function() {
            // Lupa
            jQuery(window).on('Mall.onResizeEnd', function() {
                var widthWindow = jQuery(window).width();
                var lupa = Mall.product.gallery.getLupa();
                if (widthWindow < Mall.Breakpoint.sm) {
                    lupa.hide();
                } else {
                    lupa.show();
                }
            });

            // Close button
            this.getLightbox().on('click', '#remove-lightbox', function(event) {
                event.preventDefault();
                jQuery(this).parents('#lightbox').hide();
                jQuery('body').removeClass('lightbox');
            });

            this.getBigMedia().on('click', 'a', function(event) {
                if (jQuery(window).width() >= Mall.Breakpoint.sm) {
                    Mall.product.gallery.getLightbox().find(".bl" ).html(jQuery("#galeria-lightbox-wr").html());
                    jQuery('#lightbox').css({display:'block'});
                    jQuery('body').addClass('lightbox');
                }

                Mall.product.gallery.initThumbsOpenCarousel();
                Mall.product.gallery.initBigMediaOpenCarousel();
                Mall.product.gallery.flagBigMediaOpen();
            });
        },

        /**
         * Init carousel for product related / is similar to
         * in this case other colors of product
         */
        initReleatedCarousel: function() {
            var rwd_color = this.getReleated();
            rwd_color.rwdCarousel({
                items:5,
                navigation : true,
                pagination: false,
                itemsScaleUp: false,
                responsive: false,
                rewindNav : false
            });
        },

        /**
         * Init carousel for big images for open gallery
         */
        initBigMediaOpenCarousel: function() {
            this.getBigMediaOpen().rwdCarousel({
                singleItem : true,
                navigation: true,
                pagination:true,
                afterAction : function(el){
                    Mall.product.gallery.getThumbsOpen()
                        .find(".rwd-item")
                        .removeClass("synced")
                        .eq(this.currentItem)
                        .addClass("synced");
                },
                responsiveRefreshRate : 200,
                mouseDrag:true,
                rewindNav : false,
                itemsScaleUp:true,
                transitionStyle : "fade",
                slideSpeed:10,
                afterMove: function() {
                    if(Mall.product.gallery.getLightboxInner().scrollTop()) {
                        body.animate({ scrollTop: 0 }, "slow");
                    }
                },
                afterInit:function(elem) {

                    //TODO refactoring

                    this.rwdControls.prependTo(elem);

                    Mall.product.gallery.getBigMediaOpen().find('.rwd-item').each(function(index, el) {

                        var lightbox = Mall.product.gallery.getLightbox();
                        var img = jQuery(this).find('img');
                        var windowHeight = jQuery(window).height();
                        var hlHeight = lightbox.find('#hl').innerHeight(); // Header lightbox
                        var contentHeight = windowHeight - hlHeight - 90;
                        var imgWidthLoad = jQuery(this).find('img').data('width');
                        img.css({
                            height: contentHeight,
                            width: 'auto'
                        });
                        // ustawienie szerokości contenera dla przeskalowanego zdjęcia
                        var innerItem = img.innerWidth();
                        var innerItemHeight = img.height();
                        img.closest('.inner-item').css('width', innerItem);
                        // Ukrycie button zoom
                        if (innerItem > imgWidthLoad) {
                            jQuery(this).find('.zoom_plus').hide();
                            jQuery(this).find('.zoom_minus').hide();

                        }
                        lightbox.find('.rwd-buttons').width(innerItem + 'px')
                        lightbox.find('.rwd-prev, .rwd-next').css({
                            top: innerItemHeight / 2
                        });

                        var aImage = jQuery(this).find('img');
                        var aImageWidth = parseInt(aImage.css('width'));

                        jQuery('.zoom_minus').on('click', function (event) {
                            var windowWidth = jQuery(window).innerWidth();
                            img.css({
                                height: contentHeight,
                                width: 'auto'
                            });
                            if (windowWidth <= 1023) {
                                lightbox.find('.rwd-buttons').show();
                            }
                            img.closest('.inner-item').css({'width': innerItem, 'margin': '0 auto'});
                            jQuery('.rwd-buttons').width(aImageWidth);
                            jQuery(this).closest('.rwd-wrapper').find('.rwd-item').each(function () {
                                jQuery(this).find('.zoom_minus').addClass('full disabled').removeClass('full').addClass('disabled');
                                jQuery(this).find('.zoom_plus').removeClass('full disabled');
                            });
                        });
                    });
                }
            });

            // Zoom
            this.getBigMediaOpen().find('.zoom_plus').on('click', function(event) {
                event.preventDefault();
                Mall.product.gallery.getBigMediaOpen().find('.rwd-item').each(function(){
                    var img = jQuery(this).find('img');
                    var imgScaleWidth = parseInt(img.width());
                    var imgWidth   =    jQuery(this).find('img').data('width');
                    var divWidth   = parseInt(jQuery(this).find('.item').css("width"));
                    jQuery(img).width(divWidth);
                    jQuery('.rwd-buttons').width(imgScaleWidth);
                    jQuery(img).css("height", 'auto');

                    if (imgWidth < divWidth) {
                        jQuery(img).width(imgWidth+'px'); //Set the width to the div's width
                        jQuery(this).find('.inner-item').css({width:imgWidth,margin:'0 auto'});
                        jQuery(img).css("height", 'auto');
                    } else if (imgWidth >= divWidth) {
                        jQuery(img).width('100%'); //Set the width to the div's width
                        jQuery(this).find('.inner-item').css({width:'100%',margin:'0 auto'});
                        jQuery(img).css("height", 'auto');

                    }
                    jQuery(this).find('.zoom_plus').addClass('full disabled');
                    jQuery(this).find('.zoom_minus').addClass('full').removeClass('disabled');
                })
            });

            // Next
            this.getBigMediaOpen().on('click', '.rwd-next', function(e) {
                e.preventDefault();
                var items         = Mall.product.getThumbsOpen().find('.rwd-item');
                var current       = items.index(Mall.product.getThumbsOpen().find('.synced'));
                var currentLength = items.length;
                var thumbsWrapper = Mall.product.gallery.getThumbsWrapper();
                if(current >= 4 && current <= currentLength) {
                    Mall.product.gallery.getThumbsOpen().find(".rwd-wrapper").animate({
                        marginTop: '-=98px'
                    });
                    if (current >= 4) {
                        thumbsWrapper.find('.up').removeClass('disabled')
                    } else {
                        thumbsWrapper.find('.up').addClass('disabled')
                    }
                    if (current === currentLength - 1) {
                        thumbsWrapper.find('.down').addClass('disabled')
                    } else {
                        thumbsWrapper.find('.down').removeClass('disabled')
                    }
                }
            });
        },

        /**
         * Init carousel for thumbs for open gallery
         */
        initThumbsOpenCarousel: function() {
            var thumbsOpen = this.getThumbsOpen();
            thumbsOpen.rwdCarousel({
                items : 1,
                pagination:false,
                navigation: false,
                mouseDrag:false,
                touchDrag: false,
                transitionStyle: "fade",
                afterInit: function(el) {
                    el.find(".rwd-item").eq(0).addClass("synced");
                    var items = Mall.product.gallery.getThumbsOpen().find('.rwd-item');
                    if (items.length <= 4 ) {
                        Mall.product.gallery.getThumbsWrapperOpen().find('.up, .down').addClass('disabled');
                    }
                }
            });

            thumbsOpen.on("click", ".rwd-item", function(e) {
                e.preventDefault();
                var number = jQuery(this).data("rwdItem");

                Mall.product.gallery.getBigMedia().trigger("rwd.goTo", number);// When you click on thumb image, under opened gallery image need to be that same
                Mall.product.gallery.getBigMediaOpen().trigger("rwd.goTo", number);

                Mall.product.gallery.getThumbsOpen().find('.rwd-item').removeClass("synced");
                jQuery(this).addClass("synced");
            });

            this.getThumbsWrapperOpen().on('click', '.up', function(event) {
                event.preventDefault();
                var thumbsWrapper = Mall.product.gallery.getThumbsWrapperOpen();
                var item = thumbsWrapper.find('.rwd-item');
                var itemHeight = item.height()+10;
                var position = parseInt(thumbsWrapper.find('.rwd-wrapper').css('margin-top'));
                var wrapp = thumbsWrapper.find('.rwd-wrapper');
                var sumItem = itemHeight * (item.length-5);
                wrapp.filter(':not(:animated)').animate({
                    'margin-top': '+='+itemHeight
                });

                if (position == '-'+itemHeight) {
                    thumbsWrapper.find('.up').addClass('disabled');
                }
                if (position != '-'+sumItem) {
                    thumbsWrapper.find('.down').removeClass('disabled');
                }
            });

            this.getThumbsWrapperOpen().on('click', '.down', function(event) {
                event.preventDefault();
                var thumbsWrapper = Mall.product.gallery.getThumbsWrapperOpen();
                var item = thumbsWrapper.find('.rwd-item');
                var itemHeight = item.height()+10;
                var position = parseInt(thumbsWrapper.find('.rwd-wrapper').css('margin-top'));
                var wrapp = thumbsWrapper.find('.rwd-wrapper');
                var sumItem = itemHeight * (item.length-5);

                wrapp.filter(':not(:animated)').animate({
                    'margin-top': '-='+itemHeight
                });
                if (position != '-'+itemHeight) {
                    thumbsWrapper.find('.up').removeClass('disabled');
                }
                if (position == '-'+sumItem) {
                    thumbsWrapper.find('.down').addClass('disabled');
                }
            });
        },

        getGallery: function () {
            return jQuery("#product-gallery");
        },

        getBigMedia: function () {
            return jQuery('#productGalleryBigMedia');
        },

        getBigMediaOpen: function () {
            return jQuery('#productGalleryBigMediaOpen');
        },

        getThumbs: function () {
            return jQuery('#productGalleryThumbMedia');
        },

        getThumbsOpen: function () {
            return jQuery('#productGalleryThumbMediaOpen');
        },

        getThumbsWrapperOpen: function() {
            return jQuery('#wrapper-productGalleryThumbMediaOpen');
        },

        getThumbsWrapper: function() {
            return jQuery('#wrapper-productGalleryThumbMedia');
        },

        /**
         * Get div product related / is similar to
         * in this case other colors of product
         */
        getReleated: function() {
            return jQuery("#rwd-color");
        },

        getReleatedWrapper: function() {
            return jQuery('wrapper-rwd-color');
        },

        getLightbox: function() {
            return jQuery("#lightbox");
        },

        getLightboxInner: function() {
            return jQuery("#inner-lightbox");
        },

        getLupa: function() {
            return this.getBigMedia().find('.view_lupa');
        },

    }
};

jQuery(document).ready(function() {
	Mall.product.init();
});