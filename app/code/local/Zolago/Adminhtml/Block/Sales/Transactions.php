<?php

/**
 * Class Zolago_Adminhtml_Block_Sales_Transactions
 */
class Zolago_Adminhtml_Block_Sales_Transactions
    extends Mage_Adminhtml_Block_Sales_Transactions
{
    public function __construct()
    {
        parent::__construct();

        $this->_addButton('add_bank_transfer', array(
            'label'     => $this->__("Add Bank Transfer Transaction"),
            'onclick'   => 'setLocation(\'' . $this->getAddBankTransferTransactionUrl() .'\')',
            'class'     => 'add',
        ));
    }


    public function getAddBankTransferTransactionUrl()
    {
        return $this->getUrl('*/*/edit');
    }
}