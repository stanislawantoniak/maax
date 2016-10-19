<?php

/**
 */
class ZolagoOs_IAIShop_Block_Adminhtml_Zosiaishop_Log extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'zosiaishop';
        $this->_controller = 'adminhtml_zosiaishop_log';
        $this->_headerText = Mage::helper('zosiaishop')->__('IAI Integrator Log');
        parent::__construct();
    }
}

