<?php
/**
  
 */
 
class ZolagoOs_Rma_Block_Vendor_Rma_Grid extends Mage_Core_Block_Template
{
    protected $_collection;

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($toolbar = $this->getLayout()->getBlock('urma.grid.toolbar')) {
            $toolbar->setCollection(Mage::helper('urma')->getVendorRmaCollection());
            $this->setChild('toolbar', $toolbar);
        }

        return $this;
    }
}