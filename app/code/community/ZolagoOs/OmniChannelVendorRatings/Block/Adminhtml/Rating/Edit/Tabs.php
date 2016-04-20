<?php

class ZolagoOs_OmniChannelVendorRatings_Block_Adminhtml_Rating_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('rating_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('rating')->__('Rating Information'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('form_section', array(
            'label'     => Mage::helper('rating')->__('Rating Information'),
            'title'     => Mage::helper('rating')->__('Rating Information'),
            'content'   => $this->getLayout()->createBlock('udratings/adminhtml_rating_edit_tab_form')->toHtml(),
        ))
        ;

        if( Mage::registry('rating_data') ) {
            $this->addTab('answers_section', array(
                    'label'     => Mage::helper('rating')->__('Rating Options'),
                    'title'     => Mage::helper('rating')->__('Rating Options'),
                    'content'   => $this->getLayout()->createBlock('udratings/adminhtml_rating_edit_tab_options')
                                    ->append($this->getLayout()->createBlock('udratings/adminhtml_rating_edit_tab_options'))
                                    ->toHtml(),
               ));
        }
        return parent::_beforeToHtml();
    }
}
