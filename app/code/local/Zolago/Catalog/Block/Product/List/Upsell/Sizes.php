<?php

/**
 * Class Zolago_Catalog_Block_Product_List_Upsell_Sizes
 */
class Zolago_Catalog_Block_Product_List_Upsell_Sizes extends Mage_Core_Block_Template 
{
    protected $_product;
    public function __construct() {
        $this->setTemplate('zolagocatalog/product/list/upsell/sizes.phtml');
        parent::__construct();
    }

    public function getProduct() {
        if (!$this->_product) {
            $this->_product = Mage::getModel('catalog/product')->load($this->getProductId());
        }
        return $this->_product;
    }
    public function getAttributes($_product) {
        $productAttrs = array();
        if ($_product->getTypeId() == 'configurable') {
            $productAttrs = $_product->getTypeInstance(true)->getConfigurableAttributesAsArray($_product);
        }
        return $productAttrs;
    }
    public function isInWishlist($product) {
        return Mage::helper('zolagowishlist')->productBelongsToMyWishlist($product);        
    }
}