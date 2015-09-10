<?php

class Orba_Common_Ajax_ListingController extends Orba_Common_Controller_Ajax {
	/**
	 * Init category and register it
	 */
    protected function _initCategory() {
		$categoryId = $this->getRequest()->getParam("scat", 0);
		$catModel = null;
		if($categoryId){
			$catModel = Mage::getModel("catalog/category")->load($categoryId);
		}
		if(!$catModel || !$catModel->getId()){
			$catModel = Mage::helper("zolagodropshipmicrosite")->getVendorRootCategoryObject();
		}
		Mage::register("current_category", $catModel);
	}

	/**
	 * Get list plus blocks
	 */
	public function get_blocksAction() {
		$this->_initCategory();

		/* @var $listModel Zolago_Solrsearch_Model_Catalog_Product_List */
		$listModel = Mage::getSingleton("zolagosolrsearch/catalog_product_list");
		
		$layout = $this->getLayout();
		$design = Mage::getDesign();
		
		$packageName = Mage::app()->getStore()->getConfig('design/package/name');
		$theme = Mage::app()->getStore()->getConfig('design/theme/template');
		
		$design->setPackageName($packageName);
		$design->setTheme($theme ? $theme : "default");

		$type = $listModel->getMode()==$listModel::MODE_SEARCH ? "search" : "category";

		// Product 
		$products = $this->_getProducts($listModel);


		$params = $this->getRequest()->getParams();

		$fq = isset($params["fq"]) ? $params["fq"] : array();

		Mage::register("listing_reload_params", $params);

		$categoryId = isset($params['scat']) && $params['scat'] ? $params['scat'] : 0;
		/** @var GH_Rewrite_Helper_Data $rewriteHelper */
		$rewriteHelper = Mage::helper('ghrewrite');
		$rewriteHelper->clearParams($params);
		$rewriteHelper->sortParams($params);

		/** @var Zolago_Catalog_Model_Category $category */
		$category = Mage::registry('current_category');

		$categoryDisplayMode = (int)($category->getDisplayMode()==Mage_Catalog_Model_Category::DM_PAGE);


		//if filter params then set display_mode to PRODUCTS
		if($fq){
			$category->setDisplayMode(Mage_Catalog_Model_Category::DM_PRODUCT);
		} else {
			$category->setDisplayMode(Mage_Catalog_Model_Category::DM_PAGE);
		}



		$rootId = Mage::app()->getStore()->getRootCategoryId();

		/* @var $zDropshipHelper Zolago_Dropship_Helper_Data */
		$zDropshipHelper = Mage::helper("zolagodropship");
		$vendorRootCategory = $zDropshipHelper->getCurrentVendorRootCategory();

		$url = false;
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

        Mage::register("category_with_filters", $url);

        $breadcrumbs = new Zolago_Catalog_Block_Breadcrumbs();
        $path = $breadcrumbs->getPathProp();
        $title = array();
        foreach ($path as $name => $breadcrumb) {
            $title[] = $breadcrumb['label'];
        }
        $title = join($breadcrumbs->getTitleSeparator(), array_reverse($title));

        $rewriteData = Mage::helper("ghrewrite")->getCategoryRewriteData();

        if (!empty($rewriteData) && isset($rewriteData["title"]) && !empty($rewriteData["title"])) {
            $title = $rewriteData["title"];
        }

        $block = $layout->createBlock("zolagosolrsearch/catalog_product_list_header_$type");
        $block->setChild('zolagocatalog_breadcrumbs', $layout->createBlock('zolagocatalog/breadcrumbs'));
		$block->setChild('solrsearch_product_list_active', $layout->createBlock('zolagosolrsearch/active'));

		$toolbar = $layout->createBlock("zolagosolrsearch/catalog_product_list_toolbar");
		$toolbar->setChild('product_list_toolbar_pager',
			$layout->createBlock('zolagosolrsearch/catalog_product_list_pager')
				->setGeneratedUrl($url)
				->setTemplate("zolagosolrsearch/catalog/product/list/pager.phtml")
		);

		$content=  array_merge($products, array(//Zolago_Modago_Block_Solrsearch_Faces
			"url"			=> $url,
			"header"		=> $this->_cleanUpHtml($block->toHtml()),
			"toolbar"		=> $this->_cleanUpHtml($toolbar->toHtml()),
			"filters"		=> $this->_cleanUpHtml($layout->createBlock("zolagomodago/solrsearch_faces")->toHtml()),
            "category_with_filters"=> $this->_cleanUpHtml($layout->createBlock("zolagomodago/catalog_category_rewrite")->toHtml()),
			"breadcrumbs"=> $this->_cleanUpHtml($layout->createBlock("zolagocatalog/breadcrumbs")->toHtml()),
			"active"		=> $this->_cleanUpHtml($layout->createBlock("zolagosolrsearch/active")->toHtml()),
            "category_head_title" => $title,
			"category_display_mode" => $categoryDisplayMode,
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
	protected function _cleanUpHtml($string) {
		$string = preg_replace('/\s*$^\s*/m', "\n", $string);
		return preg_replace('/[ \t]+/', ' ', $string);
	}
	
	/**
	 * Get product list for listing
	 */
	public function get_productsAction() {
		$this->_initCategory();
		$listModel = Mage::getSingleton("zolagosolrsearch/catalog_product_list");
		/* @var $listModel Zolago_Solrsearch_Model_Catalog_Product_List */
		$products=$this->_getProducts($listModel);
		
		$result = $this->_formatSuccessContentForResponse($products);

		$this->_setSuccessResponse($result);
	}
	
	/**
	 * 
	 * @param Zolago_Solrsearch_Model_Catalog_Product_List $listModel
	 * @param type $param
	 * @return type
	 */
	protected function _getSolrParam(Zolago_Solrsearch_Model_Catalog_Product_List $listModel, $param) {
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
	protected function _getProducts(Zolago_Solrsearch_Model_Catalog_Product_List $listModel) {
		
		
		//$profiler = Mage::helper("zolagocommon/profiler");
		/* @var $profiler Zolago_Common_Helper_Profiler */
		//$profiler->start();

		/** @var Zolago_Solrsearch_Helper_Data $_solrHelper */
		$_solrHelper = Mage::helper("zolagosolrsearch");

		return array(
			"total"			=> (int)$listModel->getCollection()->getSize(),
			"start"			=> (int)$this->_getSolrParam($listModel, 'start'),
			"rows"			=> (int)$this->_getSolrParam($listModel, 'rows'),
			"query"			=> '', ///    jak nie działało było dobrze. parametr prawdopodobnie kompletnie niepotrzebny w tym kontekście.  [ $this->_getSolrParam($listModel, 'q'), ]
			"sort"			=> $listModel->getCurrentOrder(),
			"dir"			=> $listModel->getCurrentDir(),
			"products"		=> $_solrHelper->prepareAjaxProducts($listModel),
		);
	}
	
	
}