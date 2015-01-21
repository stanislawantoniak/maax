<?php

/**
 * Class Zolago_Po_Block_Vendor_Po_Edit_Payments
 */
class Zolago_Po_Block_Vendor_Po_Edit_Payments
    extends Zolago_Po_Block_Vendor_Po_Edit
{

    /**
     * @return Zolago_Payment_Model_Resource_Allocation_Collection
     */
    public function getPaymentDetails()
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
        $allocationCollection->addFieldToFilter('po_id', $this->getPo()->getId());

        return $allocationCollection;
    }
}
