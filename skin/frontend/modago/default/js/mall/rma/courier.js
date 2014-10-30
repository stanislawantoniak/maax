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
        addressbook: jQuery.extend({}, Mall.customer.AddressBook.Layout, {
            /**
             * Content object
             */
            content: jQuery("#pickup-address-form"),

            /**
             * Init addressbook
             * @returns {void}
             */
            init: function(){
                var self = this;

                // No addressbook available
                if(!this.content.find("#can_init_addressbook").length){
                    return;
                }

                // Set selected address from input
                this.getAddressBook().setSelectedShipping(
                    this.content.find("#customer_address_id").val()
                );

                // Render selected and list
                this.renderSelectedAddress("shipping");
                this.renderAddressList("shipping");

                // Hide address lists
                this._rollAddressList(
                    "shipping",
                    this.content.find(".panel-adresses.shipping"),
                    false
                );

                // Handle clicks
                this.content.find(".change_address").each(function(){
                    var type = jQuery(this).hasClass("billing") ? "billing" : "shipping";
                    jQuery(this).click({type: type}, function(e){
                        self.handleChangeAddressClick(e);
                        return false;
                    })
                });

                // Bind address change event
                this.content.on("selectedAddressChange", function(e, address){
                    console.log(address);
                });

            },

            /**
             * @param {string} type
             * @returns {void}
             */
            renderSelectedAddress: function(type){
                var template = this.getSelectedTemplate(),
                    addressBook = this.getAddressBook(),
                    target = jQuery(".current-address."+type, this.content),
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

            /**
             * @param {string} type
             * @returns {void}
             */
            renderAddressList: function(type){
                var template = this.getNormalTemplate(),
                    addressBook = this.getAddressBook(),
                    target = jQuery(".panel-adresses."+type, this.content),
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
                    editable = this.content.find(".change_address."+type).hasClass("open");

                var eventData = {
                    addressBook: addressBook,
                    step: this,
                    address: address,
                    type: type
                };
                edit.click(eventData, this.editAddress);

                return node;
            },

            _rollAddressList: function(type, block, doOpen){
                var contextActions = block.
                    siblings(".current-address").
                    find(".action");

                var element = this.content.find(".change_address." + type);

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
                            self.getAddressBook().setSelectedShipping(address);
                            self.onSelectedAddressChange(address, type);
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


            ////////////////////////////////////////////////////////////////////
            // Handlers
            ////////////////////////////////////////////////////////////////////

            /**
             * @param {object} address
             * @param {string} type
             * @returns {void}
             */
            onSelectedAddressChange: function(address, type){
                var event = jQuery.Event("selectedAddressChange");
                this.content.trigger(event, [address, type]);
                this.content.find("#customer_address_id").val(address.getId());
            },

            /**
             * @param {type} event
             * @returns {Boolean}
             */
            removeAddress: function(event){
                var deffered;

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
                                event.data.step.renderAddressList("shipping");
                                event.data.step.toggleOpenAddressList(event.data.type);
                            }
                        }
                    });
                }

                return false;
            },

            /**
             * Make choose of adderss. Save need invoice if needed.
             * @param {type} object
             * @returns {Boolean}
             */
            chooseAddress: function(event){
                var addressBook = event.data.addressBook,
                    address = event.data.address,
                    type = event.data.type,
                    self = event.data.step;


                addressBook.setSelectedShipping(address);
                self.onSelectedAddressChange(address, type);

                self.renderSelectedAddress("shipping");
                self.renderAddressList("shipping");

                // Roll up list
                var listBlock = self.content.find(".panel-adresses." + type);

                self._rollAddressList(type, listBlock, false);

                return false;
            },

            /**
             * @param {object} event
             * @returns {Boolean}
             */
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
            }
        }),

		
		
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
