<?php
/**
  
 */

class ZolagoOs_OmniChannel_Model_Vendor_Product extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('udropship/vendor_product');
        parent::_construct();
    }

    public function getVendorCost()
    {
//        if (!$this->hasData('vendor_cost')) {
//            if ($this->getProductId()) {
//                $cost = Mage::getModel('catalog/product')->load($this->getProductId())->getCost();
//                $this->setData('vendor_cost', $cost);
//            }
//        }
        return $this->getData('vendor_cost');
    }
}