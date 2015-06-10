<?php

/**
 * Class Zolago_Payment_Model_Refund
 */
class Zolago_Payment_Model_Refund extends Zolago_Payment_Model_Allocation
{
    /**
     * @return Zolago_Payment_Model_Resource_Allocation_Collection
     */
    public function getTransactionLastOverpayments()
    {
        $configValue = Mage::getStoreConfig('payment_refunds/payment_refunds_automatic/interval');

        $collection = Mage::getResourceModel("zolagopayment/allocation_collection");
        $collection->addFieldToFilter('allocation_type', Zolago_Payment_Model_Allocation::ZOLAGOPAYMENT_ALLOCATION_TYPE_OVERPAY);
        $collection->getSelect()
            ->join(
                array(
                    'po' => 'udropship_po'),
                'main_table.po_id = po.entity_id'
            )
            ->join(
                array(
                    'payment_transaction' => 'sales_payment_transaction'),
                'main_table.transaction_id = payment_transaction.transaction_id'
            )
	        ->join(
		        array(
			        'rma_allocations' => 'zolago_payment_allocation'),
		        'main_table.transaction_id = rma_allocations.transaction_id && NOT_NULL(rma_allocations.rma_id)'
	        )
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(
                array(
                    'max_allocation_amount' => new Zend_Db_Expr('SUM(main_table.allocation_amount)'),
                    'main_table.customer_id',
                    'main_table.vendor_id',
                    'created_at_hours_past' => new Zend_Db_Expr('(UNIX_TIMESTAMP()-UNIX_TIMESTAMP(MAX(main_table.created_at))) /3600'),
                    'main_table.allocation_type',
                    'main_table.po_id',
                    'po.order_id',
                    'main_table.transaction_id',
                    'payment_transaction.txn_id',
	                'rma_allocations.rma_id'
                )
            )
            ->group(array('transaction_id'))
            ->having('max_allocation_amount>0')
            ->having("created_at_hours_past >= {$configValue}");
	    Mage::log((string)$collection->getSelect(),null,'refund_sql.log');
        return $collection;
    }
}