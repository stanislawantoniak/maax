<?php

class Orba_Common_Model_Ajax_Product extends Mage_Core_Model_Abstract {
    
    const GET_ONE_MODE_DATA = 'data';
    const GET_ONE_MODE_HTML = 'html';
    
    /**
     * Gets array of product data and URL
     * 
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    public function getOneData($product) {
        return array(
            'data' => $product->getData(),
            'url' => $product->getProductUrl()
        );
    }
    
    /**
     * Gets array of HTML blocks related to specified product as well as body class and URL
     * 
     * @param Mage_Catalog_Model_Product $product
     * @param Mage_Core_Model_Layout $layout
     * @return array
     */
    public function getOneHtml($product, $layout) {
        return array(
            'html' => array(
                'product_info' => $layout->getBlock('product.info')->toHtml(),
                'product_related' => $layout->getBlock('catalog.product.related')->toHtml(),
                'breadcrumbs' => $layout->getBlock('breadcrumbs')->toHtml()
            ),
            'bodyClass' => $layout->getBlock('root')->getBodyClass(),
            'url' => $product->getProductUrl()
        );
    }
    
    /**
     * Gets array of products data in collection
     * 
     * @param Mage_Catalog_Model_Resource_Product_Collection $collection
     * @return array
     */
    public function getManyData($collection) {
        $data = array();
        foreach ($collection as $product) {
            $data[] = array(
                'data' => $product->getData(),
                'url' => $product->getProductUrl()
            );
        }
        return $data;
    }
    
    /**
     * Gets collection of products 
     * 
     * @param Mage_Catalog_Model_Category $category
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function getCollection($category = null) {
        $storeId = (int) Mage::app()->getStore()->getId();
        $collection = Mage::getResourceModel('catalog/product_collection')
                ->setStoreId($storeId)
                ->addAttributeToSelect('*');
        if ($category) {
            $collection->addCategoryFilter($category);
        }
        return $collection;
    }
    
}