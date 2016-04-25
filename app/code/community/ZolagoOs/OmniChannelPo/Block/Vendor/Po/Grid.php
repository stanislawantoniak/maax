<?php
/**
  
 */
 
class ZolagoOs_OmniChannelPo_Block_Vendor_Po_Grid extends Mage_Core_Block_Template
{
    protected $_collection;

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($toolbar = $this->getLayout()->getBlock('udpo.grid.toolbar')) {
            $toolbar->setCollection(Mage::helper('udpo')->getVendorPoCollection());
            $this->setChild('toolbar', $toolbar);
        }

        return $this;
    }
}