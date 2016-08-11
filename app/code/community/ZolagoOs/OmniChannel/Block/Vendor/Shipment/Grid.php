<?php
/**
  
 */

class ZolagoOs_OmniChannel_Block_Vendor_Shipment_Grid extends Mage_Core_Block_Template
{
    protected $_collection;

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($toolbar = $this->getLayout()->getBlock('shipment.grid.toolbar')) {
            $toolbar->setCollection(Mage::helper('udropship')->getVendorShipmentCollection());
            $this->setChild('toolbar', $toolbar);
        }

        return $this;
    }
}