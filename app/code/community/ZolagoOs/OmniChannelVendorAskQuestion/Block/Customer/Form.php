<?php

class ZolagoOs_OmniChannelVendorAskQuestion_Block_Customer_Form extends Mage_Core_Block_Template
{
    protected $_shipments;
    public function getShipments()
    {
        if (null === $this->_shipments) {
            $this->_shipments = Mage::getResourceModel('sales/order_shipment_collection');
            $this->_shipments->addFieldToFilter('customer_id', Mage::getSingleton('customer/session')->getCustomerId());
            $this->_shipments->addFieldToSelect(array(
                'order_id',
                'shipment_increment_id'=>'increment_id',
                'shipment_id'=>'entity_id'
            ));
            $this->_shipments->join(
                'sales/shipment_grid',
                '`sales/shipment_grid`.entity_id=main_table.entity_id',
                array('order_increment_id')
            );
        }
        return $this->_shipments;
    }
    public function getFormAction()
    {
        return $this->getUrl('udqa/customer/post');
    }
    public function getVendors()
    {
        return Mage::getSingleton('udropship/source')->getVendors(true);
    }
}
