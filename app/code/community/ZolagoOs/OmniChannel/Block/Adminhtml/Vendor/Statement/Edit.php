<?php
/**
  
 */

class ZolagoOs_OmniChannel_Block_Adminhtml_Vendor_Statement_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->_objectId = 'id';
        $this->_blockGroup = 'udropship';
        $this->_controller = 'adminhtml_vendor_statement';

        $this->_updateButton('delete', 'label', Mage::helper('udropship')->__('Delete Statement'));
        $model = Mage::getModel('udropship/vendor_statement')
            ->load($this->getRequest()->getParam($this->_objectId));
        Mage::register('statement_data', $model);
        if (Mage::helper('udropship')->isUdpayoutActive()) {
            $this->_addButton('save_pay', array(
                'id'      => 'statement_save_pay_btn', 
                'label'   => Mage::helper('adminhtml')->__('Save and Pay'),
                'onclick'   => "\$('pay_flag').value=1; editForm.submit();",
                'class'   => 'save',
            ), 1);
        }
        $this->_addButton('save_refresh', array(
            'id'      => 'statement_save_refresh_btn', 
            'label'   => Mage::helper('adminhtml')->__('Save and Refresh'),
            'onclick'   => "\$('refresh_flag').value=1; editForm.submit();",
            'class'   => 'save',
        ), 1);
    }

    public function getHeaderText()
    {
        return Mage::helper('udropship')->__('Statement');
    }
}