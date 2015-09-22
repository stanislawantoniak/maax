<?php

/**
 * Class Zolago_Catalog_Helper_Attribute
 */
class Zolago_Catalog_Helper_Attribute extends Mage_Core_Helper_Abstract
{
    /**
     * Returns store used for labels values and vendor
     * @param Zolago_Dropship_Model_Vendor $vendor
     * @param Mage_Core_Model_Store
     * @return Mage_Core_Model_Store
     */
    public function getLabelStore($vendor, $backupStore){
        $key = "label_store_" . $vendor->getId();
        if(!$this->getData($key)){
            $store = null;
            if($vendor && $vendor->getLabelStore()){
                Mage::helper('udropship')->loadCustomData($vendor);
                $store = Mage::app()->getStore($vendor->getLabelStore());
            }
            if(!$store || !$store->getId()) {
                $store = $backupStore;
            }
            $this->setData($key, $store);
        }
        return $this->getData($key);
    }
}