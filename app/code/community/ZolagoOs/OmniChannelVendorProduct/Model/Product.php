<?php

class ZolagoOs_OmniChannelVendorProduct_Model_Product extends Mage_Catalog_Model_Product
{
    protected function _construct()
    {
        $this->_init('udprod/product');
    }
    public function resetTypeInstance()
    {
        $this->_typeInstanceSingleton = null;
        $this->_typeInstance = null;
        return $this;
    }
    
    /**
     * create url key
     * @see Mage_Catalog_Model_Attribute_Backend_Urlkey_Abstract::beforeSave()
     */
    protected function _beforeSave() {
        parent::_beforeSave();
        $urlKey = $this->getData('url_key');
        if (empty($urlKey)) {
            $name = $this->getResource()->getAttributeRawValue($this->getEntityId(),'name',Mage::app()->getStore()->getId()); 
            $urlKey = $name.' '.$this->getSkuv();
            $this->setData('url_key', $this->formatUrlKey($urlKey));
        }
    }

}