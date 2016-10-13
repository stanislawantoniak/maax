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
					target = jQuery(".current-address."+type, this.content),
                    defaultAdderssObject,
					addressObject = addressBook.getSelected(type);
					defaultAdderssObject = addressBook.getDefault(type);

				if(addressObject){
					var isDefault = false,
						selectedCaption = Mall.translate.__("selected-" + type + "-address");
				
					if(defaultAdderssObject && defaultAdderssObject.getId()==addressObject.getId()){
						isDefault = true;
					}
					
					var node = jQuery(Mall.replace(
						template, 
						jQuery.extend(this.processAddressToDisplay(addressObject), {
							"selected_caption": selectedCaption
						})
					));
					
					this.processSelectedAddressNode(node, addressObject, addressBook, type);
					
					target.html(node);


					var companyExists = addressObject._data.company && addressObject._data.company.length ? true : false;
					var vatIdExists = addressObject._data.vat_id && addressObject._data.vat_id.length ? true : false;
					var companyAddress = target.find('.companyAddress');

					if(type == "shipping") {
						if(companyExists) {
							companyAddress.show();
						}
					} else if(type == "billing") {
						if(companyExists) {
							target.find('.nameAddress').hide();
							companyAddress.show();
						}
						if(vatIdExists) {
							target.find('.vatIdAddress').show();
						}
					}
					
				}else{
					target.html(Mall.translate.__("no-addresses"));
				}	
			},
			
			renderAddressList: function(type){
				var template = this.getNormalTemplate(),
					addressBook = this.getAddressBook(),
					target = jQuery(".panel-adresses."+type, this.content),
					selectedAddress = addressBook.getSelected(type),
					self = this,
                    addNewButton,
                    addressCollection = addressBook.getAddressBook(),
					caption = jQuery("<div>").css({"margin-top": "15px", "text-transform": "uppercase"});
				
				target.html('');
				
				target.append(caption.text(Mall.translate.__("your-additional-addresses") + ":"));
				
				if(addressCollection.length){
					jQuery.each(addressCollection, function(){
						// Do not allow sleected address
						if(selectedAddress && this.getId()==selectedAddress.getId()){
							return;
						}
						
						var data = self.processAddressToDisplay(this);
						var node = jQuery(Mall.replace(template, data));
						self.processAddressNode(node, this, addressBook, type);


						var companyExists = this._data.company && this._data.company.length ? true : false;
						var vatIdExists = this._data.vat_id && this._data.vat_id.length ? true : false;
						var companyAddress = node.find('.companyAddress');

						if(type == "shipping") {
							if(companyExists) {
								companyAddress.show();
							}
						} else if(type == "billing") {
							if(companyExists) {
								node.find('.nameAddress').hide();
								companyAddress.show();
							}
							if(vatIdExists) {
								node.find('.vatIdAddress').show();
							}
						}

						target.append(node);
					});
                }else{
                    target.html(Mall.translate.__("no-addresses"));
                }
                addNewButton = this.getAddNewButton(type);
                addNewButton.show();
                target.append(addNewButton);
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
			
            attachNewAddressInputsMask: function (modal, type) {
            },
			
            attachNewAddressBootstrapTooltip: function(modal, type) {

                jQuery('#modal-body form').attr('autocomplete', "off");//no autocomplete

                //hint data
                //shoping and billing
                /*jQuery('#shipping_firstname, #billing_firstname').attr('data-original-title', Mall.translate.__("Enter name."));
                jQuery('#shipping_lastname, #billing_lastname').attr('data-original-title', Mall.translate.__("Enter last name."));
                jQuery('#shipping_company, #billing_company').attr('data-original-title', Mall.translate.__("Enter company name."));
                jQuery('#shipping_street_1, #billing_street_1').attr('data-original-title', Mall.translate.__("Enter street and number."));*/
                jQuery('#shipping_postcode, #billing_postcode').attr('data-original-title', Mall.translate.__("Zip-code should be entered in the format xx-xxx."));
                //jQuery('#shipping_city, #billing_city').attr('data-original-title', Mall.translate.__("Enter city name."));
                jQuery('#shipping_telephone, #billing_telephone').attr('data-original-title', Mall.translate.__("Phone number we need only to contact concerning orders for example courier delivering the shipment."));
                jQuery('#shipping_vat_id, #billing_vat_id').attr('data-original-title',Mall.translate.__("Enter tax number"));
                //end hint data

                //visual fix for hints
                /*jQuery('input[type=text],input[type=email],input[type=password],textarea').not('.phone, .zipcode, .nip').tooltip({
                    placement: function(a, element) {
                        var viewport = window.innerWidth;
                        var placement = "right";
                        if (viewport < 960) {
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
                });*/
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

                jQuery('#billing_vat_id, #shipping_vat_id').on('change fucus click keydown keyup', function() {
                    if (jQuery(this).val().length) {
                        jQuery(this).parents('.form-group').removeClass('hide-success-vaild');
                    } else {
                        jQuery(this).parents('.form-group').addClass('hide-success-vaild');
                    }
                });
                // backend zip validate
                jQuery(".postcode").blur(function () {
                    Mall.Checkout.steps.address.afterZipValidationAction(jQuery("[name=postcode]"));
                })
                //end validate
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
                    , Mall.translate.__("firstname")
                    , "col-sm-3"
                    , "form-control firstName required hint"
                    , "");

                formGroup = this.getFormGroup(true);
                formGroup.find(".row").append(element.label).append(element.input);
                panelBody.append(formGroup);

                jQuery.each(this.getNewAddressConfig(type), function (idx, item) {
	                if(item.name == 'vat_id' || item.name == 'telephone' || item.name == 'postcode') {
		                item.type = 'tel';
	                }
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
                    var row = formGroup.find(".row");
                    row.append(element.label).append(element.input);
                    if(item.name == "postcode"){
                        row.find("input").after('<div id="zip-warning" class="checkout-warning"></div>');
                    }

                    panelBody.append(formGroup);
                });

                panelBody.append(this.getSelectButton());

                panelBody.find(".select-address").click(function (e) {
                    e.preventDefault();
                    if (!jQuery(this).parents('form').valid()) {
                        //visual validation fix
                        if (jQuery('#billing_vat_id, #shipping_vat_id').first().val().length) {
                            jQuery('#billing_vat_id, #shipping_vat_id').parents('.form-group').removeClass('hide-success-vaild');
                        } else {
                            jQuery('#billing_vat_id, #shipping_vat_id').parents('.form-group').addClass('hide-success-vaild');
                        }
                        //end fix
                        return;
                    }
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
                            self.renderSelectedAddress("billing");
                            self.renderSelectedAddress("shipping");
                            self.renderAddressList("billing");
                            self.renderAddressList("shipping");
                            self.getModal().modal("hide");
                            self.toggleOpenAddressList(type);
                        }
                    }).always(function () {
                        self.unlockButton(e.target);
						// Save changes (like next btn)
						jQuery.ajax({
							"method": "POST",
							"url": self.content.find("form").attr("action"),
							"data": self.collect()
						});
                    });
                });

                if (type === "billing") {
                    this.injectNeedInvoiceToEditForm(form, null, null);
                }

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
				
				this._rollAddressList(relatedType, relatedBlock, relatedElement.hasClass("open"));
				this._rollAddressList(type, block, element.hasClass("open"));
			},
			
			_rollAddressList: function(type, block, doOpen){
				// Need move to one tag
				
				var contextActions = block.
						siblings(".current-address").
						find(".action");
				
				var element = this.content.find(".change_address." + type);
				
				if(doOpen){
					block.show();
					contextActions.find(".edit").show().addClass("displayed");
					element.addClass("open");
					element.text(Mall.translate.__("roll-up"));
				}else{
					block.hide();
					contextActions.find(".edit").hide().removeClass("displayed");
					element.removeClass("open");
					element.text(Mall.translate.__("change-address"));
				}
				
				this._processActionSelectedAddress(contextActions);
			},
			
			_processActionSelectedAddress: function(block){
				var show = false;
				
				if(block.find(".edit").is(".displayed")){
					show = true;
				}
				if(block.find(".set-default").is(".displayed")){
					show = true;
				}
				
				block[show ? "show" : "hide"]();
			},
			
			processSelectedAddressNode: function(node, address, addressBook, type){
				var settableDefault = true,
					defaultAddress = addressBook.getDefault(type),
					setDefault = node.find(".set-default"),
					edit = node.find(".edit"),
					editable = this.content.find(".change_address."+type).hasClass("open");
			
				if(defaultAddress && defaultAddress.getId()==address.getId()){
					settableDefault = false;
				}
				
				var eventData = {
					addressBook: addressBook, 
					step: this, 
					address: address, 
					type: type
				};

				edit.click(eventData, this.editAddress);
				setDefault.click(eventData, this.setDefaultAddress);
				
				setDefault[settableDefault ? "show" : "hide"]();
				setDefault[settableDefault ? "addClass" : "removeClass"]("displayed");
				edit[editable ? "show" : "hide"]();
				edit[editable ? "addClass" : "removeClass"]("displayed");
				
				this._processActionSelectedAddress(node.find(".action"));
				
				return node;
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

                var deffered,
                    self = this;

                if (!confirm(Mall.translate.__("address-you-sure?"))) {
                    return false;
                }

                deffered = event.data.addressBook.remove(event.data.address.getId());
                if (deffered === null) {
                    alert(Mall.translate.__("address-cant-be-removed"));
                } else {
                    deffered.done(function (data) {
                        if (data.status !== undefined) {
                            if (Boolean(data.status) === false) {
                                alert(Mall.translate.__("address-cant-be-removed"));
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
                event.preventDefault();
                var step = event.data.step,
                    address = event.data.address,
                    addressBook = event.data.addressBook;

                // show modal
                step.showAddNewModal(step.getModal(), event.data.type, true);
                step.getModal().modal("show");
                step.injectEntityIdToEditForm(
                    step.getModal().find("form"), address.getId(), addressBook
                );
                step.fillEditForm(address, step.getModal().find("form"));

				return false;
			},

			setDefaultAddress: function(event){
				var addressBook = event.data.addressBook,
					address = event.data.address,
					type = event.data.type,
					self = event.data.step;
			
				switch(type){
					case "billing":
						addressBook.setDefaultBilling(address);
					break;
					case "shipping":
						addressBook.setDefaultShipping(address);
					break;
				}
				
				addressBook.saveDefault(type).then(function(){
					self.renderSelectedAddress("billing");
					self.renderSelectedAddress("shipping");
					self.renderAddressList("shipping");
					self.renderAddressList("billing");
				})
			
				return false;
			},
			
			chooseAddress: function(event){
				var addressBook = event.data.addressBook,
					address = event.data.address,
					type = event.data.type,
					self = event.data.step;
				
				var success = function(){
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

					// Roll up list
					var listBlock = self.content.find(".panel-adresses." + type);

					self._rollAddressList(type, listBlock, false);
					// Save changes (like next btn)
					jQuery.ajax({
						"method": "POST",
						"url": self.content.find("form").attr("action"),
						"data": self.collect()
					});
				};
				
				// If address has no invoice and is choosed as billing - set need invoice
				if(type=="billing" && address.getData("need_invoice")!="1"){
					address.setData("need_invoice", "1");
					addressBook.save(address).then(success);
				}else{
					success();
				}
				
				return false;
			},

            injectEntityIdToEditForm: function (form, id, addressBook) {
                jQuery("<input/>", {
                    type: "hidden",
                    name: addressBook.getEntityIdKey(),
                    value: id
                }).appendTo(form);
            },

            injectNeedInvoiceToEditForm: function (form) {
                jQuery("<input/>", {
                    type: "hidden",
                    name: "need_invoice",
                    value: 1
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

			getNewsletterAgreement: function() {
				if(this.content.find("input[type=checkbox][name='agreement[newsletter]']").length) {
					return this.content.find("input[type=checkbox][name='agreement[newsletter]']").is(":checked");
				}
				return null;
			},
			
			collect: function(){
				
				var adressBook = this.getAddressBook(),
					billing = adressBook.getSelectedBilling(),
					shipping = adressBook.getSelectedShipping(),
					invoice = adressBook.getNeedInvoice(),
					useBillingForShipping = billing.getId() == shipping.getId(),
					inpost = this.checkout.getInPost(),
					inpostName = inpost.getName(),
					telephoneForLocker = inpost.getTelephoneForLocker(),
					data = [];

				data.push({"name": "form_key", "value": this.getFormKey()});

				if (!inpostName) {
					// No invoice needed same shipping and billing
					if(!invoice){
						billing = shipping;
					}

					data.push({"name": "billing_address_id", "value": billing.getId()});
					data.push({"name": "billing[use_for_shipping]", "value": useBillingForShipping ? 1 : 0});
					
					// Push billing data
					jQuery.each(billing.getData(), function (idx) {
						data.push({name: 'billing[' + idx + "]", value: this});
					});

					if (!useBillingForShipping) {
						// Push shipping address id
						data.push({"name": "shipping_address_id", "value": shipping.getId()});
						// Push shipping data
						jQuery.each(shipping.getData(), function (idx) {
							data.push({name: 'shipping[' + idx + "]", value: this});
						});
					}
				} else {
					if (invoice) {
						// Push billing data
						jQuery.each(billing.getData(), function (idx) {
							data.push({name: 'billing[' + idx + "]", value: this});
						});
						data.push({"name": "billing[use_for_shipping]", "value": 0});
					}
					data.push({name: 'shipping[same_as_billing]', value: invoice ? 0 : 1});
					data.push({name: 'shipping[save_in_address_book]', value: 0});
                    data.push({name: 'shipping[telephone]', value: telephoneForLocker});
                    data.push({name: 'shipping_point_code', value: inpostName});
					data.push({name: 'delivery_point[name]', value: inpostName});
					data.push({name: 'delivery_point[telephone]', value: telephoneForLocker});
				}

				var newsletterAgreement = this.getNewsletterAgreement();
				if(newsletterAgreement !== null) {
					data.push({
						name: "agreement[newsletter]",
						value: newsletterAgreement ? 1 : 0
					});
				}
				
				data.push({name: "method", value: this.checkout.getMethod()});
				
				return data;
			},
			
			onPrepare: function(){
				var self = this;
				this.content.find("form").submit(function(){
                    if (jQuery(this).valid()) {
                        jQuery("button[id*='-prev']").prop("disabled", false);
                        var submit0Button = jQuery(this).find('button[target=step-0-submit]');
                        submit0Button.prop("disabled", true);
                        var i0 = submit0Button.find('i');
                        i0.addClass('fa fa-spinner fa-spin');

                        self.submit();
                    }
					return false;
				});
				this.renderSelectedAddress("shipping");
				this.renderSelectedAddress("billing");
				
				// hide lists
				this.content.find(".panel-adresses").hide();
				
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
				"#shipping_company",
				"#shipping_lastname",
				"#shipping_firstname",
				"#shipping_street",
				"#shipping_postcode",
				"#shipping_city"
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
				this.attachCompanyTriggers();
                // add validation to form
                this.validate.init();

			},
			
			onDisable: function(){
				jQuery("#step-0-submit").prop("disabled", true);
			},
			
			onEnable: function(){
				jQuery("#step-0-submit").prop("disabled", false);
			},

			attachCompanyTriggers: function(){
				var self = this;
				jQuery("#orders_someone_else").change(function(){
					self.handleCompany();
				});
				jQuery("#invoice_vat").change(function(){
					self.handleCompany();
				});
				jQuery("#account_firstname,#account_lastname").
					change(function(){
						self.handleCompany();
					}).
					keyup(function(){
						self.handleCompany();
					})
					
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
				
				Mall.Checkout.steps.address.handleCompany();
				jQuery("#billing_street").val(jQuery("#shipping_street").val());
				jQuery("#billing_postcode").val(jQuery("#shipping_postcode").val());
				jQuery("#billing_city").val(jQuery("#shipping_city").val());

				return this;
			},

			invoiceDisableFields: function (fields) {
				var self = this,
					el;
			
				jQuery.each(fields,  function (idx, item) {
					el = jQuery(item);
					if(el.length){
						jQuery(item.replace("shipping", "billing")).
							prop("readonly", true);
						el.
							bind('change', self.copyHandler).
							bind('keyup', self.copyHandler);
					}
				});
				

				return this;
			},
			

			invoiceEnableFields: function (fields) {
				var self = this,
					el;
				jQuery.each(fields,  function (idx, item) {
					el = jQuery(item);
					if(el.length){
						jQuery(item.replace("shipping", "billing")).
							prop("readonly", false);
						el.
							unbind('change', self.copyHandler).
							unbind('keyup', self.copyHandler);
					}
				});
		
				return this;
			},
			
			
			invoiceClearCopiedFields: function (fields) {
				
				jQuery.each(fields,  function (idx, item) {
					item.replace("shipping", "billing").val("");
				});

				return this;
			},
			
			copyHandler: function(){
				var el = jQuery(this),
					dest = jQuery("#" + el.attr("id").replace("shipping", "billing"));
				
				if(el.is("#shipping_company") || el.is("#shipping_firstname") || el.is("#shipping_lastname")){
					Mall.Checkout.steps.address.handleCompany();
				}else if(dest.length){
					dest.val(el.val());
				}
			},
			
			handleCompany: function(){
				var shippingCompany = jQuery("#shipping_company"),
					billingCompany = jQuery("#billing_company"),
					needInvoice = jQuery("#invoice_vat"),
					invoideDataAddress = jQuery("#invoice_data_address");
			
				if(!needInvoice.is(":checked") || !invoideDataAddress.is(":checked")){
					return;
				}
			
				// Case 1 - company data inserted
				if(shippingCompany.val()){
					billingCompany.val(shippingCompany.val());
				// Case 2 - buy for someone else checked
				}else if(jQuery("#orders_someone_else").is(":checked")){
					billingCompany.val(
						jQuery("#shipping_firstname").val() + 
						' ' + 
						jQuery("#shipping_lastname").val()
					);
				// Case 3- buy for someone else not checked
				}else{
					billingCompany.val(
						jQuery("#account_firstname").val() + 
						' ' + 
						jQuery("#account_lastname").val()
					);
				}
				
				// Finally validate
				billingCompany.valid();
			},


			attachInvoiceCopyShippingDataEvent: function () {
				var self = this;
				jQuery("#invoice_data_address").click(function () {
					if (jQuery(this).is(":checked")) {
						self.invoiceCopyShippingData();
						self.invoiceDisableFields(self._invoice_copy_shipping_fields);
                        self.setSameAsBilling(1);
					} else {
						//self.invoiceClearCopiedFields(self._invoice_copy_shipping_fields);
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
				var needInvoice = jQuery("#invoice_vat");
				var sameData = jQuery("#invoice_data_address");
		
				needInvoice.click(function () {
					if (sameData.is(":checked")) {
						self.invoiceCopyShippingData();
						self.invoiceDisableFields(self._invoice_copy_shipping_fields);
                        self.setSameAsBilling(1);
					}
				});
				
				// Need invoice and same data
				//jQuery("#shipping_company").change(function(){
				//	if(needInvoice.is(":checked") && sameData.is(":checked")){
				//		jQuery("#billing_company").valid();
				//	}
				//})
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
                        jQuery("button[id*='-prev']").prop("disabled", false);
                        var submitNotLoggedButton = jQuery(this).find('button[target=step-0-submit]');
                        submitNotLoggedButton.prop("disabled", true);
                        var iNotLogged = submitNotLoggedButton.find('i');
                        iNotLogged.addClass('fa fa-spinner fa-spin');

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
					selector,
                    value;
				jQuery.each(this._billing_names, function (idx, item) {
					selector = item.replace("billing", "shipping");
                    value = jQuery("[name='"+ selector +"']").val();
                    if (item == "billing[postcode]") {
                        value = Mall.postcodeTransform(value);
                    }
					billingData.push({
						name: item,
						value: value
					});

				});

				return billingData;
			},



			collect: function () {
				var form = jQuery("#co-address"),
					password,
					billingData,
					stepData = [],
					accountTelephone,
					inpost = this.checkout.getInPost(),
					inpostName = inpost.getName(),
					telephoneForLocker = inpost.getTelephoneForLocker();

                if (!parseInt(jQuery("#customer_logged_in").val(), 10)) {
                    password = form.find("#account_password").val();
                    // set password confirmation
                    if (password.length > 0) {
                        form.find("#account_confirmation").val(password);
                    }
                }

				// set billing data
				if (jQuery("input[name='shipping[different_shipping_address]']").is(":checked")) {
					form.find("input[name='billing[firstname]']").val(form.find("input[name='shipping[firstname]']").val());
					form.find("input[name='billing[lastname]']").val(form.find("input[name='shipping[lastname]']").val());
				} else {
					form.find("input[name='billing[firstname]']").val(form.find("input[name='account[firstname]']").val());
					form.find("input[name='billing[lastname]']").val(form.find("input[name='account[lastname]']").val());
					form.find("input[name='shipping[firstname]']").val(form.find("input[name='account[firstname]']").val());
					form.find("input[name='shipping[lastname]']").val(form.find("input[name='account[lastname]']").val());
					if (!inpostName) {
						form.find("input[name='shipping[telephone]']").val(form.find("input[name='account[telephone]']").val());
					} else {
						form.find("input[name='shipping[telephone]']").val(form.find("input[name='inpost[telephone]']").val());
					}
				}

				// copy phone
				accountTelephone = form.find("input[name='account[telephone]']").val();
				form.find("input[name='billing[telephone]']").val(accountTelephone);

				//use_for_shipping
				if (!form.find("input[name='billing[need_invoice]']").is(":checked")) { // if is not visible
					form.find("[name='billing[use_for_shipping]']").val(1);
				} else { // is visible
					if (form.find('input[name=invoice_data_address]').is(':checked')) { // and checked
						form.find("[name='billing[use_for_shipping]']").val(1);
					} else {// is not checked
						form.find("[name='billing[use_for_shipping]']").val(0);
					}
				}

				stepData = form.serializeArray();
				// fill billing data with shipping
				if (!this.getIsNeedInvoice() && !inpostName) {
					billingData = this.getBillingFromShipping();
					stepData = this.mergeArraysOfObjects(stepData, billingData);
				}

				// Push method
				stepData.push({name: "method", value: this.checkout.getMethod()});

				return stepData;
			},
			
			_extractAddressObject: function(type){
				var obj = {},
					reg = new RegExp("^" + type + "\\[([a-z_]+)\\]"),
					res;
				jQuery.each(this.collect(), function(){
					res = reg.exec(this.name);
					if(res){
						obj[res[1]] = this.value;
					}
				});
				return obj;
			},
			
			getBillingAddress: function(){
				return this._extractAddressObject("billing");
			},
			
			getShippingAddress: function(){
				return this._extractAddressObject("shipping");
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

			getNewsletterAgreementContainer: function() {
				return jQuery("#newsletter_agreement_container");
			},

			hideNewsletterAgreement: function() {
				var container = this.getNewsletterAgreementContainer();
				container.find("[name='agreement[newsletter]']").attr('disabled',true);
				container.hide();
			},

			showNewsletterAgreement: function() {
				var container = this.getNewsletterAgreementContainer();
				container.find("[name='agreement[newsletter]']").attr('disabled',false);
				container.show();
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
                        if(data.subscribed) {
                            self.hideNewsletterAgreement();
                        } else {
                            self.showNewsletterAgreement();
                        }
                        if (data.status) {
                            // email exists
                            jQuery('#' + Mall.Checkout.steps.address._self_form_id)
                                .validate()
                                .showErrors({
                                    "account[email]":
                                        Mall.translate.__("emailbackend-exits-log-in"
                                            , "We already have an account with this address. Please <a href='customer/account/login/'>log in</a> to your account.")
                                });

                            jQuery('html, body').animate({
                                scrollTop: jQuery(
                                    jQuery('#'
                                        + Mall.Checkout.steps.address._self_form_id)
                                        .validate().errorList[0].element).offset().top
                                    - Mall.getMallHeaderHeight()
                            }, "slow");

                            self.validate._checkout.getActiveStep().disable();
                            return false;
                        } else {
                            self.validate._checkout.getActiveStep().enable();
                            return true;
                        }
                    }

                }).fail(function () {
                    /**
                     * @todo implementation. At the moment we do nothing.
                     */
                }).always(function () {
                    // do nothing or implement
                });

                return true;
            },

            afterZipValidationAction: function (field) {

                var zipEntered = field.val();

                if (jQuery(".zipcode").valid()) {
                    //send backend zip checking
                    jQuery.ajax({
                        url: Config.url.zip_validate,
                        data: {zip: zipEntered, country: jQuery("[name='billing[country_id]']").val()}
                    }).done(function (data) {
                        jQuery("#zip-warning").empty();
                        if (!data.status) {
                            // not valid zip - show warning
                            Mall.Checkout.prototype.showWarning(jQuery("#zip-warning"), Mall.translate.__('warning-wrong-zip'));
                        }
                    })
                } else {
                    jQuery("#zip-warning").empty();
                }

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

                    // backend zip validate
                    jQuery(".zipcode").blur(function () {
                        Mall.Checkout.steps.address.afterZipValidationAction(jQuery("[name='shipping[postcode]']"));
                    })



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
			_sidebarAddressesTemplate: "",
			_sidebarDeliverypaymentTemplate: "",
	        _previous_payment: false,
	        _previous_provider: false,
	        _payment_is_dotpay: false,
			
            handleChangePaymentMethodClick: function (e) {

                var view_block_default_pay = jQuery('.default_pay > .panel > .panel-body > .panel');
                var form_group_default_pay = jQuery('.default_pay');

                view_block_default_pay.toggle();
                var ifPanelClosed = jQuery(e.target).closest('.panel').children('.panel-body').find('.panel-default').is(':visible');
                var txt = ifPanelClosed ? Mall.translate.__('cancel-changes') : Mall.translate.__('select-payment-type');

                jQuery(".default_pay .panel").removeClass("payment-selected");
                jQuery('.selected_bank').hide();

                if(!ifPanelClosed){
                    jQuery("input[name='payment[method]']").prop("checked",false);
                    jQuery("input[name='payment[additional_information][provider]']").prop("checked",false);
                }
                form_group_default_pay.closest('.row').css({marginBottom: '15px'});
                jQuery(e.target).text(txt);

	            if(jQuery(window).width() < 977) {
		            var animationSpeed = 600;
		            var htmlBody = jQuery("html, body");
		            if (!ifPanelClosed) {
			            htmlBody.animate({scrollTop: jQuery('.default_pay .top-panel').offset().top - 130}, animationSpeed);
		            } else {
			            var offset = jQuery(window).height() < 750 ? 140 : 100;
			            htmlBody.animate({scrollTop: jQuery('.css-radio.payment-method').first().offset().top + offset}, animationSpeed);
		            }
	            }

	            if(!jQuery('.selected-payment div.panel.panel-default div.panel.panel-default').is(':visible')) { //if panel is closed
		            var payment = jQuery('input[name=payment_emul]'), provider = jQuery('input[name=payment_provider_emul]');
		            if(!payment.val() && this._previous_payment) {
			            payment.val(this._previous_payment);
			            if(payment.val() == 'zolagopayment_gateway' || payment.val() == 'zolagopayment_cc') {
				            provider.val(this._previous_provider);
			            }
			            this._previous_payment = false;
			            this._previous_provider = false;
						jQuery('#'+this._self_form_id).valid();
		            }
	            }

            },

            renderPaymentSelected: function (paymentMethod, providerText, imgUrl) {
                var selectedMethodContainer = jQuery(".default_pay .top-panel .row:first-child");
                selectedMethodContainer.find("dl dt").html(paymentMethod);
                if(jQuery.type(providerText) !== 'undefined'){
                    selectedMethodContainer.find("dl dd#bank-name").html(providerText);
                } else {
                    selectedMethodContainer.find("dl dd#bank-name").html("");
                }


                var logo = jQuery('<img />');
                logo.attr('src', imgUrl);

                selectedMethodContainer.find("figure").find("div").html(logo);
                jQuery(".default_pay .top-panel").show();
            },

            renderSupportedBySelected: function (label, imgUrl) {
                var selectedMethodContainer = jQuery(".default_pay .top-panel .row:nth-child(2)");
                if (imgUrl) {
                    var logo = jQuery('<img />');
                    logo.attr('src', imgUrl);
                    selectedMethodContainer.find("dl dd#payment-provider span[target='provider-label']").html(label);
                    selectedMethodContainer.find("dl dd#payment-provider span[target='provider-img']").html(logo);
                } else {
                    selectedMethodContainer.find("dl dd#payment-provider span[target='provider-label']").html("");
                    selectedMethodContainer.find("dl dd#payment-provider span[target='provider-img']").html("");
                }
            },
            setEmulatedValues: function(){
                var m, p;
                m = jQuery("input[name='payment[method]']:checked").val();

                p = (typeof(jQuery("input[name='payment[additional_information][provider]']:checked").val()) !== "undefined") ? jQuery("input[name='payment[additional_information][provider]']:checked").val() : "";

                jQuery("input[name='payment_emul']").val(m);
                jQuery("input[name='payment_provider_emul']").val(p);

	            this._previous_payment = false;
	            this._previous_provider = false;

                jQuery("input[name='payment[method]']").prop("checked",false);
                jQuery("input[name='payment[additional_information][provider]']").prop("checked",false);

	            jQuery("#"+this._self_form_id).valid();

                jQuery('.selected_bank').hide();
            },

            handleSelectPaymentMethod: function (e) {
                var self = this;

                if (jQuery(e.target).is(":not(:checked)")) {
                    return;
                }

                var paymentMemberName = jQuery(e.target).attr("name");
                var paymentMethodNameAttr = "payment[method]";
                var paymentMethodProviderNameAttr = "payment[additional_information][provider]";

                var paymentMethodName;

                if (paymentMemberName === paymentMethodNameAttr) {
                    var paymentMethodCode = jQuery(e.target).val();
	                Mall.Checkout.steps.shippingpayment._payment_is_dotpay = false;


                    if (!(paymentMethodCode === "zolagopayment_gateway" || paymentMethodCode === "zolagopayment_cc")) {
                        paymentMethodName = jQuery(e.target).data("payment-method");

                        //replace
                        var methodLogoUrl = jQuery(e.target).closest('.form-group').find('label img').attr("src");
                        var paymentDescription = jQuery(e.target).closest('.form-group').find(".payment-description").html();


                        self.renderPaymentSelected(paymentMethodName,paymentDescription, methodLogoUrl);
                        self.renderSupportedBySelected("");

                        //close payment selector widget
                        self.setEmulatedValues();

                        jQuery('#view_default_pay').trigger("click");
                    }


                } else if (paymentMemberName === paymentMethodProviderNameAttr) {
                    paymentMethodName = jQuery(e.target).closest('.panel.payment-selected').find('input[name="payment[method]"]').data("payment-method");
                    var providerName = jQuery(e.target).data("bank-name");
                    var bankLogoUrl = jQuery(e.target).closest('.provider-item').find('.payment-provider-logo-wrapper img').attr("src");
	                Mall.Checkout.steps.shippingpayment._payment_is_dotpay = true;

                    //replace
                    self.renderPaymentSelected(paymentMethodName,providerName,bankLogoUrl);


                    var supportedByLogoUrl = jQuery(e.target).data("service-provider-icon");
                    self.renderSupportedBySelected(Mall.translate.__('payment-supported-by'), supportedByLogoUrl);


                    //close payment selector widget
                    self.setEmulatedValues();


                    jQuery('#view_default_pay').trigger("click");

                }


            },

			onPrepare: function(checkoutObject){
				this._sidebarAddressesTemplate = this.getSidebarAddresses().html();
				this._sidebarDeliverypaymentTemplate = this.getSidebarDeliverypayment().html();
				var self = this;

				this.validate.init();

				this.content.find("form").submit(function(){
                    if (jQuery(this).valid()) {
                        jQuery("button[id*='-prev']").prop("disabled", false);
                        var submitButton = jQuery(this).find('button[id=step-1-submit]');
                        submitButton.prop("disabled", true);
                        var i = submitButton.find('i');
                        i.addClass('fa fa-spinner fa-spin');

                        self.submit();
                    }
					return false;
                });

                this.content.find("#view_default_pay").on('click', function (e) {
                    self.handleChangePaymentMethodClick(e);
                    return false;
                });

				// Save changes (like next btn)
				jQuery(document).on('change', 'input[name*="payment"]', function (e) {
					if (jQuery("#co-shippingpayment").validate().checkForm() && self.checkout.getActiveStep().code == 'shippingpayment') {
						jQuery.ajax({
							"method": "POST",
							"url": self.content.find("form").attr("action"),
							"data": self.collect()
						});
					}
				});

                //////////////////////
                var default_pay_bank = jQuery('.default_pay input[name="payment[method]"]');
                //jQuery('body').find('.default_pay input[name="payment[method]"]:checked').closest('.panel').addClass('payment-selected');

                jQuery(default_pay_bank).on('change', function(){
	                var me = jQuery(this);
                    jQuery(default_pay_bank).closest('.panel').removeClass('payment-selected');
                    me.closest('.panel').addClass('payment-selected');
                    jQuery('.selected_bank').hide();
                    if(me.is(":checked")) {
	                    jQuery("#"+Mall.Checkout.steps.shippingpayment._self_form_id).valid();
	                    var selectedBank = me.closest('.form-group').next('.selected_bank');
	                    if(selectedBank.length) {
		                    selectedBank.show();
		                    if(jQuery(window).width() < 977) {
			                    var offsetWithoutHeader = -35;
			                    var offsetWithHeader = -95;
			                    var scrollTo = selectedBank.prev('div').find('.label-wrapper').offset().top;
			                    var scrollFrom = jQuery(document).scrollTop();
			                    if ((scrollTo < scrollFrom && jQuery(window).height() < 750) || jQuery(window).height() >= 750) {
				                    scrollTo += offsetWithHeader;
			                    } else {
				                    scrollTo += offsetWithoutHeader;
			                    }
			                    jQuery("html, body").animate({scrollTop: scrollTo}, 600);
		                    }
	                    }
                    }
	                if(me.is(':visible')) {
		                var val = me.val();
		                if ((val == "zolagopayment_cc" || val == "zolagopayment_gateway") && !self._previous_payment) {
			                var payment = jQuery('input[name=payment_emul]'), provider = jQuery('input[name=payment_provider_emul]');
			                self._previous_payment = payment.val();
			                self._previous_provider = provider.val();
			                payment.val('');
			                provider.val('');
		                }
	                }
                });
                /////////////////////

                // Handle payment select
                var view_block_default_payS = jQuery('.checkout-singlepage-index .default_pay > .panel > .panel-body > .panel');
                view_block_default_payS.toggle(); //close panels first time
                var paymentMethod = this.content.find("input[name='payment[method]'],input[name='payment[additional_information][provider]']");
                paymentMethod.change(function(e){
                    self.handleSelectPaymentMethod(e);
                }).change();

				this.content.find("#step-1-prev").click(function(){
                    jQuery("button[id$='-submit'],button[target$='-submit']").prop("disabled", false);
                    jQuery("button[id*='-prev']").prop("disabled", false);
                    //jQuery(this).prop("disabled", true);
                    jQuery("i.fa-spinner:not(.popup-spinner)").removeClass('fa fa-spinner fa-spin');

					checkoutObject.prev();
					jQuery(window).trigger("resize");
					return false;
				});

			},
			
			onEnter: function(checkout){
				var addresses = checkout.getBillingAndShipping();
				checkout.prepareAddressSidebar(
					addresses.billing, 
					addresses.shipping, 
					this.getSidebarAddresses(), 
					this.getSidebarAddressesTemplate()
				);
				// Prepare delivery payment sidebar
				var deliverypayment = checkout.getDeliveryAndPayment();
				var inpostData = checkout.getInPostData();
				jQuery().extend(deliverypayment, inpostData);
				checkout.prepareDeliverypaymentSidebar(
					deliverypayment,
					this.getSidebarDeliverypayment(),
					this.getSidebarDeliverypaymentTemplate()
				);
				jQuery(window).trigger("resize");
				Mall.Gtm.checkoutStep(Mall.Gtm.STEP_CHECKOUT_SHIPPING_PAYMENT);
			},

            collect: function () {
                var shipping = this.content.find("form input[name=_shipping_method]:checked").val();
                var _shipping_point_code = this.content.find("form input[name=_shipping_point_code]").val();
                if (jQuery.type(shipping) !== "undefined") {
                    var inputs = '';

                    jQuery.each(this.getVendors(), function (i, vendor) {
                        inputs += '<input type="hidden" name="shipping_method[' + vendor + ']" value="' + shipping + '" required="required" />';
                    });
                    if (jQuery.type(_shipping_point_code) !== "undefined") {
                        inputs += '<input type="hidden" name="shipping_point_code" value="' + _shipping_point_code + '"  />';
                    }
                    this.content.find("form .shipping-collect").html(inputs);

                    var pInputs = '';
                    pInputs += '<input type="hidden" name="payment[method]" value="' + jQuery("input[name='payment_emul']").val() + '" required="required" />';
                    pInputs += '<input type="hidden" name="payment[additional_information][provider]" value="' + jQuery("input[name='payment_provider_emul']").val() + '" />';
                    this.content.find("form .payment-collect").html(pInputs);

                    return this.content.find("form").serializeArray();
                }
                return false;

            },
			getVendors: function(){
				return Mall.reg.get("vendors");
			},
			getVendorCosts: function(){
				return Mall.reg.get("vendor_costs");
			},
            getCODExtraCharges: function(){
                return Mall.reg.get("extra_charges");
            },
			getSidebarAddresses: function(){
				return this.content.find(".sidebar-addresses");
			},
			
			getSidebarAddressesTemplate: function(){
				return this._sidebarAddressesTemplate;
			},

			getSidebarDeliverypayment: function(){
				return this.content.find(".sidebar-deliverypayment");
			},

			getSidebarDeliverypaymentTemplate: function(){
				return this._sidebarDeliverypaymentTemplate;
			},
			
			getSelectedShipping: function(){
				return this.content.find(".shipping-method:radio:checked");
			},
			
			getSelectedPayment: function(){
				//return this.content.find(".payment-method:radio:checked");
                return this.content.find("input[type=hidden][name='payment[method]']").val();
			},
			
			getSelectedBank: function(){
                return this.content.find("input[type=hidden][name='payment[additional_information][provider]']").val();
			},
			
			getCarrierName: function(){
				return this.getSelectedShipping().data("carrierName");
			},
			
			getCarrierMethod: function(){
				return this.getSelectedShipping().data("carrierMethod");
			},
			
			getMethodCode: function(){
				return this.getSelectedShipping().val();
			},
			getMethodCost: function(){
				return this.getSelectedShipping().data("methodCost");
			},
			
			getPaymentMethod: function(){
                var selectedPaymentSource = this.content.find("input:radio[name='payment[method]'][value='"+this.getSelectedPayment()+"']");
                if(selectedPaymentSource.length){
                    return selectedPaymentSource.data("paymentMethod");
                }
				return null;
			},
			
			getCheckoutReviewInfo: function (){
				if (this.getSelectedPayment() == 'cashondelivery') {
					return jQuery("#checkout-review-info-cod").html();
				} else {	
					return jQuery("#checkout-review-info").html();
				}
			},
			getCostForVendor: function(vendorId, methodCode){
				var costs = this.getVendorCosts();
				if(typeof costs == "object" && 
					typeof costs[vendorId] == "object" && 
					typeof costs[vendorId][methodCode] != "undefined"){
					return costs[vendorId][methodCode];
				}
				return null;
			},

            getCODExtraChargeForVendor: function (methodCode, selectedPayment) {
                if (selectedPayment !== 'cashondelivery')
                    return null;

                var codExtraCharges = this.getCODExtraCharges();
                if (typeof codExtraCharges == "object" &&
                    typeof codExtraCharges == "object" &&
                    typeof codExtraCharges[methodCode] != "undefined") {
                    return codExtraCharges[methodCode];
                }
                return null;
            },
            getProvidersData: function () {
                var selectedPayment = this.getSelectedPayment();

                if (selectedPayment === 'zolagopayment_gateway' || selectedPayment === 'zolagopayment_cc') {
                    var bank = this.getSelectedBank();
                    if (bank.length) {
                        var bankDataSource = this.content.find("input:radio[name='payment[additional_information][provider]'][value='" + bank + "']");

                        if (bankDataSource.length) {
	                        var selectedPaymentImgSrc = jQuery('.default_pay_selected_bank').find('img').attr('src');
                            return '<span>' + bankDataSource.data("bankName") + '</span>' +
	                            '<img src="' + selectedPaymentImgSrc + '" alt="' + bankDataSource.data("bankName") + '" />';
                            //return ;
                        }
                    }
                }
                return null;
            },

            hasProviders: function(){
                var selectedPayment = this.getSelectedPayment();
                if (selectedPayment === 'zolagopayment_gateway' || selectedPayment === 'zolagopayment_cc') {
                    return true;
                }
                return null;
			},
			

            validate: {
                init: function () {
                    jQuery('#' + Mall.Checkout.steps.shippingpayment._self_form_id)
                        .validate({
                            //errorLabelContainer: "#containererreurtotal",
                            ignore: "",

                            rules: {
	                            '_shipping_method': {
									required: true
	                            },
                                'payment_emul': {
                                    required: true
                                }
                            },
                            messages: {
                                _shipping_method: {
                                    required: Mall.translate.__("please-select-shipping")
                                },
	                            payment_emul: {
                                    required: function() {
	                                    var payment_method = jQuery('input[name="payment[method]"]:checked');
	                                    if(payment_method.length) {
		                                    if(payment_method.val() == "zolagopayment_gateway") {
			                                    return Mall.translate.__("please-select-bank");
		                                    } else {
			                                    return Mall.translate.__("please-select-card");
		                                    }
	                                    } else {
		                                    return Mall.translate.__("please-select-payment");
	                                    }
                                    }
                                }
                            },
                            invalidHandler: function (form, validator) {
                                if (!validator.numberOfInvalids()) {
                                    return true;
                                }

                                var firstErrorElement = jQuery('#'  + Mall.Checkout.steps.shippingpayment._self_form_id).validate().errorList[0].element;
                                var scroll = jQuery(firstErrorElement).closest("fieldset").find('.data-validate').offset().top - 100;

                                jQuery('html, body').animate({
                                    scrollTop: scroll
                                }, "slow");
                            },
                            errorPlacement: function(error, element) {
                                jQuery(element).closest("fieldset").find('.data-validate').append(error);
                            }
                        });
                }
            }

        },

		////////////////////////////////////////////////////////////////////////
		// review step
		////////////////////////////////////////////////////////////////////////
		review: {
			id: "step-2",
			code: "review",
			_sidebarAddressesTemplate: "",
			_sidebarDeliverypaymentTemplate: "",
			_reviewInfoTemplate: "",
			onPrepare: function(checkoutObject){
				this._sidebarAddressesTemplate = this.getSidebarAddresses().html();
				this._sidebarDeliverypaymentTemplate = this.getSidebarDeliverypayment().html();
				this._reviewInfoTemplate = this.getReviewInfo().html();
                jQuery('.checkout_agreement_vendors').tooltip();
				this.content.find("[id^=step-2-submit]").click(function(){
					var checkout_agreements = jQuery('.checkout_agreements'),
						checkout_agreements_mobile = jQuery('.checkout_agreements_mobile'),
						checkout_agreements_to_check = checkout_agreements.is(':visible') ? checkout_agreements : checkout_agreements_mobile;

					if(!checkout_agreements_to_check.find('form').valid()) {
						return;
					}

                    jQuery("button[id*='-prev']").prop("disabled", false);

                    //disable prev buttons
                    jQuery("#step-2-prev").prop("disabled", true);
                    jQuery("#zmiana_zawartosci_koszyka").prop("disabled", true);
                    jQuery(".prev-button-address").prop("disabled", true);
                    jQuery(".prev-button-deliverypaymnet").prop("disabled", true);


                    var submit2Button = jQuery(this);
                    submit2Button.prop("disabled", true);
                    var i2 = submit2Button.find('i');
                    i2.addClass('fa fa-spinner fa-spin');

					// Add validation
					checkoutObject.placeOrder()
				});
				this.content.find("[id^=step-2-prev]").click(function(){
                    jQuery("button[id$='-submit'],button[target$='-submit']").prop("disabled", false);
                    jQuery("button[id*='-prev']").prop("disabled", false);
                   // jQuery(this).prop("disabled", true);
                    jQuery("i.fa-spinner:not(.popup-spinner)").removeClass('fa fa-spinner fa-spin');

					checkoutObject.prev();
					if(jQuery('.default_pay.selected-payment').find('.panel.panel-default').find('.panel-body').find('.panel').is(':visible')) {
						jQuery('#view_default_pay').trigger('click');
						jQuery('html,body').scrollTop(0);
					}
				});
			},
			
			onEnter: function(checkout){
				Mall.Gtm.checkoutStep(Mall.Gtm.STEP_CHECKOUT_SUMMARY);
				// Prepare address sidebar
				var addresses = checkout.getBillingAndShipping();
				checkout.prepareAddressSidebar(
					addresses.billing, 
					addresses.shipping, 
					this.getSidebarAddresses(), 
					this.getSidebarAddressesTemplate()
				);
		
				// Prepare delivery payment sidebar
				var deliverypayment = checkout.getDeliveryAndPayment();
				var inpostData = checkout.getInPostData();
				jQuery().extend(deliverypayment, inpostData);
				checkout.prepareDeliverypaymentSidebar(
					deliverypayment,
					this.getSidebarDeliverypayment(), 
					this.getSidebarDeliverypaymentTemplate()
				);
				var textpotwierdzenie = checkout.getReviewInfo();
				checkout.prepareReviewInfo(
					textpotwierdzenie,
					this.getReviewInfo(),
					this.getReviewInfoTemplate()
				);
				this._prepareTotals(checkout);

				// Prepare dotpay agreement
				var dotpayAgreement = jQuery(".dotpay_agreement");

				dotpayAgreement.each(function() {
					var dotpayAgreementContainer = jQuery(this).parent(),
						current = jQuery(this);
					if(Mall.Checkout.steps.shippingpayment._payment_is_dotpay) {
						dotpayAgreementContainer.show();
						current.prop('disabled',false);
					} else {
						dotpayAgreementContainer.hide();
						current.prop('disabled',true);
					}
				});

				//prepare last step agreements validation
				var form = jQuery('.checkout_agreements').find('form'),
					form_mobile = jQuery('.checkout_agreements_mobile').find('form');

				form.find('.has-feedback').each(function() {
					var elemToCleanup = jQuery(this);
					elemToCleanup.removeClass('has-feedback has-error has-success');
					elemToCleanup.find('.error').remove();
					elemToCleanup.find('i').remove();
				});

				form_mobile.find('.has-feedback').each(function() {
					var elemToCleanup = jQuery(this);
					elemToCleanup.removeClass('has-feedback has-error has-success');
					elemToCleanup.find('.error').remove();
					elemToCleanup.find('i').remove();
				});

				Mall.validate.init();
				form.validate(Mall.validate._default_validation_options);
				form_mobile.validate(Mall.validate._default_validation_options);
			},

			_prepareTotals: function(checkout){
				var subTotal = 0,
					shippingTotal = 0,
					discountTotal = 0,
					deliverypayment = checkout.getStepByCode("shippingpayment"),
					selectedMethod = deliverypayment.getMethodCode(),
					discountObject = this.content.find(".total_discount"),
                    selectedPayment = deliverypayment.getSelectedPayment();
			
				discountTotal = discountObject.length ? 
					parseFloat(discountObject.data('price')) * -1 : 0;
			
				// Prepare costs for vendors and totals
              var vendorCODExtraCharge = deliverypayment.getCODExtraChargeForVendor(selectedMethod, selectedPayment);
				this.content.find(".panel-vendor.panel-footer").each(function(){
					var el = jQuery(this);
					var vendorId = el.data("vendorId");
					var vendorSubtotal = parseFloat(el.find(".vendor_subtotal").data("price"));
					var vendorShipping = deliverypayment.getCostForVendor(vendorId, selectedMethod);

					if(vendorShipping!==null){
						shippingTotal += vendorShipping;
					}
					subTotal += vendorSubtotal;
					
					el.find(".vendor_delivery").html(vendorShipping!==null ? Mall.currency(vendorShipping + vendorCODExtraCharge) : "N/A");
					
				});
				
				this.content.find(".total_shipping").html(Mall.currency(shippingTotal + vendorCODExtraCharge));
				this.content.find(".total_value").html(
						Mall.currency(shippingTotal + subTotal + discountTotal + vendorCODExtraCharge)
				);
			},
			
			getSidebarAddresses: function(){
				return this.content.find(".sidebar-addresses");
			},
			
			getSidebarAddressesTemplate: function(){
				return this._sidebarAddressesTemplate;
			},
			
			getSidebarDeliverypayment: function(){
				return this.content.find(".sidebar-deliverypayment");
			},
			
			getSidebarDeliverypaymentTemplate: function(){
				return this._sidebarDeliverypaymentTemplate;
			},
			getReviewInfoTemplate: function() {	
				return this._reviewInfoTemplate;
			},
			getReviewInfo: function() {
				return this.content.find(".text-potwierdzenie");
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

