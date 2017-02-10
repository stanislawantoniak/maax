<?php
class Orba_Informwhenavailable_Model_Entry extends Mage_Core_Model_Abstract {
    
    protected $products = array();
    protected $available = array();
    
    protected function _construct() {
        $this->_init('informwhenavailable/entry');
    }
    
    protected function getConfig() {
        return Mage::getModel('informwhenavailable/config');
    }
    
    public function isRequestAlreadySent($product, $email = null) {
        $sku = $product->getSku();
        $store_id = Mage::app()->getStore()->getId();
        $collection = $this->getCollection()
                ->addFieldToFilter('sku', $sku)
                ->addFieldToFilter('store_id', $store_id)
                ->addFieldToFilter('is_active', true)
                ->setPageSize(1)
                ->setCurPage(1);
        if (Mage::helper('customer')->isLoggedIn()) {
            $customer_id = Mage::getSingleton('customer/session')->getCustomer()->getId();
            $collection->addFieldToFilter('customer_id', $customer_id);
        } else if ($email) {
            $collection->addFieldToFilter('email', $email);
        } else {
            return false;
        }
        return (bool)count($collection);
    }
    
    public function informIfAvailable() {
        ini_set('max_execution_time', 300);
        $collection = $this->getCollection()
                ->addFieldToFilter('is_active', true);
        foreach ($collection as $entry) {
            if (!isset($this->products[$entry->getStoreId()])) {
                $this->products[$entry->getStoreId()] = array();
            }
            if (!isset($this->products[$entry->getStoreId()][$entry->getSku()])) {
                $this->products[$entry->getStoreId()][$entry->getSku()] = $entry->getSku();
            }
        }
        foreach ($this->products as $store_id => $sku_array) {
            if (!empty($sku_array)) {
                $products_collection = Mage::getModel('catalog/product')->getCollection()
                        ->addStoreFilter($store_id)
                        ->addAttributeToSelect('name')
                        ->addAttributeToSelect('visibility')
                        ->addAttributeToSelect('status')
                        ->addAttributeToSelect('sku')
                        ->addAttributeToFilter('sku', array('in' => $sku_array));
                $products_collection = $this->updateCollection($products_collection);
				//Add Stock Filter to get Only Saleable Products
				Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($products_collection);
				
                foreach ($products_collection as $product) {
                    if ($this->isAvailable($product)) {
                        if (!isset($this->available[$store_id])) {
                            $this->available[$store_id] = array();
                        }
                        if (!isset($this->available[$store_id][$product->getSku()])) {
                            $this->available[$store_id][$product->getSku()] = $product;
                        }
                    }
                }
            }
        }
        if (!empty($this->available)) {
            foreach ($collection as $entry) {
                if (isset($this->available[$entry->getStoreId()][$entry->getSku()])) {
                    $this->informSingle($entry, $this->available[$entry->getStoreId()][$entry->getSku()]);
                }
            }
        }
    }
    
    protected function updateCollection($collection) {
        return $collection;
    }
    
    public function isAvailable($product) {
        return true;// $product->isSaleable();
    }
    
    protected function informSingle($entry, $product) {
        $config_email_template = $this->getConfig()->getEmailTemplate($entry->getStoreId());
        if ($config_email_template) {
            $email_template = Mage::getModel('core/email_template')
                    ->loadByCode($config_email_template);
        } else {
            $email_template = Mage::getModel('core/email_template')
                    ->loadDefault('inform_when_available_template');
        }
        $email_template->setDesignConfig(array('area'=>'frontend','store'=> $entry->getStoreId()));
        $email_template
                ->setSenderName($this->getConfig()->getSenderName($entry->getStoreId()))
                ->setSenderEmail($this->getConfig()->getSenderEmail($entry->getStoreId()))
                ->setTemplateSubject($this->getConfig()->getEmailSubject($entry->getStoreId()));
        $attributeValue = $product->getResource()->getAttributeRawValue($product->getId(), 'size', $entry->getStoreId());
        $attribute = $product->getResource()->getAttribute('size');
        $size = $attribute->getSource()->getOptionText($attributeValue);
        // localization
        Mage::app()->setCurrentStore($entry->getStoreId());
        $currentStore 	= Mage::app()->getStore()->getCode();
        $currentLocale 	= Mage::getModel('core/locale')->getLocaleCode();
        Mage::app()->getLocale()->setLocale($currentLocale);
        Mage::app()->getTranslator()->init('frontend', true);
        $parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId());
        if (!empty($parentIds[0])) {
            $product = Mage::getModel('catalog/product')->load($parentIds[0]);
        }
        $email_template_variables = array(
            'product_name' => Mage::helper('wfwf')->__('%s size %s',$product->getName(),$size),
            'product_url' => $product->getProductUrl(),
            'store' => Mage::app()->getStore($entry->getStoreId())
        );
        if ($email_template->send($entry->getEmail(), $entry->getEmail(), $email_template_variables)) {
            $datetime = date('Y-m-d H:i:s', time());
            $entry->setIsActive(0);
            $entry->setUpdatedAt($datetime);
            $entry->save();
        }
    }
    
}