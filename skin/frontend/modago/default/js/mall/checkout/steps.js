/**
 * Created by pawelchyl on 10.09.2014.
 */

(function () {
    "use strict";
    Mall.Checkout.steps = {
		////////////////////////////////////////////////////////////////////////
		// Addressbook
		////////////////////////////////////////////////////////////////////////
		addressbook: {
			id: "step-0",
			code: "addressbook",
			doSave: true,
			getSelectedTemplate: function(){
				return jQuery("#selected-address-template").html();
			},
			getNormalTemplate: function(){
				return jQuery("#normal-address-template").html();
			},
			setAddressBook: function(addressBook){
				this._addressBook = addressBook;
				return this;
			},
			getAddressBook: function(){
				return this._addressBook;
			},
			renderSelectedAddress: function(type){
				var template = this.getSelectedTemplate(),
					addressBook = this.getAddressBook(),
					target = jQuery(".current-addres."+type, this.content),
                    defaultAdderssObject,
					addressObject = addressBook.getSelected(type);
					defaultAdderssObject = addressBook.getDefault(type);

				if(addressObject){
					var defaultCaption = "";
					if(defaultAdderssObject && defaultAdderssObject.getId()==addressObject.getId()){
						defaultCaption = Mall.translate.__("default-"+type+"-address");
					}else{
						// @todo default checkbox
						defaultCaption = "[v] set as default";
					}
					var data = jQuery.extend(
						this.processAddressToDisplay(addressObject), 
						{"default_caption": defaultCaption}
					);
					target.html(Mall.replace(template, data));
				}else{
					target.html(Mall.translate.__("No addresses"));
				}	
			},
			renderAddressList: function(type){
				var template = this.getNormalTemplate(),
					addressBook = this.getAddressBook(),
					target = jQuery(".panel-adresses."+type, this.content),
					selectedAddress = addressBook.getSelected(type),
					self = this;
					
				var addressCollection = addressBook.getAddressBook();
				
				target.html('');
				
				
				if(addressCollection.length){
					jQuery.each(addressCollection, function(){
						// Do not allow sleected address
						if(selectedAddress && this.getId()==selectedAddress.getId()){
							return;
						}
						
						var data = self.processAddressToDisplay(this);
						var node = jQuery(Mall.replace(template, data));
						self.processAddressNode(node, this, addressBook, type);
						target.append(node);
					});
                }else{
                    target.html(Mall.translate.__("No addresses"));
                }
                target.append(this.getAddNewButton(type));
            },

            getAddNewButton: function (type) {
                var templateHandle = jQuery("#addressbook-add-new-template").clone(),
                    self = this;

                templateHandle.click(function () {
                    self.showAddNewModal(jQuery("#addressbook-modal"), type);
                });

                return templateHandle;
            },

            attachNewAddressInputsMask: function (modal, type) {
                modal.find("#" + type + "_postcode").mask("99-999");
                modal.find("#" + type + "_vat_id").mask("999-999-99-99");
            },

            showAddNewModal: function (modal, type) {
                modal = jQuery(modal);
                modal.find(".modal-body")
                    .html("")
                    .append(this.getAddNewForm(type));
                modal.find("#modal-title").html("DODAJ NOWY ADRES");
                this.attachNewAddressInputsMask(modal, type);
            },

            getSelectButton: function () {
                var buttonWrapper = jQuery("<div/>", {
                    "class": "form-group clearfix"
                });

                jQuery("<button/>", {
                    "class": "button button-primary large pull-right select-address",
                    html: "Wybierz"
                }).appendTo(buttonWrapper);

                return buttonWrapper;
            },

            toggleOpenAddressList: function (type) {
                jQuery(".panel-footer").find("." + type).click();
            },

            getAddNewForm: function (type) {
                var form = this.getNewAddressForm(),
                    panelBody = form.find(".panel-body"),
                    element,
                    formGroup,
                    self = this,
                    address;

                element = this.getInput("firstname"
                    , type + "_firstname"
                    , "text"
                    , "Imię"
                    , "col-sm-3"
                    , "form-control firstName hint"
                    , "");

                formGroup = this.getFormGroup(true);
                formGroup.find(".row").append(element.label).append(element.input);
                panelBody.append(formGroup);

                jQuery.each(this.getNewAddressConfig(type), function (idx, item) {
                    formGroup = self.getFormGroup();
                    element = self.getInput(
                        item.name
                        , item.id
                        , item.type
                        , item.label
                        , item.labelClass
                        , item.inputClass
                        , ""
                    );
                    formGroup.find(".row").append(element.label).append(element.input);
                    panelBody.append(formGroup);
                });

                panelBody.append(this.getSelectButton());

                panelBody.find(".select-address").click(function (e) {
                    e.preventDefault();
                    var data = self.getModalData();

                    self.lockButton(this);
                    self.getAddressBook().save(data).done(function (data) {
                        if (Boolean(data.status) === false) {
                            alert(data.content.join("\n"));
                        } else {
                            address = self.getAddressBook().get(data.content.entity_id);
                            if (type === "billing") {
                                self.getAddressBook().setSelectedBilling(address);
                            } else {
                                self.getAddressBook().setSelectedShipping(address);
                            }
                            self.renderSelectedAddress(type);
                            self.renderAddressList("billing");
                            self.renderAddressList("shipping");
                            self.getModal().modal("hide");
                            self.toggleOpenAddressList(type);
                        }
                    }).always(function () {
                        self.unlockButton(e.target);
                    });
                });

                return form;
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

            lockButton: function (button) {
                jQuery(button).prop("disabled", true);
            },

            unlockButton: function (button) {
                jQuery(button).prop("disabled", false);
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
                        label:      "Nazwisko",
                        labelClass: "col-sm-3",
                        inputClass: "form-control lastName hint"
                    },
                    {
                        name:       "company",
                        id:         type + "_company",
                        type:       "text",
                        label:      "Nazwa firmy<br>(opcjonalnie)",
                        labelClass: "col-sm-3 double-line",
                        inputClass: "form-control firm hint"
                    },
                    {
                        name:       "vat_id",
                        id:         type + "_vat_id",
                        type:       "text",
                        label:      "NIP<br>(opcjonalnie)",
                        labelClass: "col-sm-3 double-line",
                        inputClass: "form-control vat_id city hint"
                    },
                    {
                        name:       "street[]",
                        id:         type + "_street_1",
                        type:       "text",
                        label:      "Ulica i numer",
                        labelClass: "col-sm-3 ",
                        inputClass: "form-control street hint"
                    },
                    {
                        name:       "postcode",
                        id:         type + "_postcode",
                        type:       "text",
                        label:      "Kod pocztowy",
                        labelClass: "col-sm-3",
                        inputClass: "form-control postcode zipcode hint"
                    },
                    {
                        name:       "city",
                        id:         type + "_city",
                        type:       "text",
                        label:      "Miejscowość",
                        labelClass: "col-sm-3",
                        inputClass: "form-control city hint"
                    },
                    {
                        name:       "telephone",
                        id:         type + "_telephone",
                        type:       "text",
                        label:      "Numer telefonu",
                        labelClass: "col-sm-3",
                        inputClass: "form-control telephone city hint"
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
                    "class": "col-sm-9"
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

			handleNeedInvoiceClick: function(e){
				var addressBook = this.getAddressBook(),
					state = jQuery(e.target).is(":checked"),
					curState = this.getAddressBook().getNeedInvoice(),
					invoiceBlock = this.content.find("#block_invoice");
				// Only if is change neetween adressbook and widget
				if(curState!=state){
					addressBook.setNeedInvoice(state);
				}
				if(addressBook.getNeedInvoice()){
					invoiceBlock.show();
				}else{
					invoiceBlock.hide()
				}
				
			},
			
			handleChangeAddressClick: function(e){
				var type = e.data.type,
					element = jQuery(e.target),
					block = this.content.find(".panel-adresses." + type),
					relatedType = type=="billing" ? "shipping" : "billing",
					relatedElement = this.content.find(".change_address." + relatedType),
					relatedBlock = this.content.find(".panel-adresses." + relatedType);
			
				element.toggleClass("open");
				relatedElement.removeClass("open");
				
				this._rollAddressList(relatedElement, relatedBlock, relatedElement.hasClass("open"));
				this._rollAddressList(element, block, element.hasClass("open"));
			},
			
			_rollAddressList: function(element, block, doOpen){
				// Need move to one tag
				if(doOpen){
					block.show();
					block.find('.panel').show();
					element.text(Mall.translate.__("roll-up"));
				}else{
					block.hide();
					block.find('.panel').hide();
					element.text(Mall.translate.__("change-address"));
				}
				// show add new address button
                block.find(".add-new").toggleClass("hidden");
			},
			processAddressNode: function(node, address, addressBook, type){
				var settableDefault = true,
					removeable = addressBook.isRemoveable(address.getId()),
					defaultAddress = addressBook.getDefault(type),
					remove = node.find(".remove"),
					setDefault = node.find(".set-default"),
					choose = node.find(".choose"),
					edit = node.find(".edit");
			
				if(defaultAddress && defaultAddress.getId()==address.getId()){
					settableDefault = false;
				}
				
				var eventData = {
					addressBook: addressBook, 
					step: this, 
					address: address, 
					type: type
				};
				
				remove.click(eventData, this.removeAddress);
				edit.click(eventData, this.editAddress);
				setDefault.click(eventData, this.setDefaultAddress);
				choose.click(eventData, this.chooseAddress);
				
				remove[removeable ? "show" : "hide"]();
				setDefault[settableDefault ? "show" : "hide"]();
				
				return node;
			},

			removeAddress: function(event){
				console.log("Remove clicked", event.data);
                var deffered,
                    self = this;

                if (!confirm("Are you sure?")) {
                    return false;
                }

                deffered = event.data.addressBook.remove(event.data.address.getId());
                if (deffered === null) {
                    alert("Address can't be removed.");
                } else {
                    deffered.done(function (data) {
                        if (data.status !== undefined) {
                            if (Boolean(data.status) === false) {
                                alert("Address can't be removed.");
                            } else {
                                event.data.step.renderSelectedAddress(event.data.type);
                                event.data.step.renderAddressList("billing");
                                event.data.step.renderAddressList("shipping");
                                event.data.step.toggleOpenAddressList(event.data.type);
                            }
                        }
                    });
                }

				return false;
			},
			editAddress: function(event){
				console.log("Edit clicked", event.data);
				return false;
			},
			setDefaultAddress: function(event){
				console.log("Set default clicked", event.data);
				return false;
			},
			chooseAddress: function(event){
				var addressBook = event.data.addressBook,
					address = event.data.address,
					type = event.data.type,
					self = event.data.step;
				
				switch(type){
					case "billing":
						addressBook.setSelectedBilling(address);
					break;
					case "shipping":
						addressBook.setSelectedShipping(address);
					break;
				}
				
				
				self.renderSelectedAddress("shipping");
				self.renderSelectedAddress("billing");
				
				self.renderAddressList("shipping");
				self.renderAddressList("billing");
				
				return false;
			},
			processAddressToDisplay: function(address){
				var addressData = jQuery.extend({}, address.getData());
				if(addressData.street){
					addressData.street = addressData.street[0]
				}
				return addressData;
			},
			formatStreet: function(streetArray){
				return streetArray.length ? streetArray[0] : "";
			},
			getFormKey: function(){
				return this.content.find("input[name='form_key']").val();
			},
			collect: function(){
				
				var adressBook = this.getAddressBook(),
					billing = adressBook.getSelectedBilling(),
					shipping = adressBook.getSelectedShipping(),
					invoice = adressBook.getNeedInvoice(),
					useBillingForShipping;
				
				// No invoice neededd same shiping and billing
				if(!invoice){
					billing = shipping;
				}
				
				useBillingForShipping = billing.getId()==shipping.getId();
				
				var data = [
					{"name": "form_key", "value": this.getFormKey()},
					{"name": "billing_address_id", "value": billing.getId()},
					{"name": "billing[use_for_shipping]", "value": useBillingForShipping ? 1 : 0}
				];
				
				// Push billing data
				jQuery.each(billing.getData(), function(idx){
					data.push({name: 'billing['+idx+"]", value: this});
				});
				
				if(!useBillingForShipping){
					// Push shipping address id
					data.push({"name": "shipping_address_id", "value": shipping.getId()});
					// Push shipping data
					jQuery.each(shipping.getData(), function(idx){
						data.push({name: 'shipping['+idx+"]", value: this});
					});
				}
				
				return data;
			},
			onPrepare: function(){
				var self = this;
				this.content.find("form").submit(function(){
                    if (jQuery(this).valid()) {
                        self.submit();
                    }
					return false;
				});
				this.renderSelectedAddress("shipping");
				this.renderSelectedAddress("billing");
				this.renderAddressList("shipping");
				this.renderAddressList("billing");
				
				// Handle need invoice
				var invoice = this.content.find(".need_invoice");
				if(this.getAddressBook().getNeedInvoice()){
					invoice.prop("checked", true);
				}
				invoice.change(function(e){
					self.handleNeedInvoiceClick(e);
				}).change();
				
				// Handle open list
				this.content.find(".change_address").each(function(){
					var type = jQuery(this).hasClass("billing") ? "billing" : "shipping";
					jQuery(this).click({type: type}, function(e){
						self.handleChangeAddressClick(e);
						return false;
					})
				})
			},
			
		},
		////////////////////////////////////////////////////////////////////////
		// Address step for all cases
		////////////////////////////////////////////////////////////////////////
		address: {
			
			id: "step-0",
			code: "address",
			doSave: true,

            /**
             * HTML id of address form
             */
            _self_form_id: "co-address",

            /**
             * Which fields copy to invoice from shipping.
             */
			_invoice_copy_shipping_fields: [
				"#billing_company",
				"#billing_street",
				"#billing_postcode",
				"#billing_city"
			],

            /**
             * Which fields are in billing section.
             */
			_billing_names: [
				"billing[firstname]",
				"billing[lastname]",
				/*"billing[telephone]", - skip telphone - always from account*/
				"billing[company]",
				"billing[vat_id]",
				"billing[street][]",
				"billing[postcode]",
				"billing[city]"
			],

            /**
             * Init this step + validation for form fields.
             *
             * @return void
             */
			init: function () {
				this.attachInvoiceCopyShippingDataEvent();
				this.attachInvoiceEvent();
                this.setInvoiceDataVisiblity();
                this.onLoadDisableInvoiceFields();
                this.validate._checkout = this.checkout;
                this.afterEmailValidationAction();

                // add validation to form
                this.validate.init();
                this.enableBillingPostcodeMask();
			},
			
			onDisable: function(){
				jQuery("#step-0-submit").prop("disabled", true);
			},
			
			onEnable: function(){
				jQuery("#step-0-submit").prop("disabled", false);
			},

            enableBillingPostcodeMask: function () {
                jQuery("#billing_postcode").mask("99-999");

                return this;
            },

            /**
             * Toggle visibility state of invoice data form.
             *
             * @param state
             * @returns {Mall.Checkout.steps}
             */
            toggleInvoiceData: function (state) {
                jQuery('#invoice_data').css({
                    display: state ? "block" : "none"
                });

                return this;
            },

            setInvoiceDataVisiblity: function () {
                var needInvoice = jQuery("#invoice_vat").is(":checked");
                if (needInvoice) {
                    this.toggleInvoiceData(true);
                } else {
                    this.toggleInvoiceData(false);
                }

                return this;
            },

            onLoadDisableInvoiceFields: function () {
                if (jQuery("#invoice_data_address")
                    && jQuery("#invoice_data_address").is(":checked")) {
                    this.invoiceDisableFields(this._invoice_copy_shipping_fields);
                }

                return this;
            },

			invoiceCopyShippingData: function () {
				jQuery("#billing_company").val(jQuery("#shipping_company").val());
				jQuery("#billing_street").val(jQuery("#shipping_street").val());
				jQuery("#billing_postcode").val(jQuery("#shipping_postcode").val());
				jQuery("#billing_city").val(jQuery("#shipping_city").val());

				return this;
			},

			invoiceDisableFields: function (fields) {
				jQuery.each(fields,  function (idx, item) {
					jQuery(item).prop("readonly", true);
				});

				return this;
			},

			invoiceClearCopiedFields: function (fields) {
				jQuery.each(fields,  function (idx, item) {
					jQuery(item).val("");
				});

				return this;
			},

			invoiceEnableFields: function (fields) {
				jQuery.each(fields,  function (idx, item) {
					jQuery(item).prop("readonly", false);
				});

				return this;
			},

			attachInvoiceCopyShippingDataEvent: function () {
				var self = this;
				jQuery("#invoice_data_address").click(function () {
					if (jQuery(this).is(":checked")) {
						self.invoiceCopyShippingData();
						self.invoiceDisableFields(self._invoice_copy_shipping_fields);
                        self.setSameAsBilling(1);
					} else {
						self.invoiceClearCopiedFields(self._invoice_copy_shipping_fields);
						self.invoiceEnableFields(self._invoice_copy_shipping_fields);
                        self.setSameAsBilling(0);
					}
				});
			},

            setSameAsBilling: function (state) {
                jQuery("[name='shipping[same_as_billing]']").val(state);

                return this;
            },

			attachInvoiceEvent: function () {
				var self = this;
				jQuery("#invoice_vat").click(function () {
					if (jQuery("#invoice_data_address").is(":checked")) {
						self.invoiceCopyShippingData();
						self.invoiceDisableFields(self._invoice_copy_shipping_fields);
                        self.setSameAsBilling(1);
					}
				});

				return this;
			},

			getIsNeedInvoice: function () {
				return jQuery("#invoice_vat").is(":checked");
			},

			onPrepare: function(checkoutObject){
				this.init();
				var self = this;
				this.content.find("form").submit(function(){
                    if (jQuery(this).valid()) {
                        self.submit();
                    }
					return false;
				});
			},

			isPasswordNotEmpty: function(){
				if(this.content.find("[name='account[password]']").length){
					return this.content.find("[name='account[password]']").val().length>0;
				}
				return false;
			},

			getBillingFromShipping: function () {
				var self = this,
					billingData = [],
					selector;
				jQuery.each(this._billing_names, function (idx, item) {
					selector = item.replace("billing", "shipping");
					billingData.push({
						name: item,
						value: jQuery("[name='"+ selector +"']").val()
					});
				});

				return billingData;
			},

			collect: function () {
				var form = jQuery("#co-address"),
					password,
					billingData,
					stepData = [],
                    telephone;

                if (!parseInt(jQuery("#customer_logged_in").val(), 10)) {
                    password = form.find("#account_password").val();
                    // set password confirmation
                    if (password.length > 0) {
                        form.find("#account_confirmation").val(password);
                    }
                }

				// set billing data
                if (jQuery("#orders_someone_else").is(":checked")) {
                    form.find("[name='billing[firstname]']").val(form.find("[name='shipping[firstname]']").val());
                    form.find("[name='billing[lastname]']").val(form.find("[name='shipping[lastname]']").val());
                } else {
                    form.find("[name='billing[firstname]']").val(form.find("[name='account[firstname]']").val());
                    form.find("[name='billing[lastname]']").val(form.find("[name='account[lastname]']").val());
                }

				// copy shipping data if order will be delivered to myself
				if (!form.find("[name='shipping[different_shipping_address]']").is(":checked")) {
					form.find("#shipping_firstname").val(form.find("#account_firstname").val());
					form.find("#shipping_lastname").val(form.find("#account_lastname").val());
					form.find("#shipping_telephone").val(form.find("#account_telephone").val());
				}

                // copy phone
                telephone = form.find("#account_telephone").val();
				form.find("#billing_telephone").val(telephone);
                if (!form.find("#orders_someone_else").is(":checked")) {
                    form.find("#shipping_telephone").val(telephone);
                }

                //use_for_shipping
                if(!form.find("input[name='billing[need_invoice]']").is(":checked")){ // if is not visible
                    form.find("[name='billing[use_for_shipping]']").val(1);
                } else { // is visible
                    if(form.find('input[name=invoice_data_address]').is(':checked')) { // and checked
                        form.find("[name='billing[use_for_shipping]']").val(1);
                    } else {// is not checked
                        form.find("[name='billing[use_for_shipping]']").val(0);
                    }
                }

				stepData = form.serializeArray();
				// fill billing data with shipping
				if (!this.getIsNeedInvoice()) {
					billingData = this.getBillingFromShipping();
					stepData = this.mergeArraysOfObjects(stepData, billingData);
				}
				
				// Push method
				stepData.push({name: "method", value: this.checkout.getMethod()});

				return stepData;
			},

            mergeArraysOfObjects: function (arr1, arr2) {
                var self = this,
                    index;
                jQuery.each(arr2, function (idx, obj) {
                    if (Mall.Checkout.steps.getIsObjectKeyExistsInArray(obj.name, arr1)) {
                        arr1[Mall.Checkout.steps.getArrayIndexByObjectKey(obj.name, arr1)] = obj;
                    }
                });

                return arr1;
            },

            getBuingForSomeoneElse: function () {
                return jQuery("#orders_someone_else").is(":checked");
            },

            getCustomerIsLoggedIn: function () {
                return parseInt(jQuery("#customer_logged_in").val(), 10);
            },

            afterEmailValidationAction: function () {
                if (this.getCustomerIsLoggedIn()) {
                    return true;
                }

                var promise = Mall.validate.validators.emailbackend(
                    jQuery("input[name='account[email]']").val(),
                    jQuery("input[name='account[email]']"),
                    {
                        url: Config.url.customer_email_exists,
                        form_key: jQuery("input[name='form_key']").val()
                    }
                    ),
                    self = this;

                if (promise.done === undefined
                    || promise.fail === undefined
                    || promise.always === undefined) {
                    return false;
                }

                promise.done(function (data) {
                    if (data !== undefined && data.status !== undefined) {
                        if (data.status) {
                            // email exists
                            jQuery('#' + Mall.Checkout.steps.address._self_form_id)
                                .validate()
                                .showErrors({
                                    "account[email]":
                                        Mall.translate.__("emailbackend-exits-log-in"
                                            , "Typed address email exists on the site. Please log in to proceed.")
                                });

                            self.validate._checkout.getActiveStep().disable();
                            jQuery('html, body').animate({
                                scrollTop: jQuery(
                                    jQuery('#'
                                        + Mall.Checkout.steps.address._self_form_id)
                                        .validate().errorList[0].element).offset().top
                                    - Mall.getMallHeaderHeight()
                            }, "slow");

                            return false;
                        }
                    }
                    self.validate._checkout.getActiveStep().enable();

                    return true;
                }).fail(function () {
                    /**
                     * @todo implementation. At the moment we do nothing.
                     */
                }).always(function () {
                    // do nothing or implement
                });

                return true;
            },

            validate: {
                _checkout: null,

                init: function () {
                    var self = this;

                    jQuery('#' + Mall.Checkout.steps.address._self_form_id)
                        .validate(Mall.validate.getOptions({
                        ignore: ":hidden",

                        rules: { }
                    }));

                    // validate email address
                    jQuery("#account_email").blur(
                        function () {Mall.Checkout.steps.address.afterEmailValidationAction();});
                }
            }
		},
		
		////////////////////////////////////////////////////////////////////////
		// shipping & payment step
		////////////////////////////////////////////////////////////////////////
        shippingpayment: {
            id: "step-1",
            code: "shippingpayment",
            doSave: true,
            _self_form_id: "co-shippingpayment",
            init: function () {
                this.validate.init();
            },
			onPrepare: function(checkoutObject){
                this.init();
				var self = this;

				this.content.find("form").submit(function(){
                    if (jQuery(this).valid()) {
                        self.submit();
                    }
					return false;
                });
				this.content.find("[id^=step-1-prev]").click(function(){
					checkoutObject.prev();
				});
			},

            collect: function () {
                var shipping = this.content.find("form input[name=shipping]:checked").val();
                if (jQuery.type(shipping) !== "undefined") {
                    var inputs = '';
                    jQuery.each(vendors, function (i, vendor) {
                        inputs += '<input type="hidden" name="shipping_method[' + vendor + ']" value="' + shipping + '" required="required" />';
                    })
                    this.content.find("form .shipping-collect").html(inputs);

                    return this.content.find("form").serializeArray();
                }
                return false;

            },

            validate: {
                init: function () {

                    jQuery('#' + Mall.Checkout.steps.shippingpayment._self_form_id)
                        .validate(Mall.validate.getOptions({
                            errorLabelContainer: "#containererreurtotal",
                            ignore: "",

                            rules: {
                                shipping: {
                                    required: true
                                },
                                'payment[method]': {
                                    required: true
                                }
                            },
                            messages: {
                                shipping: {
                                    required: "Please select shipping"
                                },
                                "payment[method]": {
                                    required: "Please select payment"
                                }
                            },
                            invalidHandler: function (form, validator) {
                                if (!validator.numberOfInvalids()) {
                                    return true;
                                }

                                jQuery('html, body').animate({
                                    scrollTop: 50
                                }, "slow");
                            }
                        }));
                }
            }

        },

		
		////////////////////////////////////////////////////////////////////////
		// review step
		////////////////////////////////////////////////////////////////////////
		review: {
			id: "step-2",
			code: "review",
			onPrepare: function(checkoutObject){
				var self = this;
				this.content.find("[id^=step-2-submit]").click(function(){
					// Add validation
					checkoutObject.placeOrder()
				});
				this.content.find("[id^=step-2-prev]").click(function(){
					checkoutObject.prev();
				});
			}
		},

        getIsObjectKeyExistsInArray: function (key, arr) {
            var exists = false;
            jQuery.each(arr, function (idx, obj) {
                if (obj.name !== undefined && obj.name === key) {
                    exists = true;
                    return true;
                }
            });

            return exists;
        },

        getArrayIndexByObjectKey: function (key, arr) {
            var index = -1;
            jQuery.each(arr, function (idx, obj) {
                if (obj.name !== undefined && obj.name === key) {
                    index = idx;
                    return true;
                }
            });

            return index;
        }
    };
})();

