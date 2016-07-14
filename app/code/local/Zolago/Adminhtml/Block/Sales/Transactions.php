<?php

/**
 * Class Zolago_Adminhtml_Block_Sales_Transactions
 */
class Zolago_Adminhtml_Block_Sales_Transactions
    extends Mage_Adminhtml_Block_Sales_Transactions
{
    const PAYMENT_TYPE_BANK_TRANSFER                = 'banktransfer';
    const ALLOW_SET_ORDER_FOR_EXISTING_TRANSACTIONS = 'allow_set_order_for_existing_transactions';

    public function __construct()
    {
        parent::__construct();

        $this->_addButton('add_bank_transfer', array(
            'label' => $this->__("Enter Bank Payment"),
            'onclick' => 'setLocation(\'' . $this->getAddBankTransferTransactionUrl() . '\')',
            'class' => 'add',
        ));
    }


    public function getAddBankTransferTransactionUrl()
    {
        return $this->getUrl('*/*/edit');
    }
}