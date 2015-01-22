<?php

/**
 * payment helper
 */
class Zolago_Payment_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * @param $poId
     * @param string $plusType
     * @return Zolago_Payment_Model_Resource_Allocation_Collection
     */
    public function getPaymentDetails($poId)
    {
        $allocationCollection = $this->_getDetails($poId);
        $allocationCollection->addAllocationTypeFilter(Zolago_Payment_Model_Allocation::ZOLAGOPAYMENT_ALLOCATION_TYPE_PAYMENT);

        return $allocationCollection;
    }

    public function getOverpaymentDetails($poId) {

        $allocationCollection = $this->_getDetails($poId);
        $allocationCollection->addAllocationTypeFilter(Zolago_Payment_Model_Allocation::ZOLAGOPAYMENT_ALLOCATION_TYPE_OVERPAY);

        return $allocationCollection;
    }

    private function _getDetails($poId) {
        /* @var $allocationCollection Zolago_Payment_Model_Resource_Allocation_Collection */
        $allocationCollection = Mage::getModel('zolagopayment/allocation')
            ->getCollection();
        $allocationCollection->getSelect()
            ->join(
                'sales_payment_transaction',
                'main_table.transaction_id =sales_payment_transaction.transaction_id',
                array('sales_payment_transaction.txn_id'))
            ->joinLeft(
                'zolago_operator',
                'main_table.operator_id =zolago_operator.operator_id',
                array(
                    'zolago_operator.firstname',
                    'zolago_operator.lastname'
                ))
            ->joinLeft(
                'udropship_po',
                'main_table.po_id = udropship_po.entity_id',
                array('udropship_po.increment_id')
            );
        $allocationCollection->addFieldToFilter('po_id', $poId);

        return $allocationCollection;
    }

}