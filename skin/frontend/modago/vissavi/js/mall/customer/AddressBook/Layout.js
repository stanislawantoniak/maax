/**
 * Created by pawelchyl on 16.09.2014.
 */

(function () {
    "use strict";

    Mall.customer.AddressBook.Layout = {
		_book: null,
		content: jQuery("#addressbook"),
		
		getSelectedTemplate: function(){
			return jQuery("#selected-address-template").html();
		},
		getNormalTemplate: function(){
			return jQuery("#normal-address-template").html();
		},
		getModalData: function () {
			var data = {},
				form = this.getModal().find(".new-address-form");

			jQuery.each(form.serializeArray(), function () {
				data[this.name] = this.value;
			});

			return data;
		},
		getModal: function () {
			return jQuery("#addressbook-modal");
		},
		setAddressBook: function(addressBook){
			this._book = addressBook;
			return this;
		},
		getAddressBook: function(){
			return this._book;
		},
		
		injectEntityIdToEditForm: function (form, id, addressBook) {
			jQuery("<input/>", {
				type: "hidden",
				name: addressBook.getEntityIdKey(),
				value: id
			}).appendTo(form);
		},
		fillEditForm: function (address, form) {
			form = jQuery(form);
			jQuery.each(address.getData(), function (idx, item) {
                var _item = item;
				if (idx.indexOf("street") !== -1 && item) {
                    if (jQuery.isArray(item)) {
                        _item = item[0] ? item[0] : '';
                    }
				}
				if (form.find("[name='"+ idx +"']").length > 0) {
					form.find("[name='"+ idx +"']").val(_item);
				}
			});
		},
		lockButton: function (button) {
			jQuery(button).prop("disabled", true);
		},

		unlockButton: function (button) {
			jQuery(button).prop("disabled", false);
		},
		
		processAddressToDisplay: function(address){
			var addressData = jQuery.extend({}, address.getData());
			if(addressData.street){
				addressData.street = addressData.street[0]
			}
			return addressData;
		},

		getAddNewButton: function (type) {
			var templateHandle = jQuery("#addressbook-add-new-template")
					.clone()
					.removeClass("hidden"),
				self = this;

			templateHandle.click(function () {
				self.showAddNewModal(jQuery("#addressbook-modal"), type);
			});

			return templateHandle;
		},
		showAddNewModal: function (modal, type, edit) {
			edit = edit === undefined ? false : edit;

			modal = jQuery(modal);
			modal.find(".modal-body")
				.html("")
				.append(this.getAddNewForm(type));
			modal.find("#modal-title").html(edit ? 
				Mall.translate.__("edit-address") : Mall.translate.__("add-new-address"));
			
			this.attachNewAddressInputsMask(modal, type);
			this.attachNewAddressBootstrapTooltip(modal, type);
		},
		getSelectButton: function () {
			var buttonWrapper = jQuery("<div/>", {
				"class": "form-group clearfix"
			});

			jQuery("<button/>", {
				"class": "button button-primary large pull-right select-address",
				html: Mall.translate.__("save")
			}).appendTo(buttonWrapper);

			return buttonWrapper;
		},
		getNewAddressConfig: function (type) {
			return [
				//{
				//    name:       type + "[firstname]",
				//    id:         type + "_firstname",
				//    type:       "text",
				//    label:      "Imię",
				//    labelClass: "col-sm-3",
				//    inputClass: "form-control firstName hint"
				//},
				{
					name:       "lastname",
					id:         type + "_lastname",
					type:       "text",
					label:      Mall.translate.__("lastname"),
					labelClass: "col-sm-3",
					inputClass: "form-control lastName required hint"
				},
				{
					name:       "telephone",
					id:         type + "_telephone",
					type:       "text",
					label:      Mall.translate.__("phone"),
					labelClass: "col-sm-3",
					inputClass: "form-control telephone phone required validate-telephone hint"
				},
				{
					name:       "company",
					id:         type + "_company",
					type:       "text",
					label:      Mall.translate.__("company-name") + 
						"<br/>(" + Mall.translate.__("optional") + ")",
					labelClass: "col-sm-3 double-line",
					inputClass: "form-control firm hint"
				},
				{
					name:       "vat_id",
					id:         type + "_vat_id",
					type:       "text",
					label:      Mall.translate.__("nip") + 
						"<br>(" + Mall.translate.__("optional") + ")",
					labelClass: "col-sm-3 double-line",
					inputClass: "form-control vat_id nip validate-nip hint"
				},
				{
					name:       "street",
					id:         type + "_street_1",
					type:       "text",
					label:      Mall.translate.__("street-and-number"),
					labelClass: "col-sm-3 ",
					inputClass: "form-control street hint required"
				},
				{
					name:       "postcode",
					id:         type + "_postcode",
					type:       "text",
					label:      Mall.translate.__("postcode"),
					labelClass: "col-sm-3",
					inputClass: "form-control postcode zipcode hint validate-postcodeWithReplace required"
				},
				{
					name:       "city",
					id:         type + "_city",
					type:       "text",
					label:      Mall.translate.__("city"),
					labelClass: "col-sm-3",
					inputClass: "form-control city hint required"
				}

			];
		},

		getInput: function (name, id, type, label, labelClass, inputClass, value) {
			var result = {
				label: "",
				input: ""
			},
				inputWrapper;

			result.label = jQuery("<label/>", {
				"class": labelClass,
				"for": id,
				html: label
			});

			inputWrapper = jQuery("<div/>", {
				"class": "col-lg-9 col-md-9 col-sm-9 col-xs-11"
			});

			jQuery("<input/>", {
				type: type,
				class: inputClass,
				value: value,
				name: name,
				id: id
			}).appendTo(inputWrapper);

			result.input = inputWrapper;

			return result;
		},

		getFormGroup: function (first) {
			var group,
				className;

			if (first === undefined) {
				first = false;
			}

			className = "form-group clearfix" + (!first ? " border-top" : "");

			group = jQuery("<div/>", {
				"class": className
			});

			jQuery("<div/>", {
				"class": "row"
			}).appendTo(group);

			return group;
		},
		getNewAddressForm: function () {
			var form, panel;
			form = jQuery("<form/>", {
				"class": "form clearfix new-address-form",
				method: "POST",
				action: Config.url.address.save
			});

			jQuery("<input/>", {
				type: "hidden",
				name: "form_key",
				value: Mall.getFormKey()
			}).appendTo(form);

			jQuery("<input/>", {
				type: "hidden",
				name: "country_id",
				value: jQuery("#country_id").val()
			}).appendTo(form);

			panel = jQuery("<div/>", {
				"class": "panel panel-default"
			}).appendTo(form);

			jQuery("<div/>", {
				"class": "panel-body"
			}).appendTo(panel);

			return form;
		},
		handleChangeAddressClick: function(e){
			var type = e.data.type,
				element = jQuery(e.target),
				block = this.content.find(".panel-adresses." + type);

			element.toggleClass("open");

			this._rollAddressList(type, block, element.hasClass("open"));
		},
		toggleOpenAddressList: function (type) {
			jQuery(".panel-footer").find("." + type).click();
		},
		
		attachNewAddressInputsMask: function (modal, type) {

		},
		
		attachNewAddressBootstrapTooltip: function(modal, type) {

			jQuery('#modal-body form').attr('autocomplete', "off");//no autocomplete

			//hint data
			//shoping and billing
			jQuery('#shipping_firstname, #billing_firstname').attr('data-original-title', Mall.translate.__("Enter name."));
			jQuery('#shipping_lastname, #billing_lastname').attr('data-original-title', Mall.translate.__("Enter last name."));
			jQuery('#shipping_company, #billing_company').attr('data-original-title', Mall.translate.__("Enter company name."));
			jQuery('#shipping_street_1, #billing_street_1').attr('data-original-title', Mall.translate.__("Enter street and number."));
			jQuery('#shipping_postcode, #billing_postcode').attr('data-original-title', Mall.translate.__("Zip-code should be entered in the format xx-xxx."));
			jQuery('#shipping_city, #billing_city').attr('data-original-title', Mall.translate.__("Enter city name."));
			jQuery('#shipping_telephone, #billing_telephone').attr('data-original-title', Mall.translate.__("Phone number we need only to contact concerning orders for example courier delivering the shipment."));
			//end hint data

			//visual fix for hints
			jQuery('input[type=text],input[type=email],input[type=password],textarea').not('.phone, .zipcode, .nip').tooltip({
				placement: function(a, element) {
					var viewport = window.innerWidth;
					var placement = "right";
					if (viewport < 991) {
						placement = "top";
					}
					if (viewport < 768) {
						placement = "right";
					}
					if (viewport < 600) {
						placement = "top";
					}
					return placement;
				},
				trigger: "focus"
			});
			jQuery('.phone, .zipcode, .nip').tooltip({
				placement: "right",
				trigger: "focus"
			});

			jQuery('input[type=text],input[type=email],input[type=password],textarea ').off('shown.bs.tooltip').on('shown.bs.tooltip', function () {
				if(jQuery(this).parent(':has(i)').length && jQuery(this).parent().find('i').is(":visible")) {
					jQuery(this).next('div.tooltip.right').animate({left: "+=25"}, 100, function () {
					});
				}
			});
			//end visual fix for hints

			//validate
			Mall.validate.init();
			jQuery('#modal-body form').validate(Mall.validate._default_validation_options);

			jQuery("div.form-group:has('#shipping_company'), div.form-group:has('#billing_company')").addClass('hide-success-vaild');

			jQuery('#shipping_vat_id').on('change fucus click keydown keyup', function() {
				if (jQuery(this).val().length) {
					jQuery(this).parents('.form-group').removeClass('hide-success-vaild');
				} else {
					jQuery(this).parents('.form-group').addClass('hide-success-vaild');
				}
			});

			//end validate
		},
	};
})();