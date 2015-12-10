<?php

/**
 * @method string getId()
 * @method string getStatementId()
 * @method string getPoId()
 * @method string getPoIncrementId()
 * @method string getRmaId()
 * @method string getRmaIncrementId()
 * @method string getEventDate()
 * @method string getSku()
 * @method string getReason()
 * @method string getPaymentMethod()
 * @method string getPaymentChannelOwner()
 * @method string getPrice()
 * @method string getDiscountAmount()
 * @method string getFinalPrice()
 * @method string getCommissionPercent()
 * @method string getGalleryDiscountValue()
 * @method string getCommissionValue()
 * @method string getValue()
 * @method string getApprovedRefundAmount()
 * @method string getCommissionReturn()
 * @method string getDiscountReturn()
 *
 * Class GH_Statements_Model_Rma
 */
class GH_Statements_Model_Rma extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        $this->_init('ghstatements/rma');
    }

}