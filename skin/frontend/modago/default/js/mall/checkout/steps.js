/**
 * Created by pawelchyl on 10.09.2014.
 */

(function () {
    "use strict";
    Mall.Checkout.steps = {
		////////////////////////////////////////////////////////////////////////
		// Address step for all cases
		////////////////////////////////////////////////////////////////////////
		address: {
            /**
             * Step id
             */
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

                // add validation to form
                this.validate.init();
			},
			
			onDisable: function(){
				jQuery("#step-0-submit").prop("disabled", true);
			},
			
			onEnable: function(){
				jQuery("#step-0-submit").prop("disabled", false);
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
                        self.checkout.getActiveStep().enable();

                        return true;
                    }).fail(function () {
                        /**
                         * @todo implementation. At the moment we do nothing.
                         */
                    }).always(function () {
                        // do nothing or implement
                    });
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
                    if (!Mall.Checkout.steps.address.getCustomerIsLoggedIn()) {
                        jQuery("input[name='account[email]']").change(
                            Mall.Checkout.steps.address.afterEmailValidationAction() );
                    }
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
                } else {
                    alert('Select shipping method');
                }
                return false;

            },

            validate: {
                init: function () {
console.log("Hello");
                    jQuery('#' + Mall.Checkout.steps.shippingpayment._self_form_id)
                        .validate(Mall.validate.getOptions({
                            errorLabelContainer: "#containererreurtotal",
                            ignore: "",

                            rules: {
                                shipping: {
                                    required: true,
                                    "payment[method]" : true
                                }
                            },
                            messages: {
                                shipping: {
                                    required: "Please select shipping"
                                },
                                "payment[method]": {
                                    required: "Please select payment"
                                }
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

