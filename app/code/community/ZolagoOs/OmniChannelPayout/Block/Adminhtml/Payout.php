<?php
/**
  
 */

class ZolagoOs_OmniChannelPayout_Block_Adminhtml_Payout extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {
        $this->_blockGroup = 'udpayout';
        $this->_controller = 'adminhtml_payout';
        $this->_headerText = Mage::helper('udpayout')->__('Vendor Payouts');
        $this->_addButtonLabel = Mage::helper('udpayout')->__('Generate Payouts');
        parent::__construct();
    }

}
