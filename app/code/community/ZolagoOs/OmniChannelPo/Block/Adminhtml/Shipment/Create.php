<?php
/**
  
 */
 
class ZolagoOs_OmniChannelPo_Block_Adminhtml_Shipment_Create extends Mage_Adminhtml_Block_Sales_Order_Shipment_Create
{
	public function getHeaderText()
    {
        $header = Mage::helper('udpo')->__('New Shipment for PO #%s [Order #%s]',
        	$this->getShipment()->getUdpo()->getIncrementId(), 
        	$this->getShipment()->getOrder()->getRealOrderId()
        );
        return $header;
    }

    public function getBackUrl()
    {
        return $this->getUrl('*/*/view', array('udpo_id'=>$this->getShipment()->getUdpoId()));
    }
}