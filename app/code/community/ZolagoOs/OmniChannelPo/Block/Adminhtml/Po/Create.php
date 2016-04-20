<?php
/**
  
 */
 
class ZolagoOs_OmniChannelPo_Block_Adminhtml_Po_Create extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'udpo';
        $this->_objectId = 'order_id';
        $this->_controller = 'adminhtml_po';
        $this->_mode = 'create';

        parent::__construct();

        $this->_removeButton('save');
        $this->_removeButton('delete');
    }

    public function getOrder()
    {
        return Mage::registry('current_order');
    }

    public function getHeaderText()
    {
        $header = Mage::helper('udpo')->__('New Purchase Orders for Order #%s', $this->getOrder()->getRealOrderId());
        return $header;
    }

    public function getBackUrl()
    {
        return $this->getUrl('adminhtml/sales_order/view', array('order_id'=>$this->getOrder()->getId()));
    }
}