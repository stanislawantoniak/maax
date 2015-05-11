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
			"header"		=> $this->_cleanUpHtml($layout->createBlock("zolagosolrsearch/catalog_product_list_header_$type")->toHtml()),
			"toolbar"		=> $this->_cleanUpHtml($layout->createBlock("zolagosolrsearch/catalog_product_list_toolbar")->toHtml()),
			"filters"		=> $this->_cleanUpHtml($layout->createBlock("zolagomodago/solrsearch_faces")->toHtml()),
			"active"		=> $this->_cleanUpHtml($layout->createBlock("zolagosolrsearch/active")->toHtml())
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

		$currentCategory = Mage::registry('current_category')->getId();
		$customerSession = Mage::getSingleton('customer/session');
		$prevCategory = $customerSession->getData('currentProductsCategory');
		$prevProducts = $customerSession->getData('currentProducts');

		if($currentCategory != $prevCategory ||
			is_null($prevProducts) ||
			!isset($prevProducts['dir']) || $prevProducts['dir'] != $products['dir'] ||
			!isset($prevProducts['sort']) || $prevProducts['sort'] != $products['sort'] ||
			!isset($prevProducts['query']) || $prevProducts['query'] != $products['query'] ||
			!isset($prevProducts['total']) || $prevProducts['total'] != $products['total'])
		{ //clear products if user is looking at another category, changed sorting or search query
			Mage::log("session cleared",null,'sessionClear.log');
			$customerSession->unsetData('currentProductsCategory')->unsetData('currentProducts');
		}

		if(is_null($customerSession->getData('currentProductsCategory'))) {
			$customerSession->setData('currentProductsCategory', $currentCategory);
			$customerSession->setData('currentProducts', $products);
		} elseif($prevProducts['start'] < $products['start']) {
			$newProducts = $products;
			$newProducts['products'] = array_merge($prevProducts['products'],$newProducts['products']);
			$customerSession->setData('currentProducts',$newProducts);
		}

		Mage::log($customerSession->getData('currentProductsCategory'),null,'currentCategory.log');
		Mage::log($customerSession->getData('currentProducts'),null,'currentProducts.log');

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