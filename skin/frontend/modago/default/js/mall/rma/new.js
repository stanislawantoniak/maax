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

            Object.size = function(obj) {
                var size = 0, key;
                for (key in obj) {
                    if (obj.hasOwnProperty(key)) size++;
                }
                return size;
            };
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

            jQuery(document).ready( function() {

                _rma.initDateList(dateList);//INIT DATE LIST
                _rma.initDefaultSlider(dateList);//INIT SLIDER DEFAULT VALUES AND PARAMS
                _rma.attachSlideOnSlider();//CHANGE DESCRIPTIONS ON SLIDER SLIDE
                _rma.attachClickOnDate();//SET SLIDER, SAVE PICKUP TIME, WRITE MESSAGES
                _rma.initDateListValues(dateList);//INIT VALUES FOR DATE LIST
                jQuery('#pickup-date-form-panel input').first().click();//default set the first day

                //IF PAYMENT METHOD IS CHECKONDELIVERY THEN SHOW FIELD BANK ACCOUNT
                jQuery('#customer-account-wrapper').hide();
                if (showBankAcc) {
                    jQuery('#customer-account-wrapper').show();
                }
                //IF PAYMENT METHOD IS CHECKONDELIVERY THEN SHOW FIELD BANK ACCOUNT END

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

        initDateList: function(_dateList) {
            if (Object.size(_dateList) == 0) {
                jQuery('#btn-next-step-2').hide();
            } else {
                for(var day in _dateList) {
                    jQuery('#carrier_date_' + day).attr('data-PickupFrom', _rma.round(_dateList[day].getPostalCodeServicesResult.drPickupFrom, 'up') );
                    jQuery('#carrier_date_' + day).attr('data-PickupTo', _rma.round(_dateList[day].getPostalCodeServicesResult.drPickupTo, 'down') );
                }
            }
        },

        initDateListValues: function(_dateList) {
            if (Object.size(_dateList) == 0) {
                var values = jQuery("#slider-range").val();
                _rma.formatTimeRange(values[0], values[1]);
            }
        },

        initDefaultSlider : function(_dateList){
            if (Object.size(_dateList) != 0) {
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
        getDateList: function(poId, zip) {
            "use strict";
            var promise = jQuery.ajax({
                url: Config.url.dhl_pickup_date_list,
                data: {
                    po_id: poId,
                    zip: zip
                },
                dataType: 'json',
                cache: false,
                async: true,
                type: "POST"
            });

            if (promise.done === undefined
                || promise.fail === undefined
                || promise.always === undefined) {
                return false;
            }

            promise.done(function (data) {
                if (data !== undefined && data.status !== undefined) {
                    if (data.status) {
                        // is at least one day for pickup
                        console.log('done');
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
            }).fail(function( jqXHR, textStatus ) {
                //console.log( "GetDateList: Request failed: " + textStatus );
            }).always(function () {

            });

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

                jQuery("<input/>", {
                    type: "radio",
                    name: "rma[carrier_date]",
                    id: "carrier_date_" + key,
                    value: Y_m_d_date_format
                }).appendTo('#dateList');

                var label_tmp = jQuery("<label/>", {
                    for: "carrier_date_" + key,
                    class: "label-" + number
                });

                jQuery('#carrier_date_' + key).after(label_tmp);

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
            jQuery("#pickup-date-form").html(
                Mall.translate.__("For the given address is not possible to order a courier")
            )
        },

        // Step 2 functions END

		// Step 3 functions
		_getRmaAddress: function() {
			return this.step2.find('.current-rma-address').html();
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
/*(function() {
			var newRma = $("new-rma");
			var form = new VarienForm("new-rma");
			var oldOldSubmit = form.submit;
			var step1 = $("step-1");
			var step2 = $("step-2");
			var step3 = $("step-3");
			var steps = [step1, step2, step3];
			var currentStep = 1;
			var returnReasons = <?php echo Mage::helper('zolagorma')->getReturnReasons($_po, true); ?>;
			
			var showStep = function(step) {
				steps.each(function(el) {
					el.style.display = 'none';
				});
				currentStep = step;
				collectData();
				steps[currentStep - 1].style.display = 'block';
			}
			
			var collectData = function(){
				// Rma items
				var items = [];
				newRma.select(".rma-checkbox").each(function(item) {
					if ($(item).checked) {
						var tr = $(item).up('tr');
						items.push({
							name: tr.getAttribute('data-name'),
							reasonText: tr.select('option[selected]')[0].innerHTML
						});
					}
				});
				
				var reviewItems = $("review-items");
				
				reviewItems.innerHTML = "";
				var lp = 1;
				items.each(function(item){
					reviewItems.insert("<tr><td>"+(lp++)+"</td><td>"+item.name+"</td><td>"+item.reasonText+"</td></tr>");
				})
				
				// Comment 
				var comment = $("comment-text");
				var commentReview = $("review-comment-text");
				
				commentReview.innerHTML = "";
				if(comment.value){
					commentReview.innerHTML = "<strong><?php echo $_helper->__("Additional information");?>:</strong> " +
						comment.value;
				}
				
				// Address
				$("review-shipping-address").innerHTML = $("shipping-address").innerHTML;
				
				// Pickup date
				var dateText = "<?php echo $_helper->__("Carrier");?>",
					carrierDate = $("carrier-date");
			
					dateText += " " + escape(carrierDate.value) + "<br/>";
					dateText += "<?php echo $_helper->__("Between");?>";
					dateText += " " + $F('carrier-time-from');
					dateText += " <?php echo $_helper->__("and");?>"
					dateText += " " + $F('carrier-time-to');
					
				$("pickup-date-review").innerHTML = dateText;
				
				// Account
				$("customer-account-review").innerHTML = $F("customer-account") ? 
					$F("customer-account") : "<?php echo $_helper->__('N/A');?>";
			}

			var checkHandler = function() {
				var el = $(this);
				el.up("tr").down(".condition-wrapper").style.display = el.checked ? "block" : "none";
				validateItems();
			}
			
			var selectHandler = function() {
				var el = $(this),
					value = el.value,
					initialClass = el.className,
					initialId = el.id,
					advice,
					initialAdviceId,
					newClass,
					newAdviceId;
				
				
				if(value){
					newClass = 'must-be-available-' + value;
				}
				else{
					newClass = 'required-entry';
				}		
				
				el.removeClassName(initialClass).addClassName(newClass);
				
				//find advice
				initialClass = initialClass.replace('validation-passed', '');
				initialClass = initialClass.replace('validation-failed', '');
				initialClass = initialClass.replace(' ', '');
				initialAdviceId = 'advice-' + initialClass + '-' + initialId;
				
				advice = $(initialAdviceId);
				
				if(advice){
					advice.remove();				
				}
			}

			//custom validator
		    
		    <?php foreach($_helper->getItemConditionTitles() as $_key=>$_label):?>
		    
		    Validation.add('must-be-available-<?php echo $_key; ?>',returnReasons[<?php echo $_key; ?>].message,function(value){
		    	
		    	var currentReason = returnReasons[value];
		    	
		    	if(!currentReason){
		    		return false;
		    	}
		    	
		        return currentReason.isAvailable;
		    });
			
			<?php endforeach;?>
		        
			// Validate conditions
			var validateItems = function(e) {
				var checked = false;
				var hasItems = $("rma-has-items");
				newRma.select(".rma-checkbox").each(function(item) {
					if ($(item).checked) {
						checked = true;
					}
				});

				hasItems.value = "";
				if (checked) {
					hasItems.value = 1;
				}
			};

			// Register click
			newRma.select(".rma-checkbox").each(function(item) {
				$(item).observe('click', checkHandler);
			});
			
			newRma.select(".condition-wrapper > select option").each(function(item) {
				
				var value = item.value,
					currentReason;
				
				if(value && value != ""){
					
					currentReason = returnReasons[value];
					if(currentReason && !currentReason.isAvailable){
						
						item.text += ' (Not available)';
					}
				}
			});
			
			newRma.select(".condition-wrapper > select").each(function(item) {
				
				$(item).observe('change', selectHandler);
			});
			
			$("step-1-submit").observe("click", function() {
				if (form.validator.validate()) {
					showStep(2);
					
					//check which flow is selected
					applyFlow();
					
				}
			});

			var applyFlow = function(){
				
				var isAcknowledged = false,
					selects;
				
				selects = $$('.step-1 .form-list select');
				
				// Loop through all selects and find selected ones
				selects.each(function(item){
					
					if(item.value){
						
						if(returnReasons[item.value].flow == <?php echo Zolago_Rma_Model_Rma::FLOW_ACKNOWLEDGED; ?>){
							isAcknowledged = true;
						}
					}
						
				});
				<?php  $vendor = $_po->getVendor();?>				
				<?php  if (!Mage::helper('orbashipping/carrier_dhl')->isEnabledForVendor($vendor)): ?>
					isAcknowledged = true;
				<?php endif; ?>

				if(isAcknowledged){
			  		$('pickup-address-form').hide();
			  		$('pickup-date-form').hide();
			  		$('pickup-address-overview').hide();
			  		$('pickup-date-overview').hide();
			  		$('overview-message').hide();
				}
				else{
					$('pickup-address-form').show();
			  		$('pickup-date-form').show();
			  		$('pickup-address-overview').show();
			  		$('pickup-date-overview').show();
			  		$('overview-message').show();
				}
		  		
		  		return true;
			};
			
			$("step-2-submit").observe("click", function() {
				if (form.validator.validate()) {
					showStep(3);
				}
			});
			
			$("step-1-back").observe("click", function() {
				showStep(1)
			})
			
			$("step-2-back").observe("click", function() {
				showStep(2)
			})

			// Calendar setup
			Calendar.setup({
				inputField : 'carrier-date',
				ifFormat : '%d-%m-%Y',
				button : false,
				align : 'Bl',
				singleClick : true
			});

			// trigger chaneg
			newRma.select(".rma-checkbox").each(function(item) {
				checkHandler.bind(item)();
			});
			
			// check submit possible
			newRma.observe('submit', function(e){
				if(currentStep!=steps.length){
					Event.stop(e);
				}
			})
			
			// Check on beginig
			validateItems();
			showStep(1);
		})();*/
	};
	
	jQuery.extend(true, Mall, {rma: {"new": _rma}});
	// Mall.rma.new.init(); moved to phtml after setting options
});
