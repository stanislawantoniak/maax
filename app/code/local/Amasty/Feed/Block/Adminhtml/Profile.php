<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */ 
class Amasty_Feed_Block_Adminhtml_Profile extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_profile';
        $this->_headerText = Mage::helper('amfeed')->__('Manage Feeds');
        $this->_blockGroup = 'amfeed';
        parent::__construct();
    }
}