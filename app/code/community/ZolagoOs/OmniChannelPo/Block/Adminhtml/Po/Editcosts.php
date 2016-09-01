<?php
/**
  
 */

class ZolagoOs_OmniChannelPo_Block_Adminhtml_Po_Editcosts extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'udpo';
        $this->_objectId = 'order_id';
        $this->_controller = 'adminhtml_po';
        $this->_mode = 'editcosts';

        parent::__construct();

        $this->_removeButton('save');
    }

    public function getPo()
    {
        return Mage::registry('current_udpo');
    }

    public function getOrder()
    {
        return Mage::registry('current_order');
    }

    public function getHeaderText()
    {
        $header = Mage::helper('udpo')->__('Edit Costs for Purchase Orders #%s', $this->getPo()->getIncrementId());
        return $header;
    }

    public function getBackUrl()
    {
        return $this->getUrl('zospoadmin/order_po/view', array('udpo_id'=>$this->getPo()->getId()));
    }
}