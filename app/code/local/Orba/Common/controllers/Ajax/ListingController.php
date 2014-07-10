<?php

class Orba_Common_Ajax_ListingController extends Orba_Common_Controller_Ajax {
	/**
	 * Init category an register it
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
		
		// Product $
		$products = $this->_getProducts($listModel);
		
		// Header
		$type = $listModel->getMode()==$listModel::MODE_SEARCH ? "search" : "category";
		$header = $this->getLayout()->createBlock("zolagosolrsearch/catalog_product_list_header_$type")->toHtml();
		
		// Toolbar
		$toolbar = $this->getLayout()->createBlock("zolagosolrsearch/catalog_product_list_toolbar")->toHtml();
		
		// Filter
		$filters = $this->getLayout()->createBlock("zolagosolrsearch/catalog_product_list_faces")->toHtml();
		
		$content=array(
			"products" => $products,
			"total" => $listModel->getCollection()->getSize(),
			"header" => $header,
			"toolbar" => $toolbar,
			"filters" => $filters
		);
		
		$result = $this->_formatSuccessContentForResponse($content);
		$this->_setResponse($result);
	}
	
	/**
	 * Get product list for listing
	 */
	public function get_productsAction() {
		$this->_initCategory();
		$listModel = Mage::getSingleton("zolagosolrsearch/catalog_product_list");
		/* @var $listModel Zolago_Solrsearch_Model_Catalog_Product_List */
		$content=$this->_getProducts($listModel);
		$result = $this->_formatSuccessContentForResponse($content);
		
		$this->_setResponse($result);
	}
	
	/**
	 * @param Zolago_Solrsearch_Model_Catalog_Product_List $listModel
	 * @return array
	 */
	protected function _getProducts(Zolago_Solrsearch_Model_Catalog_Product_List $listModel) {
		// Create product list 
		$products = array();
		foreach ($listModel->getCollection() as $product){
			/* @var $_product Zolago_Solrsearch_Model_Catalog_Product */
			$_product = $product->getData();
			$_product['listing_resized_image_url'] = $product->getListingResizedImageUrl();
			$products[] = $_product;
		}
		return $products;
	}
	
	/**
	 * @param mixed $response
	 */
	protected function _setResponse($response) {
		$this->getResponse()
			->clearHeaders()
			->setHeader('Content-type', 'application/json')
			->setBody(Mage::helper('core')->jsonEncode($response));
	}
    
}