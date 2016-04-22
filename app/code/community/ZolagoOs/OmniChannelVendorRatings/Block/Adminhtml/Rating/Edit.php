<?php

class ZolagoOs_OmniChannelVendorRatings_Block_Adminhtml_Rating_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    protected $_blockGroup = 'udratings';
    public function __construct()
    {
        parent::__construct();
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_rating';

        $this->_updateButton('save', 'label', Mage::helper('rating')->__('Save Rating'));
        $this->_updateButton('delete', 'label', Mage::helper('rating')->__('Delete Rating'));

        if( $this->getRequest()->getParam($this->_objectId) ) {

            $ratingData = Mage::getModel('rating/rating')
                ->load($this->getRequest()->getParam($this->_objectId));

            Mage::register('rating_data', $ratingData);
        }


    }

    public function getHeaderText()
    {
        if( Mage::registry('rating_data') && Mage::registry('rating_data')->getId() ) {
            return Mage::helper('rating')->__("Edit Rating", $this->htmlEscape(Mage::registry('rating_data')->getRatingCode()));
        } else {
            return Mage::helper('rating')->__('New Rating');
        }
    }
}
