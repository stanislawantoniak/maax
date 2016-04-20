<?php

class ZolagoOs_OmniChannel_Model_Po extends Mage_Sales_Model_Order_Shipment
{
    protected function _construct()
    {
        $this->_init('udropship/po');
    }
}