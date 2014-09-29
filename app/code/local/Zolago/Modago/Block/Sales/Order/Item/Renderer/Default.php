<?php
/**
 * default order view renderer
 */
class Zolago_Modago_Block_Sales_Order_Item_Renderer_Default extends Mage_Sales_Block_Order_Item_Renderer_Default {

    
    //{{{ 
    /**
     * product
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct() {
        return $this->getItem()->getOrderItem()->getProduct();
    }
    //}}}
    //{{{
    /**
     * product name
     * @return string
     */
    public function getProductName() {
        $product = $this->getItem();
        return $product->getData('name');
    }
    //}}}
    //{{{
    /**
     * product thumbnail
     * @return string
     */
    public function getProductThumbnail() {
        $product = $this->getItem()->getOrderItem()->getProduct();
        return $this->helper('xmlconnect/catalog_product_image')->init($product, 'thumbnail');
    }
    //}}}

    /**
     * Get list of all otions for product
     *
     * @return array
     */
    public function getOptionList()
    {
        $item = $this->getItem()->getOrderItem();
        $options = $item->getProductOptions();
        return isset($options['attributes_info']) ? $options['attributes_info']:array();
    }

}