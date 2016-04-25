<?php
/**
  
 */
 
class ZolagoOs_OmniChannelPo_Block_Adminhtml_Po_View_Tab_Shipments extends ZolagoOs_OmniChannel_Block_Adminhtml_Order_Shipments
{
    public function setCollection($collection)
    {
        $collection->addAttributeToFilter('udpo_id', $this->getPo()->getId());
        return parent::setCollection($collection);
    }
    public function getPo()
    {
        return Mage::registry('current_udpo');
    }
    public function getRowUrl($row)
    {
        return $this->getUrl(
            'adminhtml/sales_order_shipment/view',
            array(
                'shipment_id'=> $row->getId(),
                'order_id'  => $row->getOrderId()
             ));
    }
}