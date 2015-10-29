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
     */
    public function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $this->recalculateBalanceCumulative();
        return parent::_afterSave($object);
    }


    /**
     * balance_cumulative
     */
    public function recalculateBalanceCumulative()
    {
        $balances = Mage::getModel("ghstatements/vendor_balance")->getCollection();

        $balancesByVendor = array();
        foreach ($balances as $balance) {
            $balancesByVendor[$balance->getVendorId()][$balance->getDate()] = $balance->getData();
            arsort($balancesByVendor[$balance->getVendorId()]);
        }

        foreach ($balancesByVendor as $vendor => $data) {
            $i = 0;

            foreach ($data as $month => $monthData) {
                if ($i == 0) {
                    $balancesByVendor[$vendor][$month]["balance_cumulative"] = $monthData["balance_per_month"];
                } else {
                    $balancesByVendor[$vendor][$month]["balance_cumulative"] = $monthData["balance_per_month"] + array_values($balancesByVendor[$vendor])[$i - 1]["balance_cumulative"];
                }
                $i++;
            }
        }

        //TODO remake to multiUpdate for better performance
        foreach ($balances as $balanceItem) {
            if ($balanceItem->getStatus() == GH_Statements_Model_Vendor_Balance::GH_VENDOR_BALANCE_STATUS_OPENED) {
                if (isset($balancesByVendor[$balanceItem->getVendorId()][$balanceItem->getDate()]["balance_cumulative"])) {

                    try {
                        $where = $this->_getWriteAdapter()->quoteInto("id=?", $balanceItem->getId());
                        $this->_getWriteAdapter()
                            ->update($this->getMainTable(),
                                array("balance_cumulative" => $balancesByVendor[$balanceItem->getVendorId()][$balanceItem->getDate()]["balance_cumulative"]),
                                $where);
                    } catch (Exception $e) {
                        Mage::logException($e);
                    }
                }
            }
        }

    }
}