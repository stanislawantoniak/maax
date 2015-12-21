<?php

/**
 * Class GH_Statements_Model_Order
 * @method string getId()
 * @method string getStatementId()
 * @method string getPoId()
 * @method string getPoIncrementId()
 * @method string getPoItemId()
 * @method string getSku()
 * @method string getShippedDate()
 * @method string getCarrier()
 * @method string getGalleryShippingSource()
 * @method string getPaymentMethod()
 * @method string getPaymentChannelOwner()
 * @method string getPrice()
 * @method string getDiscountAmount()
 * @method string getFinalPrice()
 * @method string getShippingCost()
 * @method string getCommissionPercent()
 * @method string getGalleryDiscountValue()
 * @method string getCommissionValue()
 * @method string getValue()
 * @method string getQty()
 */
class GH_Statements_Model_Order extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        $this->_init('ghstatements/order');
    }

}