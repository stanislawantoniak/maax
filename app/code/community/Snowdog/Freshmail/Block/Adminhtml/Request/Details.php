<?php

class Snowdog_Freshmail_Block_Adminhtml_Request_Details extends Mage_Adminhtml_Block_Widget_Container
{
    /**
     * Add buttons
     */
    public function __construct()
    {
        parent::__construct();

        $this->_addButton('back', array(
            'label'   => Mage::helper('snowfreshmail')->__('Back'),
            'onclick' => "setLocation('" . $this->getUrl('*/*/'). "')",
            'class'   => 'back'
        ));
    }

    /**
     * Header text getter
     *
     * @return string
     */
    public function getHeaderText()
    {
        if ($this->getCurrentRequest()) {
            return Mage::helper('snowfreshmail')->__('Request #%d', $this->getCurrentRequest()->getId());
        }

        return Mage::helper('snowfreshmail')->__('Request Details');
    }

    /**
     * Retrieve current request
     *
     * @return Snowdog_Freshmail_Model_Api_Request
     */
    public function getCurrentRequest()
    {
        return Mage::registry('current_request');
    }
}
