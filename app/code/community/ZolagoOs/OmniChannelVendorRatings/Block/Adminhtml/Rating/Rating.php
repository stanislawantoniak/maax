<?php

class ZolagoOs_OmniChannelVendorRatings_Block_Adminhtml_Rating_Rating extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    protected $_blockGroup = 'udratings';
    public function __construct()
    {
        $this->_controller = 'adminhtml_rating';
        $this->_headerText = Mage::helper('rating')->__('Manage Ratings');
        $this->_addButtonLabel = Mage::helper('rating')->__('Add New Rating');
        parent::__construct();
    }
}
