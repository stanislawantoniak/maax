<?php

/**
 * Class Zolago_Payment_Model_Resource_Vendor_Invoice
 */
class Zolago_Payment_Model_Resource_Vendor_Invoice extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('zolagopayment/vendor_invoice', 'vendor_invoice_id');
    }

    /**
     * Perform actions after object save
     *
     * @param Varien_Object $object
     * @return Zolago_Payment_Model_Resource_Vendor_Invoice
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        Mage::log($object->getData(), null, "vendor_invoice.log");

        Mage::helper("ghstatements/vendor_balance")
            ->updateVendorBalanceData(
                $object->getVendorId(),
                "vendor_invoice_cost",
                ($object->getCommissionBrutto() + $object->getTransportBrutto() + $object->getMarketingBrutto() + $object->getOtherBrutto()), //TODO ?????? which column
                $object->getDate(),
                $object->getOrigData("date")
            );
        return parent::_afterSave($object);
    }

}