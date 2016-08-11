<?php

/**
 * Class Zolago_Payment_Block_Adminhtml_Vendor_Invoice
 */
class Zolago_Payment_Block_Adminhtml_Vendor_Invoice extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'zolagopayment';
        $this->_controller = 'adminhtml_vendor_invoice';
        $this->_headerText = Mage::helper('zolagopayment')->__('Vendor Invoices');
        $this->_addButtonLabel = Mage::helper('zolagopayment')->__('Add new vendor invoice');
        parent::__construct();
    }

    public function getCreateUrl()
    {
        return $this->getUrl('*/*/edit');
    }
}

