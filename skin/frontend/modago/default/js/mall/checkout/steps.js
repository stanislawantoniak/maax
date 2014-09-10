/**
 * Created by pawelchyl on 10.09.2014.
 */

(function () {
    "use strict";
    Mall.Checkout.steps = {
		////////////////////////////////////////////////////////////////////////
		// Address step for all cases
		////////////////////////////////////////////////////////////////////////
		"address": {
			id: "step-0",
			code: "address",
			doSave: true,

			_invoice_copy_shipping_fields: [
				"#billing\\:company",
				"#billing\\:street",
				"#billing\\:postcode",
				"#billing\\:city"
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
				jQuery("#billing\\:company").val(jQuery("#shipping\\:company").val());
				jQuery("#billing\\:street").val(jQuery("#shipping\\:street").val());
				jQuery("#billing\\:postcode").val(jQuery("#shipping\\:postcode").val());
				jQuery("#billing\\:city").val(jQuery("#shipping\\:city").val());

				return this;
			},

			invoiceDisableFields: function (fields) {
				jQuery.each(fields,  function (idx, item) {
					jQuery(item).prop("disabled", true);
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
					jQuery(item).prop("disabled", false);
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
					password = form.find("#account\\:password").val(),
					billingData,
					stepData = [];

				// set password confirmation
				if (password.length > 0) {
					form.find("#account\\:confirmation").val(password);
				}
				// set billing data
				form.find("[name='billing[firstname]']").val(form.find("[name='account[firstname]']").val());
				form.find("[name='billing[lastname]']").val(form.find("[name='account[lastname]']").val());

				// copy shipping data if order will be delivered to myself
				if (!form.find("[name='shipping[different_shipping_address]']").is(":checked")) {
					form.find("#shipping\\:firstname").val(form.find("#account\\:firstname").val());
					form.find("#shipping\\:lastname").val(form.find("#account\\:lastname").val());
					form.find("#shipping\\:telephone").val(form.find("#account\\:telephone").val());
				}

				stepData = form.serializeArray();
				// fill billing data with shipping
				if (!this.getIsNeedInvoice()) {
					billingData = this.getBillingFromShipping();
					jQuery.merge(stepData, billingData);
				}

				return stepData;
			}
		}
    };
})();
