jQuery(function($){
	var _rma = {
		step2: $("#step-2"),
		newRma: $("#edit-rma"),
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

//			this.validation = this.newRma.validate(
//				Mall.validate.getOptions()
//			);

            this._initStep2();


            //visual fix for message - can't be done by css
            if ($('.messages i').length) {
                $('#content').css('margin-top', '0px');
                $('.messages i').click(function () {
                    $('#content').css('margin-top', '');
                });
            }

            _rma.addUsefulFunctions();
		},


		
        // Step 2 init
        _initStep2: function(){
            var s = this.step2,
                self = this,
                next = s.find("button.next");

            // Handle next click
            jQuery(".next").click(function(){
                console.log("Hello");
                var valid = true,
	                from = s.find('input[name="rma[carrier_time_from]"]').val().split(":")[0],
		            to = s.find('input[name="rma[carrier_time_to]"]').val().split(":")[0];

                //validate if user has chosen pickup date
                if (!s.find('input[name="rma[carrier_date]"]:checked').length) {
                    valid = false;
                }

                console.log(valid);
                //--validation
                if(valid){
                    console.log("Save RMA saveRmaCourier");
                    self._submitForm();
                }
                return false;
            });

            //PICKUP DATE AND HOURS START
            _rma.addUsefulFunctions();
            _rma.initDateList(dateList);//INIT DATE LIST
            _rma.initDefaultSlider(dateList);//INIT SLIDER DEFAULT VALUES AND PARAMS
            _rma.attachSlideOnSlider();//CHANGE DESCRIPTIONS ON SLIDER SLIDE
            _rma.attachClickOnDate();//SET SLIDER, SAVE PICKUP TIME, WRITE MESSAGES
            _rma.initDateListValues(dateList);//INIT VALUES FOR DATE LIST
            jQuery('#pickup-date-form-panel input').first().click();//default set the first day
            //PICKUP DATE AND HOURS START END


            //##############################


            this.addressbook.init();

            jQuery(this.addressbook.content).on("selectedAddressChange", function(e, address) {
                //console.log(address.getData());
                var poId = parseInt(jQuery("#edit-rma input[name='po_id']").val());
                var zip = address.getData().postcode;
                _rma.getDateList(zip);
            });
        },



        // Step 2 functions

        addUsefulFunctions: function() {
            Object.size = function(obj) {
                var size = 0, key;
                for (key in obj) {
                    if (obj.hasOwnProperty(key)) size++;
                }
                return size;
            };
        },

        initDateList: function(_dateList) {
            if (Object.keys(_dateList).length == 0) {
                jQuery('#btn-next-step-2').hide();
            } else {
                for(var day in _dateList) {
                    jQuery('#carrier_date_' + day).attr('data-PickupFrom', _rma.round(_dateList[day].getPostalCodeServicesResult.drPickupFrom, 'up') );
                    jQuery('#carrier_date_' + day).attr('data-PickupTo', _rma.round(_dateList[day].getPostalCodeServicesResult.drPickupTo, 'down') );
                }
            }
        },

        initDateListValues: function(_dateList) {
            if (Object.keys(_dateList).length == 0) {
                if (jQuery("#slider-range").length) {
                    var values = jQuery("#slider-range").val();
                    _rma.formatTimeRange(values[0], values[1]);
                }
            }
        },

        initDefaultSlider : function(_dateList){
            if (Object.keys(_dateList).length != 0) {
                if(jQuery("#slider-range").length) {
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
            }
        },

        attachSlideOnSlider: function() {
            jQuery("#slider-range").off().on({
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
        },

        attachClickOnDate:  function(){
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

                jQuery('#btn-next-step-2').show();//if can click then can go to next step
            });
        },

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

        /**
         * get new pickup date list for given:
         * current po_id and zip code
         * then rebuild content
         * @param poId
         * @param zip
         * @returns {boolean}
         */
        getDateList: function(zip) {
            "use strict";
            //poId = parseInt(poId);

            var matched = zip.match(/([0-9]{2})([0-9]{3})/);
            if(matched != null) {
                zip = matched[1] + "-" + matched[2];
            }

            OrbaLib.Rma.getDateList({
                //'poId': poId,
                'zip': zip
            }, {
                'done': function (data) {
                    console.log(data);
                    if (data !== undefined && data.status !== undefined) {
                        if (data.status) {
                            // is at least one day for pickup
                            //console.log('done');
                            _rma.rebuildPickupDateForm(data.content);

                            _rma.initDateList(data.content);//INIT DATE LIST
                            _rma.initDefaultSlider(data.content);//INIT SLIDER DEFAULT VALUES AND PARAMS
                            _rma.attachSlideOnSlider();//CHANGE DESCRIPTIONS ON SLIDER SLIDE
                            _rma.attachClickOnDate();//SET SLIDER, SAVE PICKUP TIME, WRITE MESSAGES
                            _rma.initDateListValues(data.content);//INIT VALUES FOR DATE LIST
                            jQuery('#pickup-date-form-panel input').first().click();//default set the first day

                            return false;
                        } else {
                            //there is no days for pickup
                            _rma.showInfoAboutNoPickup();
                        }
                    }
                    return true;
                },
                'fail': function( jqXHR, textStatus ) {
                    //console.log( "GetDateList: Request failed: " + textStatus );
                    _rma.showInfoAboutNoPickup(); //better then gif with infinity loading
                },
                'always': function () {
                    jQuery("#pickup-date-form-ajax-loading").remove();
                }
            }, true, false);

            jQuery("#pickup-date-form div.current-rma-date").remove(); //clear all
            jQuery("#pickup-date-form div.panel-body").html(
                "<div id='pickup-date-form-ajax-loading' style='text-align: center;'>" +
                    "<img src='" + ajaxLoaderSkinUrl + "'></div>"
            );
            jQuery('#btn-next-step-2').hide();

            return true;
        },

        rebuildPickupDateForm: function(_dateList) {
            jQuery("#pickup-date-form div.current-rma-date").remove(); //clear all

            var div_current_rma_date = jQuery("<div/>", {
                class: "current-rma-date clearfix"
            });

            var div_pickup_date_form_panel = jQuery("<div/>", {
                id: "pickup-date-form-panel",
                class: "fieldset flow-return"
            });

            var label_choose_the_date = jQuery("<label/>", {
                //id: "",
                class: "required choose-date",
                for: "carrier-date",
                html: Mall.translate.__("Choose the date") + "<em>:</em>"
            });

            var div_input_box = jQuery("<div/>", {
                class: "input-box",
                id: "dateList"
            });

            div_current_rma_date.appendTo('#pickup-date-form div.panel-body');
            div_pickup_date_form_panel.appendTo('#pickup-date-form div.current-rma-date');
            label_choose_the_date.appendTo('#pickup-date-form-panel');
            div_input_box.appendTo('#pickup-date-form-panel');

            var number = 1;
            for (key in _dateList) {
                var date = new Date(parseInt(key) * 1000);//time in ms
                var Y_m_d_date_format = date.getFullYear()+"-"+((date.getMonth()+1) < 10 ? "0"+(date.getMonth()+1) : date.getMonth()+1)+"-"+(date.getDate() < 10 ? "0"+date.getDate() : date.getDate());

                jQuery('#dateList').html( jQuery('#dateList').html() + " " +
                    "<input type='radio' name='rma[carrier_date]'" +
                    "id='" + "carrier_date_" + key + "'" +
                    "value='" + Y_m_d_date_format + "' /> " +
                    "<label for='carrier_date_" + key + "' class='label-" + number + "' > "
                );

                var span_wrapper = jQuery("<span/>").html(
                    "<span class='rma-dayname'>" + weekdays[date.getDay()] + "</span>" +
                        "<br/>" +
                        "<span class='rma-date'>" + dateListFormatedDate[Y_m_d_date_format] + "</span>"
                );

                jQuery("#dateList label[for='carrier_date_" + key + "']").append(span_wrapper);

                number++;
            }

            jQuery('#pickup-date-form-panel').append(
                "<label class='required carrier-time-from' for='carrier-time-from'>" +
                    Mall.translate.__("Select the time interval") + "<em>:</em></label>" +
                    "<div class='choose-time'><div class='field'><div class='input-box'>" +
                    "<input type='hidden' name='rma[carrier_time_from]' id='carrier-time-from'" +
                    "value='" + rmaCarrierTimeFrom + "'" +
                    "title='" + Mall.translate.__("Choose time-from of the day") + "'/>" +
                    "<input type='hidden' name='rma[carrier_time_to]' id='carrier-time-to'" +
                    "value='" + rmaCarrierTimeTo + "'" +
                    "title='" + Mall.translate.__("Choose time-to of the day") + "'/>" +
                    "</div><div id='pickup-time'></div></div><div id='slider-range'></div></div>"
            );
        },

        showInfoAboutNoPickup: function() {
            jQuery("#pickup-date-form div.current-rma-date").remove(); //clear all
            jQuery("#pickup-date-form div.panel-body").html(
                Mall.translate.__("For the given address is not possible to order a courier")
            );
            jQuery('#btn-next-step-2').hide();
        },

        _submitForm: function() {
            jQuery(window).unbind('beforeunload');
            jQuery('#edit-rma').submit();
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
                // Set selected address from input
                // It can trigger error if address not exists or empty
                try{
                    this.getAddressBook().setSelectedShipping(
                        this.content.find("#customer_address_id").val()
                    );
                }catch(e){
                    // No addresses
                }

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
                    needCaption = false,
                    addressCollection = addressBook.getAddressBook(),
                    caption = jQuery("<div>").addClass("additional");

                target.html('');
                target.css("padding-top", "0");

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
                        needCaption = true;
                    });
                }

                if(needCaption){
                    target.prepend(caption.text(Mall.translate.__("your-additional-addresses") + ":"));
                }else{
                    target.css("padding-top", "15px");
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
            /**
             * Remove vat id from form
             * @param {string} type
             * @returns {Array}
             */
            getNewAddressConfig: function(type){
                var result = [];
                jQuery.each(Mall.customer.AddressBook.Layout.getNewAddressConfig(), function(){
                    if(this.name=="vat_id"){
                        return;
                    }
                    result.push(this);
                })
                return result;

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
                    element.text(Mall.translate.__(
                        this.getAddressBook().getAddressBook().length ?
                            "change-address" : "add-address"
                    ));
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
