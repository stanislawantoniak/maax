<?php
/**
 * Statement model
 * DB Table: gh_statements
 *
 * @method string getId()
 * @method string getVendorId()
 * @method string getCalendarId()
 * @method string getEventDate()
 * @method string getName()
 * @method string getOrderCommissionValue()
 * @method string getOrderValue()
 * @method string getRmaCommissionValue()
 * @method string getRmaValue()
 * @method string getRefundValue()
 * @method string getTrackingChargeSubtotal()
 * @method string getTrackingChargeTotal()
 * @method string getMarketingValue()
 * @method string getVendorInvoiceId()
 * @method string getPaymentValue()
 * @method string getGalleryDiscountValue()
 * @method string getCommissionCorrection()
 * @method string getDeliveryCorrection()
 * @method string getMarketingCorrection()
 * @method string getToPay()
 * @method string getTotalCommissionNetto()
 * @method string getTotalCommission()
 * @method string getLastStatementBalance()
 * @method string getStatementPdf()
 * @method string getDateFrom()
 * @method string getActualBalance()
 * @method string getOrderGalleryDiscountValue()
 * @method string getRmaGalleryDiscountValue()
 */
class GH_Statements_Model_Statement extends Mage_Core_Model_Abstract {

    protected function _construct() {
        $this->_init('ghstatements/statement');
        parent::_construct();
    }
}

