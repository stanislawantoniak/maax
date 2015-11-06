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
 * @method string getPaymentValue()
 */
class GH_Statements_Model_Statement extends Mage_Core_Model_Abstract {

    protected function _construct() {
        $this->_init('ghstatements/statement');
        parent::_construct();
    }
}

