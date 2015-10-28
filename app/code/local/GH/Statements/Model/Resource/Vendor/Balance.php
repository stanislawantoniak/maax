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
        //$object->setData("balance_due", 0);

        return parent::_beforeSave($object);
    }

    /**
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Core_Model_Resource_Db_Abstract
     * @throws Exception
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        $balances = Mage::getModel("ghstatements/vendor_balance")->getCollection();

        $balancesByVendor = array();
        foreach ($balances as $balance) {
            $balancesByVendor[$balance->getVendorId()][$balance->getDate()] = $balance->getData();
            arsort($balancesByVendor[$balance->getVendorId()]);
        }


        $balancesByVendorAccumulative = array();
        foreach ($balancesByVendor as $vendor => $data) {
            foreach ($data as $month => $monthData) {
                $balancesByVendorAccumulative[$vendor][$month] = $monthData["balance_per_month"] + (isset($balancesByVendorAccumulative[$vendor]) ? array_sum($balancesByVendorAccumulative[$vendor]) : 0);
            }
        }


        //TODO remake to multiUpdate for better perfomance
        foreach ($balances as $balanceItem) {
            if ($balanceItem->getStatus() == GH_Statements_Model_Vendor_Balance::GH_VENDOR_BALANCE_STATUS_OPENED) {
                if (isset($balancesByVendorAccumulative[$balanceItem->getVendorId()][$balanceItem->getDate()])) {
                    $balanceItem->setData("balance_cumulative", $balancesByVendorAccumulative[$balanceItem->getVendorId()][$balanceItem->getDate()]);
                    $balanceItem->save();
                }
            }
        }
        return parent::_afterLoad($object);
    }
}