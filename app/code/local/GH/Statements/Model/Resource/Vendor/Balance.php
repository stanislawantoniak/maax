<?php

/**
 * Class GH_Statements_Model_Resource_Vendor_Balance
 */
class GH_Statements_Model_Resource_Vendor_Balance extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('ghstatements/vendor_balance', 'id');
    }

    /**
     * Perform actions after object save
     *
     * @param Varien_Object $object
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        //Mage::log($object->getData(), null, "beforeSave.log");
        $paymentFromClient = $object->getData("payment_from_client");
        $paymentReturnToClient = $object->getData("payment_return_to_client");
        $vendorPaymentCost = $object->getData("vendor_payment_cost");
        $vendorInvoiceCost = $object->getData("vendor_invoice_cost");

        $balancePerMonth = $paymentFromClient - $paymentReturnToClient - $vendorPaymentCost - $vendorInvoiceCost;

        $object->setData("balance_per_month", $balancePerMonth);
        //$object->setData("balance_cumulative", 0);
        //$object->setData("balance_due", 0);
        return parent::_beforeSave($object);
    }
}