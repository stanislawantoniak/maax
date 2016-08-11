<?php

/**
 * Class GH_Integrator_Block_Adminhtml_Ghintegrator_Log
 */
class GH_Integrator_Block_Adminhtml_Ghintegrator_Log extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'ghintegrator';
        $this->_controller = 'adminhtml_ghintegrator_log';
        $this->_headerText = Mage::helper('ghintegrator')->__('GH Integrator Log');
        parent::__construct();
    }
}

