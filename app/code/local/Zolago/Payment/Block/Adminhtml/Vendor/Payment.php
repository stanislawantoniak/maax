<?php

/**
 * Class Zolago_Payment_Block_Adminhtml_Vendor_Payment
 */
class Zolago_Payment_Block_Adminhtml_Vendor_Payment extends Mage_Adminhtml_Block_Widget_Grid_Container {


    public function __construct()
    {
        $this->_blockGroup = 'zolagopayment';
        $this->_controller = 'adminhtml_vendor_payment';
        $this->_headerText = Mage::helper('zolagopayment')->__('Vendor Payments');
        $this->_addButtonLabel = Mage::helper('zolagopayment')->__('Add new vendor payment');
        parent::__construct();
    }

    public function getCreateUrl()
    {
        return $this->getUrl('*/*/edit');
    }

}

