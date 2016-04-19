<?php

class ZolagoOs_OmniChannelVendorRatings_Block_Adminhtml_Review_Main extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    protected $_blockGroup = 'udratings';
    public function __construct()
    {
        parent::__construct();

        $this->_controller = 'adminhtml_review';

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
                $this->_headerText = Mage::helper('udratings')->__('Pending Vendor Reviews of Customer `%s`', $customerName);
            } else {
                $this->_headerText = Mage::helper('udratings')->__('Pending Vendor Reviews');
            }
        } else {
            if ($customerName) {
                $this->_headerText = Mage::helper('udratings')->__('All Vendor Reviews of Customer `%s`', $customerName);
            } else {
                $this->_headerText = Mage::helper('udratings')->__('All Vendor Reviews');
            }
        }
    }
}
