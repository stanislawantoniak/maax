<?php

/**
 * Class GH_Statements_Model_Vendor_Balance
 *
 * @method int getStatus()
 * @method string getBalanceMonth()
 * @method int getVendorId()
 *
 * @method string getPaymentToClient()
 * @method string getReturnToClient()
 *
 * @method string getVendorPaymentCost()
 * @method string getVendorInvoiceCost()
 *
 * @method string getBalancePerMonth()
 * @method string getBalanceCumulative()
 * @method string getBalanceDue()
 *
 */
class GH_Statements_Model_Vendor_Balance extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        $this->_init('ghstatements/vendor_balance');
    }

}