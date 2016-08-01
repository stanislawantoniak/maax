<?php

class Snowdog_Freshmail_Block_Adminhtml_Request extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_blockGroup = 'snowfreshmail';
        $this->_controller = 'adminhtml_request';
        $this->_headerText = $this->__('Request Logs');

        $this->_removeButton('add');
    }
}
