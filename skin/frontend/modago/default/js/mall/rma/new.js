jQuery(function($){
	var _rma = {
		step1: $("#step-1"),
		step2: $("#step-2"),
		step3: $("#step-3"),
		newRma: $("#new-rma"),
		validation: null,
		steps: [],
		currentStep: -1, // init value
		returnReasons: [],
		unloadMessage: 'Do You really want to leave RMA process?',
        selectReturnReasonMessage: "Select return reason",
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
			this.go(0);
			// Fix footer
			jQuery(window).resize();
		},
		
		// Internal init
		_init: function(){
			var self = this;
			this.steps = [this.step1, this.step2, this.step3];

			this.validation = this.newRma.validate(
				Mall.validate.getOptions()
			);
			
			this._initStep1();
            this._initStep2();
            this._initStep3();
			
	
			$(window).bind('beforeunload', function() {
				if (self.currentStep>0 && !self.ignoreUnload) {
					return self.unloadMessage;
				}
			});

            //visual fix for message - can't be done by css
            if ($('.messages i').length) {
                $('#content').css('margin-top', '0px');
                $('.messages i').click(function () {
                    $('#content').css('margin-top', '');
                });
            }
		},

        // Step 1 init
        _initStep1: function(){
            var s = this.step1,
                self = this,
                next = s.find("button.next"),
				selects = s.find("select");

            // Chexboxes
            var checkboxHandler = function(){
                var el = $(this),
					select = el.closest("tr").find("select");

                next[s.find(":checkbox:checked").length ? "removeClass" : "addClass"]('hidden');
				
				if(!el.is(":checked")){
					select.val("");
				}
				select.data('checkboxTrigger', true);
				select.change();
				select.data('checkboxTrigger', false);
            };
            s.find(":checkbox").change(checkboxHandler).change();

            // Make validation of select (various methods)
            var selectHandler = function(){
                var el = $(this),
                    value = el.val(),
                    rules = {},
					ruleName = null,
                    settings = self.newRma.validate().settings,
					checkbox = el.closest("tr").find(":checkbox");
			
				// Event not triggered by checkbox
				if(!el.data('checkboxTrigger') && checkbox.is(":checked")!=!!value.length){
					checkbox.prop("checked", !!value.length > 0).change();
				}
			
				// Option selected apply validatop
				if(value){
                    ruleName = 'must-be-available-' + value;
				// No option and selected chebox required validataion
                }else if(checkbox.is(":checked")){
                    ruleName = "required";
				}
				
				
				// No rule matched - add empty rule
				rules[el.attr('name')] = ruleName;
				$.extend(settings.rules, rules);

                // Validate is needed?
				if(!el.data('checkboxTrigger') && value){
					el.valid();
				}

                var selected = false;
                jQuery("select option:selected").each(function(i,item){
                    if(jQuery(item).val() > 0){
                        selected = true;
                    }
                });

                // Clear border if validation rules are empty remove error if needed
				if(ruleName===null){
					el.valid();
					el.parents(".form-group").removeClass("has-feedback has-success has-error");
				}
			}
			
			// Rewrite options labels 
			selects.find('option').each(function(item){
				var el = jQuery(this),
					value = el.attr('value'),
					currentReason;
				
				if(value && value != ""){
					currentReason = self.getReturnReasons(value);
					if(currentReason && !currentReason.isAvailable){
						el.text(el.text() + ' (' + self.getNotAvailableText() + ')');
					}
				}
			});
			
            selects.select2({minimumResultsForSearch: -1});
			selects.change(selectHandler);

            // Handle next click
            s.find(".next").click(function(){
                var valid = true;
                s.find(":checkbox:checked").each(function(){
                    var el = $(this),
                        select = el.parents("tr").find("select");
                    if(!select.valid()){
                        valid = false;
                    }
                });
                if(valid){
                    self.next();
                }else if(s.find(".has-error").length){
					jQuery('html, body').animate({
						scrollTop: s.find(".has-error").offset().top - 70
					}, 500);
				}
                return false;
            });
        },
		
        // Step 2 init
        _initStep2: function(){
            var s = this.step2,
                self = this,
                next = s.find("button.next");

            // Handle back click
            s.find(".back").click(function () {
                self.prev();
                return false;
            });

            // Handle next click
            s.find(".next").click(function(){
                var valid = true,
	                from = s.find('input[name="rma[carrier_time_from]"]').val().split(":")[0],
		            to = s.find('input[name="rma[carrier_time_to]"]').val().split(":")[0],
	                account = s.find('input[name="rma[customer_account]"]').val().replace(new RegExp('pl','gi'),"").replace(new RegExp(' ','g'),"");

	            //check if those blocks are displayed
	            if($('#pickup-address-form') && $('#pickup-date-form')) {
	            //validate if user has chosen pickup date
		            if (!s.find('input[name="rma[carrier_date]"]:checked').length) {
		                valid = false;
	                }
	            //validate if chosen timespan is minimum 3 hours
		         //   if (to - from < 3) {
		         //       valid = false;
		         //   }
	            }

	            //validate if entered account number is correct (optional field)
	            if(account && (account.length != 26 || !$.isNumeric(account))) {
		            valid = false
	            }

                //--validation
                if(valid){
	                self.fillRmaSummary();
                    self.next();
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

                //IF PAYMENT METHOD IS CHECKONDELIVERY THEN SHOW FIELD BANK ACCOUNT
                jQuery('#customer-account-wrapper').hide();
                if (showBankAcc) {
                    jQuery('#customer-account-wrapper').show();
                }
                //IF PAYMENT METHOD IS CHECKONDELIVERY THEN SHOW FIELD BANK ACCOUNT END

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
		
        // Step 3 init
        _initStep3: function(){
            var s = this.step3,
                self = this,
                next = s.find("button.next");

            // Handle back click
            s.find(".back").click(function () {
                self.prev();
                return false;
            });

            // Handle next click
            s.find(".next").click(function(){
				// Submit form
	            $(window).unbind('beforeunload');
                $('#new-rma').submit();
            });
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


		// Step 3 functions
		_getRmaAddress: function() {
			var cloned = this.step2.find('.current-rma-address dl').clone();
			cloned.find(".action").remove();
			return cloned;
		},

		_getPickup: function() {
			var s = this.step2,
				out = {};
			out.carrier_date = s.find('input[name="rma[carrier_date]"]:checked').val();
			out.carrier_time_from = s.find('input[name="rma[carrier_time_from]"]').val();
			out.carrier_time_to = s.find('input[name="rma[carrier_time_to]"]').val();
			return out;
		},

		_getAccount: function() {
			return this.step2.find('input[name="rma[customer_account]"]').val();
		},

		_getRmaItems: function() {
			var s = this.step1,
				out = [];
			s.find('input[type=checkbox][name^="rma[items_single]"]:checked').each(function() {
				var item = jQuery(this);
				var tmp = {},
					id = item.prop('id').split("_")[2];
				tmp.img = item.parent('td').next('td').find('img').prop('src');
				tmp.desc = item.parent('td').next('td').next('td').find('.desc-holder').html();
				tmp.reason = s.find('select[name="rma[items_condition_single]['+id+'][0]"]').find(':selected').html();
				out.push(tmp);
			});
			return out;
		},

		_getRmaComment: function() {
			return this.step1.find('textarea[name="rma[comment_text]"]').val();
		},

		_getRmaSummaryData: function() {
			var out = {};
			out.address = this._getRmaAddress();
			out.pickup = this._getPickup();
			out.account = this._getAccount();
			out.items = this._getRmaItems();
			out.comment = this._getRmaComment();
			return out;
		},

		_getItemHtml: function(item) {
			return "" +
			"<tr>" +
			"   <td rowspan='2' class='summary-image'>" +
			"       <img src='" + item.img + "' />" +
			"   </td>" +
			"   <td class='summary-item-desc'>" +
			"       " + item.desc +
			"   </td>" +
			"</tr>" +
			"<tr>" +
			"   <td class='summary-reason'>" +
			"       <span class=\"bold\">" + this.txtReason + "</span><br />" +
			"       " + item.reason +
			"   </td>" +
			"</tr>";
		},

		_getCommentHtml: function(comment) {
			return "" +
			"<tr>" +
			"   <td colspan='2' class='summary-comment'>" +
			"       <p id='review-comment-text'>" +
			"           <span class='bold'>" + this.txtComment + "</span> " +
						$("<div/>").text(comment).html() +
					"</p>" +
			"   </td>" +
			"</tr>";
		},

		fillRmaSummary: function() {
			var data = this._getRmaSummaryData();
			var date = new Date(data.pickup.carrier_date);
			var month = date.getMonth() + 1;
			var day = this.daysOfWeek[date.getDay()] + " " +
					date.getDate() + "-" +
					(month < 10 ? "0" + month : month) + "-" +
					date.getFullYear(),
				pickup = $("#pickup-date-review"),
				accountFieldset = $(".customer-account-fieldset"),
				account = $("#customer-account-review"),
				items = $("#review-items").find("tbody"),
				address = $("#review-shipping-address");
			if(data.pickup) {
				pickup.show();
				pickup.find('.pickup-day').html(day);
				pickup.find('.pickup-time').html(
					this.txtCarrierTime
						.replace(this._txtCarrierTimeFrom, data.pickup.carrier_time_from)
						.replace(this._txtCarrierTimeTo, data.pickup.carrier_time_to)
				);
			} else {
				pickup.hide();
			}

			if(data.account) {
				account.html(data.account);
				accountFieldset.show();
			} else {
				account.html("");
				accountFieldset.hide();
			}

			items.html("");

			for(var i = 0; i < data.items.length; i++) {
				items.append(this._getItemHtml(data.items[i]));
			}

			if(data.comment) {
				items.append(this._getCommentHtml(data.comment));
			}

			address.html(data.address);

			return false;
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
			 * 
			 * @param {object} address
			 * @param {string} type
			 * @returns {void}
			 */
			onSelectedAddressChange: function(address, type){
				var event = jQuery.Event("selectedAddressChange");
				this.content.trigger(event, [address, type]);
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
		// Navigation
		////////////////////////////////////////////////////////////////////////
		next: function(){
			if(this.currentStep<this.steps.length-1){
				this.go(this.currentStep+1);
			}
		},
		prev: function(){
			if(this.currentStep>0){
				this.go(this.currentStep-1);
			}
		},
		go: function(step){
			if(this.currentStep==step){
				return this;
			}
			var self = this;
			$.each(this.steps, function(i){
				if(i==step){
					self._showStep(this);
				}else{
					self._hideStep(this);
				}
			});
			this.currentStep = step;
			// Fix footer
			jQuery(window).resize();

			// scroll to top
			jQuery(window).scrollTop(0);
			return this;
		},
		_getStep: function(step){
			if(typeof step == "object"){
				return step;
			}
			return this.steps[step];
		},
		_hideStep: function(step){
			this._getStep(step).hide().removeClass("active");
		},
		_showStep: function(step){
			this._getStep(step).show().addClass("active");
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
		},
		
		setUnloadMessage: function(msg){
			this.unloadMessage = msg;
		}
	};
	
	jQuery.extend(true, Mall, {rma: {"new": _rma}});
	// Mall.rma.new.init(); moved to phtml after setting options
});
