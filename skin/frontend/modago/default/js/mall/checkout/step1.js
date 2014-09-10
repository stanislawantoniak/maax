/**
 * Created by pawelchyl on 10.09.2014.
 */

(function () {
    "use strict";
    Mall.Checkout.prototype.step1 = {

        _invoice_copy_shipping_fields: [
            "#billing\\:company",
            "#billing\\:street",
            "#billing\\:postcode",
            "#billing\\:city"
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
        }
    };
})();
