<?php

class Orba_Common_Ajax_ListingController extends Orba_Common_Controller_Ajax
{
    /**
     * Init category and register it
     */
    protected function _initCategory()
    {
        $categoryId = $this->getRequest()->getParam("scat", 0);
        $catModel = null;
        if ($categoryId) {
            $catModel = Mage::getModel("catalog/category")->load($categoryId);
        }
        if (!$catModel || !$catModel->getId()) {
            $catModel = Mage::helper("zolagodropshipmicrosite")->getVendorRootCategoryObject();
        }

        //if filter params then set display_mode to PRODUCTS
        $fq = Mage::app()->getRequest()->getParams("fq", array());
        $originDisplayMode = $catModel->getDisplayMode();
        $catModel->setOriginDisplayMode($originDisplayMode);
        if (!empty($fq)) {
            $catModel->setDisplayMode(Mage_Catalog_Model_Category::DM_PRODUCT);
        } else {
            $catModel->setDisplayMode(Mage_Catalog_Model_Category::DM_PAGE);
        }
        $current_category = "current_category";
        if (Mage::registry($current_category)) {
            Mage::unregister($current_category);
        }
        Mage::register($current_category, $catModel);

        return $catModel;
    }

    protected function _initCurrentLayout()
    {
        $layout = $this->getLayout();
        $design = Mage::getDesign();

        $packageName = Mage::app()->getStore()->getConfig('design/package/name');
        $theme = Mage::app()->getStore()->getConfig('design/theme/template');

        $design->setPackageName($packageName);
        $design->setTheme($theme ? $theme : "default");

        return $layout;
    }

    /**
     * Get list plus blocks
     */
    public function get_blocksAction()
    {
        $category = $this->_initCategory();

        /* @var $listModel Zolago_Solrsearch_Model_Catalog_Product_List */
        $listModel = Mage::getSingleton("zolagosolrsearch/catalog_product_list");

        $layout = $this->_initCurrentLayout();

        $type = $listModel->getMode() == $listModel::MODE_SEARCH ? "search" : "category";

        // Product
        $products = $this->_getProducts($listModel);


        $params = $this->getRequest()->getParams();

        unset($params['start']); //unset start because this means that sorting/filters has changed

        $fq = isset($params["fq"]) ? $params["fq"] : array();

        Mage::register("listing_reload_params", $params);

        $categoryId = isset($params['scat']) && $params['scat'] ? $params['scat'] : 0;



        // CMS page
        $reloadToCms = ((int)($category->getOriginDisplayMode() == Mage_Catalog_Model_Category::DM_PAGE) && empty($fq));

        /** @var Zolago_Catalog_Model_Category $category */
        $vendor = Mage::helper('umicrosite')->getCurrentVendor();
        if ($vendor) {
            // Set root category
            $vendorRootCategory = $vendor->rootCategory();
            //Vendor landing page
            $reloadToCms = ($vendorRootCategory->getId() == $category->getId() && empty($fq));
        }

        $url = $this->generateAjaxLink($category, $categoryId, $params, $type);


        $categoryWithFiltersKey = "category_with_filters";
        if (Mage::registry($categoryWithFiltersKey)) {
            Mage::unregister($categoryWithFiltersKey);
        }
        Mage::register($categoryWithFiltersKey, $url);

        //title
        $campaign = $category->getCurrentCampaign();
        if ($campaign) {
            $title = $campaign->getNameCustomer() . " - " . Mage::app()->getStore()->getName();
        } else {
            /** @var GH_Rewrite_Helper_Data $_rewriteHlp */
            $_rewriteHlp = Mage::helper("ghrewrite");
            $rewriteData = $_rewriteHlp->getCategoryRewriteData();
            if ($rewriteData && count($rewriteData) && isset($rewriteData['title']) && !empty($rewriteData['title'])) {
                $title = $rewriteData['title'];
            } elseif ($category->getMetaTitle()) {
                $title = $category->getMetaTitle();
            } else {
                $title = Mage::app()->getStore()->getName();
            }
        }

        /*        $breadcrumbs = new Zolago_Catalog_Block_Breadcrumbs();
                $path = $breadcrumbs->getPathProp();
                $title = array();
                foreach ($path as $name => $breadcrumb) {
                    $title[] = $breadcrumb['label'];
                }
                $title = join($breadcrumbs->getTitleSeparator(), array_reverse($title));

                $rewriteData = Mage::helper("ghrewrite")->getCategoryRewriteData();

                if (!empty($rewriteData) && isset($rewriteData["title"]) && !empty($rewriteData["title"])) {
                    $title = $rewriteData["title"];
                }*/



        $header = $layout->createBlock("zolagosolrsearch/catalog_product_list_header_$type");
        $header->setChild('zolagocatalog_breadcrumbs', $layout->createBlock('zolagocatalog/breadcrumbs'));
        $header->setChild('solrsearch_product_list_active', $layout->createBlock('zolagosolrsearch/active'));


        $content = array_merge($products, array(//Zolago_Modago_Block_Solrsearch_Faces
            "url" => $url,
            "header" => $this->_cleanUpHtml($header->toHtml()),
            "filters" => $this->_cleanUpHtml($layout->createBlock("zolagomodago/solrsearch_faces")->toHtml()),
            "document_title" => $title,
            "category_with_filters" => $this->_cleanUpHtml($layout->createBlock("zolagomodago/catalog_category_rewrite")->toHtml()),
            "reload_to_cms" => $reloadToCms,
            "listing_type" => $type
        ));

        $result = $this->_formatSuccessContentForResponse($content);
        $this->_setSuccessResponse($result);
    }

    /**
     * clean ups html from excess of newlines, whitespaces and tabs
     * @param $string
     * @return string
     */
    protected function _cleanUpHtml($string)
    {
        $string = preg_replace('/\s*$^\s*/m', "\n", $string);
        return preg_replace('/[ \t]+/', ' ', $string);
    }

    /**
     * Get product list for listing
     */
    public function get_productsAction()
    {
        $listModel = Mage::getSingleton("zolagosolrsearch/catalog_product_list");
        /* @var $listModel Zolago_Solrsearch_Model_Catalog_Product_List */
        $products = $this->_getProducts($listModel);

        $result = $this->_formatSuccessContentForResponse($products);

        $this->_setSuccessResponse($result);
    }

    /**
     *
     * @param Zolago_Solrsearch_Model_Catalog_Product_List $listModel
     * @param type $param
     * @return type
     */
    protected function _getSolrParam(Zolago_Solrsearch_Model_Catalog_Product_List $listModel, $param)
    {
        if (is_null($out = $listModel->getCollection()->getSolrData('request', 'responseHeader', 'params', $param))) {
            $out = $listModel->getCollection()->getSolrData('responseHeader', 'params', $param);
        }
        return $out;
    }

    /**
     * @param Zolago_Solrsearch_Model_Catalog_Product_List $listModel
     * @return array
     * @todo fix q param in getSolrParams (check if empty - **, ,***, ** , etc.))
     */
    protected function _getProducts(Zolago_Solrsearch_Model_Catalog_Product_List $listModel)
    {

        /** @var Zolago_Solrsearch_Helper_Data $_solrHelper */
        $_solrHelper = Mage::helper("zolagosolrsearch");
        $layout = $layout = $this->_initCurrentLayout();

        $params = $this->getRequest()->getParams();
        unset($params["start"]);
        $categoryId = isset($params['scat']) && $params['scat'] ? $params['scat'] : 0;

        $category = $this->_initCategory();

        $type = $listModel->getMode() == $listModel::MODE_SEARCH ? "search" : "category";

        $url = $this->generateAjaxLink($category, $categoryId, $params, $type);

        Mage::log($url,null,'urlooooo.log');

        $pager = $layout->createBlock("zolagosolrsearch/catalog_product_list_pager")
            ->setGeneratedUrl($url)
            ->setTemplate("zolagosolrsearch/catalog/product/list/pager.phtml");

        return array(
            "total" => (int)$listModel->getCollection()->getSize(),
            "start" => (int)$this->_getSolrParam($listModel, 'start'),
            "rows" => (int)$this->_getSolrParam($listModel, 'rows'),
            "query" => '', ///    jak nie działało było dobrze. parametr prawdopodobnie kompletnie niepotrzebny w tym kontekście.  [ $this->_getSolrParam($listModel, 'q'), ]
            "sort" => $listModel->getCurrentOrder(),
            "dir" => $listModel->getCurrentDir(),
            "products" => $_solrHelper->prepareAjaxProducts($listModel),
            "pager" => $this->_cleanUpHtml($pager->toHtml()),
            "url" => $url
        );
    }


    /**
     * Generate category url (depends on landing page ->@see Zolago_Campaign_Helper_LandingPage getLandingPageUrlByCampaign)
     * or GH_Rewrite ->@see GH_Rewrite_Helper_Data prepareRewriteUrl
     * or category_url
     * @param $category
     * @param $categoryId
     * @param $params
     * @param $type
     * @return $this|bool|mixed|string
     */
    public function generateAjaxLink($category, $categoryId, $params, $type)
    {
        $url = false;
        /** @var GH_Rewrite_Helper_Data $rewriteHelper */
        $rewriteHelper = Mage::helper('ghrewrite');
        $rewriteHelper->clearParams($params);
        $rewriteHelper->sortParams($params);

        $rootId = Mage::app()->getStore()->getRootCategoryId();

        /* @var $zDropshipHelper Zolago_Dropship_Helper_Data */
        $zDropshipHelper = Mage::helper("zolagodropship");
        $vendorRootCategory = $zDropshipHelper->getCurrentVendorRootCategory();

        if ($type == "search") {
            $query = http_build_query($params);
            $url = Mage::getUrl('search') . ($query ? "?" . $query : "");
        } elseif ($type == "category") {

            $campaign = $category->getCurrentCampaign();
            if ($campaign) {
                /* @var $landingPageHelper Zolago_Campaign_Helper_LandingPage */
                $landingPageHelper = Mage::helper("zolagocampaign/landingPage");
                $url = $landingPageHelper->getLandingPageUrlByCampaign($campaign, FALSE, $params);

            } elseif ($categoryId == $rootId || $categoryId == $vendorRootCategory) {
                //Case when remove last filter on GALLERY ROOT listing or VENDOR ROOT listing (Landing pages)
                $query = http_build_query($params);
                $url = Mage::getBaseUrl() . ($query ? "?" . $query : "");
            } else {
                $url = $rewriteHelper->prepareRewriteUrl('catalog/category/view', $categoryId, $params);
            }
        }
        if (!$url) {
            $query = http_build_query($params);
            $url = Mage::getBaseUrl() . $category->getUrlPath() . ($query ? "?" . $query : "");
        }

        if(Mage::app()->getStore()->isCurrentlySecure()) {
            $url = str_replace('http://','https://',$url);
        }

        return $url;
    }


}