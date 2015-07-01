<?php

/**
 * Class Zolago_Catalog_Model_Url
 */
class Zolago_Catalog_Model_Url extends Mage_Catalog_Model_Url {
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