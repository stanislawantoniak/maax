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

        $q = $this->getRequest()->getParam('q', '');
        if (!empty($q)) {
            Mage::register('IS_SEARCH_MODE', true);
        }
	}

	/**
	 * Get list plus blocks
	 */
	public function get_blocksAction() {
		$this->_initCategory();
		$listModel = Mage::getSingleton("zolagosolrsearch/catalog_product_list");
		/* @var $listModel Zolago_Solrsearch_Model_Catalog_Product_List */
		
		$layout = $this->getLayout();
		$design = Mage::getDesign();
		
		$packageName = Mage::app()->getStore()->getConfig('design/package/name');
		$theme = Mage::app()->getStore()->getConfig('design/theme/template');
		
		$design->setPackageName($packageName);
		$design->setTheme($theme ? $theme : "default");
		
		$type = $listModel->getMode()==$listModel::MODE_SEARCH ? "search" : "category";
		
		// Product 
		$products = $this->_getProducts($listModel);

		$content=  array_merge($products, array(//Zolago_Modago_Block_Solrsearch_Faces
			"header"		=> $layout->createBlock("zolagosolrsearch/catalog_product_list_header_$type")->toHtml(),
			"toolbar"		=> $layout->createBlock("zolagosolrsearch/catalog_product_list_toolbar")->toHtml(),
			"filters"		=> $layout->createBlock("zolagomodago/solrsearch_faces")->toHtml(),
			"active"		=> $layout->createBlock("zolagosolrsearch/active")->toHtml()
		));
		
		$result = $this->_formatSuccessContentForResponse($content);
		$this->_setSuccessResponse($result);
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
		return $listModel->getCollection()->getSolrData('request', 'responseHeader', 'params', $param);
	}
	
	/**
	 * @param Zolago_Solrsearch_Model_Catalog_Product_List $listModel
	 * @return array
	 */
	protected function _getProducts(Zolago_Solrsearch_Model_Catalog_Product_List $listModel) {
		
		
		//$profiler = Mage::helper("zolagocommon/profiler");
		/* @var $profiler Zolago_Common_Helper_Profiler */
		//$profiler->start();
		
		
		return array(
			"total"			=> (int)$listModel->getCollection()->getSize(),
			"start"			=> (int)$this->_getSolrParam($listModel, 'start'),
			"rows"			=> (int)$this->_getSolrParam($listModel, 'rows'),
			"query"			=> $this->_getSolrParam($listModel, 'q'),
			"sort"			=> $listModel->getCurrentOrder(),
			"dir"			=> $listModel->getCurrentDir(),
			"products"		=> Mage::helper("zolagosolrsearch")->prepareAjaxProducts($listModel),
		);
	}
	
	
}