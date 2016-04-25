<?php

class ZolagoOs_OmniChannelPo_Block_Adminhtml_Shipment_CreateForm extends Mage_Adminhtml_Block_Sales_Order_Shipment_Create_Form
{
	public function getSaveUrl()
    {
        return $this->getUrl('*/*/saveShipment', array('udpo_id' => $this->getShipment()->getUdpoId()));
    }
}