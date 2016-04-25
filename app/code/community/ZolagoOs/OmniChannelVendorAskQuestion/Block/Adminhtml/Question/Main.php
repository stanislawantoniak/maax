<?php

class ZolagoOs_OmniChannelVendorAskQuestion_Block_Adminhtml_Question_Main extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    protected $_blockGroup = 'udqa';
    public function __construct()
    {
        parent::__construct();

        $this->_controller = 'adminhtml_question';

        // lookup customer, if id is specified
        $customerId = $this->getRequest()->getParam('customerId', false);
        $customerName = '';
        if ($customerId) {
            $customer = Mage::getModel('customer/customer')->load($customerId);
            $customerName = $customer->getFirstname() . ' ' . $customer->getLastname();
        }

        $this->_removeButton('add');
        if( Mage::registry('usePendingFilter') === true ) {
            if ($customerName) {
                $this->_headerText = Mage::helper('udqa')->__('Pending Vendor Questions of Customer `%s`', $customerName);
            } else {
                $this->_headerText = Mage::helper('udqa')->__('Pending Vendor Questions');
            }
        } else {
            if ($customerName) {
                $this->_headerText = Mage::helper('udqa')->__('All Vendor Questions of Customer `%s`', $customerName);
            } else {
                $this->_headerText = Mage::helper('udqa')->__('All Vendor Questions');
            }
        }
    }
}
