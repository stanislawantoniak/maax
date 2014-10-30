jQuery(function($){
	var _rma = {
		step2: $("#step-2"),
		newRma: $("#new-rma"),
		validation: null,
		returnReasons: [],

		notAvailableText: "Not available",
		ignoreUnload: 0,
		daysOfWeek: [],
		txtReason: "",
		txtComment: "",
		txtCarrierTime: "",
		_txtCarrierTimeFrom: "carrier_time_from",
		_txtCarrierTimeTo: "carrier_time_to",
	
		
		////////////////////////////////////////////////////////////////////////
		// Init steps and general
		////////////////////////////////////////////////////////////////////////
		init: function () {
			"use strict";
			this._init();
//
			// Fix footer
			jQuery(window).resize();
		},
		
		// Internal init
		_init: function(){
			var self = this;

			this.validation = this.newRma.validate(
				Mall.validate.getOptions()
			);

            this._initStep2();


            //visual fix for message - can't be done by css
            if ($('.messages i').length) {
                $('#content').css('margin-top', '0px');
                $('.messages i').click(function () {
                    $('#content').css('margin-top', '');
                });
            }
		},

        _submitForm: function() {
            $(window).unbind('beforeunload');
            $('#new-rma').submit();
        },
		
        // Step 2 init
        _initStep2: function(){
            var s = this.step2,
                self = this,
                next = s.find("button.next");


            // Handle next click
            s.find(".next").click(function(){
                var valid = true,
	                from = s.find('input[name="rma[carrier_time_from]"]').val().split(":")[0],
		            to = s.find('input[name="rma[carrier_time_to]"]').val().split(":")[0];

	            //check if those blocks are displayed
	            if($('#pickup-address-form') && $('#pickup-date-form')) {
	            //validate if user has chosen pickup date
		            if (!s.find('input[name="rma[carrier_date]"]:checked').length) {
		                valid = false;
	                }

	            }


                //--validation
                if(valid){
                    console.log("Save RMA saveRmaCourier");
                    self._submitForm();
                }
                return false;
            });

            //PICKUP DATE AND HOURS START
            Object.size = function(obj) {
                var size = 0, key;
                for (key in obj) {
                    if (obj.hasOwnProperty(key)) size++;
                }
                return size;
            };

            jQuery(document).ready( function() {
                //INIT DATE LIST
                if (Object.size(dataList) == 0) {
                    jQuery('#btn-next-step-2').hide();
                } else {
                    for(var day in dataList) {
                        jQuery('#carrier_date_' + day).attr('data-PickupFrom', _rma.round(dataList[day].getPostalCodeServicesResult.drPickupFrom, 'up') );
                        jQuery('#carrier_date_' + day).attr('data-PickupTo', _rma.round(dataList[day].getPostalCodeServicesResult.drPickupTo, 'down') );
                    }
                }
                //INIT DATE LIST END

                //INIT SLIDER DEFAULT VALUES AND PARAMS
                if (Object.size(dataList) != 0) {
                    jQuery("#slider-range").noUiSlider({
                        start: [660, 840],
                        step: 60,
                        behaviour: 'drag-fixed',
                        connect: true,
                        range: {
                            'min': 540,
                            'max': 1200
                        }
                    });
                }
                //INIT SLIDER DEFAULT VALUES AND PARAMS END

                //CHANGE DESCRIPTIONS ON SLIDER SLIDE
                jQuery("#slider-range").on({
                    slide: function() {
                        var values = jQuery(this).val();
                        var from = values[0];
                        var to = values[1];
                        _rma.formatTimeRange(from, to);

                        var minutes0 = parseInt(from % 60, 10),
                            hours0 = parseInt(from / 60 % 24, 10),
                            minutes1 = parseInt(to % 60, 10),
                            hours1 = parseInt(to / 60 % 24, 10);

                        var startTime = _rma.getTime(hours0, minutes0);
                        var endTime = _rma.getTime(hours1, minutes1);

                        jQuery('#pickup-time-from').text(startTime);
                        jQuery('#pickup-time-to').text(endTime);
                    }
                });
                //CHANGE DESCRIPTIONS ON SLIDER SLIDE END

                //SET SLIDER, SAVE PICKUP TIME, WRITE MESSAGES
                jQuery('#pickup-date-form-panel input').click(function() {
                    var _from =  jQuery(this).attr('data-PickupFrom');
                    var _to =  jQuery(this).attr('data-PickupTo');

                    var from = parseInt(jQuery(this).attr('data-PickupFrom'))*60;
                    var to = parseInt(jQuery(this).attr('data-Pickupto'))*60;

                    if( (to - from) <= (3*60) ) {

                        jQuery("#slider-range").noUiSlider({
                            start: [from, to],
                            range: {
                                'min': from,
                                'max': to
                            }
                        }, true);
                        var values = jQuery("#slider-range").val();
                        _rma.formatTimeRange(values[0], values[1]);
                        jQuery('#pickup-time').html(Mall.translate.__("For your address is only available time interval") +
                        ': <br>&nbsp;<br>' + Mall.translate.__("between the hours") +
                        '<span id=pickup-time-from>' + _from + '</span> ' + Mall.translate.__("and") +
                        ' <span id=pickup-time-to>' + _to + '</span>');

                        jQuery('#time').hide();
                        jQuery("#slider-range").hide();
                        jQuery('.carrier-time-from').hide();
                    } else {
                        jQuery('#time').hide();
                        jQuery("#slider-range").show()
                        jQuery('.carrier-time-from').show();
                        jQuery("#slider-range").noUiSlider({
                            start: [from, from + (3 * 60)],
                            range: {
                                'min': from,
                                'max': to
                            }
                        }, true);

                        var values = jQuery("#slider-range").val();
                        jQuery('#pickup-time').html(Mall.translate.__("For your address, there are dates from ") +
                        _from + Mall.translate.__(" to ") + _to + '<br>&nbsp;<br><span id="wrapper-choosen-pickup-time">' + _rma.formatTimeRange(values[0], values[1]) + '</span>');
                    }
                });
                //SET SLIDER, SAVE PICKUP TIME, WRITE MESSAGES END



                if (Object.size(dataList)) {
                    var values = jQuery("#slider-range").val();
                    _rma.formatTimeRange(values[0], values[1]);
                }

                jQuery('#pickup-date-form-panel input').first().click();//default set the first day
                //PICKUP DATE AND HOURS START END

                //##############################

                jQuery("select[name^='rma[items_condition_single]']").each(function(item){
                    if(item.value){
                        if(returnReasons[item.value].flow == flowAcknowledged){
                            isAcknowledged = true;
                        }
                    }
                })

                if(isAcknowledged){
                    jQuery('#pickup-address-form').hide();
                    jQuery('#pickup-date-form').hide();
                    jQuery('#pickup-address-overview').hide();
                    jQuery('#pickup-date-overview').hide();
                    jQuery('#overview-message').hide();
                }
                else{
                    jQuery('#pickup-address-form').show();
                    jQuery('#pickup-date-form').show();
                    jQuery('#pickup-address-overview').show();
                    jQuery('#pickup-date-overview').show();
                    jQuery('#overview-message').show();
                }
            });
			this.addressbook.init();
        },
		


        // Step 2 functions
		
        getTime: function(hours, minutes) {
            minutes = minutes + "";
            if (minutes.length == 1) {minutes = "0" + minutes;}
            return hours + ":" + minutes;
        },

        formatTimeRange: function (from, to) {
            var minutes0 = parseInt(from % 60, 10),
                hours0 = parseInt(from / 60 % 24, 10),
                minutes1 = parseInt(to % 60, 10),
                hours1 = parseInt(to / 60 % 24, 10);


            var startTime = _rma.getTime(hours0, minutes0);
            var endTime = _rma.getTime(hours1, minutes1);

            var message = Mall.translate.__("Selected time") + ': <span id=pickup-time-from>' + startTime + '</span>&nbsp;-&nbsp;' + '<span id=pickup-time-to>' + endTime + '</span>';

            jQuery('[name="rma[carrier_time_from]"]').val(startTime);
            jQuery('[name="rma[carrier_time_to]"]').val(endTime);

            return message;
        },

        round: function(val, type){
            var h = parseInt(val.substring(0, 2));
            var m = parseInt(val.substring(3, 5));
            if (type == 'up') {
                if (m) {
                    h = h + 1;
                }
            }
            return h + ':00';
        },


		////////////////////////////////////////////////////////////////////////
		// Addressbook
		////////////////////////////////////////////////////////////////////////
		addressbook: {
			_book: null,
			_content: jQuery("#pickup-address-form"),
			/**
			 * Init addressbook
			 * @returns {void}
			 */
			init: function(){
				var self = this;

				// Render selected and list
				this.renderSelectedAddress("shipping");
				this.renderAddressList("shipping");

				// Hide address lists
				this._rollAddressList(
					"shipping",
					this._content.find(".panel-adresses.shipping"),
					false
				);

				// Handle clicks
				this._content.find(".change_address").each(function(){
					var type = jQuery(this).hasClass("billing") ? "billing" : "shipping";
					jQuery(this).click({type: type}, function(e){
						self.handleChangeAddressClick(e);
						return false;
					})
				})
			},
			renderSelectedAddress: function(type){
				var template = this.getSelectedTemplate(),
					addressBook = this.getAddressBook(),
					target = jQuery(".current-address."+type, this._content),
					addressObject = addressBook.getSelected(type);

				if(addressObject){
					var node = jQuery(Mall.replace(
						template,
						this.processAddressToDisplay(addressObject)
					));
					this.processSelectedAddressNode(node, addressObject, addressBook, type);
					target.html(node);

				}else{
					target.html(Mall.translate.__("no-addresses"));
				}
			},
			renderAddressList: function(type){
				var template = this.getNormalTemplate(),
					addressBook = this.getAddressBook(),
					target = jQuery(".panel-adresses."+type, this._content),
					selectedAddress = addressBook.getSelected(type),
					self = this,
                    addNewButton,
                    addressCollection = addressBook.getAddressBook(),
					caption = jQuery("<div>").addClass("additional");

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
						target.append(node);
					});
                }else{
                    target.html(Mall.translate.__("no-addresses"));
                }
                addNewButton = this.getAddNewButton(type);
                addNewButton.show();
                target.append(addNewButton);
            },

            processAddressNode: function(node, address, addressBook, type){
                var removeable = addressBook.isRemoveable(address.getId()),
                    remove = node.find(".remove"),
                    choose = node.find(".choose"),
                    edit = node.find(".edit");


                var eventData = {
                    addressBook: addressBook,
                    step: this,
                    address: address,
                    type: type
                };

                remove.click(eventData, this.removeAddress);
                edit.click(eventData, this.editAddress);
                choose.click(eventData, this.chooseAddress);
                remove[removeable ? "show" : "hide"]();

                return node;
            },

			processSelectedAddressNode: function(node, address, addressBook, type){
				var edit = node.find(".edit"),
					editable = this._content.find(".change_address."+type).hasClass("open");

				var eventData = {
					addressBook: addressBook,
					step: this,
					address: address,
					type: type
				};
				edit.click(eventData, this.editAddress);
				//edit[editable ? "show" : "hide"]();

				return node;
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
                //this.attachNewAddressInputsMask(modal, type);
                //his.attachNewAddressBootstrapTooltip(modal, type);
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
                    if (!jQuery(this).parents('form').valid()) {
                        //visual validation fix
                        if (jQuery('#shipping_vat_id').first().val().length) {
                            jQuery('#shipping_vat_id').parents('.form-group').removeClass('hide-success-vaild');
                        } else {
                            jQuery('#shipping_vat_id').parents('.form-group').addClass('hide-success-vaild');
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
                            if (type === "shipping") {
                                self.getAddressBook().setSelectedShipping(address);
                            }
                            self.renderSelectedAddress("shipping");
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
					block = this._content.find(".panel-adresses." + type);

				element.toggleClass("open");

				this._rollAddressList(type, block, element.hasClass("open"));
			},
			toggleOpenAddressList: function (type) {
                jQuery(".panel-footer").find("." + type).click();
            },
			////////////////////////////////////////////////////////////////////
			// Handlers
			////////////////////////////////////////////////////////////////////
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
                    if (idx.indexOf("street") !== -1 && item) {
                        if (jQuery.isArray(item)) {
                            item = item.shift();
                        }
                    }
                    if (form.find("[name='"+ idx +"']").length > 0) {
                        form.find("[name='"+ idx +"']").val(item);
                    }
                });
            },
			lockButton: function (button) {
                jQuery(button).prop("disabled", true);
            },

            unlockButton: function (button) {
                jQuery(button).prop("disabled", false);
            },
			////////////////////////////////////////////////////////////////////
			// Setters/getters
			////////////////////////////////////////////////////////////////////
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

			_rollAddressList: function(type, block, doOpen){
				var contextActions = block.
						siblings(".current-address").
						find(".action");

				var element = this._content.find(".change_address." + type);

				if(doOpen){
					block.show();
					contextActions.show();
					element.addClass("open");
					element.text(Mall.translate.__("roll-up"));
				}else{
					block.hide();
					contextActions.hide();
					element.removeClass("open");
					element.text(Mall.translate.__("change-address"));
				}

				//this._processActionSelectedAddress(contextActions);
			},
		},

		
		
		////////////////////////////////////////////////////////////////////////
		// Misc
		////////////////////////////////////////////////////////////////////////
		addValidator: function(name, message, fn){
			jQuery.validator.addMethod(name, fn, message);
		},
		
		getReturnReasons: function(index){
			if(typeof index != "undefined"){
				return this.returnReasons[index];
			}
			return this.returnReasons;
		},
		
		setReturnReasons: function(data){
			this.returnReasons = data;
		},
		
		setNotAvailableText: function(text){
			 this.notAvailableText = text;
			 return this;
		},
		
		getNotAvailableText: function(){
			return this.notAvailableText;
		}
	};
	
	jQuery.extend(true, Mall, {rma: {"new": _rma}});
	// Mall.rma.new.init(); moved to phtml after setting options
});
