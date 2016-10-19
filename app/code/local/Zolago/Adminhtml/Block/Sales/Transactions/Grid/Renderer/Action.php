<?php
class Zolago_Adminhtml_Block_Sales_Transactions_Grid_Renderer_Action
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
{
    protected function _useAllocation() {
        return Mage::helper('zolagopayment')->getConfigUseAllocation();
    }
    public function render(Varien_Object $row)
    {        
        $actions = array();
        if($row->getData('method') == Zolago_Adminhtml_Block_Sales_Transactions::PAYMENT_TYPE_BANK_TRANSFER){
            $actions[] = array(
                'caption' => Mage::helper('catalog')->__('Edit'),
                'url'     => array(
                    'base'=>'adminhtml/sales_transactions/edit',
                    'params'=>array('store'=>$this->getRequest()->getParam('store'))
                ),
                'field'   => 'txn_id'
            );
            if (!$this->_useAllocation() &&
                ($row->getData('txn_status') == Zolago_Payment_Model_Client::TRANSACTION_STATUS_NEW)) {
                $actions[] = array(
                    'caption' => Mage::helper('catalog')->__('Confirm refund'),
                    'url'     => array(
                        'base'=>'*/payment/confirm',
                        'params'=>array('store'=>$this->getRequest()->getParam('store'))
                    ),
                    'field'   => 'txn_id'
                );                
            }
            
        } elseif ($row->getData('method') == Zolago_Adminhtml_Block_Sales_Transactions::PAYMENT_TYPE_DOTPAY) {
            if (!$this->_useAllocation() &&
                ($row->getData('txn_status') == Zolago_Payment_Model_Client::TRANSACTION_STATUS_NEW)) {
                $actions[] = array(
                    'caption' => Mage::helper('catalog')->__('Make refund'),
                    'url'     => array(
                        'base'=>'*/payment/refund',
                        'params'=>array('store'=>$this->getRequest()->getParam('store'))
                    ),
                    'field'   => 'txn_id'
                );                
            }
        }
        
        $this->getColumn()->setActions($actions);

        return parent::render($row);
    }
}
