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
    public function getPaymentDetails($poId, $plusType = Zolago_Payment_Model_Allocation::ZOLAGOPAYMENT_ALLOCATION_TYPE_OVERPAY)
    {
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
                )
            );
        $allocationCollection->addFieldToFilter('po_id', $poId);
//        $allocationCollection->addAllocationTypeFilter(Zolago_Payment_Model_Allocation::ZOLAGOPAYMENT_ALLOCATION_TYPE_PAYMENT);
//        $allocationCollection->addAllocationTypeFilter($plusType);



        return $allocationCollection;
    }


}