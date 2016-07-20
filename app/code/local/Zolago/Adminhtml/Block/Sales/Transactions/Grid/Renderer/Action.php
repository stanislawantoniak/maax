<?php
class Zolago_Adminhtml_Block_Sales_Transactions_Grid_Renderer_Action
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
{
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
        }

        $this->getColumn()->setActions($actions);

        return parent::render($row);
    }
}
