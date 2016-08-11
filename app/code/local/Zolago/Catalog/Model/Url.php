<?php

/**
 * Class Zolago_Catalog_Model_Url
 */
class Zolago_Catalog_Model_Url extends Mage_Catalog_Model_Url {
    /**
     * Refresh product rewrite urls for one store or all stores
     * Called as a reaction on product change that affects rewrites
     * Only root category
     *
     * @param int $productId
     * @param int|null $storeId
     * @return Mage_Catalog_Model_Url
     */
    public function refreshProductRewrite($productId, $storeId = null)
    {
        if (is_null($storeId)) {
            foreach ($this->getStores() as $store) {
                $this->refreshProductRewrite($productId, $store->getId());
            }
            return $this;
        }

        $product = $this->getResource()->getProduct($productId, $storeId);
        if ($product) {
            $useCategoriesInUrl = Mage::getStoreConfig('catalog/seo/product_use_categories');
            $enableOptimisation = Mage::getStoreConfigFlag('dev/index/enable');

            $store = $this->getStores($storeId);
            $storeRootCategoryId = $store->getRootCategoryId();



            if($useCategoriesInUrl!="0"||!$enableOptimisation) {
                // List of categories the product is assigned to, filtered by being within the store's categories root
                $categories = $this->getResource()->getCategories($product->getCategoryIds(), $storeId);
                $this->_rewrites = $this->getResource()->prepareRewrites($storeId, '', $productId);

                // Add rewrites for all needed categories
                // If product is assigned to any of store's categories -
                // we also should use store root category to create root product url rewrite
                if (!isset($categories[$storeRootCategoryId])) {
                    $categories[$storeRootCategoryId] = $this->getResource()->getCategory($storeRootCategoryId, $storeId);
                }

                // Create product url rewrites
                foreach ($categories as $category) {
                    $this->_refreshProductRewrite($product, $category);
                }

                // Remove all other product rewrites created earlier for this store - they're invalid now
                $excludeCategoryIds = array_keys($categories);
                $this->getResource()->clearProductRewrites($productId, $storeId, $excludeCategoryIds);
                unset($categories);
            
            } else {
                $this->_rewrites = $this->getResource()->prepareRewrites($storeId, '', $productId);
                $rootCategory = $this->getResource()->getCategory($storeRootCategoryId, $storeId);
                $this->_refreshProductRewrite($product, $rootCategory);

                $this->getResource()->clearProductRewrites($productId, $storeId, array($storeRootCategoryId));
            }
            unset($product);
        } else {
            // Product doesn't belong to this store - clear all its url rewrites including root one
            $this->getResource()->clearProductRewrites($productId, $storeId, array());
        }

        return $this;
    }

    /**
     * Get unique category request path
     *
     * @param Varien_Object $category
     * @param string $parentPath
     * @return string
     */
    public function getCategoryRequestPath($category, $parentPath)
    {
        $storeId = $category->getStoreId();
        $idPath  = $this->generatePath('id', null, $category);
        $suffix  = $this->getCategoryUrlSuffix($storeId);

        if (isset($this->_rewrites[$idPath])) {
            $this->_rewrite = $this->_rewrites[$idPath];
            $existingRequestPath = $this->_rewrites[$idPath]->getRequestPath();
        }

        if ($category->getUrlKey() == '') {
            $urlKey = $this->getCategoryModel()->formatUrlKey($category->getName());
        }
        else {
            $urlKey = $this->getCategoryModel()->formatUrlKey($category->getUrlKey());
        }

        $categoryUrlSuffix = $this->getCategoryUrlSuffix($category->getStoreId());
        if (null === $parentPath) {
            $parentPath = $this->getResource()->getCategoryParentPath($category);
        }
        elseif ($parentPath == '/') {
            $parentPath = '';
        }
        $parentPath = Mage::helper('catalog/category')->getCategoryUrlPath($parentPath,
                      true, $category->getStoreId());
        $enabled = Mage::getStoreConfig('activo_categoryurlseo/global/enabled');

        if ($enabled) {
            $parentPath = '';
        }
        $requestPath = $parentPath . $urlKey . $categoryUrlSuffix;
        if (isset($existingRequestPath) && $existingRequestPath == $requestPath . $suffix) {
            return $existingRequestPath;
        }

        if ($this->_deleteOldTargetPath($requestPath, $idPath, $storeId)) {
            return $requestPath;
        }

        return $this->getUnusedPath($category->getStoreId(), $requestPath,
                                    $this->generatePath('id', null, $category)
                                   );
    }

    public function refreshProductRewrites($storeId)
    {
        ini_set("max_execution_time", 3600);
        Mage::log("Reindex start", null, "patch_url.log");
        $this->_categories = array();
        $storeRootCategoryId = $this->getStores($storeId)->getRootCategoryId();
        $this->_categories[$storeRootCategoryId] = $this->getResource()->getCategory($storeRootCategoryId, $storeId);

        $lastEntityId = 0;
        $process = true;

        $enableOptimisation = Mage::getStoreConfigFlag('dev/index/enable');
        $excludeProductsDisabled = Mage::getStoreConfigFlag('dev/index/disable');
        $excludeProductsNotVisible = Mage::getStoreConfigFlag('dev/index/notvisible');
        $useCategoriesInUrl = Mage::getStoreConfig('catalog/seo/product_use_categories');

        while ($process == true) {
            $products = $this->getResource()->getProductsByStore($storeId, $lastEntityId);
            if (!$products) {
                $process = false;
                break;
            }

            $this->_rewrites = array();
            $this->_rewrites = $this->getResource()->prepareRewrites($storeId, false, array_keys($products));

            $loadCategories = array();
            foreach ($products as $product) {
                foreach ($product->getCategoryIds() as $categoryId) {
                    if (!isset($this->_categories[$categoryId])) {
                        $loadCategories[$categoryId] = $categoryId;
                    }
                }
            }

            if ($loadCategories) {
                foreach ($this->getResource()->getCategories($loadCategories, $storeId) as $category) {
                    $this->_categories[$category->getId()] = $category;
                }
            }


            foreach ($products as $product) {
                if($enableOptimisation&&$excludeProductsDisabled&&$product->getData("status")==2)
                {
                    continue;
                }

                if($enableOptimisation&&$excludeProductsNotVisible&&$product->getData("visibility")==1)
                {
                    continue;
                }

                // Always Reindex short url
                $this->_refreshProductRewrite($product, $this->_categories[$storeRootCategoryId]);


                if($useCategoriesInUrl!="0"||!$enableOptimisation)
                {
                    foreach ($product->getCategoryIds() as $categoryId) {
                        if ($categoryId != $storeRootCategoryId && isset($this->_categories[$categoryId])) {
                            $this->_refreshProductRewrite($product, $this->_categories[$categoryId]);
                        }
                    }
                }

            }

            unset($products);
            $this->_rewrites = array();
        }

        $this->_categories = array();
        Mage::log("Reindex stop", null, "patch_url.log");
        return $this;
    }
}