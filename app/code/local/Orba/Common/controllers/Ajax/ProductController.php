<?php

class Orba_Common_Ajax_ProductController extends Orba_Common_Controller_Ajax {
    
    const GET_ONE_EXPIRE_TIME = 900;
    const GET_MANY_EXPIRE_TIME = 900;

    /*
     * Gets data of one product and set it to response body in JSON format. Available params:
     * - product_id (required if sku left blank)
     * - sku (required if product_id left blank)
     * - mode (optional, possible options: data, html, default data)
     * - current_category_id (optional)
     * - report_view (optional, default false)
     */
    public function get_oneAction() {
        try {
            $request = $this->getRequest();
            $result = array();
            $productId = $this->_getProductIdFromRequest();
            if ($productId) {
                $mode = $request->getParam('mode', Orba_Common_Model_Ajax_Product::GET_ONE_MODE_DATA);
                if ($mode && in_array($mode, array(Orba_Common_Model_Ajax_Product::GET_ONE_MODE_DATA, Orba_Common_Model_Ajax_Product::GET_ONE_MODE_HTML))) {
                    $categoryId = $request->getParam('current_category_id', Mage::app()->getStore()->getRootCategoryId());
                    $reportView = $request->getParam('report_view', false);
                    $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
                    $product = $this->_initProduct($productId, $categoryId);
                    if ($product) {
                        $lastModified = strtotime($product->getUpdatedAt());
                    } else {
                        throw Mage::exception('Orba_Common', 'Product does not exist.');
                    }
                    $expires = self::GET_ONE_EXPIRE_TIME;
                    $storeId = (int) Mage::app()->getStore()->getId();
                    if (Mage::app()->useCache('ajax_response')) {
                        $cacheId = 'ajax-product-getone-' . $mode . '-' . $productId . '-' . $categoryId . '-' . $storeId . '-' . $customerGroupId;
                        if (false !== $cacheData = Mage::app()->loadCache($cacheId)) {
                            $result = unserialize($cacheData);
                        } else {
                            $result = $this->_generateGetOneResponse($product, $mode, $categoryId, $reportView);
                            $cacheContent = serialize($result);
                            $tags = array(
                                Orba_Common_Helper_Ajax::CACHE_TAG,
                                Mage_Catalog_Model_Product::CACHE_TAG,
                                Mage_Catalog_Model_Product::CACHE_TAG . '_' . $productId
                            );
                            Mage::app()->saveCache($cacheContent, $cacheId, $tags, $expires);
                        }
                    } else {
                        $result = $this->_generateGetOneResponse($product, $mode, $categoryId, $reportView);
                    }
                    $this->_setSuccessResponse($result, $expires / 2, $lastModified);
                } else {
                    throw Mage::exception('Orba_Common', 'Mode has not been specified.');
                }
            } else {
                throw Mage::exception('Orba_Common', 'No product has been specified.');
            }
        } catch (Exception $e) {
            $this->_processException($e);
        }
    }
    
    /**
     * Gets data of many products and set it to response body in JSON format. Available params:
     * - category_id (optional)
     */
    public function get_manyAction() {
        try {
            $request = $this->getRequest();
            $result = array();
            $categoryId = $request->getParam('category_id', null);
            $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
            $collection = $this->_initProductsCollection($categoryId);
            $expires = self::GET_MANY_EXPIRE_TIME;
            $storeId = (int) Mage::app()->getStore()->getId();
            if (Mage::app()->useCache('ajax_response')) {
                $cacheId = 'ajax-product-getmany-' . $categoryId . '-' . $storeId . '-' . $customerGroupId;
                if (false !== $cacheData = Mage::app()->loadCache($cacheId)) {
                    $result = unserialize($cacheData);
                } else {
                    $result = $this->_generateGetManyResponse($collection);
                    $cacheContent = serialize($result);
                    $tags = array(
                        Orba_Common_Helper_Ajax::CACHE_TAG,
                        Mage_Catalog_Model_Product::CACHE_TAG,
                        Mage_Catalog_Model_Category::CACHE_TAG,
                        Mage_Catalog_Model_Category::CACHE_TAG . '_' . $categoryId
                    );
                    Mage::app()->saveCache($cacheContent, $cacheId, $tags, $expires);
                }
            } else {
                $result = $this->_generateGetManyResponse($collection);
            }
            $this->_setSuccessResponse($result, $expires / 2);
        } catch (Exception $e) {
            $this->_processException($e);
        }
    }
    
    /**
     * Initializes product object
     * 
     * @param int $productId
     * @param int $categoryId
     * @return Mage_Catalog_Model_Product
     */
    protected function _initProduct($productId, $categoryId) {
        $params = new Varien_Object();
        $params->setCategoryId($categoryId);
        return Mage::helper('catalog/product')->initProduct($productId, $this, $params);
    }
    
    /**
     * Initializes product layout object
     * 
     * @param Mage_Catalog_Model_Product $product
     * @param string $currentCategoryId
     */
    protected function _initProductLayout($product, $currentCategoryId = null) {
        $design = Mage::getSingleton('catalog/design');
        $settings = $design->getDesignSettings($product);

        if ($settings->getCustomDesign()) {
            $design->applyCustomDesign($settings->getCustomDesign());
        }

        $update = $this->getLayout()->getUpdate();
        $update->addHandle('default');
        $update->addHandle('catalog_product_view');
        $this->addActionLayoutHandles();

        $update->addHandle('PRODUCT_TYPE_' . $product->getTypeId());
        $update->addHandle('PRODUCT_' . $product->getId());
        $this->loadLayoutUpdates();

        // Apply custom layout update once layout is loaded
        $layoutUpdates = $settings->getLayoutUpdates();
        if ($layoutUpdates) {
            if (is_array($layoutUpdates)) {
                foreach($layoutUpdates as $layoutUpdate) {
                    $update->addUpdate($layoutUpdate);
                }
            }
        }

        $this->generateLayoutXml()->generateLayoutBlocks();

        // Apply custom layout (page) template once the blocks are generated
        if ($settings->getPageLayout()) {
            $this->getLayout()->helper('page/layout')->applyTemplate($settings->getPageLayout());
        }

        $root = $this->getLayout()->getBlock('root');
        if ($root) {
            $root->addBodyClass('catalog-product-view');
            $root->addBodyClass('product-' . $product->getUrlKey());
            if ($currentCategoryId) {
                $currentCategory = Mage::getModel('catalog/category')->load($currentCategoryId);
                if ($currentCategory->getId()) {
                    $root->addBodyClass('categorypath-' . $currentCategory->getUrlPath())
                    ->addBodyClass('category-' . $currentCategory->getUrlKey());
                }
            }
        }
    }
    
    /**
     * Generates response array for getOne method
     * 
     * @param Mage_Catalog_Model_Product $product
     * @param string $mode
     * @param bool $reportView
     * @return array
     */
    protected function _generateGetOneResponse($product, $mode, $currentCategoryId, $reportView) {
        if ($reportView && Mage::helper('catalog/product')->canShow($product)) {
            Mage::dispatchEvent('catalog_controller_product_view', array('product' => $product));
            Mage::getSingleton('catalog/session')->setLastViewedProductId($product->getId());
        }
        switch ($mode) {
            case Orba_Common_Model_Ajax_Product::GET_ONE_MODE_DATA:
                $data = Mage::getSingleton('orbacommon/ajax_product')->getOneData($product);
                break;
            case Orba_Common_Model_Ajax_Product::GET_ONE_MODE_HTML:
                $this->_initProductLayout($product, $currentCategoryId);
                $data = Mage::getSingleton('orbacommon/ajax_product')->getOneHtml($product, $this->getLayout());
                break;
        }
        $result = $this->_formatSuccessContentForResponse($data);
        return $result;
    }
    
    /**
     * Initializates products collection
     * 
     * @param int $categoryId
     * @return Mage_Catalog_Model_Resource_Product_Collection
     * @throws Orba_Common_Exception
     */
    protected function _initProductsCollection($categoryId = null) {
        $category = null;
        if ($categoryId) {
            $category = Mage::getModel('catalog/category')->load($categoryId);
            if (!$category->getId()) {
                throw Mage::exception('Orba_Common', 'Category does not exist');
            }
        }
        $collection = Mage::getSingleton('orbacommon/ajax_product')->getCollection($category);
        if ($category) {
            Mage::register('current_category', $category);
        }
        return $collection;
    }
    
    /**
     * Generates response array for getMany method
     * 
     * @param Mage_Catalog_Model_Resource_Product_Collection $collection
     * @return array
     */
    protected function _generateGetManyResponse($collection) {
        $data = Mage::getSingleton('orbacommon/ajax_product')->getManyData($collection);
        $result = $this->_formatSuccessContentForResponse($data);
        return $result;
    }
    
}