<?php

class Zolago_Catalog_Block_Breadcrumbs extends Mage_Catalog_Block_Breadcrumbs
{
    protected $_vendor;
    protected $_breadcrumbBlock;
    protected $_path;
    protected $_rootId;

    /**
     * get product
     * @return Mage_Catalog_Model_Product
     */
    protected function _getProduct()
    {
        if (($product = Mage::registry('current_product'))
            && (Mage::registry('current_product') instanceof Mage_Catalog_Model_Product)
        ) {
            return $product;
        } else {
            return 0;
        }
    }

    /**
     * return breadcrumb block
     * @return
     */

    protected function _toHtml()
    {
        return $this->getLayout()->getBlock('breadcrumbs')->toHtml();
    }

    /**
     * prepare breadcrumb path
     * @return array
     */
    protected function _getPath()
    {
        if (is_null($this->_path)) {
            $this->_path = $this->_preparePath();
        }
        return $this->_path;

        /* @var $catalogHelper Mage_Catalog_Helper_Data
        $catalogHelper = Mage::helper('catalog');
         * $category = $catalogHelper->getCategory();
         *
         * $refererUrl = $this->getRequest()->getServer("HTTP_REFERER");
         * $params = explode("&", $refererUrl);
         *
         * if (
         * !$category
         * || $category->getId() == $this->_getRootCategoryId()
         * || (Mage::registry('current_product') && (int)strpos($refererUrl,"search") > 0 && !in_array("scat=".$category->getId(), $params))
         * ) {
         * $category = $this->_getDefaultCategory(
         * $this->_getProduct(),
         * $this->_getRootCategoryId()
         * );
         * }
         * if ($category) {
         * $path = $this->_preparePath($category);
         * } else {
         * $path = array();
         * }
         * if ($product = $this->_getProduct()) {
         * $path['product'] = array('label'=>$product->getName());
         * }
         *
         * $this->_path = $path;
         * }
         *
         * return $this->_path;
         */
    }

    /**
     * get type name by vendor type
     * @param int $vendorType ;
     * @return string
     */
    protected function _getVendorTypeName($vendorType)
    {
        $out = '';
        $helper = Mage::helper('catalog');
        switch ($vendorType) {
            case Zolago_Dropship_Model_Vendor::VENDOR_TYPE_STANDARD:
                $out = $helper->__('Seller');
                break;
            case Zolago_Dropship_Model_Vendor::VENDOR_TYPE_BRANDSHOP:
                $out = ' ' . $helper->__('Shop');
                break;
        }
        return $out;
    }

    /**
     * get actual vendor
     * @return
     */
    protected function _getVendor()
    {
        if (is_null($this->_vendor)) {
            $this->_vendor = 0;
            // Add vendor
            $vendor = Mage::helper('umicrosite')->getCurrentVendor();
            if ($vendor && $vendor->getId()) {
                $this->_vendor = $vendor;
            }
        }
        return $this->_vendor;
    }


    /**
     *
     * @return boolean
     */
    protected function _isSearchContext()
    {
        $request = $this->getRequest();
        return (
            $request->getModuleName() == "search" &&
            $request->getControllerName() == "index" &&
            $request->getActionName() == "index"
        );
    }

    /**
     * @return string | null
     */
    protected function _getQuery()
    {
        return Mage::helper("solrsearch")->getParam("q");
    }

    /**
     * @param array $params
     * @return string
     */
    public function getSearchLink(array $params = array())
    {
        return $this->getUrl("search", $params);
    }

    /**
     *
     * @param type $category
     * @param type $parentCategory
     * @param type $parentId
     * @return string
     */
    protected function _prepareCategoryLink($category)
    {
        if ($this->_isSearchContext()) {
            return $this->getSearchLink(
                array(
                    "_query" => array(
                        "q" => $this->_getQuery(),
                        "scat" => $category->getId()
                    )
                ));
        }
        return $category->getUrl();
    }


    /**
     * first part of breadcrumb
     *
     * @param bool $is_vendor
     * @return array
     */
    protected function _getFirstBreadcrumb($is_vendor, Zolago_Campaign_Model_Campaign $campaign = NULL)
    {
        $out = array(
            'name' => 'home',
            'id' => 0,
            'class' => 'breadcrumb-home',
            'label' => Mage::helper('catalog')->__('Home'),
            'title' => Mage::helper('catalog')->__('Go to Home Page'),
            'link' => Mage::getBaseUrl()
        );

        if ($campaign) {
            $out['label'] = $campaign->getNameCustomer();
            $out['title'] = $campaign->getNameCustomer();
            $out['link'] = $campaign->getLPLink();

        }
        if ($is_vendor) {
            $out['label'] = Mage::helper('catalog')->__('Mall');
            $out['title'] = Mage::helper('catalog')->__('Go to Mall');
            $out['link'] = Mage::helper("zolagodropshipmicrosite")->getBaseUrl();

        }
        return $out;
    }

    /**
     * prepare vendor part
     *
     * @param Zolago_Dropship_Model_Vendor $vendor
     * @return array
     */
    protected function _getVendorBreadcrumb($vendor)
    {
        $out = array(
            'name' => 'vendor',
            'class' => 'breadcrumb-vendor',
            'label' => Mage::helper('catalog')->__($vendor->getVendorName()),
            'title' => Mage::helper('catalog')->__($vendor->getVendorName()),
            'link' => Mage::getBaseUrl()
        );
        return $out;
    }

    /**
     * prepare search part
     *
     * @return array
     */
    protected function _getSearchBreadcrumb()
    {
        $out = array(
            'name' => 'search',
            'class' => 'breadcrumb-search',
            'label' => Mage::helper('catalog')->__('Search: %s', $this->escapeHtml($this->_getQuery())),
            'title' => Mage::helper('catalog')->__('Search: %s', $this->escapeHtml($this->_getQuery())),
            'link' => $this->getSearchLink(array(
                "_query" => array(
                    "q" => $this->_getQuery()
                )
            ))
        );
        return $out;
    }

    /**
     * prepare category breadcrumb (with landing page parameters if needed)
     *
     * @param Mage_Catalog_Model_Category $category
     * @param array $lpData landing page data
     * @return array
     */
    protected function _getCategoryBreadcrumb($category, Zolago_Campaign_Model_Campaign $campaign = NULL)
    {
        $categoryName = $category->getName();
        $categoryLongName = $category->getLongName();

        if ($campaign && $category->getId() == $campaign->getLandingPageCategory()) {
            $categoryName = $campaign->getNameCustomer();
            $categoryLongName = $campaign->getNameCustomer();
            $link = $campaign->getLPLink();
        } else {
            $link = $this->_prepareCategoryLink($category);
        }

        $out = array(

            "name" => "category" . $category->getId(),
            "id" => $category->getId(),
            "label" => $categoryName,
            "link" => $link,
            'class' => 'breadcrumb-category',
            'categorylongname' => $categoryLongName

        );
        return $out;
    }

    /**
     * prepare product part
     *
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    protected function _getProductBreadcrumb($product)
    {
        $out = array(
            'name' => 'product',
            'label' => $product->getName(),
            'class' => 'breadcrumb-product',
        );
        return $out;
    }

    /**
     * preparing path
     * @param
     * @return array
     */
    protected function _preparePath()
    {
        $path = array();
        $vendor = $this->_getVendor();
        $rootId = $this->_getRootCategoryId();
        if ($product = $this->_getProduct()) {
            /* @var $category Mage_Catalog_Model_Category */
            $category = $this->_getDefaultCategory($product, $rootId);
        } else {
            $catalogHelper = Mage::helper('catalog');
            /* @var $category Mage_Catalog_Model_Category */
            $category = $catalogHelper->getCategory();
        }

        $searchContext = $this->_isSearchContext();

        /* @var $campaign Zolago_Campaign_Model_Campaign */
        $campaign = $category->getCurrentCampaign();
        /* @var $landingPageHelper Zolago_Campaign_Helper_LandingPage */
        $landingPageHelper = Mage::helper("zolagocampaign/landingPage");
        $LPLink = $landingPageHelper->getLandingPageUrlByCampaign($campaign);
        $campaign->setLPLink($LPLink);

        // gallery / main page
        $path[] = $this->_getFirstBreadcrumb(!empty($vendor), $campaign);
        // vendor
        if ($vendor) {
            $path[] = $this->_getVendorBreadcrumb($vendor);
        }
        // search
        if ($searchContext) {
            $path[] = $this->_getSearchBreadcrumb();
        }
        // category

        if ($category && $category->getId()) {
            $useLp = null;
            $pathIds = $category->getPathIds();
            $pathIds = array_slice($pathIds, 1);
            // Remove root category

            $parents = $category->getParentCategories();
            foreach ($pathIds as $k => $parentId) {

                if (($parentId == $rootId) && !$campaign) {
                    continue; // we are in root
                }
                if (isset($parents[$parentId]) && $parents[$parentId]
                    instanceof Mage_Catalog_Model_Category
                ) {

                    $path[] = $this->_getCategoryBreadcrumb($parents[$parentId], $campaign);
                }
            }
        }
        // product
        if ($product) {
            $path[] = $this->_getProductBreadcrumb($product);
        }
        return $path;
    }

    /**
     * breadcrumb for product
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    protected function _getDefaultCategory($product, $rootId)
    {
        return Mage::helper("zolagosolrsearch")->getDefaultCategory($product, $rootId);
    }

    /**
     * id of root category (depends from website and vendor)
     * @return int
     */
    protected function _getRootCategoryId()
    {
        return Mage::helper("zolagosolrsearch")->getRootCategoryId();
    }

    /**
     * prepare breadcrumb block
     * @return
     */
    protected function _getBlock()
    {

        if (is_null($this->_breadcrumbBlock)) {
            if (!($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs'))) {
                $breadcrumbsBlock = $this->getLayout()->createBlock('page/html_breadcrumbs', 'breadcrumbs');
            }
            $this->_breadcrumbBlock = is_null($breadcrumbsBlock) ? 0 : $breadcrumbsBlock;
        }
        return $this->_breadcrumbBlock;
    }

    public function getPathProp()
    {
        return $this->_getPath();
    }

    /**
     * Preparing layout
     *
     * @return Mage_Catalog_Block_Breadcrumbs
     */
    protected function _prepareLayout()
    {

        if (Mage::registry("bc_prepared")) {
            return $this;
        }
        $title = array();
        $breadcrumbsBlock = $this->_getBlock();
        $path = $this->_getPath();
        foreach ($path as $name => $breadcrumb) {
            $breadcrumbsBlock->addCrumb($name, $breadcrumb);
            $title[] = $breadcrumb['label'];
        }

        if ($headBlock = $this->getLayout()->getBlock('head')) {
            if ($this->_isSearchContext()) {
                $helperZSS = Mage::helper('zolagosolrsearch');
                if ($helperZSS->getNumFound()) {
                    $query = $helperZSS->getSolrRealQ();
                } else {
                    $query = $helperZSS->getQueryText();
                }
                $title = $helperZSS->__('Search results for:') . ' ' . $query;
            } else {
                $title = join($this->getTitleSeparator(), array_reverse($title));
            }
            $headBlock->setTitle($title);
        }

        // Do not prapare bc again
        Mage::register("bc_prepared", true);
        return $this;
    }
}
