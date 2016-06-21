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
     */
    protected function _beforeSave() {
        parent::_beforeSave();
        $urlKey = $this->getData('url_key');
        if (empty($urlKey)) {
            $urlKey = $this->getName().' '.$this->getSkuv();
            $this->setData('url_key', $this->formatUrlKey($urlKey));
        }
    }

}