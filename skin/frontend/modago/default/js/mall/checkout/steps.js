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

			id: "step-0",

			code: "address",

			doSave: true,

			_invoice_copy_shipping_fields: [
				"#billing_company",
				"#billing_street",
				"#billing_postcode",
				"#billing_city"
			],

			_billing_names: [
				"billing[firstname]",
				"billing[lastname]",
				"billing[telephone]",
				"billing[company]",
				"billing[vat_id]",
				"billing[street][]",
				"billing[postcode]",
				"billing[city]"
			],

			init: function () {
				this.attachInvoiceCopyShippingDataEvent();
				this.attachInvoiceEvent();
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
					} else {
						self.invoiceClearCopiedFields(self._invoice_copy_shipping_fields);
						self.invoiceEnableFields(self._invoice_copy_shipping_fields);
					}
				});
			},

			attachInvoiceEvent: function () {
				var self = this;
				jQuery("#invoice_vat").click(function () {
					if (jQuery("#invoice_data_address").is(":checked")) {
						self.invoiceCopyShippingData();
						self.invoiceDisableFields(self._invoice_copy_shipping_fields);
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
                    console.log(checkoutObject.getActiveStep().collect());
					self.submit();
					return false;
				});
			},
			isPasswordNotEmpty: function(){
				return this.content.find("[name='billing[customer_password]']").val().length>0;
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
					password = form.find("#account_password").val(),
					billingData,
					stepData = [],
                    telephone;

				// set password confirmation
				if (password.length > 0) {
					form.find("#account_confirmation").val(password);
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
                if (!form.find("#orders_someone_else").is(":checked")) {
                    form.find("#shipping_telephone").val(telephone);
                    form.find("#billing_telephone").val(telephone);
                }

				stepData = form.serializeArray();
				// fill billing data with shipping
				if (!this.getIsNeedInvoice()) {
					billingData = this.getBillingFromShipping();
					stepData = this.mergeArraysOfObjects(stepData, billingData);
				}

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
