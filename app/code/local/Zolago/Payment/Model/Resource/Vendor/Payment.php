<?php

/**
 * Class Zolago_Payment_Model_Resource_Vendor_Payment
 */
class Zolago_Payment_Model_Resource_Vendor_Payment extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('zolagopayment/vendor_payment', 'vendor_payment_id');
    }

    /**
     * Perform actions after object save
     *
     * @param Varien_Object $object
     * @return Zolago_Payment_Model_Resource_Vendor_Payment
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        Mage::log($object->getData(), null, "vendor_payment.log");

        Mage::helper("ghstatements/vendor_balance")
            ->updateVendorBalanceData($object->getVendorId(), "vendor_payment_cost", $object->getCost(), $object->getDate(), $object->getOrigData("date"));
        return parent::_afterSave($object);
    }

}