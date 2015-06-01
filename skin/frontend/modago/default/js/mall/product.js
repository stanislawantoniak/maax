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
        },

	    _lightboxHasSlick: false,
	    _lightboxSlickOptions: {
		    initialSlide: 0,
		    autoplay: false,
		    prevArrow: '<div class="slick-prev"></div>',
		    nextArrow: '<div class="slick-next"></div>'
	    },
	    _lightboxSlickContainer: false,

	    _lightboxThumbsTop: 84,

	    initLightbox: function() {
		    var htmlBody = jQuery('body,html'),
			    lightbox = Mall.product.gallery.getLightbox(),
			    gallery = Mall.product.gallery;
			gallery.getBigMedia().find('a').click(function() {
				lightbox.show();
				htmlBody.addClass('noscroll');
				var currentSlide = gallery.getBigMedia().data('rwdCarousel').currentItem;
				if(!gallery.lightboxInitialized()) {
					gallery._lightboxSlickContainer = gallery.getLightboxGalleryImagesContainer();
					gallery._lightboxSlickContainer.on('afterChange',gallery.lightboxAfterChange);
					gallery._lightboxSlickContainer.on('beforeChange',gallery.lightboxBeforeChange);
					gallery._lightboxSlickOptions.initialSlide = currentSlide;
					gallery._lightboxSlickContainer.slick(gallery._lightboxSlickOptions);
					gallery._lightboxHasSlick = true;
				} else {
					gallery._lightboxSlickContainer.slick('slickGoTo',currentSlide,true);
				}
				gallery._lighboxCalculationsEnabled = true;
				gallery.lightboxThumbChange();
				gallery.lightBoxCalculations();
			});
			jQuery('#lightbox-close').click(function() {
				htmlBody.removeClass('noscroll');
				gallery._lighboxCalculationsEnabled = false;
				lightbox.hide();
			});
		    gallery.getLightboxGalleryThumbs().click(gallery.lightboxThumbClick);
		    gallery.getLightboxGalleryThumbsImages().scroll(gallery.lightBoxCalculations);
		    gallery.getLightboxGalleryThumbsUp().click(gallery.lightboxGalleryThumbsUpClick);
		    gallery.getLightboxGalleryThumbsDown().click(gallery.lightboxGalleryThumbsDownClick);
		    jQuery(window).resize(gallery.lightboxGalleryItemsHideOnResize);
		    jQuery(window).on('Mall.onResizeEnd',gallery.lightBoxCalculations);
		    jQuery(document).delegate('.'+gallery._lightboxGalleryItemCanZoomClass,'click',gallery.lightboxGalleryItemZoom);
	    },

	    lightboxAfterChange: function() {
		    var gallery = Mall.product.gallery;
		    if(gallery._lightboxCurrentSlide != gallery.lightboxGetCurrentSlide()) {
			    gallery.lightboxThumbChange();
			    gallery._lightboxCurrentSlide = gallery.lightboxGetCurrentSlide();
			    gallery.lightboxGalleryItemForceUnzoom();
			    gallery.getBigMedia().trigger("rwd.goTo", gallery._lightboxCurrentSlide);
		    }
	    },

	    _lightboxCurrentSlide: false,
	    lightboxBeforeChange: function() {
		    Mall.product.gallery._lightboxCurrentSlide = Mall.product.gallery.lightboxGetCurrentSlide();
	    },

	    _lightboxThumbActiveClass: 'lighbox-gallery-thumb-active',
	    lightboxThumbChange: function(currentSlide) {
		    var gallery = Mall.product.gallery;
		    if(gallery.lightboxInitialized()) {
			    currentSlide =  !isNaN(currentSlide) ? currentSlide : gallery.lightboxGetCurrentSlide();
			    gallery.lightboxThumbRemoveActive().lightboxThumbAddActive(currentSlide);
			    if(!gallery.lightboxCurrentSlideIsVisibleInThumbs(currentSlide)) {
				    var direction = gallery._lightboxCurrentSlide < gallery.lightboxGetCurrentSlide() ? 'bottom' : 'top';
				    gallery.getLightboxGalleryThumbsImages()
					    .animate({scrollTop: gallery.lightboxCurrentSlideOffset(currentSlide,direction)}, 200);
			    }
		    }
	    },

	    lightboxCurrentSlideOffset: function(currentSlide,place) {
		    //place can be undefined (thumbs are scrolled to center), center, top or bottom;
		    var gallery = Mall.product.gallery,
			    finalPlace = place == 'center' || place =='top' || place == 'bottom' ? place : 'center',
			    thumbsImages = gallery.getLightboxGalleryThumbsImages(),
			    thumb = gallery.getLightboxGalleryThumb(currentSlide),
			    offsetToAdd;

		    switch(finalPlace) {
			    case 'top':
				    offsetToAdd = 0;
				    break;
			    case 'bottom':
				    offsetToAdd = thumbsImages.height() - thumb.height();
				    break;
			    case 'center':
			    default:
				    offsetToAdd = thumbsImages.height() / 2 - thumb.height() / 2;
				    break;
		    }

		    return thumb.offset().top + thumbsImages.scrollTop() - gallery._lightboxThumbsTop - offsetToAdd;
	    },

		lightboxCurrentSlideIsVisibleInThumbs: function (currentSlide) {
			var gallery = Mall.product.gallery,
				thumb = gallery.getLightboxGalleryThumb(currentSlide),
				container = gallery.getLightboxGalleryThumbsImages(),
				thumbTopOffset = thumb.offset().top,
				thumbBottomOffset = thumbTopOffset + thumb.height();
			return !(thumbTopOffset < gallery._lightboxThumbsTop || thumbBottomOffset - gallery._lightboxThumbsTop > container.height());
		},

	    lightboxThumbClick: function() {
		    var gallery = Mall.product.gallery;
		    if(gallery.lightboxInitialized()) {
			    var slickIndex = jQuery(this).data('slick-index');
				gallery.lightboxThumbChange(slickIndex);
			    gallery._lightboxSlickContainer.slick('slickGoTo',slickIndex);
		    }
	    },

	    lightboxThumbRemoveActive: function() {
		    this.getLightboxGalleryThumbs().removeClass(this._lightboxThumbActiveClass);
		    return this;
	    },

	    lightboxThumbAddActive: function(slickIndex) {
		    this.getLightboxGalleryThumb(slickIndex).addClass(this._lightboxThumbActiveClass);
		    return this;
	    },

	    lightboxInitialized: function() {
		    return this._lightboxHasSlick && this._lightboxSlickContainer && this._lightboxSlickContainer.length;
	    },

	    lightboxGetCurrentSlide: function() {
		    return this.lightboxInitialized() ? this._lightboxSlickContainer.slick('slickCurrentSlide') : false;
	    },

	    _lightboxGalleryItemResizingClass: 'lightbox-gallery-item-resizing',
	    lightboxGalleryItemsHideOnResize: function() {
		    Mall.product.gallery.getLightboxGalleryItems().addClass(Mall.product.gallery._lightboxGalleryItemResizingClass);
	    },

	    lightboxGalleryItemsShowOnResizeEnd: function() {
		    Mall.product.gallery.getLightboxGalleryItems().removeClass(Mall.product.gallery._lightboxGalleryItemResizingClass);
	    },

	    _lightboxGalleryImageBigClass: 'lightbox-gallery-image-big',
	    _lightboxGalleryItemCanZoomClass: 'lightbox-gallery-item-can-zoom',
	    _lighboxCalculationsEnabled: false,
	    lightBoxCalculations: function() {
		    var gallery = Mall.product.gallery;
		    if(gallery._lighboxCalculationsEnabled) {
			    var imagesContainer = gallery.getLightboxGalleryImagesContainer(),
				    imagesContainerWidth = imagesContainer.width(),
				    imagesContainerHeight = imagesContainer.height();

			    gallery.getLightboxGalleryImages().each(function () {
				    var _ = jQuery(this),
					    imageWidth = _.data('max-width'),
					    imageHeight = _.data('max-height'),
					    imageRatio = _.data('ratio');
				    if (imageWidth > imagesContainerWidth || imageHeight > imagesContainerHeight) {
					    _.addClass(gallery._lightboxGalleryImageBigClass);
					    if(imagesContainerWidth * imageRatio > imagesContainerHeight + 40) {
						    _.parent().addClass(gallery._lightboxGalleryItemCanZoomClass)
					    } else {
						    _.parent().removeClass(gallery._lightboxGalleryItemCanZoomClass);
					    }
				    } else {
					    _.removeClass(gallery._lightboxGalleryImageBigClass);
					    _.parent().removeClass(gallery._lightboxGalleryItemZoomClass);
					    _.parent().removeClass(gallery._lightboxGalleryItemCanZoomClass);
					    _.css('height','');
				    }
			    });

			    gallery.lightboxGalleryItemRecalculateZoom();
			    gallery.lightboxGalleryItemsShowOnResizeEnd();

			    gallery.lightboxArrowsCalculations();
		    }
	    },

	    lightboxArrowsCalculations: function() {
		    var gallery = Mall.product.gallery,
			    thumbsContainerHeight = gallery.getLightboxGalleryThumbsContainer().height(),
			    thumbsImagesContainer = gallery.getLightboxGalleryThumbsImages();
		    if(gallery._lighboxCalculationsEnabled && thumbsImagesContainer.is(':visible')) {
			    if (thumbsContainerHeight < thumbsImagesContainer[0].scrollHeight) {
				    var thumbsScrollTop = thumbsImagesContainer.scrollTop(),
					    thumbsScrolledToBottom = thumbsImagesContainer[0].scrollHeight - thumbsScrollTop == thumbsImagesContainer.outerHeight();
				    if (!thumbsScrollTop) {
					    gallery.lightboxGalleryThumbsUpHide();
					    gallery.lightboxGalleryThumbsDownShow();
				    } else if (thumbsScrolledToBottom) {
					    gallery.lightboxGalleryThumbsDownHide();
					    gallery.lightboxGalleryThumbsUpShow();
				    } else {
					    gallery.lightboxGalleryThumbsArrowsShow();
				    }
			    } else {
				    gallery.lightboxGalleryThumbsArrowsHide();
			    }
		    }
	    },

	    _lightboxGalleryThumbsViewportStart: 84,
	    _lighboxGalleryThumbsViewportEnd: '',
	    lightboxGalleryThumbsUpClick: function() {
		    var gallery = Mall.product.gallery,
			    thumbsImagesContainer = gallery.getLightboxGalleryThumbsImages(),
			    thumbsImagesContainerScrollTop = thumbsImagesContainer.scrollTop(),
			    thumbs = gallery.getLightboxGalleryThumbs(),
			    indexToScroll = 0;

		    if(thumbsImagesContainerScrollTop) {
			    thumbs.each(function () {
				    var thumb = jQuery(this),
					    thumbIndex = thumb.data('slick-index');
				    if (gallery.lightboxCurrentSlideIsVisibleInThumbs(thumbIndex)) {
					    indexToScroll = thumbIndex - 1;
					    return false;
				    }
			    });
			    gallery.getLightboxGalleryThumbsImages()
				    .animate({scrollTop: gallery.lightboxCurrentSlideOffset(indexToScroll,'top')}, 100);
		    }
	    },

	    lightboxGalleryThumbsDownClick: function() {
		    var gallery = Mall.product.gallery,
			    thumbsImagesContainer = gallery.getLightboxGalleryThumbsImages(),
			    thumbsImagesContainerScrollTop = thumbsImagesContainer.scrollTop(),
			    thumbs = gallery.getLightboxGalleryThumbs(),
			    indexToScroll = thumbs.length - 1;

		    if(thumbsImagesContainer[0].scrollHeight - thumbsImagesContainerScrollTop != thumbsImagesContainer.outerHeight()) {
			    jQuery(thumbs.get().reverse()).each(function () {
				    var thumb = jQuery(this),
					    thumbIndex = thumb.data('slick-index');
				    if (gallery.lightboxCurrentSlideIsVisibleInThumbs(thumbIndex)) {
					    indexToScroll = thumbIndex + 1;
					    return false;
				    }
			    });
			    gallery.getLightboxGalleryThumbsImages()
				    .animate({scrollTop: gallery.lightboxCurrentSlideOffset(indexToScroll,'bottom')}, 100);
		    }
	    },

	    lightboxGalleryThumbsArrowsHide: function() {
			this.getLightboxGalleryThumbsArrows().hide();
	    },

	    lightboxGalleryThumbsArrowsShow: function() {
		    this.getLightboxGalleryThumbsArrows().show();
	    },

	    lightboxGalleryThumbsUpHide: function() {
		    this.getLightboxGalleryThumbsUp().hide();
	    },

	    lightboxGalleryThumbsUpShow: function() {
		    this.getLightboxGalleryThumbsUp().show();
	    },

	    lightboxGalleryThumbsDownHide: function() {
		    this.getLightboxGalleryThumbsDown().hide();
	    },

	    lightboxGalleryThumbsDownShow: function() {
		    this.getLightboxGalleryThumbsDown().show();
	    },

	    _lightboxGalleryItemZoomClass: 'lightbox-gallery-item-zoom',
	    lightboxGalleryItemZoom: function() {
		    var gallery = Mall.product.gallery,
			    _ = jQuery(this);
		    if(_.hasClass(gallery._lightboxGalleryItemCanZoomClass)) {
			    if (!_.hasClass(gallery._lightboxGalleryItemZoomClass)) { //zoom
				    gallery.lightboxGalleryItemZoomIn(_);
			    } else { //zoom out
				    gallery.lightboxGalleryItemForceUnzoom();
			    }
		    }
	    },

	    lightboxGalleryItemForceUnzoom: function() {
		    var gallery = Mall.product.gallery,
			    imagesContainer = gallery.getLightboxGalleryImagesContainer();

		    imagesContainer.find('.'+gallery._lightboxGalleryItemZoomClass).each(function() {
			    var _ = jQuery(this),
				    image = _.find('.'+gallery._lightboxGalleryImageBigClass);
			    _.removeClass(gallery._lightboxGalleryItemZoomClass);
			    image.css('height','');
		    });
	    },

	    lightboxGalleryItemZoomIn: function(item) {
		    var gallery = Mall.product.gallery,
			    image = item.find('.' + gallery._lightboxGalleryImageBigClass);
		    if (image.length) {
			    item.addClass(gallery._lightboxGalleryItemZoomClass);
			    var imageWidth = image.width(),
				    zoomedHeight = 0;
			    if (image.data('max-width') > imageWidth) {
				    zoomedHeight = imageWidth * image.data('ratio');
			    } else {
				    zoomedHeight = image.data('max-height');
			    }
			    if (zoomedHeight) {
				    image.css('height', zoomedHeight + 'px');
			    }
		    }
	    },

	    lightboxGalleryItemRecalculateZoom: function() {
		    var gallery = Mall.product.gallery,
			    zoomed = gallery.getLightboxGalleryImagesContainer().find('.'+gallery._lightboxGalleryItemZoomClass);
		    if(zoomed.length) {
			    if(zoomed.hasClass(gallery._lightboxGalleryItemCanZoomClass)) {
				    gallery.lightboxGalleryItemZoomIn(zoomed);
			    } else {
				    gallery.lightboxGalleryItemForceUnzoom();
			    }
		    }
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
                    var thumbs  = Mall.product.gallery.getThumbs();
                    thumbs.find(".rwd-item")
                        .removeClass("synced")
                        .eq(this.currentItem)
                        .addClass("synced")
                },
                afterInit: function() {
                    // Horizontal center big medias and Lupa always on bottom
                    var maxHeight = Mall.product.gallery.findMaxHeightBigMedia();
                    Mall.product.gallery.getBigMedia().find('.rwd-item').each( function() {
                        if (jQuery(this).height() != maxHeight) {
                            // Horizontal center big medias
                            var itemH = jQuery(this).height();
                            var padding = ((maxHeight - itemH) / 2);
                            jQuery(this).find('a').css('padding', padding + 'px 0');
                        }
                    });
	                Mall.product.gallery.initLightbox();
                }
            });
        },

        /**
         * Find max height of big medias from rwd carousel items
         * @returns {number}
         */
        findMaxHeightBigMedia: function() {
            var items = this.getBigMedia().find('.rwd-item');
            var maxHeight = 0;
            items.each(function(index, elem) {
                if(jQuery(this).height() >= maxHeight) {
                    maxHeight = jQuery(elem).height();
                }
            });
            return maxHeight;
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
                    Mall.product.gallery.getThumbsWrapper().find('.up').addClass('disabled');
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

        getBigMedia: function () {
            return jQuery('#productGalleryBigMedia');
        },

        getThumbs: function () {
            return jQuery('#productGalleryThumbMedia');
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

	    getLightbox: function() {
		    return jQuery('#lightbox');
	    },

	    getLightboxGallery: function() {
		    return jQuery('#lightbox-gallery');
	    },

	    getLightboxGalleryThumbsContainer: function() {
		    return jQuery('#lightbox-gallery-thumbs');
	    },

	    getLightboxGalleryThumbsImages: function() {
		    return jQuery('#lightbox-gallery-thumbs-images');
	    },

	    _lightboxGalleryThumbClass: 'lightbox-gallery-thumb',
	    getLightboxGalleryThumbs: function() {
		    return this.getLightboxGalleryThumbsImages().find('.'+this._lightboxGalleryThumbClass);
	    },
	    getLightboxGalleryThumb: function(slickIndex) {
		    return this.getLightboxGalleryThumbsImages().find('.'+this._lightboxGalleryThumbClass+'[data-slick-index='+slickIndex+']');
	    },

	    getLightboxGalleryThumbsUp: function() {
			return jQuery('#lightbox-gallery-thumbs-up');
	    },

	    getLightboxGalleryThumbsDown: function() {
			return jQuery('#lightbox-gallery-thumbs-down');
	    },

	    _lightboxGalleryThumbsArrowsClass: 'lightbox-gallery-thumbs-arrow',
	    getLightboxGalleryThumbsArrows: function() {
		    return this.getLightboxGalleryThumbsContainer().find('.'+this._lightboxGalleryThumbsArrowsClass);
	    },

	    getLightboxGalleryImagesContainer: function() {
		    return jQuery('#lightbox-gallery-images');
	    },

	    _lightboxGalleryImageClass: 'lightbox-gallery-image',
	    getLightboxGalleryImages: function() {
		    return this.getLightboxGalleryImagesContainer().find('.'+this._lightboxGalleryImageClass);
	    },

	    _lightboxGalleryItemClass: 'lightbox-gallery-item',
	    getLightboxGalleryItems: function() {
		    return this.getLightboxGalleryImagesContainer().find('.'+this._lightboxGalleryItemClass);
	    }

    }
};

jQuery(document).ready(function() {
	Mall.product.init();
});