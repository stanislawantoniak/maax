<?php

/**
 * Class Zolago_Payment_Model_Observer
 */
class Zolago_Payment_Model_Observer
{

    public static function processRefunds()
    {
        $configValue = Mage::getStoreConfig('payment_refunds/payment_refunds_automatic/interval');

        $collection = Mage::getResourceModel("zolagopayment/allocation_collection");
        $collection->addFieldToFilter('allocation_type', Zolago_Payment_Model_Allocation::ZOLAGOPAYMENT_ALLOCATION_TYPE_OVERPAY);
        $collection->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(
                array(
                    'max_allocation_amount' => new Zend_Db_Expr('SUM(allocation_amount)'),
                    'customer_id',
                    'vendor_id',
                    'created_at_hours_past' => new Zend_Db_Expr('(UNIX_TIMESTAMP()-UNIX_TIMESTAMP(MAX(created_at))) /3600'),
                    'allocation_type',
                    'po_id',
                    'transaction_id'
                )
            )
            ->group(array('transaction_id'))
            ->having('max_allocation_amount>0')
            ->having("created_at_hours_past >= {$configValue}");

        if (count($collection) > 0) {
            //make refunds
        }

    }
}