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

	setChooseText: function(text) {

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
	}
};

jQuery(document).ready(function() {
	Mall.product.init();
	Mall.product.review.init();
});